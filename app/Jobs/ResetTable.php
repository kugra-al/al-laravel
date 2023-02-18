<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use DB;

class ResetTable implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $table;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($table)
    {
        $this->table = $table;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->table) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::statement("TRUNCATE {$this->table};");
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            \Log::info("Reset {$this->table} table");
        }
    }
}
