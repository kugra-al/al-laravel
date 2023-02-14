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
use App\Models\Deaths;

class WriteDeathsToDb implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->file = storage_path()."/private/DEATHS.txt";
        if (!File::exists($this->file)) {
            throw new FileNotFoundException($this->file);
        } else {
            $data = File::get($this->file);
            $data = explode("\n",$data);

            $deaths = [];
            // Check for last death from db
            foreach($data as $line) {
                if (!strlen($line))
                    continue;
                preg_match('/^(.*)\] (\w+) killed by ([a-zA-Z0-9-_ ]*) at (\/[\w\/]+)/', $line, $matches);
                if (sizeof($matches) && sizeof($matches) == 5) {
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
                    }
                } else {
                    \Log::warning("Can't read line: {$line}");
                }
            }
            if (sizeof($deaths))
                Deaths::insert($deaths);
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
