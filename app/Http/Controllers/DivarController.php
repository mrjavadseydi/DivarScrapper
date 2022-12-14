<?php

namespace App\Http\Controllers;

use App\Jobs\ScrapeDivarJob;
use App\Models\Result;
use App\Models\Scrap;
use Illuminate\Http\Request;
use Spatie\SimpleExcel\SimpleExcelWriter;

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
            'tokens'=>'required'
        ]);
        $scrap_id = Scrap::query()->create([
            'title'=>$request->title,
            'city'=>$request->city,
            'category'=>$request->category,
            'user_id'=>auth()->id(),
            'batch'=>'empty'
        ]);
        $scrap_id = $scrap_id->id;

        ScrapeDivarJob::dispatch($request->category,$request->page_limit, $request->title, $request->city,$scrap_id,$request->tokens,auth()->id());
        return redirect()->route('result');

    }

    public function result()
    {
        $scrapes = Scrap::orderBy('id', 'desc')->paginate(10);
        return view('result',compact('scrapes'));

    }

    public function download($id)
    {
        $results = Result::where('scrap_id',$id)->get()->toArray();

        $new_array = [];
        foreach ($results as $result) {
            $new_array[] = [
                'عنوان اگهی' => $result['title'],
                'توضیحات' => $result['description'],
                'مبلغ' => $result['price'],
                'تاریخ' => $result['date'],
                'تلفن' => $result['phone'],
            ];
        }
       SimpleExcelWriter::streamDownload('result.csv')
            ->addRows($new_array)
           ->toBrowser();;
//        return redirect()->back();
    }
}
