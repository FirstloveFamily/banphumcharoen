@php
    $title = 'สำหรับนายจ้าง';
@endphp
@extends('layouts.app')

@push('head')
    <style>
        .hero-gradient {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 25%, #3b82f6 50%, #dc2626 75%, #991b1b 100%);
        }

        .stat-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }

        .table-row {
            transition: all 0.3s ease;
        }

        .table-row:hover {
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.04) 0%, rgba(220, 38, 38, 0.04) 100%);
            transform: scale(1.01);
        }

        .notification-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .notification-card:hover {
            transform: translateX(6px);
            border-color: #dc2626;
            box-shadow: 0 12px 32px rgba(220, 38, 38, 0.15);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
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
    @php
        $statusLabels = [
            'pending' => 'รอเริ่มงาน',
            'processing' => 'กำลังดำเนินการ',
            'waiting_document' => 'รอเอกสาร',
            'approved' => 'อนุมัติแล้ว',
            'completed' => 'เสร็จสิ้น',
            'cancelled' => 'ยกเลิก',
            'rejected' => 'ไม่ผ่าน',
        ];

        $statusStyles = [
            'pending' => 'bg-slate-100 text-slate-700',
            'processing' => 'bg-orange-50 text-orange-700',
            'waiting_document' => 'bg-blue-50 text-blue-700',
            'approved' => 'bg-emerald-50 text-emerald-700',
            'completed' => 'bg-green-50 text-green-700',
            'cancelled' => 'bg-slate-100 text-slate-500',
            'rejected' => 'bg-red-50 text-red-700',
        ];
    @endphp

    <!-- Hero Section -->
    <section class="hero-gradient py-20 lg:py-28 relative overflow-hidden">
        <!-- Decorative Elements -->
        <div class="absolute inset-0 opacity-20 pointer-events-none">
            <div class="absolute -left-40 -top-40 h-[600px] w-[600px] rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute bottom-[-12rem] right-[-10rem] h-[500px] w-[500px] rounded-full bg-red-500/20 blur-3xl">
            </div>
            <div
                class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[700px] w-[700px] rounded-full bg-blue-400/10 blur-3xl">
            </div>
        </div>

        <!-- Grid Pattern -->
        <div
            class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,.04)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.04)_1px,transparent_1px)] bg-[size:60px_60px] opacity-30">
        </div>

        <div class="max-w-7xl mx-auto px-4 text-white sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 gap-12 lg:grid-cols-12 lg:items-center">
                <div class="lg:col-span-8 animate-fade-in-up">
                    <p class="text-sm font-semibold text-blue-200 uppercase tracking-wider">EMPLOYER PORTAL</p>
                    <h1 class="mt-4 text-5xl lg:text-6xl font-bold leading-tight">
                        @if ($selectedEmployer)
                            {{-- <i data-lucide="building-2" class="h-5 w-5"></i> --}}
                            {{ $selectedEmployer->company_name }}


                            <div
                                class="mt-8 inline-flex items-center gap-3 rounded-full bg-white/10 px-5 py-3 text-base text-blue-50 border border-white/20">
                                <i data-lucide="building-2" class="h-5 w-5"></i>
                                สำหรับนายจ้าง
                            </div>
                        @endif
                    </h1>
                    <p class="mt-6 max-w-3xl text-xl leading-8 text-white/90">
                        ภาพรวมงานเอกสารแรงงานต่างด้าว ติดตามสถานะงาน และดูรายการที่ต้องดำเนินการต่อในที่เดียว
                    </p>
                    @if ($selectedEmployer)
                        <div
                            class="mt-8 inline-flex items-center gap-3 rounded-full bg-white/10 px-5 py-3 text-base text-blue-50 border border-white/20">
                            <i data-lucide="building-2" class="h-5 w-5"></i>
                            {{ $selectedEmployer->company_name }}
                        </div>
                    @endif
                </div>

                <div class="lg:col-span-4 animate-fade-in-up animate-delay-100">
                    <form action="{{ route('status.index') }}" method="GET"
                        class="rounded-3xl bg-white p-6 shadow-2xl border border-white/20">
                        <label for="job_number" class="text-sm font-semibold text-blue-950">ตรวจสอบเลขงานด่วน</label>
                        <div class="mt-4 flex gap-3">
                            <input id="job_number" type="text" name="job_number" placeholder="เช่น AP260528001"
                                class="min-w-0 flex-1 rounded-2xl border border-slate-200 px-5 py-3.5 text-base text-slate-700 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                            <button type="submit"
                                class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-900 text-white transition hover:bg-blue-800 shadow-lg"
                                aria-label="ค้นหาเลขงาน">
                                <i data-lucide="search" class="h-6 w-6"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="bg-slate-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-6 lg:grid-cols-5">
                <div
                    class="stat-card rounded-3xl border border-white/20 bg-white p-6 shadow-xl animate-fade-in-up animate-delay-100">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-slate-500 font-medium">งานทั้งหมด</p>
                        <i data-lucide="briefcase-business" class="h-6 w-6 text-blue-600"></i>
                    </div>
                    <p class="mt-4 text-4xl font-bold text-blue-950">{{ number_format($totalJobs) }}</p>
                </div>
                <div
                    class="stat-card rounded-3xl border border-white/20 bg-white p-6 shadow-xl animate-fade-in-up animate-delay-200">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-slate-500 font-medium">กำลังดำเนินการ</p>
                        <i data-lucide="loader-circle" class="h-6 w-6 text-orange-500"></i>
                    </div>
                    <p class="mt-4 text-4xl font-bold text-blue-950">{{ number_format($processingJobs) }}</p>
                </div>
                <div
                    class="stat-card rounded-3xl border border-white/20 bg-white p-6 shadow-xl animate-fade-in-up animate-delay-300">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-slate-500 font-medium">รอเอกสาร</p>
                        <i data-lucide="file-warning" class="h-6 w-6 text-blue-600"></i>
                    </div>
                    <p class="mt-4 text-4xl font-bold text-blue-950">{{ number_format($waitingDocumentJobs) }}</p>
                </div>
                <div class="stat-card rounded-3xl border border-white/20 bg-white p-6 shadow-xl animate-fade-in-up">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-slate-500 font-medium">รอชำระเงิน</p>
                        <i data-lucide="credit-card" class="h-6 w-6 text-red-500"></i>
                    </div>
                    <p class="mt-4 text-4xl font-bold text-blue-950">{{ number_format($waitingPaymentJobs) }}</p>
                </div>
                <div
                    class="col-span-2 stat-card rounded-3xl border border-white/20 bg-white p-6 shadow-xl lg:col-span-1 animate-fade-in-up">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-slate-500 font-medium">เสร็จสิ้น</p>
                        <i data-lucide="circle-check" class="h-6 w-6 text-green-600"></i>
                    </div>
                    <p class="mt-4 text-4xl font-bold text-blue-950">{{ number_format($completedJobs) }}</p>
                </div>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-8 lg:grid-cols-3">
                <div class="lg:col-span-2 animate-fade-in-up animate-delay-100">
                    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-blue-950">งานล่าสุด</h2>
                            <p class="mt-2 text-base text-slate-500">รายการงานที่มีการอัปเดตล่าสุดของนายจ้าง</p>
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('employers.workers.index') }}"
                                class="inline-flex items-center justify-center gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-base font-medium text-slate-700 transition hover:bg-slate-50 shadow-md">
                                <i data-lucide="users" class="h-5 w-5"></i>
                                รายชื่อแรงงาน
                            </a>
                            <a href="{{ route('employers.jobs.create') }}"
                                class="inline-flex items-center justify-center gap-3 rounded-2xl bg-blue-900 px-5 py-3 text-base font-medium text-white transition hover:bg-blue-800 shadow-lg">
                                <i data-lucide="plus" class="h-5 w-5"></i>
                                แจ้งงานใหม่
                            </a>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-3xl border border-white/20 bg-white shadow-xl">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[760px] text-left text-base">
                                <thead class="bg-slate-50 text-slate-600 font-semibold">
                                    <tr>
                                        <th class="px-6 py-5">เลขงาน</th>
                                        <th class="px-6 py-5">ลูกจ้าง</th>
                                        <th class="px-6 py-5">ประเภทงาน</th>
                                        <th class="px-6 py-5">สถานะ</th>
                                        <th class="px-6 py-5">เอกสาร</th>
                                        <th class="px-6 py-5">อัปเดต</th>
                                        <th class="px-6 py-5"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($recentJobs as $job)
                                        <tr class="table-row">
                                            <td class="px-6 py-5 font-semibold text-slate-700">{{ $job->job_number }}</td>
                                            <td class="px-6 py-5 text-slate-700">
                                                {{ $job->worker?->full_name_th ?: $job->worker?->full_name_en ?: '-' }}
                                            </td>
                                            <td class="px-6 py-5 text-slate-500">{{ $job->service?->name ?: '-' }}</td>
                                            <td class="px-6 py-5">
                                                <span
                                                    class="{{ $statusStyles[$job->status] ?? 'bg-slate-100 text-slate-700' }} rounded-full px-4 py-2 text-sm font-semibold">
                                                    {{ $statusLabels[$job->status] ?? $job->status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-5 text-slate-500">
                                                @if ($job->document_issues_count > 0)
                                                    <span
                                                        class="font-semibold text-orange-600">{{ $job->document_issues_count }}
                                                        รายการ</span>
                                                @else
                                                    ครบถ้วน
                                                @endif
                                            </td>
                                            <td class="px-6 py-5 text-slate-500">{{ $job->updated_at?->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-5 text-right">
                                                <a href="{{ route('employers.jobs.show', $job->job_number) }}"
                                                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-400 transition hover:bg-blue-50 hover:text-blue-700"
                                                    aria-label="ดูรายละเอียดงาน {{ $job->job_number }}">
                                                    <i data-lucide="eye" class="h-5 w-5"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-16 text-center">
                                                <div
                                                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-blue-700 shadow-lg">
                                                    <i data-lucide="clipboard-list" class="h-8 w-8"></i>
                                                </div>
                                                <h3 class="mt-6 text-2xl font-bold text-blue-950">ยังไม่มีงานในระบบ</h3>
                                                <p class="mt-3 text-base text-slate-500">เมื่อมีการแจ้งงาน
                                                    รายการล่าสุดจะแสดงที่นี่</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="space-y-8 animate-fade-in-up animate-delay-200">
                    <div class="rounded-3xl border border-white/20 bg-white p-8 shadow-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-blue-950">แจ้งเตือนวันหมดอายุ</h2>
                                <p class="mt-2 text-base text-slate-500">ภายใน 45 วัน</p>
                            </div>
                            <i data-lucide="calendar-clock" class="h-6 w-6 text-orange-500"></i>
                        </div>

                        <div class="mt-6 space-y-4">
                            @forelse ($expiringWorkers as $worker)
                                @php
                                    $dates = collect([
                                        'ใบอนุญาตทำงาน' => $worker->wp_expiry,
                                        'วีซ่า' => $worker->visa_expiry,
                                        'พาสปอร์ต' => $worker->passport_expiry,
                                        'รายงาน 90 วัน' => $worker->report_90_days_due,
                                    ])->filter(
                                        fn($date) => $date &&
                                            $date->betweenIncluded(now()->startOfDay(), now()->addDays(45)->endOfDay()),
                                    );
                                @endphp
                                <div class="rounded-2xl border border-orange-100 bg-orange-50/50 p-5">
                                    <p class="font-semibold text-slate-800 text-base">
                                        {{ $worker->full_name_th ?: $worker->full_name_en }}
                                    </p>
                                    @foreach ($dates as $label => $date)
                                        <p class="mt-2 text-sm text-orange-700">{{ $label }}:
                                            {{ $date->format('d/m/Y') }}</p>
                                    @endforeach
                                </div>
                            @empty
                                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5 text-base text-slate-500">
                                    ไม่มีรายการใกล้หมดอายุในช่วงนี้
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-3xl border border-white/20 bg-white p-8 shadow-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-blue-950">ความเคลื่อนไหวล่าสุด</h2>
                                <p class="mt-2 text-base text-slate-500">อัปเดตจากเจ้าหน้าที่</p>
                            </div>
                            <i data-lucide="bell" class="h-6 w-6 text-blue-600"></i>
                        </div>

                        <div class="mt-6 space-y-4">
                            @forelse ($notifications as $notification)
                                <div
                                    class="notification-card flex gap-4 rounded-2xl border border-white/20 bg-white p-5 shadow-md">
                                    <div class="mt-1.5 h-3 w-3 shrink-0 rounded-full bg-blue-600"></div>
                                    <div>
                                        <p class="text-base font-medium text-slate-800">
                                            {{ $notification->jobOrder?->job_number }} {{ $notification->action }}
                                        </p>
                                        <p class="mt-1 text-sm text-slate-500">
                                            {{ $notification->created_at?->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5 text-base text-slate-500">
                                    ยังไม่มีความเคลื่อนไหวล่าสุด
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
