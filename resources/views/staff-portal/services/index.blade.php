@extends('layouts.staff-portal', ['title' => 'บริการ', 'pageTitle' => 'บริการ'])

@section('content')
    <div class="space-y-8">
        <header class="overflow-hidden rounded-lg bg-[#0b2f52] text-white shadow-xl shadow-[#0b2f52]/15">
            <div class="flex flex-col gap-6 px-5 py-6 sm:px-7 lg:flex-row lg:items-end lg:justify-between lg:px-8">
                <div class="max-w-3xl">
                    <div class="mb-4 h-1 w-24 rounded-full bg-gradient-to-r from-[#b91c1c] to-transparent"></div>
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-[#fecaca]">Service Setup</p>
                    <h2 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">
                        จัดการบริการ
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                        สร้าง แก้ไข และลบบริการ พร้อมกำหนดเอกสารที่ต้องใช้ในแต่ละบริการ
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('staff.portal.document-masters.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-white/10 bg-white/10 px-4 py-3 text-sm font-extrabold text-white transition hover:bg-white/20">
                        <i data-lucide="file-text" class="h-4 w-4"></i>
                        ประเภทเอกสาร
                    </a>
                    <a href="{{ route('staff.portal.services.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-[#0b2f52] via-[#123e68] to-[#b91c1c] px-4 py-3 text-sm font-extrabold text-white shadow-lg shadow-black/20 transition hover:opacity-95">
                        <i data-lucide="plus" class="h-4 w-4"></i>
                        เพิ่มบริการใหม่
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
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">บริการทั้งหมด</p>
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
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">รายการเอกสารที่ใช้</p>
                <h3 class="mt-3 text-3xl font-extrabold text-[#0b2f52]">{{ number_format($summary['checklists']) }}</h3>
            </article>
        </section>

        <section class="manager-card overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-end sm:justify-between sm:px-6">
                <form method="GET" class="grid gap-3 sm:grid-cols-[1.5fr_220px_auto] sm:items-end">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">ค้นหา</label>
                        <input type="text" name="q" value="{{ $keyword }}" placeholder="ชื่อบริการ, รหัสบริการ, รายละเอียด"
                            class="portal-input h-11 w-full px-4 text-sm font-medium">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">สถานะ</label>
                        <select name="status" class="portal-select h-11 w-full px-4 text-sm font-medium">
                            <option value="">ทั้งหมด</option>
                            <option value="active" @selected($status === 'active')>ใช้งานอยู่</option>
                            <option value="inactive" @selected($status === 'inactive')>ไม่ใช้งาน</option>
                        </select>
                    </div>
                    <button type="submit" class="portal-btn-primary h-11 px-5 text-sm font-bold">
                        ค้นหา
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="portal-table-head text-xs font-bold uppercase tracking-wider">
                            <th class="px-6 py-4">บริการ</th>
                            <th class="px-4 py-4">รหัส</th>
                            <th class="px-4 py-4 text-center">แจ้งเตือนก่อนหมดอายุ</th>
                            <th class="px-4 py-4 text-center">เอกสาร</th>
                            <th class="px-4 py-4 text-center">สถานะ</th>
                            <th class="px-6 py-4 text-right">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($services as $service)
                            <tr class="portal-row-hover transition-colors">
                                <td class="px-6 py-4">
                                    <div class="min-w-0">
                                        <p class="font-bold text-[#0b2f52]">{{ $service->name }}</p>
                                        <p class="mt-1 line-clamp-2 text-xs text-slate-500">{{ $service->description ?: '-' }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-4 font-mono text-xs font-bold text-slate-600">{{ $service->code }}</td>
                                <td class="px-4 py-4 text-center font-bold text-slate-700">{{ number_format($service->alert_days_before_expiry) }} วัน</td>
                                <td class="px-4 py-4 text-center font-bold text-slate-700">{{ number_format($service->checklists_count) }}</td>
                                <td class="px-4 py-4 text-center">
                                    <span @class([
                                        'inline-flex rounded-full px-3 py-1 text-[11px] font-bold ring-1 ring-inset',
                                        'bg-emerald-50 text-emerald-700 ring-emerald-600/20' => $service->is_active,
                                        'bg-slate-100 text-slate-500 ring-slate-400/20' => ! $service->is_active,
                                    ])>
                                        {{ $service->is_active ? 'ใช้งานอยู่' : 'ไม่ใช้งาน' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('staff.portal.services.show', $service) }}" class="grid h-9 w-9 place-items-center rounded-lg bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-[#0b2f52] hover:border-[#b91c1c]/40 transition-all" title="จัดการเอกสาร">
                                            <i data-lucide="file-stack" class="h-4 w-4"></i>
                                        </a>
                                        <a href="{{ route('staff.portal.services.edit', $service) }}" class="grid h-9 w-9 place-items-center rounded-lg bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-[#b91c1c] transition-all" title="แก้ไข">
                                            <i data-lucide="square-pen" class="h-4 w-4"></i>
                                        </a>
                                        <form method="POST" action="{{ route('staff.portal.services.destroy', $service) }}" onsubmit="return confirm('ต้องการลบบริการนี้ใช่หรือไม่?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="grid h-9 w-9 place-items-center rounded-lg bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-rose-600 transition-all" title="ลบ">
                                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-14 text-center text-slate-400">
                                    ไม่พบข้อมูลบริการ
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-6 py-4">
                {{ $services->withQueryString()->links() }}
            </div>
        </section>
    </div>
@endsection
