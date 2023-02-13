<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facade;

class MapController extends Controller
{
    public function index()
    {
        return view('map.index',['facades'=>Facade::all()]);
    }
}
