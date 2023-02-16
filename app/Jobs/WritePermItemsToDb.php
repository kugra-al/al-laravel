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
        $perms = Perm::where('save_type','data')->whereNotNull('inventory_location')->select('id','inventory_location','inventory_version')->get();
        $items = [];
        foreach($perms as $perm) {
            $file = substr(GithubAL::getLocalRepoPath($perm->inventory_location),0,-1);

            if (File::exists($file)) {
                $data = File::get($file);
                $tmp = explode("\n",$data);
                $version = $tmp[0];
                if (strlen($version) < 10) {
                    if ($perm->inventory_version != $version) {
                        $perm->inventory_version = $version;
                    }
                    unset($tmp[0]);
                }
                $numItems = 0;
                $itemDataSize = 0;
                foreach($tmp as $line) {
                    if (!strlen($line))
                        continue;
                    $split = explode("|",$line);
                    $object = $split[0];
                    if (strlen($object) > 150)
                        $object = "unknown";
                    $item = [
                        'data' => $line,
                        'object' => $object,
                        'perm_id' => $perm->id
                    ];
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
        if (sizeof($items)) {
            foreach(array_chunk($items,2000) as $chunk) {
                PermItem::upsert(
                    $chunk,
                    ['object','perm_id'],
                    ['data','object','perm_id']
                );
            }
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
