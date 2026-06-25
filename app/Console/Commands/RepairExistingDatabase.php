<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RepairExistingDatabase extends Command
{
    protected $signature = 'db:repair-existing';

    protected $description = 'Repair existing database state without re-creating tables';

    public function handle(): int
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->warn('This repair command is intended for PostgreSQL only.');

            return self::SUCCESS;
        }

        $this->markExistingCreateMigrations();
        $this->syncKnownSequences();

        $this->info('Database repair completed.');

        return self::SUCCESS;
    }

    private function syncKnownSequences(): void
    {
        $tables = [
            'employers',
            'employer_user',
            'nationalities',
            'services',
            'job_orders',
            'workers',
            'worker_documents',
            'document_masters',
            'service_checklists',
            'job_order_checklists',
            'job_order_payments',
            'job_order_logs',
            'notifications',
            'activity_logs',
            'roles',
            'permissions',
            'news_categories',
            'news_posts',
            'document_evidences',
            'worker_prefixes',
            'job_order_statuses',
            'delivery_sheets',
            'delivery_sheet_items',
            'delivery_sheet_attachments',
            'about_us_blocks',
            'users',
            'jobs',
            'job_batches',
            'failed_jobs',
        ];

        foreach ($tables as $table) {
            if (! DB::getSchemaBuilder()->hasTable($table)) {
                continue;
            }

            $maxId = DB::table($table)->max('id');

            if ($maxId === null) {
                continue;
            }

            $sequence = DB::selectOne(
                "select pg_get_serial_sequence(?, 'id') as sequence_name",
                [$table]
            );

            $sequenceName = $sequence?->sequence_name ?? null;

            if (! $sequenceName) {
                continue;
            }

            DB::statement(
                "SELECT setval(?, ?, true)",
                [$sequenceName, $maxId]
            );

            $this->line('Synced ' . $table . ' id sequence to max(id) = ' . $maxId);
        }
    }

    private function markExistingCreateMigrations(): void
    {
        $migrations = [
            '2026_05_28_053808_create_employers_table' => ['employers'],
            '2026_05_28_053808_create_employer_user_table' => ['employer_user'],
            '2026_05_28_053809_create_nationalities_table' => ['nationalities'],
            '2026_05_28_053809_create_services_table' => ['services'],
            '2026_05_28_053809_create_job_orders_table' => ['job_orders'],
            '2026_05_28_053809_create_workers_table' => ['workers'],
            '2026_05_28_053809_create_worker_documents_table' => ['worker_documents'],
            '2026_05_28_053809_create_document_masters_table' => ['document_masters'],
            '2026_05_28_053809_create_service_checklists_table' => ['service_checklists'],
            '2026_05_28_053809_create_job_order_checklists_table' => ['job_order_checklists'],
            '2026_05_28_053810_create_job_order_payments_table' => ['job_order_payments'],
            '2026_05_28_053810_create_job_order_logs_table' => ['job_order_logs'],
            '2026_05_28_053810_create_notifications_table' => ['notifications'],
            '2026_05_28_053810_create_activity_logs_table' => ['activity_logs'],
            '2026_05_28_053916_create_permission_tables' => ['roles', 'permissions', 'model_has_permissions', 'model_has_roles', 'role_has_permissions'],
            '2026_05_29_000001_create_news_categories_table' => ['news_categories'],
            '2026_05_29_000002_create_news_posts_table' => ['news_posts'],
            '2026_06_01_000001_create_document_evidences_table' => ['document_evidences'],
            '2026_06_07_000001_create_worker_prefixes_table' => ['worker_prefixes'],
            '2026_06_07_000002_create_job_order_statuses_table' => ['job_order_statuses'],
            '2026_06_07_000003_create_delivery_sheets_table' => ['delivery_sheets'],
            '2026_06_07_000004_create_delivery_sheet_items_table' => ['delivery_sheet_items'],
            '2026_06_07_000005_create_delivery_sheet_attachments_table' => ['delivery_sheet_attachments'],
            '2026_06_08_000001_create_about_us_blocks_table' => ['about_us_blocks'],
            '0001_01_01_000000_create_users_table' => ['users'],
            '0001_01_01_000001_create_cache_table' => ['cache', 'cache_locks'],
            '0001_01_01_000002_create_jobs_table' => ['jobs', 'job_batches', 'failed_jobs'],
        ];

        $nextBatch = (int) DB::table('migrations')->max('batch') + 1;
        $recorded = 0;

        foreach ($migrations as $migration => $tables) {
            if (DB::table('migrations')->where('migration', $migration)->exists()) {
                continue;
            }

            $exists = true;

            foreach ($tables as $table) {
                if (! DB::getSchemaBuilder()->hasTable($table)) {
                    $exists = false;
                    break;
                }
            }

            if (! $exists) {
                continue;
            }

            DB::table('migrations')->insert([
                'migration' => $migration,
                'batch' => $nextBatch,
            ]);

            $recorded++;
            $this->line('Recorded existing migration as applied: ' . $migration);
        }

        if ($recorded === 0) {
            $this->line('No existing create migrations needed recording.');
        }
    }
}
