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
        $jobTypes = [
            'perm_objs' => [
                'repo' => 'Accursedlands-perms',
                'dirs' => 'perm_objs'
            ],
            'domains' => [
                'repo' => 'Accursedlands-domains',
                'dirs' => 'player_built'
            ],
            'data' => [
                'repo' => 'Accursedlands-DATA',
                'dirs' => '??'
            ]
        ];
        $perms = Perm::select('id','filename')->get();
        $logs = [];

        $commits = [];
        $gitdir = GithubAL::getLocalRepoPath("Accursedlands-perms");
        $tmpFile = storage_path()."/private/perm_commits.log";
        if (!File::exists($tmpFile)) {
            $process = new Process(['git','log','--follow','--name-status',
                '--format=::START_HEADER::%ncommit:%H%nname:%f%ndate:%ci::END_HEADER::','perm_objs/']);
            $process->setWorkingDirectory($gitdir);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $output = $process->getOutput();
            File::put($tmpFile,$output);
        }
        $output = File::get($tmpFile);
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
        $commitData = [];
        $perms = Perm::pluck('id','filename')->toArray();
        foreach($commits as $file=>$fileCommits) {
            if (isset($perms[$file])) {
                foreach($fileCommits as $c) {
                    $commitData[] = [
                        'perm_id'=>$perms[$file],
                        'commit'=>$c['commit'],
                        'commit_date'=>\Carbon\Carbon::parse($c['date'])->toDateTimeString(),
                        'type'=>$c['type']
                    ];
                }
            }
        }
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
                    $line[1] = str_replace("perm_objs/","",$line[1]);
                    if (strpos($line[1],"/") === false)
                        $tmp[] = $line;
                }
            }
        }
        return $tmp;
    }
}
