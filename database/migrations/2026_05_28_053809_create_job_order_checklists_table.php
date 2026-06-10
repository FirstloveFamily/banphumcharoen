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
        Schema::create('job_order_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_order_id')->constrained('job_orders')->onDelete('cascade');
            $table->foreignId('document_master_id')->constrained('document_masters')->onDelete('restrict');
            $table->boolean('is_required')->default(false);
            $table->enum('status', ['pending', 'received', 'verified', 'rejected', 'missing'])->default('pending');
            $table->timestamp('received_at')->nullable();
            $table->string('attached_file_path')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_order_checklists');
    }
};
