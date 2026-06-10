@extends('layouts.staff-portal', ['title' => 'ประเภทเอกสาร', 'pageTitle' => 'ประเภทเอกสาร'])

@section('content')
    <div class="space-y-8">
        <header class="overflow-hidden rounded-lg bg-[#0b2f52] text-white shadow-xl shadow-[#0b2f52]/15">
            <div class="flex flex-col gap-6 px-5 py-6 sm:px-7 lg:flex-row lg:items-end lg:justify-between lg:px-8">
                <div class="max-w-3xl">
                    <div class="mb-4 h-1 w-24 rounded-full bg-gradient-to-r from-[#b91c1c] to-transparent"></div>
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-[#fecaca]">Document Master</p>
                    <h2 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">
                        จัดการประเภทเอกสาร
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                        เพิ่ม แก้ไข และควบคุมประเภทเอกสารหลักของระบบให้ใช้งานได้อย่างเป็นระเบียบ
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('staff.portal.document-masters.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-[#0b2f52] via-[#123e68] to-[#b91c1c] px-4 py-3 text-sm font-extrabold text-white shadow-lg shadow-black/20 transition hover:opacity-95">
                        <i data-lucide="plus" class="h-4 w-4"></i>
                        เพิ่มประเภทเอกสาร
                    </a>
                </div>
            </div>
        </header>

        @if (session('success'))
            <div class="manager-card flex items-center gap-3 border-l-4 border-emerald-500 px-4 py-3 text-sm font-bold text-emerald-700">
                <i data-lucide="check-circle-2" class="h-5 w-5"></i>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="manager-card flex items-center gap-3 border-l-4 border-rose-500 px-4 py-3 text-sm font-bold text-rose-700">
                <i data-lucide="alert-triangle" class="h-5 w-5"></i>
                {{ session('error') }}
            </div>
        @endif

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="manager-card manager-card-hover p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">ประเภทเอกสารทั้งหมด</p>
                <h3 class="mt-3 text-3xl font-extrabold text-[#0b2f52]">{{ number_format($summary['total']) }}</h3>
            </article>
            <article class="manager-card manager-card-hover p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">ใช้งานอยู่</p>
                <h3 class="mt-3 text-3xl font-extrabold text-[#0b2f52]">{{ number_format($summary['active']) }}</h3>
            </article>
            <article class="manager-card manager-card-hover p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">ไม่ใช้งาน</p>
                <h3 class="mt-3 text-3xl font-extrabold text-[#0b2f52]">{{ number_format($summary['inactive']) }}</h3>
            </article>
            <article class="manager-card manager-card-hover p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">ความเกี่ยวข้องกับบริการ</p>
                <h3 class="mt-3 text-3xl font-extrabold text-[#0b2f52]">{{ number_format($summary['service_links']) }}</h3>
            </article>
        </section>

        <section class="manager-card overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h3 class="text-lg font-extrabold text-[#0b2f52]">รายการประเภทเอกสาร</h3>
                    <p class="mt-1 text-sm text-slate-500">คลิกแก้ไขเพื่ออัปเดตชื่อ รหัส คำอธิบาย และสถานะ</p>
                </div>
                <a href="{{ route('staff.portal.services.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-bold text-[#0b2f52] shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-50">
                    <i data-lucide="briefcase-business" class="h-4 w-4"></i>
                    ไปหน้าบริการ
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="portal-table-head text-xs font-bold uppercase tracking-wider">
                            <th class="px-6 py-4">ชื่อประเภทเอกสาร</th>
                            <th class="px-4 py-4">รหัส</th>
                            <th class="px-4 py-4 text-center">ใช้กับบริการ</th>
                            <th class="px-4 py-4 text-center">ใช้กับใบงาน</th>
                            <th class="px-4 py-4 text-center">ใช้กับเอกสารคนงาน</th>
                            <th class="px-4 py-4 text-center">สถานะ</th>
                            <th class="px-6 py-4 text-right">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($documentMasters as $documentMaster)
                            <tr class="portal-row-hover transition-colors">
                                <td class="px-6 py-4">
                                    <div class="min-w-0">
                                        <p class="font-bold text-[#0b2f52]">{{ $documentMaster->name }}</p>
                                        <p class="mt-1 line-clamp-2 text-xs text-slate-500">{{ $documentMaster->description ?: '-' }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-4 font-mono text-xs font-bold text-slate-600">{{ $documentMaster->code }}</td>
                                <td class="px-4 py-4 text-center font-bold text-slate-700">{{ number_format($documentMaster->service_checklists_count) }}</td>
                                <td class="px-4 py-4 text-center font-bold text-slate-700">{{ number_format($documentMaster->job_order_checklists_count) }}</td>
                                <td class="px-4 py-4 text-center font-bold text-slate-700">{{ number_format($documentMaster->worker_documents_count) }}</td>
                                <td class="px-4 py-4 text-center">
                                    <span @class([
                                        'inline-flex rounded-full px-3 py-1 text-[11px] font-bold ring-1 ring-inset',
                                        'bg-emerald-50 text-emerald-700 ring-emerald-600/20' => $documentMaster->is_active,
                                        'bg-slate-100 text-slate-500 ring-slate-400/20' => ! $documentMaster->is_active,
                                    ])>
                                        {{ $documentMaster->is_active ? 'ใช้งานอยู่' : 'ไม่ใช้งาน' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('staff.portal.document-masters.edit', $documentMaster) }}"
                                            class="grid h-9 w-9 place-items-center rounded-lg bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-[#b91c1c] transition-all"
                                            title="แก้ไข">
                                            <i data-lucide="square-pen" class="h-4 w-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-14 text-center text-slate-400">
                                    ไม่พบข้อมูลประเภทเอกสาร
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
