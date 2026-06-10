@extends('layouts.staff-portal', ['title' => $newsPost->exists ? 'แก้ไขข่าวสารกิจกรรม' : 'เพิ่มข่าวสารกิจกรรม', 'pageTitle' => 'จัดการข่าวสารกิจกรรม'])

@section('content')
    @php
        $statusValue = old('status', $newsPost->status ?: 'draft');
    @endphp

    <div class="space-y-8 max-w-5xl mx-auto">
        <header class="overflow-hidden rounded-lg bg-[#0b2f52] text-white shadow-xl shadow-[#0b2f52]/15">
            <div class="flex flex-col gap-4 px-5 py-6 sm:px-7 lg:flex-row lg:items-end lg:justify-between lg:px-8">
                <div>
                    <div class="mb-4 h-1 w-24 rounded-full bg-gradient-to-r from-[#b91c1c] to-transparent"></div>
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-[#fecaca]">News & Activities</p>
                    <h2 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">
                        {{ $newsPost->exists ? 'แก้ไขข่าวสารกิจกรรม' : 'เพิ่มข่าวสารกิจกรรม' }}
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                        จัดการข้อมูลข่าวสารที่จะไปแสดงบนหน้าเว็บไซต์หลัก
                    </p>
                </div>

                <a href="{{ route('staff.portal.news.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-white/10 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/20">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    กลับหน้ารายการข่าว
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
            action="{{ $newsPost->exists ? route('staff.portal.news.update', $newsPost) : route('staff.portal.news.store') }}"
            enctype="multipart/form-data"
            class="manager-card overflow-hidden">
            @csrf
            @if ($newsPost->exists)
                @method('PUT')
            @endif

            <div class="grid gap-6 p-6 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">หัวข้อข่าวสาร</label>
                    <input type="text" name="title" value="{{ old('title', $newsPost->title) }}" required
                        class="portal-input h-12 w-full px-4 text-sm font-medium"
                        placeholder="เช่น แจ้งวันหยุดประจำปี">
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">Slug / URL SEO</label>
                    <input type="text" name="slug" value="{{ old('slug', $newsPost->slug) }}"
                        class="portal-input h-12 w-full px-4 text-sm font-mono font-bold"
                        placeholder="news-slug">
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">หมวดหมู่</label>
                    <select name="category_id" required class="portal-select h-12 w-full px-4 text-sm font-medium">
                        <option value="">-- เลือกหมวดหมู่ --</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $newsPost->category_id) == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">สถานะ</label>
                    <select name="status" class="portal-select h-12 w-full px-4 text-sm font-medium">
                        <option value="draft" @selected($statusValue === 'draft')>ฉบับร่าง</option>
                        <option value="published" @selected($statusValue === 'published')>เผยแพร่</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">วันเวลาที่เผยแพร่</label>
                    <input type="datetime-local" name="published_at"
                        value="{{ old('published_at', optional($newsPost->published_at)->format('Y-m-d\TH:i')) }}"
                        class="portal-input h-12 w-full px-4 text-sm font-medium">
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">รูปหน้าปก</label>
                    <input type="file" name="image_cover" accept="image/*"
                        class="portal-input h-12 w-full px-4 py-2 text-sm font-medium">
                    <p class="mt-2 text-xs text-amber-600">ขนาดรูปไม่เกิน 3 MB</p>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">เนื้อหาย่อ</label>
                    <textarea name="excerpt" rows="4" class="portal-textarea w-full px-4 py-3 text-sm font-medium"
                        placeholder="สรุปเนื้อหาสั้น ๆ">{{ old('excerpt', $newsPost->excerpt) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">เนื้อหาเต็ม</label>
                    <textarea name="content" rows="14" required class="portal-textarea w-full px-4 py-3 text-sm font-medium"
                        placeholder="สามารถใส่ HTML ได้">{{ old('content', $newsPost->content) }}</textarea>
                    <p class="mt-2 text-xs text-slate-400">หน้าแสดงผลจะเรนเดอร์เนื้อหาแบบ HTML ตามที่บันทึกไว้</p>
                </div>

                <div class="md:col-span-2">
                    <label class="flex items-center gap-3 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                        <input type="hidden" name="is_pinned" value="0">
                        <input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned', $newsPost->is_pinned))
                            class="h-4 w-4 rounded border-slate-300 text-[#0b2f52] focus:ring-[#b91c1c]/20">
                        <span>
                            <span class="block text-sm font-semibold text-slate-800">ปักหมุดข่าวนี้</span>
                            <span class="block text-xs text-slate-500">ข่าวที่ปักหมุดจะถูกดันขึ้นบนหน้าเว็บไซต์</span>
                        </span>
                    </label>
                </div>

                @if ($newsPost->image_cover)
                    <div class="md:col-span-2">
                        <p class="mb-2 text-xs font-bold uppercase tracking-widest text-slate-500">รูปปัจจุบัน</p>
                        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                            <img src="{{ asset('storage/' . $newsPost->image_cover) }}" alt="{{ $newsPost->title }}"
                                class="h-56 w-full object-cover">
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-4">
                <a href="{{ route('staff.portal.news.index') }}"
                    class="portal-btn-secondary inline-flex items-center justify-center px-5 py-3 text-sm font-bold">
                    ยกเลิก
                </a>
                <button type="submit"
                    class="portal-btn-primary inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-bold">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    {{ $newsPost->exists ? 'บันทึกการแก้ไข' : 'บันทึกข่าวสาร' }}
                </button>
            </div>
        </form>
    </div>
@endsection
