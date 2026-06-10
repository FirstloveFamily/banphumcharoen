@php
    use Illuminate\Support\Str;

    $title = 'ตรวจสอบสถานะงาน';

    $statusLabels = [
        'pending' => 'รับงานแล้ว',
        'waiting_document' => 'รอเอกสาร',
        'approved' => 'อนุมัติเอกสาร',
        'processing' => 'กำลังดำเนินการ',
        'completed' => 'เสร็จสิ้น',
        'cancelled' => 'ยกเลิก',
        'rejected' => 'ปฏิเสธ',
    ];

    $statusSteps = [
        'pending' => 'รับงานแล้ว',
        'waiting_document' => 'ตรวจสอบเอกสาร',
        'approved' => 'อนุมัติเอกสาร',
        'processing' => 'กำลังดำเนินการ',
        'completed' => 'เสร็จสิ้น',
    ];

    $statusProgress = [
        'pending' => 1,
        'waiting_document' => 2,
        'approved' => 3,
        'processing' => 4,
        'completed' => 5,
        'cancelled' => 1,
        'rejected' => 2,
    ];

    $currentStatus = $jobOrder?->status;
    $currentProgress = $statusProgress[$currentStatus] ?? 0;
    $isStopped = in_array($currentStatus, ['cancelled', 'rejected'], true);

    $workerName = $jobOrder?->worker?->full_name_th ?: $jobOrder?->worker?->full_name_en;
    $maskedWorkerName = $workerName
        ? Str::substr($workerName, 0, 1) . str_repeat('*', 4) . ' ' . Str::substr($workerName, -1)
        : '-';
@endphp

@extends('layouts.app')

@push('head')
<style>
    .hero-gradient {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 25%, #3b82f6 50%, #dc2626 75%, #991b1b 100%);
    }

    .search-card {
        transition: all 0.3s ease;
    }

    .search-card:focus-within {
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .btn-primary {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(30, 58, 138, 0.3);
    }

    .status-card {
        transition: all 0.3s ease;
    }

    .status-card:hover {
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
    }

    .step-circle {
        transition: all 0.3s ease;
    }

    .step-circle:hover {
        transform: scale(1.1);
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
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

    .animate-delay-100 {
        animation-delay: 0.1s;
    }

    .animate-delay-200 {
        animation-delay: 0.2s;
    }
</style>
@endpush

@section('content')
    <!-- Hero Section -->
    <section class="hero-gradient py-20 lg:py-24 relative overflow-hidden">
        <!-- Decorative Elements -->
        <div class="absolute inset-0 opacity-20 pointer-events-none">
            <div class="absolute -left-40 -top-40 h-[600px] w-[600px] rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute bottom-[-12rem] right-[-10rem] h-[500px] w-[500px] rounded-full bg-red-500/20 blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[700px] w-[700px] rounded-full bg-blue-400/10 blur-3xl"></div>
        </div>

        <!-- Grid Pattern -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,.04)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.04)_1px,transparent_1px)] bg-[size:60px_60px] opacity-30"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center text-white">
            <h1 class="text-5xl lg:text-6xl font-bold mb-6 leading-tight tracking-tight">ตรวจสอบสถานะงาน</h1>
            <p class="text-xl lg:text-2xl text-white/90 font-medium max-w-3xl mx-auto leading-relaxed">
                ค้นหาด้วยเลขงานเพื่อติดตามความคืบหน้าแบบเรียลไทม์
            </p>
        </div>
    </section>

    <!-- Search Section -->
    <section class="bg-slate-50 py-10 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 relative z-20">
            <form action="{{ route('status.index') }}" method="GET"
                class="search-card bg-white rounded-3xl border border-white/20 shadow-xl p-6 sm:p-8 animate-fade-in-up">
                <div class="flex flex-col sm:flex-row gap-4">
                    <label class="relative flex-1">
                        <span class="sr-only">เลขที่งาน</span>
                        <i data-lucide="search"
                            class="absolute left-5 top-1/2 -translate-y-1/2 w-6 h-6 text-slate-400"></i>
                        <input type="text" name="job_number" value="{{ $keyword }}"
                            placeholder="{{ $latestJobNumber ? 'เช่น ' . $latestJobNumber : 'กรอกเลขงาน เช่น AP260528001' }}"
                            class="w-full h-14 rounded-2xl border border-slate-300 bg-white pl-14 pr-5 text-base font-medium text-slate-800 outline-none transition focus:border-blue-700 focus:ring-4 focus:ring-blue-100">
                    </label>
                    <button type="submit"
                        class="btn-primary h-14 rounded-2xl px-8 text-base font-semibold text-white shadow-lg">
                        ค้นหาข้อมูล
                    </button>
                    <button type="button"
                        class="h-14 rounded-2xl border border-slate-200 bg-slate-50 px-6 text-base font-medium text-slate-700 transition hover:bg-slate-100 hover:shadow-md">
                        <span class="inline-flex items-center gap-2">
                            <i data-lucide="qr-code" class="w-5 h-5"></i>
                            สแกน QR
                        </span>
                    </button>
                </div>
            </form>

            @if ($keyword !== '' && ! $jobOrder)
                <div class="mt-8 rounded-3xl border border-red-100 bg-white p-10 text-center shadow-xl animate-fade-in-up animate-delay-100">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-50 text-red-600 shadow-lg">
                        <i data-lucide="file-search" class="w-8 h-8"></i>
                    </div>
                    <h2 class="mt-6 text-2xl font-bold text-slate-900">ไม่พบเลขงานนี้</h2>
                    <p class="mt-3 text-base text-slate-500">กรุณาตรวจสอบเลขงานอีกครั้ง หรือติดต่อเจ้าหน้าที่เพื่อสอบถามข้อมูล</p>
                </div>
            @elseif ($jobOrder)
                <article class="status-card mt-8 overflow-hidden rounded-3xl border border-white/20 bg-white shadow-xl animate-fade-in-up animate-delay-100">
                    <header class="flex flex-col gap-6 border-b border-slate-100 p-8 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <span
                                class="inline-flex rounded-full bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-800">สถานะงาน</span>
                            <h2 class="mt-4 text-3xl font-bold text-blue-950">
                                เลขที่งาน: {{ $jobOrder->job_number }}
                            </h2>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-4 text-center shadow-md">
                            <p class="text-sm text-slate-400 font-medium">สถานะหลัก</p>
                            <p class="text-xl font-bold {{ $isStopped ? 'text-red-600' : 'text-blue-950' }}">
                                {{ $statusLabels[$currentStatus] ?? $currentStatus }}
                            </p>
                        </div>
                    </header>

                    <div class="grid grid-cols-1 border-b border-slate-100 md:grid-cols-2">
                        <div class="p-8">
                            <p class="text-sm font-semibold text-slate-400 uppercase tracking-wider">ชื่อลูกจ้าง (Worker Name)</p>
                            <p class="mt-3 text-xl font-bold text-slate-900">{{ $maskedWorkerName }}</p>
                        </div>
                        <div class="border-t border-slate-100 p-8 md:border-l md:border-t-0">
                            <p class="text-sm font-semibold text-slate-400 uppercase tracking-wider">ประเภทงาน (Job Type)</p>
                            <p class="mt-3 text-xl font-bold text-slate-900">{{ $jobOrder->service?->name ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="border-b border-slate-100 p-8">
                        <h3 class="text-lg font-bold text-slate-900 mb-6">ความคืบหน้าการทำงาน</h3>
                        <div class="grid grid-cols-2 gap-6 sm:grid-cols-5">
                            @foreach ($statusSteps as $status => $label)
                                @php
                                    $stepNumber = $loop->iteration;
                                    $isDone = $currentProgress >= $stepNumber && ! $isStopped;
                                    $isCurrent = $currentStatus === $status;
                                @endphp
                                <div class="text-center">
                                    <div
                                        class="step-circle mx-auto flex h-14 w-14 items-center justify-center rounded-full border-4 {{ $isDone ? ($isCurrent ? 'border-blue-100 bg-blue-900 text-white shadow-lg' : 'border-green-100 bg-green-500 text-white shadow-md') : 'border-slate-100 bg-slate-200 text-slate-500' }}">
                                        @if ($isDone)
                                            <i data-lucide="check" class="w-7 h-7"></i>
                                        @else
                                            <span class="text-sm font-bold">{{ $stepNumber }}</span>
                                        @endif
                                    </div>
                                    <p class="mt-3 text-sm font-semibold text-slate-900">{{ $label }}</p>
                                </div>
                            @endforeach
                        </div>

                        @if ($isStopped)
                            <div class="mt-8 rounded-2xl border border-red-100 bg-red-50 px-6 py-4 text-base text-red-700 shadow-sm">
                                งานนี้อยู่ในสถานะ{{ $statusLabels[$currentStatus] }} กรุณาติดต่อเจ้าหน้าที่เพื่อดูรายละเอียบเพิ่มเติม
                            </div>
                        @endif
                    </div>

                    <div class="p-8">
                        <h3 class="text-lg font-bold text-slate-900 mb-6">ประวัติการดำเนินงาน (Timeline)</h3>
                        <div class="mt-6 space-y-0 border-t border-slate-100 pt-6">
                            @forelse ($jobOrder->logs as $log)
                                <div class="relative grid grid-cols-[3rem_1fr] gap-4 pb-8">
                                    @if (! $loop->last)
                                        <div class="absolute left-6 top-10 h-full w-0.5 bg-gradient-to-b from-blue-300 to-transparent"></div>
                                    @endif
                                    <div
                                        class="relative z-10 mt-3 flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 shadow-md">
                                        <span class="h-4 w-4 rounded-full bg-blue-800"></span>
                                    </div>
                                    <div class="pt-3">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <p class="font-bold text-lg text-blue-950">{{ $log->action }}</p>
                                            <span class="text-sm text-slate-400 font-medium">
                                                {{ $log->created_at?->format('d/m/Y H:i') }} น.
                                            </span>
                                        </div>
                                        @if ($log->description)
                                            <p class="mt-2 text-base text-slate-500">{{ $log->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="relative grid grid-cols-[3rem_1fr] gap-4 pb-4">
                                    <div class="relative z-10 mt-3 flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 shadow-md">
                                        <span class="h-4 w-4 rounded-full bg-blue-800"></span>
                                    </div>
                                    <div class="pt-3">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <p class="font-bold text-lg text-blue-950">รับงานแล้ว</p>
                                            <span class="text-sm text-slate-400 font-medium">
                                                {{ $jobOrder->created_at?->format('d/m/Y H:i') }} น.
                                            </span>
                                        </div>
                                        <p class="mt-2 text-base text-slate-500">รับงานเข้าสู่ระบบแล้ว</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </article>
            @else
                <div class="mt-8 rounded-3xl border border-blue-100 bg-white p-10 text-center shadow-xl animate-fade-in-up animate-delay-100">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-blue-800 shadow-lg">
                        <i data-lucide="clipboard-list" class="w-8 h-8"></i>
                    </div>
                    <h2 class="mt-6 text-2xl font-bold text-slate-900">กรอกเลขงานเพื่อเริ่มตรวจสอบ</h2>
                    <p class="mt-3 text-base text-slate-500">เลขงานสามารถดูได้จากใบรับงาน หรือข้อความแจ้งเตือนจากเจ้าหน้าที่</p>
                </div>
            @endif
        </div>
    </section>
@endsection
