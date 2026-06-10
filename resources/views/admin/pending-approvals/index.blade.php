@extends('layouts.manager', ['title' => 'รายการรออนุมัติ', 'pageTitle' => 'คิวตรวจสอบสำคัญ'])

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
        $documentสถานะLabels = [
            'pending' => 'รอเอกสาร',
            'received' => 'รอตรวจ',
            'missing' => 'ขาดเอกสาร',
            'rejected' => 'ส่งแก้ไข',
        ];

        $documentStatusClasses = [
            'pending' => 'bg-slate-50 text-slate-500 ring-slate-400/20',
            'received' => 'bg-[#fff9e8] text-[#0b2f52] ring-[#c9a227]/30',
            'missing' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            'rejected' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        ];
    @endphp

    <div class="space-y-8">
        <!-- Header Section -->
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-[#0b2f52] italic">คิวตรวจสอบสำคัญ</h2>
                <p class="mt-1 text-slate-500 text-lg font-medium">รายการเอกสารและธุรกรรมที่รอการตัดสินใจจากผู้จัดการ</p>
            </div>

            <form method="GET" class="flex w-full flex-col gap-3 sm:flex-row lg:w-auto">
                <div class="relative min-w-0 sm:w-80">
                    <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400"></i>
                    <input type="text" name="q" value="{{ $keyword }}"
                        placeholder="เลขงาน, บริษัท, แรงงาน..."
                        class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 pl-12 pr-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all">
                </div>
                <button type="submit" class="h-12 px-8 rounded-lg bg-[#0b2f52] text-white hover:bg-[#123e68] transition-all shadow-lg">
                    <i data-lucide="search" class="h-4 w-4"></i>
                </button>
                @if ($keyword !== '')
                    <a href="{{ route('manager.pending-approvals.index') }}" class="grid h-12 w-12 place-items-center rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition-all">
                        <i data-lucide="refresh-cw" class="h-5 w-5"></i>
                    </a>
                @endif
            </form>
        </header>

        <!-- แจ้งเตือนs -->
        @if (session('success'))
            <div class="rounded-lg bg-emerald-50 p-4 border border-emerald-100 flex items-center gap-3 text-emerald-700 font-bold text-sm">
                <i data-lucide="check-circle" class="h-5 w-5"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Stats สรุป -->
        <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <article class="glass-card rounded-lg p-6 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">เอกสารรอตรวจ</p>
                <h3 class="text-2xl font-black text-[#0b2f52] mt-2">{{ number_format($summary['documents_received']) }} <span class="text-xs font-medium text-slate-400">รายการ</span></h3>
            </article>
            <article class="glass-card rounded-lg p-6 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">เอกสารติดปัญหา</p>
                <h3 class="text-2xl font-black text-amber-600 mt-2">{{ number_format($summary['documents_pending']) }} <span class="text-xs font-medium text-slate-400">รายการ</span></h3>
            </article>
            <article class="glass-card rounded-lg p-6 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">สลิปรอตรวจ</p>
                <h3 class="text-2xl font-black text-[#0b2f52] mt-2">{{ number_format($summary['payments_pending']) }} <span class="text-xs font-medium text-slate-400">รายการ</span></h3>
            </article>
            <article class="glass-card rounded-lg p-6 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">ยอดเงินรอตรวจสอบ</p>
                <h3 class="text-2xl font-black text-emerald-600 mt-2">฿{{ number_format((float) $summary['payments_amount'], 2) }}</h3>
            </article>
        </section>

        <!-- เอกสาร Section -->
        <section class="glass-card overflow-hidden rounded-lg shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-8 py-5">
                <div>
                    <h3 class="text-lg font-black text-[#0b2f52] uppercase tracking-tighter italic">เอกสาร ตรวจสอบ รายการ</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">{{ number_format($documents->total()) }} รายการรอตรวจ</p>
                </div>
                <i data-lucide="files" class="h-6 w-6 text-[#c9a227]"></i>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($documents as $document)
                    <article class="p-8 hover:bg-slate-50/50 transition-colors">
                        <div class="grid gap-8 lg:grid-cols-[1fr_320px]">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-3 mb-4">
                                    <span class="text-xs font-black uppercase text-[#0b2f52] tracking-tighter">
                                        {{ $document->jobOrder?->job_number ?? '-' }}
                                    </span>
                                    <span @class([
                                        'rounded-lg px-2 py-0.5 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset',
                                        $documentStatusClasses[$document->status] ?? 'bg-slate-50 text-slate-500'
                                    ])>
                                        {{ $documentสถานะLabels[$document->status] ?? $document->status }}
                                    </span>
                                    @if ($document->is_required)
                                        <span class="text-[10px] font-black text-rose-500 uppercase tracking-widest">จำเป็น</span>
                                    @endif
                                </div>

                                <h4 class="text-xl font-bold text-[#0b2f52] mb-4 italic">
                                    {{ $document->documentMaster?->name ?? 'เอกสารประกอบงาน' }}
                                </h4>
                                
                                <div class="grid gap-6 sm:grid-cols-3">
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">นายจ้าง</p>
                                        <p class="mt-1 text-sm font-bold text-slate-700 truncate">{{ $document->jobOrder?->employer?->company_name ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">แรงงาน</p>
                                        <p class="mt-1 text-sm font-bold text-slate-700 truncate">{{ $document->jobOrder?->worker?->full_name_th ?: '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">รับเอกสาร</p>
                                        <p class="mt-1 text-sm font-bold text-slate-700">{{ $document->received_at?->format('d M Y · H:i') ?? '-' }}</p>
                                    </div>
                                </div>

                                @if ($document->remark)
                                    <div class="mt-6 bg-amber-50/50 rounded-lg p-4 border border-amber-100/50">
                                        <p class="text-[10px] font-black text-amber-700 uppercase tracking-tighter mb-1">หมายเหตุล่าสุด:</p>
                                        <p class="text-sm text-amber-800 font-medium leading-relaxed">{{ $document->remark }}</p>
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-col gap-4">
                                <div class="flex gap-2">
                                    @if ($document->attached_file_path)
                                        <a href="{{ asset('storage/' . $document->attached_file_path) }}" target="_blank"
                                            class="grid h-12 w-12 place-items-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-[#c9a227] transition-all shadow-sm">
                                            <i data-lucide="external-link" class="h-5 w-5"></i>
                                        </a>
                                    @endif
                                    @if ($document->status === 'received')
                                        <form method="POST" action="{{ route('staff.portal.document-reviews.verify', $document) }}" class="flex-1">
                                            @csrf
                                            <button type="submit" class="w-full h-12 flex items-center justify-center gap-2 rounded-lg bg-[#0b2f52] text-white text-xs font-black uppercase tracking-widest hover:bg-[#123e68] transition-all shadow-lg shadow-[#0b2f52]/20">
                                                <i data-lucide="check" class="h-4 w-4"></i>
                                                อนุมัติ
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                @if ($document->status === 'received')
                                    <form method="POST" action="{{ route('staff.portal.document-reviews.reject', $document) }}" class="space-y-3">
                                        @csrf
                                        <textarea name="remark" rows="2" required
                                            class="w-full rounded-lg border border-slate-100 bg-slate-50/50 p-4 text-xs font-medium outline-none focus:border-rose-400 focus:bg-white transition-all"
                                            placeholder="เหตุผลที่ต้องส่งกลับแก้ไข..."></textarea>
                                        <button type="submit" class="w-full h-10 flex items-center justify-center gap-2 rounded-lg border border-rose-200 bg-white text-rose-600 text-[10px] font-black uppercase hover:bg-rose-50 transition-all">
                                            <i data-lucide="x" class="h-3.5 w-3.5"></i>
                                            ส่งกลับแก้ไข
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="p-12 text-center">
                        <div class="mx-auto h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 mb-4">
                            <i data-lucide="check-check" class="h-8 w-8"></i>
                        </div>
                        <h4 class="text-lg font-bold text-[#0b2f52] italic">คิวตรวจสอบว่างแล้ว</h4>
                        <p class="text-slate-400 text-sm mt-1 uppercase tracking-widest">ไม่มีเอกสารรอผู้จัดการตรวจสอบ</p>
                    </div>
                @endforelse
            </div>

            @if ($documents->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/50 px-8 py-4">
                    {{ $documents->links() }}
                </div>
            @endif
        </section>

        <!-- การชำระเงินs Section -->
        <section class="glass-card overflow-hidden rounded-lg shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-8 py-5">
                <div>
                    <h3 class="text-lg font-black text-[#0b2f52] uppercase tracking-tighter italic">การชำระเงิน Approval รายการ</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">{{ number_format($payments->total()) }} ธุรกรรมรอตรวจ</p>
                </div>
                <i data-lucide="banknote" class="h-6 w-6 text-emerald-500"></i>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($payments as $payment)
                    <article class="p-8 hover:bg-slate-50/50 transition-colors">
                        <div class="grid gap-8 lg:grid-cols-[1fr_320px]">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-3 mb-4">
                                    <span class="text-xs font-black uppercase text-[#0b2f52] tracking-tighter">
                                        {{ $payment->jobOrder?->job_number ?? '-' }}
                                    </span>
                                    <span class="rounded-lg bg-amber-50 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                        รอตรวจสอบ
                                    </span>
                                </div>
                                
                                <div class="flex items-baseline gap-4 mb-6">
                                    <h3 class="text-3xl font-black text-[#0b2f52] tracking-tight italic">฿{{ number_format((float) $payment->amount, 2) }}</h3>
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">via {{ str($payment->payment_method)->replace('_', ' ') }}</span>
                                </div>

                                <div class="grid gap-6 sm:grid-cols-3">
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">นายจ้าง</p>
                                        <p class="mt-1 text-sm font-bold text-slate-700 truncate">{{ $payment->jobOrder?->employer?->company_name ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">แรงงาน</p>
                                        <p class="mt-1 text-sm font-bold text-slate-700 truncate">{{ $payment->jobOrder?->worker?->full_name_th ?: '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">การชำระเงิน วันที่</p>
                                        <p class="mt-1 text-sm font-bold text-slate-700">{{ $payment->payment_date?->format('d M Y') ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-4">
                                <div class="flex gap-2">
                                    @if ($payment->slip_path)
                                        <a href="{{ asset('storage/' . $payment->slip_path) }}" target="_blank"
                                            class="grid h-12 w-12 place-items-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-[#c9a227] transition-all shadow-sm">
                                            <i data-lucide="external-link" class="h-5 w-5"></i>
                                        </a>
                                    @endif
                                    <form method="POST" action="{{ route('staff.portal.payments.verify', $payment) }}" class="flex-1">
                                        @csrf
                                        <button type="submit" class="w-full h-12 flex items-center justify-center gap-2 rounded-lg bg-[#c9a227] text-white text-xs font-black uppercase tracking-widest hover:bg-[#f3d06f] transition-all shadow-lg shadow-emerald-500/20">
                                            <i data-lucide="check" class="h-4 w-4"></i>
                                            ยืนยันยอดเงิน
                                        </button>
                                    </form>
                                </div>

                                <form method="POST" action="{{ route('staff.portal.payments.reject', $payment) }}" class="space-y-3">
                                    @csrf
                                    <textarea name="note" rows="2" required
                                        class="w-full rounded-lg border border-slate-100 bg-slate-50/50 p-4 text-xs font-medium outline-none focus:border-rose-400 focus:bg-white transition-all"
                                        placeholder="เหตุผลที่ต้องส่งกลับแก้ไข..."></textarea>
                                    <button type="submit" class="w-full h-10 flex items-center justify-center gap-2 rounded-lg border border-rose-200 bg-white text-rose-600 text-[10px] font-black uppercase hover:bg-rose-50 transition-all">
                                        <i data-lucide="x" class="h-3.5 w-3.5"></i>
                                        Reject Transaction
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="p-12 text-center">
                        <div class="mx-auto h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 mb-4">
                            <i data-lucide="credit-card" class="h-8 w-8"></i>
                        </div>
                        <h4 class="text-lg font-bold text-[#0b2f52] italic">ไม่ payments to review</h4>
                        <p class="text-slate-400 text-sm mt-1 uppercase tracking-widest">ทั้งหมด transactions have been verified</p>
                    </div>
                @endforelse
            </div>

            @if ($payments->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/50 px-8 py-4">
                    {{ $payments->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
