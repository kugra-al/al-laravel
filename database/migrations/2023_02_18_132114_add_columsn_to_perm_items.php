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
        Schema::table('perm_items', function (Blueprint $table) {
            $table->string('primary_id')->nullable();
            $table->string('primary_adj')->nullable();
            $table->string('short')->nullable();
            $table->string('filename')->nullable();
            $table->string('version')->nullable();
            $table->text('touched_by')->nullable();
            $table->text('psets')->nullable();
            $table->string('last_touched')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns("perm_items",['primary_id','primary_adj','short','filename','version','touched_by','psets','last_touched']);
    }
};
