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
        Schema::create('recharge_commissions', function (Blueprint $table) {
            $table->bigIncrements('id'); // Auto-incrementing primary key
            $table->bigInteger('slab_id')->unsigned()->index(); // Unsigned bigint user_id with index
            $table->foreign('slab_id')->references('id')->on('recharge_slabs')->onDelete('cascade'); // Foreign key constraint to users table
            $table->bigInteger('op_id')->unsigned()->index(); // Unsigned bigint user_id with index
            $table->foreign('op_id')->references('id')->on('ace_operators')->onDelete('cascade'); // Foreign key constraint to users table
            $table->double('ret_commission'); // Amount column
            $table->double('dis_commission'); // Amount column
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recharge_commissions');
    }
};
