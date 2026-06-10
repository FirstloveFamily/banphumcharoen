@extends('layouts.staff-portal', ['title' => 'แก้ไขประเภทเอกสาร', 'pageTitle' => 'จัดการประเภทเอกสาร'])

@section('content')
    <div class="space-y-8 max-w-4xl mx-auto">
        <header class="overflow-hidden rounded-lg bg-[#0b2f52] text-white shadow-xl shadow-[#0b2f52]/15">
            <div class="flex flex-col gap-4 px-5 py-6 sm:px-7 lg:flex-row lg:items-end lg:justify-between lg:px-8">
                <div>
                    <div class="mb-4 h-1 w-24 rounded-full bg-gradient-to-r from-[#b91c1c] to-transparent"></div>
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-[#fecaca]">Document Master</p>
                    <h2 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">
                        แก้ไขประเภทเอกสาร
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                        ปรับชื่อ รหัส คำอธิบาย และสถานะของประเภทเอกสารที่ใช้ในระบบ
                    </p>
                </div>

                <a href="{{ route('staff.portal.document-masters.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-white/10 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/20">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    กลับหน้าประเภทเอกสาร
                </a>
            </div>
        </header>

        <section class="manager-card grid gap-4 p-5 sm:grid-cols-3">
            <div class="rounded-2xl bg-slate-50 p-4">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">บริการที่ใช้</p>
                <p class="mt-2 text-2xl font-extrabold text-[#0b2f52]">{{ number_format($documentMaster->service_checklists_count) }}</p>
            </div>
            <div class="rounded-2xl bg-slate-50 p-4">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">ใบงานที่ใช้</p>
                <p class="mt-2 text-2xl font-extrabold text-[#0b2f52]">{{ number_format($documentMaster->job_order_checklists_count) }}</p>
            </div>
            <div class="rounded-2xl bg-slate-50 p-4">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">เอกสารคนงาน</p>
                <p class="mt-2 text-2xl font-extrabold text-[#0b2f52]">{{ number_format($documentMaster->worker_documents_count) }}</p>
            </div>
        </section>

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

        <form method="POST" action="{{ route('staff.portal.document-masters.update', $documentMaster) }}" class="manager-card overflow-hidden">
            @csrf
            @method('PUT')

            <div class="grid gap-6 p-6 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">
                        ชื่อประเภทเอกสาร
                    </label>
                    <input type="text" name="name" value="{{ old('name', $documentMaster->name) }}" required
                        class="portal-input h-12 w-full px-4 text-sm font-medium"
                        placeholder="เช่น หนังสือเดินทาง">
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">
                        รหัสเอกสาร
                    </label>
                    <input type="text" name="code" value="{{ old('code', $documentMaster->code) }}" required
                        class="portal-input h-12 w-full px-4 text-sm font-mono font-bold uppercase"
                        placeholder="PASSPORT">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">
                        คำอธิบาย
                    </label>
                    <textarea name="description" rows="5"
                        class="portal-textarea w-full px-4 py-3 text-sm font-medium"
                        placeholder="รายละเอียดเพิ่มเติมของประเภทเอกสาร">{{ old('description', $documentMaster->description) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="flex items-center gap-3 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $documentMaster->is_active))
                            class="h-4 w-4 rounded border-slate-300 text-[#0b2f52] focus:ring-[#b91c1c]/20">
                        <span>
                            <span class="block text-sm font-semibold text-slate-800">เปิดใช้งานประเภทเอกสาร</span>
                            <span class="block text-xs text-slate-500">เอกสารนี้จะแสดงให้เลือกในส่วนบริการและใบงาน</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-4">
                <a href="{{ route('staff.portal.document-masters.index') }}"
                    class="portal-btn-secondary inline-flex items-center justify-center px-5 py-3 text-sm font-bold">
                    ยกเลิก
                </a>
                <button type="submit"
                    class="portal-btn-primary inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-bold">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    บันทึกการแก้ไข
                </button>
            </div>
        </form>
    </div>
@endsection
