<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Jobs\FetchGithubRepo;
use App\Jobs\ReadItmFileToCache;
use App\Jobs\ReadItmFiles;
use App\Jobs\WriteItmCacheToDatabase;
use App\Jobs\ResetItemsTable;
use App\Jobs\WriteFacadesToDb;
use App\Jobs\WriteDeathsToDb;
use App\Jobs\FetchDeathLogs;
use App\Jobs\WritePermsToDb;
use App\Jobs\WritePermItemsToDb;
use App\Models\GithubAL;

class JobController extends Controller
{
    public function index()
    {
        // Need to work out a better way to do this. This is silly
        $jobs_path = public_path()."/../app/Jobs/";
        $jobs = [
            // items
            'fetch-item-files'=>[
                'desc'=>'Fetch all item files from <a href="https://github.com/Amirani-al/Accursedlands-obj" target="_blank">Amirani-al/Accursedlands-obj</a>',
                'time'=>'<10 seconds','type'=>'items'],
            'read-all-itm-files'=>[
                'desc'=>'Read through all locally stored .itm files and write to cache',
                'time'=>'~80 seconds','type'=>'items',
                'sources'=>['repos'=>'Accursedlands-obj']
            ],
            'reset-items-table'=>[
                'desc'=>'Resets all columns and values in items table.  Run before `write-items-to-db` to reset columns and data',
                'time'=>'<10 seconds','type'=>'items'],
            'write-itms-to-db'=>[
                'desc'=>'Write all cached .itm files to database','time'=>'~20 seconds','type'=>'items'],
            // domains
            'fetch-domain-files'=>[
                'desc'=>'Fetch all lib files from <a href="https://github.com/Amirani-al/Accursedlands-Domains" target="_blank">Amirani-al/Accursedlands-Domains</a> on branch production_mud_fluffos','time'=>'<10 seconds','type'=>'domains'
            ],
            // map
            'write-facades-to-db'=>[
                'desc'=>'Read all facade files from /domains/wild/virtual/facades/ and write to db','time'=>'???','type'=>'map',
                'sources'=>['repos'=>'Accursedlands-Domains','branch'=>'production_mud_fluffos']
            ],
            // deaths
            'write-deaths-to-db'=>['desc'=>'Read any new deaths from death log and write to db','time'=>'???','type'=>'deaths','sources'=>['jobs'=>'fetch-death-logs']],
            'fetch-death-logs'=>['desc'=>'Fetch death logs','time'=>'<10s','type'=>'deaths'],
            // perms
            'fetch-perm-files'=>['desc'=>'Fetch all perms files from <a href="https://github.com/Amirani-al/Accursedlands-perms" target="_blank">Amirani-al/Accursedlands-perms</a> on branch production_mud_fluffos','time'=>'<10 seconds','type'=>'perms'],
            'fetch-data-files'=>['desc'=>'Fetch all data files from <a href="https://github.com/Amirani-al/Accursedlands-DATA" target="_blank">Amirani-al/Accursedlands-DATA</a> on branch production_mud_fluffos','time'=>'>60 seconds','type'=>'perms'],
            'write-perms-to-db'=>['desc'=>'Read any perms and write to db','time'=>'???','type'=>'perms','sources'=>['repos'=>'Accursedlands-perms','branch'=>'production_mud_fluffos']],
            'write-perm-items-to-db'=>['desc'=>'Read any perm items and write to db','time'=>'~20s','type'=>'perms'],

            'fetch-repo'=>['desc'=>'alala','time'=>'10s','type'=>'none',
                'repos'=>GithubAL::getALRepos(),
                'branches'=>GithubAL::getALRepoBranches(),
            ],

        ];
        return view('admin.jobs',["jobs"=>$jobs]);
    }

    public function runJob(Request $request) {
        $job = $request->job;
        $status = ['status'=>'Running job in background: '.$job];
        switch($job) {
            // items
            case "fetch-item-files" :
                $repo = "Amirani-al/Accursedlands-obj";
                FetchGithubRepo::dispatch($repo);
                break;
            case 'reset-items-table' :
                ResetItemsTable::dispatch();
                break;
            case 'read-all-itm-files':
                ReadItmFiles::dispatch();
                break;
            case 'write-itms-to-db':
                WriteItmCacheToDatabase::dispatch();
                break;

            case 'fetch-domain-files' :
                $repo = "Amirani-al/Accursedlands-Domains";
                FetchGithubRepo::dispatch($repo,"production_mud_fluffos");
                break;
            case 'write-facades-to-db' :
                WriteFacadesToDb::dispatch();
                break;

            case 'write-deaths-to-db' :
                WriteDeathsToDb::dispatch();
                break;
            case 'fetch-death-logs' :
                FetchDeathLogs::dispatch(false);
                break;

            case 'fetch-perm-files' :
                $repo = "Amirani-al/Accursedlands-perms";
                FetchGithubRepo::dispatch($repo,"production_mud_fluffos");
                break;
            case 'fetch-data-files' :
                $repo = "Amirani-al/Accursedlands-DATA";
                FetchGithubRepo::dispatch($repo,"production_mud_fluffos");
                break;
            case 'write-perms-to-db' :
                WritePermsToDb::dispatch();
                break;
            case 'write-perm-items-to-db':
                WritePermItemsToDb::dispatch();
                break;

            default :
                $status = ["error"=>"unknown job: $job"];
        }
        return \Redirect::to('/admin/jobs')->with($status);
    }
}
