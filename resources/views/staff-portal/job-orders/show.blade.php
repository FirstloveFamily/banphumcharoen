@extends('layouts.staff-portal', ['title' => 'รายละเอียดใบงาน', 'pageTitle' => 'จัดการข้อมูลใบงาน'])

@push('head')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
    .status-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }
</style>
@endpush

@section('content')
    @php
        $documentLabels = [
            'pending' => 'รอเอกสาร',
            'received' => 'ได้รับแล้ว',
            'verified' => 'ตรวจผ่าน',
            'rejected' => 'ไม่ผ่าน',
            'missing' => 'ขาดเอกสาร',
        ];

        $documentClasses = [
            'pending' => 'bg-slate-100 text-slate-500',
            'received' => 'bg-blue-50 text-blue-600 ring-blue-500/20',
            'verified' => 'bg-emerald-50 text-emerald-600 ring-emerald-500/20',
            'rejected' => 'bg-rose-50 text-rose-600 ring-rose-500/20',
            'missing' => 'bg-amber-50 text-amber-600 ring-amber-500/20',
        ];

        $paymentLabels = [
            'pending' => 'รอตรวจสอบ',
            'verified' => 'ตรวจผ่าน',
            'rejected' => 'ไม่ผ่าน',
        ];

        $paymentClasses = [
            'pending' => 'bg-blue-50 text-blue-600 ring-blue-500/20',
            'verified' => 'bg-emerald-50 text-emerald-600 ring-emerald-500/20',
            'rejected' => 'bg-rose-50 text-rose-600 ring-rose-500/20',
        ];
        $worker = $jobOrder->worker;
    @endphp

    <div class="space-y-8">
        <!-- Header Section -->
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-5">
                <a href="{{ route('staff.portal.job-orders.index') }}" 
                    class="grid h-12 w-12 place-items-center rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-slate-600 transition-all shadow-sm">
                    <i data-lucide="arrow-left" class="h-6 w-6"></i>
                </a>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-3xl font-bold tracking-tight text-slate-900">{{ $jobOrder->job_number }}</h2>
                        <span @class([
                            'rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider shadow-sm',
                            $jobOrder->status_badge_class
                        ])>
                            {{ $jobOrder->status_label }}
                        </span>
                    </div>
                    <p class="mt-1 text-slate-500 font-medium">{{ $jobOrder->service?->name ?: 'งานบริการเอกสารแรงงาน' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 text-right">
                <div class="hidden sm:block">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Created Date</p>
                    <p class="text-sm font-bold text-slate-700">{{ $jobOrder->created_at->format('d M Y') }}</p>
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

        <!-- Stats Summary -->
        <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <article class="glass-card rounded-3xl p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">ค่าบริการรวม</p>
                <h3 class="text-2xl font-black text-slate-900 mt-2">฿{{ number_format($summary['service_fee'], 2) }}</h3>
            </article>
            <article class="glass-card rounded-3xl p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">ชำระแล้ว</p>
                <h3 class="text-2xl font-black text-emerald-600 mt-2">฿{{ number_format($summary['paid_amount'], 2) }}</h3>
            </article>
            <article class="glass-card rounded-3xl p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">ยอดค้างชำระ</p>
                <h3 @class([
                    'text-2xl font-black mt-2',
                    'text-rose-600' => $summary['remaining_amount'] > 0,
                    'text-slate-900' => $summary['remaining_amount'] <= 0,
                ])>฿{{ number_format($summary['remaining_amount'], 2) }}</h3>
            </article>
            <article class="glass-card rounded-3xl p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">ความคืบหน้าเอกสาร</p>
                <h3 class="text-2xl font-black text-blue-600 mt-2">{{ $summary['documents_verified'] }}/{{ $summary['documents_total'] }} <span class="text-xs font-medium text-slate-400">รายการ</span></h3>
            </article>
        </section>

        <div class="grid gap-8 lg:grid-cols-3">
            <!-- Main Column -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Worker Info Card -->
                <section class="glass-card rounded-3xl p-8 shadow-sm">
                    <div class="flex items-center gap-5 mb-8 pb-5 border-b border-slate-50">
                        @if ($worker?->photo_path)
                            <img src="{{ asset('storage/' . $worker->photo_path) }}" class="h-16 w-16 rounded-2xl object-cover border-2 border-white shadow-md">
                        @else
                            <div class="h-16 w-16 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-300">
                                <i data-lucide="user" class="h-8 w-8"></i>
                            </div>
                        @endif
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">{{ $worker?->full_name_th ?: $worker?->full_name_en ?: 'ไม่ระบุชื่อ' }}</h3>
                            <p class="text-sm font-medium text-slate-500 uppercase tracking-tighter">{{ $worker?->passport_number ?: 'No Passport' }} · {{ $worker?->nationality?->name_th }}</p>
                        </div>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">บริษัทนายจ้าง</p>
                            <p class="text-sm font-bold text-slate-700">{{ $jobOrder->employer?->company_name ?? '-' }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">กำหนดส่งงาน (Due Date)</p>
                            <p class="text-sm font-bold text-slate-700">{{ $jobOrder->due_date?->format('d/m/Y') ?: '-' }}</p>
                        </div>
                    </div>
                </section>

                <!-- Document Checklist -->
                <section class="space-y-4">
                    <div class="flex items-center justify-between px-2">
                        <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <i data-lucide="check-square" class="h-5 w-5 text-blue-600"></i>
                            เอกสารประกอบใบงาน
                        </h3>
                    </div>

                    <div class="space-y-3">
                        @forelse ($jobOrder->checklists as $checklist)
                            @php
                                $attachedFile = $checklist->attached_file_path ? asset('storage/' . $checklist->attached_file_path) : null;
                                $fileExtension = $checklist->attached_file_path ? strtolower(pathinfo($checklist->attached_file_path, PATHINFO_EXTENSION)) : null;
                                $isImageFile = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'webp'], true);
                            @endphp
                            <article class="glass-card rounded-3xl p-6 shadow-sm group">
                                <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span @class([
                                                'rounded-lg px-2 py-0.5 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset',
                                                $documentClasses[$checklist->status] ?? 'bg-slate-100 text-slate-500'
                                            ])>
                                                {{ $documentLabels[$checklist->status] ?? $checklist->status }}
                                            </span>
                                            @if ($checklist->is_required)
                                                <span class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Required</span>
                                            @endif
                                        </div>
                                        <h4 class="font-bold text-slate-900 text-lg">{{ $checklist->documentMaster?->name ?: 'เอกสารประกอบงาน' }}</h4>
                                        <p class="mt-1 text-xs text-slate-400 font-medium">รับเอกสารเมื่อ: {{ $checklist->received_at?->format('d/m/Y H:i') ?? 'ยังไม่ได้รับ' }}</p>
                                        
                                        @if ($checklist->remark)
                                            <div class="mt-3 bg-amber-50/50 rounded-2xl p-3 border border-amber-100/50">
                                                <p class="text-xs font-bold text-amber-700 uppercase tracking-tighter mb-1">หมายเหตุ/เหตุผล:</p>
                                                <p class="text-sm text-amber-800 font-medium">{{ $checklist->remark }}</p>
                                            </div>
                                        @endif

                                        @if ($attachedFile && $isImageFile)
                                            <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 shadow-sm">
                                                <a href="{{ $attachedFile }}" target="_blank" class="block">
                                                    <img src="{{ $attachedFile }}" alt="{{ $checklist->documentMaster?->name ?: 'เอกสาร' }}"
                                                        class="h-56 w-full object-cover transition-transform duration-300 hover:scale-[1.02]">
                                                </a>
                                                <div class="flex items-center justify-between gap-3 border-t border-slate-200 bg-white px-4 py-3">
                                                    <div>
                                                        <p class="text-xs font-black uppercase tracking-widest text-slate-400">ไฟล์แนบ</p>
                                                        <p class="text-sm font-bold text-slate-700">แสดงตัวอย่างรูปเอกสาร</p>
                                                    </div>
                                                    <a href="{{ $attachedFile }}" target="_blank" class="text-xs font-bold text-blue-600 hover:text-blue-700">
                                                        เปิดเต็มจอ
                                                    </a>
                                                </div>
                                            </div>
                                        @elseif ($attachedFile)
                                            <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                <div class="flex items-center justify-between gap-4">
                                                    <div class="min-w-0">
                                                        <p class="text-xs font-black uppercase tracking-widest text-slate-400">ไฟล์แนบ</p>
                                                        <p class="truncate text-sm font-bold text-slate-700">{{ basename($checklist->attached_file_path) }}</p>
                                                        <p class="mt-1 text-xs text-slate-500">ไฟล์ประเภท {{ strtoupper($fileExtension ?? 'FILE') }}</p>
                                                    </div>
                                                    <a href="{{ $attachedFile }}" target="_blank" class="grid h-11 w-11 place-items-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-blue-600 hover:border-blue-100 transition-all shadow-sm" title="เปิดไฟล์">
                                                        <i data-lucide="external-link" class="h-4 w-4"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex flex-col gap-3 min-w-[200px]">
                                        <div class="flex flex-wrap gap-2 justify-end">
                                            @if ($checklist->attached_file_path)
                                                <a href="{{ asset('storage/' . $checklist->attached_file_path) }}" target="_blank"
                                                    class="grid h-10 w-10 place-items-center rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-100 transition-all shadow-sm" title="เปิดไฟล์">
                                                    <i data-lucide="external-link" class="h-4 w-4"></i>
                                                </a>
                                            @endif

                                            @if ($checklist->status !== 'verified')
                                                <form method="POST" action="{{ route('staff.portal.document-reviews.verify', $checklist) }}">
                                                    @csrf
                                                    <button type="submit" class="flex items-center gap-2 rounded-xl bg-blue-600 px-4 h-10 text-xs font-black uppercase text-white shadow-lg shadow-blue-500/20 hover:bg-blue-700 transition-all">
                                                        <i data-lucide="check" class="h-3.5 w-3.5"></i>
                                                        ตรวจผ่าน
                                                    </button>
                                                </form>
                                            @endif

                                            <form method="POST" action="{{ route('staff.portal.job-orders.documents.destroy', [$jobOrder, $checklist]) }}" onsubmit="return confirm('ต้องการลบเอกสารนี้ออกจากใบงานใช่หรือไม่?')" class="flex-shrink-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="grid h-10 w-10 place-items-center rounded-xl border border-rose-200 bg-white text-rose-600 hover:bg-rose-50 transition-all shadow-sm" title="ลบเอกสาร">
                                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                                </button>
                                            </form>
                                        </div>

                                        @if ($checklist->status !== 'verified')
                                            <form method="POST" action="{{ route('staff.portal.document-reviews.reject', $checklist) }}" class="space-y-2">
                                                @csrf
                                                <textarea name="remark" rows="2" required
                                                    class="w-full rounded-2xl border border-slate-100 bg-slate-50/50 p-3 text-xs font-medium outline-none focus:border-rose-400 focus:bg-white transition-all"
                                                    placeholder="ระบุเหตุผลที่ต้องแก้ไข..."></textarea>
                                                <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-xl border border-rose-200 bg-white px-3 py-2 text-[10px] font-black uppercase text-rose-600 hover:bg-rose-50 transition-all">
                                                    <i data-lucide="x" class="h-3 w-3"></i>
                                                    ส่งแก้ไข / ปฏิเสธ
                                                </button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('staff.portal.job-orders.documents.store', [$jobOrder, $checklist]) }}" enctype="multipart/form-data" class="space-y-2">
                                            @csrf
                                            <label class="block">
                                                <span class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-400">อัปโหลด / แทนที่ไฟล์</span>
                                                <input type="file" name="document_file" required accept=".pdf,.jpg,.jpeg,.png,.webp"
                                                    class="w-full rounded-2xl border border-slate-100 bg-slate-50/50 p-2 text-xs text-slate-500 file:mr-3 file:rounded-xl file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-[10px] file:font-black file:uppercase file:text-white hover:file:bg-slate-800 transition-all">
                                            </label>
                                            <textarea name="remark" rows="2"
                                                class="w-full rounded-2xl border border-slate-100 bg-slate-50/50 p-3 text-xs font-medium outline-none focus:border-blue-400 focus:bg-white transition-all"
                                                placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)"></textarea>
                                            <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#0b2f52] via-[#123e68] to-[#b91c1c] px-3 py-2 text-[10px] font-black uppercase text-white hover:opacity-95 transition-all">
                                                <i data-lucide="upload" class="h-3 w-3"></i>
                                                อัปโหลดเอกสาร
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <p class="text-center py-12 text-slate-400 bg-slate-50/50 rounded-3xl border-2 border-dashed border-slate-100">ยังไม่มีรายการเอกสารสำหรับใบงานนี้</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <!-- Sidebar Column -->
            <div class="space-y-8">
                <!-- Status & Fee Management -->
                <section class="glass-card rounded-3xl p-6 shadow-sm space-y-6">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 mb-4 flex items-center gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-slate-900"></span>
                            Update Job Status
                        </h3>
                        <form method="POST" action="{{ route('staff.portal.job-orders.status.update', $jobOrder) }}">
                            @csrf
                            <div class="flex gap-2">
                                <select name="status" id="job-status-select" class="h-11 flex-1 rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-blue-400 focus:bg-white transition-all appearance-none">
                                    @foreach($jobOrderStatuses as $statusOption)
                                        <option value="{{ $statusOption->code }}" @selected($jobOrder->status === $statusOption->code)>{{ $statusOption->name_th }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="h-11 px-4 rounded-2xl bg-slate-900 text-white hover:bg-slate-800 transition-all shadow-sm">
                                    <i data-lucide="save" class="h-4 w-4"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="pt-6 border-t border-slate-50">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 mb-4 flex items-center gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-blue-600"></span>
                            Service Fee (฿)
                        </h3>
                        <form method="POST" action="{{ route('staff.portal.job-orders.service-fee.update', $jobOrder) }}">
                            @csrf
                            <div class="flex gap-2">
                                <input type="number" name="service_fee" min="0" step="0.01" value="{{ (float) $jobOrder->service_fee }}"
                                    class="h-11 flex-1 rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold font-mono outline-none focus:border-blue-400 focus:bg-white transition-all">
                                <button type="submit" class="h-11 px-4 rounded-2xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition-all shadow-sm">
                                    Update
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="pt-6 border-t border-slate-50">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 mb-4 flex items-center gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-rose-600"></span>
                            Dangerous Action
                        </h3>
                        <form method="POST" action="{{ route('staff.portal.job-orders.destroy', $jobOrder) }}" onsubmit="return confirm('ต้องการลบใบงาน {{ $jobOrder->job_number }} ทั้งชุดใช่หรือไม่? การลบนี้จะลบเอกสาร สลิป และข้อมูลที่เกี่ยวข้องทั้งหมด')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full h-11 rounded-2xl border border-rose-200 bg-white text-rose-600 text-sm font-black uppercase hover:bg-rose-50 transition-all shadow-sm">
                                ลบใบงานทั้งหมด
                            </button>
                        </form>
                    </div>
                </section>

                <!-- Payments Section -->
                <section class="glass-card rounded-3xl p-6 shadow-sm">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 mb-6 flex items-center gap-2">
                        <i data-lucide="receipt-thai-baht" class="h-4 w-4"></i>
                        การชำระเงิน
                    </h3>
                    
                    <div class="space-y-4">
                        @forelse ($jobOrder->payments as $payment)
                            <div class="rounded-2xl border border-slate-100 bg-white p-4 transition-all">
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <p class="text-lg font-black text-slate-900 tracking-tight">฿{{ number_format((float) $payment->amount, 2) }}</p>
                                        <p class="text-[10px] font-bold text-slate-400 mt-0.5 uppercase tracking-tighter">{{ $payment->payment_date?->format('d/m/Y') }} · {{ str($payment->payment_method)->replace('_', ' ') }}</p>
                                    </div>
                                    <span @class([
                                        'rounded-lg px-2 py-0.5 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset',
                                        $paymentClasses[$payment->status] ?? 'bg-slate-100 text-slate-500'
                                    ])>
                                        {{ $paymentLabels[$payment->status] ?? $payment->status }}
                                    </span>
                                </div>

                                @if ($payment->slip_path)
                                    <a href="{{ asset('storage/' . $payment->slip_path) }}" target="_blank"
                                        class="flex items-center gap-2 text-[10px] font-black uppercase text-blue-600 hover:text-blue-700 transition-colors">
                                        <i data-lucide="external-link" class="h-3 w-3"></i>
                                        เปิดสลิปตรวจสอบ
                                    </a>
                                @endif

                                @if ($payment->status === 'pending')
                                    <div class="mt-4 pt-4 border-t border-slate-50 space-y-3">
                                        <form method="POST" action="{{ route('staff.portal.payments.verify', $payment) }}">
                                            @csrf
                                            <button type="submit" class="w-full h-9 rounded-xl bg-blue-600 text-white text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-md shadow-blue-500/10">
                                                ยืนยันการรับเงิน
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('staff.portal.payments.reject', $payment) }}" class="space-y-2">
                                            @csrf
                                            <textarea name="note" rows="1" required class="w-full rounded-xl border border-slate-100 bg-slate-50/50 p-2 text-[10px] font-medium" placeholder="เหตุผลที่ปฏิเสธ..."></textarea>
                                            <button type="submit" class="w-full h-8 rounded-xl border border-rose-200 bg-white text-rose-600 text-[10px] font-black uppercase hover:bg-rose-50 transition-all">
                                                ปฏิเสธสลิป
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-center py-6 text-xs font-bold text-slate-400 bg-slate-50/50 rounded-2xl border border-dashed border-slate-100">ยังไม่มีรายการชำระเงิน</p>
                        @endforelse
                    </div>
                </section>

                <!-- Activity Log -->
                <section class="glass-card rounded-3xl p-6 shadow-sm">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 mb-6 flex items-center gap-2">
                        <i data-lucide="activity" class="h-4 w-4"></i>
                        Activity Log
                    </h3>
                    <div class="space-y-6 relative before:absolute before:left-[11px] before:top-2 before:bottom-2 before:w-px before:bg-slate-100">
                        @forelse ($jobOrder->logs->take(10) as $log)
                            <div class="relative pl-8">
                                <div class="absolute left-0 top-1.5 h-6 w-6 rounded-full bg-white border border-slate-100 flex items-center justify-center shadow-sm">
                                    <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                                </div>
                                <p class="text-xs font-bold text-slate-900">{{ $log->action }}</p>
                                <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">{{ $log->description }}</p>
                                <p class="text-[9px] font-black text-slate-300 mt-1 uppercase tracking-tighter">{{ $log->created_at->format('d/m/Y H:i') }} · {{ $log->user?->name ?? 'System' }}</p>
                            </div>
                        @empty
                            <p class="text-center py-2 text-xs font-bold text-slate-400 italic">No activity recorded</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
