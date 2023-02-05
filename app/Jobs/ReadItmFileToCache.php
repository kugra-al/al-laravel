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
            $data = File::get($this->file);
            dd($data);
            dd($this->file);
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
