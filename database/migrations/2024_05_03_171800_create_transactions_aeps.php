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
        Schema::create('transactions_aeps', function (Blueprint $table) {
            $table->increments('id'); // Auto-incrementing primary key
            $table->unsignedBigInteger('user_id')->index(); // User ID with index
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // Foreign key to 'users' table

            // Other columns
            $table->string('transaction_id', 50)->index();
            $table->string('vendor_id', 50)->nullable();
            $table->string('outlet_id', 50)->nullable();
            $table->string('bank_iin', 50)->nullable();
            $table->double('amount', 15, 5)->default(0);
            $table->string('event', 50)->nullable();
            $table->string('transfer_type', 50)->nullable();
            $table->tinyInteger('status')->default(0);
            $table->string('referenceId', 50)->nullable();
            $table->string('reason', 100)->nullable();
            $table->string('ben_name', 100)->nullable();
            $table->string('tid', 50)->nullable();
            $table->string('remitterName', 100)->nullable();
            $table->string('aadhaar', 50)->nullable();
            $table->string('utr', 50)->nullable();
            $table->double('commission', 15, 5)->default(0);
            $table->char('wallets_uuid', 36)->index(); // Index for wallets_uuid
            // Define other columns as needed

            // Timestamps columns with defaults
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions_aeps');
    }
};
