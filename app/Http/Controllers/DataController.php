<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Death;
use App\Models\Facade;
use App\Models\PermItem;
use DataTables;
use App\Models\GithubAL;
use App\Models\PermLog;
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
            case 'perm_items':
                $model = PermItem::select('id','object','perm_id','pathname','short','touched_by')->get();
                break;
            case 'perm_logs':
                $model = PermLog::get();
                break;
            default :
                return back()->with('warning',"Unknown data type: {$type}");
        }
        if (request()->ajax()) {

            $dataTable = DataTables::of($model);
            if ($type == 'perm_items')
                $dataTable->addColumn('action','data.buttons.view');
            return $dataTable->toJson();
        }
        $dataTable = null;
        if ($model->count()) {
            $keys = array_keys($model->first()->toArray());
            $columns = [];
            foreach($keys as $key) {
                $columns[] = ['data' => $key];
            }
            if ($type == 'perm_items')
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
                case "perm_items" :
                    $data = PermItem::find((int)request()->get('id'));
                    break;
                default :
                    $data = ["error"=>"unknown type"];
                    break;
            }
        }
        return json_encode($data);
    }
}
