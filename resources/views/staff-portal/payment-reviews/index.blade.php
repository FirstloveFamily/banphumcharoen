@extends('layouts.staff-portal', ['title' => 'ตรวจสลิปโอนเงิน', 'pageTitle' => 'ระบบตรวจสอบการชำระเงิน'])

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
            '' => 'ทุกสถานะตรวจสอบ',
            'pending' => 'รอตรวจสอบ',
            'verified' => 'ตรวจสอบแล้ว',
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
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">ตรวจสลิปโอนเงิน</h2>
                <p class="mt-1 text-slate-500 text-lg">ตรวจสอบยอดเงินและยืนยันการชำระเงินจากนายจ้าง</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Pending</p>
                    <p class="text-2xl font-black text-amber-600">฿{{ number_format((float) $summary['total_pending_amount'], 2) }}</p>
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

        @if ($errors->any())
            <div class="rounded-2xl bg-rose-50 p-4 border border-rose-100 text-rose-700 text-sm">
                <p class="font-bold">อัปโหลดสลิปไม่สำเร็จ</p>
                <ul class="mt-1 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Stats Grid -->
        <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <article class="glass-card rounded-3xl p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">รอตรวจสอบ (สลิป)</p>
                <h3 class="text-2xl font-black text-amber-600 mt-2">{{ number_format($summary['pending']) }} <span class="text-xs font-medium text-slate-400 uppercase">items</span></h3>
            </article>
            <article class="glass-card rounded-3xl p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">ตรวจสอบแล้ว</p>
                <h3 class="text-2xl font-black text-emerald-600 mt-2">{{ number_format($summary['verified']) }} <span class="text-xs font-medium text-slate-400 uppercase">items</span></h3>
            </article>
            <article class="glass-card rounded-3xl p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">ถูกปฏิเสธ</p>
                <h3 class="text-2xl font-black text-rose-600 mt-2">{{ number_format($summary['rejected']) }} <span class="text-xs font-medium text-slate-400 uppercase">items</span></h3>
            </article>
        </section>

        <!-- Search Form -->
        <section class="glass-card rounded-3xl p-6 shadow-sm">
            <form method="GET" class="grid gap-6 lg:grid-cols-[1fr_240px_auto]">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400"></i>
                    <input type="text" name="q" value="{{ $keyword }}" 
                        placeholder="ค้นหาเลขใบงาน, บริษัท, หรือชื่อแรงงาน..." 
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
                    <a href="{{ route('staff.portal.payment-reviews.index') }}" class="grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-500 hover:bg-slate-200 transition-all">
                        <i data-lucide="refresh-cw" class="h-5 w-5"></i>
                    </a>
                </div>
            </form>
        </section>

        <!-- Payment Review List -->
        <section class="space-y-4">
            @forelse ($payments as $payment)
                <article class="glass-card rounded-3xl p-8 shadow-sm hover-shadow group">
                    <div class="flex flex-col lg:flex-row justify-between gap-8">
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-3 mb-4">
                                <a href="{{ route('staff.portal.job-orders.show', $payment->jobOrder) }}" class="text-xs font-black uppercase text-blue-600 tracking-tighter hover:underline">
                                    {{ $payment->jobOrder?->job_number ?? '-' }}
                                </a>
                                <span @class([
                                    'rounded-lg px-2 py-0.5 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset',
                                    $statusClasses[$payment->status] ?? 'bg-slate-100 text-slate-500'
                                ])>
                                    {{ $statusLabels[$payment->status] ?? $payment->status }}
                                </span>
                            </div>

                            <div class="flex items-baseline gap-4 mb-6">
                                <h3 class="text-3xl font-black text-slate-900 tracking-tight">฿{{ number_format((float) $payment->amount, 2) }}</h3>
                                @if ($payment->payment_method)
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">via {{ str($payment->payment_method)->replace('_', ' ') }}</span>
                                @endif
                            </div>
                            
                            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">นายจ้าง</p>
                                    <p class="mt-1 text-sm font-bold text-slate-700 truncate">{{ $payment->jobOrder?->employer?->company_name ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">แรงงาน</p>
                                    <p class="mt-1 text-sm font-bold text-slate-700 truncate">{{ $payment->jobOrder?->worker?->full_name_th ?: '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">วันที่โอนเงิน</p>
                                    <p class="mt-1 text-sm font-bold text-slate-700">{{ $payment->payment_date?->format('d/m/Y') ?? '-' }}</p>
                                </div>
                            </div>

                            @if ($payment->note)
                                <div class="mt-6 {{ $payment->status === 'rejected' ? 'bg-rose-50/50 border-rose-100' : 'bg-blue-50/50 border-blue-100' }} rounded-2xl p-4 border">
                                    <p class="text-[10px] font-black uppercase tracking-tighter mb-1 {{ $payment->status === 'rejected' ? 'text-rose-700' : 'text-blue-700' }}">หมายเหตุ:</p>
                                    <p class="text-sm font-medium leading-relaxed {{ $payment->status === 'rejected' ? 'text-rose-800' : 'text-blue-800' }}">{{ $payment->note }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="lg:w-[320px] flex flex-col gap-4">
                            <div class="flex gap-2 justify-end">
                                @if ($payment->slip_path)
                                    <a href="{{ asset('storage/' . $payment->slip_path) }}" target="_blank"
                                        class="grid h-12 w-12 place-items-center rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-100 transition-all shadow-sm" title="เปิดสลิปตรวจสอบ">
                                        <i data-lucide="external-link" class="h-5 w-5"></i>
                                    </a>
                                @endif

                                <a href="#payment-slip-{{ $payment->id }}"
                                    class="grid h-12 w-12 place-items-center rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-emerald-600 hover:border-emerald-100 transition-all shadow-sm"
                                    title="อัปโหลดหรือเปลี่ยนสลิป">
                                    <i data-lucide="upload" class="h-5 w-5"></i>
                                </a>

                                @if ($payment->status === 'pending')
                                    <form method="POST" action="{{ route('staff.portal.payments.verify', $payment) }}" class="flex-1">
                                        @csrf
                                        <button type="submit" class="w-full h-12 flex items-center justify-center gap-2 rounded-2xl bg-blue-600 text-sm font-black uppercase text-white shadow-lg shadow-blue-500/20 hover:bg-blue-700 transition-all">
                                            <i data-lucide="check" class="h-4 w-4"></i>
                                            ยืนยันยอดเงิน
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <form id="payment-slip-{{ $payment->id }}" method="POST" action="{{ route('staff.portal.payments.slip.store', $payment) }}" enctype="multipart/form-data" class="rounded-2xl border border-emerald-100 bg-emerald-50/50 p-4 space-y-3">
                                @csrf
                                <div>
                                    <p class="text-xs font-black text-emerald-800">{{ $payment->slip_path ? 'เปลี่ยนไฟล์สลิป' : 'อัปโหลดไฟล์สลิป' }}</p>
                                    <p class="mt-1 text-[10px] font-medium text-emerald-700">JPG, PNG, WEBP หรือ PDF ไม่เกิน 10 MB</p>
                                </div>
                                <input type="file" name="slip_file" accept=".jpg,.jpeg,.png,.webp,.pdf" required
                                    class="w-full text-xs text-slate-500 file:mr-3 file:rounded-xl file:border-0 file:bg-emerald-700 file:px-3 file:py-2 file:text-xs file:font-bold file:text-white hover:file:bg-emerald-800">
                                <button type="submit" class="w-full h-10 flex items-center justify-center gap-2 rounded-xl bg-emerald-600 text-xs font-black text-white hover:bg-emerald-700 transition-all">
                                    <i data-lucide="upload" class="h-3.5 w-3.5"></i>
                                    {{ $payment->slip_path ? 'เปลี่ยนสลิป' : 'อัปโหลดสลิป' }}
                                </button>
                            </form>

                            @if ($payment->status === 'pending')
                                <form method="POST" action="{{ route('staff.portal.payments.reject', $payment) }}" class="space-y-3">
                                    @csrf
                                    <textarea name="note" rows="2" required
                                        class="w-full rounded-2xl border border-slate-100 bg-slate-50/50 p-4 text-xs font-medium outline-none focus:border-rose-400 focus:bg-white transition-all"
                                        placeholder="ระบุเหตุผลที่ปฏิเสธสลิป..."></textarea>
                                    <button type="submit" class="w-full h-10 flex items-center justify-center gap-2 rounded-xl border border-rose-200 bg-white text-xs font-black uppercase text-rose-600 hover:bg-rose-50 transition-all">
                                        <i data-lucide="x" class="h-3.5 w-3.5"></i>
                                        ปฏิเสธสลิป
                                    </button>
                                </form>
                            @elseif ($payment->status === 'verified')
                                <div class="rounded-2xl bg-emerald-50 p-6 text-center border border-emerald-100/50">
                                    <p class="text-xs font-black uppercase tracking-widest text-emerald-700">Verified</p>
                                    <p class="mt-2 text-xs font-bold text-emerald-600/70">ตรวจโดย: {{ $payment->receiver?->name ?? 'System' }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="glass-card rounded-3xl py-24 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-50 text-slate-300">
                        <i data-lucide="receipt-thai-baht" class="h-8 w-8"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-slate-900">ไม่มีสลิปรอตรวจสอบ</h3>
                    <p class="text-slate-500">คุณได้ตรวจสอบข้อมูลการชำระเงินครบถ้วนแล้ว</p>
                </div>
            @endforelse

            @if($payments->hasPages())
                <div class="mt-8">
                    {{ $payments->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
