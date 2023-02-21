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
        if(request()->ajax())
            return $this->ajaxIndex();

        $perms = Perm::select('filename','location','object','x','y','z','lastseen','touched_by','sign_title','last_touched','psets','id','short','destroyed','perm_type')->whereNotNull('x')->get();

        return view('map.index',['facades'=>Facade::all(),'deaths'=>Death::whereNotNull('x')->get(), 'perms'=>$perms]);
    }

    public function ajaxIndex() {
        $layerCollection = MapLayer::with('user')->get();
        $layers = [
            'my layers'=>$layerCollection->where('user_id',Auth::user()->id),
            'other layers'=>$layerCollection->where('user_id','!=',Auth::user()->id)
        ];
        return response()->json(["html"=>view('map.layers.index',['layers'=>$layers])->render()]);
    }

    public function edit($id)
    {
        $layer = MapLayer::find((int)$id);
        return response()->json(["html"=>view('map.layers.edit',['layer'=>$layer])->render()]);
    }

    public function create()
    {
        if (request()->get('id'))
            return $this->edit(request()->get('id'));
        $name = request()->get('name');
        return response()->json(["html"=>view('map.layers.create',['name'=>$name])->render()]);
    }

    // need checks
    public function store()
    {
        $name = request()->get('name');
        $layer = request()->get('layer');
        $existing = new MapLayer;
        $existing->user_id = Auth::user()->id;
        $existing->name = $name;
        $existing->data = $layer;
        $existing->save();
        return response()->json(['name'=>$name,'layer'=>$layer]);
    }

    public function update() {
        $id = (int)request()->get('id');
        $layer = MapLayer::find($id);
        if ($layer) {
            if ($layer->user_id == Auth::user()->id) {
                $layer->name = request()->get('name');
                $layer->data = request()->get('layer');
                $layer->save();
                return response()->json(['id'=>$id],200);
            }
            return response()->json(['id'=>$id],403);
        }
        return response()->json(['id'=>$id,'layer'=>$layer],404);
    }


    public function destroy($id)
    {
        $layer = MapLayer::find($id);
        if ($layer->user_id == Auth::user()->id) {
            $layer->delete();
            return response()->json(['id'=>$id],200);
        }
        return response()->json(['id'=>$id],403);
    }

    public function show($id) {
        $layer = MapLayer::find($id);
        if ($layer->user_id != Auth::user()->id)
            unset($layer->id);
        return response()->json($layer,200);
    }
}
