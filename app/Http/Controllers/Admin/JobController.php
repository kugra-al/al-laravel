<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class JobController extends Controller
{
    public function index()
    {
        $jobs_path = public_path()."/../app/Jobs/";
        $jobs = [];
        foreach(File::allFiles($jobs_path) as $job) {
            $jobs[] = basename($job);
        }
        return view('admin.jobs',["jobs"=>$jobs]);
    }

    public function runJob() {
        return "ok, running job";
    }
}
