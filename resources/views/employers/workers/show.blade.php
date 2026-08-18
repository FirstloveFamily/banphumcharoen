@php
    $title = 'ประวัติแรงงาน ' . ($worker->full_name_th ?: $worker->full_name_en);

    $jobStatusLabels = [
        'pending' => 'รอเริ่มงาน',
        'processing' => 'กำลังดำเนินการ',
        'waiting_document' => 'รอเอกสาร',
        'approved' => 'อนุมัติแล้ว',
        'completed' => 'เสร็จสิ้น',
        'cancelled' => 'ยกเลิก',
        'rejected' => 'ไม่ผ่าน',
    ];

    $jobStatusStyles = [
        'pending' => 'bg-slate-100 text-slate-700',
        'processing' => 'bg-orange-50 text-orange-700',
        'waiting_document' => 'bg-blue-50 text-blue-700',
        'approved' => 'bg-emerald-50 text-emerald-700',
        'completed' => 'bg-green-50 text-green-700',
        'cancelled' => 'bg-slate-100 text-slate-500',
        'rejected' => 'bg-red-50 text-red-700',
    ];

    $dateItems = [
        ['label' => 'Passport', 'date' => $worker->passport_expiry, 'icon' => 'book-open-check'],
        ['label' => 'บัตรชมพู', 'date' => $worker->pink_card_expiry, 'icon' => 'contact'],
        ['label' => 'Work Permit', 'date' => $worker->wp_expiry, 'icon' => 'badge-check'],
        ['label' => 'Visa', 'date' => $worker->visa_expiry, 'icon' => 'stamp'],
        ['label' => 'รายงาน 90 วัน', 'date' => $worker->report_90_days_due, 'icon' => 'calendar-clock'],
    ];

    $legacyAttachments = $worker->legacyAttachments();
    $otherDocuments = $worker->documents->reject(fn ($document) => $worker->isPassportDocument($document))->values();

    $initials = mb_substr($worker->first_name_th ?: $worker->first_name_en ?: '-', 0, 1)
        . mb_substr($worker->last_name_th ?: $worker->last_name_en ?: '', 0, 1);
@endphp

@extends('layouts.app')

@push('head')
<style>
    .hero-gradient {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 25%, #3b82f6 50%, #dc2626 75%, #991b1b 100%);
    }

    .date-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .date-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
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
            <a href="{{ route('employers.workers.index') }}"
                class="inline-flex items-center gap-3 text-base font-semibold text-blue-100 transition hover:text-white">
                <i data-lucide="arrow-left" class="h-5 w-5"></i>
                กลับรายชื่อแรงงาน
            </a>
            <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-12 lg:items-end">
                <div class="lg:col-span-8">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-center">
                        @if ($worker->photo_path)
                            <img src="{{ asset('storage/' . $worker->photo_path) }}"
                                alt="{{ $worker->full_name_th ?: $worker->full_name_en }}"
                                class="h-28 w-28 rounded-3xl object-cover ring-6 ring-white/20 shadow-2xl">
                        @else
                            <div class="flex h-28 w-28 items-center justify-center rounded-3xl bg-white/10 text-3xl font-bold text-white ring-6 ring-white/20 shadow-2xl">
                                {{ $initials }}
                            </div>
                        @endif
                        <div>
                            <p class="text-sm font-semibold text-blue-200 uppercase tracking-wider">WORKER PROFILE</p>
                            <h1 class="mt-4 text-5xl lg:text-6xl font-bold leading-tight">{{ $worker->full_name_th ?: $worker->full_name_en }}</h1>
                            <p class="mt-4 text-xl leading-8 text-white/90">
                                {{ $worker->full_name_en ?: 'ข้อมูลแรงงานและประวัติการดำเนินงาน' }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-4 lg:text-right">
                    <span class="inline-flex rounded-full bg-white/10 px-6 py-3 text-base font-semibold text-blue-50 border border-white/20 shadow-lg">
                        {{ $worker->employer?->company_name }}
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Content Section -->
    <section class="bg-slate-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Date Cards -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-4 mb-12">
                @foreach ($dateItems as $item)
                    @php
                        $date = $item['date'];
                        $isExpired = $date && $date->isPast();
                        $isExpiring = $date && ! $isExpired && $date->lte(now()->addDays(45));
                    @endphp
                    <div class="date-card rounded-3xl border border-white/20 bg-white p-6 shadow-xl animate-fade-in-up">
                        <div class="flex items-center justify-between">
                            <p class="text-base font-semibold text-slate-500">{{ $item['label'] }}</p>
                            <i data-lucide="{{ $item['icon'] }}" class="h-6 w-6 {{ $isExpired ? 'text-red-500' : ($isExpiring ? 'text-orange-500' : 'text-blue-600') }}"></i>
                        </div>
                        <p class="mt-4 text-3xl font-bold {{ $isExpired ? 'text-red-600' : ($isExpiring ? 'text-orange-600' : 'text-blue-950') }}">
                            {{ $date?->format('d/m/Y') ?: '-' }}
                        </p>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <div class="space-y-8 lg:col-span-2">
                    <div class="info-card rounded-3xl border border-white/20 bg-white p-8 shadow-xl animate-fade-in-up">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-blue-950">ข้อมูลแรงงาน</h2>
                                <p class="mt-2 text-base text-slate-500">ข้อมูลหลักที่ใช้ประกอบใบงาน</p>
                            </div>
                            <i data-lucide="user-round" class="h-6 w-6 text-blue-600"></i>
                        </div>

                        <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">ชื่อภาษาไทย</p>
                                <p class="mt-2 text-base font-semibold text-slate-800">{{ $worker->full_name_th ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">ชื่อภาษาอังกฤษ</p>
                                <p class="mt-2 text-base font-semibold text-slate-800">{{ $worker->full_name_en ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">สัญชาติ</p>
                                <p class="mt-2 text-base font-semibold text-slate-800">{{ $worker->nationality?->name_th ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">เพศ</p>
                                <p class="mt-2 text-base font-semibold text-slate-800">{{ $worker->gender ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">วันเกิด</p>
                                <p class="mt-2 text-base font-semibold text-slate-800">{{ $worker->birth_date?->format('d/m/Y') ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">เลข Passport</p>
                                <p class="mt-2 text-base font-semibold text-slate-800">{{ $worker->passport_number ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">เลข Work Permit</p>
                                <p class="mt-2 text-base font-semibold text-slate-800">{{ $worker->wp_number ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">สถานะ</p>
                                <p class="mt-2 text-base font-semibold text-slate-800">{{ $worker->is_active ? 'ใช้งานอยู่' : 'ไม่ใช้งาน' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="info-card rounded-3xl border border-white/20 bg-white p-8 shadow-xl animate-fade-in-up">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-blue-950">ประวัติใบงาน</h2>
                                <p class="mt-2 text-base text-slate-500">รายการงานทั้งหมดของแรงงานรายนี้</p>
                            </div>
                            <i data-lucide="history" class="h-6 w-6 text-blue-600"></i>
                        </div>

                        <div class="mt-8 divide-y divide-slate-100">
                            @forelse ($worker->jobOrders as $jobOrder)
                                <div class="py-6">
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <a href="{{ route('employers.jobs.show', $jobOrder->job_number) }}"
                                                class="text-lg font-semibold text-blue-900 transition hover:text-blue-700">
                                                {{ $jobOrder->job_number }}
                                            </a>
                                            <p class="mt-2 text-base text-slate-500">{{ $jobOrder->service?->name ?: '-' }}</p>
                                            <p class="mt-1 text-sm text-slate-400">อัปเดต {{ $jobOrder->updated_at?->format('d/m/Y H:i') }}</p>
                                        </div>
                                        <div class="text-left sm:text-right">
                                            <span class="{{ $jobStatusStyles[$jobOrder->status] ?? 'bg-slate-100 text-slate-700' }} rounded-full px-4 py-2 text-sm font-semibold">
                                                {{ $jobStatusLabels[$jobOrder->status] ?? $jobOrder->status }}
                                            </span>
                                            <p class="mt-3 text-base text-slate-500">
                                                คงเหลือ {{ number_format($jobOrder->getRemainingAmount(), 2) }} บาท
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-6 text-base text-slate-500">
                                    ยังไม่มีประวัติใบงานของแรงงานรายนี้
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="space-y-8">
                    <div class="info-card rounded-3xl border border-white/20 bg-white p-8 shadow-xl animate-fade-in-up">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-blue-950">เอกสารแรงงาน</h2>
                                <p class="mt-2 text-base text-slate-500">ไฟล์ประจำตัวที่บันทึกไว้</p>
                            </div>
                            <i data-lucide="files" class="h-6 w-6 text-blue-600"></i>
                        </div>

                        <div class="mt-6 space-y-4">
                            @if ($legacyAttachments->contains(fn ($attachment) => filled($attachment['file'])) || $otherDocuments->isNotEmpty())
                                @if ($legacyAttachments->contains(fn ($attachment) => filled($attachment['file'])))
                                    <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5">
                                        <p class="text-base font-semibold text-blue-900">เอกสารแนบ (Attachments)</p>
                                        <div class="mt-4 space-y-3">
                                            @foreach ($legacyAttachments as $attachment)
                                                @if (! filled($attachment['file']))
                                                    @continue
                                                @endif
                                                <div class="rounded-xl border border-blue-100 bg-white/70 p-4">
                                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                                        <div>
                                                            <p class="text-sm font-semibold text-blue-900">
                                                                {{ $attachment['name'] }}
                                                            </p>
                                                            <p class="mt-2 text-sm text-blue-700">
                                                                หมดอายุ {{ $attachment['expiry_date']?->format('d/m/Y') ?: '-' }}
                                                            </p>
                                                        </div>
                                                        <a href="{{ $attachment['url'] }}" target="_blank"
                                                            class="inline-flex items-center gap-3 rounded-full bg-white px-4 py-2 text-sm font-semibold text-blue-700 shadow-sm transition hover:bg-blue-100">
                                                            <i data-lucide="external-link" class="h-4 w-4"></i>
                                                            เปิดไฟล์
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @foreach ($otherDocuments as $document)
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                                        <p class="text-base font-semibold text-slate-800">{{ $document->documentMaster?->name ?: 'เอกสาร' }}</p>
                                        <p class="mt-2 text-sm text-slate-500">หมดอายุ {{ $document->expiry_date?->format('d/m/Y') ?: '-' }}</p>
                                        @if ($document->file_path)
                                            <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank"
                                                class="mt-4 inline-flex items-center gap-3 text-base font-semibold text-blue-700 hover:text-blue-900">
                                                <i data-lucide="download" class="h-5 w-5"></i>
                                                เปิดไฟล์
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5 text-base text-slate-500">
                                    ยังไม่มีเอกสารประจำตัวในระบบ
                                </div>
                            @endif
                        </div>
                    </div>

                    <a href="{{ route('employers.jobs.create') }}"
                        class="flex items-center justify-between rounded-3xl bg-blue-900 p-6 text-white shadow-xl transition hover:bg-blue-800 animate-fade-in-up">
                        <span class="text-base font-semibold">แจ้งงานใหม่ให้แรงงานรายนี้</span>
                        <i data-lucide="arrow-right" class="h-6 w-6"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
