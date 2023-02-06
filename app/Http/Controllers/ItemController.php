<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Response;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use App\DataTables\ItemsDataTable;

class ItemController extends Controller
{
    public function index(ItemsDataTable $dataTable) {
        return $dataTable->render('items.index');
    }

    public function loadItmFile(Request $request) {
        $file = $request->get('file');
        $filepath = storage_path()."/private/git/Accursedlands-obj/".str_replace('/obj/','',$file);
        if (!File::exists($filepath)) {
            throw new FileNotFoundException($file);
        } else {
            return Response::json(File::get($filepath));
        }
    }
}
