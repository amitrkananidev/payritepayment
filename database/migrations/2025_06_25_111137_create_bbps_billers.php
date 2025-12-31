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
        Schema::create('bbps_billers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('biller_id', 50)->unique();
            $table->string('biller_name', 255);
            $table->string('biller_alias_name', 255)->nullable();
            $table->foreignId('biller_category_id')->constrained('bbps_categories')->onDelete('restrict');
            $table->string('biller_mode', 20)->nullable();
            $table->boolean('biller_accepts_adhoc')->default(false);
            $table->boolean('parent_biller')->default(false);
            $table->string('biller_ownership', 50)->nullable();
            $table->string('biller_coverage', 10)->nullable();
            $table->string('fetch_requirement', 20)->nullable();
            $table->string('payment_amount_exactness', 50)->nullable();
            $table->string('support_bill_validation', 20)->nullable();
            $table->dateTime('biller_effective_from')->nullable();
            $table->dateTime('biller_effective_to')->nullable();
            $table->dateTime('biller_temp_deactivation_start')->nullable();
            $table->dateTime('biller_temp_deactivation_end')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('plan_mdm_requirement', 20)->nullable();
            $table->string('support_deemed', 50)->nullable();
            $table->string('support_pending_status', 50)->nullable();
            $table->timestamps();
            
            $table->index('biller_id');
            $table->index('status');
            $table->index(['biller_category_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bbps_billers');
    }
};
