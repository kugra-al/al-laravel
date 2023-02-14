<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facade;
use App\Models\Deaths;

class MapController extends Controller
{
    public function index()
    {
        return view('map.index',['facades'=>Facade::all(),'deaths'=>Deaths::all()]);
    }
}
