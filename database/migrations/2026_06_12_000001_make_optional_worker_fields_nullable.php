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
        Schema::table('workers', function (Blueprint $table): void {
            $table->string('last_name_th')->nullable()->change();
            $table->string('last_name_en')->nullable()->change();
            $table->string('passport_number')->nullable()->change();
            $table->date('passport_expiry')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table): void {
            $table->string('last_name_th')->nullable(false)->change();
            $table->string('last_name_en')->nullable(false)->change();
            $table->string('passport_number')->nullable(false)->change();
            $table->date('passport_expiry')->nullable(false)->change();
        });
    }
};
