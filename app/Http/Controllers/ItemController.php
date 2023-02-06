<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\DataTables\ItemsDataTable;

class ItemController extends Controller
{
    public function index(ItemsDataTable $dataTable) {
        return $dataTable->render('items.index');
    }
}
