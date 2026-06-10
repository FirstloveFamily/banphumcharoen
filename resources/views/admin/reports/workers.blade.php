@extends('layouts.manager', ['title' => 'รายงานแรงงาน', 'pageTitle' => 'Workforce Analytics'])

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
        $withinDays = (int) request('within_days', 30);
        $dateFrom = request('date_from');
        $dateTo = request('date_to');
        $search = request('search');
        $employerId = request('employer_id');
    @endphp

    <div class="space-y-8">
        <!-- Header Section -->
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-[#0b2f52] italic">รายงานแรงงาน</h2>
                <p class="mt-1 text-slate-500 text-lg font-medium">ภาพรวมกำลังแรงงานและสถานะเอกสารสำคัญรายบุคคล</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('manager.reports.workers.export.csv') }}?{{ http_build_query(request()->only(['date_from', 'date_to', 'within_days', 'search', 'employer_id'])) }}"
                    class="flex items-center gap-2 rounded-lg bg-white border border-slate-200 px-5 py-2.5 text-xs font-bold text-slate-600 hover:text-[#c9a227] transition-all shadow-sm">
                    <i data-lucide="download-cloud" class="h-4 w-4"></i>
                    ส่งออก CSV
                </a>
                <a href="{{ route('manager.reports.workers.export.pdf') }}?{{ http_build_query(request()->only(['date_from', 'date_to', 'within_days', 'search', 'employer_id'])) }}"
                    class="flex items-center gap-2 rounded-lg bg-[#0b2f52] px-5 py-2.5 text-xs font-bold text-white hover:bg-[#123e68] transition-all shadow-lg">
                    <i data-lucide="printer" class="h-4 w-4"></i>
                    Generate รายงาน
                </a>
            </div>
        </header>

        <!-- Stats Grid -->
        <section class="grid gap-6 sm:grid-cols-3">
            @foreach ($stats as $stat)
                <article class="glass-card hover-shadow rounded-lg p-6 relative overflow-hidden group">
                    <div @class([
                        'absolute -right-4 -top-4 h-20 w-20 rounded-full opacity-50 group-hover:scale-110 transition-transform',
                        'bg-[#fff9e8]' => $stat['tone'] === 'blue',
                        'bg-rose-50' => $stat['tone'] === 'rose',
                        'bg-amber-50' => $stat['tone'] === 'amber',
                    ])></div>
                    <div class="relative flex items-center gap-5">
                        <div @class([
                            'flex h-14 w-14 shrink-0 items-center justify-center rounded-lg',
                            'bg-[#fff9e8] text-[#0b2f52]' => $stat['tone'] === 'blue',
                            'bg-rose-50 text-rose-600' => $stat['tone'] === 'rose',
                            'bg-amber-50 text-amber-600' => $stat['tone'] === 'amber',
                        ])>
                            <i data-lucide="{{ $stat['icon'] }}" class="h-7 w-7"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ $stat['label'] }}</p>
                            <h3 @class([
                                'text-2xl font-black',
                                'text-[#0b2f52]' => $stat['tone'] === 'blue',
                                'text-rose-600' => $stat['tone'] === 'rose',
                                'text-amber-600' => $stat['tone'] === 'amber',
                            ])>{{ number_format($stat['value']) }}</h3>
                            <p class="text-[10px] font-bold text-slate-400 mt-0.5">{{ $stat['description'] }}</p>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        <!-- ค้นหา & กรอง -->
        <section class="glass-card rounded-lg p-8 shadow-sm">
            <form method="GET" class="grid gap-6 xl:grid-cols-[1fr_repeat(3,180px)_auto] xl:items-end">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">แรงงาน ค้นหา</label>
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400"></i>
                        <input name="search" type="text" value="{{ $search }}"
                            placeholder="ชื่อ, พาสปอร์ต, WP..."
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
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">Expiry Window (Days)</label>
                    <input type="number" name="within_days" min="1" max="365" value="{{ $withinDays }}"
                        class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all text-center">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="h-12 px-6 rounded-lg bg-[#0b2f52] text-white hover:bg-[#123e68] transition-all shadow-lg shadow-[#0b2f52]/20">
                        <i data-lucide="filter" class="h-5 w-5"></i>
                    </button>
                    <a href="{{ route('manager.reports.workers') }}" class="grid h-12 w-12 place-items-center rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition-all">
                        <i data-lucide="refresh-cw" class="h-5 w-5"></i>
                    </a>
                </div>
            </form>
        </section>

        <!-- Data Table -->
        <section class="glass-card overflow-hidden rounded-lg shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/50 px-8 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-black text-[#0b2f52] uppercase tracking-tighter italic">ทะเบียนแรงงาน</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Detailed เอกสาร Tracking</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1200px] text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                            <th class="px-8 py-5">แรงงาน / พาสปอร์ต</th>
                            <th class="px-4 py-5">นายจ้าง</th>
                            <th class="px-4 py-5">พาสปอร์ตหมดอายุ</th>
                            <th class="px-4 py-5">ใบอนุญาตทำงานหมดอายุ</th>
                            <th class="px-4 py-5">วีซ่าหมดอายุ</th>
                            <th class="px-4 py-5">90 Days</th>
                            <th class="px-8 py-5 text-right">Earliest สถานะ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($workers as $worker)
                            @php
                                $dates = collect([
                                    $worker->passport_expiry,
                                    $worker->wp_expiry,
                                    $worker->visa_expiry,
                                    $worker->report_90_days_due,
                                ])->filter()->sort()->values();
                                $earliestExpiry = $dates->first();
                                $isExpired = $earliestExpiry ? $earliestExpiry->isPast() : false;
                                $daysคงเหลือ = $earliestExpiry ? now()->startOfDay()->diffInDays($earliestExpiry->copy()->startOfDay(), false) : null;
                                
                                $getExpiryColor = function($date) {
                                    if (!$date) return 'text-slate-300';
                                    $d = now()->startOfDay()->diffInDays($date->copy()->startOfDay(), false);
                                    if ($d < 0) return 'text-rose-600 font-bold';
                                    if ($d <= 30) return 'text-amber-600 font-bold';
                                    return 'text-slate-700 font-semibold';
                                };
                            @endphp
                            <tr class="group hover:bg-slate-50/80 transition-colors">
                                <td class="px-8 py-5">
                                    <p class="font-bold text-[#0b2f52] truncate">{{ $worker->full_name_th ?: $worker->full_name_en }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase mt-0.5 font-mono">{{ $worker->passport_number ?: 'NO PP' }}</p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-xs font-bold text-slate-500 truncate max-w-[150px]">{{ $worker->employer?->company_name ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-5 text-xs {{ $getExpiryColor($worker->passport_expiry) }}">
                                    {{ $worker->passport_expiry?->format('d M Y') ?? '-' }}
                                </td>
                                <td class="px-4 py-5 text-xs {{ $getExpiryColor($worker->wp_expiry) }}">
                                    {{ $worker->wp_expiry?->format('d M Y') ?? '-' }}
                                </td>
                                <td class="px-4 py-5 text-xs {{ $getExpiryColor($worker->visa_expiry) }}">
                                    {{ $worker->visa_expiry?->format('d M Y') ?? '-' }}
                                </td>
                                <td class="px-4 py-5 text-xs {{ $getExpiryColor($worker->report_90_days_due) }}">
                                    {{ $worker->report_90_days_due?->format('d M Y') ?? '-' }}
                                </td>
                                <td class="px-8 py-5 text-right">
                                    @if ($earliestExpiry)
                                        <span @class([
                                            'rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset inline-block min-w-[110px] text-center',
                                            'bg-rose-600 text-white shadow-sm shadow-rose-200' => $isExpired,
                                            'bg-amber-50 text-amber-600 ring-amber-500/20' => !$isExpired && $daysคงเหลือ <= 14,
                                            'bg-[#fff9e8] text-[#0b2f52] ring-[#c9a227]/30' => !$isExpired && $daysคงเหลือ > 14,
                                        ])>
                                            {{ $isExpired ? 'เกินกำหนด' : $daysคงเหลือ . ' วันคงเหลือ' }}
                                        </span>
                                    @else
                                        <span class="text-slate-300 italic text-xs font-medium">ไม่ Data</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-8 py-14 text-center text-slate-400">
                                    ไม่ worker data found for the current filter
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($workers->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/50 px-8 py-4">
                    {{ $workers->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
