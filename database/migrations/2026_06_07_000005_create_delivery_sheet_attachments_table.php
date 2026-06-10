<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_sheet_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_sheet_id')->constrained('delivery_sheets')->cascadeOnDelete();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['delivery_sheet_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_sheet_attachments');
    }
};
