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

class ScrapeContact implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tokens,$uid,$scrap_id,$title,$date,$price,$description;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tokens,$uid,$scrape_id,$title,$date,$price,$description)
    {
        $this->tokens = $tokens;
        $this->uid = $uid;
        $this->scrape_id = $scrape_id;
        $this->title = $title;
        $this->date = $date;
        $this->price = $price;
        $this->description = $description;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $headers = config('header');
        $headers['authorization'] =$this->tokens[array_rand($this->tokens)];
        $data = Http::withHeaders($headers)->get('https://api.divar.ir/v5/posts/' . $this->uid . '/contact/')->json();
        $phone = $data['widgets']['contact']['phone'];
        Result::create([
            'scrap_id' => $this->scrap_id,
            'title' => $this->title,
            'date' => $this->date,
            'price' => $this->price,
            'phone'=>$phone,
            'description'=>$this->description,
        ]);
    }
}
