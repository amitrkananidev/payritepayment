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
        Schema::create('fund_banks', function (Blueprint $table) {
            $table->bigIncrements('id'); // Auto-incrementing primary key
            $table->unsignedBigInteger('user_id')->index(); // Unsigned bigint user_id
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // Foreign key constraint
            $table->string('holder_name', 255); // Holder name column
            $table->string('account_number', 191); // Account number column
            $table->string('ifsc', 191); // IFSC code column
            $table->string('transfer_types', 1024)->nullable(); // Nullable transfer types column
            $table->integer('status')->default(1); // Status column with default value 1
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP')); // Created at column with default value current timestamp
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fund_banks');
    }
};
