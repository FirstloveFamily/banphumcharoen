@extends('layouts.staff-portal', ['title' => 'รายชื่อนายจ้าง', 'pageTitle' => 'จัดการข้อมูลนายจ้าง'])

@push('head')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
    .hover-shadow {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .hover-shadow:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
    }
</style>
@endpush

@section('content')
    <div class="space-y-8">
        <!-- Header Section -->
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">รายชื่อนายจ้างทั้งหมด</h2>
                <p class="mt-1 text-slate-500 text-lg">ค้นหาและจัดการข้อมูลบริษัท/นายจ้างในระบบ</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('staff.portal.employers.create') }}" 
                    class="flex items-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/20 hover:bg-blue-700 transition-all">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    เพิ่มนายจ้างใหม่
                </a>
            </div>
        </header>

        <!-- Stats Grid -->
        <section class="grid gap-6 sm:grid-cols-3">
            <article class="glass-card hover-shadow rounded-3xl p-6 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-blue-50 opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                        <i data-lucide="building-2" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">นายจ้างทั้งหมด</p>
                        <h3 class="text-2xl font-black text-slate-900">{{ number_format($summary['total']) }}</h3>
                    </div>
                </div>
            </article>

            <article class="glass-card hover-shadow rounded-3xl p-6 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-emerald-50 opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                        <i data-lucide="check-circle" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Active</p>
                        <h3 class="text-2xl font-black text-slate-900">{{ number_format($summary['active']) }}</h3>
                    </div>
                </div>
            </article>

            <article class="glass-card hover-shadow rounded-3xl p-6 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-slate-50 opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-400">
                        <i data-lucide="pause-circle" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Inactive</p>
                        <h3 class="text-2xl font-black text-slate-900">{{ number_format($summary['inactive']) }}</h3>
                    </div>
                </div>
            </article>
        </section>

        <!-- Search & Filter -->
        <section class="glass-card rounded-3xl p-6 shadow-sm">
            <form method="GET" class="grid gap-6 md:grid-cols-[1fr_200px_auto]">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400"></i>
                    <input type="text" name="q" value="{{ $keyword }}" 
                        placeholder="ค้นหาชื่อบริษัท, รหัส หรือชื่อผู้ติดต่อ..." 
                        class="w-full h-12 rounded-2xl border border-slate-100 bg-slate-50/50 pl-12 pr-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all">
                </div>
                <div>
                    <select name="status" class="w-full h-12 rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all appearance-none">
                        <option value="">ทุกสถานะ</option>
                        <option value="active" @selected($status === 'active')>ใช้งานอยู่ (Active)</option>
                        <option value="inactive" @selected($status === 'inactive')>ระงับ (Inactive)</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="h-12 px-6 rounded-2xl bg-slate-900 text-sm font-bold text-white hover:bg-slate-800 transition-all">
                        ค้นหา
                    </button>
                    <a href="{{ route('staff.portal.employers.index') }}" class="grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-500 hover:bg-slate-200 transition-all">
                        <i data-lucide="refresh-cw" class="h-5 w-5"></i>
                    </a>
                </div>
            </form>
        </section>

        <!-- Data Table -->
        <section class="glass-card overflow-hidden rounded-3xl shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 text-xs font-bold uppercase tracking-wider text-slate-400">
                            <th class="px-8 py-5">ชื่อบริษัท / รหัส</th>
                            <th class="px-4 py-5">ผู้ติดต่อ / เบอร์โทร</th>
                            <th class="px-4 py-5 text-center">แรงงาน</th>
                            <th class="px-4 py-5 text-center">ใบงาน</th>
                            <th class="px-4 py-5 text-center">สถานะ</th>
                            <th class="px-8 py-5 text-right">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($employers as $employer)
                            <tr class="group hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        @if ($employer->logo)
                                            <div class="h-11 w-11 shrink-0 overflow-hidden rounded-xl border border-slate-100 bg-white p-1">
                                                <img src="{{ asset('storage/' . $employer->logo) }}" alt="" class="h-full w-full object-contain">
                                            </div>
                                        @else
                                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-400 border border-slate-200">
                                                <i data-lucide="building" class="h-5 w-5"></i>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="font-bold text-slate-900 truncate">{{ $employer->company_name }}</p>
                                            <p class="text-xs font-mono text-slate-500 uppercase">{{ $employer->company_code }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="font-semibold text-slate-700">{{ $employer->contact_name ?: '-' }}</p>
                                    <p class="text-xs text-slate-500">{{ $employer->phone ?: '-' }}</p>
                                </td>
                                <td class="px-4 py-5 text-center">
                                    <span class="inline-flex h-8 items-center justify-center rounded-lg bg-blue-50 px-3 font-bold text-blue-600 ring-1 ring-inset ring-blue-500/10">
                                        {{ number_format($employer->workers_count) }}
                                    </span>
                                </td>
                                <td class="px-4 py-5 text-center">
                                    <span class="inline-flex h-8 items-center justify-center rounded-lg bg-slate-100 px-3 font-bold text-slate-600">
                                        {{ number_format($employer->job_orders_count) }}
                                    </span>
                                </td>
                                <td class="px-4 py-5 text-center">
                                    <span @class([
                                        'rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset',
                                        'bg-emerald-50 text-emerald-600 ring-emerald-500/20' => $employer->is_active,
                                        'bg-slate-100 text-slate-500 ring-slate-400/20' => ! $employer->is_active,
                                    ])>
                                        {{ $employer->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('staff.portal.employers.show', $employer) }}" 
                                            class="grid h-9 w-9 place-items-center rounded-xl bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-blue-600 hover:border-blue-100 transition-all"
                                            title="ดูรายละเอียด">
                                            <i data-lucide="eye" class="h-4 w-4"></i>
                                        </a>
                                        <a href="{{ route('staff.portal.employers.edit', $employer) }}" 
                                            class="grid h-9 w-9 place-items-center rounded-xl bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-amber-600 hover:border-amber-100 transition-all"
                                            title="แก้ไขข้อมูล">
                                            <i data-lucide="edit-2" class="h-4 w-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-20 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-50 text-slate-300">
                                        <i data-lucide="database" class="h-8 w-8"></i>
                                    </div>
                                    <h3 class="mt-4 text-lg font-bold text-slate-900">ไม่พบข้อมูลนายจ้าง</h3>
                                    <p class="text-slate-500">ลองใช้คำค้นหาอื่น หรือเพิ่มข้อมูลนายจ้างใหม่</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($employers->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/50 px-8 py-4">
                    {{ $employers->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
