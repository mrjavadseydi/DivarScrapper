<?php

namespace App\Jobs;

use App\Models\Result;
use App\Models\Scrap;
use App\Models\User;
use App\Notifications\ScrapDoneNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ScrapeDivarJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $category, $page_limit, $title, $city, $base_url = "https://api.divar.ir/v8/web-search/", $city_id, $last_page,$tokens,$user_id;
    private $scrap_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($category, $page_limit, $title, $city, $scrap_id,$tokens,$user_id)
    {
        $this->category = $category;
        $this->page_limit = $page_limit;
        $this->title = array_filter(explode(',',$title));
        $this->city = $city;
        $this->scrap_id = $scrap_id;
        $this->tokens = explode('\n',$tokens);
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->getDivar();
    }

    private function getDivar()
    {
        if ($this->category!="ROOT"){
            $url = $this->base_url . "/{$this->city}/" . $this->category;
        }else{
            $url = $this->base_url . "{$this->city}/";
        }
        $headers = config('header');
        $request = Http::withHeaders($headers)->get($url)->json();
        $this->city_id = $request['web_widgets']['post_list'][0]['action_log']['server_side_info']['info']['extra_data']['jli']['cities'][0];

        $this->filters($request);
        for ($i = $this->page_limit; $i > 0; $i--) {

            $data = [
                'json_schema' => [
                    'category' => [
                        'value' => $this->category
                    ],
                    'cities' => [
                        $this->city_id,
                    ],
                ],
                'last-post-date' => $this->last_page,
            ];
            $headers['authorization'] = $this->tokens[array_rand($this->tokens)];
            $url = $this->base_url . "/{$this->city_id}/" . $this->category;

            $request = Http::withHeaders($headers)->asJson()->post($url, $data)->json();

            $this->filters($request);

        }
        Scrap::where('id',$this->scrap_id)->update(['status'=>1]);
        Notification::send(User::find($this->user_id),new ScrapDoneNotification());
    }

    private function filters($arr)
    {
        foreach ($arr['web_widgets']['post_list'] as $post) {
            $uid = $post['data']['action']['payload']['token'];
            $title = $post['data']['title'];
            $price = $post['data']['middle_description_text'];
            $date = $post['data']['bottom_description_text'];
            $description_request = Http::withHeaders(config('header'))->get("https://api.divar.ir/v8/posts-v2/web/".$uid)->json();
            $description ='';
            foreach ($description_request['sections'] as $section) {
                if ($section['section_name']=="DESCRIPTION"){
                    foreach ($section['widgets'] as $widget) {
                        if(isset($widget['data']['text'])){
                            $description = $widget['data']['text'];
                        }
                    }
                }
            }
            if ($this->searchIn($title) !== false ||$this->searchIn($description) !== false) {
                $headers = config('header');
                $headers['authorization'] =$this->tokens[array_rand($this->tokens)];
                $data = Http::withHeaders($headers)->get('https://api.divar.ir/v5/posts/' . $uid . '/contact/')->json();
                $phone = $data['widgets']['contact']['phone'];
                Result::create([
                    'scrap_id' => $this->scrap_id,
                    'title' => $title,
                    'date' => $date,
                    'price' => $price,
                    'phone'=>$phone,
                    'description'=>$description,
                ]);
            }
            $this->last_page = $post['action_log']['server_side_info']['info']['extra_data']['last_post_sort_date'];
        }

    }
    function searchIn($text){
        foreach ($this->title as $title) {
            if (strpos($text, $title) !== false) {
                return true;
            }
        }
        return false;
    }


}
