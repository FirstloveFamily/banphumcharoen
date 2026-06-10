@extends('layouts.staff-portal', ['title' => 'สร้างใบส่งงาน', 'pageTitle' => 'ใบส่งงาน'])

@section('content')
    <div class="space-y-8">
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('staff.portal.delivery-sheets.index') }}"
                    class="grid h-12 w-12 place-items-center rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-slate-600 transition-all shadow-sm">
                    <i data-lucide="arrow-left" class="h-6 w-6"></i>
                </a>
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.3em] text-slate-400">New Delivery Sheet</p>
                    <h1 class="mt-1 text-3xl font-black tracking-tight text-[#0b2f52]">สร้างใบส่งงาน</h1>
                    <p class="mt-2 text-sm text-slate-500">เลือกนายจ้างก่อน แล้วค่อยเลือกใบงานที่ต้องการรวมในชุดนี้</p>
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

        <div class="grid gap-8 lg:grid-cols-[380px_1fr]">
            <section class="portal-card p-6 space-y-6">
                <div class="space-y-2">
                    <h2 class="text-lg font-bold text-[#0b2f52]">เลือกนายจ้าง</h2>
                    <p class="text-sm text-slate-500">ใช้ตัวกรองนี้เพื่อโหลดใบงานของนายจ้างคนนั้น</p>
                </div>

                <form method="GET" action="{{ route('staff.portal.delivery-sheets.create') }}" class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">นายจ้าง</label>
                        <select name="employer_id" class="portal-select h-12 w-full px-4 text-sm" onchange="this.form.submit()">
                            <option value="">-- เลือกนายจ้าง --</option>
                            @foreach($employers as $employer)
                                <option value="{{ $employer->id }}" @selected(optional($selectedEmployer)->id == $employer->id)>{{ $employer->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="portal-btn-secondary inline-flex h-11 items-center gap-2 px-4 text-sm font-bold">
                        <i data-lucide="filter" class="h-4 w-4"></i>
                        โหลดใบงาน
                    </button>
                </form>

                <div class="rounded-3xl bg-slate-50 p-4 text-sm text-slate-600">
                    <p class="font-bold text-slate-900">คำแนะนำ</p>
                    <p class="mt-2">ระบบจะไม่อนุญาตให้เลือกใบงานที่ถูกใส่ในใบส่งงานอื่นแล้ว เพื่อกันข้อมูลซ้ำ</p>
                </div>
            </section>

            <section class="space-y-6">
                <form action="{{ route('staff.portal.delivery-sheets.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="employer_id" value="{{ $selectedEmployer?->id }}">

                    <div class="portal-card p-6 space-y-6">
                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">วันที่ใบส่งงาน</label>
                                <input type="date" name="sheet_date" value="{{ old('sheet_date', now()->format('Y-m-d')) }}" class="portal-input h-12 w-full px-4 text-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">สถานะเริ่มต้น</label>
                                <select name="status" class="portal-select h-12 w-full px-4 text-sm">
                                    <option value="draft" @selected(old('status', 'draft') === 'draft')>ร่าง</option>
                                    <option value="submitted" @selected(old('status') === 'submitted')>ส่งแล้ว</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500">หมายเหตุ</label>
                            <textarea name="note" rows="3" class="portal-textarea w-full px-4 py-3 text-sm" placeholder="เช่น ส่งงานรอบแรก, รวมเอกสารตรวจสอบ">{{ old('note') }}</textarea>
                        </div>
                    </div>

                    <div class="portal-card overflow-hidden">
                        <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-bold text-[#0b2f52]">ใบงานที่พร้อมเลือก</h3>
                                <p class="text-sm text-slate-500">
                                    @if($selectedEmployer)
                                        {{ $selectedEmployer->company_name }} - {{ number_format($availableJobOrders->count()) }} รายการ
                                    @else
                                        กรุณาเลือกนายจ้างด้านซ้ายก่อน
                                    @endif
                                </p>
                            </div>
                            @if($selectedEmployer && $availableJobOrders->count())
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ number_format($availableJobOrders->count()) }} งาน</span>
                            @endif
                        </div>

                        @if($selectedEmployer)
                            <div class="max-h-[720px] overflow-y-auto p-4">
                                <div class="grid gap-4">
                                    @forelse($availableJobOrders as $jobOrder)
                                        <label class="cursor-pointer rounded-3xl border border-slate-100 bg-white p-4 shadow-sm hover:border-blue-200 transition-all">
                                            <div class="flex items-start gap-4">
                                                <input type="checkbox" name="job_order_ids[]" value="{{ $jobOrder->id }}" class="mt-1 h-4 w-4 rounded border-slate-300 text-[#0b2f52] focus:ring-[#0b2f52]">
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span class="font-bold text-[#0b2f52]">{{ $jobOrder->job_number }}</span>
                                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset {{ $jobOrder->status_badge_class }}">
                                                            {{ $jobOrder->status_label }}
                                                        </span>
                                                    </div>
                                                    <p class="mt-1 text-sm font-medium text-slate-700">{{ $jobOrder->worker?->full_name_th ?: $jobOrder->worker?->full_name_en }}</p>
                                                    <p class="mt-1 text-xs text-slate-500">{{ $jobOrder->service?->name ?? '-' }} · Due {{ $jobOrder->due_date?->format('d/m/Y') ?? '-' }}</p>
                                                </div>
                                            </div>
                                        </label>
                                    @empty
                                        <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50/60 p-10 text-center">
                                            <i data-lucide="clipboard-list" class="mx-auto h-8 w-8 text-slate-300"></i>
                                            <p class="mt-3 text-sm font-bold text-slate-700">ไม่มีใบงานที่พร้อมเพิ่มในใบส่งงาน</p>
                                            <p class="mt-1 text-xs text-slate-500">อาจถูกใช้ในใบส่งงานอื่นแล้ว หรือยังไม่มีใบงานของนายจ้างนี้</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @else
                            <div class="p-10 text-center text-sm text-slate-500">
                                เลือกนายจ้างก่อน เพื่อให้ระบบแสดงใบงานที่สามารถนำมารวมในใบส่งงานได้
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('staff.portal.delivery-sheets.index') }}" class="portal-btn-secondary inline-flex h-12 items-center px-5 text-sm font-bold">
                            ยกเลิก
                        </a>
                        <button type="submit" class="portal-btn-primary inline-flex h-12 items-center gap-2 px-6 text-sm font-bold" @disabled(! $selectedEmployer)>
                            <i data-lucide="save" class="h-4 w-4"></i>
                            บันทึกใบส่งงาน
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection
