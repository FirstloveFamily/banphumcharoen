@extends('layouts.manager', ['title' => 'Executive Job สรุป', 'pageTitle' => 'Job Order การจัดการ'])

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
            'pending' => 'รอเริ่มงาน',
            'processing' => 'กำลังดำเนินการ',
            'waiting_document' => 'รอเอกสาร',
            'approved' => 'อนุมัติแล้ว',
            'completed' => 'เสร็จสิ้น',
            'cancelled' => 'ยกเลิก',
            'rejected' => 'ไม่ผ่าน',
        ];

        $documentLabels = [
            'pending' => 'รอเอกสาร',
            'received' => 'รอตรวจ',
            'verified' => 'ตรวจผ่าน',
            'rejected' => 'ไม่ผ่าน',
            'missing' => 'ขาดเอกสาร',
        ];

        $documentClasses = [
            'pending' => 'bg-slate-50 text-slate-500 ring-slate-400/20',
            'received' => 'bg-[#fff9e8] text-[#0b2f52] ring-[#c9a227]/30',
            'verified' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            'rejected' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
            'missing' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        ];

        $paymentLabels = [
            'pending' => 'รอตรวจสอบ',
            'verified' => 'ตรวจสอบแล้ว',
            'rejected' => 'ไม่ผ่าน',
        ];

        $paymentClasses = [
            'pending' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            'verified' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            'rejected' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        ];

        $jobสถานะClasses = [
            'pending' => 'bg-[#0b2f52] text-white',
            'processing' => 'bg-[#0b2f52] text-white',
            'waiting_document' => 'bg-amber-500 text-white',
            'approved' => 'bg-[#c9a227] text-white',
            'completed' => 'bg-[#0b2f52] text-white',
            'cancelled' => 'bg-slate-300 text-slate-700',
            'rejected' => 'bg-rose-600 text-white',
        ];

        $worker = $jobOrder->worker;
    @endphp

    <div class="space-y-8">
        <!-- Header Section -->
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-5">
                <a href="{{ route('manager.job-orders.index') }}" 
                    class="grid h-12 w-12 place-items-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-slate-600 transition-all shadow-sm">
                    <i data-lucide="arrow-left" class="h-6 w-6"></i>
                </a>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-3xl font-bold tracking-tight text-[#0b2f52] italic">{{ $jobOrder->job_number }}</h2>
                        <span @class([
                            'rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider shadow-sm',
                            $jobสถานะClasses[$jobOrder->status] ?? 'bg-slate-100 text-slate-600'
                        ])>
                            {{ $statusLabels[$jobOrder->status] ?? $jobOrder->status }}
                        </span>
                    </div>
                    <p class="mt-1 text-slate-500 font-medium">{{ $jobOrder->service?->name ?: 'งานบริการเอกสารแรงงาน' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 text-right">
                <div class="hidden sm:block">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">ทะเบียน สรุป</p>
                    <p class="text-sm font-black text-slate-700 italic">ID: #{{ $jobOrder->id }}</p>
                </div>
            </div>
        </header>

        <!-- แจ้งเตือนs -->
        @if (session('success'))
            <div class="rounded-lg bg-emerald-50 p-4 border border-emerald-100 flex items-center gap-3 text-emerald-700 font-bold text-sm shadow-sm">
                <i data-lucide="check-circle" class="h-5 w-5"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Stats สรุป -->
        <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <article class="glass-card rounded-lg p-6 shadow-sm border-l-4 border-l-[#c9a227]">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">บริการ Fee</p>
                <h3 class="text-2xl font-black text-[#0b2f52] mt-2">฿{{ number_format($summary['service_fee'], 2) }}</h3>
            </article>
            <article class="glass-card rounded-lg p-6 shadow-sm border-l-4 border-l-emerald-600">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">รวม Collected</p>
                <h3 class="text-2xl font-black text-emerald-600 mt-2">฿{{ number_format($summary['paid_amount'], 2) }}</h3>
            </article>
            <article class="glass-card rounded-lg p-6 shadow-sm border-l-4 border-l-rose-600">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">คงเหลือ Balance</p>
                <h3 @class([
                    'text-2xl font-black mt-2 italic',
                    'text-rose-600' => $summary['remaining_amount'] > 0,
                    'text-[#0b2f52]' => $summary['remaining_amount'] <= 0,
                ])>฿{{ number_format($summary['remaining_amount'], 2) }}</h3>
            </article>
            <article class="glass-card rounded-lg p-6 shadow-sm border-l-4 border-l-[#0b2f52]">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Compliance Progress</p>
                <h3 class="text-2xl font-black text-[#0b2f52] mt-2">{{ $summary['documents_verified'] }}/{{ $summary['documents_total'] }} <span class="text-xs font-medium text-slate-400 uppercase">Items</span></h3>
            </article>
        </section>

        <div class="grid gap-8 lg:grid-cols-3">
            <!-- Left Column: Detailed ดูs -->
            <div class="lg:col-span-2 space-y-8">
                <!-- แรงงาน Portfolio Widget -->
                <section class="glass-card rounded-lg p-8 shadow-sm">
                    <div class="flex flex-col sm:flex-row items-center gap-8">
                        @if ($worker?->photo_path)
                            <img src="{{ asset('storage/' . $worker->photo_path) }}" class="h-32 w-32 rounded-lg object-cover border-4 border-white shadow-xl">
                        @else
                            <div class="h-32 w-32 rounded-lg bg-slate-100 flex items-center justify-center text-slate-300 shadow-lg italic font-black text-2xl uppercase">
                                {{ mb_substr($worker?->first_name_th ?: 'W', 0, 1) }}
                            </div>
                        @endif
                        <div class="flex-1 text-center sm:text-left">
                            <h3 class="text-2xl font-black text-[#0b2f52] italic">{{ $worker?->full_name_th ?: $worker?->full_name_en ?: 'Anonymous แรงงาน' }}</h3>
                            <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mt-1">{{ $worker?->passport_number ?: 'ไม่ พาสปอร์ต Assigned' }}</p>
                            
                            <div class="mt-6 flex flex-wrap justify-center sm:justify-start gap-3">
                                <div class="px-4 py-2 rounded-lg bg-slate-50 border border-slate-100 text-xs font-bold text-slate-600">
                                    <span class="text-[10px] text-slate-400 block uppercase mb-0.5">Nationality</span>
                                    {{ $worker?->nationality?->name_th ?: '-' }}
                                </div>
                                <div class="px-4 py-2 rounded-lg bg-slate-50 border border-slate-100 text-xs font-bold text-slate-600">
                                    <span class="text-[10px] text-slate-400 block uppercase mb-0.5">Due วันที่</span>
                                    {{ $jobOrder->due_date?->format('d M Y') ?: 'ไม่t Set' }}
                                </div>
                                <div class="px-4 py-2 rounded-lg bg-[#fff9e8] border border-[#c9a227]/30 text-xs font-bold text-[#0b2f52]">
                                    <span class="text-[10px] text-[#c9a227] block uppercase mb-0.5">ผู้รับผิดชอบ</span>
                                    {{ $jobOrder->assignedUser?->name ?: 'Unassigned' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- เอกสาร Checklist รายการ -->
                <section class="space-y-4">
                    <div class="flex items-center justify-between px-2">
                        <h3 class="text-lg font-black text-[#0b2f52] uppercase tracking-tighter italic flex items-center gap-2">
                            <i data-lucide="check-square" class="h-5 w-5 text-[#0b2f52]"></i>
                            Checklist Compliance
                        </h3>
                    </div>

                    <div class="space-y-3">
                        @forelse ($jobOrder->checklists as $checklist)
                            <article class="glass-card rounded-lg p-6 shadow-sm group">
                                <div class="flex flex-col lg:flex-row justify-between gap-6">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 mb-3">
                                            <span @class([
                                                'rounded-lg px-2 py-0.5 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset',
                                                $documentClasses[$checklist->status] ?? 'bg-slate-50 text-slate-500'
                                            ])>
                                                {{ $documentLabels[$checklist->status] ?? $checklist->status }}
                                            </span>
                                            @if ($checklist->is_required)
                                                <span class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Mandatory</span>
                                            @endif
                                        </div>
                                        <h4 class="font-bold text-[#0b2f52] italic">{{ $checklist->documentMaster?->name ?: 'เอกสาร Record' }}</h4>
                                        <p class="mt-1 text-xs text-slate-400 font-medium">Logged: {{ $checklist->received_at?->format('d M Y · H:i') ?? 'Awaiting Receipt' }}</p>
                                        
                                        @if ($checklist->remark)
                                            <div class="mt-4 bg-amber-50/50 rounded-lg p-4 border border-amber-100/50">
                                                <p class="text-xs font-black text-amber-700 uppercase tracking-tighter mb-1">Administrative หมายเหตุ:</p>
                                                <p class="text-sm text-amber-800 font-medium leading-relaxed">{{ $checklist->remark }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="lg:w-[240px] flex flex-col gap-3">
                                        <div class="flex gap-2 justify-end">
                                            @if ($checklist->attached_file_path)
                                                <a href="{{ asset('storage/' . $checklist->attached_file_path) }}" target="_blank"
                                                    class="grid h-10 w-10 place-items-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-[#c9a227] hover:border-[#c9a227]/40 transition-all shadow-sm">
                                                    <i data-lucide="external-link" class="h-4 w-4"></i>
                                                </a>
                                            @endif

                                            @if ($checklist->status !== 'verified')
                                                <form method="POST" action="{{ route('staff.portal.document-reviews.verify', $checklist) }}" class="flex-1">
                                                    @csrf
                                                    <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-lg bg-[#0b2f52] px-4 h-10 text-xs font-black uppercase text-white shadow-lg shadow-[#0b2f52]/20 hover:bg-[#123e68] transition-all">
                                                        <i data-lucide="check" class="h-3.5 w-3.5"></i>
                                                        Verify
                                                    </button>
                                                </form>
                                            @endif
                                        </div>

                                        @if ($checklist->status !== 'verified')
                                            <form method="POST" action="{{ route('staff.portal.document-reviews.reject', $checklist) }}" class="space-y-2">
                                                @csrf
                                                <textarea name="remark" rows="2" required
                                                    class="w-full rounded-lg border border-slate-100 bg-slate-50/50 p-3 text-xs font-medium outline-none focus:border-rose-400 focus:bg-white transition-all"
                                                    placeholder="Rejection reason..."></textarea>
                                                <button type="submit" class="w-full h-8 flex items-center justify-center gap-2 rounded-lg border border-rose-200 bg-white text-[10px] font-black uppercase text-rose-600 hover:bg-rose-50 transition-all">
                                                    Reject Item
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @empty
                            <p class="text-center py-12 text-slate-400 bg-slate-50/50 rounded-lg border-2 border-dashed border-slate-100 uppercase font-black text-xs tracking-widest italic">ไม่ documents mapped to this workflow</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <!-- Right Column: Administrative Widgets -->
            <aside class="space-y-8">
                <!-- Executive Actions -->
                <section class="glass-card rounded-lg p-8 shadow-sm">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-6 flex items-center gap-2 italic">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#0b2f52]"></span>
                        Executive Control
                    </h3>
                    
                    <form method="POST" action="{{ route('staff.portal.job-orders.status.update', $jobOrder) }}" class="space-y-6">
                        @csrf
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">Job ขั้นตอนงาน สถานะ</label>
                            <div class="flex gap-2">
                                <select name="status" class="h-11 flex-1 rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all appearance-none">
                                    @foreach($statusLabels as $val => $label)
                                        <option value="{{ $val }}" @selected($jobOrder->status === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="h-11 px-4 rounded-lg bg-[#0b2f52] text-white hover:bg-[#123e68] transition-all shadow-lg">
                                    <i data-lucide="save" class="h-4 w-4"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('staff.portal.job-orders.service-fee.update', $jobOrder) }}" class="mt-8 pt-8 border-t border-slate-50 space-y-6">
                        @csrf
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">Contract บริการ Fee (฿)</label>
                            <div class="flex gap-2">
                                <input type="number" name="service_fee" min="0" step="0.01" value="{{ (float) $jobOrder->service_fee }}"
                                    class="h-11 flex-1 rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-black font-mono outline-none focus:border-[#c9a227] focus:bg-white transition-all">
                                <button type="submit" class="h-11 px-4 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all">
                                    อัปเดต
                                </button>
                            </div>
                        </div>
                    </form>
                </section>

                <!-- การชำระเงิน ประวัติ Widget -->
                <section class="glass-card rounded-lg p-8 shadow-sm">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-6 flex items-center gap-2 italic">
                        <i data-lucide="banknote" class="h-4 w-4"></i>
                        Collections ประวัติ
                    </h3>
                    
                    <div class="space-y-4">
                        @forelse ($jobOrder->payments as $payment)
                            <div class="rounded-lg border border-slate-100 bg-white p-4 transition-all">
                                <div class="flex รายการ-start justify-between mb-3">
                                    <div>
                                        <p class="text-base font-black text-[#0b2f52] italic tracking-tight">฿{{ number_format((float) $payment->amount, 2) }}</p>
                                        <p class="text-[9px] font-bold text-slate-400 mt-0.5 uppercase">{{ $payment->payment_date?->format('d M Y') }}</p>
                                    </div>
                                    <span @class([
                                        'rounded-lg px-2 py-0.5 text-[9px] font-black uppercase tracking-wider ring-1 ring-inset',
                                        $paymentClasses[$payment->status] ?? 'bg-slate-50 text-slate-500'
                                    ])>
                                        {{ $paymentLabels[$payment->status] ?? $payment->status }}
                                    </span>
                                </div>

                                @if ($payment->slip_path)
                                    <a href="{{ asset('storage/' . $payment->slip_path) }}" target="_blank"
                                        class="flex items-center gap-2 text-[9px] font-black uppercase text-[#0b2f52] hover:text-[#0b2f52] transition-colors">
                                        <i data-lucide="external-link" class="h-3 w-3"></i>
                                        ตรวจสอบ Slip
                                    </a>
                                @endif

                                @if ($payment->status === 'pending')
                                    <div class="mt-4 pt-4 border-t border-slate-50 space-y-2">
                                        <form method="POST" action="{{ route('staff.portal.payments.verify', $payment) }}">
                                            @csrf
                                            <button type="submit" class="w-full h-8 rounded-lg bg-[#c9a227] text-white text-[9px] font-black uppercase tracking-widest hover:bg-[#f3d06f] transition-all">
                                                ยืนยันยอดเงิน
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-center py-6 text-[10px] font-black text-slate-300 bg-slate-50/50 rounded-lg border border-dashed border-slate-100 uppercase tracking-widest italic">ไม่ payments logged</p>
                        @endforelse
                    </div>
                </section>

                <!-- Audit Log Timeline -->
                <section class="glass-card rounded-lg p-8 shadow-sm">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-6 flex items-center gap-2 italic">
                        <i data-lucide="activity" class="h-4 w-4"></i>
                        Audit Trail
                    </h3>
                    <div class="space-y-6 relative before:absolute before:left-[11px] before:top-2 before:bottom-2 before:w-px before:bg-slate-100">
                        @forelse ($jobOrder->logs->take(8) as $log)
                            <div class="relative pl-8">
                                <div class="absolute left-0 top-1.5 h-6 w-6 rounded-full bg-white border border-slate-100 flex items-center justify-center shadow-sm">
                                    <div class="h-1.5 w-1.5 rounded-full bg-[#fff9e8]0"></div>
                                </div>
                                <p class="text-xs font-bold text-[#0b2f52] leading-snug">{{ $log->action }}</p>
                                <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-tighter">{{ $log->created_at->diffForHumans() }} · {{ $log->user?->name ?: 'System' }}</p>
                            </div>
                        @empty
                            <p class="text-center py-2 text-[10px] font-bold text-slate-300 italic uppercase">ไม่ logs recorded</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
