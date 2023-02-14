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
use App\Models\Death;
use App\Models\GithubAL;

class WriteDeathsToDb implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $fetchAll;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($fetchAll = false)
    {
        $this->fetchAll = $fetchAll;

        $deathDir = GithubAL::getDeathLogDir();
        $files = File::files($deathDir);
        $deathCount = Death::count();
        if ($deathCount) {
            // get date of last death
        }
        if ($this->fetchAll || !$deathCount)
            $files = File::allfiles($deathDir);

        $deaths = [];
        foreach($files as $file) {

            if (!File::exists($file)) {
                throw new FileNotFoundException($this->file);
            } else {
                $data = File::get($file);
                $data = explode("\n",$data);



                foreach($data as $line) {
                    if (!strlen($line))
                        continue;
                    preg_match('/^(.*)\] (\w+) killed by ([a-zA-Z0-9-_ ]*) at (\/[\w\/]+)/', $line, $matches);
                    if (sizeof($matches) && sizeof($matches) == 5) {
                        try {
                            $date = \Carbon\Carbon::parse($matches[1])->toDateTimeString();
                        } catch(\Exception $e) {
                            \Log::warning("Date: {$matches[1]} in {$line} in {$file} is not valid");
                            continue;
                        }
                        $death = [
                            'event_date' => \Carbon\Carbon::parse($matches[1])->toDateTimeString(),
                            'player' => $matches[2],
                            'cause' => $matches[3],
                            'location' => $matches[4]
                        ];
                        if (strpos($death['location'],"/domains/wild/virtual/server/") !== false) {
                            $tmp = str_replace("/domains/wild/virtual/server/","",$death['location']);
                            $coords = explode("/",$tmp);
                            $death['x'] = $coords[0];
                            $death['y'] = $coords[1];
                            $death['z'] = $coords[2] || 0;
                            $deaths[] = $death;
                        } else {
                            // todo - get gps coords for non-wild locations
                        }
                    } else {
                        \Log::warning("Can't read line: {$line}");
                    }
                }
                \Log::info("Read data from {$file}");
            }
            if (sizeof($deaths)) {
                foreach(array_chunk($deaths,5000) as $chunk) {
                    Death::insert($chunk);
                }
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
