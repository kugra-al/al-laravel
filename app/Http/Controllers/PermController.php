<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perm;
use App\Models\GithubAL;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use App\DataTables\PermsDataTable;

class PermController extends Controller
{
    public function index(PermsDataTable $dataTable) {
        return $dataTable->render('perms.index');
    }

    public function loadData() {
        $data = Perm::with('items')->find((int)request()->get('id'));
        return json_encode($data);
    }
}
