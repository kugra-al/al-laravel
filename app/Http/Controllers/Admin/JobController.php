<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Jobs\FetchGithubRepo;
use App\Jobs\ReadItmFileToCache;
use App\Jobs\ReadItmFiles;

class JobController extends Controller
{
    public function index()
    {
        // Don't do this, we know what the jobs will be
        $jobs_path = public_path()."/../app/Jobs/";
        $jobs = ['fetch-item-files','read-itm-file','read-all-itm-files'];
        return view('admin.jobs',["jobs"=>$jobs]);
    }

    public function runJob(Request $request) {
        $job = $request->job;
        $status = [];
        switch($job) {
            case "fetch-item-files" :
                $repo = "Amirani-al/Accursedlands-obj";
                FetchGithubRepo::dispatch($repo);
                $status = ["success"=>"running job: $job"];
                break;
            case 'read-itm-file':
                $file = "items/fetishes/bear_fang_necklace.itm";
                ReadItmFileToCache::dispatch($file);
                break;
            case 'read-all-itm-files':
                ReadItmFiles::dispatch();
                break;

            default :
                $status = ["errors"=>"unknown job: $job"];
        }
        return json_encode($status);
    }
}
