<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_order_statuses', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name_th', 100);
            $table->string('name_en', 100)->nullable();
            $table->string('badge_class', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->boolean('requires_note')->default(false);
            $table->boolean('sets_completed_at')->default(false);
            $table->timestamps();
        });

        DB::table('job_order_statuses')->insert([
            [
                'code' => 'pending',
                'name_th' => 'รอเริ่มงาน',
                'name_en' => 'Pending',
                'badge_class' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                'sort_order' => 1,
                'is_active' => true,
                'is_default' => true,
                'requires_note' => false,
                'sets_completed_at' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'processing',
                'name_th' => 'กำลังดำเนินการ',
                'name_en' => 'Processing',
                'badge_class' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
                'sort_order' => 2,
                'is_active' => true,
                'is_default' => false,
                'requires_note' => false,
                'sets_completed_at' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'waiting_document',
                'name_th' => 'รอเอกสาร',
                'name_en' => 'Waiting Document',
                'badge_class' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                'sort_order' => 3,
                'is_active' => true,
                'is_default' => false,
                'requires_note' => false,
                'sets_completed_at' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'approved',
                'name_th' => 'อนุมัติแล้ว',
                'name_en' => 'Approved',
                'badge_class' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                'sort_order' => 4,
                'is_active' => true,
                'is_default' => false,
                'requires_note' => false,
                'sets_completed_at' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'completed',
                'name_th' => 'เสร็จสิ้น',
                'name_en' => 'Completed',
                'badge_class' => 'bg-slate-900 text-white ring-slate-900/20',
                'sort_order' => 5,
                'is_active' => true,
                'is_default' => false,
                'requires_note' => false,
                'sets_completed_at' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'cancelled',
                'name_th' => 'ยกเลิก',
                'name_en' => 'Cancelled',
                'badge_class' => 'bg-slate-100 text-slate-500 ring-slate-400/20',
                'sort_order' => 6,
                'is_active' => true,
                'is_default' => false,
                'requires_note' => true,
                'sets_completed_at' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'rejected',
                'name_th' => 'ไม่ผ่าน',
                'name_en' => 'Rejected',
                'badge_class' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
                'sort_order' => 7,
                'is_active' => true,
                'is_default' => false,
                'requires_note' => true,
                'sets_completed_at' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('job_order_statuses');
    }
};
