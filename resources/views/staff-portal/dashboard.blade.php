@extends('layouts.staff-portal', ['title' => 'Staff Dashboard', 'pageTitle' => 'Dashboard Overview'])

@push('head')
<style>
    .stat-card {
        transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
    }
    .hero-rule {
        background: linear-gradient(90deg, #ffffff, rgba(255, 255, 255, 0));
    }
    .hero-ribbon {
        background: linear-gradient(90deg, rgba(255,255,255,0.18), rgba(255,255,255,0));
    }
</style>
@endpush

@section('content')
    <div class="space-y-8">
        <header class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#0b2f52] via-[#123e68] to-[#b91c1c] text-white shadow-2xl shadow-[#0b2f52]/20">
            <div class="pointer-events-none absolute inset-0 opacity-70">
                <div class="absolute -left-14 top-0 h-44 w-44 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute right-0 top-0 h-40 w-40 rounded-full bg-[#fecaca]/10 blur-3xl"></div>
                <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>
            </div>
            <div class="relative flex flex-col gap-6 px-5 py-6 sm:px-7 lg:flex-row lg:items-end lg:justify-between lg:px-8">
                <div class="max-w-3xl">
                    <div class="mb-4 h-1 w-28 rounded-full hero-rule"></div>
                    <p class="text-xs font-bold uppercase tracking-[0.28em] text-[#fecaca]">Enterprise Staff Portal</p>
                    <h2 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">
                        ยินดีต้อนรับ {{ auth()->user()->name }}
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-200/90 sm:text-base">
                        ภาพรวมงาน เอกสาร ใบงาน และรายการรอตรวจสอบในมุมมองเดียว
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:min-w-[360px]">
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-white/70">เวลาระบบ</p>
                        <p class="mt-1 text-sm font-semibold text-white">{{ now()->format('D, d M Y H:i') }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/15 bg-white/12 p-4 backdrop-blur">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-[#fecaca]">รายการรอจัดการ</p>
                        <p class="mt-1 text-sm font-semibold text-white">{{ number_format($pendingReviews->count() + $pendingPayments->count()) }} รายการ</p>
                    </div>
                </div>
            </div>
        </header>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @php
                $statConfigs = [
                    'blue' => ['bg' => 'bg-gradient-to-br from-[#0b2f52] to-[#123e68]', 'icon' => 'text-white'],
                    'red' => ['bg' => 'bg-gradient-to-br from-[#b91c1c] to-[#dc2626]', 'icon' => 'text-white'],
                    'white' => ['bg' => 'bg-white', 'icon' => 'text-[#0b2f52]'],
                    'slate' => ['bg' => 'bg-slate-100', 'icon' => 'text-slate-600'],
                ];
            @endphp

            @foreach ($stats as $stat)
                @php $cfg = $statConfigs[$stat['tone']] ?? $statConfigs['blue']; @endphp
                <article class="stat-card manager-card manager-card-hover p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-500">{{ $stat['label'] }}</p>
                            <h3 class="mt-3 text-3xl font-extrabold text-[#0b2f52]">{{ number_format($stat['value']) }}</h3>
                            <p class="mt-3 text-sm font-medium text-slate-500">{{ $stat['description'] }}</p>
                        </div>
                        <div class="grid h-11 w-11 place-items-center rounded-xl {{ $cfg['bg'] }} {{ $cfg['icon'] }} shadow-sm">
                            <i data-lucide="{{ $stat['icon'] }}" class="h-5 w-5"></i>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
            <div class="space-y-6">
                <section class="manager-card overflow-hidden">
                    <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div class="flex items-center gap-3">
                            <div class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-[#0b2f52] to-[#b91c1c] text-white shadow-sm">
                                <i data-lucide="clock-3" class="h-5 w-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-[#0b2f52]">คิวเอกสารใกล้หมดอายุ</h3>
                                <p class="text-sm text-slate-500">ติดตามรายการที่ต้องดำเนินการล่วงหน้า</p>
                            </div>
                        </div>
                        <a href="{{ route('staff.portal.workers.index', ['expiry' => 'expiring']) }}" class="text-sm font-bold text-[#b91c1c] hover:text-[#0b2f52]">ดูทั้งหมด →</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="manager-table-head text-xs font-bold uppercase tracking-wider">
                                    <th class="px-8 py-4">แรงงาน / นายจ้าง</th>
                                    <th class="px-4 py-4">ประเภทเอกสาร</th>
                                    <th class="px-4 py-4 text-center">วันหมดอายุ</th>
                                    <th class="px-8 py-4 text-right">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($expiringItems as $item)
                                    @php
                                        $expired = $item['expiry_date']->isPast();
                                        $days = now()->startOfDay()->diffInDays($item['expiry_date']->copy()->startOfDay(), false);
                                    @endphp
                                <tr class="manager-row-hover transition-colors">
                                    <td class="px-8 py-4">
                                        <div class="flex items-center gap-3">
                                                <div class="h-9 w-9 rounded-full bg-gradient-to-br from-white to-[#fee2e2] flex items-center justify-center font-bold text-[#0b2f52] border border-slate-200">
                                                    {{ mb_substr($item['worker'], 0, 1) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="font-bold text-[#0b2f52] truncate">{{ $item['worker'] }}</p>
                                                    <p class="text-xs text-slate-500 truncate">{{ $item['employer'] }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1 font-semibold text-slate-700">
                                                <i data-lucide="file-text" class="h-3.5 w-3.5"></i>
                                                {{ $item['document'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-center font-mono font-bold text-slate-700">
                                            {{ $item['expiry_date']->format('d/m/Y') }}
                                        </td>
                                        <td class="px-8 py-4 text-right">
                                            @if($expired)
                                                <span class="rounded-full bg-[#fff1f1] px-3 py-1 text-xs font-bold text-[#b91c1c] ring-1 ring-inset ring-[#b91c1c]/20">หมดอายุแล้ว</span>
                                            @else
                                                <span @class([
                                                    'rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset',
                                                    'bg-[#fff1f1] text-[#b91c1c] ring-[#b91c1c]/25' => $days <= 14,
                                                    'bg-[#0b2f52] text-white ring-[#0b2f52]/20' => $days > 14,
                                                ])>อีก {{ $days }} วัน</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-8 py-12 text-center text-slate-400">ไม่พบรายการใกล้หมดอายุในขณะนี้</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="manager-card overflow-hidden">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                        <div>
                            <h3 class="text-base font-extrabold text-[#0b2f52]">ใบงานที่กำลังดำเนินการ</h3>
                            <p class="text-sm text-slate-500">งานที่ยังอยู่ใน flow และต้องติดตามต่อ</p>
                        </div>
                        <a href="{{ route('staff.portal.job-orders.index') }}" class="text-sm font-bold text-[#0b2f52]">จัดการใบงาน →</a>
                    </div>
                    <div class="grid gap-4 p-6 sm:grid-cols-2">
                        @foreach ($openJobs as $job)
                            <div class="group relative rounded-2xl border border-slate-100 bg-white p-5 hover:border-[#b91c1c]/25 hover:shadow-md transition-all">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-black uppercase text-[#0b2f52]">{{ $job->job_number }}</span>
                                            @if($job->priority === 'urgent')
                                                <span class="flex h-2 w-2 rounded-full bg-rose-500 animate-pulse"></span>
                                            @endif
                                        </div>
                                        <h4 class="mt-1 font-bold text-[#0b2f52] truncate">{{ $job->service?->name ?? 'Service' }}</h4>
                                        <p class="text-xs text-slate-500 truncate">{{ $job->employer?->company_name }}</p>
                                    </div>
                                        <span @class([
                                            'rounded-lg px-2.5 py-1 text-[10px] font-black uppercase tracking-wider',
                                        'bg-[#fff1f1] text-[#b91c1c]' => $job->status === 'pending',
                                        'bg-[#0b2f52] text-white' => $job->status === 'processing',
                                        'bg-[#fff1f1] text-[#b91c1c]' => $job->status === 'waiting_document',
                                        'bg-slate-100 text-slate-700' => $job->status === 'approved',
                                    ])>{{ str($job->status)->replace('_', ' ') }}</span>
                                </div>
                                <div class="mt-4 flex items-center justify-between border-t border-slate-50 pt-4">
                                    <div class="flex items-center gap-2">
                                        <div class="h-6 w-6 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500">
                                            {{ mb_substr($job->worker?->full_name_th ?? 'W', 0, 1) }}
                                        </div>
                                        <span class="text-xs font-semibold text-slate-600 truncate max-w-[100px]">{{ $job->worker?->full_name_th }}</span>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Due Date</p>
                                        <p class="text-xs font-bold text-[#0b2f52]">{{ $job->due_date?->format('d/m/Y') ?? '-' }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('staff.portal.job-orders.show', $job) }}" class="absolute inset-0 z-10 rounded-2xl"></a>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="manager-card overflow-hidden">
                    <div class="border-b border-slate-100 bg-slate-50/50 px-5 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-extrabold uppercase tracking-[0.18em] text-[#0b2f52]">เอกสารรอตรวจ</h3>
                            <span class="rounded-full bg-[#fff1f1] px-2.5 py-0.5 text-xs font-bold text-[#b91c1c]">{{ $pendingReviews->count() }}</span>
                        </div>
                    </div>
                    <div class="p-4 space-y-3">
                        @forelse ($pendingReviews as $review)
                            <div class="rounded-2xl border border-slate-100 bg-white p-4 transition-all hover:shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-[#0b2f52] truncate">{{ $review->documentMaster?->name ?? 'เอกสาร' }}</p>
                                        <p class="text-[11px] font-medium text-slate-500">{{ $review->jobOrder?->job_number }} · {{ $review->jobOrder?->worker?->full_name_th }}</p>
                                    </div>
                                    <i data-lucide="chevron-right" class="h-4 w-4 text-slate-300"></i>
                                </div>
                                <div class="mt-3 flex items-center justify-between">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">{{ $review->updated_at->diffForHumans() }}</span>
                                    <a href="{{ route('staff.portal.document-reviews.index') }}" class="text-[11px] font-bold text-[#0b2f52]">ตรวจสอบ</a>
                                </div>
                            </div>
                        @empty
                            <p class="py-6 text-center text-sm text-slate-400">ไม่มีเอกสารรอตรวจ</p>
                        @endforelse
                    </div>
                </section>

                <section class="manager-card overflow-hidden">
                    <div class="border-b border-slate-100 bg-slate-50/50 px-5 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-extrabold uppercase tracking-[0.18em] text-[#0b2f52]">สลิปรอตรวจ</h3>
                            <i data-lucide="receipt-thai-baht" class="h-5 w-5 text-slate-400"></i>
                        </div>
                    </div>
                    <div class="p-4 space-y-3">
                        @forelse ($pendingPayments as $payment)
                            <div class="rounded-2xl border border-slate-100 bg-white p-4 transition-all hover:shadow-sm">
                                <div class="flex items-center justify-between">
                                    <div class="min-w-0">
                                        <p class="text-sm font-black text-[#0b2f52]">฿{{ number_format((float) $payment->amount, 2) }}</p>
                                        <p class="text-[11px] font-medium text-slate-500 truncate">{{ $payment->jobOrder?->job_number }} · {{ $payment->jobOrder?->employer?->company_name }}</p>
                                    </div>
                                    <a href="{{ route('staff.portal.payment-reviews.index') }}" class="grid h-8 w-8 place-items-center rounded-full bg-slate-50 text-slate-400 hover:bg-[#fff1f1] hover:text-[#0b2f52] transition-colors">
                                        <i data-lucide="arrow-right-circle" class="h-5 w-5"></i>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <p class="py-6 text-center text-sm text-slate-400">ไม่มีสลิปรอตรวจ</p>
                        @endforelse
                    </div>
                </section>

                <section class="relative overflow-hidden rounded-lg bg-[#0b2f52] p-5 text-white shadow-xl shadow-[#0b2f52]/15">
                    <div class="absolute -right-8 -bottom-8 h-32 w-32 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="relative z-10">
                        <h3 class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#fecaca]">Quick Actions</h3>
                        <div class="mt-6 space-y-3">
                            <a href="{{ route('staff.portal.job-orders.index') }}" class="flex items-center gap-3 rounded-xl bg-white/10 p-3 text-sm font-bold transition-all hover:bg-white/20">
                                <i data-lucide="plus-circle" class="h-4 w-4"></i>
                                สร้างใบงานใหม่
                            </a>
                            <a href="{{ route('staff.portal.workers.create') }}" class="flex items-center gap-3 rounded-xl bg-white/10 p-3 text-sm font-bold transition-all hover:bg-white/20">
                                <i data-lucide="user-plus" class="h-4 w-4"></i>
                                ลงทะเบียนแรงงาน
                            </a>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
@endsection
