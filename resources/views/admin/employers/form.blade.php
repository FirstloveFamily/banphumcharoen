@extends('layouts.manager', ['title' => $employer ? 'แก้ไขข้อมูลนายจ้าง' : 'ลงทะเบียนนายจ้างใหม่', 'pageTitle' => 'นายจ้าง การจัดการ'])

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
                <a href="{{ route('manager.employers.index') }}" 
                    class="grid h-12 w-12 place-items-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-slate-600 transition-all shadow-sm">
                    <i data-lucide="arrow-left" class="h-6 w-6"></i>
                </a>
                <div>
                    <h2 class="text-3xl font-bold tracking-tight text-[#0b2f52] italic">
                        {{ $employer ? 'แก้ไขข้อมูลลูกค้า' : 'ลงทะเบียนลูกค้าใหม่' }}
                    </h2>
                    <p class="mt-1 text-slate-500 font-medium">
                        {{ $employer ? 'ปรับปรุงข้อมูลบริษัทและรายละเอียดการติดต่อของคู่ค้า' : 'เพิ่มข้อมูลบริษัทนายจ้างรายใหม่เข้าสู่ระบบบริหารจัดการ' }}
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

        <form action="{{ $employer ? route('manager.employers.update', $employer) : route('manager.employers.store') }}" 
            method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @if ($employer)
                @method('PUT')
            @endif

            <!-- Company Info Section -->
            <section class="glass-card rounded-lg p-8 shadow-sm">
                <div class="flex items-center gap-3 mb-8 pb-5 border-b border-slate-50">
                    <div class="h-8 w-8 rounded-lg bg-[#fff9e8] flex items-center justify-center text-[#0b2f52]">
                        <i data-lucide="building" class="h-4 w-4"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#0b2f52] uppercase tracking-tighter italic">ข้อมูลบริษัท</h3>
                </div>

                <div class="grid md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 px-1">รหัสบริษัท <span class="text-rose-500">*</span></label>
                        <input type="text" name="company_code" value="{{ old('company_code', $employer?->company_code) }}" required
                            placeholder="เช่น BPK001"
                            class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all"
                            @if($employer) readonly @endif>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 px-1">ชื่อบริษัท <span class="text-rose-500">*</span></label>
                        <input type="text" name="company_name" value="{{ old('company_name', $employer?->company_name) }}" required
                            placeholder="ชื่อบริษัทตามทะเบียน"
                            class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 px-1">เลขประจำตัวผู้เสียภาษี</label>
                        <input type="text" name="tax_id" value="{{ old('tax_id', $employer?->tax_id) }}"
                            placeholder="เลข 13 หลัก"
                            class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold font-mono outline-none focus:border-[#c9a227] focus:bg-white transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 px-1">โลโก้บริษัท (.jpg, .png)</label>
                        <div class="flex items-center gap-4">
                            @if($employer?->logo)
                                <img src="{{ asset('storage/' . $employer->logo) }}" class="h-12 w-12 rounded-lg object-contain bg-white border border-slate-100 p-1">
                            @endif
                            <input type="file" name="logo" accept="image/*"
                                class="flex-1 text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-[#0b2f52] file:text-white hover:file:bg-[#0b2f52] transition-all border border-slate-100 rounded-lg p-1 bg-slate-50/50">
                        </div>
                        <p class="text-xs text-amber-600">ขนาดรูปไม่เกิน 3 MB</p>
                    </div>
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 px-1">ที่อยู่จดทะเบียน</label>
                        <textarea name="address" rows="3" placeholder="ที่อยู่สำนักงานแบบเต็ม..."
                            class="w-full rounded-lg border border-slate-100 bg-slate-50/50 p-4 text-sm font-medium outline-none focus:border-[#c9a227] focus:bg-white transition-all">{{ old('address', $employer?->address) }}</textarea>
                    </div>
                </div>
            </section>

            <!-- Contact Section -->
            <section class="glass-card rounded-lg p-8 shadow-sm">
                <div class="flex items-center gap-3 mb-8 pb-5 border-b border-slate-50">
                    <div class="h-8 w-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <i data-lucide="phone" class="h-4 w-4"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#0b2f52] uppercase tracking-tighter italic">ข้อมูลติดต่อ</h3>
                </div>

                <div class="grid md:grid-cols-2 gap-8">
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 px-1">ผู้ติดต่อหลัก</label>
                        <input type="text" name="contact_name" value="{{ old('contact_name', $employer?->contact_name) }}"
                            placeholder="ชื่อ-นามสกุล of Contact Person"
                            class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 px-1">เบอร์โทรศัพท์</label>
                        <input type="tel" name="phone" value="{{ old('phone', $employer?->phone) }}"
                            placeholder="เช่น 08X-XXX-XXXX"
                            class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 px-1">อีเมล</label>
                        <input type="email" name="email" value="{{ old('email', $employer?->email) }}"
                            placeholder="official.com"
                            class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all">
                    </div>
                </div>

                <div class="mt-8 space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 px-1">การจัดการ หมายเหตุ</label>
                    <textarea name="notes" rows="3" placeholder="หมายเหตุภายในหรือเงื่อนไขพิเศษของลูกค้า..."
                        class="w-full rounded-lg border border-slate-100 bg-slate-50/50 p-4 text-sm font-medium outline-none focus:border-[#c9a227] focus:bg-white transition-all">{{ old('notes', $employer?->notes) }}</textarea>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-50">
                    <label class="group flex items-center gap-4 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <div class="relative">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $employer?->is_active ?? true))
                                class="peer sr-only">
                            <div class="h-7 w-12 rounded-full bg-slate-200 transition-colors peer-checked:bg-emerald-500"></div>
                            <div class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white transition-transform peer-checked:translate-x-5 shadow-sm"></div>
                        </div>
                        <div class="min-w-0">
                            <span class="block text-sm font-black uppercase tracking-widest text-slate-700 group-hover:text-emerald-600 transition-colors">Activate นายจ้าง Account</span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">อนุญาตให้ใช้งานลูกค้ารายนี้ในระบบ</span>
                        </div>
                    </label>
                </div>
            </section>

            <div class="flex items-center justify-end gap-4 pb-12">
                <a href="{{ route('manager.employers.index') }}"
                    class="h-12 px-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-xs font-black uppercase tracking-[0.2em] text-slate-500 hover:bg-slate-50 transition-all">
                    ยกเลิก
                </a>
                <button type="submit"
                    class="h-12 px-10 flex items-center justify-center gap-3 rounded-lg bg-[#0b2f52] text-xs font-black uppercase tracking-[0.2em] text-white shadow-xl shadow-slate-900/20 hover:bg-[#123e68] transition-all focus:ring-4 focus:ring-[#c9a227]/20">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    {{ $employer ? 'บันทึกการแก้ไข' : 'บันทึกข้อมูล' }}
                </button>
            </div>
        </form>
    </div>
@endsection
