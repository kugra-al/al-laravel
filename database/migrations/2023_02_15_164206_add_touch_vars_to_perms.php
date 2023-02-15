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
            $table->string('sign_title')->nullable();
            $table->text('touched_by')->nullable();
            $table->string('last_touched')->nullable();
            $table->text('psets')->nullable();
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
            $table->dropColumns(['sign_title','touched_by','last_touched','psets']);
        });
    }
};
