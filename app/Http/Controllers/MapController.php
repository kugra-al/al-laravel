<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facade;
use App\Models\Death;
use App\Models\Perm;
use App\Models\GithubAL;
use App\Models\MapLayer;
use Illuminate\Support\Facades\Auth;
use Cache;

class MapController extends Controller
{
    public function index()
    {
        $perms = Perm::select('filename','location','object','x','y','z','lastseen','touched_by','sign_title','last_touched','psets','id','short','destroyed','perm_type')->whereNotNull('x')->get();

        return view('map.index',['facades'=>Facade::all(),'deaths'=>Death::whereNotNull('x')->get(), 'perms'=>$perms]);
    }

    public function modal($type)
    {
        switch($type) {
            case "load" :
                $layers = MapLayer::where('user_id',Auth::user()->id)->get();
                return response()->json(["html"=>view('map.modals.load',['layers'=>$layers])->render()]);
            case "save" :
                $layers = MapLayer::where('user_id',Auth::user()->id)->get();
                return response()->json(["html"=>view('map.modals.save',['layers'=>$layers])->render()]);
            default:
                return response()->json([]);
        }
    }

    public function saveLayer() {
        $name = request()->get('name');
        $layer = request()->get('layer');
        $existing = MapLayer::where('user_id',Auth::user()->id)->where('name',$name)->first();
        if (!$existing) {
            $existing = new MapLayer;
            $existing->user_id = Auth::user()->id;
            $existing->name = $name;
        }
        $existing->data = $layer;
        $existing->save();
        return response()->json(['name'=>$name,'layer'=>$layer]);
    }

    public function loadLayer() {
        $id = request()->get('id');
        return response()->json(MapLayer::find($id));
    }
}
