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
use Illuminate\Support\Facades\Cache;
use App\Models\Item;

class ReadItmFileToCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $file;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($file)
    {
        $this->file = storage_path()."/private/git/Accursedlands-obj/".$file;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!File::exists($this->file)) {
            throw new FileNotFoundException($this->file);
        } else {
            // Generated from chatgpt using prompt:
            // i want you to create a php function that will read data from a file and return that data in an array of keys/values.
            // Lines that start with # or // should not be added. Blank lines should not be added. If a line starts with + or ~, the
            // value of that field should be added to the previous key. If you understand, say "ok" and wait for an example file

            // Doesn't handle files with mutiple keys (ex. items/fetishes/bear_fang_necklace.itm has mutiple looks)
            $data = File::get($this->file);
            $lines = explode("\n",$data);

            $parsed = [];
            $key = '';

            foreach ($lines as $line) {
                $line = trim($line);
                $line = str_replace("\t"," ",$line);
                if (empty($line) || $line[0] === '#' || substr($line, 0, 2) === '//') {
                    continue;
                }

                $parts = explode(' ', $line);
                if (count($parts) < 2) {
                    continue;
                }

                $field = $parts[0];
                $value = trim(implode(' ', array_slice($parts, 1)));
                if ($field[0] === '+') {
                    $parsed[$key] .= $value;
                } elseif ($field[0] === '~') {
                    $parsed[$key] .= ' ' . $value;
                } else {
                    // Preface all keys with 'itm_' because we need 'id' field for database
                    if (preg_match('/^[a-z_\-\d]+$/i',$field)) {
                        $key = "itm_".strtolower($field);
                        $parsed[$key] = $value;
                    }
                }
            }
            $tmp = explode("/",$this->file);
            $filename = $tmp[sizeof($tmp)-1];
            // replace 'storage_path()."/private/git/Accursedlands-obj/"' with ''
            $dir = "/obj/".str_replace($filename,"",$this->file);
            $dir = str_replace(storage_path()."/private/git/Accursedlands-obj/","",$dir);
            $parsed["filename"] = $filename;
            $parsed["path"] = $dir;
            $parsed["fullpath"] = $dir.$filename;

            if (isset($parsed["itm_id"]) && strlen($parsed["itm_id"])) {
                $parsedID = explode("#",$parsed["itm_id"])[0];
                $parsed["short"] = $parsedID;
                if (isset($parsed["itm_adj"]) && strlen($parsed["itm_adj"])) {
                    $parsedAdj = explode("#",$parsed["itm_adj"])[0];
                    if ($parsedAdj != "*")
                        $parsed["short"] = $parsedAdj." ".$parsed["short"];
                }
            }
//             $now = \Carbon\Carbon::now('utc')->toDateTimeString();
//             $parsed["created_at"] = $now;
//             $parsed["updated_at"] = $now;
            // Cache any unknown keys
            $keys = array_keys($parsed);
            $cacheKey = Item::getKeyCacheName();
            $cachedKeys = Cache::get($cacheKey);
            if (!$cachedKeys)
                $cachedKeys = [];
            foreach($keys as $key) {
                $key = strtolower($key);
                if (in_array($key,$cachedKeys) === false)
                    $cachedKeys[] = $key;
            }
            Cache::forever($cacheKey, $cachedKeys);
            // Cache values
            $cacheKey = Item::getValueCacheName();
            $cachedValues = Cache::get($cacheKey);
            if (!$cachedValues)
                $cachedValues = [];
            $cachedValues[$dir.$filename] = $parsed;
            Cache::forever($cacheKey, $cachedValues);

            //dump(Cache::get(Item::getValueCacheName()));

        }
    }
}
