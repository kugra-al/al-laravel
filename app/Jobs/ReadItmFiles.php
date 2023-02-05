<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;

class ReadItmFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $gitdir = storage_path()."/private/git/Accursedlands-obj/items";
        if (!File::exists($gitdir)) {
            throw new FileNotFoundException($gitdir);
        } else {
            $files = File::allFiles($gitdir);
            $searchFiles = [];
            foreach($files as $file) {
                if ($file->isFile() && $file->getExtension() == "itm") {
                    $pathname = $file->getPathname();
                    if (str_replace(["/bak/","/old/"],'',$pathname) == $pathname) {
                        $searchFiles[] = $file;
                    }
                }

            }
            dd($searchFiles);
        }

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
