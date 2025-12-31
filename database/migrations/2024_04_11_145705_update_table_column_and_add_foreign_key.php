<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            // Add foreign key constraint for the city column
            $table->unsignedBigInteger('city_id');
            $table->foreign('city_id')->references('id')->on('cities');

            // Add foreign key constraint for the state column
            $table->unsignedBigInteger('state_id');
            $table->foreign('state_id')->references('id')->on('states');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            // Drop foreign key constraint for the city column
            $table->dropForeign(['city_id']);
            $table->dropColumn('city_id');

            // Drop foreign key constraint for the state column
            $table->dropForeign(['state_id']);
            $table->dropColumn('state_id');
        });
    }
};
