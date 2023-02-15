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
        $cacheKey = 'cached-perms-for-map';
        $perms = Cache::get($cacheKey);
        if (!$perms) {
            $perms = Perm::select('filename','location','object','x','y','z','lastseen','data')->whereNotNull('x')->get();
            foreach($perms as $perm) {
                if ($perm->object == "/obj/base/misc/signpost") {
                    $data = GithubAL::convertDataFromPermToJson($perm["data"]);
                    foreach($data as $d) {
                        if (isset($d["sign_title"]))
                            $perm->sign_title = $d["sign_title"];
                    }
                } else {
                    unset($perm->data);
                }
            }
            Cache::forever($cacheKey,$perms);
        }
       // dd($perms);
        return view('map.index',['facades'=>Facade::all(),'deaths'=>Death::whereNotNull('x')->get(), 'perms'=>$perms]);
    }
}
