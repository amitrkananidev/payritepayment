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
        Schema::create('dmt_beneficiaries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('customer_mobile', 50)->index();
            $table->string('account_holder_name', 100);
            $table->string('mobile', 50)->index();
            $table->string('account_number', 50);
            $table->string('bank_name', 100);
            $table->string('ifsc', 50);
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('is_verify')->default(0);

            // Foreign key constraint for customer_mobile
            $table->foreign('customer_mobile')->references('mobile')->on('dmt_customers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dmt_beneficiaries');
    }
};
