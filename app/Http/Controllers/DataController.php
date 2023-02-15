<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Death;
use App\Models\Facade;
use App\Models\Perm;
use DataTables;
use App\Models\GithubAL;
use Yajra\DataTables\Html\Builder;

class DataController extends Controller
{
    public function index(Builder $builder, $type)
    {
        $model = [];
        switch($type) {
            case 'deaths' :
                $model = Death::select('id','event_date','player','cause','location','x','y','z')->get();
                break;
            case 'facades' :
                $model = Facade::get();
                break;
            case 'perms' :
                $model = Perm::select('id','filename','location','object','x','y','z','lastseen')->get();
                break;
            default :
                return back()->with('warning','Unknown data type');
        }
        if (request()->ajax()) {

            $dataTable = DataTables::of($model);
            if ($type == 'perms')
                $dataTable->addColumn('action','data.buttons.perm');

            return $dataTable->toJson();
        }
        if ($model) {
            $keys = array_keys($model->first()->toArray());
            $columns = [];
            foreach($keys as $key) {
                $columns[] = ['data' => $key];
            }
            if ($type == 'perms')
                $columns[] = ['data' => 'action'];
            $dataTable = $builder
                ->columns($columns)
                ->parameters([
                    'buttons'=>[],
                    'search'=>['return'=>true],
                    'initComplete' => "function() {
                            if (typeof postInitFuncs == 'function')
                                postInitFuncs();
                        }",
            ]);
        }
        return view('data.index',['type'=>$type, "dataTable"=>$dataTable]);
    }

    public function loadData($type) {
        $data = [];
        if ($type && request()->get('id')) {
            switch($type) {
                case "perms" :
                    $data = Perm::find((int)request()->get('id'));
                    if ($data["data"]) {
                        $tmp = explode("\n",$data["data"]);
                        $object = $tmp[0];
                        unset($tmp[0]);
                        foreach($tmp as $k=>$t) {
                            if (str_ends_with($t,$object))
                                $t = substr($t,0,(0-strlen($object)));
                            $tmp[$k] = GithubAL::convertLPCDataToJson($t,false);
                        }
                        //dd($tmp);
                        $data["data"] = $tmp;
                    }
                    break;
                default :
                    $data = ["error"=>"unknown type"];
                    break;
            }
        }
        return json_encode($data);
    }
}
