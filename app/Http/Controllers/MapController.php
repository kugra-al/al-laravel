<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facade;
use App\Models\Death;

class MapController extends Controller
{
    public function index()
    {
        return view('map.index',['facades'=>Facade::all(),'deaths'=>Death::whereNotNull('x')->get()]);
    }
}
