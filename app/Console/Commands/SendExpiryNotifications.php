<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Worker;
use App\Models\JobOrder;
use App\Models\JobOrderChecklist;
use App\Models\JobOrderPayment;
use App\Models\Employer;
use App\Services\LineMessagingService;
use App\Mail\ManagerDailySummary;
use App\Mail\EmployerExpiryAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendExpiryNotifications extends Command
{
    protected $signature = 'notify:expiry';
    protected $description = 'Automated alerts for expiring documents and business summary';
    protected string $timezone = 'Asia/Bangkok';

    public function handle(LineMessagingService $lineService)
    {
        $this->info('Starting automated notification sequence...');

        $expiryWindow = 45;
        $today = Carbon::now($this->timezone)->startOfDay();
        $limitDate = Carbon::now($this->timezone)->startOfDay()->addDays($expiryWindow);

        // 1. Fetch expired and expiring data grouped by employer
        $expiringWorkers = Worker::where('is_active', true)
            ->where(function ($query) use ($limitDate) {
                $query->whereDate('passport_expiry', '<=', $limitDate)
                    ->orWhereDate('visa_expiry', '<=', $limitDate)
                    ->orWhereDate('wp_expiry', '<=', $limitDate)
                    ->orWhereDate('report_90_days_due', '<=', $limitDate);
            })
            ->with('employer')
            ->get();

        // 2. Notify Employers via Email
        if ($expiringWorkers->isNotEmpty()) {
            $groupedByEmployer = $expiringWorkers->groupBy('employer_id');
            foreach ($groupedByEmployer as $employerId => $workers) {
                $employer = $workers->first()->employer;
                if ($employer && $employer->email) {
                    $alertData = $workers->map(function($w) use ($today, $limitDate) {
                        $docs = $this->collectDocumentAlerts($w, $today, $limitDate);
                        return ['name' => ($w->full_name_th ?: $w->full_name_en), 'docs' => $docs];
                    })->filter(fn ($worker) => count($worker['docs']) > 0);

                    Mail::to($employer->email)->send(new EmployerExpiryAlert($employer, $alertData));
                    $this->info('Email sent to employer: ' . $employer->company_name);
                }
            }
        }

        // 3. Notify Staff via Line Notify
        if ($expiringWorkers->isNotEmpty()) {
            $lineMessage = $this->buildLineExpiryMessage($expiringWorkers, $today, $limitDate);

            $notifiableUsers = User::whereNotNull('line_user_id')->get();
            foreach ($notifiableUsers as $user) {
                $lineService->send($user->line_user_id, $lineMessage);
            }
        }

        // 4. Daily Business Summary for Managers
        $summaryData = [
            'open_jobs' => JobOrder::whereIn('status', ['pending', 'processing', 'waiting_document', 'approved'])->count(),
            'pending_reviews' => JobOrderChecklist::where('status', 'received')->count() + JobOrderPayment::where('status', 'pending')->count(),
            'revenue_today' => JobOrderPayment::where('status', 'verified')->whereDate('updated_at', $today)->sum('amount'),
            'expiring_docs' => $expiringWorkers->count(),
        ];

        $managers = User::whereIn('role', ['manager', 'super_admin'])->where('enable_email_notifications', true)->get();
        foreach ($managers as $manager) {
            Mail::to($manager->email)->send(new ManagerDailySummary($summaryData));
            $this->info('Summary email sent to manager: ' . $manager->name);
        }

        $this->info('All notifications processed successfully.');
    }

    private function collectDocumentAlerts(Worker $worker, Carbon $today, Carbon $limitDate): array
    {
        $documents = [
            ['type' => 'Passport', 'date' => $worker->passport_expiry],
            ['type' => 'Visa', 'date' => $worker->visa_expiry],
            ['type' => 'Work Permit', 'date' => $worker->wp_expiry],
            ['type' => '90-Days Report', 'date' => $worker->report_90_days_due],
        ];

        $alerts = [];

        foreach ($documents as $document) {
            $expiryDate = $document['date'];

            if (! $expiryDate || $expiryDate->gt($limitDate)) {
                continue;
            }

            $alerts[] = [
                'type' => $document['type'],
                'expiry' => $expiryDate->format('d/m/Y'),
                'status' => $expiryDate->lt($today) ? 'หมดอายุแล้ว' : 'ใกล้หมดอายุ',
                'days' => $today->diffInDays($expiryDate, false),
            ];
        }

        return $alerts;
    }

    private function buildLineExpiryMessage($workers, Carbon $today, Carbon $limitDate): string
    {
        $expiredCount = 0;
        $expiringCount = 0;

        foreach ($workers as $worker) {
            foreach ($this->collectDocumentAlerts($worker, $today, $limitDate) as $document) {
                $document['days'] < 0 ? $expiredCount++ : $expiringCount++;
            }
        }

        $message = "\n📢 แจ้งเตือนเอกสารแรงงาน (" . $today->format('d/m/Y') . ")\n";
        $message .= "หมดอายุแล้ว: " . $expiredCount . " รายการ\n";
        $message .= "ใกล้หมดอายุ: " . $expiringCount . " รายการ\n";
        $message .= "------------------------------\n";

        foreach ($workers->take(10) as $worker) {
            $documents = $this->collectDocumentAlerts($worker, $today, $limitDate);

            if (count($documents) === 0) {
                continue;
            }

            $message .= "👤 " . ($worker->full_name_th ?: $worker->full_name_en) . "\n";
            $message .= "🏢 " . ($worker->employer?->company_name ?? '-') . "\n";

            foreach ($documents as $document) {
                $label = $document['days'] < 0
                    ? 'หมดอายุแล้ว ' . abs($document['days']) . ' วัน'
                    : ($document['days'] === 0 ? 'ครบกำหนดวันนี้' : 'เหลือ ' . $document['days'] . ' วัน');

                $message .= "- " . $document['type'] . ": " . $document['expiry'] . " (" . $label . ")\n";
            }
        }

        if ($workers->count() > 10) {
            $message .= "...และอื่นๆ\n";
        }

        $message .= "------------------------------\n";
        $message .= "🔗 ดูรายละเอียด: " . config('app.url') . "/staff-portal/calendar";

        return $message;
    }
}
