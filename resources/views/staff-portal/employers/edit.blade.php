@extends('layouts.staff-portal', ['title' => 'แก้ไขข้อมูลนายจ้าง', 'pageTitle' => 'จัดการข้อมูลนายจ้าง'])

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
                <a href="{{ route('staff.portal.employers.show', $employer) }}" 
                    class="grid h-12 w-12 place-items-center rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-slate-600 transition-all shadow-sm">
                    <i data-lucide="arrow-left" class="h-6 w-6"></i>
                </a>
                <div>
                    <h2 class="text-3xl font-bold tracking-tight text-slate-900">แก้ไขข้อมูลนายจ้าง</h2>
                    <p class="mt-1 text-slate-500">ปรับปรุงรายละเอียดบริษัทหรือสถานประกอบการ</p>
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

        <form action="{{ route('staff.portal.employers.update', $employer) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- Company Info -->
            <section class="glass-card rounded-3xl p-8 shadow-sm">
                <div class="flex items-center gap-3 mb-8 pb-5 border-b border-slate-50">
                    <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                        <i data-lucide="building" class="h-4 w-4"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">ข้อมูลบริษัท / สถานประกอบการ</h3>
                </div>

                <div class="grid md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">รหัสบริษัท <span class="text-rose-500">*</span></label>
                        <input type="text" name="company_code" value="{{ old('company_code', $employer->company_code) }}" required
                            class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">ชื่อนายจ้าง / ชื่อสถานประกอบการ <span class="text-rose-500">*</span></label>
                        <input type="text" name="company_name" value="{{ old('company_name', $employer->company_name) }}" required
                            class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">เลขประจำตัวผู้เสียภาษี / เลขนิติบุคคล</label>
                        <input type="text" name="tax_id" value="{{ old('tax_id', $employer->tax_id) }}"
                            class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">เปลี่ยนโลโก้บริษัท</label>
                        <div class="flex items-center gap-4">
                            @if($employer->logo)
                                <img src="{{ asset('storage/' . $employer->logo) }}" class="h-12 w-12 rounded-xl object-contain bg-white border border-slate-100 p-1">
                            @endif
                            <input type="file" name="logo_file" accept=".jpg,.jpeg,.png"
                                class="flex-1 text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 transition-all border border-slate-100 rounded-2xl p-1 bg-slate-50/50">
                        </div>
                        <p class="text-xs text-amber-600">ขนาดรูปไม่เกิน 3 MB</p>
                    </div>
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">ที่อยู่สำนักงาน</label>
                        <textarea name="address" rows="3"
                            class="w-full rounded-2xl border border-slate-100 bg-slate-50/50 p-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all">{{ old('address', $employer->address) }}</textarea>
                    </div>
                </div>
            </section>

            <!-- Contact Info -->
            <section class="glass-card rounded-3xl p-8 shadow-sm">
                <div class="flex items-center gap-3 mb-8 pb-5 border-b border-slate-50">
                    <div class="h-8 w-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                        <i data-lucide="user" class="h-4 w-4"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">ข้อมูลผู้ติดต่อ</h3>
                </div>

                <div class="grid md:grid-cols-2 gap-8">
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">ชื่อผู้ติดต่อหลัก <span class="text-rose-500">*</span></label>
                        <input type="text" name="contact_name" value="{{ old('contact_name', $employer->contact_name) }}" required
                            class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">เบอร์โทรศัพท์ <span class="text-rose-500">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone', $employer->phone) }}" required
                            class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">อีเมลสำหรับติดต่อ <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $employer->email) }}" required
                            class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all">
                    </div>
                </div>

                <div class="mt-8 space-y-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500">หมายเหตุเพิ่มเติม</label>
                    <textarea name="notes" rows="3"
                        class="w-full rounded-2xl border border-slate-100 bg-slate-50/50 p-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all">{{ old('notes', $employer->notes) }}</textarea>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-50">
                    <label class="group flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <div class="relative">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $employer->is_active))
                                class="peer sr-only">
                            <div class="h-6 w-11 rounded-full bg-slate-200 transition-colors peer-checked:bg-blue-600"></div>
                            <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition-transform peer-checked:translate-x-5"></div>
                        </div>
                        <span class="text-sm font-bold text-slate-700 group-hover:text-blue-600 transition-colors">เปิดใช้งานบัญชี (Active Status)</span>
                    </label>
                </div>
            </section>

            <div class="flex items-center justify-end gap-4 pb-12">
                <a href="{{ route('staff.portal.employers.show', $employer) }}"
                    class="h-12 px-8 flex items-center justify-center rounded-2xl bg-white border border-slate-200 text-sm font-bold text-slate-500 hover:bg-slate-50 transition-all">
                    ยกเลิก
                </a>
                <button type="submit"
                    class="h-12 px-10 flex items-center justify-center gap-2 rounded-2xl bg-blue-600 text-sm font-bold text-white shadow-lg shadow-blue-500/20 hover:bg-blue-700 transition-all focus:ring-4 focus:ring-blue-100">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    บันทึกการแก้ไข
                </button>
            </div>
        </form>
    </div>
@endsection
