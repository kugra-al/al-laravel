<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facade;
use App\Models\Death;
use App\Models\Perm;
use App\Models\GithubAL;
use Cache;

class MapController extends Controller
{
    public function index()
    {
        $perms = Perm::select('filename','location','object','x','y','z','lastseen','touched_by','sign_title','last_touched','psets','id','short')->whereNotNull('x')->get();

        return view('map.index',['facades'=>Facade::all(),'deaths'=>Death::whereNotNull('x')->get(), 'perms'=>$perms]);
    }
}
