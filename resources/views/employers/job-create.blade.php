@php
    $title = 'แจ้งงานใหม่';

    $priorityLabels = [
        'low' => 'ปกติ',
        'medium' => 'มาตรฐาน',
        'high' => 'เร่งด่วน',
        'urgent' => 'ด่วนมาก',
    ];
@endphp

@extends('layouts.app')

@push('head')
<style>
    .hero-gradient {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 25%, #3b82f6 50%, #dc2626 75%, #991b1b 100%);
    }

    .form-card {
        transition: all 0.3s ease;
    }

    .form-card:hover {
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
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
                <p class="text-sm font-semibold text-blue-200 uppercase tracking-wider">NEW JOB REQUEST</p>
                <h1 class="mt-4 text-5xl lg:text-6xl font-bold leading-tight">แจ้งงานใหม่</h1>
                <p class="mt-6 max-w-3xl text-xl leading-8 text-white/90">
                    เลือกบริการและลูกจ้างที่ต้องการดำเนินงาน ระบบจะสร้างเลขงานใหม่เพื่อให้ติดตามสถานะได้ทันที
                </p>
            </div>
        </div>
    </section>

    <!-- Form Section -->
    <section class="bg-slate-50 py-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-8 rounded-3xl border border-red-100 bg-red-50 p-6 text-base font-medium text-red-700 shadow-xl animate-fade-in-up">
                    กรุณาตรวจสอบข้อมูลที่กรอกอีกครั้ง
                </div>
            @endif

            <form action="{{ route('employers.jobs.store') }}" method="POST"
                class="form-card rounded-3xl border border-white/20 bg-white p-8 sm:p-10 shadow-2xl animate-fade-in-up">
                @csrf

                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                    <div>
                        <label for="employer_id" class="text-base font-semibold text-slate-700">บริษัทนายจ้าง</label>
                        <select id="employer_id" name="employer_id" required
                            class="mt-3 h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-base text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            @foreach ($employers as $employer)
                                <option value="{{ $employer->id }}" @selected(old('employer_id') == $employer->id)>
                                    {{ $employer->company_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('employer_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="worker_id" class="text-base font-semibold text-slate-700">ลูกจ้าง</label>
                        <select id="worker_id" name="worker_id" required
                            class="mt-3 h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-base text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <option value="">เลือกลูกจ้าง</option>
                            @foreach ($workers as $worker)
                                <option value="{{ $worker->id }}" data-employer-id="{{ $worker->employer_id }}"
                                    @selected(old('worker_id') == $worker->id)>
                                    {{ $worker->full_name_th ?: $worker->full_name_en }} · {{ $worker->passport_number }}
                                </option>
                            @endforeach
                        </select>
                        @error('worker_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="service_id" class="text-base font-semibold text-slate-700">บริการที่ต้องการ</label>
                        <select id="service_id" name="service_id" required
                            class="mt-3 h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-base text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <option value="">เลือกบริการ</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>
                                    {{ $service->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('service_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="priority" class="text-base font-semibold text-slate-700">ความเร่งด่วน</label>
                        <select id="priority" name="priority" required
                            class="mt-3 h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-base text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            @foreach ($priorityLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('priority', 'medium') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('priority')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="due_date" class="text-base font-semibold text-slate-700">วันที่ต้องการให้แล้วเสร็จ</label>
                        <input id="due_date" name="due_date" type="date" value="{{ old('due_date') }}"
                            class="mt-3 h-12 w-full rounded-2xl border border-slate-200 px-4 text-base text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        @error('due_date')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="notes" class="text-base font-semibold text-slate-700">หมายเหตุเพิ่มเติม</label>
                        <textarea id="notes" name="notes" rows="5"
                            class="mt-3 w-full rounded-2xl border border-slate-200 px-4 py-4 text-base text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            placeholder="ระบุรายละเอียดที่ต้องการแจ้งเจ้าหน้าที่">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-10 flex flex-col-reverse gap-4 border-t border-slate-100 pt-8 sm:flex-row sm:justify-end">
                    <a href="{{ route('employers.dashboard') }}"
                        class="inline-flex h-12 items-center justify-center rounded-2xl border border-slate-200 px-6 text-base font-medium text-slate-600 transition hover:bg-slate-50">
                        ยกเลิก
                    </a>
                    <button type="submit"
                        class="inline-flex h-12 items-center justify-center gap-3 rounded-2xl bg-blue-900 px-6 text-base font-medium text-white transition hover:bg-blue-800 shadow-lg">
                        <i data-lucide="send" class="h-5 w-5"></i>
                        ส่งแจ้งงาน
                    </button>
                </div>
            </form>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        const employerSelect = document.getElementById('employer_id');
        const workerSelect = document.getElementById('worker_id');

        function filterWorkers() {
            const employerId = employerSelect.value;

            Array.from(workerSelect.options).forEach((option) => {
                if (!option.value) {
                    return;
                }

                option.hidden = option.dataset.employerId !== employerId;
            });

            const selectedOption = workerSelect.selectedOptions[0];
            if (selectedOption && selectedOption.hidden) {
                workerSelect.value = '';
            }
        }

        employerSelect.addEventListener('change', filterWorkers);
        filterWorkers();
    </script>
@endpush
