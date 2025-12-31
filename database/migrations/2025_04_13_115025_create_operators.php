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
        Schema::create('ace_operators', function (Blueprint $table) {
            $table->bigIncrements('id'); // Auto-incrementing primary key
            $table->string('name',50); // LongText for device column
            $table->string('op_code',50)->unique()->index('op_code_1'); // LongText for device column
            $table->string('rk_op_code',50)->index('rk_op_code_1')->default(NULL); // LongText for device column
            $table->string('op_image', 191)->default('op_image.png');
            $table->integer('status')->default(0);
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP')); // created_at with default current timestamp
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'))->onUpdate(DB::raw('CURRENT_TIMESTAMP')); // updated_at with default current timestamp and on update current timestamp
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ace_operators');
    }
};
