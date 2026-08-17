<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('worker_document_statuses')->insertOrIgnore([
            'code' => 'awaiting_upload',
            'name_th' => 'รอส่งเอกสาร',
            'color_class' => 'bg-slate-100 text-slate-500',
            'sort_order' => 5,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('worker_document_statuses')->where('code', 'awaiting_upload')->delete();
    }
};
