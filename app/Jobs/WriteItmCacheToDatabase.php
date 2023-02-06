<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use App\Models\Item;

class WriteItmCacheToDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Check cached keys against column keys in db. If any columns are
        //  not found in db, add to table
        $cacheKey = Item::getKeyCacheName();
        $keys = Cache::get($cacheKey);
        $dbKeys = \Schema::getColumnListing('items');
        $newKeys = [];
        if ($keys) {
            foreach($keys as $key) {
                $key = $key;
                if (in_array($key,$dbKeys) === false) {
                    $newKeys[] = $key;
                }
            }
            if (sizeof($newKeys)) {
                \Schema::table('items', function($table) use ($newKeys) {
                    foreach($newKeys as $key) {
                        $table->text($key)->nullable();
                    }
                });
            }
            // Clear up cached keys - we don't need them anymore
            Cache::forget($cacheKey);
        }
        // Add any cached items to table
        $cacheKey = Item::getValueCacheName();
        $values = Cache::get($cacheKey);
        if ($values) {
            // This inserts each item in seperate db calls which is costly. This means a lot of db inserts (3000 at current item count),
            //  but pretty sure this is still the best way to do it
            // If this causes problems, batch the jobs together and split the load
            //  or
            // Normalize the keys so it can be done on a single insert and skip eloquent
            foreach($values as $value) {
                $item = new Item;
                $item->fill($value);
                $item->save();
//                dd($value);
            }
            //Item::insert(array_values($values));
            Cache::forget($cacheKey);
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
