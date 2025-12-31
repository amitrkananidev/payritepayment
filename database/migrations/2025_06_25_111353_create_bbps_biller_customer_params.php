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
        Schema::create('bbps_biller_customer_params', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('biller_id', 50);
            $table->string('param_name', 255);
            $table->string('data_type', 20)->nullable();
            $table->boolean('is_optional')->default(true);
            $table->integer('min_length')->nullable();
            $table->integer('max_length')->nullable();
            $table->string('regex_pattern', 500)->nullable();
            $table->boolean('visibility')->default(true);
            $table->string('encryption_type', 20)->nullable();
            $table->timestamps();
            
            $table->foreign('biller_id')->references('biller_id')->on('bbps_billers')->onDelete('cascade');
            $table->index(['biller_id', 'param_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bbps_biller_customer_params');
    }
};
