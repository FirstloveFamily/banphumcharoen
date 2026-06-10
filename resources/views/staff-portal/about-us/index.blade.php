@extends('layouts.staff-portal', ['title' => 'About us', 'pageTitle' => 'จัดการ About us'])

@section('content')
    @php
        $sectionLabels = [
            'feature' => 'จุดเด่น',
            'value' => 'ค่านิยม',
        ];
    @endphp

    <div class="space-y-8">
        <header class="overflow-hidden rounded-lg bg-[#0b2f52] text-white shadow-xl shadow-[#0b2f52]/15">
            <div class="flex flex-col gap-6 px-5 py-6 sm:px-7 lg:flex-row lg:items-end lg:justify-between lg:px-8">
                <div class="max-w-3xl">
                    <div class="mb-4 h-1 w-24 rounded-full bg-gradient-to-r from-[#b91c1c] to-transparent"></div>
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-[#fecaca]">About Us Content</p>
                    <h2 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">
                        จัดการ About us
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                        เพิ่ม แก้ไข และลบการ์ดเนื้อหาที่แสดงบนหน้าเกี่ยวกับเรา
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('staff.portal.about-us.create') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-[#0b2f52] via-[#123e68] to-[#b91c1c] px-4 py-3 text-sm font-extrabold text-white shadow-lg shadow-black/20 transition hover:opacity-95">
                        <i data-lucide="plus" class="h-4 w-4"></i>
                        เพิ่มบล็อก
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
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">บล็อกทั้งหมด</p>
                <h3 class="mt-3 text-3xl font-extrabold text-[#0b2f52]">{{ number_format($summary['total']) }}</h3>
            </article>
            <article class="manager-card manager-card-hover p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">จุดเด่น</p>
                <h3 class="mt-3 text-3xl font-extrabold text-emerald-600">{{ number_format($summary['feature']) }}</h3>
            </article>
            <article class="manager-card manager-card-hover p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">ค่านิยม</p>
                <h3 class="mt-3 text-3xl font-extrabold text-amber-600">{{ number_format($summary['value']) }}</h3>
            </article>
            <article class="manager-card manager-card-hover p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">ใช้งานอยู่</p>
                <h3 class="mt-3 text-3xl font-extrabold text-rose-600">{{ number_format($summary['active']) }}</h3>
            </article>
        </section>

        <section class="manager-card overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h3 class="text-lg font-extrabold text-[#0b2f52]">รายการบล็อก About us</h3>
                    <p class="mt-1 text-sm text-slate-500">จัดการเนื้อหาที่แสดงบนหน้าเกี่ยวกับเรา</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="portal-table-head text-xs font-bold uppercase tracking-wider">
                            <th class="px-6 py-4">หัวข้อ</th>
                            <th class="px-4 py-4">หมวด</th>
                            <th class="px-4 py-4">ไอคอน</th>
                            <th class="px-4 py-4 text-center">ลำดับ</th>
                            <th class="px-4 py-4 text-center">สถานะ</th>
                            <th class="px-6 py-4 text-right">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($blocks as $block)
                            <tr class="portal-row-hover transition-colors">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-[#0b2f52]">{{ $block->title }}</p>
                                    <p class="mt-1 line-clamp-2 text-xs text-slate-500">{{ $block->description ?: '-' }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 ring-1 ring-blue-100">
                                        {{ $sectionLabels[$block->section] ?? $block->section }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 font-mono text-xs font-bold text-slate-600">{{ $block->icon ?: '-' }}</td>
                                <td class="px-4 py-4 text-center font-bold text-slate-700">{{ number_format($block->sort_order) }}</td>
                                <td class="px-4 py-4 text-center">
                                    <span @class([
                                        'inline-flex rounded-full px-3 py-1 text-[11px] font-bold ring-1 ring-inset',
                                        'bg-emerald-50 text-emerald-700 ring-emerald-600/20' => $block->is_active,
                                        'bg-slate-100 text-slate-500 ring-slate-400/20' => ! $block->is_active,
                                    ])>
                                        {{ $block->is_active ? 'ใช้งานอยู่' : 'ปิดใช้งาน' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('staff.portal.about-us.edit', $block) }}"
                                            class="grid h-9 w-9 place-items-center rounded-lg bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-[#0b2f52] transition-all"
                                            title="แก้ไข">
                                            <i data-lucide="square-pen" class="h-4 w-4"></i>
                                        </a>
                                        <form action="{{ route('staff.portal.about-us.destroy', $block) }}" method="POST"
                                            onsubmit="return confirm('ต้องการลบบล็อกนี้ใช่หรือไม่?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="grid h-9 w-9 place-items-center rounded-lg bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-rose-600 transition-all"
                                                title="ลบ">
                                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-14 text-center text-slate-400">
                                    ไม่พบข้อมูล About us
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
