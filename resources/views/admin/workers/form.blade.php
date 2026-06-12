@extends('layouts.manager', ['title' => $worker ? 'แก้ไขข้อมูลแรงงาน' : 'เพิ่มแรงงานใหม่', 'pageTitle' => 'แรงงาน การจัดการ'])

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
    <div class="space-y-8 max-w-5xl mx-auto">
        <!-- Header Section -->
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-5">
                <a href="{{ route('manager.workers.index') }}" 
                    class="grid h-12 w-12 place-items-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-slate-600 transition-all shadow-sm">
                    <i data-lucide="arrow-left" class="h-6 w-6"></i>
                </a>
                <div>
                    <h2 class="text-3xl font-bold tracking-tight text-[#0b2f52] italic">
                        {{ $worker ? 'แก้ไข แรงงาน โปรไฟล์' : 'เพิ่มแรงงานใหม่' }}
                    </h2>
                    <p class="mt-1 text-slate-500 font-medium">
                        {{ $worker ? 'ปรับปรุงข้อมูลส่วนบุคคลและสถานะเอกสารสำคัญของแรงงาน' : 'ลงทะเบียนแรงงานใหม่เข้าสู่ระบบพร้อมแนบเอกสารประจำตัว' }}
                    </p>
                </div>
            </div>
        </header>

        @if ($errors->any())
            <div class="rounded-lg bg-rose-50 p-6 border border-rose-100">
                <div class="flex gap-3">
                    <i data-lucide="alert-octagon" class="h-5 w-5 text-rose-500 shrink-0"></i>
                    <div>
                        <h4 class="text-sm font-bold text-rose-800 uppercase tracking-wide">ข้อมูลไม่ถูกต้อง</h4>
                        <ul class="mt-2 list-disc pl-5 text-sm text-rose-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ $worker ? route('manager.workers.update', $worker) : route('manager.workers.store') }}" 
            method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @if ($worker)
                @method('PUT')
            @endif

            <!-- Identity & นายจ้าง Section -->
            <section class="glass-card rounded-lg p-8 shadow-sm">
                <div class="flex items-center gap-3 mb-8 pb-5 border-b border-slate-50">
                    <div class="h-8 w-8 rounded-lg bg-[#fff9e8] flex items-center justify-center text-[#0b2f52]">
                        <i data-lucide="user" class="h-4 w-4"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#0b2f52] uppercase tracking-tighter italic">ข้อมูลประจำตัว</h3>
                </div>

                <div class="grid md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 px-1">ชื่อ-นามสกุล (Thai) <span class="text-rose-500">*</span></label>
                        <input type="text" name="full_name_th" value="{{ old('full_name_th', $worker?->full_name_th) }}" required
                            class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 px-1">ชื่อ-นามสกุล (English) <span class="text-rose-500">*</span></label>
                        <input type="text" name="full_name_en" value="{{ old('full_name_en', $worker?->full_name_en) }}" required
                            class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all uppercase">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 px-1">เลขพาสปอร์ต</label>
                        <input type="text" name="passport_number" value="{{ old('passport_number', $worker?->passport_number) }}"
                            class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold font-mono outline-none focus:border-[#c9a227] focus:bg-white transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 px-1">เลขใบอนุญาตทำงาน</label>
                        <input type="text" name="wp_number" value="{{ old('wp_number', $worker?->wp_number) }}"
                            class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold font-mono outline-none focus:border-[#c9a227] focus:bg-white transition-all">
                    </div>
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 px-1">Assign นายจ้าง <span class="text-rose-500">*</span></label>
                        <select name="employer_id" required class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all appearance-none">
                            <option value="">-- Select ลูกค้า / นายจ้าง --</option>
                            @foreach ($employers as $id => $name)
                                <option value="{{ $id }}" @selected(old('employer_id', $worker?->employer_id) == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

            <!-- Expiry Lifecycle Section -->
            <section class="glass-card rounded-lg p-8 shadow-sm">
                <div class="flex items-center gap-3 mb-8 pb-5 border-b border-slate-50">
                    <div class="h-8 w-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                        <i data-lucide="calendar-days" class="h-4 w-4"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#0b2f52] uppercase tracking-tighter italic">วันหมดอายุเอกสาร</h3>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">พาสปอร์ตหมดอายุ</label>
                        <input type="date" name="passport_expiry" value="{{ old('passport_expiry', $worker?->passport_expiry?->format('Y-m-d')) }}"
                            class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">ใบอนุญาตทำงานหมดอายุ</label>
                        <input type="date" name="wp_expiry" value="{{ old('wp_expiry', $worker?->wp_expiry?->format('Y-m-d')) }}"
                            class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">วีซ่าหมดอายุ</label>
                        <input type="date" name="visa_expiry" value="{{ old('visa_expiry', $worker?->visa_expiry?->format('Y-m-d')) }}"
                            class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">ครบกำหนดรายงาน 90 วัน</label>
                        <input type="date" name="report_90_days_due" value="{{ old('report_90_days_due', $worker?->report_90_days_due?->format('Y-m-d')) }}"
                            class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all">
                    </div>
                </div>
            </section>

            <!-- Assets & เอกสาร Section -->
            <section class="glass-card rounded-lg p-8 shadow-sm">
                <div class="flex items-center gap-3 mb-8 pb-5 border-b border-slate-50">
                    <div class="h-8 w-8 rounded-lg bg-rose-100 flex items-center justify-center text-rose-600">
                        <i data-lucide="file-text" class="h-4 w-4"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#0b2f52] uppercase tracking-tighter italic">แรงงาน Assets & ไฟล์</h3>
                </div>

                <!-- Photo อัปโหลด -->
                <div class="mb-10">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 px-1 mb-4 block">รูปถ่ายประจำตัว</label>
                    <div class="flex items-center gap-6">
                        @if ($worker?->photo_path)
                            <img src="{{ Storage::url($worker->photo_path) }}" class="h-24 w-24 rounded-lg object-cover border-2 border-white shadow-md">
                        @else
                            <div class="h-24 w-24 rounded-lg bg-slate-100 flex items-center justify-center text-slate-300 border-2 border-dashed border-slate-200">
                                <i data-lucide="user" class="h-8 w-8"></i>
                            </div>
                        @endif
                        <div class="flex-1">
                            <input type="file" name="photo_path" accept="image/*"
                                class="w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-[#0b2f52] file:text-white hover:file:bg-[#0b2f52] transition-all border border-slate-100 rounded-lg p-1 bg-slate-50/50">
                            <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase">รองรับ JPG, PNG · สูงสุด 3MB</p>
                            <p class="text-xs text-amber-600 mt-1">ขนาดรูปไม่เกิน 3 MB</p>
                        </div>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-8">
                    <!-- ไฟล์ -->
                    @php
                        $fileFields = [
                            'passport_file' => 'พาสปอร์ต Copy',
                            'wp_file' => 'ใบอนุญาตทำงาน Copy',
                            'visa_file' => 'สำเนาวีซ่า',
                            'report_90_days_file' => 'รายงาน 90 วัน'
                        ];
                    @endphp
                    @foreach($fileFields as $field => $label)
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">{{ $label }}</label>
                            <div class="flex items-center gap-3">
                                @if ($worker?->{$field})
                                    <a href="{{ Storage::url($worker->{$field}) }}" target="_blank" class="grid h-10 w-10 place-items-center rounded-lg bg-[#fff9e8] text-[#0b2f52] hover:bg-[#fff9e8] transition-all border border-[#c9a227]/30 shadow-sm">
                                        <i data-lucide="external-link" class="h-4 w-4"></i>
                                    </a>
                                @endif
                                <input type="file" name="{{ $field }}" accept=".pdf,.jpg,.jpeg,.png"
                                    class="flex-1 text-xs text-slate-400 file:mr-4 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-[9px] file:font-black file:uppercase file:bg-[#0b2f52] file:text-white transition-all border border-slate-100 rounded-lg p-1 bg-slate-50/50">
                            </div>
                            <p class="text-xs text-amber-600 mt-1">รูปไม่เกิน 3 MB, เอกสารไม่เกิน 10 MB</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="flex items-center justify-end gap-4 pb-12">
                <a href="{{ route('manager.workers.index') }}"
                    class="h-12 px-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-xs font-black uppercase tracking-[0.2em] text-slate-500 hover:bg-slate-50 transition-all">
                    ยกเลิก
                </a>
                <button type="submit"
                    class="h-12 px-10 flex items-center justify-center gap-3 rounded-lg bg-[#0b2f52] text-xs font-black uppercase tracking-[0.2em] text-white shadow-xl shadow-slate-900/20 hover:bg-[#123e68] transition-all focus:ring-4 focus:ring-[#c9a227]/20">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    {{ $worker ? 'บันทึกการแก้ไข' : 'Register แรงงาน' }}
                </button>
            </div>
        </form>
    </div>
@endsection
