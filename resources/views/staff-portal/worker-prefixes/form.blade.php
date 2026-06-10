@extends('layouts.staff-portal', ['title' => $workerPrefix ? 'แก้ไขคำนำหน้าชื่อ' : 'เพิ่มคำนำหน้าชื่อ', 'pageTitle' => 'คำนำหน้าชื่อ'])

@section('content')
    <div class="space-y-8 max-w-4xl mx-auto">
        <header class="overflow-hidden rounded-lg bg-[#0b2f52] text-white shadow-xl shadow-[#0b2f52]/15">
            <div class="flex flex-col gap-4 px-5 py-6 sm:px-7 lg:flex-row lg:items-end lg:justify-between lg:px-8">
                <div>
                    <div class="mb-4 h-1 w-24 rounded-full bg-gradient-to-r from-[#b91c1c] to-transparent"></div>
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-[#fecaca]">Worker Prefix</p>
                    <h2 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">
                        {{ $workerPrefix ? 'แก้ไขคำนำหน้าชื่อ' : 'เพิ่มคำนำหน้าชื่อ' }}
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                        ตั้งค่าคำนำหน้าชื่อกลางสำหรับแรงงาน เพื่อใช้ทั่วทั้งระบบได้อย่างเป็นมาตรฐาน
                    </p>
                </div>

                <a href="{{ route('staff.portal.worker-prefixes.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-white/10 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/20">
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

        <form method="POST" action="{{ $workerPrefix ? route('staff.portal.worker-prefixes.update', $workerPrefix) : route('staff.portal.worker-prefixes.store') }}" class="manager-card overflow-hidden">
            @csrf
            @if ($workerPrefix)
                @method('PUT')
            @endif

            <div class="grid gap-6 p-6 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">
                        รหัสคำนำหน้า
                    </label>
                    <input type="text" name="code" value="{{ old('code', $workerPrefix?->code) }}" required
                        class="portal-input h-12 w-full px-4 text-sm font-mono font-bold uppercase"
                        placeholder="mr / mrs / ms">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">
                        ลำดับ
                    </label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $workerPrefix?->sort_order ?? 0) }}" min="0"
                        class="portal-input h-12 w-full px-4 text-sm font-medium">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">
                        คำนำหน้าไทย
                    </label>
                    <input type="text" name="name_th" value="{{ old('name_th', $workerPrefix?->name_th) }}" required
                        class="portal-input h-12 w-full px-4 text-sm font-medium"
                        placeholder="เช่น นาย">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">
                        คำนำหน้าอังกฤษ
                    </label>
                    <input type="text" name="name_en" value="{{ old('name_en', $workerPrefix?->name_en) }}" required
                        class="portal-input h-12 w-full px-4 text-sm font-medium"
                        placeholder="Mr.">
                </div>
                <div class="md:col-span-2">
                    <label class="flex items-center gap-3 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $workerPrefix?->is_active ?? true))
                            class="h-4 w-4 rounded border-slate-300 text-[#0b2f52] focus:ring-[#b91c1c]/20">
                        <span>
                            <span class="block text-sm font-semibold text-slate-800">เปิดใช้งาน</span>
                            <span class="block text-xs text-slate-500">คำนำหน้าชื่อที่เปิดใช้งานจะถูกแสดงในฟอร์มแรงงาน</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-4">
                <a href="{{ route('staff.portal.worker-prefixes.index') }}"
                    class="portal-btn-secondary inline-flex items-center justify-center px-5 py-3 text-sm font-bold">
                    ยกเลิก
                </a>
                <button type="submit"
                    class="portal-btn-primary inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-bold">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    {{ $workerPrefix ? 'บันทึกการแก้ไข' : 'บันทึกคำนำหน้าชื่อ' }}
                </button>
            </div>
        </form>
    </div>
@endsection
