<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_prefixes', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name_th', 100);
            $table->string('name_en', 100);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('worker_prefixes')->insert([
            [
                'code' => 'mr',
                'name_th' => 'นาย',
                'name_en' => 'Mr.',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'mrs',
                'name_th' => 'นาง',
                'name_en' => 'Mrs.',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ms',
                'name_th' => 'นางสาว',
                'name_en' => 'Ms.',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Schema::table('workers', function (Blueprint $table): void {
            $table->foreignId('worker_prefix_id')
                ->nullable()
                ->after('nationality_id')
                ->constrained('worker_prefixes')
                ->nullOnDelete();
        });

        $prefixMap = DB::table('worker_prefixes')->pluck('id', 'code');

        DB::table('workers')->where('prefix_th', 'นาย')->update(['worker_prefix_id' => $prefixMap['mr'] ?? null]);
        DB::table('workers')->where('prefix_th', 'นาง')->update(['worker_prefix_id' => $prefixMap['mrs'] ?? null]);
        DB::table('workers')->where('prefix_th', 'นางสาว')->update(['worker_prefix_id' => $prefixMap['ms'] ?? null]);

        DB::table('workers')->whereNull('worker_prefix_id')->where('prefix_en', 'Mr.')->update(['worker_prefix_id' => $prefixMap['mr'] ?? null]);
        DB::table('workers')->whereNull('worker_prefix_id')->where('prefix_en', 'Mrs.')->update(['worker_prefix_id' => $prefixMap['mrs'] ?? null]);
        DB::table('workers')->whereNull('worker_prefix_id')->where('prefix_en', 'Ms.')->update(['worker_prefix_id' => $prefixMap['ms'] ?? null]);
    }

    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('worker_prefix_id');
        });

        Schema::dropIfExists('worker_prefixes');
    }
};
