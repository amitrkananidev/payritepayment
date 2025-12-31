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
        Schema::create('bbps_biller_additional_info', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('biller_id', 50);
            $table->string('param_name', 255);
            $table->string('data_type', 20)->nullable();
            $table->boolean('is_optional')->default(true);
            $table->string('info_type', 20)->default('GENERAL');
            $table->timestamps();
            
            $table->foreign('biller_id')->references('biller_id')->on('bbps_billers')->onDelete('cascade');
            $table->index(['biller_id', 'info_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bbps_biller_additional_info');
    }
};
