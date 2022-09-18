<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScrapePost implements ShouldQueue
{
    use Batchable,Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tokens, $uid, $scrap_id, $title, $date, $price, $description, $filters,$batch_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tokens, $uid, $scrape_id, $title, $date, $price, $filters,$batch_id)
    {
        $this->tokens = $tokens;
        $this->uid = $uid;
        $this->scrap_id = $scrape_id;
        $this->title = $title;
        $this->date = $date;
        $this->price = $price;
        $this->filters = $filters;
        $this->batch_id = $batch_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $description_request = Http::withHeaders(config('header'))->get("https://api.divar.ir/v8/posts-v2/web/" . $this->uid)->json();
        $description = '';
        if (!isset($description_request['sections'])) {
            Log::alert($description_request);
        } else {

            foreach ($description_request['sections'] as $section) {
                if ($section['section_name'] == "DESCRIPTION") {
                    foreach ($section['widgets'] as $widget) {
                        if (isset($widget['data']['text'])) {
                            $description = $widget['data']['text'];
                        }
                    }
                }
            }
            if ($this->searchIn($description) !== false) {
                Bus::findBatch($this->batch_id)->add([
                    (new ScrapeContact($this->tokens, $this->uid, $this->scrap_id, $this->title, $this->date, $this->price, $description))->delay(now()->addSeconds(5 * rand(10, 30)))
                ]);
            }
        }

    }

    function searchIn($text)
    {
        foreach ($this->filters as $title) {
            if (strpos($text, $title) !== false) {
                return true;
            }
        }
        return false;
    }
}
