<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
//             Cache any unknown keys
//             $keys = array_keys($parsed);
//             $cacheKey = 'item_d-itm-keys';
//             $cachedKeys = Cache::get($cacheKey);
//             if (!$cachedKeys)
//                 $cachedKeys = [];
//             foreach($keys as $key) {
//                 if (in_array($key,$cachedKeys) === false)
//                     $cachedKeys[] = $key;
//             }
//             Cache::forever($cacheKey, $cachedKeys);
//             Cache values
//             $cacheKey = 'item_d-itm-values';
//             $cachedValues = Cache::get($cacheKey);
//             if (!$cachedValues)
//                 $cachedValues = [];
//             $cachedValues[] = $parsed;
//             Cache::forever($cacheKey, $cachedValues);

//            if (in_array($
//             $keys = \Schema::getColumnListing('items');
//             $newKeys = [];
//             foreach(array_keys($parsed) as $key) {
//                 if (in_array($key,$keys) === false) {
//                     $newKeys[] = $key;
//                 }
//             }
//             dd($newKeys);
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
