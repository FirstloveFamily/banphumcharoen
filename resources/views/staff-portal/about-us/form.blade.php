@extends('layouts.staff-portal', ['title' => $aboutUsBlock->exists ? 'แก้ไข About us' : 'เพิ่ม About us', 'pageTitle' => 'จัดการ About us'])

@section('content')
    <div class="space-y-8 max-w-4xl mx-auto">
        <header class="overflow-hidden rounded-lg bg-[#0b2f52] text-white shadow-xl shadow-[#0b2f52]/15">
            <div class="flex flex-col gap-4 px-5 py-6 sm:px-7 lg:flex-row lg:items-end lg:justify-between lg:px-8">
                <div>
                    <div class="mb-4 h-1 w-24 rounded-full bg-gradient-to-r from-[#b91c1c] to-transparent"></div>
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-[#fecaca]">About Us Content</p>
                    <h2 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">
                        {{ $aboutUsBlock->exists ? 'แก้ไข About us' : 'เพิ่ม About us' }}
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                        เพิ่มบล็อกข้อมูลที่จะนำไปแสดงบนหน้าเกี่ยวกับเรา
                    </p>
                </div>

                <a href="{{ route('staff.portal.about-us.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-white/10 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/20">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    กลับหน้ารายการ
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

        <form method="POST"
            action="{{ $aboutUsBlock->exists ? route('staff.portal.about-us.update', $aboutUsBlock) : route('staff.portal.about-us.store') }}"
            class="manager-card overflow-hidden">
            @csrf
            @if ($aboutUsBlock->exists)
                @method('PUT')
            @endif

            <div class="grid gap-6 p-6 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">หมวด</label>
                    <select name="section" class="portal-select h-12 w-full px-4 text-sm font-medium">
                        <option value="feature" @selected(old('section', $aboutUsBlock->section) === 'feature')>จุดเด่น</option>
                        <option value="value" @selected(old('section', $aboutUsBlock->section) === 'value')>ค่านิยม</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">ลำดับการแสดงผล</label>
                    <input type="number" name="sort_order" min="0" step="1" value="{{ old('sort_order', $aboutUsBlock->sort_order ?? 0) }}"
                        class="portal-input h-12 w-full px-4 text-sm font-medium">
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">หัวข้อ</label>
                    <input type="text" name="title" value="{{ old('title', $aboutUsBlock->title) }}" required
                        class="portal-input h-12 w-full px-4 text-sm font-medium"
                        placeholder="เช่น ทีมงานมืออาชีพ">
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">Icon</label>
                    <input type="text" name="icon" value="{{ old('icon', $aboutUsBlock->icon) }}"
                        class="portal-input h-12 w-full px-4 text-sm font-mono font-bold"
                        placeholder="users, shield-check, badge-check">
                    <p class="mt-2 text-xs text-slate-400">ใช้ชื่อไอคอนของ Lucide</p>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">รายละเอียด</label>
                    <textarea name="description" rows="6" class="portal-textarea w-full px-4 py-3 text-sm font-medium"
                        placeholder="คำอธิบายของบล็อกนี้">{{ old('description', $aboutUsBlock->description) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="flex items-center gap-3 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $aboutUsBlock->is_active ?? true))
                            class="h-4 w-4 rounded border-slate-300 text-[#0b2f52] focus:ring-[#b91c1c]/20">
                        <span>
                            <span class="block text-sm font-semibold text-slate-800">แสดงผลบนหน้า About us</span>
                            <span class="block text-xs text-slate-500">หากปิด ระบบจะไม่แสดงบล็อกนี้บนหน้าเว็บไซต์</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-4">
                <a href="{{ route('staff.portal.about-us.index') }}"
                    class="portal-btn-secondary inline-flex items-center justify-center px-5 py-3 text-sm font-bold">
                    ยกเลิก
                </a>
                <button type="submit"
                    class="portal-btn-primary inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-bold">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    {{ $aboutUsBlock->exists ? 'บันทึกการแก้ไข' : 'บันทึกข้อมูล' }}
                </button>
            </div>
        </form>
    </div>
@endsection
