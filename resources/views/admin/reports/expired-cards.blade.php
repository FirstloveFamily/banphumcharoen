@extends('layouts.manager', ['title' => 'ตรวจเอกสารหมดอายุ', 'pageTitle' => 'แรงงาน Compliance รายงาน'])

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

        $statusClasses = [
            'received' => 'bg-[#fff9e8] text-[#0b2f52] ring-[#c9a227]/30',
            'missing' => 'bg-amber-50 text-amber-600 ring-amber-500/20',
            'rejected' => 'bg-rose-50 text-rose-600 ring-rose-500/20',
            'pending' => 'bg-slate-50 text-slate-500 ring-slate-400/20',
        ];
    @endphp

    <div class="space-y-8">
        <!-- Header Section -->
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-[#0b2f52] italic">ตรวจเอกสารหมดอายุ</h2>
                <p class="mt-1 text-slate-500 text-lg font-medium">ติดตามสถานะความถูกต้องของเอกสารแรงงานเพื่อการกำกับดูแล</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ url('/manager/reports/expired-cards/export/csv') }}?{{ http_build_query(request()->only(['date_from', 'date_to', 'within_days', 'search'])) }}"
                    class="flex items-center gap-2 rounded-lg bg-white border border-slate-200 px-5 py-2.5 text-xs font-bold text-slate-600 hover:text-[#c9a227] transition-all shadow-sm">
                    <i data-lucide="download-cloud" class="h-4 w-4"></i>
                    ส่งออก CSV
                </a>
                <a href="{{ url('/manager/reports/expired-cards/export/pdf') }}?{{ http_build_query(request()->only(['date_from', 'date_to', 'within_days', 'search'])) }}"
                    class="flex items-center gap-2 rounded-lg bg-[#0b2f52] px-5 py-2.5 text-xs font-bold text-white hover:bg-[#123e68] transition-all shadow-lg">
                    <i data-lucide="printer" class="h-4 w-4"></i>
                    Generate รายงาน
                </a>
            </div>
        </header>

        <!-- Stats Grid -->
        <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <article class="glass-card hover-shadow rounded-lg p-6 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-[#fff9e8] opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative flex items-center gap-5">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-[#fff9e8] text-[#0b2f52]">
                        <i data-lucide="calendar" class="h-7 w-7"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">รายการแจ้งเตือนทั้งหมด</p>
                        <h3 class="text-2xl font-black text-[#0b2f52]">{{ number_format($totalCount) }} <span class="text-xs font-medium text-slate-400">รายการ</span></h3>
                    </div>
                </div>
            </article>

            <article class="glass-card hover-shadow rounded-lg p-6 relative overflow-hidden group border-rose-100">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-rose-50 opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative flex items-center gap-5">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
                        <i data-lucide="alert-circle" class="h-7 w-7"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">หมดอายุแล้ว (เกินกำหนด)</p>
                        <h3 class="text-2xl font-black text-rose-600">{{ number_format($expiredCount) }} <span class="text-xs font-medium text-rose-400">รายการ</span></h3>
                    </div>
                </div>
            </article>

            <article class="glass-card hover-shadow rounded-lg p-6 relative overflow-hidden group border-amber-100">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-amber-50 opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative flex items-center gap-5">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                        <i data-lucide="clock" class="h-7 w-7"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">ใกล้หมดอายุ (Expiring)</p>
                        <h3 class="text-2xl font-black text-amber-600">{{ number_format($nearCount) }} <span class="text-xs font-medium text-amber-400">ใน {{ $withinDays }} วัน</span></h3>
                    </div>
                </div>
            </article>

            <article class="rounded-lg p-6 relative overflow-hidden group bg-[#0b2f52] shadow-xl">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10 opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative flex items-center gap-5">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-white/10 text-white">
                        <i data-lucide="shield-check" class="h-7 w-7"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">รอตรวจเช็ควันนี้</p>
                        <h3 class="text-2xl font-black text-white">{{ number_format($pendingReviews->count() + $pendingPayments->count()) }} <span class="text-xs font-medium text-slate-400">งาน</span></h3>
                    </div>
                </div>
            </article>
        </section>

        <!-- ค้นหา & กรอง -->
        <section class="glass-card rounded-lg p-8 shadow-sm">
            <form method="GET" class="grid gap-6 xl:grid-cols-[1fr_repeat(3,180px)_120px_auto] xl:items-end">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">ค้นหา แรงงาน / Docs</label>
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400"></i>
                        <input name="search" type="text" value="{{ $search }}"
                            placeholder="ชื่อแรงงาน, นายจ้าง..."
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
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">ภายในกี่วัน</label>
                    <input type="number" name="within_days" min="1" max="365" value="{{ $withinDays }}"
                        class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all text-center">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">จำนวนต่อหน้า</label>
                    <select name="per_page" class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all appearance-none text-center">
                        @foreach ([15, 30, 50, 100] as $size)
                            <option value="{{ $size }}" @selected((int) request('per_page', 15) === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="h-12 px-6 rounded-lg bg-[#0b2f52] text-white hover:bg-[#123e68] transition-all shadow-lg shadow-[#0b2f52]/20">
                        <i data-lucide="filter" class="h-5 w-5"></i>
                    </button>
                    <a href="{{ route('manager.reports.expired_cards') }}" class="grid h-12 w-12 place-items-center rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition-all">
                        <i data-lucide="refresh-cw" class="h-5 w-5"></i>
                    </a>
                </div>
            </form>
        </section>

        <!-- Main Content Area -->
        <div class="grid gap-8 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-8">
                <!-- Expiry ทะเบียน -->
                <section class="glass-card overflow-hidden rounded-lg shadow-sm">
                    <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/50 px-8 py-5 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="text-lg font-black text-[#0b2f52] uppercase tracking-tighter italic">ทะเบียนติดตามเอกสาร</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm min-w-[900px]">
                            <thead>
                                <tr class="bg-slate-50/50 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                                    <th class="px-8 py-5">แรงงาน</th>
                                    <th class="px-4 py-5">ลูกค้า</th>
                                    <th class="px-4 py-5">เอกสาร</th>
                                    <th class="px-4 py-5">เลขอ้างอิง</th>
                                    <th class="px-4 py-5 text-center">วันหมดอายุ</th>
                                    <th class="px-8 py-5 text-right">คงเหลือ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($expiringItems as $item)
                                    @php
                                        $expiryวันที่ = $item['expiry_date'] instanceof \Carbon\Carbon ? $item['expiry_date'] : \Carbon\Carbon::parse($item['expiry_date']);
                                        $expired = $expiryวันที่->isPast();
                                        $days = now()->startOfDay()->diffInDays($expiryวันที่->copy()->startOfDay(), false);
                                        $reference = $item['passport_number'] ?: ($item['wp_number'] ?: '-');
                                    @endphp
                                    <tr class="group hover:bg-slate-50/80 transition-colors">
                                        <td class="px-8 py-5">
                                            <div class="flex items-center gap-3">
                                                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-slate-100 text-[10px] font-black text-slate-400">
                                                    {{ mb_substr($item['worker'], 0, 1) }}
                                                </span>
                                                <span class="font-bold text-[#0b2f52] truncate">{{ $item['worker'] }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-5">
                                            <p class="text-xs font-bold text-slate-500 truncate max-w-[150px]">{{ $item['employer'] }}</p>
                                        </td>
                                        <td class="px-4 py-5">
                                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2 py-1 text-[10px] font-black uppercase text-slate-600">
                                                <i data-lucide="file-text" class="h-3 w-3"></i>
                                                {{ $item['document'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-5 font-mono text-xs font-bold text-slate-400">{{ $reference }}</td>
                                        <td @class([
                                            'px-4 py-5 text-center font-bold text-sm italic',
                                            'text-rose-600' => $expired,
                                            'text-[#0b2f52]' => !$expired,
                                        ])>
                                            {{ $expiryวันที่->format('d M Y') }}
                                        </td>
                                        <td class="px-8 py-5 text-right">
                                            @if($expired)
                                                <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider bg-rose-600 text-white shadow-sm shadow-rose-200">เกินกำหนด</span>
                                            @else
                                                <span @class([
                                                    'rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset',
                                                    'bg-amber-50 text-amber-600 ring-amber-500/20' => $days <= 14,
                                                    'bg-[#fff9e8] text-[#0b2f52] ring-[#c9a227]/30' => $days > 14,
                                                ])>{{ $days }} วันคงเหลือ</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-8 py-20 text-center">
                                            <p class="text-slate-400 italic">ไม่พบรายการเอกสารตามเงื่อนไขที่เลือก</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($expiringItems->hasPages())
                        <div class="border-t border-slate-100 bg-slate-50/50 px-8 py-4">
                            {{ $expiringItems->links() }}
                        </div>
                    @endif
                </section>
            </div>

            <aside class="space-y-8">
                <!-- Related Job Orders Widget -->
                <section class="glass-card rounded-lg overflow-hidden shadow-sm">
                    <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                        <h3 class="font-black text-[#0b2f52] uppercase tracking-widest text-xs">ใบงานที่เกี่ยวข้อง</h3>
                        <span class="h-6 px-2.5 rounded-full bg-[#0b2f52] text-white text-[10px] font-black flex items-center">{{ $openJobs->count() }}</span>
                    </div>
                    <div class="p-6 space-y-4">
                        @forelse ($openJobs->take(5) as $job)
                            <a href="{{ route('staff.portal.job-orders.show', $job) }}" class="block p-4 rounded-lg bg-slate-50/50 border border-slate-100 hover:border-[#c9a227]/40 transition-all group">
                                <div class="flex justify-between รายการ-start">
                                    <div class="min-w-0">
                                        <p class="font-black text-[#0b2f52] text-[10px] uppercase tracking-tighter">{{ $job->job_number }}</p>
                                        <p class="mt-1 font-bold text-[#0b2f52] text-sm truncate">{{ $job->worker?->full_name_th }}</p>
                                    </div>
                                    <i data-lucide="chevron-right" class="h-4 w-4 text-slate-300 group-hover:translate-x-1 transition-all"></i>
                                </div>
                                <div class="mt-3 flex items-center gap-2">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    <span class="text-[10px] font-bold text-slate-500 uppercase">{{ $job->status }}</span>
                                </div>
                            </a>
                        @empty
                            <p class="text-center py-4 text-xs font-bold text-slate-300 italic uppercase">ไม่ active งาน</p>
                        @endforelse
                    </div>
                </section>

                <!-- ด่วน ตรวจสอบs Widget -->
                <section class="rounded-lg bg-[#0b2f52] p-8 text-white shadow-xl shadow-slate-900/20 relative overflow-hidden">
                    <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="relative z-10 flex items-center justify-between mb-6">
                        <h3 class="text-xs font-black uppercase tracking-[0.2em] italic">รายการรอตรวจs</h3>
                        <span class="h-6 px-2 rounded-lg bg-white/10 text-[10px] font-black flex items-center">{{ $pendingReviews->count() }}</span>
                    </div>
                    <div class="space-y-4 relative z-10">
                        @foreach ($pendingReviews->take(3) as $review)
                            <div class="p-4 rounded-lg bg-white/10 border border-white/10">
                                <p class="text-xs font-bold leading-relaxed line-clamp-1 italic">{{ $review->documentMaster?->name }}</p>
                                <p class="mt-2 text-[9px] font-black text-slate-400 uppercase tracking-tighter">{{ $review->jobOrder?->job_number }} · {{ $review->updated_at->diffForHumans() }}</p>
                            </div>
                        @endforeach
                        <a href="{{ route('manager.pending-approvals.index') }}" class="mt-4 block text-center text-[10px] font-black uppercase tracking-[0.3em] text-[#c9a227] hover:text-white transition-colors">
                            Manage ทั้งหมด ตรวจสอบs →
                        </a>
                    </div>
                </section>
            </aside>
        </div>
    </div>
@endsection
