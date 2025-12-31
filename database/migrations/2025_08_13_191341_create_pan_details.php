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
        Schema::create('pan_details', function (Blueprint $table) {
            $table->id();
            // Personal Information
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->default('');
            $table->string('last_name', 100);
            $table->string('full_name', 300);
            $table->enum('gender', ['male', 'female', 'other']);
            $table->bigInteger('dob')->comment('Date of birth as Unix timestamp');
            
            // Contact Information
            $table->string('email', 255)->default('')->nullable();
            $table->string('phone', 20)->default('')->nullable();
            
            // Address Information
            $table->string('building_name', 255)->default('')->nullable();
            $table->string('street_name', 255)->default('')->nullable();
            $table->string('locality', 255)->default('')->nullable();
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('pin_code', 10);
            
            // Government ID Information
            $table->string('masked_aadhaar', 12)->comment('Masked Aadhaar number');
            $table->boolean('aadhaar_linked')->default(false);
            $table->string('pan', 10);
            $table->string('pan_type', 10);
            $table->boolean('is_pan_valid')->default(false);
            
            $table->timestamps();
            
            // Indexes
            $table->index('pan');
            $table->index('masked_aadhaar');
            $table->index('email');
            $table->index('phone');
            $table->index('pin_code');
            $table->index('created_at');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pan_details');
    }
};
