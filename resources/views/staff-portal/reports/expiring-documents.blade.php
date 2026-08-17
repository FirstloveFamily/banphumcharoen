@extends('layouts.staff-portal', ['title' => 'เอกสารใกล้หมดอายุ', 'pageTitle' => 'รายงานเอกสารใกล้หมดอายุ'])

@section('content')
    @php
        $daysUntil = fn ($date) => now()->startOfDay()->diffInDays($date->copy()->startOfDay(), false);
    @endphp

    <div class="space-y-8">
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">รายงานเอกสารใกล้หมดอายุ</h2>
                <p class="mt-1 text-lg text-slate-500">แสดงเอกสารที่มีวันหมดอายุอยู่ในช่วงวันที่ที่เลือก</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('staff.portal.reports.expiring-documents.export', request()->query()) }}" class="inline-flex h-12 items-center gap-2 rounded-2xl bg-emerald-600 px-5 text-sm font-bold text-white shadow-sm hover:bg-emerald-700">
                    <i data-lucide="file-spreadsheet" class="h-5 w-5"></i>
                    Export Excel
                </a>
                <div class="rounded-2xl bg-amber-50 px-5 py-3 text-sm font-bold text-amber-700">
                    พบ {{ number_format($reportRows->total()) }} รายการ
                </div>
            </div>
        </header>

        <section class="glass-card relative z-30 rounded-3xl p-6 shadow-sm">
            <form method="GET" class="grid gap-5 md:grid-cols-2 lg:grid-cols-[180px_180px_220px_220px_180px_auto] lg:items-end">
                <div>
                    <label for="date_from" class="text-xs font-bold uppercase tracking-wider text-slate-500">ตั้งแต่วันที่</label>
                    <input id="date_from" type="date" name="date_from" value="{{ $dateFrom->format('Y-m-d') }}" class="mt-2 h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white">
                </div>
                <div>
                    <label for="date_to" class="text-xs font-bold uppercase tracking-wider text-slate-500">ถึงวันที่</label>
                    <input id="date_to" type="date" name="date_to" value="{{ $dateTo->format('Y-m-d') }}" class="mt-2 h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white">
                </div>
                <div>
                    <label for="document_type" class="text-xs font-bold uppercase tracking-wider text-slate-500">ประเภทเอกสาร</label>
                    <select id="document_type" name="document_type" class="mt-2 h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white">
                        @foreach ($documentTypeOptions as $value => $label)
                            <option value="{{ $value }}" @selected($documentType === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="employer_id" class="text-xs font-bold uppercase tracking-wider text-slate-500">บริษัทนายจ้าง</label>
                    <div class="relative mt-2" data-employer-autocomplete>
                        <input type="hidden" name="employer_id" value="{{ $employerId ?: 0 }}" data-employer-id>
                        <input id="employer_id" type="text" value="{{ $employerId ? $employers->firstWhere('id', $employerId)?->company_name : '' }}" placeholder="พิมพ์ชื่อหรือรหัสบริษัท..." autocomplete="off" data-employer-input class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 pr-10 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white">
                        <button type="button" data-employer-clear class="absolute right-2 top-1/2 hidden h-8 w-8 -translate-y-1/2 items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="ล้างบริษัท"><i data-lucide="x" class="h-4 w-4"></i></button>
                        <div data-employer-options class="absolute inset-x-0 top-14 z-50 hidden max-h-64 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                            <button type="button" data-employer-option data-id="0" data-label="ทุกบริษัท" class="flex w-full items-center rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-600 hover:bg-blue-50 hover:text-blue-700">ทุกบริษัท</button>
                            @foreach ($employers as $employer)
                                <button type="button" data-employer-option data-id="{{ $employer->id }}" data-label="{{ $employer->company_name }}" data-search="{{ $employer->company_name }} {{ $employer->company_code }}" class="flex w-full items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-700">
                                    <span class="truncate">{{ $employer->company_name }}</span>
                                    <span class="shrink-0 text-xs font-medium text-slate-400">{{ $employer->company_code ?: '-' }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div>
                    <label for="active" class="text-xs font-bold uppercase tracking-wider text-slate-500">สถานะแรงงาน</label>
                    <select id="active" name="active" class="mt-2 h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white">
                        <option value="active" @selected($activeStatus === 'active')>Active</option>
                        <option value="inactive" @selected($activeStatus === 'inactive')>Inactive</option>
                        <option value="" @selected($activeStatus === '')>ทั้งหมด</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="h-12 flex-1 rounded-2xl bg-slate-900 px-5 text-sm font-bold text-white hover:bg-slate-800">ค้นหา</button>
                    <a href="{{ route('staff.portal.reports.expiring-documents') }}" class="grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-500 hover:bg-slate-200" aria-label="ล้างตัวกรอง"><i data-lucide="refresh-cw" class="h-5 w-5"></i></a>
                </div>
            </form>
        </section>

        <section class="glass-card relative z-0 overflow-hidden rounded-3xl shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[950px] text-left text-sm">
                    <thead><tr class="bg-slate-50/60 text-xs font-bold uppercase tracking-wider text-slate-400"><th class="px-8 py-5">แรงงาน / นายจ้าง</th><th class="px-4 py-5">เอกสาร</th><th class="px-4 py-5 text-center">วันหมดอายุ</th><th class="px-4 py-5 text-center">เหลืออีก</th><th class="px-4 py-5 text-center">สถานะเอกสาร</th><th class="px-8 py-5 text-right">ไฟล์</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($reportRows as $row)
                            @php($days = $daysUntil($row['expiry_date']))
                            @php($statusDefinition = $workflowStatuses->get($row['status']))
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-8 py-5"><p class="font-bold text-slate-900">{{ $row['worker']->full_name_th ?: $row['worker']->full_name_en }}</p><p class="mt-1 text-xs text-slate-500">{{ $row['worker']->employer?->company_name ?: '-' }}</p></td>
                                <td class="px-4 py-5 font-semibold text-slate-700">{{ $row['label'] }}</td>
                                <td class="px-4 py-5 text-center font-bold {{ $days < 0 ? 'text-rose-600' : 'text-amber-600' }}">{{ $row['expiry_date']->format('d/m/Y') }}</td>
                                <td class="px-4 py-5 text-center"><span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $days < 0 ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700' }}">{{ $days < 0 ? 'หมดอายุแล้ว' : 'อีก ' . $days . ' วัน' }}</span></td>
                                <td class="px-4 py-5 text-center"><span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $statusDefinition?->color_class ?? 'bg-slate-100 text-slate-500' }}">{{ $statusDefinition?->name_th ?? 'รอส่งเอกสาร' }}</span></td>
                                <td class="px-8 py-5 text-right">@if ($row['file_path'])<a href="{{ asset('storage/' . $row['file_path']) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700 hover:bg-blue-100"><i data-lucide="file-text" class="h-4 w-4"></i> เปิดไฟล์</a>@else<span class="text-xs text-slate-400">ไม่มีไฟล์</span>@endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-8 py-20 text-center text-slate-500">ไม่พบเอกสารในช่วงวันที่เลือก</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($reportRows->hasPages())<div class="border-t border-slate-100 bg-slate-50/50 px-8 py-4">{{ $reportRows->links() }}</div>@endif
        </section>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const wrapper = document.querySelector('[data-employer-autocomplete]');
            if (!wrapper) return;
            const input = wrapper.querySelector('[data-employer-input]');
            const hidden = wrapper.querySelector('[data-employer-id]');
            const options = wrapper.querySelector('[data-employer-options]');
            const clear = wrapper.querySelector('[data-employer-clear]');
            const choices = [...wrapper.querySelectorAll('[data-employer-option]')];

            const showOptions = () => {
                options.classList.remove('hidden');
                choices.forEach(choice => {
                    const search = (choice.dataset.search || choice.dataset.label || '').toLowerCase();
                    const keyword = input.value.toLowerCase().trim();
                    choice.classList.toggle('hidden', keyword !== '' && !search.includes(keyword));
                });
            };
            const updateClear = () => clear.classList.toggle('hidden', input.value.trim() === '');

            input.addEventListener('focus', showOptions);
            input.addEventListener('input', () => {
                hidden.value = '0';
                showOptions();
                updateClear();
            });
            choices.forEach(choice => choice.addEventListener('click', () => {
                input.value = choice.dataset.id === '0' ? '' : choice.dataset.label;
                hidden.value = choice.dataset.id;
                options.classList.add('hidden');
                updateClear();
            }));
            clear.addEventListener('click', () => {
                input.value = '';
                hidden.value = '0';
                options.classList.add('hidden');
                updateClear();
                input.focus();
            });
            document.addEventListener('click', event => {
                if (!wrapper.contains(event.target)) options.classList.add('hidden');
            });
            updateClear();
        });
    </script>
@endsection
