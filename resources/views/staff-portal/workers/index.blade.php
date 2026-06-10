@extends('layouts.staff-portal', ['title' => 'รายชื่อแรงงาน', 'pageTitle' => 'จัดการข้อมูลแรงงาน'])

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
    @php
        $expiryLabels = [
            '' => 'เอกสารทั้งหมด',
            'expiring' => 'ใกล้หมดอายุ (45 วัน)',
            'expired' => 'หมดอายุแล้ว',
        ];

        $activeLabels = [
            '' => 'ทุกสถานะแรงงาน',
            'active' => 'ใช้งานอยู่ (Active)',
            'inactive' => 'ไม่ใช้งาน (Inactive)',
        ];

        $getDateStatus = function ($date) {
            if (! $date) return ['label' => '-', 'class' => 'text-slate-400'];
            
            $days = now()->startOfDay()->diffInDays($date->copy()->startOfDay(), false);
            
            if ($days < 0) return ['label' => $date->format('d/m/Y'), 'class' => 'text-rose-600 font-bold'];
            if ($days <= 45) return ['label' => $date->format('d/m/Y'), 'class' => 'text-amber-600 font-bold'];
            
            return ['label' => $date->format('d/m/Y'), 'class' => 'text-slate-700 font-semibold'];
        };
    @endphp

    <div class="space-y-8">
        <!-- Header Section -->
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">รายชื่อแรงงานทั้งหมด</h2>
                <p class="mt-1 text-slate-500 text-lg">ติดตามสถานะเอกสารและข้อมูลแรงงานจากทุกบริษัท</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('staff.portal.workers.create') }}" 
                    class="flex items-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/20 hover:bg-blue-700 transition-all">
                    <i data-lucide="user-plus" class="h-4 w-4"></i>
                    เพิ่มแรงงานใหม่
                </a>
            </div>
        </header>

        <!-- Stats Grid -->
        <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <article class="glass-card hover-shadow rounded-3xl p-6 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-blue-50 opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                        <i data-lucide="users" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">แรงงานทั้งหมด</p>
                        <h3 class="text-2xl font-black text-slate-900">{{ number_format($summary['total']) }}</h3>
                    </div>
                </div>
            </article>

            <article class="glass-card hover-shadow rounded-3xl p-6 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-emerald-50 opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                        <i data-lucide="user-check" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">ใช้งานอยู่</p>
                        <h3 class="text-2xl font-black text-slate-900">{{ number_format($summary['active']) }}</h3>
                    </div>
                </div>
            </article>

            <article class="glass-card hover-shadow rounded-3xl p-6 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-amber-50 opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                        <i data-lucide="clock" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">ใกล้หมดอายุ</p>
                        <h3 class="text-2xl font-black text-slate-900">{{ number_format($summary['expiring']) }}</h3>
                    </div>
                </div>
            </article>

            <article class="glass-card hover-shadow rounded-3xl p-6 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-rose-50 opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                        <i data-lucide="alert-triangle" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">หมดอายุแล้ว</p>
                        <h3 class="text-2xl font-black text-slate-900">{{ number_format($summary['expired']) }}</h3>
                    </div>
                </div>
            </article>
        </section>

        <!-- Search & Filter -->
        <section class="glass-card rounded-3xl p-6 shadow-sm">
            <form method="GET" class="grid gap-6 md:grid-cols-2 lg:grid-cols-[1fr_200px_200px_auto]">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400"></i>
                    <input type="text" name="q" value="{{ $keyword }}" 
                        placeholder="ค้นหาชื่อ, บริษัท, Passport, หรือ Work Permit..." 
                        class="w-full h-12 rounded-2xl border border-slate-100 bg-slate-50/50 pl-12 pr-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all">
                </div>
                <div>
                    <select name="expiry" class="w-full h-12 rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all appearance-none">
                        @foreach ($expiryLabels as $value => $label)
                            <option value="{{ $value }}" @selected($expiryStatus === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="active" class="w-full h-12 rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-blue-400 focus:bg-white transition-all appearance-none">
                        @foreach ($activeLabels as $value => $label)
                            <option value="{{ $value }}" @selected($activeStatus === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="h-12 px-8 rounded-2xl bg-slate-900 text-sm font-bold text-white hover:bg-slate-800 transition-all">
                        ค้นหา
                    </button>
                    <a href="{{ route('staff.portal.workers.index') }}" class="grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-500 hover:bg-slate-200 transition-all">
                        <i data-lucide="refresh-cw" class="h-5 w-5"></i>
                    </a>
                </div>
            </form>
        </section>

        <!-- Data Table -->
        <section class="glass-card overflow-hidden rounded-3xl shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm min-w-[1000px]">
                    <thead>
                        <tr class="bg-slate-50/50 text-xs font-bold uppercase tracking-wider text-slate-400">
                            <th class="px-8 py-5">แรงงาน / นายจ้าง</th>
                            <th class="px-4 py-5 text-center">Passport</th>
                            <th class="px-4 py-5 text-center">Work Permit</th>
                            <th class="px-4 py-5 text-center">Visa</th>
                            <th class="px-4 py-5 text-center">90 วัน</th>
                            <th class="px-8 py-5 text-right">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($workers as $worker)
                            @php
                                $passport = $getDateStatus($worker->passport_expiry);
                                $wp = $getDateStatus($worker->wp_expiry);
                                $visa = $getDateStatus($worker->visa_expiry);
                                $report = $getDateStatus($worker->report_90_days_due);
                            @endphp
                            <tr class="group hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        @if ($worker->photo_path)
                                            <img src="{{ asset('storage/' . $worker->photo_path) }}" alt="" 
                                                class="h-12 w-12 rounded-full object-cover border-2 border-white shadow-sm">
                                        @else
                                            <div class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-400 border border-slate-200 uppercase text-[10px]">
                                                {{ mb_substr($worker->first_name_th ?: $worker->first_name_en, 0, 1) }}{{ mb_substr($worker->last_name_th ?: $worker->last_name_en, 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <p class="font-bold text-slate-900 truncate">{{ $worker->full_name_th ?: $worker->full_name_en }}</p>
                                                @if(! $worker->is_active)
                                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span>
                                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Inactive</span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-slate-500 truncate">{{ $worker->employer?->company_name ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-5 text-center">
                                    <p class="text-xs font-bold text-slate-900">{{ $worker->passport_number ?: '-' }}</p>
                                    <p class="mt-1 text-xs {{ $passport['class'] }}">{{ $passport['label'] }}</p>
                                </td>
                                <td class="px-4 py-5 text-center">
                                    <p class="text-xs font-bold text-slate-900">{{ $worker->wp_number ?: '-' }}</p>
                                    <p class="mt-1 text-xs {{ $wp['class'] }}">{{ $wp['label'] }}</p>
                                </td>
                                <td class="px-4 py-5 text-center">
                                    <p class="text-xs {{ $visa['class'] }}">{{ $visa['label'] }}</p>
                                </td>
                                <td class="px-4 py-5 text-center">
                                    <p class="text-xs {{ $report['class'] }}">{{ $report['label'] }}</p>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('staff.portal.workers.show', $worker) }}" 
                                            class="grid h-9 w-9 place-items-center rounded-xl bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-blue-600 hover:border-blue-100 transition-all"
                                            title="ดูรายละเอียด">
                                            <i data-lucide="eye" class="h-4 w-4"></i>
                                        </a>
                                        <a href="{{ route('staff.portal.workers.edit', $worker) }}" 
                                            class="grid h-9 w-9 place-items-center rounded-xl bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-amber-600 hover:border-amber-100 transition-all"
                                            title="แก้ไขข้อมูล">
                                            <i data-lucide="edit-2" class="h-4 w-4"></i>
                                        </a>
                                        <form action="{{ route('staff.portal.workers.destroy', $worker) }}" method="POST"
                                            onsubmit="return confirm('ยืนยันการลบแรงงานรายนี้ใช่หรือไม่?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="grid h-9 w-9 place-items-center rounded-xl bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-rose-600 hover:border-rose-100 transition-all"
                                                title="ลบแรงงาน">
                                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-20 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-50 text-slate-300">
                                        <i data-lucide="users" class="h-8 w-8"></i>
                                    </div>
                                    <h3 class="mt-4 text-lg font-bold text-slate-900">ไม่พบข้อมูลแรงงาน</h3>
                                    <p class="text-slate-500">ลองใช้คำค้นหาอื่น หรือเปลี่ยนตัวกรอง</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($workers->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/50 px-8 py-4">
                    {{ $workers->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
