<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\Cache;
class ItemController extends Controller
{
    public function index() {
        $items = Item::paginate(50);
        return view('items.index',["items"=>$items]);
    }
}
