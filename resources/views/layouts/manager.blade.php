<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'พอร์ทัลผู้จัดการ' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=ไม่to+Sans+Thai:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #0b2f52;
            --navy-2: #0b1f3a;
            --navy-3: #123e68;
            --gold: #c9a227;
            --gold-2: #f3d06f;
            --paper: #f7f9fc;
        }

        body { font-family: 'Inter', 'ไม่to Sans Thai', ui-sans-serif, system-ui, sans-serif; }
        .portal-shell { background: var(--paper); }
        .portal-sidebar {
            background:
                linear-gradient(180deg, rgba(201, 162, 39, 0.12), rgba(201, 162, 39, 0) 32%),
                linear-gradient(180deg, var(--navy) 0%, #08213a 100%);
        }
        .nav-item-active { 
            background: rgba(201, 162, 39, 0.14);
            border-left: 3px solid var(--gold);
            color: #fff7d6;
        }
        .manager-topbar {
            background: rgba(255, 255, 255, 0.92);
            border-color: rgba(7, 20, 38, 0.08);
            box-shadow: 0 12px 30px rgba(7, 20, 38, 0.05);
        }
        .glass-card,
        .portal-card,
        .manager-card {
            background: #fff;
            border: 1px solid rgba(11, 47, 82, 0.08);
            border-radius: 8px;
            box-shadow: 0 12px 28px rgba(11, 47, 82, 0.06);
        }
        .hover-shadow,
        .manager-card-hover {
            transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
        }
        .hover-shadow:hover,
        .manager-card-hover:hover {
            transform: translateY(-2px);
            border-color: rgba(201, 162, 39, 0.35);
            box-shadow: 0 18px 36px rgba(11, 47, 82, 0.1);
        }
        .manager-page-title {
            color: var(--navy);
            font-weight: 800;
            letter-spacing: -0.015em;
        }
        .manager-muted {
            color: #64748b;
        }
        .manager-btn-primary {
            background: var(--navy);
            color: #fff;
            border-radius: 8px;
            box-shadow: 0 12px 22px rgba(11, 47, 82, 0.18);
        }
        .manager-btn-primary:hover {
            background: var(--navy-3);
        }
        .manager-btn-gold {
            background: var(--gold);
            color: var(--navy);
            border-radius: 8px;
            box-shadow: 0 12px 22px rgba(201, 162, 39, 0.22);
        }
        .manager-btn-gold:hover {
            background: var(--gold-2);
        }
        .manager-btn-secondary {
            background: #fff;
            color: var(--navy);
            border: 1px solid rgba(11, 47, 82, 0.12);
            border-radius: 8px;
        }
        .manager-btn-secondary:hover {
            border-color: rgba(201, 162, 39, 0.45);
            background: #fff9e8;
        }
        .manager-input,
        .manager-select,
        .manager-textarea {
            border: 1px solid rgba(11, 47, 82, 0.12);
            background: #f8fafc;
            border-radius: 8px;
            color: var(--navy);
        }
        .manager-input:focus,
        .manager-select:focus,
        .manager-textarea:focus {
            outline: none;
            border-color: rgba(201, 162, 39, 0.8);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(201, 162, 39, 0.12);
        }
        .manager-table-head {
            background: #f8fafc;
            color: #64748b;
        }
        .manager-row-hover:hover {
            background: #fff9e8;
        }
    </style>
    @stack('head')
</head>

<body class="portal-shell min-h-screen text-slate-900">
    <div class="min-h-screen lg:grid lg:grid-cols-[280px_1fr]">
        <!-- Mobile Overlay -->
        <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0 lg:hidden"></div>

        <aside id="portal-sidebar" class="portal-sidebar fixed inset-y-0 left-0 z-40 flex w-[280px] -translate-x-full flex-col text-white transition-transform duration-300 lg:static lg:translate-x-0 shadow-2xl lg:shadow-none">
            <div class="flex items-center gap-4 px-6 py-7">
                <div class="grid h-12 w-12 place-items-center rounded-lg bg-[#c9a227] text-xl font-extrabold text-[#0b2f52] shadow-lg shadow-black/20">M</div>
                <div class="min-w-0">
                    <p class="text-base font-extrabold tracking-tight text-white">พอร์ทัลผู้จัดการ</p>
                    <p class="truncate text-xs font-medium text-[#f3d06f]">พื้นที่ทำงานผู้บริหาร</p>
                </div>
            </div>

            <nav class="flex-1 space-y-1 px-3">
                <a href="{{ route('manager.dashboard') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('manager.dashboard'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('manager.dashboard'),
                ])>
                    <i data-lucide="layout-dashboard" class="h-5 w-5"></i>
                    แดชบอร์ดผู้บริหาร
                </a>
                
                <div class="pt-4 pb-2 px-4">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#c9a227]/80">Business Control</p>
                </div>

                <a href="{{ route('manager.reports.financial') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('manager.reports.financial'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('manager.reports.financial'),
                ])>
                    <i data-lucide="bar-chart-big" class="h-5 w-5"></i>
                    รายงานการเงิน
                </a>
                
                <a href="{{ route('manager.reports.pipeline') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('manager.reports.pipeline'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('manager.reports.pipeline'),
                ])>
                    <i data-lucide="activity" class="h-5 w-5"></i>
                    วิเคราะห์งาน
                </a>
                
                <a href="{{ route('manager.employers.index') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('manager.employers.*'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('manager.employers.*'),
                ])>
                    <i data-lucide="building-2" class="h-5 w-5"></i>
                    จัดการลูกค้า
                </a>

                <div class="pt-4 pb-2 px-4">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#c9a227]/80">Workforce & Compliance</p>
                </div>

                <a href="{{ route('manager.workers.index') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('manager.workers.*'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('manager.workers.*'),
                ])>
                    <i data-lucide="users-2" class="h-5 w-5"></i>
                    ทะเบียนแรงงาน
                </a>
                <a href="{{ route('manager.reports.expired_cards') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('manager.reports.expired_cards'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('manager.reports.expired_cards'),
                ])>
                    <i data-lucide="alert-triangle" class="h-5 w-5"></i>
                    ตรวจเอกสารหมดอายุ
                </a>

                <div class="pt-4 pb-2 px-4">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#c9a227]/80">System Preferences</p>
                </div>

                <a href="{{ route('staff.portal.settings') }}" @class([
                    'group flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 rounded-lg',
                    'nav-item-active' => request()->routeIs('staff.portal.settings'),
                    'text-slate-300 hover:text-white hover:bg-white/10' => ! request()->routeIs('staff.portal.settings'),
                ])>
                    <i data-lucide="settings" class="h-5 w-5"></i>
                    ตั้งค่าบัญชี
                </a>

                <div class="mt-8 pt-4 border-t border-white/5 px-4">
                    <a href="{{ route('staff.portal.dashboard') }}" class="flex items-center gap-3 text-xs text-slate-400 hover:text-[#f3d06f] transition-colors">
                        <i data-lucide="arrow-left-circle" class="h-4 w-4"></i>
                        ไปยังพอร์ทัลเจ้าหน้าที่
                    </a>
                </div>
            </nav>

            <div class="p-4 border-t border-white/5">
                <div class="flex items-center gap-3 px-2 py-3 rounded-lg bg-white/10 ring-1 ring-white/10">
                    <div class="h-9 w-9 rounded-lg bg-[#c9a227] flex items-center justify-center font-extrabold text-xs text-[#0b2f52]">
                        {{ mb_substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-slate-500 uppercase">Manager</p>
                    </div>
                </div>
            </div>
        </aside>

        <div class="min-w-0 flex flex-col">
            <header class="manager-topbar sticky top-0 z-20 border-b px-4 py-4 backdrop-blur-md lg:px-8">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <button id="mobile-menu-btn" class="lg:hidden grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-[#0b2f52] text-white hover:bg-[#123e68] transition-colors">
                            <i data-lucide="menu" class="h-5 w-5"></i>
                        </button>
                        <nav class="flex text-sm text-slate-500">
                            <span class="font-extrabold text-[#0b2f52] uppercase tracking-widest">การจัดการ</span>
                            <span class="mx-2 text-[#c9a227]">/</span>
                            <span class="font-semibold text-slate-600">{{ $title ?? 'Dashboard' }}</span>
                        </nav>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="flex items-center gap-2 rounded-lg bg-[#0b2f52] px-4 py-2 text-xs font-bold text-white hover:bg-[#123e68] transition-all shadow-sm ring-1 ring-[#c9a227]/20">
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

        const menuBtn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('portal-sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        function toggleSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
            setTimeout(() => overlay.classList.toggle('opacity-0'), 0);
        }

        menuBtn?.addEventListener('click', toggleSidebar);
        overlay?.addEventListener('click', toggleSidebar);
    </script>
</body>

</html>
