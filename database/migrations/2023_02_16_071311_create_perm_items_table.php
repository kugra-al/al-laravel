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
        Schema::create('perm_items', function (Blueprint $table) {
            $table->id();
            $table->longtext('data');
            $table->string('object')->nullable();
            $table->unsignedBigInteger('perm_id');
            $table->foreign('perm_id')->references('id')->on('perms');
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
        Schema::dropIfExists('perm_items');
    }
};
