<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\JobOrder;
use App\Models\Employer;
use App\Models\Service;
use App\Models\Worker;
use App\Models\DocumentMaster;
use App\Models\JobOrderChecklist;
use App\Models\JobOrderLog;
use App\Models\JobOrderPayment;
use App\Models\AboutUsBlock;
use App\Models\NewsCategory;
use App\Models\NewsPost;
use App\Models\ServiceChecklist;
use App\Http\Controllers\StaffPortalController;
use App\Http\Controllers\LineWebhookController;
use App\Http\Controllers\EmployerWorkerRequestController;
use App\Filament\Pages\WorkersExpiryDashboard;
use App\Support\UploadLimits;

// LINE Webhook (ไม่ต้อง auth, ไม่ต้อง CSRF — LINE Platform จะ POST มา)
Route::post('/webhook/line', [LineWebhookController::class, 'handle'])
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
    ->name('webhook.line');

// Manager Dashboard (put this early to avoid route conflicts)
Route::get('/manager-dashboard', function () {
    try {
        $totalJobs = \App\Models\JobOrder::count();
        $processingJobs = \App\Models\JobOrder::whereIn('status', ['pending', 'processing', 'approved'])->count();
        $completedJobs = \App\Models\JobOrder::where('status', 'completed')->count();
        $pendingApprovals = \App\Models\JobOrder::whereIn('payment_status', ['pending', 'partial'])->count();

        $recentJobs = \App\Models\JobOrder::with(['worker', 'service', 'employer'])
            ->latest('updated_at')
            ->take(5)
            ->get();

        $recentActivities = \App\Models\JobOrderLog::with(['jobOrder.worker', 'jobOrder.service'])
            ->latest()
            ->take(5)
            ->get();

        $unreadNotifications = auth()->user()->notifications()->unread()->count();
        $recentNotifications = auth()->user()->notifications()
            ->latest()
            ->take(5)
            ->get();

        $pendingApprovalsList = collect([
            (object) [
                'title' => 'ตรวจเอกสารรออนุมัติ',
                'description' => \App\Models\JobOrderChecklist::where('status', 'received')->count() . ' รายการ',
                'action_url' => route('staff.portal.document-reviews.index'),
                'created_at' => now(),
            ],
            (object) [
                'title' => 'ตรวจสลิปรออนุมัติ',
                'description' => \App\Models\JobOrderPayment::where('status', 'pending')->count() . ' รายการ',
                'action_url' => route('staff.portal.payment-reviews.index'),
                'created_at' => now(),
            ],
        ]);

        return view('admin.manager-dashboard', compact(
            'totalJobs',
            'processingJobs',
            'completedJobs',
            'pendingApprovals',
            'recentJobs',
            'recentActivities',
            'unreadNotifications',
            'recentNotifications',
            'pendingApprovalsList',
        ));
    } catch (\Exception $e) {
        // Return a simple error view or redirect with error message
        return redirect()->route('admin.dashboard')->with('error', 'เกิดข้อผิดพลาดในการโหลดข้อมูล: ' . $e->getMessage());
    }
})->middleware('auth')->name('manager.dashboard');

Route::get('/manager/pending-approvals', function (Request $request) {
    $keyword = $request->string('q')->trim()->toString();

    $documentQuery = JobOrderChecklist::query()
        ->with(['jobOrder.employer', 'jobOrder.worker', 'documentMaster'])
        ->whereIn('status', ['received', 'missing', 'rejected', 'pending'])
        ->when($keyword !== '', function ($query) use ($keyword) {
            $query->where(function ($subQuery) use ($keyword) {
                $subQuery
                    ->whereHas('jobOrder', fn($jobQuery) => $jobQuery->where('job_number', 'like', "%{$keyword}%"))
                    ->orWhereHas('jobOrder.employer', fn($employerQuery) => $employerQuery->where('company_name', 'like', "%{$keyword}%"))
                    ->orWhereHas('jobOrder.worker', fn($workerQuery) => $workerQuery
                        ->where('full_name_th', 'like', "%{$keyword}%")
                        ->orWhere('full_name_en', 'like', "%{$keyword}%"))
                    ->orWhereHas('documentMaster', fn($documentQuery) => $documentQuery->where('name', 'like', "%{$keyword}%"));
            });
        })
        ->orderByRaw("case status when 'received' then 0 when 'rejected' then 1 when 'missing' then 2 else 3 end")
        ->latest('updated_at');

    $paymentQuery = JobOrderPayment::query()
        ->with(['jobOrder.employer', 'jobOrder.worker'])
        ->where('status', 'pending')
        ->when($keyword !== '', function ($query) use ($keyword) {
            $query->where(function ($subQuery) use ($keyword) {
                $subQuery
                    ->where('payment_reference', 'like', "%{$keyword}%")
                    ->orWhereHas('jobOrder', fn($jobQuery) => $jobQuery->where('job_number', 'like', "%{$keyword}%"))
                    ->orWhereHas('jobOrder.employer', fn($employerQuery) => $employerQuery->where('company_name', 'like', "%{$keyword}%"))
                    ->orWhereHas('jobOrder.worker', fn($workerQuery) => $workerQuery
                        ->where('full_name_th', 'like', "%{$keyword}%")
                        ->orWhere('full_name_en', 'like', "%{$keyword}%"));
            });
        })
        ->latest('payment_date');

    $summary = [
        'documents_received' => JobOrderChecklist::query()->where('status', 'received')->count(),
        'documents_pending' => JobOrderChecklist::query()->whereIn('status', ['pending', 'missing', 'rejected'])->count(),
        'payments_pending' => JobOrderPayment::query()->where('status', 'pending')->count(),
        'payments_amount' => JobOrderPayment::query()->where('status', 'pending')->sum('amount'),
    ];

    $documents = $documentQuery->paginate(8, ['*'], 'documents_page')->withQueryString();
    $payments = $paymentQuery->paginate(8, ['*'], 'payments_page')->withQueryString();

    return view('admin.pending-approvals.index', compact('keyword', 'summary', 'documents', 'payments'));
})->middleware(['auth', \App\Http\Middleware\EnsureAdmin::class])->name('manager.pending-approvals.index');

Route::get('/manager/job-orders', function (Request $request) {
    $keyword = $request->string('q')->trim()->toString();
    $status = $request->string('status')->toString();
    $priority = $request->string('priority')->toString();
    $paymentStatus = $request->string('payment_status')->toString();

    $jobOrders = JobOrder::query()
        ->with(['employer', 'worker', 'service', 'assignedUser'])
        ->withCount([
            'checklists as pending_documents_count' => fn($query) => $query->whereIn('status', ['pending', 'missing', 'rejected']),
            'checklists as received_documents_count' => fn($query) => $query->where('status', 'received'),
            'payments as pending_payments_count' => fn($query) => $query->where('status', 'pending'),
        ])
        ->when($keyword !== '', function ($query) use ($keyword) {
            $query->where(function ($subQuery) use ($keyword) {
                $subQuery
                    ->where('job_number', 'like', "%{$keyword}%")
                    ->orWhereHas('employer', fn($employerQuery) => $employerQuery->where('company_name', 'like', "%{$keyword}%"))
                    ->orWhereHas('worker', fn($workerQuery) => $workerQuery
                        ->where('first_name_th', 'like', "%{$keyword}%")
                        ->orWhere('last_name_th', 'like', "%{$keyword}%")
                        ->orWhere('first_name_en', 'like', "%{$keyword}%")
                        ->orWhere('last_name_en', 'like', "%{$keyword}%"));
            });
        })
        ->when($status !== '', fn($query) => $query->where('status', $status))
        ->when($priority !== '', fn($query) => $query->where('priority', $priority))
        ->when($paymentStatus !== '', fn($query) => $query->where('payment_status', $paymentStatus))
        ->orderByRaw("case priority when 'urgent' then 0 when 'high' then 1 when 'medium' then 2 else 3 end")
        ->latest('updated_at')
        ->paginate(15)
        ->withQueryString();

    $summary = [
        'total' => JobOrder::query()->count(),
        'open' => JobOrder::query()->whereIn('status', ['pending', 'processing', 'waiting_document', 'approved'])->count(),
        'waiting_document' => JobOrder::query()->where('status', 'waiting_document')->count(),
        'unpaid' => JobOrder::query()->whereIn('payment_status', ['pending', 'partial'])->count(),
    ];

    return view('admin.job-orders.index', compact(
        'jobOrders',
        'keyword',
        'status',
        'priority',
        'paymentStatus',
        'summary',
    ));
})->middleware(['auth', \App\Http\Middleware\EnsureAdmin::class])->name('manager.job-orders.index');

Route::get('/manager/job-orders/{jobOrder}', function (JobOrder $jobOrder) {
    $jobOrder->load([
        'employer',
        'worker.nationality',
        'service',
        'assignedUser',
        'checklists.documentMaster',
        'checklists.verifiedBy',
        'payments.receiver',
        'logs.user',
    ]);

    $summary = [
        'service_fee' => (float) $jobOrder->service_fee,
        'paid_amount' => (float) $jobOrder->paid_amount,
        'remaining_amount' => $jobOrder->getRemainingAmount(),
        'documents_total' => $jobOrder->checklists->count(),
        'documents_verified' => $jobOrder->checklists->where('status', 'verified')->count(),
        'payments_pending' => $jobOrder->payments->where('status', 'pending')->count(),
    ];

    return view('admin.job-orders.show', compact('jobOrder', 'summary'));
})->middleware(['auth', \App\Http\Middleware\EnsureAdmin::class])->name('manager.job-orders.show');

Route::get('/manager/reports/pipeline', function (Request $request) {
    $dateFrom = $request->string('date_from')->toString();
    $dateTo = $request->string('date_to')->toString();
    $employerId = $request->integer('employer_id', 0);
    $assignedUserId = $request->integer('assigned_user_id', 0);
    $priority = $request->string('priority')->toString();

    $baseQuery = JobOrder::query()
        ->when($dateFrom || $dateTo, function ($query) use ($dateFrom, $dateTo) {
            $start = $dateFrom ?: '1900-01-01';
            $end = $dateTo ?: now()->addYears(100)->format('Y-m-d');
            $query->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
        })
        ->when($employerId > 0, fn($query) => $query->where('employer_id', $employerId))
        ->when($assignedUserId > 0, fn($query) => $query->where('assigned_user_id', $assignedUserId))
        ->when($priority !== '', fn($query) => $query->where('priority', $priority));

    $statusLabels = [
        'pending' => 'รอเริ่มงาน',
        'processing' => 'กำลังดำเนินการ',
        'waiting_document' => 'รอเอกสาร',
        'approved' => 'อนุมัติแล้ว',
        'completed' => 'เสร็จสิ้น',
        'cancelled' => 'ยกเลิก',
        'rejected' => 'ไม่ผ่าน',
    ];

    $statusCounts = collect($statusLabels)->map(function ($label, $status) use ($baseQuery) {
        return [
            'status' => $status,
            'label' => $label,
            'count' => (clone $baseQuery)->where('status', $status)->count(),
        ];
    })->values();

    $total = (clone $baseQuery)->count();
    $open = (clone $baseQuery)->whereIn('status', ['pending', 'processing', 'waiting_document', 'approved'])->count();
    $overdue = (clone $baseQuery)
        ->whereNotNull('due_date')
        ->whereDate('due_date', '<', now())
        ->whereNotIn('status', ['completed', 'cancelled', 'rejected'])
        ->count();
    $completed = (clone $baseQuery)->where('status', 'completed')->count();

    $priorityCounts = collect(['urgent' => 'ด่วน', 'high' => 'สูง', 'medium' => 'ปานกลาง', 'low' => 'ต่ำ'])
        ->map(fn($label, $value) => [
            'value' => $value,
            'label' => $label,
            'count' => (clone $baseQuery)->where('priority', $value)->count(),
        ])->values();

    $attentionJobs = (clone $baseQuery)
        ->with(['employer', 'worker', 'service', 'assignedUser'])
        ->withCount([
            'checklists as received_documents_count' => fn($query) => $query->where('status', 'received'),
            'checklists as pending_documents_count' => fn($query) => $query->whereIn('status', ['pending', 'missing', 'rejected']),
            'payments as pending_payments_count' => fn($query) => $query->where('status', 'pending'),
        ])
        ->whereNotIn('status', ['completed', 'cancelled', 'rejected'])
        ->orderByRaw("case when due_date is not null and due_date < ? then 0 else 1 end", [now()->toDateString()])
        ->orderByRaw("case priority when 'urgent' then 0 when 'high' then 1 when 'medium' then 2 else 3 end")
        ->orderBy('due_date')
        ->take(12)
        ->get();

    $employers = Employer::orderBy('company_name')->pluck('company_name', 'id');
    $assignedUsers = \App\Models\User::whereHas('assignedJobOrders')->orderBy('name')->pluck('name', 'id');

    return view('admin.reports.pipeline', compact(
        'statusCounts',
        'statusLabels',
        'priorityCounts',
        'attentionJobs',
        'employers',
        'assignedUsers',
        'total',
        'open',
        'overdue',
        'completed',
    ));
})->middleware(['auth', \App\Http\Middleware\EnsureAdmin::class])->name('manager.reports.pipeline');

// Manager Reports - Expired Cards (list, filters, pagination)
Route::get('/manager/reports/expired-cards', function (Request $request) {
    $query = \App\Models\WorkerDocument::with(['worker', 'documentMaster'])
        ->whereNotNull('expiry_date');

    $withinDays = (int) $request->query('within_days', 30);

    // Filters
    $dateFrom = $request->string('date_from')->toString();
    $dateTo = $request->string('date_to')->toString();
    $search = $request->string('search')->trim()->toString();
    $status = $request->string('status')->toString();

    // If user supplied a date range, include documents whose expiry falls in that range
    // OR documents that will expire within the next $withinDays days. This makes the
    // result a union of the requested range plus near-expiry items.
    if ($dateFrom || $dateTo) {
        $start = $dateFrom ?: '1900-01-01';
        $end = $dateTo ?: now()->addYears(100)->format('Y-m-d');

        $query->where(function ($q) use ($start, $end, $withinDays) {
            $q->whereBetween('expiry_date', [$start, $end])
                ->orWhereDate('expiry_date', '<=', now()->addDays($withinDays));
        });
    } else {
        // Default: show expired or expiring within N days
        $query->whereDate('expiry_date', '<=', now()->addDays($withinDays));
    }

    // Search filter applies to the whole result set
    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->whereHas('worker', fn($w) => $w->where('full_name_th', 'like', "%{$search}%")
                ->orWhere('full_name_en', 'like', "%{$search}%"))
                ->orWhereHas('documentMaster', fn($d) => $d->where('name', 'like', "%{$search}%"))
                ->orWhere('worker_id', 'like', "%{$search}%");
        });
    }

    $totalCount = (clone $query)->count();

    // Breakdown: how many are already expired vs will expire within the window
    $expiredCount = (clone $query)->whereDate('expiry_date', '<', now())->count();
    $nearCount = max(0, $totalCount - $expiredCount);

    $perPage = (int) $request->query('per_page', 15);
    $expired = $query->orderBy('expiry_date', 'asc')
        ->paginate($perPage)
        ->withQueryString();

    // Build small dashboard-like stats to match staff-portal visuals
    $stats = [
        [
            'label' => 'รวมรายการเตือน',
            'value' => $totalCount,
            'description' => 'หมดอายุ / ใกล้หมด',
            'icon' => 'alert-triangle',
            'tone' => 'blue',
        ],
        [
            'label' => 'หมดอายุแล้ว',
            'value' => $expiredCount,
            'description' => 'รายการที่หมดอายุ ณ วันนี้',
            'icon' => 'clock',
            'tone' => 'rose',
        ],
        [
            'label' => 'ใกล้หมดภายใน',
            'value' => $nearCount,
            'description' => 'ภายใน ' . $withinDays . ' วัน',
            'icon' => 'calendar-days',
            'tone' => 'blue',
        ],
    ];

    $pendingReviews = \App\Models\JobOrderChecklist::where('status', 'received')->latest()->take(5)->get();
    $pendingPayments = \App\Models\JobOrderPayment::where('status', 'pending')->latest()->take(5)->get();
    $openJobs = \App\Models\JobOrder::with(['worker', 'employer'])->where('status', '!=', 'completed')->latest()->take(6)->get();

    // Build combined expiring items (worker card dates + document records) to match staff-portal view
    $limitDays = $withinDays;
    $limitDate = now()->copy()->addDays($limitDays)->endOfDay();

    $workerDates = \App\Models\Worker::query()
        ->with('employer')
        ->active()
        ->where(function ($q) use ($limitDate) {
            $q->whereDate('passport_expiry', '<=', $limitDate)
                ->orWhereDate('wp_expiry', '<=', $limitDate)
                ->orWhereDate('visa_expiry', '<=', $limitDate)
                ->orWhereDate('report_90_days_due', '<=', $limitDate);
        })
        ->limit(100)
        ->get()
        ->flatMap(function ($worker) {
            return collect([
                ['document' => 'Passport', 'date' => $worker->passport_expiry],
                ['document' => 'Work Permit', 'date' => $worker->wp_expiry],
                ['document' => 'Visa', 'date' => $worker->visa_expiry],
                ['document' => '90 Days Report', 'date' => $worker->report_90_days_due],
            ])->filter(fn($item) => $item['date'] instanceof \Illuminate\Support\Carbon)
                ->map(fn($item) => [
                    'worker' => $worker->full_name_th ?: $worker->full_name_en,
                    'employer' => $worker->employer?->company_name ?? '-',
                    'document' => $item['document'],
                    'expiry_date' => $item['date'],
                    'passport_number' => $worker->passport_number ?? '',
                    'wp_number' => $worker->wp_number ?? '',
                    'days_until_expiry' => ($item['date']->isPast() ? -$item['date']->diffInDays(now()) : $item['date']->diffInDays(now())),
                    'source' => 'worker',
                ])
                ->all();
        });

    $documents = (clone $query)->orderBy('expiry_date', 'asc')->get()->map(function ($doc) {
        return [
            'worker' => $doc->worker?->full_name_th ?: $doc->worker?->full_name_en ?: '-',
            'employer' => $doc->worker?->employer?->company_name ?? '-',
            'document' => $doc->documentMaster?->name ?? 'เอกสาร',
            'expiry_date' => $doc->expiry_date,
            'passport_number' => $doc->worker?->passport_number ?? '',
            'wp_number' => $doc->worker?->wp_number ?? '',
            'source' => 'document',
        ];
    });

    $allExpiringItems = $workerDates->merge($documents)->sortBy('expiry_date')->values();
    $currentPage = max(1, (int) request('page', 1));
    $expiringItems = new \Illuminate\Pagination\LengthAwarePaginator(
        $allExpiringItems->forPage($currentPage, $perPage)->values(),
        $allExpiringItems->count(),
        $perPage,
        $currentPage,
        [
            'path' => request()->url(),
            'query' => request()->query(),
        ]
    );

    return view('admin.reports.expired-cards', compact('expired', 'expiringItems', 'totalCount', 'expiredCount', 'nearCount', 'stats', 'pendingReviews', 'pendingPayments', 'openJobs'));
})->middleware(['auth', \App\Http\Middleware\EnsureAdmin::class])->name('manager.reports.expired_cards');

// Export CSV
Route::get('/manager/reports/expired-cards/export/csv', function (Request $request) {
    $query = \App\Models\WorkerDocument::with(['worker', 'documentMaster'])
        ->whereNotNull('expiry_date');

    $withinDays = (int) $request->query('within_days', 30);

    // CSV export: apply same union logic as the listing
    $dateFrom = $request->string('date_from')->toString();
    $dateTo = $request->string('date_to')->toString();

    if ($dateFrom || $dateTo) {
        $start = $dateFrom ?: '1900-01-01';
        $end = $dateTo ?: now()->addYears(100)->format('Y-m-d');

        $query->where(function ($q) use ($start, $end, $withinDays) {
            $q->whereBetween('expiry_date', [$start, $end])
                ->orWhereDate('expiry_date', '<=', now()->addDays($withinDays));
        });
    } else {
        $query->whereDate('expiry_date', '<=', now()->addDays($withinDays));
    }
    if ($request->string('search')->trim()->toString() !== '') {
        $search = $request->string('search')->trim()->toString();
        $query->where(function ($q) use ($search) {
            $q->whereHas('worker', fn($w) => $w->where('full_name_th', 'like', "%{$search}%")
                ->orWhere('full_name_en', 'like', "%{$search}%"))
                ->orWhereHas('documentMaster', fn($d) => $d->where('name', 'like', "%{$search}%"))
                ->orWhere('worker_id', 'like', "%{$search}%");
        });
    }

    $documents = $query->orderBy('expiry_date', 'asc')->get();

    // Build worker-derived expiry items with same union logic
    $workerQuery = \App\Models\Worker::query()->with('employer')->active();

    if ($dateFrom || $dateTo) {
        $start = $dateFrom ?: '1900-01-01';
        $end = $dateTo ?: now()->addYears(100)->format('Y-m-d');

        $workerQuery->where(function ($q) use ($start, $end, $withinDays) {
            $limitDate = now()->addDays($withinDays);
            $q->whereBetween('passport_expiry', [$start, $end])
                ->orWhereBetween('wp_expiry', [$start, $end])
                ->orWhereBetween('visa_expiry', [$start, $end])
                ->orWhereBetween('report_90_days_due', [$start, $end])
                ->orWhereDate('passport_expiry', '<=', $limitDate)
                ->orWhereDate('wp_expiry', '<=', $limitDate)
                ->orWhereDate('visa_expiry', '<=', $limitDate)
                ->orWhereDate('report_90_days_due', '<=', $limitDate);
        });
    } else {
        $limitDate = now()->addDays($withinDays);
        $workerQuery->where(function ($q) use ($limitDate) {
            $q->whereDate('passport_expiry', '<=', $limitDate)
                ->orWhereDate('wp_expiry', '<=', $limitDate)
                ->orWhereDate('visa_expiry', '<=', $limitDate)
                ->orWhereDate('report_90_days_due', '<=', $limitDate);
        });
    }

    $workerDates = $workerQuery->limit(200)->get()->flatMap(function ($worker) {
        return collect([
            ['document' => 'Passport', 'date' => $worker->passport_expiry],
            ['document' => 'Work Permit', 'date' => $worker->wp_expiry],
            ['document' => 'Visa', 'date' => $worker->visa_expiry],
            ['document' => '90 Days Report', 'date' => $worker->report_90_days_due],
        ])->filter(fn($it) => $it['date'] instanceof \Illuminate\Support\Carbon)
            ->map(fn($it) => [
                'worker' => $worker->full_name_th ?: $worker->full_name_en,
                'employer' => $worker->employer?->company_name ?? '-',
                'document' => $it['document'],
                'expiry_date' => $it['date'],
                'passport_number' => $worker->passport_number ?? '',
                'wp_number' => $worker->wp_number ?? '',
                'reference' => null,
                'note' => null,
                'days_until_expiry' => ($it['date']->isPast() ? -$it['date']->diffInDays(now()) : $it['date']->diffInDays(now())),
                'source' => 'worker',
            ])->all();
    });

    // Apply search filter to workerDates if search provided
    if (! empty($search)) {
        $workerDates = collect($workerDates)->filter(function ($it) use ($search) {
            return str_contains(strtolower($it['worker']), strtolower($search))
                || str_contains(strtolower($it['employer'] ?? ''), strtolower($search))
                || str_contains(strtolower($it['document']), strtolower($search));
        })->values();
    }

    // Merge documents and workerDates into unified items array
    $docItems = $documents->map(function ($doc) {
        return [
            'worker' => $doc->worker?->full_name_th ?? ($doc->worker?->full_name_en ?? ('Worker #' . $doc->worker_id)),
            'employer' => $doc->worker?->employer?->company_name ?? '-',
            'document' => $doc->documentMaster?->name ?? 'เอกสาร',
            'expiry_date' => $doc->expiry_date,
            'reference' => $doc->id,
            'passport_number' => $doc->worker?->passport_number ?? '',
            'wp_number' => $doc->worker?->wp_number ?? '',
            'note' => $doc->note ?? '',
            'days_until_expiry' => ($doc->expiry_date->isPast() ? -$doc->expiry_date->diffInDays(now()) : $doc->expiry_date->diffInDays(now())),
            'source' => 'document',
        ];
    });

    $items = $workerDates->merge($docItems)->sortBy('expiry_date')->values();

    $filename = 'expired-cards-' . now()->format('Ymd_His') . '.csv';

    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ];

    $callback = function () use ($items) {
        $handle = fopen('php://output', 'w');
        // UTF-8 BOM for Excel on Windows
        fwrite($handle, "\xEF\xBB\xBF");

        $header = ['แรงงาน', 'นายจ้าง', 'ประเภทเอกสาร', 'หมายเลขเอกสาร', 'passport_number', 'wp_number', 'วันที่หมดอายุ', 'วันเหลือก่อนหมด', 'แหล่งที่มา', 'หมายเหตุ'];
        $tmp = fopen('php://temp', 'r+');
        fputcsv($tmp, $header);
        rewind($tmp);
        $hline = stream_get_contents($tmp);
        fclose($tmp);
        fwrite($handle, rtrim($hline, "\n") . "\r\n");

        foreach ($items as $row) {
            $line = [
                $row['worker'] ?? '-',
                $row['employer'] ?? '-',
                $row['document'] ?? '-',
                $row['reference'] ?? '',
                $row['passport_number'] ?? '',
                $row['wp_number'] ?? '',
                isset($row['expiry_date']) ? $row['expiry_date']->format('Y-m-d') : '',
                isset($row['days_until_expiry']) ? $row['days_until_expiry'] : (isset($row['expiry_date']) ? ($row['expiry_date']->isPast() ? -$row['expiry_date']->diffInDays(now()) : $row['expiry_date']->diffInDays(now())) : ''),
                $row['source'] ?? '',
                $row['note'] ?? '',
            ];

            $tmp = fopen('php://temp', 'r+');
            fputcsv($tmp, $line);
            rewind($tmp);
            $csvLine = stream_get_contents($tmp);
            fclose($tmp);
            fwrite($handle, rtrim($csvLine, "\n") . "\r\n");
        }

        fclose($handle);
    };

    return response()->stream($callback, 200, $headers);
})->middleware(['auth', \App\Http\Middleware\EnsureAdmin::class])->name('manager.reports.expired_cards.export.csv');

// Export PDF (requires barryvdh/laravel-dompdf)
Route::get('/manager/reports/expired-cards/export/pdf', function (Request $request) {
    if (! app()->bound('dompdf.wrapper')) {
        return redirect()->back()->with('error', 'ต้องติดตั้ง barryvdh/laravel-dompdf เพื่อใช้งานการส่งออก PDF');
    }


    $query = \App\Models\WorkerDocument::with(['worker', 'documentMaster'])
        ->whereNotNull('expiry_date');
    $withinDays = (int) $request->query('within_days', 30);

    // PDF export: apply same union logic as the listing
    $dateFrom = $request->string('date_from')->toString();
    $dateTo = $request->string('date_to')->toString();

    if ($dateFrom || $dateTo) {
        $start = $dateFrom ?: '1900-01-01';
        $end = $dateTo ?: now()->addYears(100)->format('Y-m-d');

        $query->where(function ($q) use ($start, $end, $withinDays) {
            $q->whereBetween('expiry_date', [$start, $end])
                ->orWhereDate('expiry_date', '<=', now()->addDays($withinDays));
        });
    } else {
        $query->whereDate('expiry_date', '<=', now()->addDays($withinDays));
    }
    if ($request->string('search')->trim()->toString() !== '') {
        $search = $request->string('search')->trim()->toString();
        $query->where(function ($q) use ($search) {
            $q->whereHas('worker', fn($w) => $w->where('full_name_th', 'like', "%{$search}%")
                ->orWhere('full_name_en', 'like', "%{$search}%"))
                ->orWhereHas('documentMaster', fn($d) => $d->where('name', 'like', "%{$search}%"))
                ->orWhere('worker_id', 'like', "%{$search}%");
        });
    }

    $documents = $query->orderBy('expiry_date', 'asc')->get();

    // Build worker items same as CSV export
    $workerQuery = \App\Models\Worker::query()->with('employer')->active();
    if ($dateFrom || $dateTo) {
        $start = $dateFrom ?: '1900-01-01';
        $end = $dateTo ?: now()->addYears(100)->format('Y-m-d');

        $workerQuery->where(function ($q) use ($start, $end, $withinDays) {
            $limitDate = now()->addDays($withinDays);
            $q->whereBetween('passport_expiry', [$start, $end])
                ->orWhereBetween('wp_expiry', [$start, $end])
                ->orWhereBetween('visa_expiry', [$start, $end])
                ->orWhereBetween('report_90_days_due', [$start, $end])
                ->orWhereDate('passport_expiry', '<=', $limitDate)
                ->orWhereDate('wp_expiry', '<=', $limitDate)
                ->orWhereDate('visa_expiry', '<=', $limitDate)
                ->orWhereDate('report_90_days_due', '<=', $limitDate);
        });
    } else {
        $limitDate = now()->addDays($withinDays);
        $workerQuery->where(function ($q) use ($limitDate) {
            $q->whereDate('passport_expiry', '<=', $limitDate)
                ->orWhereDate('wp_expiry', '<=', $limitDate)
                ->orWhereDate('visa_expiry', '<=', $limitDate)
                ->orWhereDate('report_90_days_due', '<=', $limitDate);
        });
    }

    $workerDates = $workerQuery->limit(200)->get()->flatMap(function ($worker) {
        return collect([
            ['document' => 'Passport', 'date' => $worker->passport_expiry],
            ['document' => 'Work Permit', 'date' => $worker->wp_expiry],
            ['document' => 'Visa', 'date' => $worker->visa_expiry],
            ['document' => '90 Days Report', 'date' => $worker->report_90_days_due],
        ])->filter(fn($it) => $it['date'] instanceof \Illuminate\Support\Carbon)
            ->map(fn($it) => [
                'worker' => $worker->full_name_th ?: $worker->full_name_en,
                'employer' => $worker->employer?->company_name ?? '-',
                'document' => $it['document'],
                'expiry_date' => $it['date'],
                'reference' => null,
                'note' => null,
            ])->all();
    });

    $docItems = $documents->map(function ($doc) {
        return [
            'worker' => $doc->worker?->full_name_th ?? ($doc->worker?->full_name_en ?? ('Worker #' . $doc->worker_id)),
            'employer' => $doc->worker?->employer?->company_name ?? '-',
            'document' => $doc->documentMaster?->name ?? 'เอกสาร',
            'expiry_date' => $doc->expiry_date,
            'reference' => $doc->id,
            'note' => $doc->note ?? '',
        ];
    });

    $items = $workerDates->merge($docItems)->sortBy('expiry_date')->values();

    $html = view('admin.reports.expired-cards-pdf', compact('items'))->render();
    $pdf = app('dompdf.wrapper');
    $pdf->loadHtml($html);

    return $pdf->download('expired-cards-' . now()->format('Ymd_His') . '.pdf');
})->middleware(['auth', \App\Http\Middleware\EnsureAdmin::class])->name('manager.reports.expired_cards.export.pdf');

// Manager: Workers report page
Route::get('/manager/reports/workers', function (Request $request) {
    $withinDays = (int) $request->query('within_days', 30);
    $dateFrom = $request->string('date_from')->toString();
    $dateTo = $request->string('date_to')->toString();
    $search = $request->string('search')->trim()->toString();
    $employerId = $request->integer('employer_id', 0);

    $query = \App\Models\Worker::with('employer');
    $limitDate = now()->addDays($withinDays);

    // Apply employer filter
    if ($employerId) {
        $query->where('employer_id', $employerId);
    }

    // Union logic: date range OR within_days
    if ($dateFrom || $dateTo) {
        $start = $dateFrom ?: '1900-01-01';
        $end = $dateTo ?: now()->addYears(100)->format('Y-m-d');

        $query->where(function ($q) use ($start, $end, $withinDays) {
            $limitDate = now()->addDays($withinDays);
            $q->whereBetween('passport_expiry', [$start, $end])
                ->orWhereBetween('wp_expiry', [$start, $end])
                ->orWhereBetween('visa_expiry', [$start, $end])
                ->orWhereBetween('report_90_days_due', [$start, $end])
                ->orWhereDate('passport_expiry', '<=', $limitDate)
                ->orWhereDate('wp_expiry', '<=', $limitDate)
                ->orWhereDate('visa_expiry', '<=', $limitDate)
                ->orWhereDate('report_90_days_due', '<=', $limitDate);
        });
    } else {
        $limitDate = now()->addDays($withinDays);
        $query->where(function ($q) use ($limitDate) {
            $q->whereDate('passport_expiry', '<=', $limitDate)
                ->orWhereDate('wp_expiry', '<=', $limitDate)
                ->orWhereDate('visa_expiry', '<=', $limitDate)
                ->orWhereDate('report_90_days_due', '<=', $limitDate);
        });
    }

    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->where('first_name_th', 'like', "%{$search}%")
                ->orWhere('last_name_th', 'like', "%{$search}%")
                ->orWhere('first_name_en', 'like', "%{$search}%")
                ->orWhere('last_name_en', 'like', "%{$search}%")
                ->orWhere('passport_number', 'like', "%{$search}%")
                ->orWhere('wp_number', 'like', "%{$search}%");
        });
    }

    // Stats: total, expired, near (<= withinDays)
    $totalWorkers = (clone $query)->count();
    $expiredCount = (clone $query)->where(function ($q) {
        $q->whereDate('passport_expiry', '<', now())
            ->orWhereDate('wp_expiry', '<', now())
            ->orWhereDate('visa_expiry', '<', now())
            ->orWhereDate('report_90_days_due', '<', now());
    })->count();

    $nearCount = (clone $query)->where(function ($q) use ($limitDate) {
        $q->whereBetween('passport_expiry', [now(), $limitDate])
            ->orWhereBetween('wp_expiry', [now(), $limitDate])
            ->orWhereBetween('visa_expiry', [now(), $limitDate])
            ->orWhereBetween('report_90_days_due', [now(), $limitDate]);
    })->count();

    $stats = [
        ['label' => 'แรงงานทั้งหมด', 'value' => $totalWorkers, 'description' => 'รวมแรงงานทั้งหมด', 'tone' => 'blue', 'icon' => 'users'],
        ['label' => 'หมดอายุแล้ว', 'value' => $expiredCount, 'description' => 'เอกสารหมดอายุ', 'tone' => 'rose', 'icon' => 'alert-circle'],
        ['label' => 'ใกล้หมด (' . $withinDays . ' วัน)', 'value' => $nearCount, 'description' => 'จะหมดในช่วงเวลา', 'tone' => 'amber', 'icon' => 'clock'],
    ];

    $workers = $query->orderBy('first_name_th')->paginate(20)->withQueryString();

    $employers = \App\Models\Employer::orderBy('company_name')->get();

    return view('admin.reports.workers', compact('workers', 'employers', 'stats'));
})->middleware(['auth', \App\Http\Middleware\EnsureAdmin::class])->name('manager.reports.workers');

// Export Workers CSV
Route::get('/manager/reports/workers/export/csv', function (Request $request) {
    $withinDays = (int) $request->query('within_days', 30);
    $dateFrom = $request->string('date_from')->toString();
    $dateTo = $request->string('date_to')->toString();
    $search = $request->string('search')->trim()->toString();
    $employerId = $request->integer('employer_id', 0);

    $query = \App\Models\Worker::with('employer');

    if ($employerId) {
        $query->where('employer_id', $employerId);
    }

    if ($dateFrom || $dateTo) {
        $start = $dateFrom ?: '1900-01-01';
        $end = $dateTo ?: now()->addYears(100)->format('Y-m-d');

        $query->where(function ($q) use ($start, $end, $withinDays) {
            $limitDate = now()->addDays($withinDays);
            $q->whereBetween('passport_expiry', [$start, $end])
                ->orWhereBetween('wp_expiry', [$start, $end])
                ->orWhereBetween('visa_expiry', [$start, $end])
                ->orWhereBetween('report_90_days_due', [$start, $end])
                ->orWhereDate('passport_expiry', '<=', $limitDate)
                ->orWhereDate('wp_expiry', '<=', $limitDate)
                ->orWhereDate('visa_expiry', '<=', $limitDate)
                ->orWhereDate('report_90_days_due', '<=', $limitDate);
        });
    } else {
        $limitDate = now()->addDays($withinDays);
        $query->where(function ($q) use ($limitDate) {
            $q->whereDate('passport_expiry', '<=', $limitDate)
                ->orWhereDate('wp_expiry', '<=', $limitDate)
                ->orWhereDate('visa_expiry', '<=', $limitDate)
                ->orWhereDate('report_90_days_due', '<=', $limitDate);
        });
    }

    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->where('first_name_th', 'like', "%{$search}%")
                ->orWhere('last_name_th', 'like', "%{$search}%")
                ->orWhere('first_name_en', 'like', "%{$search}%")
                ->orWhere('last_name_en', 'like', "%{$search}%")
                ->orWhere('passport_number', 'like', "%{$search}%")
                ->orWhere('wp_number', 'like', "%{$search}%");
        });
    }

    $workers = $query->orderBy('first_name_th')->get();

    $filename = 'workers-report-' . now()->format('Ymd_His') . '.csv';
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ];

    $callback = function () use ($workers) {
        $handle = fopen('php://output', 'w');
        fwrite($handle, "\xEF\xBB\xBF");

        $header = ['แรงงาน', 'นายจ้าง', 'passport_number', 'wp_number', 'passport_expiry', 'wp_expiry', 'visa_expiry', 'report_90_days_due'];
        $tmp = fopen('php://temp', 'r+');
        fputcsv($tmp, $header);
        rewind($tmp);
        $hline = stream_get_contents($tmp);
        fclose($tmp);
        fwrite($handle, rtrim($hline, "\n") . "\r\n");

        foreach ($workers as $w) {
            $row = [
                ($w->full_name_th ?: $w->full_name_en),
                $w->employer?->company_name ?? '-',
                $w->passport_number ?? '',
                $w->wp_number ?? '',
                $w->passport_expiry?->format('Y-m-d') ?? '',
                $w->wp_expiry?->format('Y-m-d') ?? '',
                $w->visa_expiry?->format('Y-m-d') ?? '',
                $w->report_90_days_due?->format('Y-m-d') ?? '',
            ];

            $tmp = fopen('php://temp', 'r+');
            fputcsv($tmp, $row);
            rewind($tmp);
            $line = stream_get_contents($tmp);
            fclose($tmp);
            fwrite($handle, rtrim($line, "\n") . "\r\n");
        }

        fclose($handle);
    };

    return response()->stream($callback, 200, $headers);
})->middleware(['auth', \App\Http\Middleware\EnsureAdmin::class])->name('manager.reports.workers.export.csv');

// Export Workers PDF
Route::get('/manager/reports/workers/export/pdf', function (Request $request) {
    if (! app()->bound('dompdf.wrapper')) {
        return redirect()->back()->with('error', 'ต้องติดตั้ง barryvdh/laravel-dompdf เพื่อใช้งานการส่งออก PDF');
    }

    $withinDays = (int) $request->query('within_days', 30);
    $dateFrom = $request->string('date_from')->toString();
    $dateTo = $request->string('date_to')->toString();
    $search = $request->string('search')->trim()->toString();
    $employerId = $request->integer('employer_id', 0);

    $query = \App\Models\Worker::with('employer');
    if ($employerId) {
        $query->where('employer_id', $employerId);
    }

    if ($dateFrom || $dateTo) {
        $start = $dateFrom ?: '1900-01-01';
        $end = $dateTo ?: now()->addYears(100)->format('Y-m-d');

        $query->where(function ($q) use ($start, $end, $withinDays) {
            $limitDate = now()->addDays($withinDays);
            $q->whereBetween('passport_expiry', [$start, $end])
                ->orWhereBetween('wp_expiry', [$start, $end])
                ->orWhereBetween('visa_expiry', [$start, $end])
                ->orWhereBetween('report_90_days_due', [$start, $end])
                ->orWhereDate('passport_expiry', '<=', $limitDate)
                ->orWhereDate('wp_expiry', '<=', $limitDate)
                ->orWhereDate('visa_expiry', '<=', $limitDate)
                ->orWhereDate('report_90_days_due', '<=', $limitDate);
        });
    } else {
        $limitDate = now()->addDays($withinDays);
        $query->where(function ($q) use ($limitDate) {
            $q->whereDate('passport_expiry', '<=', $limitDate)
                ->orWhereDate('wp_expiry', '<=', $limitDate)
                ->orWhereDate('visa_expiry', '<=', $limitDate)
                ->orWhereDate('report_90_days_due', '<=', $limitDate);
        });
    }

    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->where('first_name_th', 'like', "%{$search}%")
                ->orWhere('last_name_th', 'like', "%{$search}%")
                ->orWhere('first_name_en', 'like', "%{$search}%")
                ->orWhere('last_name_en', 'like', "%{$search}%")
                ->orWhere('passport_number', 'like', "%{$search}%")
                ->orWhere('wp_number', 'like', "%{$search}%");
        });
    }

    $workers = $query->orderBy('first_name_th')->get();

    $html = view('admin.reports.workers-pdf', compact('workers'))->render();
    $pdf = app('dompdf.wrapper');
    $pdf->loadHtml($html);

    return $pdf->download('workers-report-' . now()->format('Ymd_His') . '.pdf');
})->middleware(['auth', \App\Http\Middleware\EnsureAdmin::class])->name('manager.reports.workers.export.pdf');

// Manager Financial Report (payments)
Route::get('/manager/reports/financial', function (Request $request) {
    $query = \App\Models\JobOrderPayment::with(['jobOrder.worker', 'jobOrder.employer']);

    $dateFrom = $request->string('date_from')->toString();
    $dateTo = $request->string('date_to')->toString();
    $search = $request->string('search')->trim()->toString();
    $status = $request->string('status')->toString();

    if ($dateFrom || $dateTo) {
        $start = $dateFrom ?: '1900-01-01';
        $end = $dateTo ?: now()->addYears(100)->format('Y-m-d');
        $query->whereBetween('payment_date', [$start, $end]);
    }

    if ($request->filled('employer_id')) {
        $query->whereHas('jobOrder', fn($q) => $q->where('employer_id', $request->employer_id));
    }

    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->where('payment_reference', 'like', "%{$search}%")
                ->orWhere('payment_method', 'like', "%{$search}%")
                ->orWhereHas('jobOrder', fn($j) => $j->where('job_number', 'like', "%{$search}%"));
        });
    }

    if ($status !== '') {
        $query->where('status', $status);
    }

    $totalPayments = (clone $query)->sum('amount');
    $verifiedTotal = (clone $query)->where('status', 'verified')->sum('amount');
    $pendingTotal = (clone $query)->where('status', '!=', 'verified')->sum('amount');
    $pendingCount = (clone $query)->where('status', '!=', 'verified')->count();

    $perPage = (int) $request->query('per_page', 20);
    $payments = $query->orderBy('payment_date', 'desc')->paginate($perPage)->withQueryString();

    $employers = \App\Models\Employer::pluck('company_name', 'id');

    return view('admin.reports.financial', compact('payments', 'totalPayments', 'verifiedTotal', 'pendingTotal', 'pendingCount', 'employers'));
})->middleware(['auth', \App\Http\Middleware\EnsureAdmin::class])->name('manager.reports.financial');

// Export CSV
Route::get('/manager/reports/financial/export/csv', function (Request $request) {
    $query = \App\Models\JobOrderPayment::with(['jobOrder.worker', 'jobOrder.employer']);

    $dateFrom = $request->string('date_from')->toString();
    $dateTo = $request->string('date_to')->toString();
    $search = $request->string('search')->trim()->toString();
    $status = $request->string('status')->toString();
    if ($dateFrom || $dateTo) {
        $start = $dateFrom ?: '1900-01-01';
        $end = $dateTo ?: now()->addYears(100)->format('Y-m-d');
        $query->whereBetween('payment_date', [$start, $end]);
    }
    if ($request->filled('employer_id')) {
        $query->whereHas('jobOrder', fn($q) => $q->where('employer_id', $request->employer_id));
    }
    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->where('payment_reference', 'like', "%{$search}%")
                ->orWhere('payment_method', 'like', "%{$search}%")
                ->orWhereHas('jobOrder', fn($j) => $j->where('job_number', 'like', "%{$search}%"));
        });
    }
    if ($status !== '') {
        $query->where('status', $status);
    }

    $payments = $query->orderBy('payment_date', 'desc')->get();

    $filename = 'financial-report-' . now()->format('Ymd_His') . '.csv';
    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ];

    $callback = function () use ($payments) {
        $handle = fopen('php://output', 'w');
        // BOM for Excel UTF-8
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, ['Date', 'Job Number', 'Employer', 'Worker', 'Amount', 'Method', 'Reference', 'Status']);

        foreach ($payments as $p) {
            fputcsv($handle, [
                optional($p->payment_date)->format('Y-m-d'),
                $p->jobOrder?->job_number ?? '-',
                $p->jobOrder?->employer?->company_name ?? '-',
                $p->jobOrder?->worker?->full_name_th ?? $p->jobOrder?->worker?->full_name_en ?? '-',
                number_format($p->amount, 2),
                $p->payment_method,
                $p->payment_reference,
                $p->status,
            ]);
        }
        fclose($handle);
    };

    return response()->stream($callback, 200, $headers);
})->middleware(['auth', \App\Http\Middleware\EnsureAdmin::class])->name('manager.reports.financial.export.csv');

// Export PDF
Route::get('/manager/reports/financial/export/pdf', function (Request $request) {
    if (! app()->bound('dompdf.wrapper')) {
        return redirect()->back()->with('error', 'ต้องติดตั้ง barryvdh/laravel-dompdf เพื่อใช้งานการส่งออก PDF');
    }

    $query = \App\Models\JobOrderPayment::with(['jobOrder.worker', 'jobOrder.employer']);
    $dateFrom = $request->string('date_from')->toString();
    $dateTo = $request->string('date_to')->toString();
    $search = $request->string('search')->trim()->toString();
    $status = $request->string('status')->toString();
    if ($dateFrom || $dateTo) {
        $start = $dateFrom ?: '1900-01-01';
        $end = $dateTo ?: now()->addYears(100)->format('Y-m-d');
        $query->whereBetween('payment_date', [$start, $end]);
    }
    if ($request->filled('employer_id')) {
        $query->whereHas('jobOrder', fn($q) => $q->where('employer_id', $request->employer_id));
    }
    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->where('payment_reference', 'like', "%{$search}%")
                ->orWhere('payment_method', 'like', "%{$search}%")
                ->orWhereHas('jobOrder', fn($j) => $j->where('job_number', 'like', "%{$search}%"));
        });
    }
    if ($status !== '') {
        $query->where('status', $status);
    }

    $payments = $query->orderBy('payment_date', 'desc')->get();

    $html = view('admin.reports.financial-pdf', compact('payments'))->render();
    $pdf = app('dompdf.wrapper');
    $pdf->loadHtml($html);

    return $pdf->download('financial-report-' . now()->format('Ymd_His') . '.pdf');
})->middleware(['auth', \App\Http\Middleware\EnsureAdmin::class])->name('manager.reports.financial.export.pdf');

// Manager Document Settings Management (CRUD)
Route::middleware(['auth', \App\Http\Middleware\EnsureAdmin::class])->prefix('/manager/document-settings')->group(function () {
    Route::get('/', function (Request $request) {
        $query = \App\Models\DocumentMaster::query()
            ->withCount(['workerDocuments', 'jobOrderChecklists', 'serviceChecklists']);

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->string('is_active')->toString() === 'true');
        }

        $documentSettings = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.document-settings.index', compact('documentSettings'));
    })->name('manager.document-settings.index');

    Route::get('/create', function () {
        $documentSetting = null;
        $services = collect();

        return view('admin.document-settings.form', compact('documentSetting', 'services'));
    })->name('manager.document-settings.create');

    Route::post('/', function (Request $request) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', 'unique:document_masters,code'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        \App\Models\DocumentMaster::create($validated);

        return redirect()->route('manager.document-settings.index')->with('success', 'เพิ่มการตั้งค่าเอกสารสำเร็จ');
    })->name('manager.document-settings.store');

    Route::get('/{documentSetting}/edit', function (\App\Models\DocumentMaster $documentSetting) {
        $documentSetting->load(['serviceChecklists.service']);
        $services = \App\Models\Service::orderBy('name')->pluck('name', 'id');

        return view('admin.document-settings.form', compact('documentSetting', 'services'));
    })->name('manager.document-settings.edit');

    Route::put('/{documentSetting}', function (Request $request, \App\Models\DocumentMaster $documentSetting) {
        $oldName = $documentSetting->name;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', 'unique:document_masters,code,' . $documentSetting->id],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);
        $documentSetting->update($validated);

        if ($oldName !== $documentSetting->name) {
            \App\Models\ServiceChecklist::where('document_name', $oldName)
                ->update(['document_name' => $documentSetting->name]);
        }

        return redirect()->route('manager.document-settings.index')->with('success', 'อัปเดตการตั้งค่าเอกสารสำเร็จ');
    })->name('manager.document-settings.update');

    Route::delete('/{documentSetting}', function (\App\Models\DocumentMaster $documentSetting) {
        if ($documentSetting->workerDocuments()->exists() || $documentSetting->jobOrderChecklists()->exists()) {
            return redirect()->route('manager.document-settings.index')->with('error', 'ไม่สามารถลบประเภทเอกสารที่ถูกใช้งานอยู่ได้');
        }

        $documentSetting->delete();

        return redirect()->route('manager.document-settings.index')->with('success', 'ลบการตั้งค่าเอกสารสำเร็จ');
    })->name('manager.document-settings.destroy');

    Route::post('/{documentSetting}/service-checklists', function (Request $request, \App\Models\DocumentMaster $documentSetting) {
        $validated = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_required' => ['nullable', 'boolean'],
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_required'] = $request->boolean('is_required', true);
        $validated['document_name'] = $documentSetting->name;

        \App\Models\ServiceChecklist::create($validated);

        return redirect()->route('manager.document-settings.edit', $documentSetting)->with('success', 'เพิ่มความเกี่ยวข้องกับบริการสำเร็จ');
    })->name('manager.document-settings.service-checklists.store');

    Route::put('/{documentSetting}/service-checklists/{serviceChecklist}', function (Request $request, \App\Models\DocumentMaster $documentSetting, \App\Models\ServiceChecklist $serviceChecklist) {
        abort_unless($serviceChecklist->document_name === $documentSetting->name, 404);

        $validated = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_required' => ['nullable', 'boolean'],
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_required'] = $request->boolean('is_required', false);
        $validated['document_name'] = $documentSetting->name;
        $serviceChecklist->update($validated);

        return redirect()->route('manager.document-settings.edit', $documentSetting)->with('success', 'อัปเดตความเกี่ยวข้องกับบริการสำเร็จ');
    })->name('manager.document-settings.service-checklists.update');

    Route::delete('/{documentSetting}/service-checklists/{serviceChecklist}', function (\App\Models\DocumentMaster $documentSetting, \App\Models\ServiceChecklist $serviceChecklist) {
        abort_unless($serviceChecklist->document_name === $documentSetting->name, 404);

        $serviceChecklist->delete();

        return redirect()->route('manager.document-settings.edit', $documentSetting)->with('success', 'ลบความเกี่ยวข้องกับบริการสำเร็จ');
    })->name('manager.document-settings.service-checklists.destroy');
});

// Manager Documents Management (CRUD)
Route::middleware(['auth', \App\Http\Middleware\EnsureAdmin::class])->prefix('/manager/documents')->group(function () {
    Route::get('/', function (Request $request) {
        $query = \App\Models\WorkerDocument::with(['worker.employer', 'documentMaster']);

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();
            $query->where(function ($q) use ($search) {
                $q->where('note', 'like', "%{$search}%")
                    ->orWhereHas('worker', function ($workerQuery) use ($search) {
                        $workerQuery->where('first_name_th', 'like', "%{$search}%")
                            ->orWhere('last_name_th', 'like', "%{$search}%")
                            ->orWhere('first_name_en', 'like', "%{$search}%")
                            ->orWhere('last_name_en', 'like', "%{$search}%")
                            ->orWhere('passport_number', 'like', "%{$search}%")
                            ->orWhere('wp_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('documentMaster', fn($documentQuery) => $documentQuery->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('worker_id')) {
            $query->where('worker_id', $request->integer('worker_id'));
        }

        if ($request->filled('document_master_id')) {
            $query->where('document_master_id', $request->integer('document_master_id'));
        }

        $documents = $query->latest()->paginate(20)->withQueryString();
        $workers = \App\Models\Worker::with('employer')->orderBy('first_name_th')->get();
        $documentMasters = \App\Models\DocumentMaster::orderBy('name')->pluck('name', 'id');

        return view('admin.documents.index', compact('documents', 'workers', 'documentMasters'));
    })->name('manager.documents.index');

    Route::get('/create', function () {
        $document = null;
        $workers = \App\Models\Worker::with('employer')->orderBy('first_name_th')->get();
        $documentMasters = \App\Models\DocumentMaster::orderBy('name')->pluck('name', 'id');

        return view('admin.documents.form', compact('document', 'workers', 'documentMasters'));
    })->name('manager.documents.create');

    Route::post('/', function (Request $request) {
        $validated = $request->validate([
            'worker_id' => ['required', 'exists:workers,id'],
            'document_master_id' => ['required', 'exists:document_masters,id'],
            'file_path' => UploadLimits::fileRules(true, ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx']),
            'expiry_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['file_path'] = $request->file('file_path')->store('worker-documents', 'public');

        \App\Models\WorkerDocument::create($validated);

        return redirect()->route('manager.documents.index')->with('success', 'เพิ่มเอกสารสำเร็จ');
    })->name('manager.documents.store');

    Route::get('/{document}/edit', function (\App\Models\WorkerDocument $document) {
        $workers = \App\Models\Worker::with('employer')->orderBy('first_name_th')->get();
        $documentMasters = \App\Models\DocumentMaster::orderBy('name')->pluck('name', 'id');

        return view('admin.documents.form', compact('document', 'workers', 'documentMasters'));
    })->name('manager.documents.edit');

    Route::put('/{document}', function (Request $request, \App\Models\WorkerDocument $document) {
        $validated = $request->validate([
            'worker_id' => ['required', 'exists:workers,id'],
            'document_master_id' => ['required', 'exists:document_masters,id'],
            'file_path' => UploadLimits::fileRules(false, ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx']),
            'expiry_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($request->hasFile('file_path')) {
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            $validated['file_path'] = $request->file('file_path')->store('worker-documents', 'public');
        } else {
            unset($validated['file_path']);
        }

        $document->update($validated);

        return redirect()->route('manager.documents.index')->with('success', 'อัปเดตเอกสารสำเร็จ');
    })->name('manager.documents.update');

    Route::delete('/{document}', function (\App\Models\WorkerDocument $document) {
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('manager.documents.index')->with('success', 'ลบเอกสารสำเร็จ');
    })->name('manager.documents.destroy');
});

// Manager Workers Management (CRUD)
Route::middleware(['auth', \App\Http\Middleware\EnsureAdmin::class])->prefix('/manager/workers')->group(function () {
    // List workers
    Route::get('/', function (Request $request) {
        $query = \App\Models\Worker::with(['employer', 'documents']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name_th', 'like', "%{$search}%")
                    ->orWhere('full_name_en', 'like', "%{$search}%")
                    ->orWhere('passport_number', 'like', "%{$search}%")
                    ->orWhere('wp_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('employer_id')) {
            $query->where('employer_id', $request->employer_id);
        }

        $workers = $query->paginate(20)->withQueryString();
        $employers = \App\Models\Employer::pluck('company_name', 'id');

        return view('admin.workers.index', compact('workers', 'employers'));
    })->name('manager.workers.index');

    // Create form
    Route::get('/create', function () {
        $worker = null;
        $employers = \App\Models\Employer::pluck('company_name', 'id');
        return view('admin.workers.form', compact('worker', 'employers'));
    })->name('manager.workers.create');

    // Store worker
    Route::post('/', function (Request $request) {
        $validated = $request->validate([
            'full_name_th' => ['required', 'string', 'max:255'],
            'full_name_en' => ['required', 'string', 'max:255'],
            'passport_number' => ['nullable', 'string', 'unique:workers,passport_number'],
            'wp_number' => ['nullable', 'string', 'unique:workers,wp_number'],
            'visa_expiry' => ['nullable', 'date'],
            'passport_expiry' => ['nullable', 'date'],
            'wp_expiry' => ['nullable', 'date'],
            'report_90_days_due' => ['nullable', 'date'],
            'employer_id' => ['required', 'exists:employers,id'],
            'photo_path' => UploadLimits::imageRules(),
            'passport_file' => UploadLimits::documentRules(),
            'wp_file' => UploadLimits::documentRules(),
            'visa_file' => UploadLimits::documentRules(),
            'report_90_days_file' => UploadLimits::documentRules(),
        ]);

        $splitName = function (string $name): array {
            $parts = preg_split('/\s+/', trim($name)) ?: [];

            if (count($parts) <= 1) {
                return [$name, null];
            }

            $lastName = array_pop($parts);
            $firstName = trim(implode(' ', $parts));

            return [$firstName !== '' ? $firstName : $name, $lastName];
        };

        [$firstNameTh, $lastNameTh] = $splitName($validated['full_name_th']);
        [$firstNameEn, $lastNameEn] = $splitName($validated['full_name_en']);

        $validated['first_name_th'] = $firstNameTh;
        $validated['last_name_th'] = $lastNameTh;
        $validated['first_name_en'] = $firstNameEn;
        $validated['last_name_en'] = $lastNameEn;

        unset($validated['full_name_th'], $validated['full_name_en']);

        // Handle file uploads
        if ($request->hasFile('photo_path')) {
            $validated['photo_path'] = $request->file('photo_path')->store('workers/photos', 'public');
        }
        if ($request->hasFile('passport_file')) {
            $validated['passport_file'] = $request->file('passport_file')->store('workers/documents', 'public');
        }
        if ($request->hasFile('wp_file')) {
            $validated['wp_file'] = $request->file('wp_file')->store('workers/documents', 'public');
        }
        if ($request->hasFile('visa_file')) {
            $validated['visa_file'] = $request->file('visa_file')->store('workers/documents', 'public');
        }
        if ($request->hasFile('report_90_days_file')) {
            $validated['report_90_days_file'] = $request->file('report_90_days_file')->store('workers/documents', 'public');
        }

        \App\Models\Worker::create($validated);
        return redirect()->route('manager.workers.index')->with('success', 'เพิ่มแรงงานสำเร็จ');
    })->name('manager.workers.store');

    // Edit form
    Route::get('/{worker}/edit', function (\App\Models\Worker $worker) {
        $employers = \App\Models\Employer::pluck('company_name', 'id');
        return view('admin.workers.form', compact('worker', 'employers'));
    })->name('manager.workers.edit');

    // Update worker
    Route::put('/{worker}', function (Request $request, \App\Models\Worker $worker) {
        $validated = $request->validate([
            'full_name_th' => ['required', 'string', 'max:255'],
            'full_name_en' => ['required', 'string', 'max:255'],
            'passport_number' => ['nullable', 'string', Rule::unique('workers', 'passport_number')->ignore($worker->id)],
            'wp_number' => ['nullable', 'string', Rule::unique('workers', 'wp_number')->ignore($worker->id)],
            'visa_expiry' => ['nullable', 'date'],
            'passport_expiry' => ['nullable', 'date'],
            'wp_expiry' => ['nullable', 'date'],
            'report_90_days_due' => ['nullable', 'date'],
            'employer_id' => ['required', 'exists:employers,id'],
            'photo_path' => UploadLimits::imageRules(),
            'passport_file' => UploadLimits::documentRules(),
            'wp_file' => UploadLimits::documentRules(),
            'visa_file' => UploadLimits::documentRules(),
            'report_90_days_file' => UploadLimits::documentRules(),
        ]);

        $splitName = function (string $name): array {
            $parts = preg_split('/\s+/', trim($name)) ?: [];

            if (count($parts) <= 1) {
                return [$name, null];
            }

            $lastName = array_pop($parts);
            $firstName = trim(implode(' ', $parts));

            return [$firstName !== '' ? $firstName : $name, $lastName];
        };

        [$firstNameTh, $lastNameTh] = $splitName($validated['full_name_th']);
        [$firstNameEn, $lastNameEn] = $splitName($validated['full_name_en']);

        $validated['first_name_th'] = $firstNameTh;
        $validated['last_name_th'] = $lastNameTh;
        $validated['first_name_en'] = $firstNameEn;
        $validated['last_name_en'] = $lastNameEn;

        unset($validated['full_name_th'], $validated['full_name_en']);

        // Handle file uploads
        if ($request->hasFile('photo_path')) {
            if ($worker->photo_path && Storage::disk('public')->exists($worker->photo_path)) {
                Storage::disk('public')->delete($worker->photo_path);
            }
            $validated['photo_path'] = $request->file('photo_path')->store('workers/photos', 'public');
        }
        if ($request->hasFile('passport_file')) {
            if ($worker->passport_file && Storage::disk('public')->exists($worker->passport_file)) {
                Storage::disk('public')->delete($worker->passport_file);
            }
            $validated['passport_file'] = $request->file('passport_file')->store('workers/documents', 'public');
        }
        if ($request->hasFile('wp_file')) {
            if ($worker->wp_file && Storage::disk('public')->exists($worker->wp_file)) {
                Storage::disk('public')->delete($worker->wp_file);
            }
            $validated['wp_file'] = $request->file('wp_file')->store('workers/documents', 'public');
        }
        if ($request->hasFile('visa_file')) {
            if ($worker->visa_file && Storage::disk('public')->exists($worker->visa_file)) {
                Storage::disk('public')->delete($worker->visa_file);
            }
            $validated['visa_file'] = $request->file('visa_file')->store('workers/documents', 'public');
        }
        if ($request->hasFile('report_90_days_file')) {
            if ($worker->report_90_days_file && Storage::disk('public')->exists($worker->report_90_days_file)) {
                Storage::disk('public')->delete($worker->report_90_days_file);
            }
            $validated['report_90_days_file'] = $request->file('report_90_days_file')->store('workers/documents', 'public');
        }

        $worker->update($validated);
        return redirect()->route('manager.workers.index')->with('success', 'อัปเดตแรงงานสำเร็จ');
    })->name('manager.workers.update');

    // Delete worker
    Route::delete('/{worker}', function (\App\Models\Worker $worker) {
        $worker->documents()->delete();
        $worker->delete();
        return redirect()->route('manager.workers.index')->with('success', 'ลบแรงงานสำเร็จ');
    })->name('manager.workers.destroy');
});

// Manager Employers Management (CRUD)
Route::middleware(['auth', \App\Http\Middleware\EnsureAdmin::class])->prefix('/manager/employers')->group(function () {
    // List employers
    Route::get('/', function (Request $request) {
        $query = \App\Models\Employer::with(['workers', 'jobOrders']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('company_code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('tax_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active == 'true');
        }

        $employers = $query->paginate(20)->withQueryString();

        return view('admin.employers.index', compact('employers'));
    })->name('manager.employers.index');

    // Create form
    Route::get('/create', function () {
        $employer = null;
        return view('admin.employers.form', compact('employer'));
    })->name('manager.employers.create');

    // Store employer
    Route::post('/', function (Request $request) {
        $validated = $request->validate([
            'company_code' => ['required', 'string', 'unique:employers'],
            'company_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'tax_id' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'logo' => UploadLimits::imageRules(),
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('employers/logos', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        \App\Models\Employer::create($validated);
        return redirect()->route('manager.employers.index')->with('success', 'เพิ่มนายจ้างสำเร็จ');
    })->name('manager.employers.store');

    // Edit form
    Route::get('/{employer}/edit', function (\App\Models\Employer $employer) {
        return view('admin.employers.form', compact('employer'));
    })->name('manager.employers.edit');

    // Update employer
    Route::put('/{employer}', function (Request $request, \App\Models\Employer $employer) {
        $validated = $request->validate([
            'company_code' => ['required', 'string', 'unique:employers,company_code,' . $employer->id],
            'company_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'tax_id' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'logo' => UploadLimits::imageRules(),
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            if ($employer->logo && Storage::disk('public')->exists($employer->logo)) {
                Storage::disk('public')->delete($employer->logo);
            }
            $validated['logo'] = $request->file('logo')->store('employers/logos', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $employer->update($validated);
        return redirect()->route('manager.employers.index')->with('success', 'อัปเดตนายจ้างสำเร็จ');
    })->name('manager.employers.update');

    // Delete employer
    Route::delete('/{employer}', function (\App\Models\Employer $employer) {
        if ($employer->workers()->exists()) {
            return redirect()->route('manager.employers.index')->with('error', 'ไม่สามารถลบนายจ้างที่มีแรงงานได้');
        }
        $employer->delete();
        return redirect()->route('manager.employers.index')->with('success', 'ลบนายจ้างสำเร็จ');
    })->name('manager.employers.destroy');
});

Route::get('/', function () {
    $totalCases = JobOrder::count();
    $completedCases = JobOrder::completed()->count();
    $totalEmployers = Employer::count();
    $successRate = $totalCases > 0 ? round(($completedCases / $totalCases) * 100, 1) : 100;

    $services = Service::active()->get();

    // Recent job orders (latest 5)
    $cases = JobOrder::with(['worker', 'service'])
        ->latest('updated_at')
        ->take(5)
        ->get();

    // Recent logs as notifications (latest 5)
    $notifications = JobOrderLog::with(['jobOrder.worker', 'jobOrder'])
        ->latest()
        ->take(5)
        ->get();

    return view('welcome', compact(
        'totalCases',
        'totalEmployers',
        'successRate',
        'services',
        'cases',
        'notifications',
    ));
})->name('home');

// Services page
Route::get('/services', function () {
    $services = Service::active()->get();
    return view('services', compact('services'));
})->name('services.index');

Route::get('/services/{service:code}', function (Service $service) {
    abort_unless($service->is_active, 404);

    $service->load(['checklists' => fn($query) => $query->orderBy('sort_order')]);

    return view('services-show', compact('service'));
})->name('services.show');

// About us page
Route::get('/aboutus', function () {
    $defaultFeatureBlocks = collect([
        ['title' => 'ถูกต้องตามกฎหมาย', 'description' => 'ดำเนินการทุกขั้นตอนอย่างถูกต้องตามกฎหมายและระเบียบข้อบังคับ', 'icon' => 'shield-check'],
        ['title' => 'ทีมงานมืออาชีพ', 'description' => 'ดูแลโดยทีมงานที่มีประสบการณ์และเข้าใจงานเอกสารแรงงานเป็นอย่างดี', 'icon' => 'users'],
        ['title' => 'บริการครบวงจร', 'description' => 'รองรับงานเอกสารแรงงานต่างด้าวแบบครบวงจรตั้งแต่ต้นจนจบ', 'icon' => 'clipboard-list'],
        ['title' => 'ดูแลใกล้ชิดทุกขั้นตอน', 'description' => 'ติดตามงานอย่างใกล้ชิด เพื่อให้ลูกค้าอุ่นใจตลอดกระบวนการ', 'icon' => 'handshake'],
    ]);

    $defaultValueBlocks = collect([
        ['title' => 'ถูกต้อง', 'description' => 'ดำเนินการทุกขั้นตอนอย่างถูกต้องตามกฎหมายและระเบียบข้อบังคับ เพื่อความปลอดภัยของลูกค้า', 'icon' => 'file-check-2'],
        ['title' => 'รวดเร็ว', 'description' => 'เร่งรัดทุกขั้นตอนอย่างมีประสิทธิภาพ เพื่อให้ลูกค้าได้รับบริการที่รวดเร็วและทันต่อความต้องการ', 'icon' => 'timer'],
        ['title' => 'เชื่อถือได้', 'description' => 'ยึดมั่นในความซื่อสัตย์ โปร่งใส และรับผิดชอบ พร้อมดูแลลูกค้าอย่างต่อเนื่อง', 'icon' => 'badge-check'],
    ]);

    $featureBlocks = AboutUsBlock::query()
        ->active()
        ->where('section', 'feature')
        ->orderBy('sort_order')
        ->get();

    $valueBlocks = AboutUsBlock::query()
        ->active()
        ->where('section', 'value')
        ->orderBy('sort_order')
        ->get();

    return view('aboutus', [
        'title' => 'เกี่ยวกับเรา',
        'featureBlocks' => $featureBlocks->isNotEmpty() ? $featureBlocks : $defaultFeatureBlocks,
        'valueBlocks' => $valueBlocks->isNotEmpty() ? $valueBlocks : $defaultValueBlocks,
    ]);
})->name('aboutus.index');

// Employer portal introduction page
Route::get('/employers', function () {
    $services = Service::active()
        ->orderBy('name')
        ->take(4)
        ->get();

    return view('employers.index', compact('services'));
})->name('employers.index');

// News and activities page
Route::get('/news', function (Request $request) {
    $categorySlug = $request->string('category')->toString();

    $categories = NewsCategory::withCount([
        'posts' => fn($query) => $query->published(),
    ])
        ->orderBy('name')
        ->get();

    $featuredPost = $categorySlug === ''
        ? NewsPost::published()
        ->with(['category', 'user'])
        ->where('is_pinned', true)
        ->latest('published_at')
        ->first()
        : null;

    $newsPosts = NewsPost::published()
        ->with(['category', 'user'])
        ->when($categorySlug !== '', function ($query) use ($categorySlug) {
            $query->whereHas('category', fn($categoryQuery) => $categoryQuery->where('slug', $categorySlug));
        })
        ->when($featuredPost, fn($query) => $query->whereKeyNot($featuredPost->getKey()))
        ->latest('published_at')
        ->paginate(9)
        ->withQueryString();

    return view('news', compact('categories', 'featuredPost', 'newsPosts', 'categorySlug'));
})->name('news.index');

Route::get('/news/{newsPost:slug}', function (NewsPost $newsPost) {
    abort_unless(
        $newsPost->status === 'published'
            && $newsPost->published_at
            && $newsPost->published_at->lte(now()),
        404
    );

    $newsPost->load(['category', 'user']);
    $newsPost->increment('views_count');

    $relatedPosts = NewsPost::published()
        ->with('category')
        ->whereKeyNot($newsPost->getKey())
        ->where('category_id', $newsPost->category_id)
        ->latest('published_at')
        ->take(3)
        ->get();

    return view('news-show', compact('newsPost', 'relatedPosts'));
})->name('news.show');

// Employer dashboard
Route::get('/employers/dashboard', function () {
    $user = Auth::user();
    $employerIds = $user->employers()->pluck('employers.id');
    $selectedEmployer = $user->employers()->orderBy('company_name')->first();

    $jobOrdersQuery = JobOrder::query()
        ->whereIn('employer_id', $employerIds);

    $totalJobs = (clone $jobOrdersQuery)->count();
    $processingJobs = (clone $jobOrdersQuery)->whereIn('status', ['pending', 'processing', 'approved'])->count();
    $waitingDocumentJobs = (clone $jobOrdersQuery)->where('status', 'waiting_document')->count();
    $waitingPaymentJobs = (clone $jobOrdersQuery)->whereIn('payment_status', ['pending', 'partial'])->count();
    $completedJobs = (clone $jobOrdersQuery)->where('status', 'completed')->count();

    $recentJobs = (clone $jobOrdersQuery)
        ->with([
            'worker',
            'service',
            'checklists',
            'payments',
        ])
        ->withCount([
            'checklists as document_issues_count' => fn($query) => $query->whereIn('status', ['pending', 'missing', 'rejected']),
            'payments as pending_payments_count' => fn($query) => $query->where('status', 'pending'),
        ])
        ->latest('updated_at')
        ->take(6)
        ->get();

    $today = now()->toDateString();
    $soon = now()->addDays(45)->toDateString();

    $expiringWorkers = Worker::query()
        ->whereIn('employer_id', $employerIds)
        ->where(function ($query) use ($today, $soon) {
            $query->whereBetween('wp_expiry', [$today, $soon])
                ->orWhereBetween('visa_expiry', [$today, $soon])
                ->orWhereBetween('passport_expiry', [$today, $soon])
                ->orWhereBetween('report_90_days_due', [$today, $soon]);
        })
        ->orderBy('wp_expiry')
        ->take(5)
        ->get();

    $notifications = JobOrderLog::with(['jobOrder.worker', 'jobOrder.service'])
        ->whereHas('jobOrder', fn($query) => $query->whereIn('employer_id', $employerIds))
        ->latest()
        ->take(5)
        ->get();

    return view('employers.dashboard', compact(
        'selectedEmployer',
        'totalJobs',
        'processingJobs',
        'waitingDocumentJobs',
        'waitingPaymentJobs',
        'completedJobs',
        'recentJobs',
        'expiringWorkers',
        'notifications',
    ));
})->middleware('auth')->name('employers.dashboard');

Route::get('/employers/workers', function (Request $request) {
    $employerIds = Auth::user()->employers()->pluck('employers.id');
    $keyword = trim((string) $request->query('q', ''));
    $expiryStatus = (string) $request->query('expiry', '');
    $today = now()->startOfDay();
    $soon = now()->copy()->addDays(45)->endOfDay();

    $workers = Worker::query()
        ->with(['employer', 'nationality', 'documents.documentMaster'])
        ->withCount('jobOrders')
        ->whereIn('employer_id', $employerIds)
        ->when($keyword !== '', function ($query) use ($keyword) {
            $query->where(function ($subQuery) use ($keyword) {
                $subQuery->where('first_name_th', 'like', "%{$keyword}%")
                    ->orWhere('last_name_th', 'like', "%{$keyword}%")
                    ->orWhere('first_name_en', 'like', "%{$keyword}%")
                    ->orWhere('last_name_en', 'like', "%{$keyword}%")
                    ->orWhere('passport_number', 'like', "%{$keyword}%")
                    ->orWhere('pink_card_number', 'like', "%{$keyword}%")
                    ->orWhere('wp_number', 'like', "%{$keyword}%");
            });
        })
        ->when($expiryStatus === 'expiring', function ($query) use ($today, $soon) {
            $query->where(function ($subQuery) use ($today, $soon) {
                $subQuery->whereBetween('wp_expiry', [$today, $soon])
                    ->orWhereBetween('visa_expiry', [$today, $soon])
                    ->orWhereBetween('passport_expiry', [$today, $soon])
                    ->orWhereBetween('pink_card_expiry', [$today, $soon])
                    ->orWhereBetween('report_90_days_due', [$today, $soon]);
            });
        })
        ->when($expiryStatus === 'expired', function ($query) use ($today) {
            $query->where(function ($subQuery) use ($today) {
                $subQuery->whereDate('wp_expiry', '<', $today)
                    ->orWhereDate('visa_expiry', '<', $today)
                    ->orWhereDate('passport_expiry', '<', $today)
                    ->orWhereDate('pink_card_expiry', '<', $today)
                    ->orWhereDate('report_90_days_due', '<', $today);
            });
        })
        ->orderBy('first_name_th')
        ->paginate(12)
        ->withQueryString();

    return view('employers.workers.index', compact('workers', 'keyword', 'expiryStatus'));
})->middleware('auth')->name('employers.workers.index');

Route::get('/employers/workers/request', [EmployerWorkerRequestController::class, 'create'])
    ->middleware('auth')->name('employers.workers.request.create');

Route::post('/employers/workers/request', [EmployerWorkerRequestController::class, 'store'])
    ->middleware('auth')->name('employers.workers.request.store');

Route::get('/employers/workers/{worker}', function (Worker $worker) {
    $employerIds = Auth::user()->employers()->pluck('employers.id');

    abort_unless($employerIds->contains($worker->employer_id), 403);

    $worker->load([
        'employer',
        'nationality',
        'documents.documentMaster',
        'jobOrders' => fn($query) => $query
            ->with(['service', 'payments', 'checklists'])
            ->latest('updated_at'),
    ]);

    return view('employers.workers.show', compact('worker'));
})->middleware('auth')->name('employers.workers.show');

Route::get('/employers/jobs/create', function () {
    $employerIds = Auth::user()->employers()->pluck('employers.id');

    $employers = Employer::query()
        ->whereIn('id', $employerIds)
        ->orderBy('company_name')
        ->get();

    $workers = Worker::query()
        ->with('employer')
        ->whereIn('employer_id', $employerIds)
        ->active()
        ->orderBy('first_name_th')
        ->get();

    $services = Service::active()
        ->with(['checklists' => fn($query) => $query->orderBy('sort_order')])
        ->orderBy('name')
        ->get();

    return view('staff-portal.job-orders.create', [
        'employers' => $employers,
        'workers' => $workers,
        'services' => $services,
        'employerId' => (int) old('employer_id', $employers->first()?->id),
        'workerId' => (int) old('worker_id', 0),
        'serviceId' => (int) old('service_id', 0),
        'requestMode' => true,
    ]);
})->middleware('auth')->name('employers.jobs.create');

Route::post('/employers/jobs', function (Request $request) {
    $employerIds = Auth::user()->employers()->pluck('employers.id');

    $validated = $request->validate([
        'employer_id' => ['required', 'integer'],
        'worker_id' => ['required', 'integer'],
        'service_id' => ['required', 'integer'],
        'priority' => ['required', 'in:low,medium,high,urgent'],
        'due_date' => ['nullable', 'date'],
        'notes' => ['nullable', 'string', 'max:2000'],
    ]);

    abort_unless($employerIds->contains((int) $validated['employer_id']), 403);

    $worker = Worker::query()
        ->where('id', $validated['worker_id'])
        ->where('employer_id', $validated['employer_id'])
        ->firstOrFail();

    $service = Service::active()
        ->with(['checklists' => fn($query) => $query->orderBy('sort_order')])
        ->findOrFail($validated['service_id']);

    $jobOrder = DB::transaction(function () use ($validated, $worker, $service) {
        $jobOrder = JobOrder::create([
            'employer_id' => $validated['employer_id'],
            'worker_id' => $worker->id,
            'service_id' => $service->id,
            'service_fee' => 0,
            'paid_amount' => 0,
            'payment_status' => 'pending',
            'status' => 'pending',
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $documentMasters = DocumentMaster::query()
            ->whereIn('name', $service->checklists->pluck('document_name'))
            ->get()
            ->keyBy('name');

        foreach ($service->checklists as $checklist) {
            $documentMaster = $documentMasters->get($checklist->document_name);

            if (! $documentMaster) {
                continue;
            }

            JobOrderChecklist::create([
                'job_order_id' => $jobOrder->id,
                'document_master_id' => $documentMaster->id,
                'is_required' => $checklist->is_required,
                'status' => 'pending',
            ]);
        }

        return $jobOrder;
    });

    return redirect()
        ->route('employers.jobs.show', $jobOrder->job_number)
        ->with('success', 'แจ้งงานใหม่เรียบร้อยแล้ว');
})->middleware('auth')->name('employers.jobs.store');

// Admin export for expiring docs (CSV)
Route::get('/admin/workers-expiry-dashboard/export', function () {
    return WorkersExpiryDashboard::streamExpiringDocsCsv(45);
})->middleware('auth')->name('admin.workers_expiry.export');

// Admin notify action (creates an activity log entry)
Route::post('/admin/workers-expiry-dashboard/notify', function (Request $request) {
    $ok = WorkersExpiryDashboard::notifyExpiringDocs();

    if ($request->wantsJson()) {
        return response()->json(['ok' => $ok]);
    }

    return back()->with('success', $ok ? 'ส่งแจ้งเตือนไปเรียบร้อยแล้ว' : 'ไม่สามารถส่งแจ้งเตือนได้');
})->middleware('auth')->name('admin.workers_expiry.notify');

Route::post('/employers/jobs/{jobOrder:job_number}/documents/{checklist}', function (Request $request, JobOrder $jobOrder, JobOrderChecklist $checklist) {
    $employerIds = Auth::user()->employers()->pluck('employers.id');

    abort_unless($employerIds->contains($jobOrder->employer_id), 403);
    abort_unless($checklist->job_order_id === $jobOrder->id, 404);

    $validated = $request->validate([
        'document_file' => UploadLimits::fileRules(true, ['pdf', 'jpg', 'jpeg', 'png', 'webp']),
        'remark' => ['nullable', 'string', 'max:1000'],
    ]);

    $path = $validated['document_file']->store("job-order-documents/{$jobOrder->job_number}", 'public');

    if ($checklist->attached_file_path) {
        Storage::disk('public')->delete($checklist->attached_file_path);
    }

    $checklist->update([
        'attached_file_path' => $path,
        'received_at' => now(),
        'status' => 'received',
        'remark' => $validated['remark'] ?? $checklist->remark,
        'verified_by' => null,
        'verified_at' => null,
    ]);

    if ($jobOrder->status === 'pending') {
        $jobOrder->update(['status' => 'processing', 'started_at' => $jobOrder->started_at ?? now()]);
    }

    JobOrderLog::create([
        'job_order_id' => $jobOrder->id,
        'user_id' => Auth::id(),
        'action' => 'นายจ้างอัปโหลดเอกสาร',
        'description' => 'อัปโหลดเอกสาร ' . ($checklist->documentMaster?->name ?: 'ประกอบงาน') . ' เพื่อรอตรวจสอบ',
    ]);

    return back()->with('success', 'อัปโหลดเอกสารเรียบร้อยแล้ว รอเจ้าหน้าที่ตรวจสอบ');
})->middleware('auth')->name('employers.jobs.documents.store');

Route::post('/employers/jobs/{jobOrder:job_number}/payments', function (Request $request, JobOrder $jobOrder) {
    $employerIds = Auth::user()->employers()->pluck('employers.id');

    abort_unless($employerIds->contains($jobOrder->employer_id), 403);
    abort_if(in_array($jobOrder->status, ['completed', 'cancelled', 'rejected'], true), 403);

    $validated = $request->validate([
        'amount' => ['required', 'numeric', 'min:1'],
        'payment_date' => ['required', 'date'],
        'payment_method' => ['required', 'in:transfer,promptpay,credit_card,cash'],
        'payment_reference' => ['nullable', 'string', 'max:255'],
        'slip_file' => UploadLimits::fileRules(true, ['jpg', 'jpeg', 'png', 'webp', 'pdf']),
        'note' => ['nullable', 'string', 'max:1000'],
    ]);

    $path = $validated['slip_file']->store("job-order-payments/{$jobOrder->job_number}", 'public');

    JobOrderPayment::create([
        'job_order_id' => $jobOrder->id,
        'amount' => $validated['amount'],
        'payment_date' => $validated['payment_date'],
        'payment_method' => $validated['payment_method'],
        'payment_reference' => $validated['payment_reference'] ?? null,
        'slip_path' => $path,
        'status' => 'pending',
        'note' => $validated['note'] ?? null,
    ]);

    JobOrderLog::create([
        'job_order_id' => $jobOrder->id,
        'user_id' => Auth::id(),
        'action' => 'นายจ้างอัปโหลดสลิป',
        'description' => 'ส่งหลักฐานชำระเงินจำนวน ' . number_format((float) $validated['amount'], 2) . ' บาท รอเจ้าหน้าที่ตรวจสอบ',
    ]);

    return back()->with('success', 'อัปโหลดสลิปเรียบร้อยแล้ว รอเจ้าหน้าที่ตรวจสอบ');
})->middleware('auth')->name('employers.jobs.payments.store');

Route::get('/employers/jobs/{jobOrder:job_number}', function (JobOrder $jobOrder) {
    $employerIds = Auth::user()->employers()->pluck('employers.id');

    abort_unless($employerIds->contains($jobOrder->employer_id), 403);

    $jobOrder->load([
        'employer',
        'worker.nationality',
        'service',
        'assignedUser',
        'checklists.documentMaster',
        'checklists.verifiedBy',
        'payments.receiver',
        'logs.user',
    ]);

    return view('employers.job-show', compact('jobOrder'));
})->middleware('auth')->name('employers.jobs.show');

// Unified login page
Route::get('/login', function () {
    return view('auth.login');
})->middleware('guest')->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
        'portal' => ['required', 'in:customer,admin,staff'],
    ]);

    $remember = $request->boolean('remember');
    $portal = $credentials['portal'];
    $email = Str::lower(trim($credentials['email']));

    $user = User::query()
        ->whereRaw('lower(email) = ?', [$email])
        ->first();

    if (! $user || ! Hash::check($credentials['password'], $user->password)) {
        throw ValidationException::withMessages([
            'email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง',
        ]);
    }

    Auth::login($user, $remember);
    $request->session()->regenerate();

    $user = $request->user();
    $portalRoles = [
        'customer' => ['employer', 'hr'],
        'admin' => ['super_admin', 'admin', 'manager'],
        'staff' => ['staff', 'accounting'],
    ];

    $canAccessPortal = in_array($user->role, $portalRoles[$portal], true)
        || $user->hasAnyRole($portalRoles[$portal]);

    if (! $canAccessPortal) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        throw ValidationException::withMessages([
            'portal' => 'บัญชีนี้ไม่มีสิทธิ์เข้าสู่พอร์ทัลที่เลือก',
        ]);
    }

    // Determine redirect based on role and portal
    if ($portal === 'admin') {
        // Check if user is manager
        if ($user->hasRole('manager') || $user->role === 'manager') {
            return redirect()->intended(route('manager.dashboard'));
        }
        // Otherwise admin/super_admin go to /admin
        return redirect()->intended('/admin');
    }

    if ($portal === 'customer' && $user->hasRole('employer')) {
        $employerIds = $user->employers()->pluck('employers.id');

        if ($employerIds->count() !== 1) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'portal' => 'บัญชีนายจ้างต้องเชื่อมกับนายจ้างเพียง 1 รายการ',
            ]);
        }
    }

    return match ($portal) {
        'customer' => redirect()->intended(route('employers.dashboard')),
        'staff' => redirect()->intended(route('staff.portal.dashboard')),
        default => redirect()->intended('/admin'),
    };
})->middleware('guest')->name('login.store');

Route::get('/staff-portal', [StaffPortalController::class, 'dashboard'])
    ->middleware('auth')
    ->name('staff.portal.dashboard');

Route::get('/staff-portal/calendar', [StaffPortalController::class, 'calendar'])
    ->middleware('auth')
    ->name('staff.portal.calendar');

Route::get('/staff-portal/tasks', [StaffPortalController::class, 'tasks'])
    ->middleware('auth')
    ->name('staff.portal.tasks.index');

Route::get('/staff-portal/reports', [StaffPortalController::class, 'reports'])
    ->middleware('auth')
    ->name('staff.portal.reports.index');

Route::get('/staff-portal/reports/expiring-documents', [StaffPortalController::class, 'expiringDocumentReport'])
    ->middleware('auth')
    ->name('staff.portal.reports.expiring-documents');

Route::get('/staff-portal/reports/expiring-documents/export', [StaffPortalController::class, 'expiringDocumentReportExport'])
    ->middleware('auth')
    ->name('staff.portal.reports.expiring-documents.export');

Route::get('/staff-portal/worker-documents/download', [StaffPortalController::class, 'workerDocumentDownload'])
    ->middleware('auth')
    ->name('staff.portal.worker-documents.download');

Route::post('/staff-portal/worker-documents/download', [StaffPortalController::class, 'workerDocumentDownloadStore'])
    ->middleware('auth')
    ->name('staff.portal.worker-documents.download.store');


Route::get('/staff-portal/settings', [StaffPortalController::class, 'settings'])
    ->middleware('auth')
    ->name('staff.portal.settings');

Route::post('/staff-portal/settings', [StaffPortalController::class, 'updateSettings'])
    ->middleware('auth')
    ->name('staff.portal.settings.update');

Route::get('/staff-portal/settings/document-statuses', [StaffPortalController::class, 'documentStatuses'])
    ->middleware('auth')
    ->name('staff.portal.document-statuses.index');

Route::post('/staff-portal/settings/document-statuses', [StaffPortalController::class, 'documentStatusStore'])
    ->middleware('auth')
    ->name('staff.portal.document-statuses.store');

Route::put('/staff-portal/settings/document-statuses/{workerDocumentStatus}', [StaffPortalController::class, 'documentStatusUpdate'])
    ->middleware('auth')
    ->name('staff.portal.document-statuses.update');

Route::get('/staff-portal/users', [StaffPortalController::class, 'users'])
    ->middleware('auth')
    ->name('staff.portal.users.index');

Route::get('/staff-portal/users/create', [StaffPortalController::class, 'userCreate'])
    ->middleware('auth')
    ->name('staff.portal.users.create');

Route::post('/staff-portal/users', [StaffPortalController::class, 'userStore'])
    ->middleware('auth')
    ->name('staff.portal.users.store');

Route::get('/staff-portal/users/{user}/edit', [StaffPortalController::class, 'userEdit'])
    ->middleware('auth')
    ->name('staff.portal.users.edit');

Route::put('/staff-portal/users/{user}', [StaffPortalController::class, 'userUpdate'])
    ->middleware('auth')
    ->name('staff.portal.users.update');

Route::delete('/staff-portal/users/{user}', [StaffPortalController::class, 'userDestroy'])
    ->middleware('auth')
    ->name('staff.portal.users.destroy');

Route::get('/staff-portal/services', [StaffPortalController::class, 'services'])
    ->middleware('auth')
    ->name('staff.portal.services.index');

Route::get('/staff-portal/news', [StaffPortalController::class, 'newsIndex'])
    ->middleware('auth')
    ->name('staff.portal.news.index');

Route::get('/staff-portal/news/create', [StaffPortalController::class, 'newsCreate'])
    ->middleware('auth')
    ->name('staff.portal.news.create');

Route::post('/staff-portal/news', [StaffPortalController::class, 'newsStore'])
    ->middleware('auth')
    ->name('staff.portal.news.store');

Route::get('/staff-portal/news/{newsPost}/edit', [StaffPortalController::class, 'newsEdit'])
    ->middleware('auth')
    ->name('staff.portal.news.edit');

Route::put('/staff-portal/news/{newsPost}', [StaffPortalController::class, 'newsUpdate'])
    ->middleware('auth')
    ->name('staff.portal.news.update');

Route::delete('/staff-portal/news/{newsPost}', [StaffPortalController::class, 'newsDestroy'])
    ->middleware('auth')
    ->name('staff.portal.news.destroy');

Route::get('/staff-portal/about-us', [StaffPortalController::class, 'aboutUsIndex'])
    ->middleware('auth')
    ->name('staff.portal.about-us.index');

Route::get('/staff-portal/about-us/create', [StaffPortalController::class, 'aboutUsCreate'])
    ->middleware('auth')
    ->name('staff.portal.about-us.create');

Route::post('/staff-portal/about-us', [StaffPortalController::class, 'aboutUsStore'])
    ->middleware('auth')
    ->name('staff.portal.about-us.store');

Route::get('/staff-portal/about-us/{aboutUsBlock}/edit', [StaffPortalController::class, 'aboutUsEdit'])
    ->middleware('auth')
    ->name('staff.portal.about-us.edit');

Route::put('/staff-portal/about-us/{aboutUsBlock}', [StaffPortalController::class, 'aboutUsUpdate'])
    ->middleware('auth')
    ->name('staff.portal.about-us.update');

Route::delete('/staff-portal/about-us/{aboutUsBlock}', [StaffPortalController::class, 'aboutUsDestroy'])
    ->middleware('auth')
    ->name('staff.portal.about-us.destroy');

Route::get('/staff-portal/document-masters/create', [StaffPortalController::class, 'documentMasterCreate'])
    ->middleware('auth')
    ->name('staff.portal.document-masters.create');

Route::post('/staff-portal/document-masters', [StaffPortalController::class, 'documentMasterStore'])
    ->middleware('auth')
    ->name('staff.portal.document-masters.store');

Route::get('/staff-portal/document-masters', [StaffPortalController::class, 'documentMasterIndex'])
    ->middleware('auth')
    ->name('staff.portal.document-masters.index');

Route::get('/staff-portal/document-masters/{documentMaster}/edit', [StaffPortalController::class, 'documentMasterEdit'])
    ->middleware('auth')
    ->name('staff.portal.document-masters.edit');

Route::put('/staff-portal/document-masters/{documentMaster}', [StaffPortalController::class, 'documentMasterUpdate'])
    ->middleware('auth')
    ->name('staff.portal.document-masters.update');

Route::get('/staff-portal/worker-prefixes', [StaffPortalController::class, 'workerPrefixesIndex'])
    ->middleware('auth')
    ->name('staff.portal.worker-prefixes.index');

Route::get('/staff-portal/worker-prefixes/create', [StaffPortalController::class, 'workerPrefixCreate'])
    ->middleware('auth')
    ->name('staff.portal.worker-prefixes.create');

Route::post('/staff-portal/worker-prefixes', [StaffPortalController::class, 'workerPrefixStore'])
    ->middleware('auth')
    ->name('staff.portal.worker-prefixes.store');

Route::get('/staff-portal/worker-prefixes/{workerPrefix}/edit', [StaffPortalController::class, 'workerPrefixEdit'])
    ->middleware('auth')
    ->name('staff.portal.worker-prefixes.edit');

Route::put('/staff-portal/worker-prefixes/{workerPrefix}', [StaffPortalController::class, 'workerPrefixUpdate'])
    ->middleware('auth')
    ->name('staff.portal.worker-prefixes.update');

Route::delete('/staff-portal/worker-prefixes/{workerPrefix}', [StaffPortalController::class, 'workerPrefixDestroy'])
    ->middleware('auth')
    ->name('staff.portal.worker-prefixes.destroy');

Route::get('/staff-portal/job-order-statuses', [StaffPortalController::class, 'jobOrderStatusesIndex'])
    ->middleware('auth')
    ->name('staff.portal.job-order-statuses.index');

Route::get('/staff-portal/job-order-statuses/create', [StaffPortalController::class, 'jobOrderStatusCreate'])
    ->middleware('auth')
    ->name('staff.portal.job-order-statuses.create');

Route::post('/staff-portal/job-order-statuses', [StaffPortalController::class, 'jobOrderStatusStore'])
    ->middleware('auth')
    ->name('staff.portal.job-order-statuses.store');

Route::get('/staff-portal/job-order-statuses/{jobOrderStatus}/edit', [StaffPortalController::class, 'jobOrderStatusEdit'])
    ->middleware('auth')
    ->name('staff.portal.job-order-statuses.edit');

Route::put('/staff-portal/job-order-statuses/{jobOrderStatus}', [StaffPortalController::class, 'jobOrderStatusUpdate'])
    ->middleware('auth')
    ->name('staff.portal.job-order-statuses.update');

Route::delete('/staff-portal/job-order-statuses/{jobOrderStatus}', [StaffPortalController::class, 'jobOrderStatusDestroy'])
    ->middleware('auth')
    ->name('staff.portal.job-order-statuses.destroy');

Route::get('/staff-portal/services/create', [StaffPortalController::class, 'serviceCreate'])
    ->middleware('auth')
    ->name('staff.portal.services.create');

Route::post('/staff-portal/services', [StaffPortalController::class, 'serviceStore'])
    ->middleware('auth')
    ->name('staff.portal.services.store');

Route::get('/staff-portal/services/{service}', [StaffPortalController::class, 'serviceShow'])
    ->middleware('auth')
    ->name('staff.portal.services.show');

Route::get('/staff-portal/services/{service}/edit', [StaffPortalController::class, 'serviceEdit'])
    ->middleware('auth')
    ->name('staff.portal.services.edit');

Route::put('/staff-portal/services/{service}', [StaffPortalController::class, 'serviceUpdate'])
    ->middleware('auth')
    ->name('staff.portal.services.update');

Route::delete('/staff-portal/services/{service}', [StaffPortalController::class, 'serviceDestroy'])
    ->middleware('auth')
    ->name('staff.portal.services.destroy');

Route::post('/staff-portal/services/{service}/documents', [StaffPortalController::class, 'serviceChecklistStore'])
    ->middleware('auth')
    ->name('staff.portal.services.documents.store');

Route::get('/staff-portal/services/{service}/documents/{serviceChecklist}/edit', [StaffPortalController::class, 'serviceChecklistEdit'])
    ->middleware('auth')
    ->name('staff.portal.services.documents.edit');

Route::put('/staff-portal/services/{service}/documents/{serviceChecklist}', [StaffPortalController::class, 'serviceChecklistUpdate'])
    ->middleware('auth')
    ->name('staff.portal.services.documents.update');

Route::delete('/staff-portal/services/{service}/documents/{serviceChecklist}', [StaffPortalController::class, 'serviceChecklistDestroy'])
    ->middleware('auth')
    ->name('staff.portal.services.documents.destroy');



Route::get('/staff-portal/employers', [StaffPortalController::class, 'employers'])
    ->middleware('auth')
    ->name('staff.portal.employers.index');

Route::get('/staff-portal/employers/create', [StaffPortalController::class, 'employerCreate'])
    ->middleware('auth')
    ->name('staff.portal.employers.create');

Route::post('/staff-portal/employers', [StaffPortalController::class, 'employerStore'])
    ->middleware('auth')
    ->name('staff.portal.employers.store');

Route::get('/staff-portal/employers/{employer}', [StaffPortalController::class, 'employerShow'])
    ->middleware('auth')
    ->name('staff.portal.employers.show');

Route::get('/staff-portal/employers/{employer}/edit', [StaffPortalController::class, 'employerEdit'])
    ->middleware('auth')
    ->name('staff.portal.employers.edit');

Route::put('/staff-portal/employers/{employer}', [StaffPortalController::class, 'employerUpdate'])
    ->middleware('auth')
    ->name('staff.portal.employers.update');

Route::get('/staff-portal/notifications', [StaffPortalController::class, 'notifications'])
    ->middleware('auth')
    ->name('staff.portal.notifications.index');

Route::post('/staff-portal/notifications/{notification}/read', [StaffPortalController::class, 'markNotificationRead'])
    ->middleware('auth')
    ->name('staff.portal.notifications.read');

Route::post('/staff-portal/notifications/read-all', [StaffPortalController::class, 'markAllNotificationsRead'])
    ->middleware('auth')
    ->name('staff.portal.notifications.read-all');

Route::get('/staff-portal/workers', [StaffPortalController::class, 'workers'])
    ->middleware('auth')
    ->name('staff.portal.workers.index');

Route::get('/staff-portal/workers/active', [StaffPortalController::class, 'workers'])
    ->defaults('worker_status', 'active')
    ->middleware('auth')
    ->name('staff.portal.workers.active');

Route::get('/staff-portal/workers/inactive', [StaffPortalController::class, 'workers'])
    ->defaults('worker_status', 'inactive')
    ->middleware('auth')
    ->name('staff.portal.workers.inactive');

Route::get('/staff-portal/workers/export', [StaffPortalController::class, 'workersExport'])
    ->middleware('auth')
    ->name('staff.portal.workers.export');

Route::get('/staff-portal/worker-registration-requests', [StaffPortalController::class, 'workerRegistrationRequests'])
    ->middleware('auth')->name('staff.portal.worker-registration-requests.index');

Route::post('/staff-portal/worker-registration-requests/{registrationRequest}/approve', [StaffPortalController::class, 'workerRegistrationRequestApprove'])
    ->middleware('auth')->name('staff.portal.worker-registration-requests.approve');

Route::post('/staff-portal/worker-registration-requests/{registrationRequest}/reject', [StaffPortalController::class, 'workerRegistrationRequestReject'])
    ->middleware('auth')->name('staff.portal.worker-registration-requests.reject');

Route::get('/staff-portal/workers/create', [StaffPortalController::class, 'workerCreate'])
    ->middleware('auth')
    ->name('staff.portal.workers.create');

Route::post('/staff-portal/workers', [StaffPortalController::class, 'workerStore'])
    ->middleware('auth')
    ->name('staff.portal.workers.store');

Route::get('/staff-portal/workers/{worker}', [StaffPortalController::class, 'workerShow'])
    ->middleware('auth')
    ->name('staff.portal.workers.show');

Route::get('/staff-portal/workers/{worker}/edit', [StaffPortalController::class, 'workerEdit'])
    ->middleware('auth')
    ->name('staff.portal.workers.edit');

Route::put('/staff-portal/workers/{worker}', [StaffPortalController::class, 'workerUpdate'])
    ->middleware('auth')
    ->name('staff.portal.workers.update');

Route::delete('/staff-portal/workers/{worker}', [StaffPortalController::class, 'workerDestroy'])
    ->middleware('auth')
    ->name('staff.portal.workers.destroy');

Route::post('/staff-portal/workers/{worker}/documents', [StaffPortalController::class, 'workerDocumentStore'])
    ->middleware('auth')
    ->name('staff.portal.workers.documents.store');

Route::delete('/staff-portal/workers/{worker}/documents/{workerDocument}', [StaffPortalController::class, 'workerDocumentDestroy'])
    ->middleware('auth')
    ->name('staff.portal.workers.documents.destroy');

Route::put('/staff-portal/workers/{worker}/documents/{workerDocument}/status', [StaffPortalController::class, 'workerDocumentStatusUpdate'])
    ->middleware('auth')
    ->name('staff.portal.workers.documents.status.update');

Route::get('/staff-portal/delivery-sheets', [StaffPortalController::class, 'deliverySheets'])
    ->middleware('auth')
    ->name('staff.portal.delivery-sheets.index');

Route::get('/staff-portal/delivery-sheets/create', [StaffPortalController::class, 'deliverySheetCreate'])
    ->middleware('auth')
    ->name('staff.portal.delivery-sheets.create');

Route::post('/staff-portal/delivery-sheets', [StaffPortalController::class, 'deliverySheetStore'])
    ->middleware('auth')
    ->name('staff.portal.delivery-sheets.store');

Route::get('/staff-portal/delivery-sheets/{deliverySheet}', [StaffPortalController::class, 'deliverySheetShow'])
    ->middleware('auth')
    ->name('staff.portal.delivery-sheets.show');

Route::put('/staff-portal/delivery-sheets/{deliverySheet}', [StaffPortalController::class, 'deliverySheetUpdate'])
    ->middleware('auth')
    ->name('staff.portal.delivery-sheets.update');

Route::delete('/staff-portal/delivery-sheets/{deliverySheet}', [StaffPortalController::class, 'deliverySheetDestroy'])
    ->middleware('auth')
    ->name('staff.portal.delivery-sheets.destroy');

Route::post('/staff-portal/delivery-sheets/{deliverySheet}/attachments', [StaffPortalController::class, 'deliverySheetAttachmentStore'])
    ->middleware('auth')
    ->name('staff.portal.delivery-sheets.attachments.store');

Route::delete('/staff-portal/delivery-sheets/{deliverySheet}/attachments/{attachment}', [StaffPortalController::class, 'deliverySheetAttachmentDestroy'])
    ->middleware('auth')
    ->name('staff.portal.delivery-sheets.attachments.destroy');

Route::delete('/staff-portal/delivery-sheets/{deliverySheet}/items/{item}', [StaffPortalController::class, 'deliverySheetItemDestroy'])
    ->middleware('auth')
    ->name('staff.portal.delivery-sheets.items.destroy');

Route::get('/staff-portal/job-orders', [StaffPortalController::class, 'jobOrders'])
    ->middleware('auth')
    ->name('staff.portal.job-orders.index');

Route::get('/staff-portal/job-orders/export', [StaffPortalController::class, 'jobOrdersExport'])
    ->middleware('auth')
    ->name('staff.portal.job-orders.export');

Route::get('/staff-portal/job-orders/create', [StaffPortalController::class, 'jobOrderCreate'])
    ->middleware('auth')
    ->name('staff.portal.job-orders.create');

Route::post('/staff-portal/job-orders', [StaffPortalController::class, 'jobOrderStore'])
    ->middleware('auth')
    ->name('staff.portal.job-orders.store');

Route::get('/staff-portal/job-orders/{jobOrder}', [StaffPortalController::class, 'jobOrderShow'])
    ->middleware('auth')
    ->name('staff.portal.job-orders.show');

Route::post('/staff-portal/job-orders/{jobOrder}/status', [StaffPortalController::class, 'updateJobStatus'])
    ->middleware('auth')
    ->name('staff.portal.job-orders.status.update');

Route::post('/staff-portal/job-orders/{jobOrder}/service-fee', [StaffPortalController::class, 'updateServiceFee'])
    ->middleware('auth')
    ->name('staff.portal.job-orders.service-fee.update');

Route::delete('/staff-portal/job-orders/{jobOrder}', [StaffPortalController::class, 'jobOrderDestroy'])
    ->middleware('auth')
    ->name('staff.portal.job-orders.destroy');

Route::delete('/staff-portal/job-orders/{jobOrder}/documents/{checklist}', [StaffPortalController::class, 'jobOrderChecklistDestroy'])
    ->middleware('auth')
    ->name('staff.portal.job-orders.documents.destroy');

Route::post('/staff-portal/job-orders/{jobOrder}/documents/{checklist}/upload', [StaffPortalController::class, 'jobOrderChecklistStore'])
    ->middleware('auth')
    ->name('staff.portal.job-orders.documents.store');

Route::get('/staff-portal/document-reviews', [StaffPortalController::class, 'documentReviews'])
    ->middleware('auth')
    ->name('staff.portal.document-reviews.index');

Route::post('/staff-portal/document-reviews/{checklist}/verify', [StaffPortalController::class, 'verifyDocument'])
    ->middleware('auth')
    ->name('staff.portal.document-reviews.verify');

Route::post('/staff-portal/document-reviews/{checklist}/reject', [StaffPortalController::class, 'rejectDocument'])
    ->middleware('auth')
    ->name('staff.portal.document-reviews.reject');

Route::get('/staff-portal/payment-reviews', [StaffPortalController::class, 'paymentReviews'])
    ->middleware('auth')
    ->name('staff.portal.payment-reviews.index');

Route::post('/staff-portal/payments/{payment}/slip', [StaffPortalController::class, 'paymentSlipStore'])
    ->middleware('auth')
    ->name('staff.portal.payments.slip.store');

Route::post('/staff-portal/job-orders/{jobOrder}/payments', [StaffPortalController::class, 'staffPaymentStore'])
    ->middleware('auth')
    ->name('staff.portal.job-orders.payments.store');

Route::post('/staff-portal/payments/{payment}/verify', [StaffPortalController::class, 'verifyPayment'])
    ->middleware('auth')
    ->name('staff.portal.payments.verify');

Route::post('/staff-portal/payments/{payment}/reject', [StaffPortalController::class, 'rejectPayment'])
    ->middleware('auth')
    ->name('staff.portal.payments.reject');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('home');
})->middleware('auth')->name('logout');

// Public job status lookup
Route::get('/status', function () {
    $keyword = trim((string) request('job_number'));
    $latestJobNumber = JobOrder::latest('created_at')->value('job_number');
    $jobOrder = null;

    if ($keyword !== '') {
        $jobOrder = JobOrder::with(['worker', 'service', 'logs' => fn($query) => $query->latest()])
            ->where('job_number', $keyword)
            ->first();
    }

    return view('status', compact('keyword', 'latestJobNumber', 'jobOrder'));
})->name('status.index');

// Admin Permission Management Routes
Route::prefix('admin-manage')->name('admin-manage.')->middleware(['auth', \App\Http\Middleware\EnsureAdmin::class])->group(function () {
    // Permissions
    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/', function () {
            $permissions = \Spatie\Permission\Models\Permission::latest()->paginate(20);
            return view('admin.permissions.index', compact('permissions'));
        })->name('index');

        Route::get('/create', function () {
            $permissions = \Spatie\Permission\Models\Permission::all();
            return view('admin.permissions.create', compact('permissions'));
        })->name('create');

        Route::post('/', function (Request $request) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'slug' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:1000'],
            ]);

            \Spatie\Permission\Models\Permission::create($validated);

            return redirect()->route('admin-manage.permissions.index')->with('success', 'สร้างสิทธิ์เรียบร้อยแล้ว');
        })->name('store');

        Route::get('/{permission}/edit', function (\Spatie\Permission\Models\Permission $permission) {
            return view('admin.permissions.edit', compact('permission'));
        })->name('edit');

        Route::put('/{permission}', function (Request $request, \Spatie\Permission\Models\Permission $permission) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'slug' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:1000'],
            ]);

            $permission->update($validated);

            return redirect()->route('admin-manage.permissions.index')->with('success', 'แก้ไขสิทธิ์เรียบร้อยแล้ว');
        })->name('update');

        Route::delete('/{permission}', function (\Spatie\Permission\Models\Permission $permission) {
            $permission->delete();

            return redirect()->route('admin-manage.permissions.index')->with('success', 'ลบสิทธิ์เรียบร้อยแล้ว');
        })->name('destroy');
    });

    // Roles
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', function () {
            $roles = \Spatie\Permission\Models\Role::withCount('users')->latest()->paginate(20);
            return view('admin.roles.index', compact('roles'));
        })->name('index');

        Route::get('/create', function () {
            $permissions = \Spatie\Permission\Models\Permission::all();
            return view('admin.roles.create', compact('permissions'));
        })->name('create');

        Route::post('/', function (Request $request) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
                'permissions' => ['nullable', 'array'],
                'permissions.*' => ['integer', 'exists:permissions,id'],
            ]);

            $role = \Spatie\Permission\Models\Role::create(['name' => $validated['name']]);

            if (isset($validated['permissions'])) {
                $role->permissions()->sync($validated['permissions']);
            }

            return redirect()->route('admin-manage.roles.index')->with('success', 'สร้างบทบาทเรียบร้อยแล้ว');
        })->name('store');

        Route::get('/{role}/edit', function (\Spatie\Permission\Models\Role $role) {
            $permissions = \Spatie\Permission\Models\Permission::all();
            return view('admin.roles.edit', compact('role', 'permissions'));
        })->name('edit');

        Route::put('/{role}', function (Request $request, \Spatie\Permission\Models\Role $role) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
                'permissions' => ['nullable', 'array'],
                'permissions.*' => ['integer', 'exists:permissions,id'],
            ]);

            $role->update(['name' => $validated['name']]);

            if (isset($validated['permissions'])) {
                $role->permissions()->sync($validated['permissions']);
            } else {
                $role->permissions()->detach();
            }

            return redirect()->route('admin-manage.roles.index')->with('success', 'แก้ไขบทบาทเรียบร้อยแล้ว');
        })->name('update');

        Route::delete('/{role}', function (\Spatie\Permission\Models\Role $role) {
            $role->delete();

            return redirect()->route('admin-manage.roles.index')->with('success', 'ลบบทบาทเรียบร้อยแล้ว');
        })->name('destroy');

        Route::get('/{role}/permissions', function (\Spatie\Permission\Models\Role $role) {
            $permissions = \Spatie\Permission\Models\Permission::all();
            return view('admin.roles.permissions', compact('role', 'permissions'));
        })->name('permissions');

        Route::put('/{role}/permissions', function (Request $request, \Spatie\Permission\Models\Role $role) {
            $validated = $request->validate([
                'permissions' => ['nullable', 'array'],
                'permissions.*' => ['integer', 'exists:permissions,id'],
            ]);

            if (isset($validated['permissions'])) {
                $role->permissions()->sync($validated['permissions']);
            } else {
                $role->permissions()->detach();
            }

            return redirect()->route('admin-manage.roles.index')->with('success', 'บันทึกสิทธิ์บทบาทเรียบร้อยแล้ว');
        })->name('permissions.update');
    });
});
