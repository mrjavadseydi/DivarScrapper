<?php

namespace App\Jobs;

use App\Models\Result;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FilterPage implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $arr,$tokens,$user_id,$scrap_id,$title,$batch_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($arr,$tokens,$user_id,$scrap_id,$title,$batch_id)
    {
        $this->arr = $arr;
        $this->tokens = $tokens;
        $this->user_id = $user_id;
        $this->scrap_id = $scrap_id;
        $this->title = $title;
        $this->batch_id = $batch_id;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $arr = $this->arr;
        if (!isset($arr['web_widgets']['post_list'])){
            Log::alert($arr);
            return;
        }
        foreach ($arr['web_widgets']['post_list'] as $i=> $post) {

            if (!isset($post['data']['action']['payload']['token'])){
                Log::alert($post);
            }
            $uid = $post['data']['action']['payload']['token'];

            $title = $post['data']['title']??'';
            $price = $post['data']['middle_description_text'];
            $date = $post['data']['bottom_description_text'];
            $batch = Bus::findBatch($this->batch_id);
            $batch->add([
               (new ScrapePost($this->tokens,$uid,$this->scrap_id,$title,$date,$price,$this->title,$this->batch_id))->delay(now()->addSeconds(rand(50,100)*$i))
            ]);
            if ($this->searchIn($title) !== false ) {
                $description_request = Http::withHeaders(config('header'))->get("https://api.divar.ir/v8/posts-v2/web/" . $uid)->json();
                $description = '';
                foreach ($description_request['sections'] as $section) {
                    if ($section['section_name'] == "DESCRIPTION") {
                        foreach ($section['widgets'] as $widget) {
                            if (isset($widget['data']['text'])) {
                                $description = $widget['data']['text'];
                            }
                        }
                    }
                }
                $batch->add([
                   (new ScrapeContact($this->tokens,$uid,$this->scrap_id,$title,$date,$price,$description))->delay(now()->addSeconds($i*rand(20,50)))
                ]);
            }
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
