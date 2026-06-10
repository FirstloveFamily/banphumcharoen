@extends('layouts.staff-portal', ['title' => 'ตรวจเอกสาร', 'pageTitle' => 'ระบบตรวจสอบเอกสาร'])

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
            'pending' => 'รอเอกสาร',
            'received' => 'ได้รับแล้ว',
            'missing' => 'ขาดเอกสาร',
            'rejected' => 'ถูกปฏิเสธ',
        ];

        $statusClasses = [
            'pending' => 'bg-slate-100 text-slate-500',
            'received' => 'bg-blue-50 text-blue-600 ring-blue-500/20',
            'missing' => 'bg-amber-50 text-amber-600 ring-amber-500/20',
            'rejected' => 'bg-rose-50 text-rose-600 ring-rose-500/20',
        ];
    @endphp

    <div class="space-y-8">
        <!-- Header Section -->
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">ตรวจเอกสาร</h2>
                <p class="mt-1 text-slate-500 text-lg">ตรวจสอบความถูกต้องของไฟล์เอกสารที่ระบบได้รับ</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Items to Review</p>
                    <p class="text-2xl font-black text-blue-600">{{ number_format($reviews->total()) }}</p>
                </div>
            </div>
        </header>

        <!-- Alerts -->
        @if (session('success'))
            <div class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 flex items-center gap-3 text-emerald-700 font-bold text-sm">
                <i data-lucide="check-circle" class="h-5 w-5"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Stats Grid -->
        <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <article class="glass-card rounded-3xl p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">รอเอกสาร</p>
                <h3 class="text-2xl font-black text-slate-900 mt-2">{{ number_format($summary['pending']) }}</h3>
            </article>
            <article class="glass-card rounded-3xl p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">ได้รับแล้ว (รอตรวจ)</p>
                <h3 class="text-2xl font-black text-blue-600 mt-2">{{ number_format($summary['received']) }}</h3>
            </article>
            <article class="glass-card rounded-3xl p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">ขาดเอกสาร</p>
                <h3 class="text-2xl font-black text-amber-600 mt-2">{{ number_format($summary['missing']) }}</h3>
            </article>
            <article class="glass-card rounded-3xl p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">ถูกปฏิเสธแล้ว</p>
                <h3 class="text-2xl font-black text-rose-600 mt-2">{{ number_format($summary['rejected']) }}</h3>
            </article>
        </section>

        <!-- Search Form -->
        <section class="glass-card rounded-3xl p-6 shadow-sm">
            <form method="GET" class="grid gap-6 lg:grid-cols-[1fr_240px_auto]">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400"></i>
                    <input type="text" name="q" value="{{ $keyword }}" 
                        placeholder="ค้นหาเลขงาน, บริษัท, แรงงาน, หรือชื่อเอกสาร..." 
                        class="w-full h-12 rounded-2xl border border-slate-100 bg-slate-50/50 pl-12 pr-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all">
                </div>
                <div>
                    <select name="status" class="w-full h-12 rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all appearance-none">
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="h-12 px-8 rounded-2xl bg-slate-900 text-sm font-bold text-white hover:bg-slate-800 transition-all">
                        กรองข้อมูล
                    </button>
                    <a href="{{ route('staff.portal.document-reviews.index') }}" class="grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-500 hover:bg-slate-200 transition-all">
                        <i data-lucide="refresh-cw" class="h-5 w-5"></i>
                    </a>
                </div>
            </form>
        </section>

        <!-- Review List -->
        <section class="space-y-4">
            @forelse ($reviews as $review)
                <article class="glass-card rounded-3xl p-8 shadow-sm hover-shadow group">
                    <div class="flex flex-col lg:flex-row justify-between gap-8">
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-3 mb-4">
                                <span class="text-xs font-black uppercase text-blue-600 tracking-tighter">{{ $review->jobOrder?->job_number ?? '-' }}</span>
                                <span @class([
                                    'rounded-lg px-2 py-0.5 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset',
                                    $statusClasses[$review->status] ?? 'bg-slate-100 text-slate-500'
                                ])>
                                    {{ $statusLabels[$review->status] ?? $review->status }}
                                </span>
                                @if ($review->is_required)
                                    <span class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Required</span>
                                @endif
                            </div>

                            <h3 class="text-xl font-bold text-slate-900 mb-2">{{ $review->documentMaster?->name ?? 'เอกสารประกอบงาน' }}</h3>
                            
                            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">นายจ้าง</p>
                                    <p class="mt-1 text-sm font-bold text-slate-700 truncate">{{ $review->jobOrder?->employer?->company_name ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">แรงงาน</p>
                                    <p class="mt-1 text-sm font-bold text-slate-700 truncate">{{ $review->jobOrder?->worker?->full_name_th ?: '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">วันที่ได้รับ</p>
                                    <p class="mt-1 text-sm font-bold text-slate-700">{{ $review->received_at?->format('d/m/Y H:i') ?? '-' }}</p>
                                </div>
                            </div>

                            @if ($review->remark)
                                <div class="mt-6 bg-amber-50/50 rounded-2xl p-4 border border-amber-100/50">
                                    <p class="text-[10px] font-black text-amber-700 uppercase tracking-tighter mb-1">หมายเหตุล่าสุด:</p>
                                    <p class="text-sm text-amber-800 font-medium leading-relaxed">{{ $review->remark }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="lg:w-[320px] flex flex-col gap-4">
                            <div class="flex gap-2 justify-end">
                                @if ($review->attached_file_path)
                                    <a href="{{ asset('storage/' . $review->attached_file_path) }}" target="_blank"
                                        class="grid h-12 w-12 place-items-center rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-100 transition-all shadow-sm" title="เปิดไฟล์">
                                        <i data-lucide="external-link" class="h-5 w-5"></i>
                                    </a>
                                @endif

                                <form method="POST" action="{{ route('staff.portal.document-reviews.verify', $review) }}" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full h-12 flex items-center justify-center gap-2 rounded-2xl bg-blue-600 text-sm font-black uppercase text-white shadow-lg shadow-blue-500/20 hover:bg-blue-700 transition-all">
                                        <i data-lucide="check" class="h-4 w-4"></i>
                                        ตรวจผ่าน
                                    </button>
                                </form>
                            </div>

                            <form method="POST" action="{{ route('staff.portal.document-reviews.reject', $review) }}" class="space-y-3">
                                @csrf
                                <textarea name="remark" rows="2" required
                                    class="w-full rounded-2xl border border-slate-100 bg-slate-50/50 p-4 text-xs font-medium outline-none focus:border-rose-400 focus:bg-white transition-all"
                                    placeholder="ระบุเหตุผลที่ต้องส่งแก้ไข..."></textarea>
                                <button type="submit" class="w-full h-10 flex items-center justify-center gap-2 rounded-xl border border-rose-200 bg-white text-xs font-black uppercase text-rose-600 hover:bg-rose-50 transition-all">
                                    <i data-lucide="x" class="h-3.5 w-3.5"></i>
                                    ส่งกลับแก้ไข
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="glass-card rounded-3xl py-24 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-50 text-slate-300">
                        <i data-lucide="file-check" class="h-8 w-8"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-slate-900">ไม่มีเอกสารรอตรวจ</h3>
                    <p class="text-slate-500">คุณได้จัดการเอกสารทั้งหมดในคิวเรียบร้อยแล้ว</p>
                </div>
            @endforelse

            @if($reviews->hasPages())
                <div class="mt-8">
                    {{ $reviews->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
