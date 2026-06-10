@extends('layouts.manager', ['title' => 'ทะเบียนใบงาน', 'pageTitle' => 'ติดตามการดำเนินงาน'])

@push('head')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
    .hover-shadow {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .hover-shadow:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
    }
</style>
@endpush

@section('content')
    @php
        $statusLabels = [
            '' => 'ทุกสถานะ',
            'pending' => 'รอเริ่มงาน',
            'processing' => 'กำลังดำเนินการ',
            'waiting_document' => 'รอเอกสาร',
            'approved' => 'อนุมัติแล้ว',
            'completed' => 'เสร็จสิ้น',
            'cancelled' => 'ยกเลิก',
            'rejected' => 'ไม่ผ่าน',
        ];

        $priorityLabels = [
            '' => 'ทุกความสำคัญ',
            'low' => 'ต่ำ',
            'medium' => 'ปานกลาง',
            'high' => 'สูง',
            'urgent' => 'ด่วนพิเศษ',
        ];

        $paymentLabels = [
            '' => 'ทุกสถานะชำระเงิน',
            'pending' => 'รอชำระ',
            'partial' => 'ชำระบางส่วน',
            'paid' => 'ชำระครบ',
            'cancelled' => 'ยกเลิก',
        ];

        $statusClasses = [
            'pending' => 'bg-[#fff9e8] text-[#0b2f52] ring-[#c9a227]/30',
            'processing' => 'bg-[#fff9e8] text-[#0b2f52] ring-[#c9a227]/30',
            'waiting_document' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            'completed' => 'bg-[#0b2f52] text-white',
            'cancelled' => 'bg-slate-100 text-slate-500 ring-slate-400/20',
            'rejected' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        ];

        $priorityClasses = [
            'urgent' => 'bg-rose-600 text-white shadow-sm shadow-rose-200',
            'high' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
            'medium' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            'low' => 'bg-slate-50 text-slate-600 ring-slate-400/10',
        ];

        $paymentClasses = [
            'pending' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            'partial' => 'bg-[#fff9e8] text-[#0b2f52] ring-[#c9a227]/30',
            'paid' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            'cancelled' => 'bg-slate-100 text-slate-500 ring-slate-400/20',
        ];
    @endphp

    <div class="space-y-8">
        <!-- Header Section -->
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-[#0b2f52] italic">ติดตามการดำเนินงาน</h2>
                <p class="mt-1 text-slate-500 text-lg font-medium">ติดตามและบริหารจัดการใบงานทั้งหมดในระบบ</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('manager.pending-approvals.index') }}" 
                    class="flex items-center gap-2 rounded-lg bg-white border border-slate-200 px-6 py-3 text-sm font-bold text-slate-600 hover:text-[#c9a227] transition-all shadow-sm">
                    <i data-lucide="shield-alert" class="h-4 w-4"></i>
                    รายการรออนุมัติ
                </a>
            </div>
        </header>

        <!-- Stats สรุป -->
        <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <article class="glass-card rounded-lg p-6 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">งานที่กำลังดำเนินการ</p>
                <h3 class="text-3xl font-black text-[#0b2f52] mt-2">{{ number_format($summary['open']) }} <span class="text-xs font-medium text-slate-400">งาน</span></h3>
            </article>
            <article class="glass-card rounded-lg p-6 shadow-sm border-amber-100">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">รอเอกสาร</p>
                <h3 class="text-3xl font-black text-amber-600 mt-2">{{ number_format($summary['waiting_document']) }} <span class="text-xs font-medium text-amber-400">รายการ</span></h3>
            </article>
            <article class="glass-card rounded-lg p-6 shadow-sm border-rose-100">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">ยอดค้างชำระ</p>
                <h3 class="text-3xl font-black text-rose-600 mt-2">{{ number_format($summary['unpaid']) }} <span class="text-xs font-medium text-rose-400">ใบแจ้งหนี้</span></h3>
            </article>
            <article class="glass-card rounded-lg p-6 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">ประวัติงานทั้งหมด</p>
                <h3 class="text-3xl font-black text-[#0b2f52] mt-2">{{ number_format($summary['total']) }}</h3>
            </article>
        </section>

        <!-- ค้นหา & กรอง -->
        <section class="glass-card rounded-lg p-8 shadow-sm">
            <form method="GET" class="grid gap-6 xl:grid-cols-[1fr_repeat(3,180px)_auto] xl:items-end">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">ค้นหาใบงาน</label>
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400"></i>
                        <input name="q" type="text" value="{{ $keyword }}"
                            placeholder="เลขงาน, นายจ้าง, แรงงาน..."
                            class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 pl-12 pr-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">ขั้นตอนงาน สถานะ</label>
                    <select name="status" class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all appearance-none">
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">ความสำคัญ</label>
                    <select name="priority" class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all appearance-none text-center">
                        @foreach ($priorityLabels as $value => $label)
                            <option value="{{ $value }}" @selected($priority === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">การชำระเงิน</label>
                    <select name="payment_status" class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all appearance-none text-center">
                        @foreach ($paymentLabels as $value => $label)
                            <option value="{{ $value }}" @selected($paymentสถานะ === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="h-12 px-6 rounded-lg bg-[#0b2f52] text-white hover:bg-[#123e68] transition-all shadow-lg shadow-[#0b2f52]/20">
                        <i data-lucide="filter" class="h-5 w-5"></i>
                    </button>
                    <a href="{{ route('manager.job-orders.index') }}" class="grid h-12 w-12 place-items-center rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition-all">
                        <i data-lucide="refresh-cw" class="h-5 w-5"></i>
                    </a>
                </div>
            </form>
        </section>

        <!-- Data Table -->
        <section class="glass-card overflow-hidden rounded-lg shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/50 px-8 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-black text-[#0b2f52] uppercase tracking-tighter italic">ทะเบียนใบงาน</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">แสดง {{ number_format($jobOrders->total()) }} total pipeline entries</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1200px] text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                            <th class="px-8 py-5">เลขที่ใบงาน</th>
                            <th class="px-4 py-5">นายจ้าง / แรงงาน</th>
                            <th class="px-4 py-5">ขั้นตอนงาน</th>
                            <th class="px-4 py-5">ความสำคัญ</th>
                            <th class="px-4 py-5">การเงิน</th>
                            <th class="px-4 py-5 text-right">คงเหลือ</th>
                            <th class="px-8 py-5 text-right">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($jobOrders as $job)
                            <tr class="group hover:bg-slate-50/80 transition-colors">
                                <td class="px-8 py-5">
                                    <p class="font-black text-[#0b2f52] uppercase tracking-tighter">{{ $job->job_number }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase">กำหนด: {{ $job->due_date?->format('d M Y') ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-5 min-w-[250px]">
                                    <p class="font-bold text-[#0b2f52] truncate">{{ $job->employer?->company_name ?? '-' }}</p>
                                    <p class="text-[10px] font-black text-slate-400 uppercase mt-0.5 truncate">{{ $job->worker?->full_name_th ?: '-' }}</p>
                                </td>
                                <td class="px-4 py-5">
                                    <span @class([
                                        'rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset inline-block min-w-[100px] text-center',
                                        $statusClasses[$job->status] ?? 'bg-slate-50 text-slate-500'
                                    ])>
                                        {{ $statusLabels[$job->status] ?? $job->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-5">
                                    <span @class([
                                        'rounded-lg px-2.5 py-1 text-[10px] font-black uppercase tracking-wider inline-block min-w-[80px] text-center',
                                        $priorityClasses[$job->priority] ?? 'bg-slate-100 text-slate-500'
                                    ])>
                                        {{ $priorityLabels[$job->priority] ?? $job->priority }}
                                    </span>
                                </td>
                                <td class="px-4 py-5">
                                    <span @class([
                                        'rounded-lg px-2.5 py-1 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset inline-block min-w-[90px] text-center',
                                        $paymentClasses[$job->payment_status] ?? 'bg-slate-50 text-slate-500'
                                    ])>
                                        {{ $paymentLabels[$job->payment_status] ?? $job->payment_status }}
                                    </span>
                                </td>
                                <td class="px-4 py-5 text-right">
                                    <p @class([
                                        'font-black text-sm italic',
                                        'text-rose-600' => $job->getRemainingAmount() > 0,
                                        'text-[#0b2f52]' => $job->getRemainingAmount() <= 0,
                                    ])>฿{{ number_format($job->getRemainingAmount(), 2) }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">รวม: {{ number_format((float)$job->service_fee, 0) }}</p>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <a href="{{ route('manager.job-orders.show', $job) }}"
                                        class="grid h-9 w-9 place-items-center rounded-lg bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-[#c9a227] hover:border-[#c9a227]/40 transition-all opacity-0 group-hover:opacity-100 ml-auto" title="Executive Job สรุป">
                                        <i data-lucide="chevron-right" class="h-5 w-5"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-8 py-20 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-lg bg-slate-50 text-slate-300">
                                        <i data-lucide="briefcase" class="h-8 w-8"></i>
                                    </div>
                                    <h3 class="mt-4 text-lg font-bold text-[#0b2f52] italic">ไม่พบใบงาน</h3>
                                    <p class="text-slate-500">ลองปรับเงื่อนไขการค้นหาเพื่อดูข้อมูลอื่น</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($jobOrders->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/50 px-8 py-4">
                    {{ $jobOrders->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
