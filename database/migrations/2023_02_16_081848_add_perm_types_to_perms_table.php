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
            $table->string('perm_type')->nullable();
            $table->string('save_type')->nullable();
            $table->boolean('is_inventory_container')->nullable();
            $table->string('inventory_location')->nullable();
            $table->string('inventory_version')->nullable();
            $table->integer('num_items')->nullable();
            $table->integer('item_data_size')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns("perms", ["perm_type","save_type","is_inventory_container","inventory_location","inventory_version","num_items","item_data_size"]);
    }
};
