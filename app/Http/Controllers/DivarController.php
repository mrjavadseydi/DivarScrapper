<?php

namespace App\Http\Controllers;

use App\Jobs\ScrapeDivarJob;
use App\Models\Scrap;
use Illuminate\Http\Request;

class DivarController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    public function scrap(Request $request)
    {

        $request->validate([
            'category' => 'required',
            'page_limit' => 'required|numeric',
            'title' => 'required',
            'city' => 'required',
        ]);
        $scrap_id = Scrap::query()->create([
            'title'=>$request->title,
            'city'=>$request->city,
            'category'=>$request->category,
        ]);
        $scrap_id = $scrap_id->id;

        ScrapeDivarJob::dispatch($request->category,$request->page_limit, $request->title, $request->city,$scrap_id);
//        return redirect()->back();

    }

    public function result()
    {

    }

    public function download($id)
    {

    }
}
