<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\GithubAL;
use App\Models\Perm;
use App\Models\PermLog;
use GitHub;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use File;
use Cache;

class FetchPermCommits implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $type;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($type = 'perm_objs')
    {
        $this->type = $type;
       // $this->handle();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $jobType = 'domains';
        $this->type = $jobType;
        $jobTypes = [
            'perm_objs' => [
                'repo' => 'Accursedlands-perms',
                'dir' => 'perm_objs'
            ],
            'domains' => [
                'repo' => 'Accursedlands-Domains',
                'dir' => 'player_built'
            ],
            'data' => [
                'repo' => 'Accursedlands-DATA',
                'dir' => '??'
            ]
        ];

        // Setup
        $commits = [];
        $repo = $jobTypes[$jobType]['repo'];
        $dir = $jobTypes[$jobType]['dir'];
        $gitdir = GithubAL::getLocalRepoPath($repo);
        $cacheFile = "commits_{$repo}_{$dir}";
        //Cache::forget($cacheFile);
        $output = Cache::get($cacheFile);
        // If !cached output, fetch all commits via cli
        if (!$output) {
            $process = new Process(['git','log','--follow','--name-status',
                '--format=::START_HEADER::%ncommit:%H%nname:%f%ndate:%ci::END_HEADER::',$dir]);
            $process->setWorkingDirectory($gitdir);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $output = $process->getOutput();
            Cache::forever($cacheFile,$output);
        }
        // Parse through commits
        $commitData = explode('::START_HEADER::',$output);
    //    $output = [];
        foreach($commitData as $commit) {
            if (!strlen($commit))
                continue;
            $parts = explode('::END_HEADER::',$commit);

            $headers = $this->parseCommitHeader($parts[0]);
            $files = $this->parseCommitFiles($parts[1]);

            for($x = 0; $x < sizeof($files); $x++) {
                $file = $files[$x];

                // Only got most recent M (modify)
                if ($file[0] != "M" || ($file[0] == "M" && !isset($commits[$file[1]]))) {
                    switch($file[0]) {
                        case 'A' :  $type='created';
                                    break;
                        case 'D' :  $type='deleted';
                                    break;
                        case 'M' :  $type='modified';
                                    break;
                        case 'R100' :
                            $type='deleted';
                            //$file[1] = $file[1]." to ".$file[2];
                            //dump($file);
                            break;
                        default  :
                                    $type=$file[0];
                                    break;
                    }
                    if (!isset($commits[$file[1]]))
                        $commits[$file[1]] = [];
                    $commits[$file[1]][] = ['commit'=>$headers[1],'date'=>$headers[3],'type'=>$type];
                }
            }
        }
      //  dd($commits);
        // Sort commits into data for insert
        $commitData = [];
        switch($this->type) {
            case 'perm_objs' :

                $perms = Perm::pluck('id','filename')->toArray();
                foreach($commits as $file=>$fileCommits) {
                    $splitFile = explode("/",$file);
                    $splitFile = $splitFile[sizeof($splitFile)-1];

                   // dd($splitFile);
                    if (isset($perms[$splitFile])) {
                        foreach($fileCommits as $c) {
                            $commitData[] = [
                                'perm_id'=>$perms[$splitFile],
                                'commit'=>$c['commit'],
                                'commit_date'=>\Carbon\Carbon::parse($c['date'])->toDateTimeString(),
                                'type'=>$c['type'],
                                'repo'=>$repo,
                                'file'=>$file
                            ];
                        }
                    }
                }
                break;
            case 'domains':
                $permFilenames = Perm::pluck('id','filename')->toArray();
                $cleanFilenames = Perm::pluck('id','clean_filename')->toArray();
                $mapDirNames = Perm::pluck('id','map_dir')->toArray();
                $checked = [];
                foreach($commits as $file=>$fileCommits) {
                    // path like player_built/data/_domains_wild_virtual_server_613_1336_0:48026/_domains_wild_virtual_server_613_1336_0:48026.map
                    $splitFile = explode("/",$file);
                    $splitFile = $splitFile[sizeof($splitFile)-2];

                    $found = null;
                    if (isset($permFilenames[$splitFile]))
                        $found = $permFilenames[$splitFile];
                    else {

                        if(isset($cleanFilenames[$splitFile]))
                            $found = $cleanFilenames[$splitFile];
                        else {
                            $path = "Accursedlands-Domains/".implode("/",array_slice(explode("/",$file),0,-1));
                            if (isset($mapDirNames[$path]))
                                $found = $mapDirNames[$path];
                        }
                    }

//                    dd($found);
                    if ($found) {
                        foreach($fileCommits as $c) {
                            $checker = $c['commit'].implode("/",array_slice(explode("/",$file),0,-1));
                            if (in_array($checker,$checked) !== false) // match on map file and city_server otherwise
                                continue;
                            $checked[] = $checker;

                            $commitData[] = [
                                'perm_id'=>$found,
                                'commit'=>$c['commit'],
                                'commit_date'=>\Carbon\Carbon::parse($c['date'])->toDateTimeString(),
                                'type'=>$c['type'],
                                'repo'=>$repo,
                                'file'=>$file
                            ];
                        }
                        //dd($perms[$splitFile]);
                    }
                    //dd($splitFile);
                }
               // dd($perms);
                break;
        }
//         foreach($commitData as $d) {
//
//             if (strpos($d["file"],"545_1500") !== false)
//                 dump($d);
//         }
//         dd($commitData);
      //  dd('stop before insert');
        // Insert data
        if (sizeof($commitData))
            PermLog::insert($commitData);
    }


    public function parseCommitHeader($fileCommit) {
        preg_match("/commit:(.*)\nname:(.*)\ndate:(.*)/",$fileCommit,$matches);
        return $matches;
    }

    public function parseCommitFiles($commit) {
        $tmp = [];
        foreach(explode("\n",$commit) as $line) {
            if (strlen($line)) {
                $line = explode("\t",$line);
                if (sizeof($line) > 1) {
                    switch($this->type) {
                        case 'perm_objs':
                            if (strpos(str_replace("perm_objs/","",$line[1]),"/") === false)
                                $tmp[] = $line;
                            break;
                        case 'domains':
                            if (strpos($line[1],"player_built/base/") === false && strpos($line[1],'/bak') === false)
                                $tmp[] = $line;
                            break;
                        default :
                            $tmp[] = $line;
                            break;
                    }
                }
            }
        }
        return $tmp;
    }
}
