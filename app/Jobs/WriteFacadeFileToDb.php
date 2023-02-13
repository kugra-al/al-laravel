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

class WriteFacadeFileToDb implements ShouldQueue
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
        $this->file = $file;

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
            $data = File::get($this->file);
            $lines = explode("\n",$data);

            $facadeID = str_replace(["_facade.c",GithubAL::getLocalRepoPath('Accursedlands-Domains')."wild/virtual/facades/"],"",$this->file);
            $destination = "";
            $coords = [];

            foreach($lines as $line) {
                $line = trim($line);
                if (preg_match("/^set_destination\((.*)\);$/i",$line,$matches)) {
                    if ($matches[1]) {
                        $matches[1] = str_replace("\"","",$matches[1]);
                        $destination = $matches[1];
                    }
                } else
                if (preg_match("/^set_location\((.*)\);$/i",$line,$matches)) {
                    if ($matches[1]) {
                        $coords = explode(",",$matches[1]);
                    }
                }
            }
            if ($facadeID && strlen($destination) && sizeof($coords)) {
                //
            } else {
                \Log::warning("Failed to read facade data from {$this->file}");
            }
        }
    }
}
