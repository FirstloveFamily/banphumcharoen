@extends('layouts.staff-portal', ['title' => 'รายการใบงาน', 'pageTitle' => 'จัดการใบงาน (Job Orders)'])

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
        $statusLabels = ['' => 'ทุกสถานะงาน'] + $jobOrderStatuses->pluck('name_th', 'code')->all();

        $priorityLabels = [
            '' => 'ทุกระดับความสำคัญ',
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
        $priorityClasses = [
            'urgent' => 'bg-rose-600 text-white shadow-sm shadow-rose-200',
            'high' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
            'medium' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            'low' => 'bg-slate-50 text-slate-600 ring-slate-400/10',
        ];
    @endphp

    <div class="space-y-8">
        <!-- Header Section -->
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">ใบงานทั้งหมด</h2>
                <p class="mt-1 text-slate-500 text-lg">ติดตามและบริหารจัดการใบงานบริการจากทุกต้นสังกัด</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden sm:block text-right">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Total Active Jobs</p>
                    <p class="text-2xl font-black text-slate-900">{{ number_format($summary['open']) }}</p>
                </div>
                <a href="{{ route('staff.portal.delivery-sheets.index') }}"
                    class="flex items-center gap-2 rounded-2xl bg-white border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700 shadow-sm hover:border-[#0b2f52]/20 hover:text-[#0b2f52] transition-all">
                    <i data-lucide="package-check" class="h-4 w-4"></i>
                    ใบส่งงาน
                </a>
                <a href="{{ route('staff.portal.job-orders.export', request()->query()) }}"
                    class="flex items-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 hover:bg-emerald-700 transition-all">
                    <i data-lucide="file-spreadsheet" class="h-4 w-4"></i>
                    Export Excel
                </a>
                <a href="{{ route('staff.portal.job-orders.create') }}"
                    class="flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#0b2f52] via-[#123e68] to-[#b91c1c] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-[#0b2f52]/20 transition hover:opacity-95">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    เพิ่มงาน
                </a>
            </div>
        </header>

        <!-- Stats Grid -->
        <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <article class="glass-card hover-shadow rounded-3xl p-6 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-blue-50 opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">ใบงานทั้งหมด</p>
                    <h3 class="text-3xl font-black text-slate-900 mt-2">{{ number_format($summary['total']) }}</h3>
                </div>
            </article>

            <article class="glass-card hover-shadow rounded-3xl p-6 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-indigo-50 opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">เปิดดำเนินการอยู่</p>
                    <h3 class="text-3xl font-black text-indigo-600 mt-2">{{ number_format($summary['open']) }}</h3>
                </div>
            </article>

            <article class="glass-card hover-shadow rounded-3xl p-6 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-amber-50 opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">รอรับเอกสาร</p>
                    <h3 class="text-3xl font-black text-amber-600 mt-2">{{ number_format($summary['waiting_document']) }}</h3>
                </div>
            </article>

            <article class="glass-card hover-shadow rounded-3xl p-6 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-rose-50 opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">ค้างชำระเงิน</p>
                    <h3 class="text-3xl font-black text-rose-600 mt-2">{{ number_format($summary['unpaid']) }}</h3>
                </div>
            </article>
        </section>

        <!-- Search & Filter -->
        <section class="glass-card rounded-3xl p-6 shadow-sm">
            <form method="GET" class="grid gap-6 lg:grid-cols-[1fr_repeat(4,200px)_auto]">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400"></i>
                    <input type="text" name="q" value="{{ $keyword }}" 
                        placeholder="เลขงาน, บริษัท, หรือชื่อแรงงาน..." 
                        class="w-full h-12 rounded-2xl border border-slate-100 bg-slate-50/50 pl-12 pr-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all">
                </div>
                <div>
                    <select name="status" class="w-full h-12 rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all appearance-none">
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="priority" class="w-full h-12 rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all appearance-none">
                        @foreach ($priorityLabels as $value => $label)
                            <option value="{{ $value }}" @selected($priority === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="payment_status" class="w-full h-12 rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all appearance-none">
                        @foreach ($paymentLabels as $value => $label)
                            <option value="{{ $value }}" @selected($paymentStatus === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="sort" class="w-full h-12 rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all appearance-none">
                        @foreach ($sortOptions as $value => $label)
                            <option value="{{ $value }}" @selected($sort === $value)>เรียงตาม{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="h-12 px-8 rounded-2xl bg-slate-900 text-sm font-bold text-white hover:bg-slate-800 transition-all">
                        กรองข้อมูล
                    </button>
                    <a href="{{ route('staff.portal.job-orders.index') }}" class="grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-500 hover:bg-slate-200 transition-all">
                        <i data-lucide="refresh-cw" class="h-5 w-5"></i>
                    </a>
                </div>
            </form>
        </section>

        <!-- Data Table -->
        <section class="glass-card overflow-hidden rounded-3xl shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm min-w-[1200px]">
                    <thead>
                        <tr class="bg-slate-50/50 text-xs font-bold uppercase tracking-wider text-slate-400">
                            <th class="px-8 py-5">เลขใบงาน / กำหนดเสร็จ</th>
                            <th class="px-4 py-5">แรงงาน / นายจ้าง</th>
                            <th class="px-4 py-5">ประเภทบริการ</th>
                            <th class="px-4 py-5 text-center">สถานะ</th>
                            <th class="px-4 py-5 text-center">ความสำคัญ</th>
                            <th class="px-4 py-5 text-right">ยอดคงเหลือ</th>
                            <th class="px-8 py-5 text-right">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($jobOrders as $job)
                            <tr class="group hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-5">
                                    <p class="font-black text-blue-600 uppercase tracking-tighter">{{ $job->job_number }}</p>
                                    <p class="mt-1 text-xs font-bold text-slate-400">Due: {{ $job->due_date?->format('d/m/Y') ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="font-bold text-slate-900 truncate">{{ $job->worker?->full_name_th ?: '-' }}</p>
                                    <p class="text-xs font-medium text-slate-500 truncate max-w-[200px]">{{ $job->employer?->company_name ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-5">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1 font-semibold text-slate-700">
                                        <i data-lucide="settings-2" class="h-3.5 w-3.5"></i>
                                        {{ $job->service?->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-5 text-center">
                                    <span @class([
                                        'rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset inline-block min-w-[110px]',
                                        $job->status_badge_class
                                    ])>
                                        {{ $job->status_label }}
                                    </span>
                                    @if($job->pending_documents_count || $job->pending_payments_count)
                                        <div class="mt-1 flex items-center justify-center gap-1.5 text-[10px] font-bold text-rose-500">
                                            @if($job->pending_documents_count) <i data-lucide="file-warning" class="h-3 w-3"></i> @endif
                                            @if($job->pending_payments_count) <i data-lucide="receipt" class="h-3 w-3"></i> @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-5 text-center">
                                    <span @class([
                                        'rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wider inline-block min-w-[80px]',
                                        $priorityClasses[$job->priority] ?? 'bg-slate-100 text-slate-500'
                                    ])>
                                        {{ $priorityLabels[$job->priority] ?? $job->priority }}
                                    </span>
                                </td>
                                <td class="px-4 py-5 text-right">
                                    <p @class([
                                        'font-black text-sm',
                                        'text-rose-600' => $job->getRemainingAmount() > 0,
                                        'text-slate-900' => $job->getRemainingAmount() <= 0,
                                    ])>฿{{ number_format($job->getRemainingAmount(), 2) }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Total: {{ number_format((float)$job->service_fee, 0) }}</p>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <a href="{{ route('staff.portal.job-orders.show', $job) }}" 
                                        class="grid h-9 w-9 place-items-center rounded-xl bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-blue-600 hover:border-blue-100 transition-all opacity-0 group-hover:opacity-100 mx-auto lg:ml-auto lg:mr-0">
                                        <i data-lucide="chevron-right" class="h-5 w-5"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-8 py-20 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-50 text-slate-300">
                                        <i data-lucide="clipboard-x" class="h-8 w-8"></i>
                                    </div>
                                    <h3 class="mt-4 text-lg font-bold text-slate-900">ไม่พบข้อมูลใบงาน</h3>
                                    <p class="text-slate-500">ลองใช้คำค้นหาอื่น หรือเปลี่ยนตัวกรอง</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($jobOrders->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/50 px-8 py-4">
                    {{ $jobOrders->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
