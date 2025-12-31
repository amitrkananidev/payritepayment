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
        Schema::create('transactions_cc', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index(); // User ID with index
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // Foreign key to 'users' table
            
            $table->string('sender_id', 20)->index(); // Unsigned bigint user_id with index
            $table->foreign('sender_id')->references('sender_id')->on('cc_senders')->onDelete('cascade'); // Foreign key constraint to users table
            
            $table->string('beneficiary_id', 50)->index(); // Unsigned bigint user_id with index
            $table->foreign('beneficiary_id')->references('sender_id')->on('cc_beneficiaries')->onDelete('cascade'); // Foreign key constraint to users table
            
            $table->string('transaction_id', 50)->index();
            $table->double('amount', 15, 5)->default(0);
            $table->string('event', 50)->nullable();
            $table->string('payment_mode', 50)->nullable();
            $table->string('transfer_type', 50)->nullable();
            $table->tinyInteger('status')->default(0);
            
            $table->string('referenceId', 50)->nullable();
            $table->string('reason', 100)->nullable();
            
            $table->string('ben_name', 100)->nullable();
            $table->string('ben_ac_number', 50)->nullable();
            $table->string('ifsc', 20)->nullable();
            
            $table->string('utr', 50)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions_cc');
    }
};
