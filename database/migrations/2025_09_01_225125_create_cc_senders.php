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
        Schema::create('cc_senders', function (Blueprint $table) {
            $table->bigIncrements('id'); // Auto-incrementing primary key
            $table->bigInteger('user_id')->unsigned()->index(); // Unsigned bigint user_id with index
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // Foreign key constraint to users table
            
            $table->string('sender_id', 20)->unique()->comment('Unique sender identifier');
            $table->string('reference_id', 20)->index()->comment('Reference identifier');
            $table->string('name_in_pan', 100)->comment('Name as per PAN card');
            $table->string('pan', 10)->index()->comment('PAN number');
            $table->string('name', 100)->comment('User name');
            $table->string('aadhar_number', 15)->nullable()->comment('Aadhaar number');
            $table->string('mobile', 15)->index()->comment('Mobile number');
            $table->string('card_number', 20)->comment('Card number (encrypted recommended)');
            $table->double('charge', 10, 2)->default(0.00)->comment('Charge amount');
            $table->double('gst', 10, 2)->default(0.00)->comment('GST amount');
            $table->tinyInteger('is_active')->default(0)->comment('Active status');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cc_senders');
    }
};
