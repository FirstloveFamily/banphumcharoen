@extends('layouts.staff-portal', ['title' => 'เพิ่มแรงงานใหม่', 'pageTitle' => 'จัดการข้อมูลแรงงาน'])

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
                        <select name="employer_id" required class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all appearance-none">
                            <option value="">-- เลือกบริษัท --</option>
                            @foreach($employers as $employer)
                                <option value="{{ $employer->id }}" @selected(old('employer_id', request('employer_id')) == $employer->id)>{{ $employer->company_name }}</option>
                            @endforeach
                        </select>
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
