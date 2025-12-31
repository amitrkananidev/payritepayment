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
        Schema::create('cc_beneficiaries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned()->index(); // Unsigned bigint user_id with index
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // Foreign key constraint to users table
            
            $table->string('sender_id', 20)->index(); // Unsigned bigint user_id with index
            $table->foreign('sender_id')->references('sender_id')->on('cc_senders')->onDelete('cascade'); // Foreign key constraint to users table
            
            $table->string('reference', 20)->index(); // Unsigned bigint user_id with index
            $table->string('beneficiary_id', 20)->index(); // Unsigned bigint user_id with index
            $table->string('account_holder_name', 100);
            $table->string('account_number', 50);
            $table->string('ifsc', 50)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->tinyInteger('is_verify')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cc_beneficiaries');
    }
};
