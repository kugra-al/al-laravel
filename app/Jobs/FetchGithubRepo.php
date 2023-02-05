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

class FetchGithubRepo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $repo;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($repo)
    {
        $this->repo = $repo;
        $gitdir = storage_path()."/private/git/";
       // dd($gitdir.explode("/",$repo)[1]);
        if (File::exists($gitdir.explode("/",$repo)[1])) {
            $gitdir = $gitdir.explode("/",$repo)[1];
            $process = new Process(['git', 'pull', 'https://'.env('GITHUB_TOKEN').'@github.com/'.$repo.'.git']);
        } else {
            // Each arg needs to be in seperate element or this don't work - https://stackoverflow.com/a/65290996
            $process = new Process(['git', 'clone', 'https://'.env('GITHUB_TOKEN').'@github.com/'.$repo.'.git']);
        }
        $process->setWorkingDirectory($gitdir);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        dd( $process->getOutput() );
        dd($process);
        dd($this);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }
}
