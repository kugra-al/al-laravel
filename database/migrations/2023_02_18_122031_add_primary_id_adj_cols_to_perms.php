<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('perms', function (Blueprint $table) {
            $table->string('primary_id')->nullable();
            $table->string('primary_adj')->nullable();
            $table->string('short')->nullable();
            $table->string('pathname')->nullable();
            $table->integer('decay_value')->nullable();
            $table->integer('last_decay_time')->nullable();;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns("perms",['primary_id','primary_adj','short','decay_value','last_decay_time','pathname']);
    }
};
