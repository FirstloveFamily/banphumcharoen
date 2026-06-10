@php
    $title = 'รายละเอียดงาน ' . $jobOrder->job_number;

    $statusLabels = [
        'pending' => 'รอเริ่มงาน',
        'processing' => 'กำลังดำเนินการ',
        'waiting_document' => 'รอเอกสาร',
        'approved' => 'อนุมัติแล้ว',
        'completed' => 'เสร็จสิ้น',
        'cancelled' => 'ยกเลิก',
        'rejected' => 'ไม่ผ่าน',
    ];

    $statusStyles = [
        'pending' => 'bg-slate-100 text-slate-700',
        'processing' => 'bg-orange-50 text-orange-700',
        'waiting_document' => 'bg-blue-50 text-blue-700',
        'approved' => 'bg-emerald-50 text-emerald-700',
        'completed' => 'bg-green-50 text-green-700',
        'cancelled' => 'bg-slate-100 text-slate-500',
        'rejected' => 'bg-red-50 text-red-700',
    ];

    $paymentLabels = [
        'pending' => 'รอชำระ',
        'partial' => 'ชำระบางส่วน',
        'paid' => 'ชำระครบแล้ว',
        'cancelled' => 'ยกเลิก',
    ];

    $checklistLabels = [
        'pending' => 'รอเอกสาร',
        'received' => 'ได้รับแล้ว',
        'verified' => 'ตรวจผ่าน',
        'rejected' => 'ไม่ผ่าน',
        'missing' => 'ขาดเอกสาร',
    ];

    $checklistStyles = [
        'pending' => 'bg-slate-100 text-slate-700',
        'received' => 'bg-blue-50 text-blue-700',
        'verified' => 'bg-green-50 text-green-700',
        'rejected' => 'bg-red-50 text-red-700',
        'missing' => 'bg-orange-50 text-orange-700',
    ];

    $paymentMethodLabels = [
        'cash' => 'เงินสด',
        'transfer' => 'โอนเงิน',
        'promptpay' => 'พร้อมเพย์',
        'credit_card' => 'บัตรเครดิต',
    ];

    $paymentStatusLabels = [
        'pending' => 'รอตรวจสอบ',
        'verified' => 'ตรวจสอบแล้ว',
        'rejected' => 'ไม่ผ่าน',
    ];

    $paymentStatusStyles = [
        'pending' => 'bg-orange-50 text-orange-700',
        'verified' => 'bg-green-50 text-green-700',
        'rejected' => 'bg-red-50 text-red-700',
    ];

    $worker = $jobOrder->worker;
    $workerInitials = $worker
        ? mb_substr($worker->first_name_th ?: $worker->first_name_en ?: '-', 0, 1)
            . mb_substr($worker->last_name_th ?: $worker->last_name_en ?: '', 0, 1)
        : '-';
@endphp

@extends('layouts.app')

@push('head')
<style>
    .hero-gradient {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 25%, #3b82f6 50%, #dc2626 75%, #991b1b 100%);
    }

    .info-card {
        transition: all 0.3s ease;
    }

    .info-card:hover {
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-up {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
    }
</style>
@endpush

@section('content')
    <!-- Hero Section -->
    <section class="hero-gradient py-20 lg:py-28 relative overflow-hidden">
        <!-- Decorative Elements -->
        <div class="absolute inset-0 opacity-20 pointer-events-none">
            <div class="absolute -left-40 -top-40 h-[600px] w-[600px] rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute bottom-[-12rem] right-[-10rem] h-[500px] w-[500px] rounded-full bg-red-500/20 blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[700px] w-[700px] rounded-full bg-blue-400/10 blur-3xl"></div>
        </div>

        <!-- Grid Pattern -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,.04)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.04)_1px,transparent_1px)] bg-[size:60px_60px] opacity-30"></div>

        <div class="max-w-7xl mx-auto px-4 text-white sm:px-6 lg:px-8 relative z-10">
            <a href="{{ route('employers.dashboard') }}"
                class="inline-flex items-center gap-3 text-base font-semibold text-blue-100 transition hover:text-white">
                <i data-lucide="arrow-left" class="h-5 w-5"></i>
                กลับแดชบอร์ด
            </a>
            <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-12 lg:items-end">
                <div class="lg:col-span-8">
                    <p class="text-sm font-semibold text-blue-200 uppercase tracking-wider">JOB DETAIL</p>
                    <h1 class="mt-4 text-5xl lg:text-6xl font-bold leading-tight">{{ $jobOrder->job_number }}</h1>
                    <p class="mt-6 text-xl leading-8 text-white/90">
                        {{ $jobOrder->service?->name ?: 'งานบริการเอกสารแรงงานต่างด้าว' }}
                    </p>
                </div>
                <div class="lg:col-span-4 lg:text-right">
                    <span class="{{ $statusStyles[$jobOrder->status] ?? 'bg-slate-100 text-slate-700' }} inline-flex rounded-full px-6 py-3 text-base font-semibold shadow-lg">
                        {{ $statusLabels[$jobOrder->status] ?? $jobOrder->status }}
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Content Section -->
    <section class="bg-slate-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-8 rounded-3xl border border-green-100 bg-green-50 p-6 text-base font-semibold text-green-700 shadow-xl animate-fade-in-up">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-8 rounded-3xl border border-red-100 bg-red-50 p-6 text-base font-semibold text-red-700 shadow-xl animate-fade-in-up">
                    กรุณาตรวจสอบไฟล์เอกสารที่อัปโหลดอีกครั้ง
                </div>
            @endif

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-4 mb-12">
                <div class="info-card rounded-3xl border border-white/20 bg-white p-6 shadow-xl animate-fade-in-up">
                    <p class="text-sm text-slate-500 font-medium">ค่าบริการ</p>
                    <p class="mt-3 text-3xl font-bold text-blue-950">{{ number_format($jobOrder->service_fee, 2) }}</p>
                </div>
                <div class="info-card rounded-3xl border border-white/20 bg-white p-6 shadow-xl animate-fade-in-up">
                    <p class="text-sm text-slate-500 font-medium">ชำระแล้ว</p>
                    <p class="mt-3 text-3xl font-bold text-green-700">{{ number_format($jobOrder->paid_amount, 2) }}</p>
                </div>
                <div class="info-card rounded-3xl border border-white/20 bg-white p-6 shadow-xl animate-fade-in-up">
                    <p class="text-sm text-slate-500 font-medium">คงเหลือ</p>
                    <p class="mt-3 text-3xl font-bold text-orange-600">{{ number_format($jobOrder->getRemainingAmount(), 2) }}</p>
                </div>
                <div class="info-card rounded-3xl border border-white/20 bg-white p-6 shadow-xl animate-fade-in-up">
                    <p class="text-sm text-slate-500 font-medium">กำหนดดำเนินการ</p>
                    <p class="mt-3 text-3xl font-bold text-blue-950">{{ $jobOrder->due_date?->format('d/m/Y') ?: '-' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <div class="space-y-8 lg:col-span-2">
                    <div class="info-card rounded-3xl border border-white/20 bg-white p-8 shadow-xl animate-fade-in-up">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-blue-950">ข้อมูลลูกจ้าง</h2>
                                <p class="mt-2 text-base text-slate-500">ข้อมูลหลักที่ใช้ประกอบการดำเนินงาน</p>
                            </div>
                            @if ($jobOrder->worker?->photo_path)
                                <img src="{{ asset('storage/' . $jobOrder->worker->photo_path) }}"
                                    alt="{{ $jobOrder->worker?->full_name_th ?: $jobOrder->worker?->full_name_en }}"
                                    class="h-16 w-16 rounded-3xl object-cover ring-4 ring-slate-100 shadow-lg">
                            @else
                                <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-blue-50 text-base font-bold text-blue-700 ring-4 ring-slate-100 shadow-lg">
                                    {{ $workerInitials }}
                                </div>
                            @endif
                        </div>

                        <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">ชื่อภาษาไทย</p>
                                <p class="mt-2 text-base font-semibold text-slate-800">{{ $jobOrder->worker?->full_name_th ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">ชื่อภาษาอังกฤษ</p>
                                <p class="mt-2 text-base font-semibold text-slate-800">{{ $jobOrder->worker?->full_name_en ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">สัญชาติ</p>
                                <p class="mt-2 text-base font-semibold text-slate-800">{{ $jobOrder->worker?->nationality?->name_th ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">เลขพาสปอร์ต</p>
                                <p class="mt-2 text-base font-semibold text-slate-800">{{ $jobOrder->worker?->passport_number ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">ใบอนุญาตทำงานหมดอายุ</p>
                                <p class="mt-2 text-base font-semibold text-slate-800">{{ $jobOrder->worker?->wp_expiry?->format('d/m/Y') ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">วีซ่าหมดอายุ</p>
                                <p class="mt-2 text-base font-semibold text-slate-800">{{ $jobOrder->worker?->visa_expiry?->format('d/m/Y') ?: '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="info-card rounded-3xl border border-white/20 bg-white p-8 shadow-xl animate-fade-in-up">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-blue-950">รายการเอกสาร</h2>
                                <p class="mt-2 text-base text-slate-500">ตรวจสอบเอกสารที่ได้รับและเอกสารที่ต้องแก้ไข</p>
                            </div>
                            <i data-lucide="files" class="h-6 w-6 text-blue-600"></i>
                        </div>

                        <div class="mt-8 divide-y divide-slate-100">
                            @forelse ($jobOrder->checklists as $checklist)
                                @php
                                    $canUploadDocument = ! in_array($jobOrder->status, ['completed', 'cancelled', 'rejected'], true)
                                        && in_array($checklist->status, ['pending', 'missing', 'rejected'], true);
                                    $attachedFile = $checklist->attached_file_path ? asset('storage/' . $checklist->attached_file_path) : null;
                                    $fileExtension = $checklist->attached_file_path ? strtolower(pathinfo($checklist->attached_file_path, PATHINFO_EXTENSION)) : null;
                                    $isImageFile = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
                                @endphp
                                <div class="py-6">
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                        <p class="text-base font-semibold text-slate-800">{{ $checklist->documentMaster?->name ?: 'เอกสารประกอบงาน' }}</p>
                                        @if ($checklist->received_at)
                                            <p class="mt-2 text-sm text-slate-400">อัปโหลดล่าสุด {{ $checklist->received_at->format('d/m/Y H:i') }}</p>
                                        @endif
                                        @if ($checklist->remark)
                                            <p class="mt-2 text-base text-slate-500">{{ $checklist->remark }}</p>
                                        @endif
                                        @if ($checklist->attached_file_path)
                                            <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                                                @if ($isImageFile)
                                                    <div class="bg-slate-900/5 p-3">
                                                        <img src="{{ $attachedFile }}"
                                                            alt="{{ $checklist->documentMaster?->name ?: 'เอกสาร' }}"
                                                            class="max-h-72 w-full rounded-xl object-contain">
                                                    </div>
                                                @else
                                                    <div class="flex items-center justify-between gap-4 p-4">
                                                        <div class="min-w-0">
                                                            <p class="truncate text-sm font-bold text-slate-700">{{ basename($checklist->attached_file_path) }}</p>
                                                            <p class="mt-1 text-xs text-slate-400">ไฟล์แนบพร้อมเปิดดู</p>
                                                        </div>
                                                        <a href="{{ $attachedFile }}" target="_blank"
                                                            class="inline-flex flex-shrink-0 items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-blue-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-blue-50">
                                                            <i data-lucide="external-link" class="h-4 w-4"></i>
                                                            เปิดไฟล์
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <span class="{{ $checklistStyles[$checklist->status] ?? 'bg-slate-100 text-slate-700' }} rounded-full px-4 py-2 text-sm font-semibold">
                                                {{ $checklistLabels[$checklist->status] ?? $checklist->status }}
                                            </span>
                                            @if ($checklist->attached_file_path)
                                                <a href="{{ asset('storage/' . $checklist->attached_file_path) }}"
                                                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-400 transition hover:bg-blue-50 hover:text-blue-700"
                                                    aria-label="ดาวน์โหลดเอกสาร">
                                                    <i data-lucide="download" class="h-5 w-5"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                                    @if ($canUploadDocument)
                                        <form action="{{ route('employers.jobs.documents.store', [$jobOrder->job_number, $checklist->id]) }}"
                                            method="POST" enctype="multipart/form-data"
                                            class="mt-6 rounded-2xl border border-slate-100 bg-slate-50 p-6">
                                            @csrf
                                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-[1fr_1fr_auto] lg:items-end">
                                                <div>
                                                    <label for="document_file_{{ $checklist->id }}" class="text-sm font-semibold text-slate-500">ไฟล์เอกสาร</label>
                                                    <input id="document_file_{{ $checklist->id }}" name="document_file" type="file"
                                                        accept=".pdf,.jpg,.jpeg,.png,.webp" required
                                                        class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-700 file:mr-3 file:rounded-xl file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-base file:font-semibold file:text-blue-700">
                                                </div>
                                                <div>
                                                    <label for="remark_{{ $checklist->id }}" class="text-sm font-semibold text-slate-500">หมายเหตุ</label>
                                                    <input id="remark_{{ $checklist->id }}" name="remark" type="text"
                                                        class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-base text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                                        placeholder="เช่น ส่งฉบับล่าสุด">
                                                </div>
                                                <button type="submit"
                                                    class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-blue-900 px-5 text-base font-semibold text-white transition hover:bg-blue-800 shadow-lg">
                                                    <i data-lucide="upload" class="h-5 w-5"></i>
                                                    ส่งตรวจ
                                                </button>
                                            </div>
                                            <p class="mt-3 text-sm text-slate-400">รองรับ PDF, JPG, PNG, WEBP ขนาดไม่เกิน 10 MB</p>
                                        </form>
                                    @elseif ($checklist->status === 'received')
                                        <div class="mt-6 rounded-2xl border border-blue-100 bg-blue-50 p-6 text-base text-blue-700">
                                            ส่งเอกสารแล้ว รอเจ้าหน้าที่ตรวจสอบ
                                        </div>
                                    @elseif ($checklist->status === 'verified')
                                        <div class="mt-6 rounded-2xl border border-green-100 bg-green-50 p-6 text-base text-green-700">
                                            เอกสารนี้ตรวจผ่านแล้ว
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-6 text-base text-slate-500">
                                    ยังไม่มีรายการเอกสารสำหรับงานนี้
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="space-y-8">
                    <div class="info-card rounded-3xl border border-white/20 bg-white p-8 shadow-xl animate-fade-in-up">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold text-blue-950">ข้อมูลงาน</h2>
                            <i data-lucide="briefcase-business" class="h-6 w-6 text-blue-600"></i>
                        </div>

                        <div class="mt-6 space-y-5 text-base">
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500">นายจ้าง</span>
                                <span class="text-right font-semibold text-slate-800">{{ $jobOrder->employer?->company_name ?: '-' }}</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500">ผู้รับผิดชอบ</span>
                                <span class="text-right font-semibold text-slate-800">{{ $jobOrder->assignedUser?->name ?: '-' }}</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500">สถานะชำระเงิน</span>
                                <span class="text-right font-semibold text-slate-800">{{ $paymentLabels[$jobOrder->payment_status] ?? $jobOrder->payment_status }}</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500">เริ่มงาน</span>
                                <span class="text-right font-semibold text-slate-800">{{ $jobOrder->started_at?->format('d/m/Y') ?: '-' }}</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500">เสร็จสิ้น</span>
                                <span class="text-right font-semibold text-slate-800">{{ $jobOrder->completed_at?->format('d/m/Y') ?: '-' }}</span>
                            </div>
                        </div>

                        @if ($jobOrder->notes)
                            <div class="mt-6 rounded-2xl bg-slate-50 p-5 text-base leading-6 text-slate-600">
                                {{ $jobOrder->notes }}
                            </div>
                        @endif
                    </div>

                    <div class="info-card rounded-3xl border border-white/20 bg-white p-8 shadow-xl animate-fade-in-up">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold text-blue-950">ประวัติชำระเงิน</h2>
                            <i data-lucide="receipt" class="h-6 w-6 text-green-600"></i>
                        </div>

                        @if (! in_array($jobOrder->status, ['completed', 'cancelled', 'rejected'], true) && $jobOrder->payment_status !== 'paid')
                            <form action="{{ route('employers.jobs.payments.store', $jobOrder->job_number) }}" method="POST"
                                enctype="multipart/form-data" class="mt-6 rounded-2xl border border-slate-100 bg-slate-50 p-6">
                                @csrf
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label for="amount" class="text-sm font-semibold text-slate-500">ยอดชำระ</label>
                                        <input id="amount" name="amount" type="number" min="1" step="0.01"
                                            value="{{ old('amount', max($jobOrder->getRemainingAmount(), 0)) }}"
                                            class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-base text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                            required>
                                    </div>
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="payment_date" class="text-sm font-semibold text-slate-500">วันที่ชำระ</label>
                                            <input id="payment_date" name="payment_date" type="date"
                                                value="{{ old('payment_date', now()->toDateString()) }}"
                                                class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-base text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                                required>
                                        </div>
                                        <div>
                                            <label for="payment_method" class="text-sm font-semibold text-slate-500">ช่องทาง</label>
                                            <select id="payment_method" name="payment_method"
                                                class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-base text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                                required>
                                                <option value="transfer" @selected(old('payment_method') === 'transfer')>โอนเงิน</option>
                                                <option value="promptpay" @selected(old('payment_method') === 'promptpay')>พร้อมเพย์</option>
                                                <option value="credit_card" @selected(old('payment_method') === 'credit_card')>บัตรเครดิต</option>
                                                <option value="cash" @selected(old('payment_method') === 'cash')>เงินสด</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="payment_reference" class="text-sm font-semibold text-slate-500">เลขอ้างอิง</label>
                                        <input id="payment_reference" name="payment_reference" type="text"
                                            value="{{ old('payment_reference') }}"
                                            class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-base text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                            placeholder="เช่น เลขรายการโอน">
                                    </div>
                                    <div>
                                        <label for="slip_file" class="text-sm font-semibold text-slate-500">สลิป / หลักฐานชำระเงิน</label>
                                        <input id="slip_file" name="slip_file" type="file" accept=".jpg,.jpeg,.png,.webp,.pdf"
                                            class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-700 file:mr-3 file:rounded-xl file:border-0 file:bg-green-50 file:px-4 file:py-2 file:text-base file:font-semibold file:text-green-700"
                                            required>
                                    </div>
                                    <div>
                                        <label for="payment_note" class="text-sm font-semibold text-slate-500">หมายเหตุ</label>
                                        <input id="payment_note" name="note" type="text" value="{{ old('note') }}"
                                            class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-base text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                            placeholder="เช่น ชำระงวดแรก">
                                    </div>
                                </div>
                                <button type="submit"
                                    class="mt-5 inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-green-700 px-4 text-base font-semibold text-white transition hover:bg-green-800 shadow-lg">
                                    <i data-lucide="upload" class="h-5 w-5"></i>
                                    ส่งสลิปให้ตรวจสอบ
                                </button>
                                <p class="mt-3 text-sm text-slate-400">รองรับ PDF, JPG, PNG, WEBP ขนาดไม่เกิน 10 MB</p>
                            </form>
                        @endif

                        <div class="mt-6 space-y-4">
                            @forelse ($jobOrder->payments as $payment)
                                <div class="rounded-2xl border border-slate-100 p-5">
                                    <div class="flex items-center justify-between gap-4">
                                        <p class="text-lg font-semibold text-slate-800">{{ number_format($payment->amount, 2) }} บาท</p>
                                        <span class="{{ $paymentStatusStyles[$payment->status] ?? 'bg-slate-100 text-slate-700' }} rounded-full px-4 py-2 text-sm font-semibold">
                                            {{ $paymentStatusLabels[$payment->status] ?? $payment->status }}
                                        </span>
                                    </div>
                                    <p class="mt-3 text-sm text-slate-500">
                                        {{ $payment->payment_date?->format('d/m/Y') }} · {{ $paymentMethodLabels[$payment->payment_method] ?? $payment->payment_method }}
                                    </p>
                                    @if ($payment->payment_reference)
                                        <p class="mt-1 text-sm text-slate-500">อ้างอิง: {{ $payment->payment_reference }}</p>
                                    @endif
                                    @if ($payment->note)
                                        <p class="mt-2 text-base text-slate-600">{{ $payment->note }}</p>
                                    @endif
                                    @if ($payment->slip_path)
                                        <a href="{{ asset('storage/' . $payment->slip_path) }}" target="_blank"
                                            class="mt-4 inline-flex items-center gap-2 text-base font-semibold text-green-700 hover:text-green-900">
                                            <i data-lucide="external-link" class="h-5 w-5"></i>
                                            เปิดสลิป
                                        </a>
                                    @endif
                                </div>
                            @empty
                                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5 text-base text-slate-500">
                                    ยังไม่มีประวัติชำระเงิน
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="info-card rounded-3xl border border-white/20 bg-white p-8 shadow-xl animate-fade-in-up">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold text-blue-950">ประวัติอัปเดต</h2>
                            <i data-lucide="history" class="h-6 w-6 text-blue-600"></i>
                        </div>

                        <div class="mt-6 space-y-4">
                            @forelse ($jobOrder->logs->sortByDesc('created_at') as $log)
                                <div class="flex gap-4">
                                    <div class="mt-2 h-3 w-3 shrink-0 rounded-full bg-blue-600"></div>
                                    <div>
                                        <p class="text-base font-semibold text-slate-800">{{ $log->action }}</p>
                                        @if ($log->description)
                                            <p class="mt-1 text-base text-slate-500">{{ $log->description }}</p>
                                        @endif
                                        <p class="mt-1 text-sm text-slate-400">{{ $log->created_at?->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5 text-base text-slate-500">
                                    ยังไม่มีประวัติอัปเดต
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
