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
        Schema::create('fund_onlines', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->bigInteger('user_id')->unsigned()->index(); // Unsigned bigint user_id with index
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // Foreign key constraint to users table
            $table->string('transaction_id', 191)->index(); // Transaction ID column with index
            $table->double('amount'); // Amount column
            $table->string('ref_id', 512)->nullable(); // Nullable bank_ref column
            $table->string('transfer_type', 512)->nullable(); // Nullable transfer_type column
            $table->tinyInteger('status')->default(0); // Status column with default value 0
            $table->Integer('pg_id')->unsigned()->nullable(); // Unsigned bigint approved_by column, nullable
            $table->char('wallets_uuid', 36)->index();
            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fund_onlines');
    }
};
