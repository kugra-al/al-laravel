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
use App\Jobs\ResetTable;

class JobController extends Controller
{
    public function index()
    {
        // Need to work out a better way to do this. This is silly
        $jobs_path = public_path()."/../app/Jobs/";
        $jobs = [
            // items
            'read-all-itm-files'=>[
                'desc'=>'Read through all locally stored .itm files and write to cache',
                'time'=>'~80 seconds','type'=>'process','group'=>'items',
                'sources'=>['repos'=>['Accursedlands-obj']]
            ],
            'reset-items-table'=>[
                'desc'=>'Resets all columns and values in items table.  Run before `write-items-to-db` to reset columns and data',
                'time'=>'<10 seconds','type'=>'reset','group'=>'items'],
            'write-itms-to-db'=>[
                'desc'=>'Write all cached .itm files to database','time'=>'~20 seconds','type'=>'write','group'=>'items'],

            // map
            'write-facades-to-db'=>[
                'desc'=>'Read all facade files from /domains/wild/virtual/facades/ and write to db','time'=>'???','type'=>'write',
                'sources'=>['repos'=>['Accursedlands-Domains'],'branch'=>'production_mud_fluffos'],'group'=>'map',
            ],

            // deaths
            'write-deaths-to-db'=>[
                'desc'=>'Read any new deaths from death log and write to db','time'=>'???','type'=>'write',
                'sources'=>['jobs'=>'fetch-death-logs'],'group'=>'deaths',
            ],
            'fetch-death-logs'=>[
                'desc'=>'Fetch death logs','time'=>'<10s','type'=>'fetch',
                'sources'=>['github_api'=>['Accursedlands-LOGS'],'branches'=>['production_mud_fluffos','master']],
                'group'=>'deaths'
            ],

            // Perms
            'write-perms-to-db'=>[
                'desc'=>'Read any perms and write to db','time'=>'???','type'=>'write',
                'sources'=>['repos'=>['Accursedlands-perms','Accursedlands-Domains','Accursedlands-DATA'],'branch'=>'production_mud_fluffos'],
                'group'=>'perms',
            ],
            'write-perm-items-to-db'=>[
                'desc'=>'Read any perm items and write to db','time'=>'~20s','type'=>'write',
                'sources'=>['repos'=>['Accursedlands-perms','Accursedlands-Domains','Accursedlands-DATA'],'branch'=>'production_mud_fluffos'],
                'group'=>'perms',
            ],

            // Multiuse
            'fetch-repo'=>[
                'desc'=>'Fetch selected repo from Amirani-AL/* on selected branch.<br/>Accursedlands-DATA is on a sparse-checkout with /data/permanent_rooms.<br/>Don\'t fetch new repo data unless needed (limited space)',
                'time'=>'~10s','type'=>'fetch',
                'repos'=>GithubAL::getALRepos(),
                'branches'=>GithubAL::getALRepoBranches(),
                'group'=>'git'
            ],
            'reset-table'=>[
                'desc'=>'Resets an AL data table. For items, use job reset-items-table','time'=>'~10s','type'=>'reset',
                'tables'=>['perms','perm_items','facades','deaths'],
                'group'=>'mysql'
            ],

        ];
        return view('admin.jobs',["jobs"=>$jobs]);
    }

    public function runJob(Request $request) {
        $job = $request->job;
        $status = ['status'=>'Running job in background: '.$job];
        switch($job) {
            // items
            case 'reset-items-table' :
                ResetItemsTable::dispatch();
                break;
            case 'read-all-itm-files':
                ReadItmFiles::dispatch();
                break;
            case 'write-itms-to-db':
                WriteItmCacheToDatabase::dispatch();
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

            case 'write-perms-to-db' :
                WritePermsToDb::dispatch();
                break;
            case 'write-perm-items-to-db':
                WritePermItemsToDb::dispatch();
                break;

            case 'fetch-repo':
                $repo = $request->get('repo');
                $branch = $request->get('branch');
                if ($repo && $branch) {
                    if (in_array($repo,GithubAL::getALRepos()) === false) {
                        $status = ['warning'=>"unknown repo: $repo"];
                        break;
                    }
                    if (in_array($branch,GithubAL::getALRepoBranches()) === false) {
                        $status = ['warning'=>"unknown branch: $branch"];
                        break;
                    }
                    FetchGithubRepo::dispatch("Amirani-al/".$repo,$branch);
                    $status = ['status'=>"Fetching: $repo on branch: $branch"];
                } else {
                    $status = ['warning'=>'no repo'];
                }

                break;

            case 'reset-table':
                $table = $request->get('table');
                if ($table) {
                    if (in_array($table,GithubAL::getALTables()) === false) {
                        $status = ['warning'=>"unknown table: $table"];
                        break;
                    }
                    ResetTable::dispatch($request->get('table'));
                }
                break;
            default :
                $status = ["warning"=>"unknown job: $job"];
                break;
        }
        return back()->with($status);
    }
}
