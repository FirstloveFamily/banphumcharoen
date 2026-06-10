@extends('layouts.manager', ['title' => 'จัดการลูกค้า', 'pageTitle' => 'นายจ้าง การจัดการ'])

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
                <h2 class="text-3xl font-bold tracking-tight text-[#0b2f52] italic">จัดการลูกค้า</h2>
                <p class="mt-1 text-slate-500 text-lg font-medium">บริหารจัดการข้อมูลนายจ้างและคู่ค้าในระบบ</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('manager.employers.create') }}" 
                    class="flex items-center gap-2 rounded-lg bg-[#0b2f52] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-[#0b2f52]/20 hover:bg-[#123e68] transition-all">
                    <i data-lucide="plus-circle" class="h-4 w-4"></i>
                    ลงทะเบียนลูกค้าใหม่
                </a>
            </div>
        </header>

        <!-- แจ้งเตือนs -->
        @if (session('success'))
            <div class="rounded-lg bg-emerald-50 p-4 border border-emerald-100 flex items-center gap-3 text-emerald-700 font-bold text-sm">
                <i data-lucide="check-circle" class="h-5 w-5"></i>
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-lg bg-rose-50 p-4 border border-rose-100 flex items-center gap-3 text-rose-700 font-bold text-sm">
                <i data-lucide="alert-octagon" class="h-5 w-5"></i>
                {{ session('error') }}
            </div>
        @endif

        <!-- ค้นหา & กรอง -->
        <section class="glass-card rounded-lg p-8 shadow-sm">
            <form method="GET" class="grid gap-6 md:grid-cols-[1fr_220px_auto] md:items-end">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">ค้นหา</label>
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400"></i>
                        <input name="search" type="text" value="{{ request('search') }}"
                            placeholder="ชื่อบริษัท, รหัส, อีเมล, เลขภาษี..."
                            class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 pl-12 pr-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">สถานะ</label>
                    <select name="is_active" class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all appearance-none">
                        <option value="">ทั้งหมด สถานะ</option>
                        <option value="true" @selected(request('is_active') === 'true')>เฉพาะเปิดใช้งาน</option>
                        <option value="false" @selected(request('is_active') === 'false')>เฉพาะปิดใช้งาน</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="h-12 px-8 rounded-lg bg-[#0b2f52] text-white hover:bg-[#123e68] transition-all shadow-lg">
                        <i data-lucide="filter" class="h-5 w-5"></i>
                    </button>
                    <a href="{{ route('manager.employers.index') }}" class="grid h-12 w-12 place-items-center rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition-all">
                        <i data-lucide="refresh-cw" class="h-5 w-5"></i>
                    </a>
                </div>
            </form>
        </section>

        <!-- Data Table -->
        <section class="glass-card overflow-hidden rounded-lg shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/50 px-8 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-black text-[#0b2f52] uppercase tracking-tighter italic">นายจ้าง ทะเบียน</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">แสดง {{ number_format($employers->total()) }} ลูกค้าทั้งหมด</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                            <th class="px-8 py-5">บริษัท / รหัส</th>
                            <th class="px-4 py-5">ผู้ติดต่อหลัก</th>
                            <th class="px-4 py-5">เบอร์โทรศัพท์</th>
                            <th class="px-4 py-5 text-center">สถานะ</th>
                            <th class="px-8 py-5 text-right">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($employers as $employer)
                            <tr class="group hover:bg-slate-50/80 transition-colors">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        @if ($employer->logo)
                                            <img src="{{ asset('storage/' . $employer->logo) }}" class="h-10 w-10 rounded-lg object-contain bg-white border border-slate-100 p-1 shadow-sm">
                                        @else
                                            <div class="h-10 w-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 border border-slate-200">
                                                <i data-lucide="building" class="h-5 w-5"></i>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="font-bold text-[#0b2f52] truncate">{{ $employer->company_name }}</p>
                                            <p class="text-[10px] font-black text-[#c9a227] uppercase mt-0.5">{{ $employer->company_code ?: '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-5 font-bold text-slate-700">
                                    {{ $employer->contact_name ?: '-' }}
                                </td>
                                <td class="px-4 py-5 font-mono text-xs font-bold text-slate-500">
                                    {{ $employer->phone ?: '-' }}
                                </td>
                                <td class="px-4 py-5 text-center">
                                    <span @class([
                                        'rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset inline-block min-w-[90px]',
                                        'bg-emerald-50 text-emerald-700 ring-emerald-600/20' => $employer->is_active,
                                        'bg-slate-100 text-slate-400 ring-slate-400/20' => !$employer->is_active,
                                    ])>
                                        {{ $employer->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('staff.portal.employers.show', $employer) }}" target="_blank"
                                            class="grid h-9 w-9 place-items-center rounded-lg bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-[#c9a227] transition-all" title="ดูรายละเอียด">
                                            <i data-lucide="eye" class="h-4 w-4"></i>
                                        </a>
                                        <a href="{{ route('manager.employers.edit', $employer) }}" 
                                            class="grid h-9 w-9 place-items-center rounded-lg bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-amber-600 transition-all" title="แก้ไขข้อมูลลูกค้า">
                                            <i data-lucide="edit-3" class="h-4 w-4"></i>
                                        </a>
                                        <form action="{{ route('manager.employers.destroy', $employer) }}" method="POST" onsubmit="return confirm('ยืนยันลบลูกค้านี้หรือไม่? การดำเนินการนี้ไม่สามารถย้อนกลับได้');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="grid h-9 w-9 place-items-center rounded-lg bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-rose-600 transition-all" title="ลบลูกค้า">
                                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-20 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-lg bg-slate-50 text-slate-300">
                                        <i data-lucide="building" class="h-8 w-8"></i>
                                    </div>
                                    <h3 class="mt-4 text-lg font-bold text-[#0b2f52]">ไม่พบข้อมูลลูกค้า</h3>
                                    <p class="text-slate-500">ลองปรับเงื่อนไขการค้นหา</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($employers->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/50 px-8 py-4">
                    {{ $employers->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
