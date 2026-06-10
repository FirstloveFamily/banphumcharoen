<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_sheet_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_sheet_id')->constrained('delivery_sheets')->cascadeOnDelete();
            $table->foreignId('job_order_id')->constrained('job_orders')->cascadeOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique('job_order_id');
            $table->index(['delivery_sheet_id', 'job_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_sheet_items');
    }
};
