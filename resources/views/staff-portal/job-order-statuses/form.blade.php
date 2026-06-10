@extends('layouts.staff-portal', ['title' => $jobOrderStatus ? 'แก้ไขสถานะใบงาน' : 'เพิ่มสถานะใบงาน', 'pageTitle' => 'สถานะใบงาน'])

@section('content')
    <div class="space-y-8 max-w-4xl mx-auto">
        <header class="overflow-hidden rounded-lg bg-[#0b2f52] text-white shadow-xl shadow-[#0b2f52]/15">
            <div class="flex flex-col gap-4 px-5 py-6 sm:px-7 lg:flex-row lg:items-end lg:justify-between lg:px-8">
                <div>
                    <div class="mb-4 h-1 w-24 rounded-full bg-gradient-to-r from-[#b91c1c] to-transparent"></div>
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-[#fecaca]">Job Order Status</p>
                    <h2 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">
                        {{ $jobOrderStatus ? 'แก้ไขสถานะใบงาน' : 'เพิ่มสถานะใบงาน' }}
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                        กำหนดชื่อ รหัส สี และพฤติกรรมของสถานะใบงานให้ใช้งานร่วมกันทั้งระบบ
                    </p>
                </div>

                <a href="{{ route('staff.portal.job-order-statuses.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-white/10 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/20">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    กลับรายการ
                </a>
            </div>
        </header>

        @if ($errors->any())
            <div class="manager-card border-l-4 border-rose-500 p-5 text-sm text-rose-700">
                <p class="font-bold">พบข้อผิดพลาดในการบันทึก</p>
                <ul class="mt-3 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $jobOrderStatus ? route('staff.portal.job-order-statuses.update', $jobOrderStatus) : route('staff.portal.job-order-statuses.store') }}" class="manager-card overflow-hidden">
            @csrf
            @if ($jobOrderStatus)
                @method('PUT')
            @endif

            <div class="grid gap-6 p-6 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">รหัสสถานะ</label>
                    <input type="text" name="code" value="{{ old('code', $jobOrderStatus?->code) }}" required
                        class="portal-input h-12 w-full px-4 text-sm font-mono font-bold uppercase"
                        placeholder="pending / processing">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">ลำดับ</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $jobOrderStatus?->sort_order ?? 0) }}" min="0"
                        class="portal-input h-12 w-full px-4 text-sm font-medium">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">ชื่อสถานะ (ไทย)</label>
                    <input type="text" name="name_th" value="{{ old('name_th', $jobOrderStatus?->name_th) }}" required
                        class="portal-input h-12 w-full px-4 text-sm font-medium"
                        placeholder="เช่น รอเริ่มงาน">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">ชื่อสถานะ (EN)</label>
                    <input type="text" name="name_en" value="{{ old('name_en', $jobOrderStatus?->name_en) }}"
                        class="portal-input h-12 w-full px-4 text-sm font-medium"
                        placeholder="Pending">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">Badge Class</label>
                    <input type="text" name="badge_class" value="{{ old('badge_class', $jobOrderStatus?->badge_class) }}"
                        class="portal-input h-12 w-full px-4 text-sm font-mono"
                        placeholder="bg-blue-50 text-blue-700 ring-blue-600/20">
                </div>
                <div class="md:col-span-2 grid gap-4 md:grid-cols-3">
                    <label class="flex items-center gap-3 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $jobOrderStatus?->is_active ?? true))
                            class="h-4 w-4 rounded border-slate-300 text-[#0b2f52] focus:ring-[#b91c1c]/20">
                        <span>
                            <span class="block text-sm font-semibold text-slate-800">เปิดใช้งาน</span>
                            <span class="block text-xs text-slate-500">ใช้ได้ในฟอร์มและหน้าต่าง ๆ</span>
                        </span>
                    </label>
                    <label class="flex items-center gap-3 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                        <input type="hidden" name="is_default" value="0">
                        <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $jobOrderStatus?->is_default ?? false))
                            class="h-4 w-4 rounded border-slate-300 text-[#0b2f52] focus:ring-[#b91c1c]/20">
                        <span>
                            <span class="block text-sm font-semibold text-slate-800">สถานะเริ่มต้น</span>
                            <span class="block text-xs text-slate-500">ใช้ตอนสร้างใบงานใหม่</span>
                        </span>
                    </label>
                    <label class="flex items-center gap-3 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                        <input type="hidden" name="requires_note" value="0">
                        <input type="checkbox" name="requires_note" value="1" @checked(old('requires_note', $jobOrderStatus?->requires_note ?? false))
                            class="h-4 w-4 rounded border-slate-300 text-[#0b2f52] focus:ring-[#b91c1c]/20">
                        <span>
                            <span class="block text-sm font-semibold text-slate-800">ต้องมีหมายเหตุ</span>
                            <span class="block text-xs text-slate-500">ตอนเปลี่ยนสถานะจะบังคับกรอก</span>
                        </span>
                    </label>
                    <label class="flex items-center gap-3 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3 md:col-span-3">
                        <input type="hidden" name="sets_completed_at" value="0">
                        <input type="checkbox" name="sets_completed_at" value="1" @checked(old('sets_completed_at', $jobOrderStatus?->sets_completed_at ?? false))
                            class="h-4 w-4 rounded border-slate-300 text-[#0b2f52] focus:ring-[#b91c1c]/20">
                        <span>
                            <span class="block text-sm font-semibold text-slate-800">ตั้งค่าวันเสร็จสิ้นอัตโนมัติ</span>
                            <span class="block text-xs text-slate-500">ถ้าเปิดไว้ ระบบจะใส่ `completed_at` เมื่อเลือกสถานะนี้</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-4">
                <a href="{{ route('staff.portal.job-order-statuses.index') }}"
                    class="portal-btn-secondary inline-flex items-center justify-center px-5 py-3 text-sm font-bold">
                    ยกเลิก
                </a>
                <button type="submit"
                    class="portal-btn-primary inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-bold">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    {{ $jobOrderStatus ? 'บันทึกการแก้ไข' : 'บันทึกสถานะใบงาน' }}
                </button>
            </div>
        </form>
    </div>
@endsection
