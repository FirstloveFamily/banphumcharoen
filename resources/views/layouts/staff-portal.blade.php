<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Staff Portal' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    @stack('head')
    <style>
        :root {
            --navy: #0b2f52;
            --navy-2: #071c33;
            --navy-3: #123e68;
            --crimson: #b91c1c;
            --crimson-2: #dc2626;
            --paper: #f4f7fb;
            --paper-2: #ffffff;
            --mist: #e8eef8;
        }

        body {
            font-family: 'Prompt', ui-sans-serif, system-ui, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(185, 28, 28, 0.10), transparent 28%),
                radial-gradient(circle at top right, rgba(11, 47, 82, 0.12), transparent 24%),
                linear-gradient(180deg, #ffffff 0%, var(--paper) 44%, #eef3fb 100%);
        }
        .portal-shell { background: transparent; }
        .portal-sidebar {
            background:
                radial-gradient(circle at top right, rgba(220, 38, 38, 0.18), transparent 28%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0) 22%),
                linear-gradient(180deg, var(--navy) 0%, var(--navy-2) 100%);
        }
        .nav-item-active {
            background: linear-gradient(90deg, rgba(220, 38, 38, 0.20), rgba(220, 38, 38, 0.08));
            border-left: 3px solid #fca5a5;
            color: #fff;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }
        .portal-topbar {
            background:
                linear-gradient(90deg, rgba(11, 47, 82, 0.04), rgba(185, 28, 28, 0.04)),
                rgba(255, 255, 255, 0.92);
            border-color: rgba(7, 20, 38, 0.08);
            box-shadow: 0 12px 30px rgba(7, 20, 38, 0.06);
        }
        .glass-card,
        .portal-card,
        .manager-card {
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid rgba(11, 47, 82, 0.08);
            border-radius: 18px;
            box-shadow: 0 14px 34px rgba(11, 47, 82, 0.07);
            backdrop-filter: blur(12px);
        }
        .hover-shadow,
        .manager-card-hover {
            transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
        }
        .hover-shadow:hover,
        .manager-card-hover:hover {
            transform: translateY(-2px);
            border-color: rgba(220, 38, 38, 0.22);
            box-shadow: 0 18px 36px rgba(11, 47, 82, 0.11);
        }
        .portal-page-title {
            color: var(--navy);
            font-weight: 800;
            letter-spacing: -0.015em;
        }
        .portal-muted {
            color: #64748b;
        }
        .portal-btn-primary {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-3) 55%, var(--crimson) 100%);
            color: #fff;
            border-radius: 12px;
            box-shadow: 0 12px 22px rgba(11, 47, 82, 0.18);
        }
        .portal-btn-primary:hover {
            filter: saturate(1.05);
            box-shadow: 0 16px 28px rgba(11, 47, 82, 0.24);
        }
        .portal-btn-secondary {
            background: #fff;
            color: var(--navy);
            border: 1px solid rgba(11, 47, 82, 0.12);
            border-radius: 12px;
        }
        .portal-btn-secondary:hover {
            border-color: rgba(220, 38, 38, 0.28);
            background: #fff7f7;
        }
        .portal-btn-danger {
            background: linear-gradient(135deg, var(--crimson) 0%, var(--crimson-2) 100%);
            color: #fff;
            border-radius: 12px;
            box-shadow: 0 12px 22px rgba(185, 28, 28, 0.20);
        }
        .portal-btn-danger:hover {
            box-shadow: 0 16px 28px rgba(185, 28, 28, 0.24);
        }
        .portal-input,
        .portal-select,
        .portal-textarea {
            border: 1px solid rgba(11, 47, 82, 0.12);
            background: #f8fafc;
            border-radius: 12px;
            color: var(--navy);
        }
        .portal-input:focus,
        .portal-select:focus,
        .portal-textarea:focus {
            outline: none;
            border-color: rgba(220, 38, 38, 0.55);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.10);
        }
        .portal-table-head {
            background: linear-gradient(90deg, #f8fafc 0%, #eef4fb 100%);
            color: #5f6b7a;
        }
        .portal-row-hover:hover {
            background: linear-gradient(90deg, rgba(11, 47, 82, 0.03), rgba(185, 28, 28, 0.03));
        }

        /* Theme override for common Tailwind blue utilities inside staff portal */
        .bg-blue-50 { background-color: #f5f9ff !important; }
        .bg-blue-100 { background-color: #e5efff !important; }
        .bg-blue-600 { background-color: var(--navy) !important; }
        .bg-blue-700 { background-color: var(--navy-3) !important; }
        .bg-red-50 { background-color: #fff1f1 !important; }
        .bg-red-100 { background-color: #ffe4e6 !important; }
        .bg-red-600 { background-color: var(--crimson) !important; }
        .bg-red-700 { background-color: var(--crimson-2) !important; }
        .text-blue-50 { color: #f5f9ff !important; }
        .text-blue-100 { color: #dbeafe !important; }
        .text-blue-600 { color: var(--navy) !important; }
        .text-blue-700 { color: var(--navy-3) !important; }
        .text-red-50 { color: #fff1f1 !important; }
        .text-red-100 { color: #fee2e2 !important; }
        .text-red-600 { color: var(--crimson) !important; }
        .text-red-700 { color: var(--crimson-2) !important; }
        .bg-amber-50 { background-color: #fff1f1 !important; }
        .bg-amber-100 { background-color: #ffe4e6 !important; }
        .bg-amber-500 { background-color: var(--crimson) !important; }
        .bg-amber-600 { background-color: var(--crimson) !important; }
        .bg-amber-700 { background-color: var(--crimson-2) !important; }
        .text-amber-50 { color: #fff1f1 !important; }
        .text-amber-100 { color: #ffe4e6 !important; }
        .text-amber-600 { color: var(--crimson) !important; }
        .text-amber-700 { color: var(--crimson-2) !important; }
        .border-blue-100 { border-color: rgba(11, 47, 82, 0.16) !important; }
        .border-blue-200 { border-color: rgba(11, 47, 82, 0.24) !important; }
        .border-red-100 { border-color: rgba(220, 38, 38, 0.16) !important; }
        .border-red-200 { border-color: rgba(220, 38, 38, 0.24) !important; }
        .border-amber-100 { border-color: rgba(220, 38, 38, 0.16) !important; }
        .border-amber-200 { border-color: rgba(220, 38, 38, 0.24) !important; }
        .ring-blue-100 { --tw-ring-color: rgba(11, 47, 82, 0.16) !important; }
        .ring-blue-200 { --tw-ring-color: rgba(11, 47, 82, 0.24) !important; }
        .ring-red-100 { --tw-ring-color: rgba(220, 38, 38, 0.16) !important; }
        .ring-red-200 { --tw-ring-color: rgba(220, 38, 38, 0.24) !important; }
        .ring-amber-100 { --tw-ring-color: rgba(220, 38, 38, 0.16) !important; }
        .ring-amber-500 { --tw-ring-color: rgba(220, 38, 38, 0.55) !important; }
        .hover\:bg-blue-600:hover { background-color: var(--navy) !important; }
        .hover\:bg-blue-700:hover { background-color: var(--navy-3) !important; }
        .hover\:text-blue-600:hover { color: var(--navy) !important; }
        .hover\:border-blue-100:hover { border-color: rgba(11, 47, 82, 0.16) !important; }
        .hover\:bg-red-600:hover { background-color: var(--crimson) !important; }
        .hover\:bg-red-700:hover { background-color: var(--crimson-2) !important; }
        .hover\:text-red-600:hover { color: var(--crimson) !important; }
        .hover\:border-red-100:hover { border-color: rgba(220, 38, 38, 0.16) !important; }
        .hover\:bg-amber-50:hover { background-color: #fff1f1 !important; }
        .hover\:bg-amber-100:hover { background-color: #ffe4e6 !important; }
        .hover\:bg-amber-600:hover { background-color: var(--crimson) !important; }
        .hover\:bg-amber-700:hover { background-color: var(--crimson-2) !important; }
        .hover\:text-amber-600:hover { color: var(--crimson) !important; }
        .hover\:text-amber-700:hover { color: var(--crimson-2) !important; }
        .hover\:border-amber-100:hover { border-color: rgba(220, 38, 38, 0.16) !important; }
        .focus\:border-blue-400:focus { border-color: rgba(11, 47, 82, 0.45) !important; }
        .focus\:border-red-400:focus { border-color: rgba(220, 38, 38, 0.45) !important; }
        .focus\:border-amber-400:focus { border-color: rgba(220, 38, 38, 0.45) !important; }
        .focus\:ring-blue-100:focus { --tw-ring-color: rgba(11, 47, 82, 0.16) !important; }
        .focus\:ring-red-100:focus { --tw-ring-color: rgba(220, 38, 38, 0.16) !important; }
        .focus\:ring-amber-100:focus { --tw-ring-color: rgba(220, 38, 38, 0.16) !important; }
    </style>
</head>

<body class="portal-shell min-h-screen text-slate-900">
    <div class="min-h-screen lg:grid lg:grid-cols-[280px_1fr]">
        <!-- Mobile Overlay -->
        <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-slate-950/45 backdrop-blur-sm transition-opacity opacity-0 lg:hidden"></div>

        <aside id="portal-sidebar" class="portal-sidebar fixed inset-y-0 left-0 z-40 flex w-[280px] -translate-x-full flex-col text-white transition-transform duration-300 lg:static lg:translate-x-0 shadow-2xl lg:shadow-none">
            <div class="flex items-center gap-4 px-6 py-8">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-white via-[#f5f9ff] to-[#ffebeb] text-xl font-extrabold text-[#0b2f52] shadow-lg shadow-black/20 ring-1 ring-white/20">SP</div>
                <div class="min-w-0">
                    <p class="text-base font-extrabold tracking-tight text-white">Staff Portal</p>
                    <p class="truncate text-xs font-medium text-[#fecaca]">Enterprise Management System</p>
                </div>
            </div>

            <nav class="flex-1 space-y-1 px-3">
                <a href="{{ route('staff.portal.dashboard') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('staff.portal.dashboard'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('staff.portal.dashboard'),
                ])>
                    <i data-lucide="layout-grid" class="h-5 w-5"></i>
                    Dashboard
                </a>
                
                <div class="pt-4 pb-2 px-4">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Planning & Tracking</p>
                </div>

                <a href="{{ route('staff.portal.calendar') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('staff.portal.calendar'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('staff.portal.calendar'),
                ])>
                    <i data-lucide="calendar" class="h-5 w-5"></i>
                    ปฏิทินงาน (Calendar)
                </a>
                <a href="{{ route('staff.portal.tasks.index') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('staff.portal.tasks.*'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('staff.portal.tasks.*'),
                ])>
                    <i data-lucide="layout-list" class="h-5 w-5"></i>
                    กระดานงาน (Task Board)
                </a>

                <div class="pt-4 pb-2 px-4">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Reports & Analytics</p>
                </div>

                <a href="{{ route('staff.portal.reports.index') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('staff.portal.reports.*'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('staff.portal.reports.*'),
                ])>
                    <i data-lucide="bar-chart-3" class="h-5 w-5"></i>
                    ศูนย์รวมรายงาน
                </a>

                <div class="pt-4 pb-2 px-4">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Core Management</p>
                </div>

                <a href="{{ route('staff.portal.employers.index') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('staff.portal.employers.*'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('staff.portal.employers.*'),
                ])>
                    <i data-lucide="building" class="h-5 w-5"></i>
                    รายชื่อนายจ้าง
                </a>
                <a href="{{ route('staff.portal.workers.index') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('staff.portal.workers.*'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('staff.portal.workers.*'),
                ])>
                    <i data-lucide="users-2" class="h-5 w-5"></i>
                    รายชื่อแรงงาน
                </a>
                <a href="{{ route('staff.portal.worker-registration-requests.index') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('staff.portal.worker-registration-requests.*'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('staff.portal.worker-registration-requests.*'),
                ])>
                    <i data-lucide="user-plus" class="h-5 w-5"></i>
                    คำขอเพิ่มแรงงาน
                </a>
                <a href="{{ route('staff.portal.worker-prefixes.index') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('staff.portal.worker-prefixes.*'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('staff.portal.worker-prefixes.*'),
                ])>
                    <i data-lucide="badge-info" class="h-5 w-5"></i>
                    คำนำหน้าชื่อ
                </a>
                <a href="{{ route('staff.portal.job-orders.index') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('staff.portal.job-orders.*'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('staff.portal.job-orders.*'),
                ])>
                    <i data-lucide="clipboard-list" class="h-5 w-5"></i>
                    ใบงาน (Job Orders)
                </a>
                <a href="{{ route('staff.portal.job-order-statuses.index') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('staff.portal.job-order-statuses.*'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('staff.portal.job-order-statuses.*'),
                ])>
                    <i data-lucide="badge-check" class="h-5 w-5"></i>
                    สถานะใบงาน
                </a>
                <a href="{{ route('staff.portal.delivery-sheets.index') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('staff.portal.delivery-sheets.*'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('staff.portal.delivery-sheets.*'),
                ])>
                    <i data-lucide="package-check" class="h-5 w-5"></i>
                    ใบส่งงาน
                </a>
                <a href="{{ route('staff.portal.services.index') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('staff.portal.services.*'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('staff.portal.services.*'),
                ])>
                    <i data-lucide="briefcase-business" class="h-5 w-5"></i>
                    บริการ
                </a>
                <a href="{{ route('staff.portal.document-masters.index') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('staff.portal.document-masters.*'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('staff.portal.document-masters.*'),
                ])>
                    <i data-lucide="file-text" class="h-5 w-5"></i>
                    ประเภทเอกสาร
                </a>

                <div class="pt-4 pb-2 px-4">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Website Content</p>
                </div>

                <a href="{{ route('staff.portal.news.index') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('staff.portal.news.*'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('staff.portal.news.*'),
                ])>
                    <i data-lucide="newspaper" class="h-5 w-5"></i>
                    ข่าวสารกิจกรรม
                </a>
                <a href="{{ route('staff.portal.about-us.index') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('staff.portal.about-us.*'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('staff.portal.about-us.*'),
                ])>
                    <i data-lucide="layout-grid" class="h-5 w-5"></i>
                    About us
                </a>

                <div class="pt-4 pb-2 px-4">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Approvals</p>
                </div>

                <a href="{{ route('staff.portal.document-reviews.index') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('staff.portal.document-reviews.*'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('staff.portal.document-reviews.*'),
                ])>
                    <i data-lucide="file-check" class="h-5 w-5"></i>
                    ตรวจเอกสาร
                </a>
                <a href="{{ route('staff.portal.payment-reviews.index') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('staff.portal.payment-reviews.*') || request()->routeIs('staff.portal.payments.*'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! (request()->routeIs('staff.portal.payment-reviews.*') || request()->routeIs('staff.portal.payments.*')),
                ])>
                    <i data-lucide="banknote" class="h-5 w-5"></i>
                    ตรวจสลิปโอนเงิน
                </a>

                <div class="pt-4 pb-2 px-4">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Preferences</p>
                </div>

                <a href="{{ route('staff.portal.settings') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('staff.portal.settings'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('staff.portal.settings'),
                ])>
                    <i data-lucide="settings" class="h-5 w-5"></i>
                    ตั้งค่าการใช้งาน
                </a>
                <a href="{{ route('staff.portal.document-statuses.index') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('staff.portal.document-statuses.*'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('staff.portal.document-statuses.*'),
                ])>
                    <i data-lucide="tags" class="h-5 w-5"></i>
                    สถานะเอกสาร
                </a>
                @if(auth()->user()?->hasAnyRole(['super_admin', 'admin', 'manager', 'staff', 'accounting']))
                    <a href="{{ route('staff.portal.users.index') }}" @class([
                        'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                        'nav-item-active' => request()->routeIs('staff.portal.users.*'),
                        'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('staff.portal.users.*'),
                    ])>
                        <i data-lucide="users-round" class="h-5 w-5"></i>
                        ผู้ใช้งานระบบ
                    </a>
                @endif
            </nav>

            <div class="p-4 border-t border-white/5">
                <div class="flex items-center gap-3 px-2 py-3 rounded-xl bg-white/10 ring-1 ring-white/10">
                    <div class="h-9 w-9 rounded-lg bg-gradient-to-br from-[#ffffff] to-[#fee2e2] flex items-center justify-center font-extrabold text-xs text-[#0b2f52]">
                        {{ mb_substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-slate-400 uppercase">{{ auth()->user()->role ?? 'Staff' }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <div class="min-w-0 flex flex-col">
            <header class="portal-topbar sticky top-0 z-20 border-b px-4 py-4 backdrop-blur-md lg:px-8">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                    <button id="mobile-menu-btn" class="lg:hidden grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-[#0b2f52] text-white hover:bg-[#123e68] transition-colors">
                        <i data-lucide="menu" class="h-5 w-5"></i>
                    </button>
                    <div class="hidden sm:block">
                        <nav class="flex text-sm text-slate-500">
                            <span class="font-extrabold text-[#0b2f52] uppercase tracking-widest">การจัดการ</span>
                            <span class="mx-2 text-[#b91c1c]">/</span>
                            <span class="font-semibold text-slate-600">{{ $pageTitle ?? 'Dashboard' }}</span>
                        </nav>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="flex items-center gap-2 rounded-lg portal-btn-danger px-4 py-2 text-xs font-bold transition-all shadow-sm">
                            <span>ออกจากระบบ</span>
                            <i data-lucide="log-out" class="h-3.5 w-3.5"></i>
                        </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 px-4 py-8 lg:px-10 max-w-[1600px] mx-auto w-full">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        lucide.createIcons();
        
        // Mobile sidebar toggle
        const sidebar = document.getElementById('portal-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const menuBtn = document.getElementById('mobile-menu-btn');
        const closeBtn = document.getElementById('close-sidebar-btn');
        let isSidebarOpen = false;

        function toggleSidebar() {
            isSidebarOpen = !isSidebarOpen;
            if (isSidebarOpen) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.remove('opacity-0'), 10);
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('opacity-0');
                setTimeout(() => overlay.classList.add('hidden'), 300);
            }
        }

        menuBtn?.addEventListener('click', toggleSidebar);
        closeBtn?.addEventListener('click', toggleSidebar);
        overlay?.addEventListener('click', toggleSidebar);
    </script>
    @stack('scripts')
</body>

</html>
