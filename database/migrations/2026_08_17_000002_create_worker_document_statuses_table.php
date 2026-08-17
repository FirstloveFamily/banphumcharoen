<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_document_statuses', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name_th', 100);
            $table->string('color_class', 255)->default('bg-slate-100 text-slate-500');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        
        DB::table('worker_document_statuses')->insert([
            ['code' => 'pending_review', 'name_th' => 'รอตรวจสอบ', 'color_class' => 'bg-amber-50 text-amber-700', 'sort_order' => 10, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'processing', 'name_th' => 'กำลังดำเนินการ', 'color_class' => 'bg-blue-50 text-blue-700', 'sort_order' => 20, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'approved', 'name_th' => 'ผ่านแล้ว', 'color_class' => 'bg-emerald-50 text-emerald-700', 'sort_order' => 30, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'rejected', 'name_th' => 'ถูกตีกลับ', 'color_class' => 'bg-rose-50 text-rose-700', 'sort_order' => 40, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_document_statuses');
    }
};
