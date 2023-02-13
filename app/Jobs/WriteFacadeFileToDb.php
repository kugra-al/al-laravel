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
use App\Models\Facade;

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

            $facadeID = str_replace(["_facade.c",".c",GithubAL::getLocalRepoPath('Accursedlands-Domains')."wild/virtual/facades/"],"",$this->file);
            $destination = "none";
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
            if ($facadeID && sizeof($coords)) {
                if (!is_numeric($coords[0]) || !is_numeric($coords[1]) || !is_numeric($coords[2])) {
                    \Log::warning("Failed to read facade data from {$this->file} because set_location is malformed");
                } else {
                    Facade::updateOrCreate(
                        ['facade_id'=>$facadeID],
                        ['destination'=>$destination,'x'=>$coords[0],'y'=>$coords[1],'z'=>$coords[2]]
                    );
                }
            } else {
                \Log::warning("Failed to read facade data from {$this->file}");
            }
        }
    }
}
