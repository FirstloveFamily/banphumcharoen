@extends('layouts.staff-portal', ['title' => 'ดึงเอกสารแรงงาน', 'pageTitle' => 'ดึงเอกสารแรงงาน'])

@section('content')
    <div class="space-y-8" x-data>
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-bold text-indigo-600">รายชื่อแรงงาน / ดึงเอกสารแรงงาน</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">ดึงเอกสารแรงงาน</h1>
                <p class="mt-1 text-slate-500">เลือกนายจ้าง ลูกจ้าง และประเภทเอกสารที่ต้องการดาวน์โหลด</p>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-3">
                <div class="rounded-2xl border border-indigo-100 bg-indigo-50 px-5 py-3 text-sm font-bold text-indigo-700">
                    ไฟล์จะถูกจัดรวมเป็น ZIP ไฟล์เดียว
                </div>
                <button type="submit" form="download-documents-form" class="inline-flex h-12 items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-5 text-sm font-black text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700">
                    <i data-lucide="download" class="h-5 w-5"></i>
                    ดาวน์โหลดไฟล์ที่เลือก
                </button>
            </div>
        </header>

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-5 lg:grid-cols-[0.9fr_1.25fr_0.9fr]">
            <section class="glass-card overflow-hidden rounded-3xl shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="text-xs font-black uppercase tracking-wider text-indigo-600">ขั้นตอนที่ 1</p>
                    <h2 class="mt-1 text-lg font-black text-slate-900">เลือกนายจ้าง</h2>
                    <input type="search" data-filter-employer placeholder="ค้นหาชื่อบริษัท หรือรหัสบริษัท..." class="mt-4 h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-indigo-400 focus:bg-white">
                </div>
                <div class="max-h-[560px] divide-y divide-slate-100 overflow-y-auto">
                    @forelse ($employers as $employer)
                        <a href="{{ route('staff.portal.worker-documents.download', ['employer_id' => $employer->id]) }}" data-employer-row data-employer-search="{{ $employer->company_name }} {{ $employer->company_code }}" class="flex items-center gap-3 px-6 py-4 transition hover:bg-indigo-50/60 {{ $selectedEmployerId === $employer->id ? 'bg-indigo-50' : '' }}">
                            <span class="grid h-5 w-5 shrink-0 place-items-center rounded-full border-2 {{ $selectedEmployerId === $employer->id ? 'border-indigo-600' : 'border-slate-300' }}">
                                @if ($selectedEmployerId === $employer->id)<span class="h-2.5 w-2.5 rounded-full bg-indigo-600"></span>@endif
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-bold text-slate-800">{{ $employer->company_name }}</span>
                                <span class="mt-1 block text-xs text-slate-400">{{ $employer->company_code ?: 'ไม่มีรหัสบริษัท' }}</span>
                            </span>
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500">{{ $employer->workers_count }} คน</span>
                        </a>
                    @empty
                        <p class="px-6 py-12 text-center text-sm text-slate-400">ยังไม่มีข้อมูลนายจ้าง</p>
                    @endforelse
                </div>
            </section>

            <section class="glass-card overflow-hidden rounded-3xl shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="text-xs font-black uppercase tracking-wider text-indigo-600">ขั้นตอนที่ 2</p>
                    <div class="mt-1 flex items-start justify-between gap-3">
                        <h2 class="text-lg font-black text-slate-900">เลือกลูกจ้าง{{ $selectedEmployer ? 'ของ ' . $selectedEmployer->company_name : '' }}</h2>
                        <label class="flex shrink-0 items-center gap-2 text-xs font-bold text-slate-500"><input type="checkbox" data-select-all-workers class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"> เลือกทั้งหมด</label>
                    </div>
                    <input type="search" data-filter-worker placeholder="ค้นหาชื่อลูกจ้าง หรือเลข Passport..." class="mt-4 h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-indigo-400 focus:bg-white" {{ $selectedEmployer ? '' : 'disabled' }}>
                </div>
                <div class="max-h-[560px] divide-y divide-slate-100 overflow-y-auto">
                    @forelse ($workers as $worker)
                        @php
                            $availableFiles = collect([$worker->passport_file, $worker->wp_file, $worker->visa_file, $worker->report_90_days_file])->filter()->count() + $worker->documents->filter(fn ($document) => filled($document->file_path))->count();
                        @endphp
                        <label data-worker-row data-worker-search="{{ $worker->full_name_th }} {{ $worker->full_name_en }} {{ $worker->passport_number }}" class="flex cursor-pointer items-center gap-3 px-6 py-4 transition hover:bg-slate-50">
                            <input type="checkbox" name="worker_ids[]" value="{{ $worker->id }}" form="download-documents-form" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" data-worker-checkbox>
                            @if ($worker->photo_path)
                                <img src="{{ asset('storage/' . $worker->photo_path) }}" class="h-10 w-10 rounded-xl object-cover" alt="">
                            @else
                                <span class="grid h-10 w-10 place-items-center rounded-xl bg-indigo-50 text-sm font-black text-indigo-600">{{ mb_substr($worker->first_name_th ?: $worker->first_name_en ?: '?', 0, 1) }}</span>
                            @endif
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-bold text-slate-800">{{ $worker->full_name_th ?: $worker->full_name_en }}</span>
                                <span class="mt-1 block text-xs text-slate-400">Passport: {{ $worker->passport_number ?: '-' }}</span>
                            </span>
                            <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $availableFiles ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-600' }}">{{ $availableFiles }} ไฟล์</span>
                        </label>
                    @empty
                        <div class="px-6 py-16 text-center text-sm text-slate-400">กรุณาเลือกนายจ้างเพื่อแสดงรายชื่อลูกจ้าง</div>
                    @endforelse
                </div>
            </section>

            <section class="glass-card overflow-hidden rounded-3xl shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="text-xs font-black uppercase tracking-wider text-indigo-600">ขั้นตอนที่ 3</p>
                    <div class="mt-1 flex items-start justify-between gap-3">
                        <h2 class="text-lg font-black text-slate-900">เลือกประเภทเอกสาร</h2>
                        <label class="flex shrink-0 items-center gap-2 text-xs font-bold text-slate-500"><input type="checkbox" data-select-all-documents checked class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"> ทั้งหมด</label>
                    </div>
                </div>
                <form id="download-documents-form" method="POST" action="{{ route('staff.portal.worker-documents.download.store') }}" class="flex h-full flex-col">
                    @csrf
                    <div class="flex-1 divide-y divide-slate-100 px-6">
                        @foreach ($documentTypes as $type => $label)
                            <label class="flex cursor-pointer items-center gap-3 py-4 text-sm font-semibold text-slate-700">
                                <input type="checkbox" name="document_types[]" value="{{ $type }}" checked data-document-checkbox class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="flex-1">{{ $label }}</span>
                                <i data-lucide="file" class="h-4 w-4 text-slate-300"></i>
                            </label>
                        @endforeach
                    </div>
                    <div class="m-5 rounded-2xl bg-indigo-50 p-4 text-xs leading-5 text-indigo-700">เลือกได้หลายรายการ ระบบจะรวมไฟล์ของลูกจ้างและเอกสารที่เลือกไว้ใน ZIP เดียว</div>
                </form>
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const filter = (input, rows, attribute) => {
                input?.addEventListener('input', () => {
                    const search = input.value.toLowerCase().trim();
                    rows.forEach(row => row.classList.toggle('hidden', search !== '' && !(row.dataset[attribute] || '').toLowerCase().includes(search)));
                });
            };
            filter(document.querySelector('[data-filter-employer]'), [...document.querySelectorAll('[data-employer-row]')], 'employerSearch');
            filter(document.querySelector('[data-filter-worker]'), [...document.querySelectorAll('[data-worker-row]')], 'workerSearch');

            const toggleAll = (master, selector) => master?.addEventListener('change', () => document.querySelectorAll(selector).forEach(input => input.checked = master.checked));
            toggleAll(document.querySelector('[data-select-all-workers]'), '[data-worker-checkbox]');
            toggleAll(document.querySelector('[data-select-all-documents]'), '[data-document-checkbox]');
        });
    </script>
@endsection
