<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facade;
use App\Models\Death;
use App\Models\Perm;

class MapController extends Controller
{
    public function index()
    {
        $perms = Perm::select('filename','location','object','x','y','z','lastseen')->whereNotNull('x')->get();
        return view('map.index',['facades'=>Facade::all(),'deaths'=>Death::whereNotNull('x')->get(), 'perms'=>$perms]);
    }
}
