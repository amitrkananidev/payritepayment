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
        Schema::create('user_devices', function (Blueprint $table) {
            $table->bigIncrements('id'); // Auto-incrementing primary key
            $table->bigInteger('user_id')->unsigned()->index(); // Unsigned bigint user_id with index
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // Foreign key constraint to users table
            $table->longText('device'); // LongText for device column
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP')); // created_at with default current timestamp
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'))->onUpdate(DB::raw('CURRENT_TIMESTAMP')); // updated_at with default current timestamp and on update current timestamp
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
