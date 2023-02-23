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
        Schema::create('perm_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('perm_id');
            $table->foreign('perm_id')->references('id')->on('perms');
            $table->unsignedBigInteger('perm_item_id')->nullable();
            $table->foreign('perm_item_id')->references('id')->on('perm_items');
            $table->string('commit');
            $table->datetime('commit_date');
            $table->string('type');
            $table->string('repo');
            $table->string('file');
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
        Schema::dropIfExists('perm_logs');
    }
};
