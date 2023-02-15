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
        Schema::create('perms', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('location')->nullable();
            $table->string('object')->nullable();
            $table->longtext('data')->nullable();
            $table->integer('x')->nullable();
            $table->integer('y')->nullable();
            $table->integer('z')->nullable();
            $table->datetime('lastseen')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('perms');
    }
};
