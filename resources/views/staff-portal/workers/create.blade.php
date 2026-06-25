@extends('layouts.staff-portal', ['title' => 'เพิ่มแรงงานใหม่', 'pageTitle' => 'จัดการข้อมูลแรงงาน'])

@push('head')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
    .employer-autocomplete {
        position: relative;
    }
    .employer-autocomplete-panel {
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
        $selectedEmployerId = old('employer_id', request('employer_id'));
        $selectedEmployer = $employers->firstWhere('id', (int) $selectedEmployerId);
        $selectedEmployerLabel = $selectedEmployer?->company_name ?? '';
        $selectedEmployerSubLabel = $selectedEmployer?->company_code ?? '';
        $employerOptions = $employers->map(fn ($employer) => [
            'id' => $employer->id,
            'company_name' => $employer->company_name,
            'company_code' => $employer->company_code,
            'contact_name' => $employer->contact_name,
            'search' => mb_strtolower(trim(implode(' ', array_filter([
                $employer->company_name,
                $employer->company_code,
                $employer->contact_name,
                $employer->phone,
                $employer->email,
            ])))),
        ])->values();
    @endphp

    <div class="space-y-8 max-w-5xl mx-auto">
        <!-- Header Section -->
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-5">
                <a href="{{ route('staff.portal.workers.index') }}" 
                    class="grid h-12 w-12 place-items-center rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-slate-600 transition-all shadow-sm">
                    <i data-lucide="arrow-left" class="h-6 w-6"></i>
                </a>
                <div>
                    <h2 class="text-3xl font-bold tracking-tight text-slate-900">เพิ่มแรงงานใหม่</h2>
                    <p class="mt-1 text-slate-500">บันทึกข้อมูลและอัปโหลดเอกสารสำคัญของแรงงานรายใหม่</p>
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

        <form action="{{ route('staff.portal.workers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <!-- General Info -->
            <section class="glass-card rounded-3xl p-8 shadow-sm">
                <div class="flex items-center gap-3 mb-8 pb-5 border-b border-slate-50">
                    <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                        <i data-lucide="user" class="h-4 w-4"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">ข้อมูลทั่วไปและต้นสังกัด</h3>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-1 space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">บริษัทนายจ้าง / ต้นสังกัด <span class="text-rose-500">*</span></label>
                        <div
                            class="employer-autocomplete"
                            data-employer-autocomplete
                            data-employers='@json($employerOptions)'
                        >
                            <input type="hidden" name="employer_id" value="{{ $selectedEmployerId }}">
                            <div class="relative">
                                <input
                                    type="text"
                                    data-employer-input
                                    value="{{ $selectedEmployerLabel }}"
                                    placeholder="พิมพ์ชื่อบริษัท, รหัสบริษัท, ผู้ติดต่อ..."
                                    autocomplete="off"
                                    required
                                    class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 pr-11 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all"
                                >
                                <button
                                    type="button"
                                    data-employer-clear
                                    class="absolute right-2 top-1/2 hidden h-8 w-8 -translate-y-1/2 items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors"
                                    aria-label="ล้างบริษัทนายจ้าง"
                                >
                                    <i data-lucide="x" class="h-4 w-4"></i>
                                </button>
                            </div>
                            <div data-employer-panel class="employer-autocomplete-panel hidden" role="listbox" aria-label="รายชื่อบริษัทนายจ้าง"></div>
                        </div>
                        <p data-employer-hint class="text-[11px] text-slate-400">
                            @if($selectedEmployerLabel)
                                เลือกไว้แล้ว: {{ $selectedEmployerLabel }}{{ $selectedEmployerSubLabel ? ' (' . $selectedEmployerSubLabel . ')' : '' }}
                            @else
                                พิมพ์เพื่อค้นหาแล้วคลิกเลือกจากรายการ
                            @endif
                        </p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">สัญชาติ <span class="text-rose-500">*</span></label>
                        <select name="nationality_id" required class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all appearance-none">
                            <option value="">-- เลือกสัญชาติ --</option>
                            @foreach($nationalities as $nationality)
                                <option value="{{ $nationality->id }}" @selected(old('nationality_id') == $nationality->id)>{{ $nationality->name_th }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">เพศ</label>
                        <select name="gender" class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all appearance-none">
                            <option value="">-- เลือกเพศ --</option>
                            <option value="male" @selected(old('gender') == 'male')>ชาย (Male)</option>
                            <option value="female" @selected(old('gender') == 'female')>หญิง (Female)</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">วัน/เดือน/ปี เกิด <span class="text-rose-500">*</span></label>
                        <input type="date" name="birth_date" value="{{ old('birth_date') }}" required
                            class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all">
                    </div>
                    <div class="lg:col-span-2 space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">รูปถ่ายแรงงาน (.jpg, .png)</label>
                        <input type="file" name="photo_file" accept=".jpg,.jpeg,.png"
                            class="w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 transition-all border border-slate-100 rounded-2xl p-1 bg-slate-50/50">
                        <p class="text-xs text-amber-600">ขนาดรูปไม่เกิน 3 MB</p>
                    </div>
                </div>

                <div class="mt-8 grid md:grid-cols-3 gap-8 pt-8 border-t border-slate-50">
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-400">คำนำหน้า</label>
                        <select name="worker_prefix_id"
                            class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all appearance-none">
                            <option value="">-- ไม่ระบุคำนำหน้า --</option>
                            @foreach($workerPrefixes as $workerPrefix)
                                <option value="{{ $workerPrefix->id }}" @selected(old('worker_prefix_id') == $workerPrefix->id)>
                                    {{ $workerPrefix->name_th }} / {{ $workerPrefix->name_en }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">ชื่อ (ไทย) <span class="text-rose-500">*</span></label>
                        <input type="text" name="first_name_th" value="{{ old('first_name_th') }}" required
                            class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">นามสกุล (ไทย)</label>
                        <input type="text" name="last_name_th" value="{{ old('last_name_th') }}"
                            class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all">
                    </div>
                </div>

                <div class="mt-8 grid md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-400">First Name (EN) <span class="text-rose-500">*</span></label>
                        <input type="text" name="first_name_en" value="{{ old('first_name_en') }}" required
                            class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all uppercase">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-400">Last Name (EN)</label>
                        <input type="text" name="last_name_en" value="{{ old('last_name_en') }}"
                            class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all uppercase">
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-50">
                    <label class="group flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <div class="relative">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))
                                class="peer sr-only">
                            <div class="h-6 w-11 rounded-full bg-slate-200 transition-colors peer-checked:bg-blue-600"></div>
                            <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition-transform peer-checked:translate-x-5"></div>
                        </div>
                        <span class="text-sm font-bold text-slate-700 group-hover:text-blue-600 transition-colors">สถานะแรงงานปกติ (Active)</span>
                    </label>
                </div>
            </section>

            <!-- Identity Documents -->
            <section class="glass-card rounded-3xl p-8 shadow-sm">
                <div class="flex items-center gap-3 mb-8 pb-5 border-b border-slate-50">
                    <div class="h-8 w-8 rounded-lg bg-rose-100 flex items-center justify-center text-rose-600">
                        <i data-lucide="file-key" class="h-4 w-4"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">เอกสารประจำตัวแรงงาน</h3>
                </div>

                <div class="grid lg:grid-cols-2 gap-12">
                    <!-- Passport -->
                    <div class="space-y-6">
                        <h4 class="text-sm font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-blue-600"></span>
                            Passport Information
                            <span class="text-xs font-medium normal-case tracking-normal text-slate-400">(ไม่บังคับ)</span>
                        </h4>
                        <div class="grid gap-6">
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">เลขที่ Passport</label>
                                <input type="text" name="passport_number" value="{{ old('passport_number') }}"
                                    class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold font-mono outline-none focus:border-blue-400 focus:bg-white transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">วันหมดอายุ Passport</label>
                                <input type="date" name="passport_expiry" value="{{ old('passport_expiry') }}"
                                    class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">แนบไฟล์ Passport</label>
                                <input type="file" name="passport_file" accept=".pdf,.jpg,.jpeg,.png"
                                    class="w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 transition-all border border-slate-100 rounded-2xl p-1 bg-slate-50/50">
                                <p class="text-xs text-amber-600">รูปไม่เกิน 3 MB, ไฟล์เอกสารไม่เกิน 10 MB</p>
                            </div>
                        </div>
                    </div>

                    <!-- Work Permit -->
                    <div class="space-y-6">
                        <h4 class="text-sm font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                            Work Permit Information
                        </h4>
                        <div class="grid gap-6">
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">เลขที่ Work Permit</label>
                                <input type="text" name="wp_number" value="{{ old('wp_number') }}"
                                    class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold font-mono outline-none focus:border-blue-400 focus:bg-white transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">วันหมดอายุ WP</label>
                                <input type="date" name="wp_expiry" value="{{ old('wp_expiry') }}"
                                    class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">แนบไฟล์ Work Permit</label>
                                <input type="file" name="wp_file" accept=".pdf,.jpg,.jpeg,.png"
                                    class="w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 transition-all border border-slate-100 rounded-2xl p-1 bg-slate-50/50">
                                <p class="text-xs text-amber-600">รูปไม่เกิน 3 MB, ไฟล์เอกสารไม่เกิน 10 MB</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid lg:grid-cols-2 gap-12 mt-12 pt-12 border-t border-slate-50">
                    <!-- Visa -->
                    <div class="space-y-6">
                        <h4 class="text-sm font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-600"></span>
                            Visa Status
                        </h4>
                        <div class="grid gap-6">
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">วันหมดอายุ Visa</label>
                                <input type="date" name="visa_expiry" value="{{ old('visa_expiry') }}"
                                    class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">แนบไฟล์ Visa</label>
                                <input type="file" name="visa_file" accept=".pdf,.jpg,.jpeg,.png"
                                    class="w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 transition-all border border-slate-100 rounded-2xl p-1 bg-slate-50/50">
                                <p class="text-xs text-amber-600">รูปไม่เกิน 3 MB, ไฟล์เอกสารไม่เกิน 10 MB</p>
                            </div>
                        </div>
                    </div>

                    <!-- 90 Days Report -->
                    <div class="space-y-6">
                        <h4 class="text-sm font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-slate-900"></span>
                            90-Days Report
                        </h4>
                        <div class="grid gap-6">
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">วันครบกำหนดรายงานตัว</label>
                                <input type="date" name="report_90_days_due" value="{{ old('report_90_days_due') }}"
                                    class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">แนบไฟล์รายงานตัว</label>
                                <input type="file" name="report_90_days_file" accept=".pdf,.jpg,.jpeg,.png"
                                    class="w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 transition-all border border-slate-100 rounded-2xl p-1 bg-slate-50/50">
                                <p class="text-xs text-amber-600">รูปไม่เกิน 3 MB, ไฟล์เอกสารไม่เกิน 10 MB</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="flex items-center justify-end gap-4 pb-12">
                <a href="{{ route('staff.portal.workers.index') }}"
                    class="h-12 px-8 flex items-center justify-center rounded-2xl bg-white border border-slate-200 text-sm font-bold text-slate-500 hover:bg-slate-50 transition-all">
                    ยกเลิก
                </a>
                <button type="submit"
                    class="h-12 px-10 flex items-center justify-center gap-2 rounded-2xl bg-blue-600 text-sm font-bold text-white shadow-lg shadow-blue-500/20 hover:bg-blue-700 transition-all focus:ring-4 focus:ring-blue-100">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    บันทึกข้อมูลแรงงาน
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const root = document.querySelector('[data-employer-autocomplete]');
            if (! root) return;

            const form = root.closest('form');
            const input = root.querySelector('[data-employer-input]');
            const hidden = root.querySelector('input[type="hidden"][name="employer_id"]');
            const panel = root.querySelector('[data-employer-panel]');
            const clearButton = root.querySelector('[data-employer-clear]');
            const hint = root.querySelector('[data-employer-hint]');
            const employers = JSON.parse(root.dataset.employers || '[]');

            let activeIndex = -1;

            const normalize = (value) => String(value ?? '').trim().toLowerCase();
            const escapeHtml = (value) => String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const setHint = (employer) => {
                if (! hint) return;

                if (! employer) {
                    hint.textContent = 'พิมพ์เพื่อค้นหาแล้วคลิกเลือกจากรายการ';
                    return;
                }

                const suffix = employer.company_code ? ` (${employer.company_code})` : '';
                hint.textContent = `เลือกไว้แล้ว: ${employer.company_name}${suffix}`;
            };

            const updateClearButton = () => {
                clearButton.classList.toggle('hidden', input.value.trim() === '' && hidden.value === '');
            };

            const getMatches = () => {
                const query = normalize(input.value);
                const items = employers.filter((employer) => {
                    const haystack = normalize([
                        employer.company_name,
                        employer.company_code,
                        employer.contact_name,
                        employer.search,
                    ].filter(Boolean).join(' '));

                    return query === '' || haystack.includes(query);
                });

                return items.slice(0, 8);
            };

            const closePanel = () => {
                panel.classList.add('hidden');
                panel.innerHTML = '';
                activeIndex = -1;
            };

            const openPanel = () => {
                panel.classList.remove('hidden');
            };

            const render = () => {
                const matches = getMatches();

                if (! matches.length) {
                    panel.innerHTML = `
                        <div class="px-4 py-4 text-sm text-slate-500">
                            ไม่พบบริษัทที่ตรงกับคำค้นหา
                        </div>
                    `;
                    openPanel();
                    updateClearButton();
                    return;
                }

                panel.innerHTML = matches.map((employer, index) => {
                    const subtitle = [employer.company_code, employer.contact_name].filter(Boolean).join(' · ') || 'ไม่มีรหัสบริษัท';
                    return `
                        <button
                            type="button"
                            class="employer-autocomplete-item"
                            role="option"
                            data-id="${String(employer.id)}"
                            data-index="${index}"
                            aria-selected="${index === activeIndex ? 'true' : 'false'}"
                        >
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-bold text-slate-900">${escapeHtml(employer.company_name)}</span>
                                <span class="mt-0.5 block truncate text-xs font-medium text-slate-500">${escapeHtml(subtitle)}</span>
                            </span>
                            <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-slate-500">
                                เลือก
                            </span>
                        </button>
                    `;
                }).join('');

                openPanel();
                updateClearButton();
            };

            const selectEmployer = (employer) => {
                hidden.value = employer ? String(employer.id) : '';
                input.value = employer ? employer.company_name : '';
                input.setCustomValidity('');
                setHint(employer || null);
                updateClearButton();
                closePanel();
            };

            input.addEventListener('input', () => {
                hidden.value = '';
                input.setCustomValidity('');
                setHint(null);
                render();
            });

            input.addEventListener('focus', () => {
                render();
            });

            input.addEventListener('keydown', (event) => {
                const options = [...panel.querySelectorAll('[data-id]')];
                if (! options.length || panel.classList.contains('hidden')) return;

                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    activeIndex = Math.min(activeIndex + 1, options.length - 1);
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    activeIndex = Math.max(activeIndex - 1, 0);
                } else if (event.key === 'Enter') {
                    if (activeIndex >= 0 && options[activeIndex]) {
                        event.preventDefault();
                        options[activeIndex].click();
                    }
                    return;
                } else if (event.key === 'Escape') {
                    closePanel();
                    return;
                } else {
                    return;
                }

                options.forEach((option, index) => {
                    option.setAttribute('aria-selected', index === activeIndex ? 'true' : 'false');
                });

                if (options[activeIndex]) {
                    options[activeIndex].scrollIntoView({ block: 'nearest' });
                }
            });

            panel.addEventListener('click', (event) => {
                const item = event.target.closest('[data-id]');
                if (! item) return;

                const employer = employers.find((candidate) => String(candidate.id) === item.dataset.id);
                if (employer) {
                    selectEmployer(employer);
                }
            });

            clearButton.addEventListener('click', () => {
                selectEmployer(null);
                input.focus();
            });

            document.addEventListener('click', (event) => {
                if (! root.contains(event.target)) {
                    closePanel();
                }
            });

            input.addEventListener('blur', () => {
                window.setTimeout(() => {
                    if (root.contains(document.activeElement)) {
                        return;
                    }

                    const selected = employers.find((candidate) => String(candidate.id) === String(hidden.value));
                    if (selected && input.value.trim() === '') {
                        input.value = selected.company_name;
                    }

                    updateClearButton();
                    closePanel();
                }, 120);
            });

            if (hidden.value) {
                const selected = employers.find((candidate) => String(candidate.id) === String(hidden.value));
                if (selected) {
                    setHint(selected);
                }
            } else {
                setHint(null);
            }

            if (input.value.trim() !== '') {
                updateClearButton();
            }

            if (form) {
                form.addEventListener('submit', (event) => {
                    if (hidden.value) {
                        input.setCustomValidity('');
                        return;
                    }

                    input.setCustomValidity('กรุณาเลือกบริษัทนายจ้างจากรายการ');
                    input.reportValidity();
                    event.preventDefault();
                });
            }

            updateClearButton();
        })();
    </script>
@endpush
