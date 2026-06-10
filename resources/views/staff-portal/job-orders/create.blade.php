@extends('layouts.staff-portal', ['title' => 'เพิ่มใบงานใหม่', 'pageTitle' => 'จัดการใบงาน (Job Orders)'])

@section('content')
    @php
        $priorityLabels = [
            'low' => 'ต่ำ',
            'medium' => 'ปานกลาง',
            'high' => 'สูง',
            'urgent' => 'ด่วนพิเศษ',
        ];
    @endphp

    <div class="space-y-8 max-w-6xl mx-auto">
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-5">
                <a href="{{ route('staff.portal.job-orders.index') }}"
                    class="grid h-12 w-12 place-items-center rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-slate-600 transition-all shadow-sm">
                    <i data-lucide="arrow-left" class="h-6 w-6"></i>
                </a>
                <div>
                    <h2 class="text-3xl font-bold tracking-tight text-slate-900">เพิ่มใบงานใหม่</h2>
                    <p class="mt-1 text-slate-500">สร้างใบงานและระบบจะสร้างรายการเอกสารประกอบให้จากบริการที่เลือกอัตโนมัติ</p>
                </div>
            </div>
        </header>

        @if ($errors->any())
            <div class="rounded-3xl bg-rose-50 p-6 border border-rose-100">
                <div class="flex gap-3">
                    <i data-lucide="alert-circle" class="h-5 w-5 text-rose-500 shrink-0"></i>
                    <div>
                        <h4 class="text-sm font-bold text-rose-800 uppercase tracking-wide">กรุณาตรวจสอบข้อผิดพลาด</h4>
                        <ul class="mt-2 list-disc pl-5 text-sm text-rose-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('staff.portal.job-orders.store') }}" method="POST" class="space-y-8">
            @csrf

            <section class="glass-card rounded-3xl p-8 shadow-sm">
                <div class="flex items-center justify-between gap-4 mb-8 pb-5 border-b border-slate-50">
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600">
                            <i data-lucide="briefcase-business" class="h-4 w-4"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">ข้อมูลใบงาน</h3>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-2 text-xs font-bold text-slate-500">
                        Assigned to: {{ auth()->user()->name }}
                    </div>
                </div>

                <div class="grid gap-8 lg:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">บริษัทนายจ้าง <span class="text-rose-500">*</span></label>
                        <select id="employer_id" name="employer_id" required class="portal-select h-12 w-full px-4 text-sm font-medium">
                            <option value="">-- เลือกบริษัท --</option>
                            @foreach ($employers as $employer)
                                <option value="{{ $employer->id }}" @selected(old('employer_id', $employerId) == $employer->id)>
                                    {{ $employer->company_name }} ({{ $employer->company_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">แรงงาน <span class="text-rose-500">*</span></label>
                        <select id="worker_id" name="worker_id" required @disabled(! old('employer_id', $employerId)) class="portal-select h-12 w-full px-4 text-sm font-medium">
                            <option value="">-- เลือกแรงงาน --</option>
                            @foreach ($workers as $worker)
                                <option value="{{ $worker->id }}" data-employer-id="{{ $worker->employer_id }}" @selected(old('worker_id', $workerId) == $worker->id)>
                                    {{ $worker->full_name_th ?: $worker->full_name_en }} · {{ $worker->employer?->company_name ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400">เลือกนายจ้างก่อน แล้วระบบจะแสดงเฉพาะแรงงานของบริษัทนั้น</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">บริการ <span class="text-rose-500">*</span></label>
                        <select name="service_id" required class="portal-select h-12 w-full px-4 text-sm font-medium">
                            <option value="">-- เลือกบริการ --</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" @selected(old('service_id', $serviceId) == $service->id)>
                                    {{ $service->name }} · {{ $service->code }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">ระดับความสำคัญ <span class="text-rose-500">*</span></label>
                        <select name="priority" required class="portal-select h-12 w-full px-4 text-sm font-medium">
                            @foreach ($priorityLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('priority', 'medium') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">กำหนดส่งงาน</label>
                        <input type="date" name="due_date" value="{{ old('due_date', now()->addDays(7)->format('Y-m-d')) }}"
                            class="portal-input h-12 w-full px-4 text-sm font-medium">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">ค่าบริการ <span class="text-rose-500">*</span></label>
                        <input type="number" name="service_fee" min="0" step="0.01" value="{{ old('service_fee', 0) }}"
                            class="portal-input h-12 w-full px-4 text-sm font-medium">
                    </div>
                </div>

                <div class="mt-8 space-y-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500">หมายเหตุ</label>
                    <textarea name="notes" rows="4" placeholder="ระบุรายละเอียดเพิ่มเติมของใบงาน"
                        class="portal-textarea w-full px-4 py-3 text-sm font-medium">{{ old('notes') }}</textarea>
                </div>

                <div class="mt-6 rounded-2xl border border-blue-100 bg-blue-50/60 p-4 text-sm text-slate-600">
                    <p class="font-bold text-[#0b2f52]">หมายเหตุระบบ</p>
                    <p class="mt-1">เมื่อบันทึก ระบบจะสร้าง checklist เอกสารจากบริการที่เลือกให้อัตโนมัติ และตั้งสถานะเริ่มต้นเป็น “รอเริ่มงาน”</p>
                </div>
            </section>

            <div class="flex items-center justify-end gap-4 pb-12">
                <a href="{{ route('staff.portal.job-orders.index') }}"
                    class="h-12 px-8 flex items-center justify-center rounded-2xl bg-white border border-slate-200 text-sm font-bold text-slate-500 hover:bg-slate-50 transition-all">
                    ยกเลิก
                </a>
                <button type="submit"
                    class="h-12 px-10 flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-[#0b2f52] via-[#123e68] to-[#b91c1c] text-sm font-bold text-white shadow-lg shadow-[#0b2f52]/20 hover:opacity-95 transition-all focus:ring-4 focus:ring-blue-100">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    บันทึกใบงาน
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        const employerSelect = document.getElementById('employer_id');
        const workerSelect = document.getElementById('worker_id');
        const workerOptions = Array.from(workerSelect.querySelectorAll('option[data-employer-id]'));

        function filterWorkers() {
            const employerId = employerSelect.value;
            let selectedOptionIsVisible = false;

            workerOptions.forEach((option) => {
                const isVisible = employerId !== '' && option.dataset.employerId === employerId;
                option.hidden = ! isVisible;
                option.disabled = ! isVisible;

                if (option.selected && isVisible) {
                    selectedOptionIsVisible = true;
                }
            });

            if (! employerId) {
                workerSelect.value = '';
                workerSelect.disabled = true;
                return;
            }

            workerSelect.disabled = false;

            if (! selectedOptionIsVisible) {
                workerSelect.value = '';
            }
        }

        employerSelect.addEventListener('change', filterWorkers);
        filterWorkers();
    </script>
@endpush
