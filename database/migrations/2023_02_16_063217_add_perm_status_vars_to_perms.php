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
            $table->string('perm_id')->nullable();
            $table->string('map_dir')->nullable();
            $table->boolean('destroyed')->nullable();
            $table->boolean('live')->nullable();
            //
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('perms', function (Blueprint $table) {
            $table->dropColumns(['perm_id','map_dir','destroyed','live']);
        });
    }
};
