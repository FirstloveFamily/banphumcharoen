@php
    $title = 'หน้าหลัก';

    $sampleCases = [
        [
            'job_number' => 'AP260608001',
            'worker_name' => 'MR. AUNG KYAW',
            'service_name' => 'ต่ออายุใบอนุญาตทำงาน',
            'status_label' => 'กำลังดำเนินการ',
            'status_badge_class' => 'bg-orange-50 text-orange-600',
            'updated_at' => '08/06/2026',
        ],
        [
            'job_number' => 'AP260608002',
            'worker_name' => 'นางสาว มะ ซอ',
            'service_name' => 'เปลี่ยนนายจ้าง',
            'status_label' => 'รอตรวจเอกสาร',
            'status_badge_class' => 'bg-blue-50 text-blue-600',
            'updated_at' => '07/06/2026',
        ],
        [
            'job_number' => 'AP260608003',
            'worker_name' => 'MR. THAN WIN',
            'service_name' => 'แจ้งออก',
            'status_label' => 'เสร็จสิ้น',
            'status_badge_class' => 'bg-emerald-50 text-emerald-600',
            'updated_at' => '07/06/2026',
        ],
        [
            'job_number' => 'AP260608004',
            'worker_name' => 'นาย มะลิ ทอง',
            'service_name' => 'ตรวจสอบสถานะงาน',
            'status_label' => 'กำลังดำเนินการ',
            'status_badge_class' => 'bg-orange-50 text-orange-600',
            'updated_at' => '06/06/2026',
        ],
        [
            'job_number' => 'AP260608005',
            'worker_name' => 'MS. HLA WIN',
            'service_name' => 'ต่ออายุวีซ่า',
            'status_label' => 'รอชำระเงิน',
            'status_badge_class' => 'bg-rose-50 text-rose-600',
            'updated_at' => '05/06/2026',
        ],
    ];
@endphp
@extends('layouts.app')

@push('head')
    <style>
        .hero-gradient {
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.18), transparent 28%),
                linear-gradient(135deg, #102a75 0%, #1e3a8a 24%, #2563eb 55%, #b91c1c 100%);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .soft-shadow {
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.12);
        }

        .lift-card {
            transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
        }

        .lift-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 24px 50px rgba(15, 23, 42, 0.12);
        }

        .action-card {
            transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
        }

        .action-card:hover {
            transform: translateY(-6px);
            border-color: rgba(37, 99, 235, 0.2);
            box-shadow: 0 24px 50px rgba(15, 23, 42, 0.12);
        }

        .action-card:hover .icon-bg {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            transform: scale(1.08);
        }

        .action-card:hover .icon-bg i {
            color: white;
        }

        .icon-bg {
            transition: all 0.35s ease;
        }

        .table-row {
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .table-row:hover {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.04) 0%, rgba(220, 38, 38, 0.04) 100%);
        }

        .notification-card {
            transition: transform 0.35s ease, border-color 0.35s ease, box-shadow 0.35s ease;
        }

        .notification-card:hover {
            transform: translateX(4px);
            border-color: rgba(37, 99, 235, 0.2);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
            opacity: 0;
        }

        .animate-delay-100 {
            animation-delay: 0.1s;
        }

        .animate-delay-200 {
            animation-delay: 0.2s;
        }

        .animate-delay-300 {
            animation-delay: 0.3s;
        }
    </style>
@endpush

@section('content')
    <!-- Hero Section -->
    <section class="hero-gradient relative overflow-hidden py-12 sm:py-16 lg:py-20">
        <div class="absolute inset-0 opacity-20 pointer-events-none">
            <div class="absolute -left-40 -top-40 h-[520px] w-[520px] rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute bottom-[-12rem] right-[-10rem] h-[420px] w-[420px] rounded-full bg-red-500/20 blur-3xl">
            </div>
            <div
                class="absolute left-1/2 top-1/2 h-[640px] w-[640px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-blue-300/10 blur-3xl">
            </div>
        </div>

        <div
            class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,.04)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.04)_1px,transparent_1px)] bg-[size:56px_56px] opacity-30">
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 gap-8 xl:grid-cols-12 xl:gap-10 items-stretch">
                <div class="xl:col-span-6 animate-fade-in-up">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white shadow-lg">
                        <i data-lucide="shield-check" class="h-4 w-4 text-emerald-300"></i>
                        บริการจัดการเอกสารแรงงานต่างด้าวครบวงจร
                    </div>

                    <h4 class="mt-6 text-3xl font-bold leading-tight text-white sm:text-4xl lg:text-5xl xl:text-6xl">
                        บริการจัดการเอกสารแรงงานต่างด้าว</h4>


                    <p class="mt-5 max-w-2xl text-base leading-8 text-white/90 sm:text-lg">
                        ถูกต้อง รวดเร็ว เชื่อถือได้ พร้อมช่วยให้คุณติดตามงาน ตรวจสอบสถานะ
                        และเข้าถึงบริการสำคัญได้ง่ายทั้งมือถือ แท็บเล็ต และเดสก์ท็อป ddfdsf
                    </p>

                    <ul class="mt-8 space-y-4">
                        <li
                            class="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-white/90">
                            <i data-lucide="check-circle-2" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-300"></i>
                            <span>จดทะเบียนแรงงานต่างด้าวและงานเอกสารที่เกี่ยวข้อง</span>
                        </li>
                        <li
                            class="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-white/90">
                            <i data-lucide="check-circle-2" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-300"></i>
                            <span>ต่ออายุใบอนุญาตทำงานและจัดการเอกสารสำคัญ</span>
                        </li>
                        <li
                            class="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-white/90">
                            <i data-lucide="check-circle-2" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-300"></i>
                            <span>เปลี่ยนนายจ้าง แจ้งออก และติดตามสถานะงานได้ทันที</span>
                        </li>
                    </ul>

                    <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-4">
                        @foreach ([['label' => 'รายการทั้งหมด', 'value' => number_format(2568), 'icon' => 'clipboard-list', 'tone' => 'text-blue-600'], ['label' => 'นายจ้างที่ใช้บริการ', 'value' => number_format(248), 'icon' => 'users', 'tone' => 'text-emerald-600'], ['label' => 'อัตราความสำเร็จ', 'value' => number_format(100, 1) . '%', 'icon' => 'badge-check', 'tone' => 'text-amber-500'], ['label' => 'บริการที่พร้อมใช้งาน', 'value' => number_format(200), 'icon' => 'sparkles', 'tone' => 'text-violet-600']] as $stat)
                            <div class="glass-card soft-shadow rounded-3xl border border-white/20 p-4 text-white">
                                <i data-lucide="{{ $stat['icon'] }}" class="mb-3 h-5 w-5 {{ $stat['tone'] }}"></i>
                                <div class="text-2xl font-bold leading-none">{{ $stat['value'] }}</div>
                                <div class="mt-2 text-xs leading-relaxed text-white/75">{{ $stat['label'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="xl:col-span-3 animate-fade-in-up animate-delay-100">
                    <div
                        class="relative h-full overflow-hidden rounded-[2rem] border border-white/15 bg-white/10 p-4 shadow-2xl backdrop-blur xl:p-5">
                        <div class="overflow-hidden rounded-[1.5rem] border border-white/20">
                            <img src="{{ Storage::url('images/logo.jpeg') }}" alt="บ้านพุ่มเจริญ จำกัด"
                                class="aspect-[4/5] w-full object-cover object-center">
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3 text-white">
                            <div class="rounded-2xl bg-white/10 p-4">
                                <div class="text-xs text-white/70">พร้อมให้บริการ</div>
                                <div class="mt-2 text-xl font-bold">24/7</div>
                            </div>
                            <div class="rounded-2xl bg-white/10 p-4">
                                <div class="text-xs text-white/70">ประสบการณ์</div>
                                <div class="mt-2 text-xl font-bold">15+ ปี</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-3 animate-fade-in-up animate-delay-200">
                    <div
                        class="relative h-full overflow-hidden rounded-[2rem] border border-white/10 bg-slate-900 p-6 text-white shadow-2xl sm:p-8">
                        <div class="relative z-10">
                            <div
                                class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-blue-200">
                                <i data-lucide="search" class="h-4 w-4"></i>
                                ตรวจสอบสถานะ
                            </div>
                            <h3 class="mt-4 text-2xl font-semibold">เช็กงานได้ทันที</h3>
                            <p class="mt-2 text-sm leading-7 text-slate-300">
                                กรอกเลขงานเพื่อตรวจสอบความคืบหน้าและดูข้อมูลล่าสุดของงาน
                            </p>

                            <form action="{{ route('status.index') }}" method="GET" class="relative mt-6">
                                <input type="text" name="job_number" placeholder="เช่น AP260528001"
                                    class="w-full rounded-2xl border border-white/15 bg-white/10 py-3.5 pl-4 pr-12 text-base text-white placeholder:text-slate-400 focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-400/40">
                                <button type="submit"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 rounded-xl p-2 text-blue-200 transition hover:bg-white/10 hover:text-white">
                                    <i data-lucide="search" class="h-5 w-5"></i>
                                </button>
                            </form>

                            <div class="my-6 text-center text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">
                                หรือสแกน QR Code
                            </div>

                            <button type="button"
                                class="flex w-full flex-col items-center justify-center gap-3 rounded-3xl border border-white/15 bg-white/10 px-4 py-6 text-white transition hover:bg-white/15">
                                <i data-lucide="qr-code" class="h-11 w-11"></i>
                                <span class="text-base font-medium">สแกน QR Code</span>
                            </button>
                        </div>

                        <div class="absolute -bottom-16 -right-16 h-48 w-48 rounded-full bg-blue-800/60"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Actions -->
    <section class="relative z-20 -mt-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                <a href="{{ route('services.index') }}"
                    class="action-card rounded-3xl border border-white/30 bg-white p-5 text-center shadow-xl sm:p-6">
                    <div class="icon-bg mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50">
                        <i data-lucide="file-plus" class="h-7 w-7 text-blue-600"></i>
                    </div>
                    <h4 class="mb-1 text-base font-semibold text-slate-800">บริการของเรา</h4>
                    <p class="text-sm text-slate-500">ดูงานที่สามารถเริ่มได้</p>
                </a>
                <a href="{{ route('status.index') }}"
                    class="action-card rounded-3xl border border-white/30 bg-white p-5 text-center shadow-xl sm:p-6">
                    <div class="icon-bg mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50">
                        <i data-lucide="search" class="h-7 w-7 text-blue-600"></i>
                    </div>
                    <h4 class="mb-1 text-base font-semibold text-slate-800">ตรวจสอบสถานะ</h4>
                    <p class="text-sm text-slate-500">ติดตามความคืบหน้า</p>
                </a>
                <a href="{{ route('news.index') }}"
                    class="action-card rounded-3xl border border-white/30 bg-white p-5 text-center shadow-xl sm:p-6">
                    <div class="icon-bg mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50">
                        <i data-lucide="newspaper" class="h-7 w-7 text-blue-600"></i>
                    </div>
                    <h4 class="mb-1 text-base font-semibold text-slate-800">ข่าวสาร</h4>
                    <p class="text-sm text-slate-500">อัปเดตประกาศสำคัญ</p>
                </a>
                <a href="{{ route('aboutus.index') }}"
                    class="action-card rounded-3xl border border-white/30 bg-white p-5 text-center shadow-xl sm:p-6">
                    <div class="icon-bg mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50">
                        <i data-lucide="badge-info" class="h-7 w-7 text-blue-600"></i>
                    </div>
                    <h4 class="mb-1 text-base font-semibold text-slate-800">เกี่ยวกับเรา</h4>
                    <p class="text-sm text-slate-500">รู้จักทีมและบริการ</p>
                </a>
                <a href="#"
                    class="action-card col-span-2 rounded-3xl border border-white/30 bg-white p-5 text-center shadow-xl sm:col-span-2 sm:p-6 lg:col-span-1">
                    <div class="icon-bg mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50">
                        <i data-lucide="headphones" class="h-7 w-7 text-blue-600"></i>
                    </div>
                    <h4 class="mb-1 text-base font-semibold text-slate-800">ติดต่อเจ้าหน้าที่</h4>
                    <p class="text-sm text-slate-500">สอบถามข้อมูลเพิ่มเติม</p>
                </a>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-14 sm:px-6 lg:px-8 lg:py-16">
        <div class="grid grid-cols-1 gap-8 xl:grid-cols-3">
            <!-- Recent Tasks -->
            <section class="xl:col-span-2 animate-fade-in-up animate-delay-100">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-900">งานล่าสุดของคุณ</h3>
                        <p class="mt-1 text-sm text-slate-500">ดูสถานะงานที่มีการอัปเดตล่าสุด</p>
                    </div>
                    <a href="#"
                        class="text-sm font-semibold text-blue-600 transition hover:text-blue-700">ดูทั้งหมด</a>
                </div>

                <div class="overflow-hidden rounded-[2rem] border border-white/60 bg-white shadow-xl">
                    <div class="md:hidden space-y-4 p-4 sm:p-6">
                        @forelse ($sampleCases as $case)
                            <article class="rounded-3xl border border-gray-100 bg-slate-50 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-slate-500">เลขงาน</p>
                                        <p class="mt-1 truncate text-lg font-bold text-slate-900">
                                            {{ $case['job_number'] }}
                                        </p>
                                    </div>
                                    <span
                                        class="inline-flex shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $case['status_badge_class'] }}">
                                        {{ $case['status_label'] }}
                                    </span>
                                </div>

                                <dl class="mt-4 space-y-3 text-sm">
                                    <div class="flex items-center justify-between gap-4 border-b border-gray-200 pb-2">
                                        <dt class="text-slate-500">ชื่อลูกจ้าง</dt>
                                        <dd class="text-right font-medium text-slate-800">
                                            {{ $case['worker_name'] }}
                                        </dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 border-b border-gray-200 pb-2">
                                        <dt class="text-slate-500">ประเภทงาน</dt>
                                        <dd class="text-right font-medium text-slate-800">
                                            {{ $case['service_name'] }}</dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-slate-500">อัปเดตล่าสุด</dt>
                                        <dd class="text-right font-medium text-slate-800">
                                            {{ $case['updated_at'] }}</dd>
                                    </div>
                                </dl>
                            </article>
                        @empty
                            <div class="rounded-3xl border border-dashed border-gray-200 bg-gray-50 p-8 text-center">
                                <p class="font-medium text-slate-700">ยังไม่มีงานล่าสุดในระบบ</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="hidden md:block">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[760px] text-left text-sm lg:text-base">
                                <thead class="bg-slate-50 text-slate-600">
                                    <tr>
                                        <th class="px-6 py-4 font-semibold">เลขงาน</th>
                                        <th class="px-6 py-4 font-semibold">ชื่อลูกจ้าง</th>
                                        <th class="px-6 py-4 font-semibold">ประเภทงาน</th>
                                        <th class="px-6 py-4 font-semibold">สถานะ</th>
                                        <th class="px-6 py-4 font-semibold">วันที่อัปเดต</th>
                                        <th class="px-6 py-4 font-semibold">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($sampleCases as $case)
                                        <tr class="table-row">
                                            <td class="px-6 py-5 font-semibold text-gray-700">{{ $case['job_number'] }}
                                            </td>
                                            <td class="px-6 py-5 text-gray-600">
                                                {{ $case['worker_name'] }}
                                            </td>
                                            <td class="px-6 py-5 text-gray-500">{{ $case['service_name'] }}</td>
                                            <td class="px-6 py-5">
                                                <span
                                                    class="inline-flex rounded-full px-3 py-1.5 text-xs font-semibold {{ $case['status_badge_class'] }}">
                                                    {{ $case['status_label'] }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-5 text-gray-500">
                                                {{ $case['updated_at'] }}</td>
                                            <td class="px-6 py-5">
                                                <button type="button"
                                                    class="text-gray-400 transition hover:text-blue-600">
                                                    <i data-lucide="eye" class="h-5 w-5"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                                ยังไม่มีงานล่าสุดในระบบ
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Notifications -->
            <aside class="animate-fade-in-up animate-delay-200">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-900">การแจ้งเตือน</h3>
                        <p class="mt-1 text-sm text-slate-500">การอัปเดตล่าสุดที่ควรติดตาม</p>
                    </div>
                    <a href="#"
                        class="text-sm font-semibold text-blue-600 transition hover:text-blue-700">ดูทั้งหมด</a>
                </div>

                <div class="space-y-4">
                    @forelse ($notifications as $notification)
                        @php
                            $icon = match ($notification->event_type ?? '') {
                                'completed' => 'check-circle',
                                'document_uploaded' => 'file-text',
                                'payment_verified' => 'credit-card',
                                default => 'bell',
                            };
                        @endphp
                        <article
                            class="notification-card flex gap-4 rounded-3xl border border-white/60 bg-white p-4 shadow-lg sm:p-5">
                            <div class="h-fit rounded-2xl bg-blue-50 p-3">
                                <i data-lucide="{{ $icon }}" class="h-6 w-6 text-blue-600"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <h5 class="text-sm font-semibold text-gray-800">
                                        งาน {{ $notification->jobOrder?->job_number ?? '-' }}
                                    </h5>
                                    <span class="text-xs text-gray-400">
                                        {{ $notification->created_at?->format('d/m/Y H:i') ?? '-' }}
                                    </span>
                                </div>
                                <p class="mt-2 text-sm leading-6 text-gray-500">
                                    {{ $notification->description ?? 'มีการอัปเดตสถานะใหม่' }}
                                </p>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-3xl border border-dashed border-gray-200 bg-white p-8 text-center shadow-sm">
                            <p class="font-medium text-slate-700">ยังไม่มีการแจ้งเตือน</p>
                            <p class="mt-2 text-sm text-slate-500">ระบบจะแสดงอัปเดตล่าสุดเมื่อมีความเคลื่อนไหวของงาน</p>
                        </div>
                    @endforelse
                </div>
            </aside>
        </div>
    </main>

    @if ($services->take(4)->isNotEmpty())
        <section class="pb-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-900">บริการยอดนิยม</h3>
                        <p class="mt-1 text-sm text-slate-500">เลือกบริการที่สนใจและดูรายละเอียดเพิ่มเติมได้ทันที</p>
                    </div>
                    <a href="{{ route('services.index') }}"
                        class="text-sm font-semibold text-blue-600 transition hover:text-blue-700">ดูบริการทั้งหมด</a>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($services->take(4) as $service)
                        <article class="lift-card rounded-[2rem] border border-white/70 bg-white p-6 shadow-xl">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50">
                                    <i data-lucide="layers-3" class="h-6 w-6 text-blue-600"></i>
                                </div>
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                    พร้อมใช้งาน
                                </span>
                            </div>

                            <h4 class="mt-5 text-xl font-bold text-slate-900">{{ $service->name }}</h4>
                            <p class="mt-3 text-sm leading-7 text-slate-500">
                                {{ \Illuminate\Support\Str::limit($service->description ?? 'บริการสำหรับจัดการเอกสารและขั้นตอนที่เกี่ยวข้อง', 120) }}
                            </p>

                            <a href="{{ route('services.show', $service->code) }}"
                                class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-blue-600 transition hover:text-blue-700">
                                ดูรายละเอียด
                                <i data-lucide="arrow-right" class="h-4 w-4"></i>
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
