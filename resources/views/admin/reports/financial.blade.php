@extends('layouts.manager', ['title' => 'รายงานการเงิน', 'pageTitle' => 'รายงานการเงินing'])

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
        $dateFrom = request('date_from');
        $dateTo = request('date_to');
        $employerId = request('employer_id');
        $search = request('search');
        $status = request('status');
        $statusLabels = [
            '' => 'ทุกสถานะตรวจสอบ',
            'pending' => 'รอตรวจสอบ',
            'verified' => 'ยืนยันยอดแล้ว',
            'rejected' => 'ถูกปฏิเสธ',
        ];
        $statusClasses = [
            'pending' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            'verified' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            'rejected' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        ];
    @endphp

    <div class="space-y-8">
        <!-- Header Section -->
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-[#0b2f52] italic">รายงานการเงิน</h2>
                <p class="mt-1 text-slate-500 text-lg font-medium">วิเคราะห์กระแสเงินสดและการรับชำระเงินขององค์กร</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('manager.reports.financial.export.csv', request()->only(['date_from', 'date_to', 'employer_id', 'search', 'status'])) }}"
                    class="flex items-center gap-2 rounded-lg bg-white border border-slate-200 px-5 py-2.5 text-xs font-bold text-slate-600 hover:text-[#c9a227] transition-all shadow-sm">
                    <i data-lucide="download-cloud" class="h-4 w-4"></i>
                    ส่งออก CSV
                </a>
                <a href="{{ route('manager.reports.financial.export.pdf', request()->only(['date_from', 'date_to', 'employer_id', 'search', 'status'])) }}"
                    class="flex items-center gap-2 rounded-lg bg-[#0b2f52] px-5 py-2.5 text-xs font-bold text-white hover:bg-[#123e68] transition-all shadow-lg shadow-slate-900/10">
                    <i data-lucide="file-text" class="h-4 w-4"></i>
                    Generate PDF
                </a>
            </div>
        </header>

        <!-- Stats Grid -->
        <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <article class="glass-card hover-shadow rounded-lg p-6 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-[#fff9e8] opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative flex items-center gap-5">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-[#fff9e8] text-[#0b2f52]">
                        <i data-lucide="banknote" class="h-7 w-7"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">รวมรับชำระทั้งหมด</p>
                        <h3 class="text-2xl font-black text-[#0b2f52]">฿{{ number_format($totalPayments, 2) }}</h3>
                    </div>
                </div>
            </article>

            <article class="glass-card hover-shadow rounded-lg p-6 relative overflow-hidden group border-emerald-100">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-emerald-50 opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative flex items-center gap-5">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                        <i data-lucide="check-circle" class="h-7 w-7"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">ตรวจสอบแล้ว</p>
                        <h3 class="text-2xl font-black text-emerald-600">฿{{ number_format($verifiedTotal, 2) }}</h3>
                    </div>
                </div>
            </article>

            <article class="glass-card hover-shadow rounded-lg p-6 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-amber-50 opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative flex items-center gap-5">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                        <i data-lucide="help-circle" class="h-7 w-7"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">รอดำเนินการ / ไม่ผ่าน</p>
                        <h3 class="text-2xl font-black text-amber-600">฿{{ number_format($pendingTotal, 2) }}</h3>
                    </div>
                </div>
            </article>

            <article class="rounded-lg p-6 relative overflow-hidden group bg-[#0b2f52] shadow-xl shadow-slate-900/20">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10 opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative flex items-center gap-5">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-white/10 text-white">
                        <i data-lucide="alert-octagon" class="h-7 w-7"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">รายการค้างตรวจ</p>
                        <h3 class="text-2xl font-black text-white">{{ number_format($pendingCount ?? 0) }} รายการ</h3>
                    </div>
                </div>
            </article>
        </section>

        <!-- ค้นหา & กรอง -->
        <section class="glass-card rounded-lg p-8 shadow-sm">
            <form action="{{ route('manager.reports.financial') }}" method="GET" class="grid gap-6 xl:grid-cols-[1fr_repeat(3,180px)_120px_auto] xl:items-end">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">Keywords</label>
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400"></i>
                        <input name="search" type="text" value="{{ $search }}"
                            placeholder="Job ID, เลขอ้างอิง..."
                            class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 pl-12 pr-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">วันที่เริ่มต้น</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                        class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">วันที่สิ้นสุด</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                        class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">นายจ้าง</label>
                    <select name="employer_id" class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all appearance-none">
                        <option value="">ทั้งหมด ลูกค้า</option>
                        @foreach ($employers as $id => $name)
                            <option value="{{ $id }}" @selected((string) $employerId === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">จำนวนต่อหน้า</label>
                    <select name="per_page" class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all appearance-none text-center">
                        @foreach ([20, 50, 100] as $size)
                            <option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="h-12 px-6 rounded-lg bg-[#0b2f52] text-white hover:bg-[#123e68] transition-all shadow-lg shadow-[#0b2f52]/20">
                        <i data-lucide="filter" class="h-5 w-5"></i>
                    </button>
                    <a href="{{ route('manager.reports.financial') }}" class="grid h-12 w-12 place-items-center rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition-all">
                        <i data-lucide="refresh-cw" class="h-5 w-5"></i>
                    </a>
                </div>
            </form>
        </section>

        <!-- Data Table -->
        <section class="glass-card overflow-hidden rounded-lg shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/50 px-8 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-black text-[#0b2f52] uppercase tracking-tighter italic">การชำระเงิน ทะเบียน</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">แสดง {{ number_format($payments->total()) }} total records</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                            <th class="px-8 py-5">การชำระเงิน วันที่</th>
                            <th class="px-4 py-5">เลขที่ใบงาน</th>
                            <th class="px-4 py-5">ลูกค้า / แรงงาน</th>
                            <th class="px-4 py-5 text-right">จำนวนเงิน</th>
                            <th class="px-4 py-5">Method</th>
                            <th class="px-8 py-5 text-right">Verification</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($payments as $payment)
                            <tr class="group hover:bg-slate-50/80 transition-colors">
                                <td class="px-8 py-5 font-bold text-slate-700">
                                    {{ $payment->payment_date?->format('d M Y') ?? '-' }}
                                </td>
                                <td class="px-4 py-5">
                                    <a href="{{ $payment->jobOrder ? route('staff.portal.job-orders.show', $payment->jobOrder) : '#' }}" target="_blank"
                                        class="font-black text-[#0b2f52] uppercase tracking-tighter hover:underline">
                                        {{ $payment->jobOrder?->job_number ?? '-' }}
                                    </a>
                                </td>
                                <td class="px-4 py-5 min-w-[280px]">
                                    <p class="font-bold text-[#0b2f52] truncate">{{ $payment->jobOrder?->employer?->company_name ?? '-' }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase mt-0.5">{{ $payment->jobOrder?->worker?->full_name_th ?: '-' }}</p>
                                </td>
                                <td class="px-4 py-5 text-right font-black text-[#0b2f52] text-base italic">
                                    ฿{{ number_format((float) $payment->amount, 2) }}
                                </td>
                                <td class="px-4 py-5">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2 py-1 text-[10px] font-black uppercase text-slate-600">
                                        <i data-lucide="credit-card" class="h-3 w-3"></i>
                                        {{ str($payment->payment_method)->replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <span @class([
                                        'rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset inline-block min-w-[110px] text-center',
                                        $statusClasses[$payment->status] ?? 'bg-slate-100 text-slate-500'
                                    ])>
                                        {{ $statusLabels[$payment->status] ?? $payment->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-20 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-lg bg-slate-50 text-slate-300">
                                        <i data-lucide="database" class="h-8 w-8"></i>
                                    </div>
                                    <h3 class="mt-4 text-lg font-bold text-[#0b2f52]">ไม่ payment data found</h3>
                                    <p class="text-slate-500">Try adjusting your filters or date range</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($payments->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/50 px-8 py-4">
                    {{ $payments->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
