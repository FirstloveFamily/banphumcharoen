@extends('layouts.staff-portal', ['title' => 'รายละเอียดนายจ้าง', 'pageTitle' => 'ข้อมูลนายจ้าง/บริษัท'])

@push('head')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
</style>
@endpush

@section('content')
    <div class="space-y-8">
        <!-- Header Section -->
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-5">
                <a href="{{ route('staff.portal.employers.index') }}" 
                    class="grid h-12 w-12 place-items-center rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-slate-600 transition-all shadow-sm">
                    <i data-lucide="arrow-left" class="h-6 w-6"></i>
                </a>
                <div>
                    <h2 class="text-3xl font-bold tracking-tight text-slate-900">{{ $employer->company_name }}</h2>
                    <div class="mt-1 flex items-center gap-3">
                        <span class="text-xs font-mono font-bold text-slate-400 uppercase tracking-tighter">Code: {{ $employer->company_code }}</span>
                        <div class="h-1 w-1 rounded-full bg-slate-300"></div>
                        <span @class([
                            'rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider',
                            'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-500/20' => $employer->is_active,
                            'bg-slate-100 text-slate-500 ring-1 ring-slate-400/20' => ! $employer->is_active,
                        ])>
                            {{ $employer->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('staff.portal.employers.edit', $employer) }}" 
                    class="flex items-center gap-2 rounded-2xl bg-amber-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-amber-500/20 hover:bg-amber-600 transition-all">
                    <i data-lucide="edit-3" class="h-4 w-4"></i>
                    แก้ไขข้อมูล
                </a>
            </div>
        </header>

        <div class="grid gap-8 lg:grid-cols-3">
            <!-- Left Column: Details -->
            <div class="space-y-8 lg:col-span-1">
                <!-- Profile Card -->
                <section class="glass-card rounded-3xl p-8 shadow-sm">
                    <div class="flex flex-col items-center text-center">
                        <div class="relative">
                            @if ($employer->logo)
                                <div class="h-32 w-32 overflow-hidden rounded-3xl border-4 border-white bg-white shadow-xl">
                                    <img src="{{ asset('storage/' . $employer->logo) }}" alt="" class="h-full w-full object-contain p-2">
                                </div>
                            @else
                                <div class="flex h-32 w-32 items-center justify-center rounded-3xl bg-slate-100 border-4 border-white text-slate-300 shadow-xl">
                                    <i data-lucide="building" class="h-12 w-12"></i>
                                </div>
                            @endif
                            <div class="absolute -bottom-2 -right-2 grid h-10 w-10 place-items-center rounded-2xl bg-blue-600 text-white shadow-lg">
                                <i data-lucide="shield-check" class="h-5 w-5"></i>
                            </div>
                        </div>
                        <h3 class="mt-6 text-xl font-bold text-slate-900">{{ $employer->company_name }}</h3>
                        <p class="text-sm font-medium text-slate-500 uppercase tracking-widest">{{ $employer->company_code }}</p>
                    </div>

                    <div class="mt-8 space-y-6 border-t border-slate-50 pt-8">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">ผู้ติดต่อหลัก</p>
                            <p class="mt-1.5 text-sm font-bold text-slate-900">{{ $employer->contact_name ?: '-' }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">เบอร์โทรศัพท์</p>
                                <p class="mt-1.5 text-sm font-bold text-slate-900">{{ $employer->phone ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">อีเมล</p>
                                <p class="mt-1.5 text-sm font-bold text-slate-900">{{ $employer->email ?: '-' }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">เลขผู้เสียภาษี</p>
                            <p class="mt-1.5 text-sm font-bold text-slate-900">{{ $employer->tax_id ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">ที่อยู่บริษัท</p>
                            <p class="mt-1.5 text-sm font-medium leading-relaxed text-slate-600">{{ $employer->address ?: '-' }}</p>
                        </div>
                        @if($employer->notes)
                            <div class="rounded-2xl bg-rose-50 p-4 border border-rose-100">
                                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-rose-400">หมายเหตุ</p>
                                <p class="mt-1.5 text-sm font-bold text-rose-700 leading-relaxed">{{ $employer->notes }}</p>
                            </div>
                        @endif
                    </div>
                </section>

                <!-- Stats Summary -->
                <section class="grid grid-cols-2 gap-4">
                    <div class="glass-card rounded-3xl p-6 text-center shadow-sm">
                        <p class="text-3xl font-black text-blue-600">{{ number_format($summary['total_workers']) }}</p>
                        <p class="mt-1 text-xs font-bold uppercase tracking-wider text-slate-400">ลูกจ้างทั้งหมด</p>
                    </div>
                    <div class="glass-card rounded-3xl p-6 text-center shadow-sm">
                        <p class="text-3xl font-black text-emerald-600">{{ number_format($summary['active_workers']) }}</p>
                        <p class="mt-1 text-xs font-bold uppercase tracking-wider text-slate-400">สถานะ Active</p>
                    </div>
                </section>
            </div>

            <!-- Right Column: Workers Table -->
            <div class="space-y-8 lg:col-span-2">
                <section class="glass-card overflow-hidden rounded-3xl shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-8 py-5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-500/20">
                                <i data-lucide="users" class="h-5 w-5"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">รายชื่อแรงงานในสังกัด</h3>
                                <p class="text-sm text-slate-500">จัดการข้อมูลแรงงานที่เชื่อมโยงกับนายจ้างนี้</p>
                            </div>
                        </div>
                        <a href="{{ route('staff.portal.workers.create', ['employer_id' => $employer->id]) }}" 
                            class="flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800 transition-all">
                            <i data-lucide="user-plus" class="h-3.5 w-3.5"></i>
                            เพิ่มแรงงาน
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="bg-slate-50/50 text-xs font-bold uppercase tracking-wider text-slate-400">
                                    <th class="px-8 py-5">ชื่อ-นามสกุล</th>
                                    <th class="px-4 py-5">สัญชาติ / เพศ</th>
                                    <th class="px-4 py-5">Passport No.</th>
                                    <th class="px-4 py-5 text-center">สถานะ</th>
                                    <th class="px-8 py-5 text-right">การจัดการ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($employer->workers as $worker)
                                    <tr class="group hover:bg-slate-50/50 transition-colors">
                                        <td class="px-8 py-5">
                                            <div class="flex items-center gap-3">
                                                @if ($worker->photo_path)
                                                    <img src="{{ asset('storage/' . $worker->photo_path) }}" alt="" 
                                                        class="h-10 w-10 rounded-full object-cover border-2 border-white shadow-sm">
                                                @else
                                                    <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-400 border border-slate-200 uppercase text-[10px]">
                                                        {{ mb_substr($worker->first_name_th ?: $worker->first_name_en, 0, 1) }}{{ mb_substr($worker->last_name_th ?: $worker->last_name_en, 0, 1) }}
                                                    </div>
                                                @endif
                                                <div class="min-w-0">
                                                    <p class="font-bold text-slate-900 truncate">{{ $worker->full_name_th ?: $worker->full_name_en }}</p>
                                                    <p class="text-xs text-slate-500 truncate">{{ $worker->full_name_en }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-5">
                                            <p class="font-semibold text-slate-700">{{ $worker->nationality?->name_th ?: '-' }}</p>
                                            <p class="text-xs text-slate-400">{{ $worker->gender ?: '-' }}</p>
                                        </td>
                                        <td class="px-4 py-5 font-mono font-bold text-slate-700">
                                            {{ $worker->passport_number ?: '-' }}
                                        </td>
                                        <td class="px-4 py-5 text-center">
                                            <span @class([
                                                'rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset',
                                                'bg-blue-50 text-blue-600 ring-blue-500/20' => $worker->is_active,
                                                'bg-slate-100 text-slate-500 ring-slate-400/20' => ! $worker->is_active,
                                            ])>
                                                {{ $worker->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-8 py-5 text-right">
                                            <a href="{{ route('staff.portal.workers.show', $worker) }}" 
                                                class="grid h-9 w-9 place-items-center rounded-xl bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-blue-600 hover:border-blue-100 transition-all opacity-0 group-hover:opacity-100">
                                                <i data-lucide="eye" class="h-4 w-4"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-8 py-16 text-center">
                                            <p class="text-slate-400">ไม่มีข้อมูลแรงงานในสังกัดของนายจ้างนี้</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
