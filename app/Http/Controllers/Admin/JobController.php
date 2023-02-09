<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Jobs\FetchGithubRepo;
use App\Jobs\ReadItmFileToCache;
use App\Jobs\ReadItmFiles;
use App\Jobs\WriteItmCacheToDatabase;

class JobController extends Controller
{
    public function index()
    {
        // Don't do this, we know what the jobs will be
        $jobs_path = public_path()."/../app/Jobs/";
        $jobs = [
            'fetch-item-files'=>['desc'=>'Fetch all item files from <a href="https://github.com/Amirani-al/Accursedlands-obj" target="_blank">Amirani-al/Accursedlands-obj</a>','time'=>'<10 seconds'],
            'read-all-itm-files'=>['desc'=>'Read through all locally stored .itm files and write to cache','time'=>'~80 seconds'],
            'write-itms-to-db'=>['desc'=>'Write all cached .itm files to database','time'=>'<10 seconds']
        ];
        return view('admin.jobs',["jobs"=>$jobs]);
    }

    public function runJob(Request $request) {
        $job = $request->job;
        $status = ['status'=>'Running job in background: '.$job];
        switch($job) {
            case "fetch-item-files" :
                $repo = "Amirani-al/Accursedlands-obj";
                FetchGithubRepo::dispatch($repo);
                break;
            case 'read-all-itm-files':
                ReadItmFiles::dispatch();
                break;
            case 'write-itms-to-db':
                WriteItmCacheToDatabase::dispatch();
                break;

            default :
                $status = ["error"=>"unknown job: $job"];
        }
        return \Redirect::to('/admin/jobs')->with($status);
    }
}
