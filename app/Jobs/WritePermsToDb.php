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

    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
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
                'lastseen' => \Carbon\Carbon::now()->toDateTimeString(),
                'sign_title' => null,
                'touched_by' => null,
                'last_touched' => null,
                'psets' => null
            ];


            $location = explode(":",$filename)[0];
            $location = str_replace("_","/",$location);
            $location = str_replace("/domains/player/built/data/","",$location);
            $perm['location'] = $location;
            $coords = GithubAL::getCoordsFromLocation($location);
            if (sizeof($coords) && sizeof($coords) == 3)
                $perm = array_merge($perm,$coords);

            if (sizeof($data)) {
                $perm['object'] = $data[0];

                if ($perm['object'] == "/obj/base/misc/signpost") {
                    preg_match('/"sign_title":"(.*?)","/',$perm['data'],$matches);
                    if (isset($matches[1]) && strlen($matches[1]))
                        $perm['sign_title'] = json_encode($matches[1]);
                }
                preg_match('/"touched_by":\((.*?)\)/',$perm['data'],$matches);
                if (sizeof($matches) > 1) {
                    if (strlen($matches[1]) > 4)
                        $perm['touched_by'] = $matches[1];
                }
                preg_match('/"last_touched":(\d+)/',$perm['data'],$matches);
                if (sizeof($matches) > 1) {
                    if (strlen($matches[1]) > 4)
                        $perm['last_touched'] = $matches[1];
                }
                preg_match('/"psets":\((.*?)\)/',$perm['data'],$matches);
                if (sizeof($matches) > 1) {
                    if (strlen($matches[1]) > 4)
                        $perm['psets'] = $matches[1];
                }
            }
            $perms[] = $perm;
        }
        if (sizeof($perms)) {
            Perm::upsert(
                $perms,
                ['filename'],['data','lastseen','x','y','z','object','location']
            );
            \Log::info("Wrote perms");
        } else {
            \Log::info("Nothing to write");
        }
    }
}
