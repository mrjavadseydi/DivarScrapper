<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DivarController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }
    public function scrap(){
        return view('scrap');
    }
    public function result(){

    }
    public function download($id){

    }
}
