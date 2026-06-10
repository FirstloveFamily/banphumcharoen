@extends('layouts.manager', ['title' => 'ภาพรวมผู้บริหาร'])

@push('head')
<style>
    .manager-card {
        background: #ffffff;
        border: 1px solid rgba(7, 20, 38, 0.08);
        border-radius: 8px;
        box-shadow: 0 12px 28px rgba(7, 20, 38, 0.06);
    }

    .manager-card-hover {
        transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
    }

    .manager-card-hover:hover {
        transform: translateY(-2px);
        border-color: rgba(201, 162, 39, 0.35);
        box-shadow: 0 18px 36px rgba(7, 20, 38, 0.1);
    }

    .gold-rule {
        background: linear-gradient(90deg, #c9a227, rgba(201, 162, 39, 0));
    }
</style>
@endpush

@section('content')
    <div class="space-y-8">
        <header class="overflow-hidden rounded-lg bg-[#0b2f52] text-white shadow-xl shadow-[#0b2f52]/15">
            <div class="flex flex-col gap-6 px-5 py-6 sm:px-7 lg:flex-row lg:items-end lg:justify-between lg:px-8">
                <div class="max-w-3xl">
                    <div class="mb-4 h-1 w-24 rounded-full gold-rule"></div>
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-[#f3d06f]">แดชบอร์ดผู้บริหาร</p>
                    <h2 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">
                        ยินดีต้อนรับ {{ auth()->user()->name }}
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                        สรุปภาพรวมธุรกิจ งานที่ต้องติดตาม และรายการที่ต้องตรวจสอบสำหรับผู้บริหาร
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:min-w-[360px]">
                    <div class="rounded-lg border border-white/10 bg-white/10 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">เวลาระบบ</p>
                        <p class="mt-1 text-sm font-semibold text-white">{{ now()->format('D, d M Y H:i') }}</p>
                    </div>
                    <div class="rounded-lg border border-[#c9a227]/30 bg-[#c9a227]/15 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-[#f3d06f]">รายการรอตรวจ</p>
                        <p class="mt-1 text-sm font-semibold text-white">{{ number_format($pendingApprovals) }} รายการ</p>
                    </div>
                </div>
            </div>
        </header>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="manager-card manager-card-hover p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-500">งานสะสมทั้งหมด</p>
                        <h3 class="mt-3 text-3xl font-extrabold text-[#0b2f52]">{{ number_format($totalJobs) }}</h3>
                    </div>
                    <div class="grid h-11 w-11 place-items-center rounded-lg bg-[#0b2f52] text-[#f3d06f]">
                        <i data-lucide="briefcase" class="h-5 w-5"></i>
                    </div>
                </div>
                <div class="mt-5 h-1 rounded-full bg-slate-100">
                    <div class="h-1 w-4/5 rounded-full bg-[#c9a227]"></div>
                </div>
            </article>

            <article class="manager-card manager-card-hover p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-500">กำลังดำเนินการ</p>
                        <h3 class="mt-3 text-3xl font-extrabold text-[#0b2f52]">{{ number_format($processingJobs) }}</h3>
                    </div>
                    <div class="grid h-11 w-11 place-items-center rounded-lg bg-[#fff7d6] text-[#9a7415]">
                        <i data-lucide="clock-3" class="h-5 w-5"></i>
                    </div>
                </div>
                <p class="mt-4 text-sm font-medium text-slate-500">รอดำเนินการ, processing และ approved</p>
            </article>

            <article class="manager-card manager-card-hover p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-500">ปิดงานเรียบร้อย</p>
                        <h3 class="mt-3 text-3xl font-extrabold text-[#0b2f52]">{{ number_format($completedJobs) }}</h3>
                    </div>
                    <div class="grid h-11 w-11 place-items-center rounded-lg bg-emerald-50 text-emerald-700">
                        <i data-lucide="check-circle-2" class="h-5 w-5"></i>
                    </div>
                </div>
                <p class="mt-4 text-sm font-medium text-slate-500">งานที่ดำเนินการเสร็จสมบูรณ์</p>
            </article>

            <article class="manager-card manager-card-hover border-[#c9a227]/35 bg-[#0b2f52] p-5 text-white">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-[#f3d06f]">รอตรวจ/อนุมัติ</p>
                        <h3 class="mt-3 text-3xl font-extrabold">{{ number_format($pendingApprovals) }}</h3>
                    </div>
                    <div class="grid h-11 w-11 place-items-center rounded-lg bg-[#c9a227] text-[#0b2f52]">
                        <i data-lucide="shield-alert" class="h-5 w-5"></i>
                    </div>
                </div>
                <p class="mt-4 text-sm font-medium text-slate-300">รายการที่ควรปิดให้เร็วที่สุด</p>
            </article>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
            <div class="space-y-6">
                <section class="manager-card overflow-hidden">
                    <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div class="flex items-center gap-3">
                            <div class="grid h-10 w-10 place-items-center rounded-lg bg-[#0b2f52] text-[#f3d06f]">
                                <i data-lucide="list-todo" class="h-5 w-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-[#0b2f52]">งานล่าสุดในระบบ</h3>
                                <p class="text-sm text-slate-500">ติดตามความเคลื่อนไหวของใบงานชุดใหม่</p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-left text-sm">
                            <thead>
                                <tr class="bg-slate-50 text-xs font-bold uppercase tracking-widest text-slate-500">
                                    <th class="px-6 py-4">ใบงาน / แรงงาน</th>
                                    <th class="px-4 py-4">บริการ</th>
                                    <th class="px-4 py-4 text-center">สถานะ</th>
                                    <th class="px-6 py-4 text-right">อัปเดตเมื่อ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($recentJobs as $job)
                                    <tr class="group cursor-pointer transition-colors hover:bg-[#fff9e8]" onclick="window.location='{{ route('staff.portal.job-orders.show', $job) }}'">
                                        <td class="px-6 py-4">
                                            <p class="font-extrabold uppercase tracking-tight text-[#0b2f52]">{{ $job->job_number }}</p>
                                            <p class="mt-1 text-xs font-bold text-slate-700">{{ $job->worker?->full_name_th ?: '-' }}</p>
                                            <p class="mt-0.5 max-w-[260px] truncate text-[11px] text-slate-500">{{ $job->employer?->company_name }}</p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex max-w-[220px] items-center rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">
                                                <span class="truncate">{{ $job->service?->name ?? '-' }}</span>
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span @class([
                                                'inline-flex min-w-[92px] justify-center rounded-full px-3 py-1 text-[11px] font-extrabold uppercase tracking-wide ring-1 ring-inset',
                                                'bg-emerald-50 text-emerald-700 ring-emerald-600/20' => $job->status === 'completed',
                                                'bg-[#fff7d6] text-[#8b6914] ring-[#c9a227]/30' => $job->status === 'processing',
                                                'bg-slate-50 text-slate-700 ring-slate-300' => $job->status === 'pending',
                                                'bg-[#0b2f52] text-[#f3d06f] ring-[#c9a227]/40' => $job->status === 'approved',
                                                'bg-rose-50 text-rose-700 ring-rose-600/20' => !in_array($job->status, ['completed', 'processing', 'pending', 'approved']),
                                            ])>
                                                {{ $job->status_label ?? $job->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right text-xs font-bold uppercase tracking-tight text-slate-500">
                                            {{ $job->updated_at?->diffForHumans() }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-12 text-center text-sm font-semibold text-slate-400">ยังไม่มีข้อมูลงานล่าสุด</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="manager-card p-5 sm:p-6">
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="grid h-10 w-10 place-items-center rounded-lg bg-[#fff7d6] text-[#8b6914]">
                                <i data-lucide="shield-alert" class="h-5 w-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-[#0b2f52]">รายการรออนุมัติ / ตรวจสอบพิเศษ</h3>
                                <p class="text-sm text-slate-500">งานที่ต้องมีการตัดสินใจหรือยืนยัน</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @forelse($pendingApprovalsList as $item)
                            <div class="flex flex-col gap-4 rounded-lg border border-slate-100 bg-slate-50/70 p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <h4 class="font-bold text-[#0b2f52]">{{ $item->title ?? 'รายการรออนุมัติ' }}</h4>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">{{ $item->description ?? '-' }}</p>
                                    <p class="mt-2 text-[11px] font-bold uppercase tracking-widest text-slate-400">{{ $item->created_at?->format('d M Y H:i') }}</p>
                                </div>
                                <a href="{{ $item->action_url ?? '#' }}" class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-lg bg-[#0b2f52] px-4 text-xs font-extrabold uppercase tracking-widest text-white transition hover:bg-[#123e68]">
                                    <span>ตรวจสอบ</span>
                                    <i data-lucide="arrow-right" class="h-4 w-4 text-[#f3d06f]"></i>
                                </a>
                            </div>
                        @empty
                            <div class="rounded-lg border border-emerald-100 bg-emerald-50 py-8 text-center">
                                <i data-lucide="check-circle-2" class="mx-auto h-8 w-8 text-emerald-600"></i>
                                <p class="mt-3 text-sm font-bold text-emerald-700">ไม่มีรายการรออนุมัติ</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="manager-card p-5">
                    <h3 class="mb-4 flex items-center gap-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#0b2f52]">
                        <span class="h-2 w-2 rounded-full bg-[#c9a227]"></span>
                        เมนูลัดรายงาน
                    </h3>
                    <div class="grid gap-3">
                        <a href="{{ route('manager.reports.financial') }}" class="group flex items-center justify-between rounded-lg border border-slate-100 bg-white p-4 text-[#0b2f52] transition hover:border-[#c9a227]/40 hover:bg-[#fff9e8]">
                            <span class="flex items-center gap-3 text-sm font-bold">
                                <i data-lucide="banknote" class="h-5 w-5 text-[#c9a227]"></i>
                                สรุปการเงิน
                            </span>
                            <i data-lucide="chevron-right" class="h-4 w-4 text-slate-400 transition group-hover:translate-x-1 group-hover:text-[#c9a227]"></i>
                        </a>
                        <a href="{{ route('manager.reports.expired_cards') }}" class="group flex items-center justify-between rounded-lg border border-slate-100 bg-white p-4 text-[#0b2f52] transition hover:border-[#c9a227]/40 hover:bg-[#fff9e8]">
                            <span class="flex items-center gap-3 text-sm font-bold">
                                <i data-lucide="alert-triangle" class="h-5 w-5 text-[#c9a227]"></i>
                                ตรวจเอกสารหมดอายุ
                            </span>
                            <i data-lucide="chevron-right" class="h-4 w-4 text-slate-400 transition group-hover:translate-x-1 group-hover:text-[#c9a227]"></i>
                        </a>
                        <a href="{{ route('manager.reports.workers') }}" class="group flex items-center justify-between rounded-lg border border-slate-100 bg-white p-4 text-[#0b2f52] transition hover:border-[#c9a227]/40 hover:bg-[#fff9e8]">
                            <span class="flex items-center gap-3 text-sm font-bold">
                                <i data-lucide="users" class="h-5 w-5 text-[#c9a227]"></i>
                                รายงานแรงงาน
                            </span>
                            <i data-lucide="chevron-right" class="h-4 w-4 text-slate-400 transition group-hover:translate-x-1 group-hover:text-[#c9a227]"></i>
                        </a>
                    </div>
                </section>

                <section class="manager-card p-5">
                    <h3 class="mb-5 flex items-center gap-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#0b2f52]">
                        <i data-lucide="activity" class="h-4 w-4 text-[#c9a227]"></i>
                        กิจกรรมล่าสุด
                    </h3>
                    <div class="relative space-y-5 before:absolute before:bottom-2 before:left-[9px] before:top-2 before:w-px before:bg-slate-200">
                        @forelse($recentActivities as $activity)
                            <div class="relative pl-7">
                                <div class="absolute left-0 top-1.5 h-5 w-5 rounded-full border-2 border-white bg-[#c9a227] shadow-sm"></div>
                                <p class="text-xs font-bold leading-5 text-[#0b2f52]">{{ $activity->description ?? $activity->action }}</p>
                                <p class="mt-1 text-[11px] font-bold uppercase tracking-tight text-slate-400">{{ $activity->created_at?->diffForHumans() }}</p>
                            </div>
                        @empty
                            <p class="rounded-lg bg-slate-50 py-4 text-center text-xs font-bold text-slate-400">ไม่มีกิจกรรมล่าสุด</p>
                        @endforelse
                    </div>
                </section>

                <section class="overflow-hidden rounded-lg bg-[#0b2f52] p-5 text-white shadow-xl shadow-[#0b2f52]/15">
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <h3 class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#f3d06f]">ศูนย์แจ้งเตือน</h3>
                        @if ($unreadNotifications > 0)
                            <span class="flex h-7 min-w-7 items-center justify-center rounded-full bg-[#c9a227] px-2 text-[11px] font-extrabold text-[#0b2f52]">{{ $unreadNotifications }}</span>
                        @endif
                    </div>
                    <div class="space-y-3">
                        @forelse($recentNotifications->take(3) as $notification)
                            <div class="rounded-lg border border-white/10 bg-white/10 p-4">
                                <p class="line-clamp-2 text-xs font-semibold leading-5 text-white">{{ $notification->title ?? ($notification->data['message'] ?? '-') }}</p>
                                <p class="mt-2 text-[11px] font-bold uppercase tracking-tight text-[#f3d06f]">{{ $notification->created_at?->diffForHumans() }}</p>
                            </div>
                        @empty
                            <p class="rounded-lg border border-white/10 bg-white/5 py-4 text-center text-xs font-bold text-slate-400">ไม่มีการแจ้งเตือนใหม่</p>
                        @endforelse
                    </div>
                    @if ($recentNotifications->count() > 0)
                        <a href="{{ route('staff.portal.notifications.index') }}" class="mt-5 inline-flex w-full items-center justify-center rounded-lg border border-[#c9a227]/40 px-4 py-3 text-[11px] font-extrabold uppercase tracking-[0.2em] text-[#f3d06f] transition hover:bg-[#c9a227] hover:text-[#0b2f52]">
                            ดูการแจ้งเตือนทั้งหมด
                        </a>
                    @endif
                </section>
            </aside>
        </div>
    </div>
@endsection
