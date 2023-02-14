<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use App\Models\GithubAL;
use GitHub;

class FetchDeathLogs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $logs;
    public $fetchAll = false;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($fetchAll)
    {
        $this->fetchAll = $fetchAll;
        $this->logs = [
            [
                'owner'=>'Amirani-al',
                'repo'=>'Accursedlands-LOGS',
                'branch'=>'master',
                'file'=>'admin/PreVUH01MUD01/DEATHS.0',
                'local'=>GithubAL::getDeathLogDir()."old/deaths-1998-2000.log"
            ],
            [
                'owner'=>'Amirani-al',
                'repo'=>'Accursedlands-LOGS',
                'branch'=>'master',
                'file'=>'admin/PreVUH01MUD01/DEATHS.1',
                'local'=>GithubAL::getDeathLogDir()."old/deaths-2000-2003.log"
            ],
            [
                'owner'=>'Amirani-al',
                'repo'=>'Accursedlands-LOGS',
                'branch'=>'master',
                'file'=>'admin/PreVUH01MUD01/DEATHS.2',
                'local'=>GithubAL::getDeathLogDir()."old/deaths-2003-2011.log"
            ],
            [
                'owner'=>'Amirani-al',
                'repo'=>'Accursedlands-LOGS',
                'branch'=>'production_mud_fluffos',
                'file'=>'admin/DEATHS',
                'current'=>true,
                'local'=>GithubAL::getDeathLogDir()."deaths.log"
            ],
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $path = GithubAL::getDeathLogDir()."old/";
        if (!File::exists($path))
            !File::makeDirectory($path, $mode = 0775, true, true);

        foreach($this->logs as $log) {
            if (!File::exists($log['local']) || $this->fetchAll || isset($log["current"])) {
                $branch = GitHub::repo()->branches($log['owner'],$log['repo'],$log['branch']);
                if ($branch && isset($branch["commit"]["sha"])) {
                    $branchSHA = $branch["commit"]["sha"];
                    $contents = GitHub::repo()->contents()->show($log['owner'],$log['repo'],$log["file"],$branchSHA);
                    if ($contents && isset($contents["content"])) {
                        $contents = base64_decode($contents["content"]);
                        File::put($log["local"],$contents);
                        \Log::info("wrote {$log['local']} to file");
                    }
                }
            }
        }
    }
}
