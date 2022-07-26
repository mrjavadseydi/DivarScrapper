<?php

namespace App\Jobs;

use App\Models\Result;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScrapeDivarJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $category, $page_limit, $title, $city, $base_url = "https://api.divar.ir/v8/web-search/", $city_id, $last_page;
    private $scrap_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($category, $page_limit, $title, $city, $scrap_id)
    {
        $this->category = $category;
        $this->page_limit = $page_limit;
        $this->title = $title;
        $this->city = $city;
        $this->scrap_id = $scrap_id;
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
        $url = $this->base_url . "/{$this->city}/" . $this->category;
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
            $headers['authorization'] = 'Basic eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyIjoiMDkzOTc2ODgxNzQiLCJpc3MiOiJhdXRoIiwiaWF0IjoxNjU4NzAxMDg4LCJleHAiOjE2NTk5OTcwODgsInZlcmlmaWVkX3RpbWUiOjE2NTg3MDEwODgsInVzZXItdHlwZSI6InBlcnNvbmFsIiwidXNlci10eXBlLWZhIjoiXHUwNjdlXHUwNjQ2XHUwNjQ0IFx1MDYzNFx1MDYyZVx1MDYzNVx1MDZjYyIsInNpZCI6IjgyM2IwNWMwLTYxMTAtNDBhZC05NmE2LWU0ZWJlZDk2ZTMyYyJ9.CsUlqPnrWO2qTXutUDUoJHUcvHWq6acLX-vakiuakUo';
            $url = $this->base_url . "/{$this->city_id}/" . $this->category;

            $request = Http::withHeaders($headers)->asJson()->post($url, $data)->json();

            $this->filters($request);

        }
    }

    private function filters($arr)
    {
        foreach ($arr['web_widgets']['post_list'] as $post) {
            $uid = $post['data']['action']['payload']['token'];
            $title = $post['data']['title'];
            $price = $post['data']['middle_description_text'];
            $date = $post['data']['bottom_description_text'];
            if (strpos($title, $this->title) !== false) {
                $headers = config('header');
                $headers['authorization'] = 'Basic eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyIjoiMDkzOTc2ODgxNzQiLCJpc3MiOiJhdXRoIiwiaWF0IjoxNjU4NzAxMDg4LCJleHAiOjE2NTk5OTcwODgsInZlcmlmaWVkX3RpbWUiOjE2NTg3MDEwODgsInVzZXItdHlwZSI6InBlcnNvbmFsIiwidXNlci10eXBlLWZhIjoiXHUwNjdlXHUwNjQ2XHUwNjQ0IFx1MDYzNFx1MDYyZVx1MDYzNVx1MDZjYyIsInNpZCI6IjgyM2IwNWMwLTYxMTAtNDBhZC05NmE2LWU0ZWJlZDk2ZTMyYyJ9.CsUlqPnrWO2qTXutUDUoJHUcvHWq6acLX-vakiuakUo';
                $data = Http::withHeaders($headers)->get('https://api.divar.ir/v5/posts/' . $uid . '/contact/')->json();
                $phone = $data['widgets']['contact']['phone'];
                Result::create([
                    'scrap_id' => $this->scrap_id,
                    'title' => $title,
                    'date' => $date,
                    'price' => $price,
                    'phone'=>$phone
                ]);
            }
            $this->last_page = $post['action_log']['server_side_info']['info']['extra_data']['last_post_sort_date'];
        }

    }


}
