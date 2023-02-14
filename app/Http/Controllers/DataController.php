<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Death;
use App\Models\Facade;
use DataTables;
use Yajra\DataTables\Html\Builder;

class DataController extends Controller
{
    public function index(Builder $builder, $type)
    {
        $model = [];
        switch($type) {
            case 'deaths' :
                $model = Death::orderBy('event_date','desc')->select('event_date','player','cause','location','x','y','z')->get();
                break;
            case 'facades' :
                $model = Facade::get();
                break;
            default :
                return back()->with('warning','Unknown data type');
        }
        if (request()->ajax()) {
            return DataTables::of($model)->toJson();
        }
        if ($model) {
            $keys = array_keys($model->first()->toArray());
            $columns = [];
            foreach($keys as $key) {
                $columns[] = ['data' => $key];
            }
            $dataTable = $builder->columns($columns)->parameters(['buttons'=>[],'search'=>['return'=>true]]);
        }
        return view('data.index',['type'=>$type, "dataTable"=>$dataTable]);
    }
}
