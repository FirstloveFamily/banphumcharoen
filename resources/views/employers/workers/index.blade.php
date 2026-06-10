@php
    $title = 'รายชื่อแรงงาน';

    $expiryLabels = [
        '' => 'ทั้งหมด',
        'expiring' => 'ใกล้หมดอายุ',
        'expired' => 'หมดอายุแล้ว',
    ];
@endphp

@extends('layouts.app')

@push('head')
<style>
    .hero-gradient {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 25%, #3b82f6 50%, #dc2626 75%, #991b1b 100%);
    }

    .table-row {
        transition: all 0.3s ease;
    }

    .table-row:hover {
        background: linear-gradient(135deg, rgba(30, 58, 138, 0.04) 0%, rgba(220, 38, 38, 0.04) 100%);
        transform: scale(1.01);
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
            <div class="mt-8">
                <p class="text-sm font-semibold text-blue-200 uppercase tracking-wider">WORKERS</p>
                <h1 class="mt-4 text-5xl lg:text-6xl font-bold leading-tight">รายชื่อแรงงาน</h1>
                <p class="mt-6 max-w-3xl text-xl leading-8 text-white/90">
                    ดูข้อมูลแรงงานของบริษัท ประวัติใบงาน และวันหมดอายุเอกสารสำคัญ
                </p>
            </div>
        </div>
    </section>

    <!-- Content Section -->
    <section class="bg-slate-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="GET" class="rounded-3xl border border-white/20 bg-white p-6 shadow-xl animate-fade-in-up">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_240px_auto] lg:items-end">
                    <div>
                        <label for="q" class="text-base font-semibold text-slate-700">ค้นหาแรงงาน</label>
                        <input id="q" name="q" type="text" value="{{ $keyword }}"
                            placeholder="ชื่อ, Passport, เลข Work Permit"
                            class="mt-3 h-12 w-full rounded-2xl border border-slate-200 px-5 text-base text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </div>
                    <div>
                        <label for="expiry" class="text-base font-semibold text-slate-700">สถานะเอกสาร</label>
                        <select id="expiry" name="expiry"
                            class="mt-3 h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-base text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            @foreach ($expiryLabels as $value => $label)
                                <option value="{{ $value }}" @selected($expiryStatus === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                        class="inline-flex h-12 items-center justify-center gap-3 rounded-2xl bg-blue-900 px-6 text-base font-semibold text-white transition hover:bg-blue-800 shadow-lg">
                        <i data-lucide="search" class="h-5 w-5"></i>
                        ค้นหา
                    </button>
                </div>
            </form>

            <div class="mt-8 overflow-hidden rounded-3xl border border-white/20 bg-white shadow-xl animate-fade-in-up">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[920px] text-left text-base">
                        <thead class="bg-slate-50 text-slate-600 font-semibold">
                            <tr>
                                <th class="px-6 py-5">แรงงาน</th>
                                <th class="px-6 py-5">สัญชาติ</th>
                                <th class="px-6 py-5">Passport</th>
                                <th class="px-6 py-5">Work Permit</th>
                                <th class="px-6 py-5">Visa</th>
                                <th class="px-6 py-5">90 วัน</th>
                                <th class="px-6 py-5">ใบงาน</th>
                                <th class="px-6 py-5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($workers as $worker)
                                @php
                                    $initials = mb_substr($worker->first_name_th ?: $worker->first_name_en ?: '-', 0, 1)
                                        . mb_substr($worker->last_name_th ?: $worker->last_name_en ?: '', 0, 1);
                                    $passportAttachment = $worker->passportAttachment();
                                @endphp
                                <tr class="table-row">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-4">
                                            @if ($worker->photo_path)
                                                <img src="{{ asset('storage/' . $worker->photo_path) }}"
                                                    alt="{{ $worker->full_name_th ?: $worker->full_name_en }}"
                                                    class="h-14 w-14 rounded-full object-cover ring-4 ring-slate-100 shadow-lg">
                                            @else
                                                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-blue-50 text-base font-bold text-blue-700 ring-4 ring-slate-100 shadow-lg">
                                                    {{ $initials }}
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-semibold text-slate-800 text-base">{{ $worker->full_name_th ?: $worker->full_name_en }}</p>
                                                <p class="mt-1 text-sm text-slate-400">{{ $worker->full_name_en ?: '-' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-slate-600">{{ $worker->nationality?->name_th ?: '-' }}</td>
                                    <td class="px-6 py-5">
                                        <p class="font-semibold text-slate-700">{{ $worker->passport_number ?: '-' }}</p>
                                        <p class="mt-1 text-sm text-slate-400">{{ $worker->passport_expiry?->format('d/m/Y') ?: '-' }}</p>
                                        @if ($passportAttachment)
                                            <a href="{{ $passportAttachment['url'] }}" target="_blank"
                                                class="mt-3 inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-100">
                                                <i data-lucide="paperclip" class="h-3.5 w-3.5"></i>
                                                {{ $passportAttachment['label'] }}
                                            </a>
                                        @else
                                            <p class="mt-3 text-xs font-medium text-slate-300">ไม่มีไฟล์พาสปอร์ต</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5">
                                        <p class="font-semibold text-slate-700">{{ $worker->wp_number ?: '-' }}</p>
                                        <p class="mt-1 text-sm text-slate-400">{{ $worker->wp_expiry?->format('d/m/Y') ?: '-' }}</p>
                                    </td>
                                    <td class="px-6 py-5 text-slate-600">{{ $worker->visa_expiry?->format('d/m/Y') ?: '-' }}</td>
                                    <td class="px-6 py-5 text-slate-600">{{ $worker->report_90_days_due?->format('d/m/Y') ?: '-' }}</td>
                                    <td class="px-6 py-5 text-slate-600">{{ number_format($worker->job_orders_count) }}</td>
                                    <td class="px-6 py-5 text-right">
                                        <a href="{{ route('employers.workers.show', $worker) }}"
                                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-400 transition hover:bg-blue-50 hover:text-blue-700"
                                            aria-label="ดูประวัติแรงงาน">
                                            <i data-lucide="eye" class="h-5 w-5"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-16 text-center">
                                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-blue-700 shadow-lg">
                                            <i data-lucide="users" class="h-8 w-8"></i>
                                        </div>
                                        <h2 class="mt-6 text-2xl font-bold text-blue-950">ไม่พบรายชื่อแรงงาน</h2>
                                        <p class="mt-3 text-base text-slate-500">ลองปรับคำค้นหาหรือตัวกรองเอกสารอีกครั้ง</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-8">
                {{ $workers->links() }}
            </div>
        </div>
    </section>
@endsection
