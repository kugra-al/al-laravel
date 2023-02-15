<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;

class FetchGithubRepo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $repo;
    public $branch;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($repo, $branch = "master")
    {
        $this->repo = $repo;
        $this->branch = $branch;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // This little maneuver saves us 100s of lines vs using the api.
        $gitdir = storage_path()."/private/git/";
        if (File::exists($gitdir.explode("/",$this->repo)[1])) {
            $gitdir = $gitdir.explode("/",$this->repo)[1];
            $process = new Process(['git', 'pull', '--rebase', '--autostash', 'https://'.env('GITHUB_TOKEN').'@github.com/'.$this->repo.'.git', $this->branch]);
        } else {
            $process = new Process(['git', 'clone', '-b', $this->branch, 'https://'.env('GITHUB_TOKEN').'@github.com/'.$this->repo.'.git']);
        }
        $process->setWorkingDirectory($gitdir);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        Log::info("Fetched latest version of: {$this->repo} on branch: {$this->branch}");
        $output = $process->getOutput();
        if ($output && strlen($output))
            Log::info($process->getOutput());
    }
}
