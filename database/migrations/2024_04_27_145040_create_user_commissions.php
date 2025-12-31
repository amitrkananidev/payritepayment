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
        Schema::create('user_commissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->double('total_amount', 15, 5)->default(0);
            $table->double('amount', 15, 5)->default(0);
            $table->double('tds', 15, 5)->default(0);
            $table->double('tds_par', 15, 5)->default(0);
            $table->double('gst', 15, 5)->default(0);
            $table->double('gst_par', 15, 5)->default(0);
            $table->char('wallets_uuid', 36)->index();
            $table->foreign('wallets_uuid')->references('uuid')->on('transactions')->onDelete('cascade');
            $table->string('ref_transaction_id', 100)->nullable()->index();
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_commissions');
    }
};
