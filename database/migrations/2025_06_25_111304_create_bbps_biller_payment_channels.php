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
        Schema::create('bbps_biller_payment_channels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('biller_id', 50);
            $table->string('payment_channel', 20);
            $table->decimal('max_limit', 15, 2)->nullable();
            $table->decimal('min_limit', 15, 2)->nullable();
            $table->timestamps();
            
            $table->foreign('biller_id')->references('biller_id')->on('bbps_billers')->onDelete('cascade');
            $table->index(['biller_id', 'payment_channel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bbps_biller_payment_channels');
    }
};
