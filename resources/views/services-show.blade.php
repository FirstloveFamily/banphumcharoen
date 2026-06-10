@php($title = $service->name)
@extends('layouts.app')

@section('content')
    <section class="bg-gradient-to-br from-blue-950 via-blue-900 to-blue-700 text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <a href="{{ route('services.index') }}"
                class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm text-blue-100 transition hover:bg-white/15">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                กลับหน้าบริการ
            </a>

            <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-[1fr_280px] lg:items-end">
                <div>
                    <div class="inline-flex rounded-full bg-blue-500/20 px-3 py-1 text-xs font-semibold text-blue-100">
                        รหัสบริการ: {{ $service->code }}
                    </div>
                    <h1 class="mt-4 text-3xl font-bold leading-tight sm:text-4xl">{{ $service->name }}</h1>
                    <p class="mt-4 max-w-3xl text-base leading-7 text-blue-100">
                        {{ $service->description ?: 'บริการดูแลเอกสารแรงงานต่างด้าวแบบครบวงจร โดยทีมงานผู้เชี่ยวชาญพร้อมติดตามงานทุกขั้นตอน' }}
                    </p>
                </div>

                <div class="rounded-2xl border border-white/15 bg-white/10 p-5 backdrop-blur">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/15">
                            <i data-lucide="file-check-2" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ $service->checklists->count() }}</p>
                            <p class="text-sm text-blue-100">เอกสารที่เกี่ยวข้อง</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-slate-50 py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_1.2fr]">
                <article class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                            <i data-lucide="info" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-950">รายละเอียดบริการ</h2>
                            <p class="text-sm text-slate-500">ข้อมูลสำคัญของบริการนี้</p>
                        </div>
                    </div>

                    <div class="mt-6 space-y-5">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">ชื่อบริการ</p>
                            <p class="mt-2 text-lg font-bold text-slate-950">{{ $service->name }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">คำอธิบาย</p>
                            <p class="mt-2 leading-7 text-slate-700">
                                {{ $service->description ?: 'ยังไม่มีรายละเอียดเพิ่มเติมสำหรับบริการนี้' }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-xs font-semibold text-slate-400">รหัสบริการ</p>
                                <p class="mt-2 font-bold text-blue-900">{{ $service->code }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-xs font-semibold text-slate-400">แจ้งเตือนก่อนหมดอายุ</p>
                                <p class="mt-2 font-bold text-blue-900">{{ $service->alert_days_before_expiry }} วัน</p>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                                <i data-lucide="clipboard-list" class="h-5 w-5"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-slate-950">เอกสารที่เกี่ยวข้อง</h2>
                                <p class="text-sm text-slate-500">รายการเอกสารที่ใช้ประกอบบริการ</p>
                            </div>
                        </div>
                        <span class="w-fit rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                            {{ $service->checklists->count() }} รายการ
                        </span>
                    </div>

                    <div class="mt-6 space-y-3">
                        @forelse ($service->checklists as $checklist)
                            <div class="flex items-start gap-4 rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-700 text-white">
                                    <span class="text-sm font-bold">{{ $loop->iteration }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <p class="font-bold text-slate-950">{{ $checklist->document_name }}</p>
                                        <span
                                            class="w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $checklist->is_required ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-600' }}">
                                            {{ $checklist->is_required ? 'จำเป็น' : 'เพิ่มเติม' }}
                                        </span>
                                    </div>
                                    <p class="mt-2 text-sm leading-6 text-slate-500">
                                        ใช้สำหรับตรวจสอบความถูกต้องและประกอบการดำเนินงานของบริการ {{ $service->name }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                                <i data-lucide="file-question" class="mx-auto h-10 w-10 text-slate-400"></i>
                                <p class="mt-3 font-semibold text-slate-700">ยังไม่มีการกำหนดเอกสารที่เกี่ยวข้อง</p>
                                <p class="mt-1 text-sm text-slate-500">สามารถเพิ่มรายการได้จากหลังบ้านในเมนูบริการ</p>
                            </div>
                        @endforelse
                    </div>
                </article>
            </div>
        </div>
    </section>
@endsection
