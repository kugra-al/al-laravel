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
use App\Jobs\WriteFacadeFileToDb;
use App\Models\GithubAL;

class WriteFacadesToDb implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $path = GithubAL::getLocalRepoPath('Accursedlands-Domains');
        $path .= "wild/virtual/facades/";
        if (!File::exists($path)) {
            throw new FileNotFoundException($path);
        } else {
            $files = File::allFiles($path);
            $searchFiles = [];
            foreach($files as $file) {
                if ($file->isFile() && $file->getExtension() == "c") {
                    $pathname = $file->getPathname();
                    if (str_replace(["/old/"],'',$pathname) == $pathname) {
                        WriteFacadeFileToDb::dispatch($pathname)->delay(now()->addSeconds(1));
                    }
                }
            }
        }
    }
}
