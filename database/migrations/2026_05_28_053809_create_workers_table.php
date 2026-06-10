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
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained('employers')->onDelete('cascade');
            $table->foreignId('nationality_id')->constrained('nationalities')->onDelete('restrict');
            // Thai Name
            $table->string('prefix_th')->nullable();
            $table->string('first_name_th');
            $table->string('last_name_th');
            // English Name
            $table->string('prefix_en')->nullable();
            $table->string('first_name_en');
            $table->string('last_name_en');
            // Personal Info
            $table->date('birth_date');
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            // Passport
            $table->string('passport_number')->unique();
            $table->date('passport_expiry');
            // Work Permit
            $table->string('wp_number')->nullable()->unique();
            $table->date('wp_expiry')->nullable();
            // Visa
            $table->date('visa_expiry')->nullable();
            // 90 Days Report
            $table->date('report_90_days_due')->nullable();
            // Files
            $table->string('passport_file')->nullable();
            $table->string('wp_file')->nullable();
            $table->string('visa_file')->nullable();
            $table->string('report_90_days_file')->nullable();
            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workers');
    }
};
