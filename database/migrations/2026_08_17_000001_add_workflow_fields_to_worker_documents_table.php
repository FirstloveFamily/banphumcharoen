<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_documents', function (Blueprint $table): void {
            $table->string('status', 40)->default('pending_review')->after('file_path');
            $table->timestamp('submitted_at')->nullable()->after('status');
            $table->timestamp('verified_at')->nullable()->after('submitted_at');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
        });

        DB::table('worker_documents')->update(['status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('worker_documents', function (Blueprint $table): void {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['status', 'submitted_at', 'verified_at', 'verified_by']);
        });
    }
};
