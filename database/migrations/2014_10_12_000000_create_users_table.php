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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('surname')->nullable();
            $table->string('email', 191)->unique()->index('email_1');
            $table->string('mobile', 191)->unique()->index('mobile_1');
            $table->date('dob')->nullable();
            $table->boolean('user_type')->comment('1=Admin, 2=Retailers, 12=subuser, 21=subadmin');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('user_token');
            $table->integer('first_login')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
