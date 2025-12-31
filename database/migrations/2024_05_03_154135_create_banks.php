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
        Schema::create('banks', function (Blueprint $table) {
            $table->increments('id'); // Auto-incrementing primary key
            $table->string('name', 100); // Name column with length of 100 characters
            $table->string('ifsc', 100); // IFSC column with length of 100 characters
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP')); // created_at with default current timestamp
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
