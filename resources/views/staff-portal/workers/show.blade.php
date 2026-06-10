@extends('layouts.staff-portal', ['title' => 'รายละเอียดแรงงาน', 'pageTitle' => 'ข้อมูลแรงงานรายบุคคล'])

@push('head')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
</style>
@endpush

@section('content')
    @php
        $getDateStatus = function ($date) {
            if (! $date) return ['label' => '-', 'class' => 'text-slate-400', 'badge' => 'bg-slate-50 text-slate-400'];
            
            $days = now()->startOfDay()->diffInDays($date->copy()->startOfDay(), false);
            
            if ($days < 0) return ['label' => $date->format('d/m/Y'), 'class' => 'text-rose-600', 'badge' => 'bg-rose-50 text-rose-600 ring-rose-500/20'];
            if ($days <= 45) return ['label' => $date->format('d/m/Y'), 'class' => 'text-amber-600', 'badge' => 'bg-amber-50 text-amber-600 ring-amber-500/20'];
            
            return ['label' => $date->format('d/m/Y'), 'class' => 'text-slate-700', 'badge' => 'bg-blue-50 text-blue-600 ring-blue-500/20'];
        };

        $jobStatusClasses = [
            'pending' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
            'processing' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
            'waiting_document' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            'completed' => 'bg-slate-900 text-white',
            'cancelled' => 'bg-slate-100 text-slate-500 ring-slate-400/10',
            'rejected' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        ];
    @endphp

    <div class="space-y-8">
        <!-- Header Section -->
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-5">
                <a href="{{ route('staff.portal.workers.index') }}" 
                    class="grid h-12 w-12 place-items-center rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-slate-600 transition-all shadow-sm">
                    <i data-lucide="arrow-left" class="h-6 w-6"></i>
                </a>
                <div>
                    <h2 class="text-3xl font-bold tracking-tight text-slate-900">{{ $worker->full_name_th ?: $worker->full_name_en }}</h2>
                    <div class="mt-1 flex items-center gap-3 text-sm font-medium text-slate-500">
                        <span>{{ $worker->employer?->company_name ?? 'ไม่พบนายจ้าง' }}</span>
                        <div class="h-1 w-1 rounded-full bg-slate-300"></div>
                        <span @class([
                            'rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider',
                            'bg-blue-600 text-white' => $worker->is_active,
                            'bg-slate-100 text-slate-500' => ! $worker->is_active,
                        ])>
                            {{ $worker->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('staff.portal.workers.edit', $worker) }}" 
                    class="flex items-center gap-2 rounded-2xl bg-amber-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-amber-500/20 hover:bg-amber-600 transition-all">
                    <i data-lucide="edit-3" class="h-4 w-4"></i>
                    แก้ไขข้อมูล
                </a>
                <form action="{{ route('staff.portal.workers.destroy', $worker) }}" method="POST"
                    onsubmit="return confirm('ยืนยันการลบแรงงานรายนี้ใช่หรือไม่?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="flex items-center gap-2 rounded-2xl bg-rose-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-rose-500/20 hover:bg-rose-700 transition-all">
                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                        ลบแรงงาน
                    </button>
                </form>
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

        <!-- Profile & Summary Row -->
        <div class="grid gap-8 lg:grid-cols-4">
            <div class="lg:col-span-1 space-y-6">
                <!-- Profile Image Card -->
                <section class="glass-card rounded-3xl p-8 shadow-sm text-center">
                    <div class="relative mx-auto w-32 h-32">
                        @if ($worker->photo_path)
                            <img src="{{ asset('storage/' . $worker->photo_path) }}" alt="" 
                                class="h-full w-full rounded-3xl object-cover border-4 border-white shadow-xl">
                        @else
                            <div class="flex h-full w-full items-center justify-center rounded-3xl bg-slate-100 border-4 border-white text-slate-300 shadow-xl uppercase font-black text-2xl">
                                {{ mb_substr($worker->first_name_th ?: $worker->first_name_en, 0, 1) }}{{ mb_substr($worker->last_name_th ?: $worker->last_name_en, 0, 1) }}
                            </div>
                        @endif
                        <div class="absolute -bottom-2 -right-2 grid h-10 w-10 place-items-center rounded-2xl bg-blue-600 text-white shadow-lg">
                            <i data-lucide="verified" class="h-5 w-5"></i>
                        </div>
                    </div>
                    <div class="mt-6">
                        <h3 class="font-bold text-slate-900 text-lg">{{ $worker->full_name_en ?: '-' }}</h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Worker Profile</p>
                    </div>

                    <div class="mt-8 grid grid-cols-2 gap-3">
                        <div class="bg-slate-50/50 rounded-2xl p-4 text-center border border-slate-100/50">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-tighter">Jobs</p>
                            <p class="text-xl font-black text-slate-900">{{ number_format($summary['total_jobs']) }}</p>
                        </div>
                        <div class="bg-blue-50/50 rounded-2xl p-4 text-center border border-blue-100/50">
                            <p class="text-xs font-bold text-blue-400 uppercase tracking-tighter">Files</p>
                            <p class="text-xl font-black text-blue-600">{{ number_format($summary['documents']) }}</p>
                        </div>
                    </div>
                </section>

                <!-- Nationality & Gender Widget -->
                <section class="glass-card rounded-3xl p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">สัญชาติ</span>
                        <span class="text-sm font-bold text-slate-900">{{ $worker->nationality?->name_th ?: '-' }}</span>
                    </div>
                    <div class="h-px bg-slate-50"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">เพศ</span>
                        <span class="text-sm font-bold text-slate-900">{{ $worker->gender ?: '-' }}</span>
                    </div>
                </section>
            </div>

            <!-- Documents Grid (Top level) -->
            <div class="lg:col-span-3">
                <section class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                    @foreach ($expiryCards as $card)
                        @php $status = $getDateStatus($card['date']); @endphp
                        <article class="glass-card rounded-3xl p-6 hover-shadow group">
                            <div class="flex items-center justify-between mb-4">
                                <div class="h-10 w-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-600 transition-colors">
                                    <i data-lucide="{{ $card['icon'] }}" class="h-5 w-5"></i>
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-tighter text-slate-400">{{ $card['label'] }}</span>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-900">{{ $status['label'] }}</h4>
                                <p class="mt-1.5 inline-flex rounded-lg px-2 py-0.5 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset {{ $status['badge'] }}">
                                    @php
                                        $days = $card['date'] ? now()->startOfDay()->diffInDays($card['date']->copy()->startOfDay(), false) : null;
                                    @endphp
                                    @if($days === null) - @elseif($days < 0) หมดอายุแล้ว @elseif($days <= 45) อีก {{ $days }} วัน @else ปกติ @endif
                                </p>
                            </div>
                        </article>
                    @endforeach
                </section>

                <div class="mt-8 grid gap-8 lg:grid-cols-2">
                    <!-- Basic Information -->
                    <section class="glass-card rounded-3xl p-8 shadow-sm">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                                <i data-lucide="info" class="h-4 w-4"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900">ข้อมูลแรงงาน (Primary Data)</h3>
                        </div>
                        <div class="space-y-6">
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">เลข Passport</p>
                                    <p class="mt-1.5 font-bold text-slate-900 font-mono">{{ $worker->passport_number ?: '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">เลข Work Permit</p>
                                    <p class="mt-1.5 font-bold text-slate-900 font-mono">{{ $worker->wp_number ?: '-' }}</p>
                                </div>
                            </div>
                            <div class="h-px bg-slate-50"></div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">ชื่อเต็มภาษาไทย</p>
                                <p class="mt-1.5 font-bold text-slate-900">{{ $worker->full_name_th ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Full Name (English)</p>
                                <p class="mt-1.5 font-bold text-slate-900 uppercase">{{ $worker->full_name_en ?: '-' }}</p>
                            </div>
                        </div>
                    </section>

                    <!-- Document Files Widget -->
                    <section class="glass-card rounded-3xl p-8 shadow-sm">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="h-8 w-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                                <i data-lucide="file-text" class="h-4 w-4"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900">เอกสารแนบ (Attachments)</h3>
                        </div>
                        <div class="space-y-3">
                            @php
                                $files = [
                                    ['name' => 'Passport Copy', 'file' => $worker->passport_file],
                                    ['name' => 'Work Permit Copy', 'file' => $worker->wp_file],
                                    ['name' => 'Visa Copy', 'file' => $worker->visa_file],
                                    ['name' => '90-Days Report', 'file' => $worker->report_90_days_file],
                                ];
                            @endphp
                            @foreach($files as $f)
                                <div class="flex items-center justify-between rounded-2xl bg-slate-50/50 p-4 border border-slate-100/50">
                                    <span class="text-sm font-bold text-slate-700">{{ $f['name'] }}</span>
                                    @if($f['file'])
                                        <a href="{{ asset('storage/' . $f['file']) }}" target="_blank" 
                                            class="flex items-center gap-2 rounded-xl bg-white px-3 py-1.5 text-[10px] font-black uppercase text-blue-600 shadow-sm hover:bg-blue-600 hover:text-white transition-all">
                                            <i data-lucide="external-link" class="h-3 w-3"></i>
                                            เปิดไฟล์
                                        </a>
                                    @else
                                        <span class="text-[10px] font-bold text-slate-300 italic">ไม่มีข้อมูล</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <!-- Additional Documents -->
        <section class="glass-card rounded-3xl shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50/50 px-8 py-5 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">เอกสารเพิ่มเติมของแรงงาน</h3>
                    <p class="text-sm text-slate-500">อัปโหลดเอกสารประกอบได้หลายรายการ เช่น ใบรับรอง, ใบอนุญาต, หนังสือรับรอง ฯลฯ</p>
                </div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    รายการทั้งหมด {{ number_format($worker->documents->count()) }}
                </div>
            </div>

            <div class="grid gap-8 p-8 lg:grid-cols-2">
                <form action="{{ route('staff.portal.workers.documents.store', $worker) }}" method="POST" enctype="multipart/form-data" class="space-y-5 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                    @csrf
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                            <i data-lucide="upload" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-slate-900">อัปโหลดเอกสารใหม่</h4>
                            <p class="text-xs text-slate-500">เลือกประเภทเอกสาร แนบไฟล์ และเพิ่มวันหมดอายุถ้ามี</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">ประเภทเอกสาร <span class="text-rose-500">*</span></label>
                        <select name="document_master_id" required class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all appearance-none">
                            <option value="">-- เลือกประเภทเอกสาร --</option>
                            @foreach($documentMasters as $documentMaster)
                                <option value="{{ $documentMaster->id }}" @selected(old('document_master_id') == $documentMaster->id)>
                                    {{ $documentMaster->name }}{{ $documentMaster->code ? ' (' . $documentMaster->code . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500">วันหมดอายุ</label>
                            <input type="date" name="expiry_date" value="{{ old('expiry_date') }}"
                                class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500">ไฟล์แนบ <span class="text-rose-500">*</span></label>
                            <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.webp" required
                                class="w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 transition-all border border-slate-100 rounded-2xl p-1 bg-slate-50/50">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">หมายเหตุ</label>
                        <textarea name="note" rows="3" class="w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 py-3 text-sm outline-none focus:border-blue-400 focus:bg-white transition-all" placeholder="เช่น อัปโหลดสำเนาฉบับจริง, เพิ่มเอกสารฉบับล่าสุด">{{ old('note') }}</textarea>
                    </div>

                    <div class="flex items-center justify-end">
                        <button type="submit" class="inline-flex h-11 items-center gap-2 rounded-2xl bg-blue-600 px-5 text-sm font-bold text-white shadow-lg shadow-blue-500/20 hover:bg-blue-700 transition-all">
                            <i data-lucide="save" class="h-4 w-4"></i>
                            บันทึกเอกสาร
                        </button>
                    </div>
                </form>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-base font-bold text-slate-900">รายการเอกสารที่อัปโหลดแล้ว</h4>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ number_format($worker->documents->count()) }} รายการ</span>
                    </div>

                    <div class="space-y-3">
                        @forelse($worker->documents as $document)
                            @php
                                $docStatus = $getDateStatus($document->expiry_date);
                            @endphp
                            <article class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <h5 class="text-sm font-bold text-slate-900">{{ $document->documentMaster?->name ?? 'เอกสารไม่ระบุประเภท' }}</h5>
                                        <p class="mt-1 text-xs text-slate-500">{{ $document->documentMaster?->code ?? '-' }}</p>
                                    </div>
                                    <span class="inline-flex rounded-lg px-2 py-1 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset {{ $docStatus['badge'] }}">
                                        {{ $docStatus['label'] }}
                                    </span>
                                </div>

                                <div class="mt-4 grid gap-3 text-sm">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-slate-500">ไฟล์</span>
                                        @if($document->file_path)
                                            <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-blue-50 px-3 py-1.5 text-[10px] font-black uppercase text-blue-600 hover:bg-blue-600 hover:text-white transition-all">
                                                <i data-lucide="external-link" class="h-3 w-3"></i>
                                                เปิดไฟล์
                                            </a>
                                        @else
                                            <span class="text-xs text-slate-300">ไม่มีไฟล์</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-slate-500">วันหมดอายุ</span>
                                        <span class="font-bold text-slate-900">{{ $document->expiry_date?->format('d/m/Y') ?? '-' }}</span>
                                    </div>
                                    <div class="flex items-start justify-between gap-3">
                                        <span class="text-slate-500">หมายเหตุ</span>
                                        <span class="max-w-[14rem] text-right text-slate-700">{{ $document->note ?: '-' }}</span>
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-end gap-2">
                                    <form action="{{ route('staff.portal.workers.documents.destroy', [$worker, $document]) }}" method="POST" onsubmit="return confirm('ต้องการลบเอกสารนี้ใช่ไหม?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-rose-50 px-3 py-1.5 text-[10px] font-black uppercase text-rose-600 hover:bg-rose-600 hover:text-white transition-all">
                                            <i data-lucide="trash-2" class="h-3 w-3"></i>
                                            ลบ
                                        </button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50/50 p-8 text-center">
                                <i data-lucide="file-text" class="mx-auto h-8 w-8 text-slate-300"></i>
                                <p class="mt-3 text-sm font-bold text-slate-700">ยังไม่มีเอกสารเพิ่มเติม</p>
                                <p class="mt-1 text-xs text-slate-500">อัปโหลดเอกสารเพิ่มได้จากฟอร์มด้านซ้าย</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <!-- Job History Section -->
        <section class="glass-card overflow-hidden rounded-3xl shadow-sm mt-8">
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-8 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white shadow-lg">
                        <i data-lucide="clipboard-list" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">ประวัติการดำเนินงาน (Job History)</h3>
                        <p class="text-sm text-slate-500">ใบงานทั้งหมดที่เคยเปิดให้แรงงานรายนี้</p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 text-xs font-bold uppercase tracking-wider text-slate-400">
                            <th class="px-8 py-5">เลขที่ใบงาน</th>
                            <th class="px-4 py-5">ประเภทบริการ</th>
                            <th class="px-4 py-5 text-center">อัปเดตล่าสุด</th>
                            <th class="px-4 py-5 text-center">สถานะ</th>
                            <th class="px-4 py-5 text-right">ยอดคงเหลือ</th>
                            <th class="px-8 py-5 text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($worker->jobOrders as $job)
                            <tr class="group hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-5">
                                    <p class="font-black text-slate-900 uppercase tracking-tighter">{{ $job->job_number }}</p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="font-bold text-slate-700">{{ $job->service?->name ?: '-' }}</p>
                                </td>
                                <td class="px-4 py-5 text-center text-slate-500 font-medium">
                                    {{ $job->updated_at?->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-5 text-center">
                                    <span @class([
                                        'rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset inline-block min-w-[100px]',
                                        $jobStatusClasses[$job->status] ?? 'bg-slate-100 text-slate-500'
                                    ])>
                                        {{ str($job->status)->replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="px-4 py-5 text-right font-bold text-slate-900">
                                    ฿{{ number_format($job->getRemainingAmount(), 2) }}
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <a href="{{ route('staff.portal.job-orders.show', $job) }}" 
                                        class="grid h-8 w-8 place-items-center rounded-lg bg-slate-100 text-slate-400 hover:text-slate-600 transition-all opacity-0 group-hover:opacity-100">
                                        <i data-lucide="chevron-right" class="h-4 w-4"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-16 text-center">
                                    <p class="text-slate-400">ไม่พบประวัติใบงานของแรงงานรายนี้</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
