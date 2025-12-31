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
        Schema::create('bbps_biller_response_params', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('biller_id', 50);
            $table->string('param_type', 50)->nullable();
            $table->string('param_key', 100)->nullable();
            $table->string('param_value', 255)->nullable();
            $table->timestamps();
            
            $table->foreign('biller_id')->references('biller_id')->on('bbps_billers')->onDelete('cascade');
            $table->index(['biller_id', 'param_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bbps_biller_response_params');
    }
};
