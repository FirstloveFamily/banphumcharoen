<?php

namespace App\Http\Controllers;

use App\Models\JobOrder;
use App\Models\JobOrderChecklist;
use App\Models\JobOrderPayment;
use App\Models\AboutUsBlock;
use App\Models\Employer;
use App\Models\DocumentMaster;
use App\Models\DeliverySheet;
use App\Models\DeliverySheetAttachment;
use App\Models\DeliverySheetItem;
use App\Models\NewsCategory;
use App\Models\NewsPost;
use App\Models\JobOrderStatus;
use App\Models\Service;
use App\Models\ServiceChecklist;
use App\Models\Worker;
use App\Models\WorkerDocument;
use App\Models\WorkerPrefix;
use App\Support\UploadLimits;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class StaffPortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $this->authorizeStaff($request);

        $today = now()->startOfDay();
        $next30Days = $today->copy()->addDays(30)->endOfDay();

        $stats = [
            [
                'label' => 'Passport ใกล้หมดอายุ',
                'value' => Worker::query()->active()->whereBetween('passport_expiry', [$today, $next30Days])->count(),
                'description' => 'ภายใน 30 วัน',
                'icon' => 'badge-check',
                'tone' => 'amber',
            ],
            [
                'label' => 'เอกสารอื่นใกล้หมดอายุ',
                'value' => WorkerDocument::query()->whereBetween('expiry_date', [$today, $next30Days])->count(),
                'description' => 'เอกสารแนบทั้งหมด',
                'icon' => 'files',
                'tone' => 'blue',
            ],
            [
                'label' => 'ใบงานที่ยังเปิดอยู่',
                'value' => JobOrder::query()->whereIn('status', ['pending', 'processing', 'waiting_document', 'approved'])->count(),
                'description' => 'รอดำเนินการ',
                'icon' => 'briefcase-business',
                'tone' => 'emerald',
            ],
            [
                'label' => 'สลิปรอตรวจ',
                'value' => JobOrderPayment::query()->where('status', 'pending')->count(),
                'description' => 'รายการชำระเงิน',
                'icon' => 'receipt-text',
                'tone' => 'rose',
            ],
        ];

        $expiringItems = $this->getExpiringItems();

        $pendingReviews = JobOrderChecklist::query()
            ->with(['jobOrder.employer', 'jobOrder.worker', 'documentMaster'])
            ->whereIn('status', ['pending', 'received', 'missing', 'rejected'])
            ->latest()
            ->limit(7)
            ->get();

        $openJobs = JobOrder::query()
            ->with(['employer', 'worker', 'service'])
            ->whereIn('status', ['pending', 'processing', 'waiting_document', 'approved'])
            ->orderByRaw("case priority when 'urgent' then 0 when 'high' then 1 when 'medium' then 2 else 3 end")
            ->orderBy('due_date')
            ->limit(7)
            ->get();

        $pendingPayments = JobOrderPayment::query()
            ->with(['jobOrder.employer', 'jobOrder.worker'])
            ->where('status', 'pending')
            ->latest('payment_date')
            ->limit(5)
            ->get();

        return view('staff-portal.dashboard', compact(
            'stats',
            'expiringItems',
            'pendingReviews',
            'openJobs',
            'pendingPayments',
        ));
    }

    public function calendar(Request $request)
    {
        $this->authorizeStaff($request);

        $workers = Worker::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNotNull('passport_expiry')
                    ->orWhereNotNull('wp_expiry')
                    ->orWhereNotNull('visa_expiry')
                    ->orWhereNotNull('report_90_days_due');
            })
            ->get();

        $jobs = JobOrder::query()
            ->with(['worker', 'employer', 'service'])
            ->whereIn('status', ['pending', 'processing', 'waiting_document', 'approved'])
            ->whereNotNull('due_date')
            ->get();

        return view('staff-portal.calendar', compact('workers', 'jobs'));
    }

    public function tasks(Request $request)
    {
        $this->authorizeStaff($request);

        $statusGroups = [
            'pending' => 'รอเริ่มงาน',
            'processing' => 'กำลังดำเนินการ',
            'waiting_document' => 'รอเอกสาร',
            'approved' => 'อนุมัติแล้ว (เตรียมส่งเล่ม)',
        ];

        $jobs = JobOrder::with(['worker', 'employer', 'service'])
            ->whereIn('status', array_keys($statusGroups))
            ->orderByRaw("case priority when 'urgent' then 0 when 'high' then 1 when 'medium' then 2 else 3 end")
            ->orderBy('due_date', 'asc')
            ->get()
            ->groupBy('status');

        return view('staff-portal.tasks.index', compact('jobs', 'statusGroups'));
    }

    public function reports(Request $request)
    {
        $this->authorizeStaff($request);

        // 1. Financial Analytics
        $totalServiceFee = JobOrder::sum('service_fee');
        $totalPaid = JobOrderPayment::where('status', 'verified')->sum('amount');
        $totalRemaining = $totalServiceFee - $totalPaid;

        // 2. Job Status Analytics
        $jobStats = JobOrder::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // 3. Worker Nationality Distribution
        $nationalities = \App\Models\Nationality::withCount('workers')->get();

        // 4. Aging Receivables (Top Unpaid Employers)
        $agingReceivables = \App\Models\Employer::query()
            ->with(['jobOrders' => function($q) {
                $q->whereIn('payment_status', ['pending', 'partial']);
            }])
            ->get()
            ->map(function($employer) {
                $remaining = $employer->jobOrders->sum(fn($j) => $j->getRemainingAmount());
                return [
                    'name' => $employer->company_name,
                    'code' => $employer->company_code,
                    'remaining' => $remaining,
                    'jobs_count' => $employer->jobOrders->count()
                ];
            })
            ->where('remaining', '>', 0)
            ->sortByDesc('remaining')
            ->take(5);

        return view('staff-portal.reports.index', compact(
            'totalServiceFee', 'totalPaid', 'totalRemaining',
            'jobStats', 'nationalities', 'agingReceivables'
        ));
    }

    public function settings(Request $request)
    {
        $this->authorizeStaff($request);
        $user = auth()->user();
        return view('staff-portal.settings', compact('user'));
    }

    public function users(Request $request)
    {
        $this->authorizeUserManagement($request);

        $keyword = trim((string) $request->query('q', ''));
        $roleFilter = (string) $request->query('role', '');
        $allowedRoles = $this->getManageableRoles();

        $users = User::query()
            ->with(['roles', 'employers'])
            ->when($keyword !== '', function ($query) use ($keyword): void {
                $query->where(function ($subQuery) use ($keyword): void {
                    $subQuery
                        ->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            ->when($roleFilter !== '', function ($query) use ($roleFilter): void {
                $query->whereHas('roles', fn($roleQuery) => $roleQuery->where('name', $roleFilter));
            })
            ->whereHas('roles', fn($query) => $query->whereIn('name', $allowedRoles))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => User::query()->whereHas('roles', fn($query) => $query->whereIn('name', $allowedRoles))->count(),
            'staff' => User::query()->whereHas('roles', fn($query) => $query->where('name', 'staff'))->count(),
            'employer' => User::query()->whereHas('roles', fn($query) => $query->where('name', 'employer'))->count(),
        ];

        $roleLabels = $this->getRoleMap();
        $employers = Employer::query()
            ->active()
            ->orderBy('company_name')
            ->get();

        return view('staff-portal.users.index', compact('users', 'keyword', 'roleFilter', 'summary', 'allowedRoles', 'roleLabels', 'employers'));
    }

    public function userCreate(Request $request)
    {
        $this->authorizeUserManagement($request);

        $roles = Role::query()
            ->whereIn('name', $this->getManageableRoles())
            ->orderBy('name')
            ->get();

        $roleLabels = $this->getRoleMap();
        $employers = Employer::query()
            ->active()
            ->orderBy('company_name')
            ->get();

        return view('staff-portal.users.create', compact('roles', 'roleLabels', 'employers'));
    }

    public function userStore(Request $request)
    {
        $this->authorizeUserManagement($request);

        $allowedRoles = $this->getManageableRoles();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:' . implode(',', $allowedRoles)],
            'employer_id' => ['required_if:role,employer', 'nullable', 'integer', 'exists:employers,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $user->syncRoles([$validated['role']]);
        $this->syncEmployerForUser($user, $validated['role'], isset($validated['employer_id']) ? (int) $validated['employer_id'] : null);

        return redirect()
            ->route('staff.portal.users.index')
            ->with('success', 'สร้างบัญชีผู้ใช้งานเรียบร้อยแล้ว');
    }

    public function userEdit(Request $request, User $user)
    {
        $this->authorizeUserManagement($request);
        $user->load(['roles', 'employers']);

        $roles = Role::query()
            ->whereIn('name', $this->getManageableRoles())
            ->orderBy('name')
            ->get();

        $roleLabels = $this->getRoleMap();
        $employers = Employer::query()
            ->active()
            ->orderBy('company_name')
            ->get();

        return view('staff-portal.users.edit', compact('user', 'roles', 'roleLabels', 'employers'));
    }

    public function userUpdate(Request $request, User $user)
    {
        $this->authorizeUserManagement($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:' . implode(',', array_keys($this->getRoleMap()))],
            'employer_id' => ['required_if:role,employer', 'nullable', 'integer', 'exists:employers,id'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = $validated['password'];
        }

        $user->update($updateData);

        $user->syncRoles([$validated['role']]);
        $this->syncEmployerForUser($user, $validated['role'], isset($validated['employer_id']) ? (int) $validated['employer_id'] : null);

        return redirect()
            ->route('staff.portal.users.index')
            ->with('success', 'อัปเดตบัญชีผู้ใช้งานเรียบร้อยแล้ว');
    }

    public function userDestroy(Request $request, User $user)
    {
        $this->authorizeUserManagement($request);

        if ($request->user()->is($user)) {
            return back()->with('error', 'ไม่สามารถลบบัญชีของตัวเองได้');
        }

        $user->syncRoles([]);
        $user->delete();

        return redirect()
            ->route('staff.portal.users.index')
            ->with('success', 'ลบบัญชีผู้ใช้งานเรียบร้อยแล้ว');
    }

    public function updateSettings(Request $request)
    {
        $this->authorizeStaff($request);
        $user = auth()->user();

        $validated = $request->validate([
            'line_user_id' => [
                'nullable',
                'string',
                'regex:/^U[a-fA-F0-9]{32}$/',
            ],
            'enable_email_notifications' => ['boolean'],
        ], [
            'line_user_id.regex' => 'LINE User ID ต้องขึ้นต้นด้วย U และตามด้วยตัวอักษร/ตัวเลขฐานสิบหก 32 ตัว',
        ]);

        $user->update([
            'line_user_id' => $validated['line_user_id'] ? trim($validated['line_user_id']) : null,
            'enable_email_notifications' => $request->boolean('enable_email_notifications'),
        ]);

        return back()->with('success', 'บันทึกการตั้งค่าเรียบร้อยแล้ว');
    }

    public function newsIndex(Request $request)
    {
        $this->authorizeStaff($request);

        $keyword = $request->string('q')->trim()->toString();
        $status = $request->string('status')->toString();
        $categoryId = $request->integer('category_id', 0);

        $newsPosts = NewsPost::query()
            ->with(['category', 'user'])
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('title', 'like', "%{$keyword}%")
                        ->orWhere('slug', 'like', "%{$keyword}%")
                        ->orWhere('excerpt', 'like', "%{$keyword}%");
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($categoryId > 0, fn ($query) => $query->where('category_id', $categoryId))
            ->latest('published_at')
            ->latest('created_at')
            ->paginate(12)
            ->withQueryString();

        $summary = [
            'total' => NewsPost::count(),
            'published' => NewsPost::query()->where('status', 'published')->count(),
            'draft' => NewsPost::query()->where('status', 'draft')->count(),
            'pinned' => NewsPost::query()->where('is_pinned', true)->count(),
        ];

        $categories = NewsCategory::query()->orderBy('name')->get();

        return view('staff-portal.news.index', compact(
            'newsPosts',
            'keyword',
            'status',
            'categoryId',
            'summary',
            'categories',
        ));
    }

    public function newsCreate(Request $request)
    {
        $this->authorizeStaff($request);

        $newsPost = new NewsPost([
            'status' => 'draft',
            'views_count' => 0,
            'is_pinned' => false,
        ]);
        $categories = NewsCategory::query()->orderBy('name')->get();

        return view('staff-portal.news.form', compact('newsPost', 'categories'));
    }

    public function newsStore(Request $request)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:news_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:news_posts,slug'],
            'excerpt' => ['nullable', 'string', 'max:2000'],
            'content' => ['required', 'string'],
            'image_cover' => UploadLimits::imageRules(),
            'status' => ['required', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        $imageCover = null;
        if ($request->hasFile('image_cover')) {
            $imageCover = $request->file('image_cover')->store('news-covers', 'public');
        }

        $publishedAt = $validated['published_at'] ?? null;
        if ($validated['status'] === 'published' && blank($publishedAt)) {
            $publishedAt = now();
        }

        NewsPost::create([
            'category_id' => $validated['category_id'],
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'slug' => $validated['slug'] ? Str::slug($validated['slug']) : Str::slug($validated['title']),
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'],
            'image_cover' => $imageCover,
            'views_count' => 0,
            'is_pinned' => $request->boolean('is_pinned'),
            'status' => $validated['status'],
            'published_at' => $publishedAt,
        ]);

        return redirect()
            ->route('staff.portal.news.index')
            ->with('success', 'เพิ่มข่าวสารเรียบร้อยแล้ว');
    }

    public function newsEdit(Request $request, NewsPost $newsPost)
    {
        $this->authorizeStaff($request);

        $categories = NewsCategory::query()->orderBy('name')->get();

        return view('staff-portal.news.form', compact('newsPost', 'categories'));
    }

    public function newsUpdate(Request $request, NewsPost $newsPost)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:news_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:news_posts,slug,' . $newsPost->id],
            'excerpt' => ['nullable', 'string', 'max:2000'],
            'content' => ['required', 'string'],
            'image_cover' => UploadLimits::imageRules(),
            'status' => ['required', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        $imageCover = $newsPost->image_cover;
        if ($request->hasFile('image_cover')) {
            if ($imageCover) {
                Storage::disk('public')->delete($imageCover);
            }
            $imageCover = $request->file('image_cover')->store('news-covers', 'public');
        }

        $publishedAt = $validated['published_at'] ?? null;
        if ($validated['status'] === 'published' && blank($publishedAt)) {
            $publishedAt = $newsPost->published_at ?? now();
        }

        $newsPost->update([
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'slug' => $validated['slug'] ? Str::slug($validated['slug']) : Str::slug($validated['title']),
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'],
            'image_cover' => $imageCover,
            'is_pinned' => $request->boolean('is_pinned'),
            'status' => $validated['status'],
            'published_at' => $publishedAt,
        ]);

        return redirect()
            ->route('staff.portal.news.index')
            ->with('success', 'อัปเดตข่าวสารเรียบร้อยแล้ว');
    }

    public function newsDestroy(Request $request, NewsPost $newsPost)
    {
        $this->authorizeStaff($request);

        if ($newsPost->image_cover) {
            Storage::disk('public')->delete($newsPost->image_cover);
        }

        $newsPost->delete();

        return back()->with('success', 'ลบข่าวสารเรียบร้อยแล้ว');
    }

    public function aboutUsIndex(Request $request)
    {
        $this->authorizeStaff($request);

        $blocks = AboutUsBlock::query()
            ->orderBy('section')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $summary = [
            'total' => AboutUsBlock::count(),
            'feature' => AboutUsBlock::query()->where('section', 'feature')->count(),
            'value' => AboutUsBlock::query()->where('section', 'value')->count(),
            'active' => AboutUsBlock::active()->count(),
        ];

        return view('staff-portal.about-us.index', compact('blocks', 'summary'));
    }

    public function aboutUsCreate(Request $request)
    {
        $this->authorizeStaff($request);

        $aboutUsBlock = new AboutUsBlock([
            'section' => 'feature',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        return view('staff-portal.about-us.form', compact('aboutUsBlock'));
    }

    public function aboutUsStore(Request $request)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'section' => ['required', 'in:feature,value'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'icon' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        AboutUsBlock::create([
            'section' => $validated['section'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('staff.portal.about-us.index')
            ->with('success', 'เพิ่มข้อมูล About us เรียบร้อยแล้ว');
    }

    public function aboutUsEdit(Request $request, AboutUsBlock $aboutUsBlock)
    {
        $this->authorizeStaff($request);

        return view('staff-portal.about-us.form', compact('aboutUsBlock'));
    }

    public function aboutUsUpdate(Request $request, AboutUsBlock $aboutUsBlock)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'section' => ['required', 'in:feature,value'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'icon' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $aboutUsBlock->update([
            'section' => $validated['section'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', false),
        ]);

        return redirect()
            ->route('staff.portal.about-us.index')
            ->with('success', 'อัปเดตข้อมูล About us เรียบร้อยแล้ว');
    }

    public function aboutUsDestroy(Request $request, AboutUsBlock $aboutUsBlock)
    {
        $this->authorizeStaff($request);

        $aboutUsBlock->delete();

        return back()->with('success', 'ลบข้อมูล About us เรียบร้อยแล้ว');
    }

    public function documentMasterCreate(Request $request)
    {
        $this->authorizeStaff($request);

        return view('staff-portal.document-masters.create');
    }

    public function documentMasterStore(Request $request)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', 'unique:document_masters,code'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        DocumentMaster::create($validated);

        return redirect()
            ->route('staff.portal.document-masters.index')
            ->with('success', 'เพิ่มประเภทเอกสารเรียบร้อยแล้ว');
    }

    public function documentMasterIndex(Request $request)
    {
        $this->authorizeStaff($request);

        $documentMasters = DocumentMaster::query()
            ->withCount(['serviceChecklists', 'jobOrderChecklists', 'workerDocuments'])
            ->orderBy('name')
            ->get();

        $summary = [
            'total' => DocumentMaster::count(),
            'active' => DocumentMaster::active()->count(),
            'inactive' => DocumentMaster::query()->where('is_active', false)->count(),
            'service_links' => ServiceChecklist::count(),
        ];

        return view('staff-portal.document-masters.index', compact('documentMasters', 'summary'));
    }

    public function documentMasterEdit(Request $request, DocumentMaster $documentMaster)
    {
        $this->authorizeStaff($request);

        $documentMaster->loadCount(['serviceChecklists', 'jobOrderChecklists', 'workerDocuments']);

        return view('staff-portal.document-masters.edit', compact('documentMaster'));
    }

    public function documentMasterUpdate(Request $request, DocumentMaster $documentMaster)
    {
        $this->authorizeStaff($request);

        $oldName = $documentMaster->name;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', 'unique:document_masters,code,' . $documentMaster->id],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);
        $documentMaster->update($validated);

        if ($oldName !== $documentMaster->name) {
            ServiceChecklist::where('document_name', $oldName)
                ->update(['document_name' => $documentMaster->name]);
        }

        return redirect()
            ->route('staff.portal.document-masters.index')
            ->with('success', 'อัปเดตประเภทเอกสารเรียบร้อยแล้ว');
    }

    public function jobOrderStatusesIndex(Request $request)
    {
        $this->authorizeStaff($request);

        $jobOrderStatuses = JobOrderStatus::query()
            ->withCount('jobOrders')
            ->orderBy('sort_order')
            ->orderBy('name_th')
            ->get();

        $summary = [
            'total' => JobOrderStatus::count(),
            'active' => JobOrderStatus::active()->count(),
            'inactive' => JobOrderStatus::query()->where('is_active', false)->count(),
            'jobs' => JobOrder::query()->count(),
        ];

        return view('staff-portal.job-order-statuses.index', compact('jobOrderStatuses', 'summary'));
    }

    public function jobOrderStatusCreate(Request $request)
    {
        $this->authorizeStaff($request);

        $jobOrderStatus = null;

        return view('staff-portal.job-order-statuses.form', compact('jobOrderStatus'));
    }

    public function jobOrderStatusStore(Request $request)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:job_order_statuses,code'],
            'name_th' => ['required', 'string', 'max:100'],
            'name_en' => ['nullable', 'string', 'max:100'],
            'badge_class' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
            'requires_note' => ['nullable', 'boolean'],
            'sets_completed_at' => ['nullable', 'boolean'],
        ]);

        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_default'] = $request->boolean('is_default', false);
        $validated['requires_note'] = $request->boolean('requires_note', false);
        $validated['sets_completed_at'] = $request->boolean('sets_completed_at', false);

        if ($validated['is_default']) {
            JobOrderStatus::query()->update(['is_default' => false]);
        }

        JobOrderStatus::create($validated);

        return redirect()
            ->route('staff.portal.job-order-statuses.index')
            ->with('success', 'เพิ่มสถานะใบงานเรียบร้อยแล้ว');
    }

    public function jobOrderStatusEdit(Request $request, JobOrderStatus $jobOrderStatus)
    {
        $this->authorizeStaff($request);

        return view('staff-portal.job-order-statuses.form', compact('jobOrderStatus'));
    }

    public function jobOrderStatusUpdate(Request $request, JobOrderStatus $jobOrderStatus)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:job_order_statuses,code,' . $jobOrderStatus->id],
            'name_th' => ['required', 'string', 'max:100'],
            'name_en' => ['nullable', 'string', 'max:100'],
            'badge_class' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
            'requires_note' => ['nullable', 'boolean'],
            'sets_completed_at' => ['nullable', 'boolean'],
        ]);

        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active', false);
        $validated['is_default'] = $request->boolean('is_default', false);
        $validated['requires_note'] = $request->boolean('requires_note', false);
        $validated['sets_completed_at'] = $request->boolean('sets_completed_at', false);

        if ($validated['is_default']) {
            JobOrderStatus::query()
                ->whereKeyNot($jobOrderStatus->id)
                ->update(['is_default' => false]);
        }

        $jobOrderStatus->update($validated);

        return redirect()
            ->route('staff.portal.job-order-statuses.index')
            ->with('success', 'อัปเดตสถานะใบงานเรียบร้อยแล้ว');
    }

    public function jobOrderStatusDestroy(Request $request, JobOrderStatus $jobOrderStatus)
    {
        $this->authorizeStaff($request);

        if ($jobOrderStatus->jobOrders()->exists()) {
            return back()->with('error', 'ไม่สามารถลบสถานะนี้ได้ เพราะยังมีใบงานใช้งานอยู่');
        }

        if ($jobOrderStatus->is_default) {
            return back()->with('error', 'ไม่สามารถลบสถานะเริ่มต้นของระบบได้');
        }

        $jobOrderStatus->delete();

        return back()->with('success', 'ลบสถานะใบงานเรียบร้อยแล้ว');
    }

    public function workerPrefixesIndex(Request $request)
    {
        $this->authorizeStaff($request);

        $workerPrefixes = WorkerPrefix::query()
            ->withCount('workers')
            ->orderBy('sort_order')
            ->orderBy('name_th')
            ->get();

        $summary = [
            'total' => WorkerPrefix::count(),
            'active' => WorkerPrefix::active()->count(),
            'inactive' => WorkerPrefix::query()->where('is_active', false)->count(),
            'workers' => Worker::query()->whereNotNull('worker_prefix_id')->count(),
        ];

        return view('staff-portal.worker-prefixes.index', compact('workerPrefixes', 'summary'));
    }

    public function workerPrefixCreate(Request $request)
    {
        $this->authorizeStaff($request);

        $workerPrefix = null;

        return view('staff-portal.worker-prefixes.form', compact('workerPrefix'));
    }

    public function workerPrefixStore(Request $request)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:worker_prefixes,code'],
            'name_th' => ['required', 'string', 'max:100'],
            'name_en' => ['required', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active', true);

        WorkerPrefix::create($validated);

        return redirect()
            ->route('staff.portal.worker-prefixes.index')
            ->with('success', 'เพิ่มคำนำหน้าชื่อเรียบร้อยแล้ว');
    }

    public function workerPrefixEdit(Request $request, WorkerPrefix $workerPrefix)
    {
        $this->authorizeStaff($request);

        return view('staff-portal.worker-prefixes.form', compact('workerPrefix'));
    }

    public function workerPrefixUpdate(Request $request, WorkerPrefix $workerPrefix)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:worker_prefixes,code,' . $workerPrefix->id],
            'name_th' => ['required', 'string', 'max:100'],
            'name_en' => ['required', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active', false);

        $workerPrefix->update($validated);

        return redirect()
            ->route('staff.portal.worker-prefixes.index')
            ->with('success', 'อัปเดตคำนำหน้าชื่อเรียบร้อยแล้ว');
    }

    public function workerPrefixDestroy(Request $request, WorkerPrefix $workerPrefix)
    {
        $this->authorizeStaff($request);

        if ($workerPrefix->workers()->exists()) {
            return back()->with('error', 'ไม่สามารถลบคำนำหน้าชื่อนี้ได้ เพราะยังมีแรงงานใช้งานอยู่');
        }

        $workerPrefix->delete();

        return back()->with('success', 'ลบคำนำหน้าชื่อเรียบร้อยแล้ว');
    }

    public function jobOrderCreate(Request $request)
    {
        $this->authorizeStaff($request);

        $employerId = (int) $request->query('employer_id', 0);
        $workerId = (int) $request->query('worker_id', 0);
        $serviceId = (int) $request->query('service_id', 0);

        $employers = Employer::query()
            ->active()
            ->orderBy('company_name')
            ->get();

        $workers = Worker::query()
            ->with('employer')
            ->active()
            ->orderBy('first_name_th')
            ->get();

        $services = Service::query()
            ->active()
            ->with(['checklists' => fn($query) => $query->orderBy('sort_order')])
            ->orderBy('name')
            ->get();

        return view('staff-portal.job-orders.create', compact(
            'employers',
            'workers',
            'services',
            'employerId',
            'workerId',
            'serviceId',
        ));
    }

    public function jobOrderStore(Request $request)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'employer_id' => ['required', 'integer'],
            'worker_id' => ['required', 'integer'],
            'service_id' => ['required', 'integer'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'due_date' => ['nullable', 'date'],
            'service_fee' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $employer = Employer::query()
            ->active()
            ->findOrFail($validated['employer_id']);

        $worker = Worker::query()
            ->active()
            ->where('id', $validated['worker_id'])
            ->where('employer_id', $employer->id)
            ->firstOrFail();

        $service = Service::query()
            ->active()
            ->with(['checklists' => fn($query) => $query->orderBy('sort_order')])
            ->findOrFail($validated['service_id']);

        $defaultStatus = JobOrderStatus::query()
            ->active()
            ->where('is_default', true)
            ->orderBy('sort_order')
            ->first();

        $jobOrder = DB::transaction(function () use ($validated, $employer, $worker, $service, $request, $defaultStatus) {
            $jobOrder = JobOrder::create([
                'employer_id' => $employer->id,
                'worker_id' => $worker->id,
                'service_id' => $service->id,
                'assigned_user_id' => $request->user()?->id,
                'service_fee' => $validated['service_fee'],
                'paid_amount' => 0,
                'payment_status' => 'pending',
                'status' => $defaultStatus?->code ?: 'pending',
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

            \App\Models\JobOrderLog::create([
                'job_order_id' => $jobOrder->id,
                'user_id' => $request->user()?->id,
                'action' => 'สร้างใบงานใหม่',
                'description' => 'สร้างใบงานสำหรับ ' . ($worker->full_name_th ?: $worker->full_name_en) . ' ภายใต้ ' . $employer->company_name,
            ]);

            return $jobOrder;
        });

        return redirect()
            ->route('staff.portal.job-orders.show', $jobOrder)
            ->with('success', 'สร้างใบงานใหม่เรียบร้อยแล้ว');
    }

    public function services(Request $request)
    {
        $this->authorizeStaff($request);

        $keyword = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $services = Service::query()
            ->withCount(['checklists', 'jobOrders'])
            ->when($keyword !== '', function ($query) use ($keyword): void {
                $query->where(function ($subQuery) use ($keyword): void {
                    $subQuery
                        ->where('name', 'like', "%{$keyword}%")
                        ->orWhere('code', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%");
                });
            })
            ->when($status === 'active', fn($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => Service::count(),
            'active' => Service::active()->count(),
            'inactive' => Service::query()->where('is_active', false)->count(),
            'checklists' => ServiceChecklist::count(),
        ];

        return view('staff-portal.services.index', compact(
            'services',
            'keyword',
            'status',
            'summary',
        ));
    }

    public function serviceCreate(Request $request)
    {
        $this->authorizeStaff($request);

        $service = null;

        return view('staff-portal.services.form', compact('service'));
    }

    public function serviceStore(Request $request)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:services,name'],
            'code' => ['required', 'string', 'max:100', 'unique:services,code'],
            'description' => ['nullable', 'string', 'max:2000'],
            'alert_days_before_expiry' => ['required', 'integer', 'min:0', 'max:3650'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $service = Service::create($validated);

        return redirect()
            ->route('staff.portal.services.show', $service)
            ->with('success', 'เพิ่มบริการเรียบร้อยแล้ว');
    }

    public function serviceShow(Request $request, Service $service)
    {
        $this->authorizeStaff($request);

        $service->load([
            'checklists' => fn($query) => $query->orderBy('sort_order'),
            'jobOrders',
        ]);

        $documentMasters = DocumentMaster::query()
            ->orderBy('name')
            ->get();

        $summary = [
            'documents_total' => $service->checklists->count(),
            'documents_required' => $service->checklists->where('is_required', true)->count(),
            'job_orders' => $service->jobOrders->count(),
        ];

        return view('staff-portal.services.show', compact(
            'service',
            'documentMasters',
            'summary',
        ));
    }

    public function serviceEdit(Request $request, Service $service)
    {
        $this->authorizeStaff($request);

        return view('staff-portal.services.form', compact('service'));
    }

    public function serviceUpdate(Request $request, Service $service)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:services,name,' . $service->id],
            'code' => ['required', 'string', 'max:100', 'unique:services,code,' . $service->id],
            'description' => ['nullable', 'string', 'max:2000'],
            'alert_days_before_expiry' => ['required', 'integer', 'min:0', 'max:3650'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);

        $service->update($validated);

        return redirect()
            ->route('staff.portal.services.show', $service)
            ->with('success', 'อัปเดตบริการเรียบร้อยแล้ว');
    }

    public function serviceDestroy(Request $request, Service $service)
    {
        $this->authorizeStaff($request);

        if ($service->jobOrders()->exists()) {
            return back()->with('error', 'ไม่สามารถลบบริการที่มีใบงานใช้งานอยู่ได้');
        }

        ServiceChecklist::query()->where('service_id', $service->id)->delete();
        $service->delete();

        return redirect()
            ->route('staff.portal.services.index')
            ->with('success', 'ลบบริการเรียบร้อยแล้ว');
    }

    public function serviceChecklistStore(Request $request, Service $service)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'document_name' => ['required', 'string', 'max:255', 'exists:document_masters,name'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_required' => ['nullable', 'boolean'],
        ]);

        $exists = $service->checklists()
            ->where('document_name', $validated['document_name'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'มีเอกสารนี้อยู่ในบริการแล้ว');
        }

        ServiceChecklist::create([
            'service_id' => $service->id,
            'document_name' => $validated['document_name'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_required' => $request->boolean('is_required', true),
        ]);

        return redirect()
            ->route('staff.portal.services.show', $service)
            ->with('success', 'เพิ่มเอกสารที่ใช้กับบริการเรียบร้อยแล้ว');
    }

    public function serviceChecklistEdit(Request $request, Service $service, ServiceChecklist $serviceChecklist)
    {
        $this->authorizeStaff($request);
        abort_unless($serviceChecklist->service_id === $service->id, 404);

        $documentMasters = DocumentMaster::query()
            ->orderBy('name')
            ->get();

        return view('staff-portal.services.document-edit', compact(
            'service',
            'serviceChecklist',
            'documentMasters',
        ));
    }

    public function serviceChecklistUpdate(Request $request, Service $service, ServiceChecklist $serviceChecklist)
    {
        $this->authorizeStaff($request);
        abort_unless($serviceChecklist->service_id === $service->id, 404);

        $validated = $request->validate([
            'document_name' => ['required', 'string', 'max:255', 'exists:document_masters,name'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_required' => ['nullable', 'boolean'],
        ]);

        $duplicate = $service->checklists()
            ->where('document_name', $validated['document_name'])
            ->where('id', '!=', $serviceChecklist->id)
            ->exists();

        if ($duplicate) {
            return back()->with('error', 'มีเอกสารนี้อยู่ในบริการแล้ว');
        }

        $serviceChecklist->update([
            'document_name' => $validated['document_name'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_required' => $request->boolean('is_required', false),
        ]);

        return redirect()
            ->route('staff.portal.services.show', $service)
            ->with('success', 'อัปเดตเอกสารที่ใช้กับบริการเรียบร้อยแล้ว');
    }

    public function serviceChecklistDestroy(Request $request, Service $service, ServiceChecklist $serviceChecklist)
    {
        $this->authorizeStaff($request);
        abort_unless($serviceChecklist->service_id === $service->id, 404);

        $serviceChecklist->delete();

        return redirect()
            ->route('staff.portal.services.show', $service)
            ->with('success', 'ลบเอกสารที่ใช้กับบริการเรียบร้อยแล้ว');
    }

    public function employers(Request $request)
    {
        $this->authorizeStaff($request);

        $keyword = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $employers = \App\Models\Employer::query()
            ->withCount(['workers', 'jobOrders'])
            ->when($keyword !== '', function ($query) use ($keyword): void {
                $query->where(function ($q) use ($keyword) {
                    $q->where('company_name', 'like', "%{$keyword}%")
                        ->orWhere('company_code', 'like', "%{$keyword}%")
                        ->orWhere('contact_name', 'like', "%{$keyword}%");
                });
            })
            ->when($status !== '', fn($query) => $query->where('is_active', $status === 'active'))
            ->orderBy('company_name')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => \App\Models\Employer::count(),
            'active' => \App\Models\Employer::where('is_active', true)->count(),
            'inactive' => \App\Models\Employer::where('is_active', false)->count(),
        ];

        return view('staff-portal.employers.index', compact('employers', 'keyword', 'status', 'summary'));
    }

    public function employerCreate(Request $request)
    {
        $this->authorizeStaff($request);

        $companyCode = $this->generateEmployerCode();

        return view('staff-portal.employers.create', compact('companyCode'));
    }

    public function employerStore(Request $request)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'company_code' => 'nullable|string|max:255',
            'company_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'tax_id' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            'logo_file' => UploadLimits::imageRules(),
        ]);

        $validated['company_code'] = $this->generateEmployerCode();
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('logo_file')) {
            $validated['logo'] = $request->file('logo_file')->store('employers/logos', 'public');
        }

        unset($validated['logo_file']);

        \App\Models\Employer::create($validated);

        return redirect()->route('staff.portal.employers.index')
            ->with('success', 'เพิ่มข้อมูลนายจ้างเรียบร้อยแล้ว');
    }

    public function employerShow(Request $request, \App\Models\Employer $employer)
    {
        $this->authorizeStaff($request);

        $employer->load(['workers.nationality', 'workers.workerPrefix']);

        $summary = [
            'total_workers' => $employer->workers->count(),
            'active_workers' => $employer->workers->where('is_active', true)->count(),
            'inactive_workers' => $employer->workers->where('is_active', false)->count(),
        ];

        return view('staff-portal.employers.show', compact('employer', 'summary'));
    }

    public function employerEdit(Request $request, \App\Models\Employer $employer)
    {
        $this->authorizeStaff($request);

        return view('staff-portal.employers.edit', compact('employer'));
    }

    public function employerUpdate(Request $request, \App\Models\Employer $employer)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'company_code' => 'required|string|max:255|unique:employers,company_code,' . $employer->id,
            'company_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'tax_id' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            'logo_file' => UploadLimits::imageRules(),
        ]);

        if ($request->hasFile('logo_file')) {
            if ($employer->logo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($employer->logo);
            }
            $validated['logo'] = $request->file('logo_file')->store('employers/logos', 'public');
        }

        unset($validated['logo_file']);

        $employer->update($validated);

        return redirect()->route('staff.portal.employers.show', $employer)
            ->with('success', 'แก้ไขข้อมูลนายจ้างเรียบร้อยแล้ว');
    }

    private function generateEmployerCode(): string
    {
        $prefix = 'EMP-';
        $maxNumber = Employer::query()
            ->pluck('company_code')
            ->map(function (string $companyCode) use ($prefix): ?int {
                if (! preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $companyCode, $matches)) {
                    return null;
                }

                return (int) $matches[1];
            })
            ->filter()
            ->max() ?? 0;

        do {
            $maxNumber++;
            $companyCode = sprintf('%s%03d', $prefix, $maxNumber);
        } while (Employer::query()->where('company_code', $companyCode)->exists());

        return $companyCode;
    }

    public function notifications(Request $request)
    {
        $this->authorizeStaff($request);

        $notifications = \App\Models\Notification::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('staff-portal.notifications.index', compact('notifications'));
    }

    public function markNotificationRead(Request $request, \App\Models\Notification $notification)
    {
        $this->authorizeStaff($request);

        if ($notification->user_id === $request->user()->id) {
            $notification->markAsRead();
        }

        return back();
    }

    public function markAllNotificationsRead(Request $request)
    {
        $this->authorizeStaff($request);

        \App\Models\Notification::query()
            ->where('user_id', $request->user()->id)
            ->unread()
            ->update(['is_read' => true]);

        return back()->with('success', 'อ่านการแจ้งเตือนทั้งหมดแล้ว');
    }

    public function workers(Request $request)
    {
        $this->authorizeStaff($request);

        $keyword = trim((string) $request->query('q', ''));
        $expiryStatus = (string) $request->query('expiry', '');
        $activeStatus = (string) $request->query('active', '');
        $today = now()->startOfDay();
        $soon = now()->copy()->addDays(45)->endOfDay();

        $workers = Worker::query()
            ->with(['employer', 'nationality', 'workerPrefix'])
            ->withCount('jobOrders')
            ->when($keyword !== '', function ($query) use ($keyword): void {
                $query->where(function ($subQuery) use ($keyword): void {
                    $subQuery
                        ->where('first_name_th', 'like', "%{$keyword}%")
                        ->orWhere('last_name_th', 'like', "%{$keyword}%")
                        ->orWhere('first_name_en', 'like', "%{$keyword}%")
                        ->orWhere('last_name_en', 'like', "%{$keyword}%")
                        ->orWhere('passport_number', 'like', "%{$keyword}%")
                        ->orWhere('wp_number', 'like', "%{$keyword}%")
                        ->orWhereHas('employer', fn($employerQuery) => $employerQuery->where('company_name', 'like', "%{$keyword}%"));
                });
            })
            ->when($expiryStatus === 'expiring', function ($query) use ($today, $soon): void {
                $query->where(function ($subQuery) use ($today, $soon): void {
                    $subQuery
                        ->whereBetween('wp_expiry', [$today, $soon])
                        ->orWhereBetween('visa_expiry', [$today, $soon])
                        ->orWhereBetween('passport_expiry', [$today, $soon])
                        ->orWhereBetween('report_90_days_due', [$today, $soon]);
                });
            })
            ->when($expiryStatus === 'expired', function ($query) use ($today): void {
                $query->where(function ($subQuery) use ($today): void {
                    $subQuery
                        ->whereDate('wp_expiry', '<', $today)
                        ->orWhereDate('visa_expiry', '<', $today)
                        ->orWhereDate('passport_expiry', '<', $today)
                        ->orWhereDate('report_90_days_due', '<', $today);
                });
            })
            ->when($activeStatus === 'active', fn($query) => $query->where('is_active', true))
            ->when($activeStatus === 'inactive', fn($query) => $query->where('is_active', false))
            ->orderByDesc('is_active')
            ->orderBy('first_name_th')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => Worker::query()->count(),
            'active' => Worker::query()->active()->count(),
            'expiring' => Worker::query()
                ->where(function ($query) use ($today, $soon): void {
                    $query
                        ->whereBetween('wp_expiry', [$today, $soon])
                        ->orWhereBetween('visa_expiry', [$today, $soon])
                        ->orWhereBetween('passport_expiry', [$today, $soon])
                        ->orWhereBetween('report_90_days_due', [$today, $soon]);
                })
                ->count(),
            'expired' => Worker::query()
                ->where(function ($query) use ($today): void {
                    $query
                        ->whereDate('wp_expiry', '<', $today)
                        ->orWhereDate('visa_expiry', '<', $today)
                        ->orWhereDate('passport_expiry', '<', $today)
                        ->orWhereDate('report_90_days_due', '<', $today);
                })
                ->count(),
        ];

        return view('staff-portal.workers.index', compact(
            'workers',
            'keyword',
            'expiryStatus',
            'activeStatus',
            'summary',
        ));
    }

    public function workerCreate(Request $request)
    {
        $this->authorizeStaff($request);

        $employers = \App\Models\Employer::orderBy('company_name')->get();
        $nationalities = \App\Models\Nationality::orderBy('name_th')->get();
        $workerPrefixes = WorkerPrefix::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name_th')
            ->get();

        return view('staff-portal.workers.create', compact('employers', 'nationalities', 'workerPrefixes'));
    }

    public function workerStore(Request $request)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'employer_id' => 'required|exists:employers,id',
            'nationality_id' => 'required|exists:nationalities,id',
            'is_active' => 'boolean',
            'worker_prefix_id' => 'nullable|exists:worker_prefixes,id',
            'first_name_th' => 'required|string|max:150',
            'last_name_th' => 'nullable|string|max:150',
            'first_name_en' => 'required|string|max:150',
            'last_name_en' => 'nullable|string|max:150',
            'birth_date' => 'required|date',
            'gender' => 'nullable|string|max:20',

            'passport_number' => 'nullable|string|max:100',
            'passport_expiry' => 'nullable|date',
            'wp_number' => 'nullable|string|max:100',
            'wp_expiry' => 'nullable|date',
            'visa_expiry' => 'nullable|date',
            'report_90_days_due' => 'nullable|date',

            'photo_file' => UploadLimits::imageRules(),
            'passport_file' => UploadLimits::documentRules(false, ['pdf', 'jpg', 'jpeg', 'png']),
            'wp_file' => UploadLimits::documentRules(false, ['pdf', 'jpg', 'jpeg', 'png']),
            'visa_file' => UploadLimits::documentRules(false, ['pdf', 'jpg', 'jpeg', 'png']),
            'report_90_days_file' => UploadLimits::documentRules(false, ['pdf', 'jpg', 'jpeg', 'png']),
        ]);

        $prefix = ! empty($validated['worker_prefix_id'])
            ? WorkerPrefix::find($validated['worker_prefix_id'])
            : null;

        $validated['prefix_th'] = $prefix?->name_th;
        $validated['prefix_en'] = $prefix?->name_en;

        if ($request->hasFile('photo_file')) {
            $validated['photo_path'] = $request->file('photo_file')->store('worker-photos', 'public');
        }

        $files = ['passport_file' => 'workers/passports', 'wp_file' => 'workers/wp', 'visa_file' => 'workers/visa', 'report_90_days_file' => 'workers/report_90_days'];
        foreach ($files as $field => $path) {
            if ($request->hasFile($field)) {
                $validated[$field] = $request->file($field)->store($path, 'public');
            }
        }

        unset($validated['photo_file']);

        $worker = \App\Models\Worker::create($validated);

        return redirect()->route('staff.portal.workers.show', $worker)
            ->with('success', 'เพิ่มข้อมูลแรงงานเรียบร้อยแล้ว');
    }

    public function workerShow(Request $request, Worker $worker)
    {
        $this->authorizeStaff($request);

        $documentMasters = DocumentMaster::query()
            ->active()
            ->orderBy('name')
            ->get();

        $worker->load([
            'employer',
            'nationality',
            'workerPrefix',
            'documents.documentMaster',
            'jobOrders' => fn($query) => $query
                ->with(['service', 'payments', 'checklists'])
                ->latest('updated_at'),
        ]);

        $expiryCards = [
            ['label' => 'Passport', 'date' => $worker->passport_expiry, 'icon' => 'badge-check'],
            ['label' => 'Work Permit', 'date' => $worker->wp_expiry, 'icon' => 'id-card'],
            ['label' => 'Visa', 'date' => $worker->visa_expiry, 'icon' => 'stamp'],
            ['label' => '90 Days Report', 'date' => $worker->report_90_days_due, 'icon' => 'calendar-clock'],
        ];

        $summary = [
            'total_jobs' => $worker->jobOrders->count(),
            'open_jobs' => $worker->jobOrders
                ->whereIn('status', ['pending', 'processing', 'waiting_document', 'approved'])
                ->count(),
            'documents' => $worker->documents->count(),
            'unpaid_amount' => $worker->jobOrders->sum(fn(JobOrder $jobOrder): float => $jobOrder->getRemainingAmount()),
        ];

        return view('staff-portal.workers.show', compact('worker', 'expiryCards', 'summary', 'documentMasters'));
    }

    public function workerEdit(Request $request, Worker $worker)
    {
        $this->authorizeStaff($request);

        $employers = \App\Models\Employer::orderBy('company_name')->get();
        $nationalities = \App\Models\Nationality::orderBy('name_th')->get();
        $workerPrefixes = WorkerPrefix::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name_th')
            ->get();

        return view('staff-portal.workers.edit', compact('worker', 'employers', 'nationalities', 'workerPrefixes'));
    }

    public function workerUpdate(Request $request, Worker $worker)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'employer_id' => 'required|exists:employers,id',
            'nationality_id' => 'required|exists:nationalities,id',
            'is_active' => 'boolean',
            'worker_prefix_id' => 'nullable|exists:worker_prefixes,id',
            'first_name_th' => 'required|string|max:150',
            'last_name_th' => 'nullable|string|max:150',
            'first_name_en' => 'required|string|max:150',
            'last_name_en' => 'nullable|string|max:150',
            'birth_date' => 'required|date',
            'gender' => 'nullable|string|max:20',

            'passport_number' => 'nullable|string|max:100',
            'passport_expiry' => 'nullable|date',
            'wp_number' => 'nullable|string|max:100',
            'wp_expiry' => 'nullable|date',
            'visa_expiry' => 'nullable|date',
            'report_90_days_due' => 'nullable|date',

            'photo_file' => UploadLimits::imageRules(),
            'passport_file' => UploadLimits::documentRules(false, ['pdf', 'jpg', 'jpeg', 'png']),
            'wp_file' => UploadLimits::documentRules(false, ['pdf', 'jpg', 'jpeg', 'png']),
            'visa_file' => UploadLimits::documentRules(false, ['pdf', 'jpg', 'jpeg', 'png']),
            'report_90_days_file' => UploadLimits::documentRules(false, ['pdf', 'jpg', 'jpeg', 'png']),
        ]);

        $prefix = ! empty($validated['worker_prefix_id'])
            ? WorkerPrefix::find($validated['worker_prefix_id'])
            : null;

        $validated['prefix_th'] = $prefix?->name_th;
        $validated['prefix_en'] = $prefix?->name_en;

        if ($request->hasFile('photo_file')) {
            if ($worker->photo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($worker->photo_path);
            }
            $validated['photo_path'] = $request->file('photo_file')->store('worker-photos', 'public');
        }

        $files = ['passport_file' => 'workers/passports', 'wp_file' => 'workers/wp', 'visa_file' => 'workers/visa', 'report_90_days_file' => 'workers/report_90_days'];
        foreach ($files as $field => $path) {
            if ($request->hasFile($field)) {
                if ($worker->$field) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($worker->$field);
                }
                $validated[$field] = $request->file($field)->store($path, 'public');
            }
        }

        unset($validated['photo_file']);

        $worker->update($validated);

        return redirect()->route('staff.portal.workers.show', $worker)
            ->with('success', 'แก้ไขข้อมูลแรงงานเรียบร้อยแล้ว');
    }

    public function workerDestroy(Request $request, Worker $worker)
    {
        $this->authorizeStaff($request);

        DB::transaction(function () use ($worker): void {
            $worker->loadMissing('documents');

            foreach ([
                'photo_path',
                'passport_file',
                'wp_file',
                'visa_file',
                'report_90_days_file',
            ] as $field) {
                if ($worker->$field) {
                    Storage::disk('public')->delete($worker->$field);
                }
            }

            foreach ($worker->documents as $document) {
                if ($document->file_path) {
                    Storage::disk('public')->delete($document->file_path);
                }
                $document->delete();
            }

            $worker->delete();
        });

        return redirect()
            ->route('staff.portal.workers.index')
            ->with('success', 'ลบแรงงานเรียบร้อยแล้ว');
    }

    public function workerDocumentStore(Request $request, Worker $worker)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'document_master_id' => ['required', 'exists:document_masters,id'],
            'file' => UploadLimits::fileRules(true, ['pdf', 'jpg', 'jpeg', 'png', 'webp']),
            'expiry_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $documentMaster = DocumentMaster::query()
            ->active()
            ->findOrFail($validated['document_master_id']);

        $existingDocument = $worker->documents()
            ->where('document_master_id', $documentMaster->id)
            ->first();

        if ($existingDocument?->file_path) {
            Storage::disk('public')->delete($existingDocument->file_path);
        }

        $filePath = $request->file('file')->store("workers/documents/{$worker->id}", 'public');

        $worker->documents()->updateOrCreate(
            ['document_master_id' => $documentMaster->id],
            [
                'file_path' => $filePath,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'note' => $validated['note'] ?? null,
            ]
        );

        return back()->with('success', 'อัปโหลดเอกสารเพิ่มเติมเรียบร้อยแล้ว');
    }

    public function workerDocumentDestroy(Request $request, Worker $worker, WorkerDocument $workerDocument)
    {
        $this->authorizeStaff($request);

        abort_unless($workerDocument->worker_id === $worker->id, 404);

        if ($workerDocument->file_path) {
            Storage::disk('public')->delete($workerDocument->file_path);
        }

        $workerDocument->delete();

        return back()->with('success', 'ลบเอกสารเพิ่มเติมเรียบร้อยแล้ว');
    }

    public function deliverySheets(Request $request)
    {
        $this->authorizeStaff($request);

        $keyword = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $deliverySheets = DeliverySheet::query()
            ->with(['employer', 'createdBy'])
            ->withCount(['items', 'attachments'])
            ->when($keyword !== '', function ($query) use ($keyword): void {
                $query->where(function ($subQuery) use ($keyword): void {
                    $subQuery
                        ->where('sheet_number', 'like', "%{$keyword}%")
                        ->orWhereHas('employer', fn($employerQuery) => $employerQuery->where('company_name', 'like', "%{$keyword}%"));
                });
            })
            ->when($status !== '', fn($query) => $query->where('status', $status))
            ->orderByDesc('sheet_date')
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => DeliverySheet::count(),
            'draft' => DeliverySheet::query()->where('status', 'draft')->count(),
            'submitted' => DeliverySheet::query()->where('status', 'submitted')->count(),
            'approved' => DeliverySheet::query()->where('status', 'approved')->count(),
        ];

        return view('staff-portal.delivery-sheets.index', compact(
            'deliverySheets',
            'keyword',
            'status',
            'summary',
        ));
    }

    public function deliverySheetCreate(Request $request)
    {
        $this->authorizeStaff($request);

        $employers = Employer::query()->orderBy('company_name')->get();
        $selectedEmployerId = (int) $request->query('employer_id', 0);

        $availableJobOrders = collect();
        $selectedEmployer = null;

        if ($selectedEmployerId > 0) {
            $selectedEmployer = Employer::query()->findOrFail($selectedEmployerId);
            $availableJobOrders = JobOrder::query()
                ->with(['worker', 'service', 'statusDefinition'])
                ->where('employer_id', $selectedEmployerId)
                ->whereNotIn('status', ['cancelled', 'rejected'])
                ->whereDoesntHave('deliverySheetItems')
                ->orderByRaw('case when due_date is null then 1 else 0 end')
                ->orderBy('due_date', 'asc')
                ->orderByDesc('updated_at')
                ->get();
        }

        return view('staff-portal.delivery-sheets.create', compact(
            'employers',
            'selectedEmployer',
            'availableJobOrders',
        ));
    }

    public function deliverySheetStore(Request $request)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'employer_id' => ['required', 'exists:employers,id'],
            'sheet_date' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,submitted'],
            'note' => ['nullable', 'string', 'max:2000'],
            'job_order_ids' => ['required', 'array', 'min:1'],
            'job_order_ids.*' => ['integer', 'distinct', 'exists:job_orders,id'],
        ]);

        $employer = Employer::query()->findOrFail($validated['employer_id']);

        $jobOrders = JobOrder::query()
            ->with(['worker', 'service'])
            ->where('employer_id', $employer->id)
            ->whereIn('id', $validated['job_order_ids'])
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->get()
            ->keyBy('id');

        if ($jobOrders->count() !== count($validated['job_order_ids'])) {
            throw ValidationException::withMessages([
                'job_order_ids' => 'มีใบงานบางรายการไม่ถูกต้อง หรือไม่ได้อยู่ภายใต้นายจ้างที่เลือก',
            ]);
        }

        $alreadyLinked = DeliverySheetItem::query()
            ->whereIn('job_order_id', $validated['job_order_ids'])
            ->with('jobOrder')
            ->get();

        if ($alreadyLinked->isNotEmpty()) {
            $jobNumbers = $alreadyLinked->map(fn($item) => $item->jobOrder?->job_number)->filter()->values()->all();

            throw ValidationException::withMessages([
                'job_order_ids' => 'ใบงานบางรายการถูกใช้ในใบส่งงานอื่นแล้ว: ' . implode(', ', $jobNumbers),
            ]);
        }

        $deliverySheet = DB::transaction(function () use ($validated, $employer, $request) {
            $deliverySheet = DeliverySheet::create([
                'employer_id' => $employer->id,
                'created_by_user_id' => $request->user()?->id,
                'sheet_date' => $validated['sheet_date'] ?? null,
                'status' => $validated['status'],
                'note' => $validated['note'] ?? null,
            ]);

            foreach ($validated['job_order_ids'] as $jobOrderId) {
                DeliverySheetItem::create([
                    'delivery_sheet_id' => $deliverySheet->id,
                    'job_order_id' => $jobOrderId,
                ]);
            }

            return $deliverySheet;
        });

        return redirect()
            ->route('staff.portal.delivery-sheets.show', $deliverySheet)
            ->with('success', 'สร้างใบส่งงานเรียบร้อยแล้ว');
    }

    public function deliverySheetShow(Request $request, DeliverySheet $deliverySheet)
    {
        $this->authorizeStaff($request);

        $deliverySheet->load([
            'employer',
            'createdBy',
            'items.jobOrder.worker',
            'items.jobOrder.service',
            'items.jobOrder.statusDefinition',
            'attachments.uploadedBy',
        ]);

        $jobOrderCount = $deliverySheet->items->count();
        $attachmentCount = $deliverySheet->attachments->count();

        $statusOptions = [
            'draft' => 'ร่าง',
            'submitted' => 'ส่งแล้ว',
            'approved' => 'อนุมัติ',
            'rejected' => 'ไม่อนุมัติ',
            'completed' => 'เสร็จสิ้น',
        ];

        return view('staff-portal.delivery-sheets.show', compact(
            'deliverySheet',
            'jobOrderCount',
            'attachmentCount',
            'statusOptions',
        ));
    }

    public function deliverySheetUpdate(Request $request, DeliverySheet $deliverySheet)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'status' => ['required', 'in:draft,submitted,approved,rejected,completed'],
            'sheet_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $deliverySheet->update($validated);

        return back()->with('success', 'อัปเดตใบส่งงานเรียบร้อยแล้ว');
    }

    public function deliverySheetDestroy(Request $request, DeliverySheet $deliverySheet)
    {
        $this->authorizeStaff($request);

        foreach ($deliverySheet->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $deliverySheet->delete();

        return redirect()
            ->route('staff.portal.delivery-sheets.index')
            ->with('success', 'ลบใบส่งงานเรียบร้อยแล้ว');
    }

    public function deliverySheetAttachmentStore(Request $request, DeliverySheet $deliverySheet)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'file' => UploadLimits::fileRules(true, ['pdf', 'jpg', 'jpeg', 'png', 'webp']),
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $uploadedFile = $request->file('file');
        $filePath = $uploadedFile->store("delivery-sheets/{$deliverySheet->id}", 'public');

        $deliverySheet->attachments()->create([
            'uploaded_by_user_id' => $request->user()?->id,
            'file_name' => $uploadedFile->getClientOriginalName(),
            'file_path' => $filePath,
            'mime_type' => $uploadedFile->getMimeType(),
            'file_size' => $uploadedFile->getSize(),
            'note' => $validated['note'] ?? null,
        ]);

        return back()->with('success', 'อัปโหลดหลักฐานเรียบร้อยแล้ว');
    }

    public function deliverySheetAttachmentDestroy(Request $request, DeliverySheet $deliverySheet, DeliverySheetAttachment $attachment)
    {
        $this->authorizeStaff($request);

        abort_unless($attachment->delivery_sheet_id === $deliverySheet->id, 404);

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'ลบหลักฐานเรียบร้อยแล้ว');
    }

    public function deliverySheetItemDestroy(Request $request, DeliverySheet $deliverySheet, DeliverySheetItem $item)
    {
        $this->authorizeStaff($request);

        abort_unless($item->delivery_sheet_id === $deliverySheet->id, 404);

        $item->delete();

        return back()->with('success', 'ลบใบงานออกจากใบส่งงานเรียบร้อยแล้ว');
    }

    public function jobOrders(Request $request)
    {
        $this->authorizeStaff($request);

        $keyword = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        $priority = (string) $request->query('priority', '');
        $paymentStatus = (string) $request->query('payment_status', '');

        $jobOrders = JobOrder::query()
            ->with(['employer', 'worker', 'service', 'assignedUser', 'statusDefinition'])
            ->withCount([
                'checklists as pending_documents_count' => fn($query) => $query->whereIn('status', ['pending', 'missing', 'rejected']),
                'payments as pending_payments_count' => fn($query) => $query->where('status', 'pending'),
            ])
            ->when($keyword !== '', function ($query) use ($keyword): void {
                $query->where(function ($subQuery) use ($keyword): void {
                    $subQuery
                        ->where('job_number', 'like', "%{$keyword}%")
                        ->orWhereHas('employer', fn($employerQuery) => $employerQuery->where('company_name', 'like', "%{$keyword}%"))
                        ->orWhereHas('worker', function ($workerQuery) use ($keyword): void {
                            $workerQuery
                                ->where('first_name_th', 'like', "%{$keyword}%")
                                ->orWhere('last_name_th', 'like', "%{$keyword}%")
                                ->orWhere('first_name_en', 'like', "%{$keyword}%")
                                ->orWhere('last_name_en', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($status !== '', fn($query) => $query->where('status', $status))
            ->when($priority !== '', fn($query) => $query->where('priority', $priority))
            ->when($paymentStatus !== '', fn($query) => $query->where('payment_status', $paymentStatus))
            ->orderByRaw('case when due_date is null then 1 else 0 end')
            ->orderBy('due_date', 'asc')
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

        $jobOrderStatuses = JobOrderStatus::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name_th')
            ->get();

        return view('staff-portal.job-orders.index', compact(
            'jobOrders',
            'keyword',
            'status',
            'priority',
            'paymentStatus',
            'summary',
            'jobOrderStatuses',
        ));
    }

    public function jobOrderShow(Request $request, JobOrder $jobOrder)
    {
        $this->authorizeStaff($request);

        $jobOrder->load([
            'employer',
            'worker.nationality',
            'statusDefinition',
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

        $jobOrderStatuses = JobOrderStatus::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name_th')
            ->get();

        return view('staff-portal.job-orders.show', compact('jobOrder', 'summary', 'jobOrderStatuses'));
    }

    public function updateJobStatus(Request $request, JobOrder $jobOrder)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'status' => ['required', 'exists:job_order_statuses,code'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldStatus = $jobOrder->status;
        $newStatus = $validated['status'];
        $newStatusRecord = JobOrderStatus::query()->where('code', $newStatus)->first();

        if ($newStatusRecord?->requires_note && blank($validated['note'] ?? null)) {
            throw ValidationException::withMessages([
                'note' => 'กรุณาระบุหมายเหตุสำหรับสถานะนี้',
            ]);
        }

        if ($oldStatus === $newStatus) {
            return back();
        }

        $jobOrder->update([
            'status' => $newStatus,
            'completed_at' => $newStatusRecord?->sets_completed_at
                ? now()
                : $jobOrder->completed_at,
        ]);

        $newStatusLabel = $newStatusRecord?->name_th ?? $newStatus;
        $description = 'เปลี่ยนสถานะเป็น ' . $newStatusLabel;
        if (!empty($validated['note'])) {
            $description .= ' (เหตุผล: ' . $validated['note'] . ')';
        }

        \App\Models\JobOrderLog::create([
            'job_order_id' => $jobOrder->id,
            'user_id' => $request->user()->id,
            'action' => 'เปลี่ยนสถานะใบงาน',
            'description' => $description,
        ]);

        return back()->with('success', 'เปลี่ยนสถานะใบงานเรียบร้อยแล้ว');
    }

    public function updateServiceFee(Request $request, JobOrder $jobOrder)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'service_fee' => ['required', 'numeric', 'min:0', 'max:999999'],
        ]);

        $oldFee = (float) $jobOrder->service_fee;
        $newFee = (float) $validated['service_fee'];

        if ($oldFee === $newFee) {
            return back();
        }

        $jobOrder->update([
            'service_fee' => $newFee,
        ]);

        $jobOrder->syncPaymentSummary();

        \App\Models\JobOrderLog::create([
            'job_order_id' => $jobOrder->id,
            'user_id' => $request->user()->id,
            'action' => 'แก้ไขค่าบริการ',
            'description' => 'เปลี่ยนค่าบริการจาก ' . number_format($oldFee, 2) . ' เป็น ' . number_format($newFee, 2) . ' บาท',
        ]);

        return back()->with('success', 'แก้ไขค่าบริการเรียบร้อยแล้ว');
    }

    public function jobOrderDestroy(Request $request, JobOrder $jobOrder)
    {
        $this->authorizeStaff($request);

        $jobNumber = $jobOrder->job_number;

        DB::transaction(function () use ($jobOrder): void {
            $jobOrder->load(['checklists', 'payments']);

            foreach ($jobOrder->checklists as $checklist) {
                if ($checklist->attached_file_path) {
                    Storage::disk('public')->delete($checklist->attached_file_path);
                }

                $checklist->delete();
            }

            foreach ($jobOrder->payments as $payment) {
                if ($payment->slip_path) {
                    Storage::disk('public')->delete($payment->slip_path);
                }

                $payment->delete();
            }

            $jobOrder->logs()->delete();
            $jobOrder->forceDelete();
        });

        return redirect()
            ->route('staff.portal.job-orders.index')
            ->with('success', "ลบใบงาน {$jobNumber} เรียบร้อยแล้ว");
    }

    public function jobOrderChecklistDestroy(Request $request, JobOrder $jobOrder, JobOrderChecklist $checklist)
    {
        $this->authorizeStaff($request);
        abort_unless($checklist->job_order_id === $jobOrder->id, 404);

        if ($checklist->attached_file_path) {
            Storage::disk('public')->delete($checklist->attached_file_path);
        }

        $documentName = $checklist->documentMaster?->name ?: 'เอกสาร';
        $checklist->delete();

        \App\Models\JobOrderLog::create([
            'job_order_id' => $jobOrder->id,
            'user_id' => $request->user()?->id,
            'action' => 'ลบเอกสารออกจากใบงาน',
            'description' => 'ลบเอกสาร ' . $documentName . ' ออกจากใบงาน',
        ]);

        return back()->with('success', 'ลบเอกสารออกจากใบงานเรียบร้อยแล้ว');
    }

    public function jobOrderChecklistStore(Request $request, JobOrder $jobOrder, JobOrderChecklist $checklist)
    {
        $this->authorizeStaff($request);
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

        \App\Models\JobOrderLog::create([
            'job_order_id' => $jobOrder->id,
            'user_id' => $request->user()?->id,
            'action' => 'staff อัปโหลดเอกสาร',
            'description' => 'อัปโหลดเอกสาร ' . ($checklist->documentMaster?->name ?: 'ประกอบงาน') . ' เพื่อรอตรวจสอบ',
        ]);

        return back()->with('success', 'อัปโหลดเอกสารเรียบร้อยแล้ว');
    }

    public function documentReviews(Request $request)
    {
        $this->authorizeStaff($request);

        $keyword = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $reviews = JobOrderChecklist::query()
            ->with(['jobOrder.employer', 'jobOrder.worker', 'documentMaster'])
            ->whereIn('status', ['pending', 'received', 'missing', 'rejected'])
            ->when($keyword !== '', function ($query) use ($keyword): void {
                $query->where(function ($subQuery) use ($keyword): void {
                    $subQuery
                        ->whereHas('jobOrder', fn($jobQuery) => $jobQuery->where('job_number', 'like', "%{$keyword}%"))
                        ->orWhereHas('jobOrder.employer', fn($employerQuery) => $employerQuery->where('company_name', 'like', "%{$keyword}%"))
                        ->orWhereHas('jobOrder.worker', function ($workerQuery) use ($keyword): void {
                            $workerQuery
                                ->where('first_name_th', 'like', "%{$keyword}%")
                                ->orWhere('last_name_th', 'like', "%{$keyword}%")
                                ->orWhere('first_name_en', 'like', "%{$keyword}%")
                                ->orWhere('last_name_en', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('documentMaster', fn($documentQuery) => $documentQuery->where('name', 'like', "%{$keyword}%"));
                });
            })
            ->when($status !== '', fn($query) => $query->where('status', $status))
            ->orderByRaw("case status when 'received' then 0 when 'rejected' then 1 when 'missing' then 2 else 3 end")
            ->latest('received_at')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'pending' => JobOrderChecklist::query()->where('status', 'pending')->count(),
            'received' => JobOrderChecklist::query()->where('status', 'received')->count(),
            'missing' => JobOrderChecklist::query()->where('status', 'missing')->count(),
            'rejected' => JobOrderChecklist::query()->where('status', 'rejected')->count(),
        ];

        return view('staff-portal.document-reviews.index', compact(
            'reviews',
            'keyword',
            'status',
            'summary',
        ));
    }

    public function paymentReviews(Request $request)
    {
        $this->authorizeStaff($request);

        $keyword = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $payments = JobOrderPayment::query()
            ->with(['jobOrder.employer', 'jobOrder.worker', 'receiver'])
            ->when($keyword !== '', function ($query) use ($keyword): void {
                $query->where(function ($subQuery) use ($keyword): void {
                    $subQuery
                        ->whereHas('jobOrder', fn($jobQuery) => $jobQuery->where('job_number', 'like', "%{$keyword}%"))
                        ->orWhereHas('jobOrder.employer', fn($employerQuery) => $employerQuery->where('company_name', 'like', "%{$keyword}%"))
                        ->orWhereHas('jobOrder.worker', function ($workerQuery) use ($keyword): void {
                            $workerQuery
                                ->where('first_name_th', 'like', "%{$keyword}%")
                                ->orWhere('last_name_th', 'like', "%{$keyword}%")
                                ->orWhere('first_name_en', 'like', "%{$keyword}%")
                                ->orWhere('last_name_en', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($status !== '', fn($query) => $query->where('status', $status))
            ->orderByRaw("case status when 'pending' then 0 when 'rejected' then 1 else 2 end")
            ->latest('payment_date')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'pending' => JobOrderPayment::query()->where('status', 'pending')->count(),
            'verified' => JobOrderPayment::query()->where('status', 'verified')->count(),
            'rejected' => JobOrderPayment::query()->where('status', 'rejected')->count(),
            'total_pending_amount' => JobOrderPayment::query()->where('status', 'pending')->sum('amount'),
        ];

        return view('staff-portal.payment-reviews.index', compact(
            'payments',
            'keyword',
            'status',
            'summary',
        ));
    }

    public function verifyDocument(Request $request, JobOrderChecklist $checklist)
    {
        $this->authorizeStaff($request);

        $checklist->load(['jobOrder.checklists', 'documentMaster']);

        $checklist->update([
            'status' => 'verified',
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        $jobOrder = $checklist->jobOrder()->with('checklists')->first();

        if ($jobOrder && $jobOrder->checklists->every(fn(JobOrderChecklist $item): bool => $item->status === 'verified')) {
            $jobOrder->update(['status' => 'approved']);
        }

        \App\Models\JobOrderLog::create([
            'job_order_id' => $checklist->job_order_id,
            'user_id' => $request->user()->id,
            'action' => 'ตรวจเอกสารผ่าน',
            'description' => ($checklist->documentMaster?->name ?: 'เอกสารประกอบงาน') . ' ตรวจสอบผ่านแล้ว',
        ]);

        return back()->with('success', 'ตรวจเอกสารผ่านแล้ว');
    }

    public function rejectDocument(Request $request, JobOrderChecklist $checklist)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'remark' => ['required', 'string', 'max:1000'],
        ]);

        $checklist->load(['jobOrder', 'documentMaster']);

        $checklist->update([
            'status' => 'rejected',
            'remark' => $validated['remark'],
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        $checklist->jobOrder?->update(['status' => 'waiting_document']);

        \App\Models\JobOrderLog::create([
            'job_order_id' => $checklist->job_order_id,
            'user_id' => $request->user()->id,
            'action' => 'เอกสารต้องแก้ไข',
            'description' => ($checklist->documentMaster?->name ?: 'เอกสารประกอบงาน') . ': ' . $validated['remark'],
        ]);

        return back()->with('success', 'ส่งเอกสารกลับไปแก้ไขแล้ว');
    }

    public function verifyPayment(Request $request, JobOrderPayment $payment)
    {
        $this->authorizeStaff($request);

        $payment->load('jobOrder');

        $payment->update([
            'status' => 'verified',
            'received_by' => $request->user()->id,
        ]);

        $payment->jobOrder?->syncPaymentSummary();

        if ($payment->jobOrder) {
            \App\Models\JobOrderLog::create([
                'job_order_id' => $payment->jobOrder->id,
                'user_id' => $request->user()->id,
                'action' => 'ตรวจสลิปผ่าน',
                'description' => 'ตรวจสอบยอดชำระ ' . number_format((float) $payment->amount, 2) . ' บาท ผ่านแล้ว',
            ]);
        }

        return back()->with('success', 'ยืนยันสลิปเรียบร้อยแล้ว');
    }

    public function rejectPayment(Request $request, JobOrderPayment $payment)
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:1000'],
        ]);

        $payment->load('jobOrder');

        $payment->update([
            'status' => 'rejected',
            'received_by' => $request->user()->id,
            'note' => $validated['note'],
        ]);

        $payment->jobOrder?->syncPaymentSummary();

        if ($payment->jobOrder) {
            \App\Models\JobOrderLog::create([
                'job_order_id' => $payment->jobOrder->id,
                'user_id' => $request->user()->id,
                'action' => 'สลิปไม่ผ่าน',
                'description' => $validated['note'],
            ]);
        }

        return back()->with('success', 'ปฏิเสธสลิปและบันทึกหมายเหตุแล้ว');
    }

    private function authorizeStaff(Request $request): void
    {
        abort_unless(
            $request->user()?->hasAnyRole(['staff', 'accounting', 'super_admin', 'admin', 'manager']),
            403
        );
    }

    private function authorizeUserManagement(Request $request): void
    {
        abort_unless(
            $request->user()?->hasAnyRole(['super_admin', 'admin', 'manager', 'staff', 'accounting']),
            403
        );
    }

    private function getManageableRoles(): array
    {
        return ['staff', 'employer'];
    }

    private function getRoleMap(): array
    {
        return [
            'staff' => 'Staff',
            'employer' => 'Employer',
        ];
    }

    private function syncEmployerForUser(User $user, string $role, ?int $employerId): void
    {
        if ($role !== 'employer') {
            $user->employers()->detach();

            return;
        }

        if (! $employerId) {
            $user->employers()->detach();

            return;
        }

        $user->employers()->sync([
            $employerId => ['role' => 'owner'],
        ]);
    }

    private function getExpiringItems(): Collection
    {
        $today = now()->startOfDay();
        $limit = $today->copy()->addDays(45)->endOfDay();

        $workerDates = Worker::query()
            ->with('employer')
            ->active()
            ->where(function ($query) use ($limit): void {
                $query
                    ->whereDate('passport_expiry', '<=', $limit)
                    ->orWhereDate('wp_expiry', '<=', $limit)
                    ->orWhereDate('visa_expiry', '<=', $limit)
                    ->orWhereDate('report_90_days_due', '<=', $limit);
            })
            ->limit(100)
            ->get()
            ->flatMap(function (Worker $worker): array {
                return collect([
                    ['document' => 'Passport', 'date' => $worker->passport_expiry],
                    ['document' => 'Work Permit', 'date' => $worker->wp_expiry],
                    ['document' => 'Visa', 'date' => $worker->visa_expiry],
                    ['document' => '90 Days Report', 'date' => $worker->report_90_days_due],
                ])
                    ->filter(fn(array $item): bool => $item['date'] instanceof Carbon)
                    ->map(fn(array $item): array => [
                        'worker' => $worker->full_name_th ?: $worker->full_name_en,
                        'employer' => $worker->employer?->company_name ?? '-',
                        'document' => $item['document'],
                        'expiry_date' => $item['date'],
                    ])
                    ->all();
            });

        $documents = WorkerDocument::query()
            ->with(['worker.employer', 'documentMaster'])
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', $limit)
            ->limit(100)
            ->get()
            ->map(fn(WorkerDocument $document): array => [
                'worker' => $document->worker?->full_name_th ?: $document->worker?->full_name_en ?: '-',
                'employer' => $document->worker?->employer?->company_name ?? '-',
                'document' => $document->documentMaster?->name ?? 'เอกสาร',
                'expiry_date' => $document->expiry_date,
            ]);

        return $workerDates
            ->merge($documents)
            ->sortBy('expiry_date')
            ->take(12)
            ->values();
    }
}
