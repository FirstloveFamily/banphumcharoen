@extends('layouts.manager', ['title' => $documentSetting ? 'แก้ไขตั้งค่าเอกสาร' : 'เพิ่มตั้งค่าเอกสาร', 'pageTitle' => $documentSetting ? 'แก้ไขตั้งค่าเอกสาร' : 'เพิ่มตั้งค่าเอกสาร'])

@push('head')
    <style>
        .hero-gradient {
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 45%, #0f766e 100%);
        }
    </style>
@endpush

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="hero-gradient rounded-lg p-8 text-white mb-8 shadow-2xl">
            <h1 class="text-3xl font-bold tracking-tight">
                {{ $documentSetting ? 'แก้ไขการตั้งค่าเอกสาร' : 'เพิ่มการตั้งค่าเอกสาร' }}
            </h1>
            <p class="mt-2 text-white/90">
                {{ $documentSetting ? 'อัปเดตชื่อ รหัส คำอธิบาย และสถานะของประเภทเอกสาร' : 'กำหนดประเภทเอกสารใหม่สำหรับใช้งานในระบบ' }}
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-8">
            <form
                action="{{ $documentSetting ? route('manager.document-settings.update', $documentSetting) : route('manager.document-settings.store') }}"
                method="POST">
                @csrf
                @if ($documentSetting)
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">ชื่อเอกสาร <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $documentSetting?->name) }}"
                            class="w-full px-4 py-2 border @error('name') border-rose-500 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-[#c9a227]/30"
                            placeholder="เช่น หนังสือเดินทาง" required>
                        @error('name')
                            <p class="text-rose-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">รหัสเอกสาร <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $documentSetting?->code) }}"
                            class="w-full px-4 py-2 border @error('code') border-rose-500 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-[#c9a227]/30"
                            placeholder="เช่น PASSPORT" required>
                        @error('code')
                            <p class="text-rose-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">คำอธิบาย</label>
                        <textarea name="description" rows="4"
                            class="w-full px-4 py-2 border @error('description') border-rose-500 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-[#c9a227]/30"
                            placeholder="รายละเอียดหรือเงื่อนไขของเอกสารนี้">{{ old('description', $documentSetting?->description) }}</textarea>
                        @error('description')
                            <p class="text-rose-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="inline-flex items-center gap-3 rounded-lg border border-slate-200 px-4 py-3">
                            <input type="checkbox" name="is_active" value="1"
                                class="h-4 w-4 rounded border-slate-300 text-[#0b2f52] focus:ring-[#c9a227]/30"
                                @checked(old('is_active', $documentSetting?->is_active ?? true))>
                            <span>
                                <span class="block text-sm font-semibold text-slate-800">เปิดใช้งาน</span>
                                <span class="block text-xs text-slate-500">ประเภทเอกสารนี้จะแสดงให้เลือกในส่วนอื่นของระบบ</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="flex gap-3 justify-end">
                    <a href="{{ route('manager.document-settings.index') }}"
                        class="px-6 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition font-medium">
                        ยกเลิก
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-[#0b2f52] text-white rounded-lg hover:bg-[#123e68] transition font-medium">
                        {{ $documentSetting ? 'บันทึกการแก้ไข' : 'เพิ่มการตั้งค่า' }}
                    </button>
                </div>
            </form>
        </div>

        @if ($documentSetting)
            <div class="mt-8 bg-white rounded-lg shadow-sm border border-slate-200 p-8">
                <div class="flex รายการ-start justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-[#0b2f52]">บริการที่ใช้เอกสารนี้</h2>
                        <p class="mt-1 text-sm text-slate-500">ข้อมูลส่วนนี้ดึงและบันทึกจากตาราง services และ service_checklists</p>
                    </div>
                    <span class="rounded-full bg-[#fff9e8] px-3 py-1 text-sm font-semibold text-[#0b2f52]">
                        {{ $documentSetting->serviceChecklists->count() }} รายการ
                    </span>
                </div>

                @if (session('success'))
                    <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('manager.document-settings.service-checklists.store', $documentSetting) }}"
                    method="POST" class="mb-8 rounded-lg border border-slate-200 bg-slate-50 p-5">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 รายการ-start">
                        <div class="md:col-span-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">บริการ <span class="text-rose-500">*</span></label>
                            <select name="service_id"
                                class="w-full px-4 py-2 border @error('service_id') border-rose-500 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-[#c9a227]/30"
                                required>
                                <option value="">-- เลือกบริการ --</option>
                                @foreach ($services as $id => $name)
                                    <option value="{{ $id }}" @selected(old('service_id') == $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">ลำดับ</label>
                            <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                                class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#c9a227]/30">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">จำเป็น</label>
                            <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2">
                                <input type="checkbox" name="is_required" value="1"
                                    class="h-4 w-4 rounded border-slate-300 text-[#0b2f52] focus:ring-[#c9a227]/30" checked>
                                <span class="text-sm text-slate-700">ใช่</span>
                            </label>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-[#c9a227] px-5 py-2 text-sm font-semibold text-[#0b2f52] hover:bg-[#f3d06f]">
                            <i data-lucide="plus" class="h-4 w-4"></i> เพิ่มความเกี่ยวข้อง
                        </button>
                    </div>
                </form>

                <div class="space-y-4">
                    @forelse ($documentSetting->serviceChecklists as $checklist)
                        <div class="rounded-lg border border-slate-200 p-5">
                            <form id="service-checklist-update-{{ $checklist->id }}"
                                action="{{ route('manager.document-settings.service-checklists.update', [$documentSetting, $checklist]) }}"
                                method="POST">
                                @csrf
                                @method('PUT')
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 รายการ-start">
                                    <div class="md:col-span-6">
                                        <label class="block text-xs font-semibold text-slate-500 mb-1">บริการ</label>
                                        <select name="service_id"
                                            class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#c9a227]/30"
                                            required>
                                            @foreach ($services as $id => $name)
                                                <option value="{{ $id }}" @selected($checklist->service_id == $id)>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="md:col-span-3">
                                        <label class="block text-xs font-semibold text-slate-500 mb-1">ลำดับ</label>
                                        <input type="number" name="sort_order" value="{{ $checklist->sort_order }}" min="0"
                                            class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#c9a227]/30">
                                    </div>
                                    <div class="md:col-span-3">
                                        <label class="block text-xs font-semibold text-slate-500 mb-1">จำเป็น</label>
                                        <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2">
                                            <input type="checkbox" name="is_required" value="1"
                                                class="h-4 w-4 rounded border-slate-300 text-[#0b2f52] focus:ring-[#c9a227]/30"
                                                @checked($checklist->is_required)>
                                            <span class="text-sm text-slate-700">ใช่</span>
                                        </label>
                                    </div>
                                </div>
                            </form>

                            <div class="mt-4 flex justify-end gap-2">
                                <button type="submit" form="service-checklist-update-{{ $checklist->id }}"
                                    class="inline-flex items-center gap-1 rounded-lg bg-[#fff9e8] px-4 py-2 text-sm font-semibold text-[#0b2f52] hover:bg-[#f3d06f]">
                                    <i data-lucide="save" class="h-4 w-4"></i> บันทึก
                                </button>
                                <form action="{{ route('manager.document-settings.service-checklists.destroy', [$documentSetting, $checklist]) }}"
                                    method="POST" onsubmit="return confirm('คุณแน่ใจหรือ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center gap-1 rounded-lg bg-rose-100 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-200">
                                        <i data-lucide="trash-2" class="h-4 w-4"></i> ลบ
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-slate-300 p-8 text-center">
                            <i data-lucide="clipboard-list" class="mx-auto h-10 w-10 text-slate-400"></i>
                            <p class="mt-3 font-semibold text-slate-700">ยังไม่มีบริการที่ใช้เอกสารนี้</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>

    <script>
        lucide.createIcons();
    </script>
@endsection
