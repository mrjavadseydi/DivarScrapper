<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ScrapePage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $url,$data, $headers, $tokens, $scrap_id, $user_id,$delay,$filters;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($url, $data, $headers, $tokens, $user_id, $scrap_id,$filters)
    {
        $this->url = $url;
        $this->data = $data;
        $this->headers = $headers;
        $this->tokens = $tokens;
        $this->user_id = $user_id;
        $this->scrap_id = $scrap_id;
        $this->filters = $filters;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $request = Http::withHeaders($this->headers)->asJson()->post($this->url, $this->data)->json();
        FilterPage::dispatch($request, $this->tokens, $this->user_id, $this->scrap_id,$this->filters);

    }
}
