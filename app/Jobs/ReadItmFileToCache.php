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
                    $key = $field;
                    $parsed[$key] = $value;
                }
            }

            dd($parsed);
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//         if (!File::exists($file)) {
//             throw new ProcessFailedException($file);
//         } else {
//             dd($file);
//         }
    }
}
