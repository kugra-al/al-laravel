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
use App\Models\PermItem;

class WritePermItemsToDb implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->handle();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $perms = Perm::where('save_type','data')->whereNotNull('inventory_location')->select('id','inventory_location','inventory_version')->get();
        $items = [];
        foreach($perms as $perm) {
            if (sizeof($items) > 1000) {
                PermItem::upsert(
                    $items,
                    ['object','perm_id'],
                    ['data','object','perm_id','pathname','filename','primary_id','primary_adj','short','touched_by','pathname','last_touched','psets']
                );
                $items = [];
            }
            $file = substr(GithubAL::getLocalRepoPath($perm->inventory_location),0,-1);

            if (File::exists($file)) {
                $data = File::get($file);
                $tmp = explode("\n",$data);
                $version = $tmp[0];
                $version = str_replace("v","",$version);
                if ($version == "([])")
                    continue;
                if (strlen($version) < 10 && $version != "([])") {
                    if ($perm->inventory_version != $version) {
                        $perm->inventory_version = $version;
                    }
                    unset($tmp[0]);
                } else {
                    $version = "1.0";
                    $perm->inventory_version = "1.0";
                }
                $numItems = 0;
                $itemDataSize = 0;
                foreach($tmp as $line) {
                    if (!strlen($line))
                        continue;
                    // v1 object like: (["/obj/base/misc/furniture#519":"([\"resistances\":
                    $object = "unknown";
                   // $code = $line;
                    $pathname = null;

                    $item = [
                        'data' => $line,
                        'object' => null,
                        'perm_id' => $perm->id,
                        'pathname' => null,
                        'primary_id'=>null,
                        'primary_adj'=>null,
                        'short'=>null,
                        'touched_by'=>null,
                        'pathname'=>null,
                        'version'=>$version,
                        'filename'=>$perm->inventory_location
                    ];

                    if ($version == "2.0") {
                        $split = explode("|",$line);
                        $item["object"] = $split[0];
                        // v2.0 items store things like this
                        if (strlen($item["object"]) > 150) {
                            $item["object"] = "unknown";

                        }

                        $vars = GithubAL::readVarsFromObjectData($line,['primary_id','primary_adj','short','touched_by','pathname','psets','last_touched']);
                        $item = array_merge($item,$vars);
                    } else {
                        // catch v1 pathname
                        $split = explode(":",$line);
                        $object = $split[0];
                        $object = str_replace(["([","\""],"",$object);
                        if (strlen($object) > 150 || strlen($object) < 5)
                            $object = "unknown";
                        $item["object"] = $object;
                        $vars = GithubAL::readVarsFromObjectData($line,['primary_id','primary_adj','short','touched_by','pathname','psets','last_touched'],true);
                        $item = array_merge($item,$vars);
                    }


                    $items[] = $item;
                    $numItems++;
                    $itemDataSize += strlen($line);
                }
                $perm->num_items = $numItems;
                $perm->item_data_size = $itemDataSize;
                if ($perm->isDirty()) {
                    $perm->save();
                }
            }
        }
        \Log::info('Wrote perm items');
    }
}
