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
use App\Models\Perm;

class WritePermsToDb implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $dir = GithubAL::getLocalRepoPath('Accursedlands-perms')."perm_objs/";
        $perms = [];
        foreach(File::files($dir) as $file) {
            $filename = File::name($file);
            $contents = utf8_encode(File::get($file));
            $data = explode("\n",$contents);
            $perm = [
                'filename' => $filename,
                'object'   => null,
                'data'     => $contents,
                'x'        => null,
                'y'        => null,
                'z'        => null,
                'lastseen' => \Carbon\Carbon::now()->toDateTimeString()
            ];
            if (sizeof($data))
                $perm['object'] = $data[0];
            $location = explode(":",$filename)[0];
            $location = str_replace("_","/",$location);
            $perm['location'] = $location;
            $coords = GithubAL::getCoordsFromLocation($location);
            if (sizeof($coords) && sizeof($coords) == 3)
                $perm = array_merge($perm,$coords);
            $perms[] = $perm;
        }
        if (sizeof($perms))
            Perm::upsert(
                $perms,
                ['filename'],['data','lastseen','x','y','z','object','location']
            );
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
