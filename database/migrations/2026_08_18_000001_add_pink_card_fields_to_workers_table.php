<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workers', function (Blueprint $table): void {
            $table->string('pink_card_number')->nullable()->after('passport_number');
            $table->date('pink_card_expiry')->nullable()->after('passport_expiry');
            $table->string('pink_card_file')->nullable()->after('passport_file');
            $table->string('pink_card_status')->nullable()->after('pink_card_file');
        });

        $pinkMasterIds = DB::table('document_masters')
            ->where(function ($query): void {
                $query->where('id', 12)
                    ->orWhere('name', 'บัตรชมพู')
                    ->orWhere('code', 'Pink Identification Card for Foreign Workers');
            })
            ->pluck('id');

        if ($pinkMasterIds->isEmpty()) {
            return;
        }

        DB::table('worker_documents')
            ->whereIn('document_master_id', $pinkMasterIds)
            ->where(function ($query): void {
                $query->whereNotNull('file_path')->orWhereNotNull('expiry_date');
            })
            ->orderBy('id')
            ->get()
            ->each(function ($document): void {
                DB::table('workers')
                    ->where('id', $document->worker_id)
                    ->whereNull('pink_card_file')
                    ->whereNull('pink_card_expiry')
                    ->update([
                        'pink_card_file' => $document->file_path,
                        'pink_card_expiry' => $document->expiry_date,
                        'pink_card_status' => $document->status,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table): void {
            $table->dropColumn([
                'pink_card_number',
                'pink_card_expiry',
                'pink_card_file',
                'pink_card_status',
            ]);
        });
    }
};
