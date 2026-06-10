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
        Schema::create('job_orders', function (Blueprint $table) {
            $table->id();
            $table->string('job_number')->unique();
            $table->foreignId('employer_id')->constrained('employers')->onDelete('cascade');
            $table->foreignId('worker_id')->constrained('workers')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services')->onDelete('restrict');
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->onDelete('set null');
            // Financial
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'cancelled'])->default('pending');
            // Workflow
            $table->enum('status', ['pending', 'processing', 'waiting_document', 'approved', 'completed', 'cancelled', 'rejected'])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            // Dates
            $table->date('due_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            // Notes
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_orders');
    }
};
