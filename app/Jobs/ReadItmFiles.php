<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use App\Jobs\ReadItmFileToCache;

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


    }

    // Note: this job doesn't get run by itself. It gets called from job ReadItmFileToCache.php for all .itm file
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
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
                    // Skip /bak/, /old/ dirs. str_replace is faster - https://stackoverflow.com/a/42311760
                    if (str_replace(["/bak/","/old/"],'',$pathname) == $pathname) {
                        ReadItmFileToCache::dispatch(str_replace(storage_path()."/private/git/Accursedlands-obj/","",$file->getPathname()))->delay(now()->addSeconds(1));
//                        $searchFiles[] = $file;
                    }
                }

            }
  //          dd($searchFiles);
        }
    }
}
