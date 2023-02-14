<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Death;
use App\Models\Facade;

class DataController extends Controller
{
    public function index($type)
    {
        $data = [];
        switch($type) {
            case 'deaths' :
                $data = Death::orderBy('event_date','desc')->select('event_date','player','cause','location','x','y','z')->paginate(20);
                break;
            case 'facades' :
                $data = Facade::paginate(20);
                break;
            default :
                return back()->with('warning','Unknown data type');
        }
        return view('data.index',['data'=>$data,'type'=>$type]);
    }
}
