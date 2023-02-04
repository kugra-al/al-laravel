<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class FetchGithubFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $repo, $directory, $extensions, $skipdirs;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($repo, $directory, $extensions, $skipdirs)
    {
        $this->repo = $repo;
        $this->directory = $directory;
        $this->extensions = $extensions;
        $this->skipdirs = $skipdirs;

        $process = new Process(['git clone ', '--with-token '.env('GITHUB_TOKEN'), $repo]);
        $process->setWorkingDirectory(storage_path()."/private/git/");
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
