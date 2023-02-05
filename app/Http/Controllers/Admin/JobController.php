<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Jobs\FetchGithubFiles;

class JobController extends Controller
{
    public function index()
    {
        // Don't do this, we know what the jobs will be
        $jobs_path = public_path()."/../app/Jobs/";
        $jobs = ['fetch-item-files'];
        return view('admin.jobs',["jobs"=>$jobs]);
    }

    public function runJob(Request $request) {
        $job = $request->job;
        $status = [];
        switch($job) {
            case "fetch-item-files" :
                $repo = "Amirani-al/Accursedlands-obj";
                $directory = "obj/items/";
                $extensions = [".itm"];
                $skipdirs = ["bak","old"];
                FetchGithubFiles::dispatch($repo, $directory, $extensions, $skipdirs);
                $status = ["success"=>"running job: $job"];
                break;
            default :
                $status = ["errors"=>"unknown job: $job"];
        }
        return json_encode($status);
    }
}