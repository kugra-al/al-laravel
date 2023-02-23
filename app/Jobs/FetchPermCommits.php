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
        $this->handle();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $jobType = 'perm_objs';
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
        $output = [];
        foreach($commitData as $commit) {
            if (!strlen($commit))
                continue;
            $parts = explode('::END_HEADER::',$commit);

            $headers = $this->parseCommitHeader($parts[0]);
            $files = $this->parseCommitFiles($parts[1]);
            for($x = 0; $x < sizeof($files); $x++) {
                $file = $files[$x];
                // Only get A or D (add or delete) and most recent
                if ($file[0] == 'A' || $file[0] == 'D' || !isset($commits[$file[1]])) {
                    switch($file[0]) {
                        case 'A' :  $type='created';
                                    break;
                        case 'D' :  $type='deleted';
                                    break;
                        case 'M' :  $type='modified';
                                    break;
                        default  :  $type=$file[0];
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
                // This needs to check map dir
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
                $perms = Perm::pluck('id','filename','map_dir')->toArray();
                foreach($commits as $file=>$fileCommits) {
                    // path like player_built/data/_domains_wild_virtual_server_613_1336_0:48026/_domains_wild_virtual_server_613_1336_0:48026.map
                    $splitFile = explode("/",$file);
                    if ($splitFile[sizeof($splitFile)-1] != "city_server.c") // match on map file and city_server otherwise
                        continue;
                    $splitFile = $splitFile[sizeof($splitFile)-2];

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
                        //dd($perms[$splitFile]);
                    }
                    //dd($splitFile);
                }
               // dd($perms);
                break;
        }
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
                if (sizeof($line) == 2) {
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
