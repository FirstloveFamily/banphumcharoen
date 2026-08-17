@php
    $requestMode = $requestMode ?? false;
    $layout = $requestMode ? 'layouts.app' : 'layouts.staff-portal';
    $title = $requestMode ? 'แจ้งงานใหม่' : 'เพิ่มใบงานใหม่';
    $pageTitle = 'จัดการใบงาน (Job Orders)';
@endphp
@extends($layout)

@push('head')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
    .worker-autocomplete {
        position: relative;
    }
    .worker-autocomplete-panel {
        position: absolute;
        z-index: 40;
        left: 0;
        right: 0;
        top: calc(100% + 0.5rem);
        max-height: 18rem;
        overflow: auto;
        border: 1px solid rgba(11, 47, 82, 0.12);
        background: rgba(255, 255, 255, 0.98);
        border-radius: 1rem;
        box-shadow: 0 20px 40px rgba(11, 47, 82, 0.12);
        backdrop-filter: blur(14px);
    }
    .worker-autocomplete-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        width: 100%;
        padding: 0.875rem 1rem;
        text-align: left;
        transition: background-color 160ms ease;
    }
    .worker-autocomplete-item:hover,
    .worker-autocomplete-item[aria-selected="true"] {
        background: rgba(219, 234, 254, 0.75);
    }
    .worker-autocomplete-item + .worker-autocomplete-item {
        border-top: 1px solid rgba(226, 232, 240, 0.9);
    }
    .employer-autocomplete {
        position: relative;
    }
    .employer-autocomplete-panel {
        position: absolute;
        z-index: 50;
        left: 0;
        right: 0;
        top: calc(100% + 0.5rem);
        max-height: 18rem;
        overflow: auto;
        border: 1px solid rgba(11, 47, 82, 0.12);
        background: rgba(255, 255, 255, 0.98);
        border-radius: 1rem;
        box-shadow: 0 20px 40px rgba(11, 47, 82, 0.12);
        backdrop-filter: blur(14px);
    }
    .employer-autocomplete-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        width: 100%;
        padding: 0.875rem 1rem;
        text-align: left;
        transition: background-color 160ms ease;
    }
    .employer-autocomplete-item:hover,
    .employer-autocomplete-item[aria-selected="true"] {
        background: rgba(219, 234, 254, 0.75);
    }
    .employer-autocomplete-item + .employer-autocomplete-item {
        border-top: 1px solid rgba(226, 232, 240, 0.9);
    }
</style>
@endpush

@section('content')
    @php
        $priorityLabels = [
            'low' => 'ต่ำ',
            'medium' => 'ปานกลาง',
            'high' => 'สูง',
            'urgent' => 'ด่วนพิเศษ',
        ];
        $selectedEmployer = $employers->firstWhere('id', (int) old('employer_id', $employerId));
        $selectedWorker = $workers->firstWhere('id', (int) old('worker_id', $workerId));
        $selectedEmployerId = old('employer_id', $employerId);
        $selectedWorkerEmployerId = $selectedEmployerId;
        $selectedWorkerId = old('worker_id', $workerId);
        $selectedEmployerLabel = $selectedEmployer ? trim($selectedEmployer->company_name . ' (' . ($selectedEmployer->company_code ?? '-') . ')') : '';
        $selectedEmployerSubLabel = $selectedEmployer ? trim(($selectedEmployer->contact_name ?: '-') . ($selectedEmployer->phone ? ' · ' . $selectedEmployer->phone : '')) : '';
        $selectedWorkerLabel = $selectedWorker ? trim(($selectedWorker->full_name_th ?: $selectedWorker->full_name_en) . ' · ' . ($selectedWorker->employer?->company_name ?? '-')) : '';
        $employerOptions = $employers->map(fn ($employer) => [
            'id' => $employer->id,
            'company_name' => $employer->company_name,
            'company_code' => $employer->company_code,
            'contact_name' => $employer->contact_name,
            'phone' => $employer->phone,
            'email' => $employer->email,
            'search' => mb_strtolower(trim(implode(' ', array_filter([
                $employer->company_name,
                $employer->company_code,
                $employer->contact_name,
                $employer->phone,
                $employer->email,
            ])))),
        ])->values();
        $workerOptions = $workers->map(fn ($worker) => [
            'id' => $worker->id,
            'employer_id' => $worker->employer_id,
            'label' => trim(($worker->full_name_th ?: $worker->full_name_en) . ' · ' . ($worker->employer?->company_name ?? '-')),
            'company_name' => $worker->employer?->company_name ?? '-',
            'passport_number' => $worker->passport_number,
            'wp_number' => $worker->wp_number,
            'search' => mb_strtolower(trim(implode(' ', array_filter([
                $worker->full_name_th,
                $worker->full_name_en,
                $worker->passport_number,
                $worker->wp_number,
                $worker->employer?->company_name,
                $worker->employer?->company_code,
            ])))),
        ])->values();
    @endphp

    <div class="space-y-8 max-w-6xl mx-auto">
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-5">
                <a href="{{ $requestMode ? route('employers.dashboard') : route('staff.portal.job-orders.index') }}"
                    class="grid h-12 w-12 place-items-center rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-slate-600 transition-all shadow-sm">
                    <i data-lucide="arrow-left" class="h-6 w-6"></i>
                </a>
                <div>
                    <h2 class="text-3xl font-bold tracking-tight text-slate-900">{{ $requestMode ? 'แจ้งงานใหม่' : 'เพิ่มใบงานใหม่' }}</h2>
                    <p class="mt-1 text-slate-500">{{ $requestMode ? 'เลือกบริการและลูกจ้างเพื่อส่งคำขอดำเนินงาน' : 'สร้างใบงานและระบบจะสร้างรายการเอกสารประกอบให้จากบริการที่เลือกอัตโนมัติ' }}</p>
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

        <form action="{{ $requestMode ? route('employers.jobs.store') : route('staff.portal.job-orders.store') }}" method="POST" class="space-y-8">
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
                        {{ $requestMode ? 'ส่งโดย: ' : 'Assigned to: ' }}{{ auth()->user()->name }}
                    </div>
                </div>

                <div class="grid gap-8 lg:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">บริษัทนายจ้าง <span class="text-rose-500">*</span></label>
                        <div class="space-y-2">
                            <div
                                class="employer-autocomplete"
                                data-employer-autocomplete
                                data-readonly="{{ $requestMode ? 'true' : 'false' }}"
                                data-employers='@json($employerOptions)'
                            >
                                <input type="hidden" name="employer_id" value="{{ $selectedEmployerId }}">
                                <div class="relative">
                                    <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400"></i>
                                    <input
                                        type="text"
                                        id="employer_search"
                                        value="{{ $selectedEmployerLabel }}"
                                        placeholder="พิมพ์ชื่อบริษัท, รหัสบริษัท, ผู้ติดต่อ..."
                                        autocomplete="off"
                                        @readonly($requestMode)
                                        required
                                        class="portal-input h-12 w-full pl-11 pr-11 text-sm font-medium"
                                    >
                                    <button
                                        type="button"
                                        id="employer_clear"
                                        class="absolute right-2 top-1/2 {{ $requestMode ? 'hidden' : '' }} h-8 w-8 -translate-y-1/2 items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors"
                                        aria-label="ล้างบริษัทนายจ้าง"
                                    >
                                        <i data-lucide="x" class="h-4 w-4"></i>
                                    </button>
                                </div>
                                <div data-employer-panel class="employer-autocomplete-panel hidden" role="listbox" aria-label="รายชื่อบริษัทนายจ้าง"></div>
                            </div>
                            <p data-employer-hint class="text-[11px] text-slate-400">
                                @if($selectedEmployerLabel)
                                    เลือกไว้แล้ว: {{ $selectedEmployerLabel }}{{ $selectedEmployerSubLabel ? ' · ' . $selectedEmployerSubLabel : '' }}
                                @else
                                    พิมพ์เพื่อค้นหาแล้วคลิกเลือกบริษัท
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">แรงงาน <span class="text-rose-500">*</span></label>
                        <div class="space-y-3">
                            <div class="relative">
                                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400"></i>
                                <input
                                    id="worker_search"
                                    type="text"
                                    value="{{ $selectedWorkerLabel }}"
                                    placeholder="พิมพ์ชื่อ, พาสปอร์ต, Work Permit..."
                                    class="portal-input h-12 w-full pl-11 pr-11 text-sm font-medium"
                                    autocomplete="off"
                                >
                                <button
                                    type="button"
                                    id="worker_clear"
                                    class="absolute right-2 top-1/2 hidden h-8 w-8 -translate-y-1/2 items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors"
                                    aria-label="ล้างแรงงาน"
                                >
                                    <i data-lucide="x" class="h-4 w-4"></i>
                                </button>
                            </div>
                            <div
                                class="worker-autocomplete"
                                data-worker-autocomplete
                                data-workers='@json($workerOptions)'
                                data-selected-employer-id="{{ $selectedWorkerEmployerId }}"
                                data-selected-worker-id="{{ $selectedWorkerId }}"
                            >
                                <input type="hidden" name="worker_id" value="{{ $selectedWorkerId }}">
                                <input type="hidden" data-worker-employer value="{{ $selectedWorkerEmployerId }}">
                                <div data-worker-panel class="worker-autocomplete-panel hidden" role="listbox" aria-label="รายชื่อแรงงาน"></div>
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-400">เลือกนายจ้างก่อน แล้วระบบจะแสดงเฉพาะแรงงานของบริษัทนั้น</p>
                        <p data-worker-hint class="text-[11px] text-slate-400">
                            @if($selectedWorker)
                                เลือกไว้แล้ว: {{ $selectedWorker->full_name_th ?: $selectedWorker->full_name_en }} · {{ $selectedWorker->employer?->company_name ?? '-' }}
                            @else
                                พิมพ์เพื่อค้นหาแรงงานที่อยู่ในบริษัทที่เลือก
                            @endif
                        </p>
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

                    @if (! $requestMode)
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">ค่าบริการ <span class="text-rose-500">*</span></label>
                        <input type="number" name="service_fee" min="0" step="0.01" value="{{ old('service_fee', 0) }}"
                            class="portal-input h-12 w-full px-4 text-sm font-medium">
                    </div>
                    @endif
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
                <a href="{{ $requestMode ? route('employers.dashboard') : route('staff.portal.job-orders.index') }}"
                    class="h-12 px-8 flex items-center justify-center rounded-2xl bg-white border border-slate-200 text-sm font-bold text-slate-500 hover:bg-slate-50 transition-all">
                    ยกเลิก
                </a>
                <button type="submit"
                    class="h-12 px-10 flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-[#0b2f52] via-[#123e68] to-[#b91c1c] text-sm font-bold text-white shadow-lg shadow-[#0b2f52]/20 hover:opacity-95 transition-all focus:ring-4 focus:ring-blue-100">
                    <i data-lucide="{{ $requestMode ? 'send' : 'save' }}" class="h-4 w-4"></i>
                    {{ $requestMode ? 'ส่งแจ้งงาน' : 'บันทึกใบงาน' }}
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        const employerSearch = document.getElementById('employer_search');
        const employerClear = document.getElementById('employer_clear');
        const employerRoot = document.querySelector('[data-employer-autocomplete]');
        const employerHidden = employerRoot?.querySelector('input[type="hidden"][name="employer_id"]');
        const employerPanel = employerRoot?.querySelector('[data-employer-panel]');
        const employerHint = document.querySelector('[data-employer-hint]');
        const employerOptions = JSON.parse(employerRoot?.dataset.employers || '[]');
        const employerReadonly = employerRoot?.dataset.readonly === 'true';
        const form = employerRoot?.closest('form');
        const workerSearch = document.getElementById('worker_search');
        const workerClear = document.getElementById('worker_clear');
        const workerRoot = document.querySelector('[data-worker-autocomplete]');
        const workerHidden = workerRoot?.querySelector('input[type="hidden"][name="worker_id"]');
        const workerEmployerHidden = workerRoot?.querySelector('[data-worker-employer]');
        const workerPanel = workerRoot?.querySelector('[data-worker-panel]');
        const workerHint = document.querySelector('[data-worker-hint]');
        const workerOptions = JSON.parse(workerRoot?.dataset.workers || '[]');
        let employerActiveIndex = -1;
        let employerIsFocused = false;
        let workerActiveIndex = -1;
        let workerIsFocused = false;

        const normalize = (value) => String(value ?? '').trim().toLowerCase();
        const escapeHtml = (value) => String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        function filterEmployers() {
            if (employerReadonly) {
                employerPanel?.classList.add('hidden');
                return;
            }

            const searchValue = normalize(employerSearch.value);
            const matches = employerOptions.filter((employer) => {
                const haystack = normalize([
                    employer.company_name,
                    employer.company_code,
                    employer.contact_name,
                    employer.phone,
                    employer.email,
                    employer.search,
                ].filter(Boolean).join(' '));

                return searchValue === '' || haystack.includes(searchValue);
            }).slice(0, 8);

            if (employerClear) {
                employerClear.classList.toggle('hidden', employerSearch.value.trim() === '' && (employerHidden?.value || '') === '');
            }

            if (employerPanel) {
                const shouldShowPanel = employerIsFocused || searchValue !== '';
                if (! shouldShowPanel) {
                    employerPanel.classList.add('hidden');
                    employerPanel.innerHTML = '';
                    return;
                }

                if (! matches.length) {
                    employerPanel.innerHTML = '<div class="px-4 py-4 text-sm text-slate-500">ไม่พบบริษัทที่ตรงกับคำค้นหา</div>';
                    employerPanel.classList.remove('hidden');
                } else {
                    employerPanel.innerHTML = matches.map((employer, index) => {
                        const subtitle = [employer.company_code, employer.contact_name, employer.phone].filter(Boolean).join(' · ') || 'ไม่มีข้อมูลเพิ่มเติม';
                        return `
                            <button
                                type="button"
                                class="employer-autocomplete-item"
                                data-employer-id="${String(employer.id)}"
                                data-index="${index}"
                                aria-selected="${index === employerActiveIndex ? 'true' : 'false'}"
                            >
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-bold text-slate-900">${escapeHtml(employer.company_name)}</span>
                                    <span class="mt-0.5 block truncate text-xs font-medium text-slate-500">${escapeHtml(subtitle)}</span>
                                </span>
                                <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-slate-500">เลือก</span>
                            </button>
                        `;
                    }).join('');
                    employerPanel.classList.remove('hidden');
                }
            }
        }

        function setEmployer(employer) {
            if (employerHidden) employerHidden.value = employer ? String(employer.id) : '';
            employerSearch.value = employer ? `${employer.company_name}${employer.company_code ? ` (${employer.company_code})` : ''}` : '';
            employerSearch.setCustomValidity('');
            employerActiveIndex = -1;
            if (employerHint) {
                employerHint.textContent = employer
                    ? `เลือกไว้แล้ว: ${employer.company_name}${employer.company_code ? ` · ${employer.company_code}` : ''}${employer.contact_name ? ` · ${employer.contact_name}` : ''}`
                    : 'พิมพ์เพื่อค้นหาแล้วคลิกเลือกบริษัท';
            }
            if (employerPanel) {
                employerPanel.classList.add('hidden');
            }
            if (employerClear) {
                employerClear.classList.toggle('hidden', employerSearch.value.trim() === '' && (employerHidden?.value || '') === '');
            }

            if (workerHidden) workerHidden.value = '';
            if (workerEmployerHidden) workerEmployerHidden.value = employer ? String(employer.id) : '';
            workerSearch.value = '';
            workerActiveIndex = -1;
            if (workerHint) {
                workerHint.textContent = employer
                    ? 'พิมพ์เพื่อค้นหาแรงงานที่อยู่ในบริษัทที่เลือก'
                    : 'เลือกนายจ้างก่อน แล้วระบบจะแสดงเฉพาะแรงงานของบริษัทนั้น';
            }
            if (workerPanel) {
                workerPanel.classList.add('hidden');
                workerPanel.innerHTML = '';
            }
        }

        function clearWorkerSelection() {
            if (workerHidden) workerHidden.value = '';
            if (workerEmployerHidden) workerEmployerHidden.value = '';
            workerSearch.value = '';
            workerSearch.setCustomValidity('');
            workerActiveIndex = -1;
            if (workerHint) {
                workerHint.textContent = 'เลือกนายจ้างก่อน แล้วระบบจะแสดงเฉพาะแรงงานของบริษัทนั้น';
            }
            if (workerPanel) {
                workerPanel.classList.add('hidden');
                workerPanel.innerHTML = '';
            }
            if (workerClear) {
                workerClear.classList.add('hidden');
            }
        }

        employerSearch.addEventListener('input', () => {
            if (employerHidden) employerHidden.value = '';
            employerSearch.setCustomValidity('');
            if (employerHint) employerHint.textContent = 'พิมพ์เพื่อค้นหาแล้วคลิกเลือกบริษัท';
            clearWorkerSelection();
            filterEmployers();
        });

        employerSearch.addEventListener('focus', () => {
            employerIsFocused = true;
            filterEmployers();
        });

        employerSearch.addEventListener('keydown', (event) => {
            if (! employerPanel || employerPanel.classList.contains('hidden')) return;

            const items = [...employerPanel.querySelectorAll('[data-employer-id]')];
            if (! items.length) return;

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                employerActiveIndex = Math.min(employerActiveIndex + 1, items.length - 1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                employerActiveIndex = Math.max(employerActiveIndex - 1, 0);
            } else if (event.key === 'Enter') {
                if (employerActiveIndex >= 0 && items[employerActiveIndex]) {
                    event.preventDefault();
                    items[employerActiveIndex].click();
                }
                return;
            } else if (event.key === 'Escape') {
                employerPanel.classList.add('hidden');
                return;
            } else {
                return;
            }

            items.forEach((item, index) => {
                item.setAttribute('aria-selected', index === employerActiveIndex ? 'true' : 'false');
            });

            if (items[employerActiveIndex]) {
                items[employerActiveIndex].scrollIntoView({ block: 'nearest' });
            }
        });

        employerPanel?.addEventListener('click', (event) => {
            const item = event.target.closest('[data-employer-id]');
            if (! item) return;

            const employer = employerOptions.find((candidate) => String(candidate.id) === item.dataset.employerId);
            if (employer) {
                setEmployer(employer);
            }
        });

        employerClear?.addEventListener('click', () => {
            setEmployer(null);
            employerSearch.focus();
        });

        document.addEventListener('click', (event) => {
            if (employerRoot && ! employerRoot.contains(event.target)) {
                employerPanel?.classList.add('hidden');
            }
        });

        employerSearch.addEventListener('blur', () => {
            window.setTimeout(() => {
                if (employerRoot?.contains(document.activeElement)) {
                    return;
                }

                const employer = employerOptions.find((candidate) => String(candidate.id) === String(employerHidden?.value || ''));
                if (employer && employerSearch.value.trim() === '') {
                    employerSearch.value = `${employer.company_name}${employer.company_code ? ` (${employer.company_code})` : ''}`;
                }
                employerIsFocused = false;
                employerPanel?.classList.add('hidden');
                filterEmployers();
            }, 120);
        });

        if (employerHidden?.value && ! employerSearch.value.trim()) {
            const selectedEmployer = employerOptions.find((candidate) => String(candidate.id) === String(employerHidden.value));
            if (selectedEmployer) {
                employerSearch.value = `${selectedEmployer.company_name}${selectedEmployer.company_code ? ` (${selectedEmployer.company_code})` : ''}`;
            }
        }

        employerIsFocused = false;
        filterEmployers();

        function filterWorkers() {
            const employerId = employerHidden?.value || '';
            const searchValue = normalize(workerSearch.value);
            const matches = workerOptions.filter((worker) => {
                const matchesEmployer = employerId !== '' && String(worker.employer_id) === employerId;
                const matchesSearch = searchValue === '' || normalize(worker.search || '').includes(searchValue);
                return matchesEmployer && matchesSearch;
            }).slice(0, 8);

            if (workerClear) {
                workerClear.classList.toggle('hidden', workerSearch.value.trim() === '' && (workerHidden?.value || '') === '');
            }

            if (! employerId) {
                if (workerHidden) workerHidden.value = '';
                if (workerEmployerHidden) workerEmployerHidden.value = '';
                if (workerHint) workerHint.textContent = 'เลือกนายจ้างก่อน แล้วระบบจะแสดงเฉพาะแรงงานของบริษัทนั้น';
                if (workerPanel) {
                    workerPanel.classList.add('hidden');
                    workerPanel.innerHTML = '';
                }
                return;
            }

            const selectedWorker = workerOptions.find((candidate) => String(candidate.id) === String(workerHidden?.value || ''));
            if (selectedWorker && String(selectedWorker.employer_id) !== employerId) {
                if (workerHidden) workerHidden.value = '';
                if (workerEmployerHidden) workerEmployerHidden.value = '';
                workerSearch.value = '';
                if (workerHint) workerHint.textContent = 'พิมพ์เพื่อค้นหาแรงงานที่อยู่ในบริษัทที่เลือก';
            }

            if (workerPanel) {
                const shouldShowPanel = workerIsFocused || searchValue !== '';
                if (! shouldShowPanel) {
                    workerPanel.classList.add('hidden');
                    workerPanel.innerHTML = '';
                    return;
                }

                if (! matches.length) {
                    workerPanel.innerHTML = '<div class="px-4 py-4 text-sm text-slate-500">ไม่พบแรงงานที่ตรงกับคำค้นหา</div>';
                    workerPanel.classList.remove('hidden');
                } else {
                    workerPanel.innerHTML = matches.map((worker, index) => `
                        <button
                            type="button"
                            class="worker-autocomplete-item"
                            data-worker-id="${String(worker.id)}"
                            data-index="${index}"
                            aria-selected="${index === workerActiveIndex ? 'true' : 'false'}"
                        >
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-bold text-slate-900">${escapeHtml(worker.label)}</span>
                                <span class="mt-0.5 block truncate text-xs font-medium text-slate-500">${escapeHtml(worker.passport_number || worker.wp_number || 'ไม่มีเลขเอกสาร')}</span>
                            </span>
                            <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-slate-500">เลือก</span>
                        </button>
                    `).join('');
                    workerPanel.classList.remove('hidden');
                }
            }
        }

        function setWorker(worker) {
            if (workerHidden) workerHidden.value = worker ? String(worker.id) : '';
            if (workerEmployerHidden) workerEmployerHidden.value = worker ? String(worker.employer_id) : '';
            workerSearch.value = worker ? String(worker.label) : '';
            workerSearch.setCustomValidity('');
            workerActiveIndex = -1;
            if (workerHint) {
                workerHint.textContent = worker
                    ? `เลือกไว้แล้ว: ${worker.label}`
                    : 'พิมพ์เพื่อค้นหาแรงงานที่อยู่ในบริษัทที่เลือก';
            }
            if (workerPanel) {
                workerPanel.classList.add('hidden');
            }
            if (workerClear) {
                workerClear.classList.toggle('hidden', workerSearch.value.trim() === '' && (workerHidden?.value || '') === '');
            }
        }

        workerSearch.addEventListener('input', filterWorkers);
        workerSearch.addEventListener('focus', () => {
            workerIsFocused = true;
            filterWorkers();
        });
        workerSearch.addEventListener('keydown', (event) => {
            if (! workerPanel || workerPanel.classList.contains('hidden')) return;

            const items = [...workerPanel.querySelectorAll('[data-worker-id]')];
            if (! items.length) return;

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                workerActiveIndex = Math.min(workerActiveIndex + 1, items.length - 1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                workerActiveIndex = Math.max(workerActiveIndex - 1, 0);
            } else if (event.key === 'Enter') {
                if (workerActiveIndex >= 0 && items[workerActiveIndex]) {
                    event.preventDefault();
                    items[workerActiveIndex].click();
                }
                return;
            } else if (event.key === 'Escape') {
                workerPanel.classList.add('hidden');
                return;
            } else {
                return;
            }

            items.forEach((item, index) => {
                item.setAttribute('aria-selected', index === workerActiveIndex ? 'true' : 'false');
            });

            if (items[workerActiveIndex]) {
                items[workerActiveIndex].scrollIntoView({ block: 'nearest' });
            }
        });

        workerPanel?.addEventListener('click', (event) => {
            const item = event.target.closest('[data-worker-id]');
            if (! item) return;

            const worker = workerOptions.find((candidate) => String(candidate.id) === item.dataset.workerId);
            if (worker) {
                setWorker(worker);
            }
        });

        workerClear?.addEventListener('click', () => {
            setWorker(null);
            workerSearch.focus();
        });

        document.addEventListener('click', (event) => {
            if (workerRoot && ! workerRoot.contains(event.target)) {
                workerPanel?.classList.add('hidden');
            }
        });

        workerSearch.addEventListener('blur', () => {
            window.setTimeout(() => {
                if (workerRoot?.contains(document.activeElement)) {
                    return;
                }

                const worker = workerOptions.find((candidate) => String(candidate.id) === String(workerHidden?.value || ''));
                if (worker && workerSearch.value.trim() === '') {
                    workerSearch.value = String(worker.label);
                }
                workerIsFocused = false;
                workerPanel?.classList.add('hidden');
                filterWorkers();
            }, 120);
        });

        if (workerHidden?.value && ! workerSearch.value.trim()) {
            const selectedWorker = workerOptions.find((candidate) => String(candidate.id) === String(workerHidden.value));
            if (selectedWorker) {
                workerSearch.value = String(selectedWorker.label);
            }
        }

        workerIsFocused = false;
        filterWorkers();

        if (employerHidden?.value) {
            const currentEmployer = employerOptions.find((candidate) => String(candidate.id) === String(employerHidden.value));
            if (currentEmployer) {
                setEmployer(currentEmployer);
            }
        }

        form?.addEventListener('submit', (event) => {
            if (! (employerHidden?.value || '').toString()) {
                employerSearch.setCustomValidity('กรุณาเลือกบริษัทนายจ้างจากรายการ');
                employerSearch.reportValidity();
                employerSearch.focus();
                event.preventDefault();
                return;
            }

            if (! (workerHidden?.value || '').toString()) {
                workerSearch.setCustomValidity('กรุณาเลือกแรงงานจากรายการ');
                workerSearch.reportValidity();
                workerSearch.focus();
                event.preventDefault();
                return;
            }

            employerSearch.setCustomValidity('');
            workerSearch.setCustomValidity('');
        });
    </script>
@endpush
