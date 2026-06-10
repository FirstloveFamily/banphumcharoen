@extends('layouts.manager', ['title' => 'ทะเบียนแรงงาน', 'pageTitle' => 'Workforce การจัดการ'])

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
                <h2 class="text-3xl font-bold tracking-tight text-[#0b2f52] italic">ทะเบียนแรงงาน</h2>
                <p class="mt-1 text-slate-500 text-lg font-medium">บริหารจัดการข้อมูลแรงงานและเอกสารประจำตัวทั้งหมด</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('manager.workers.create') }}" 
                    class="flex items-center gap-2 rounded-lg bg-[#0b2f52] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-[#0b2f52]/20 hover:bg-[#123e68] transition-all">
                    <i data-lucide="user-plus" class="h-4 w-4"></i>
                    เพิ่ม New แรงงาน
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

        <!-- ค้นหา & กรอง -->
        <section class="glass-card rounded-lg p-8 shadow-sm">
            <form method="GET" class="grid gap-6 md:grid-cols-[1fr_240px_auto] md:items-end">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">ค้นหาแรงงาน</label>
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400"></i>
                        <input name="search" type="text" value="{{ request('search') }}"
                            placeholder="ชื่อ, เลขพาสปอร์ต, ใบอนุญาตทำงาน..."
                            class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 pl-12 pr-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">นายจ้าง / ลูกค้า</label>
                    <select name="employer_id" class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all appearance-none">
                        <option value="">ทั้งหมด นายจ้าง</option>
                        @foreach ($employers as $id => $name)
                            <option value="{{ $id }}" @selected(request('employer_id') == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="h-12 px-8 rounded-lg bg-[#0b2f52] text-white hover:bg-[#123e68] transition-all shadow-lg">
                        <i data-lucide="filter" class="h-5 w-5"></i>
                    </button>
                    <a href="{{ route('manager.workers.index') }}" class="grid h-12 w-12 place-items-center rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition-all">
                        <i data-lucide="refresh-cw" class="h-5 w-5"></i>
                    </a>
                </div>
            </form>
        </section>

        <!-- Data Table -->
        <section class="glass-card overflow-hidden rounded-lg shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/50 px-8 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-black text-[#0b2f52] uppercase tracking-tighter italic">ทะเบียนแรงงาน</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">แสดง {{ number_format($workers->total()) }} แรงงานทั้งหมด</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                            <th class="px-8 py-5">ชื่อ-นามสกุล</th>
                            <th class="px-4 py-5">พาสปอร์ต</th>
                            <th class="px-4 py-5">ใบอนุญาตทำงาน</th>
                            <th class="px-4 py-5">นายจ้าง</th>
                            <th class="px-8 py-5 text-right">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($workers as $worker)
                            @php
                                $firstInitial = mb_substr($worker->first_name_th ?: $worker->first_name_en ?: $worker->full_name_th ?: $worker->full_name_en ?: 'W', 0, 1);
                                $lastInitial = mb_substr($worker->last_name_th ?: $worker->last_name_en ?: '', 0, 1);
                            @endphp
                            <tr class="group hover:bg-slate-50/80 transition-colors">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 border border-slate-200 uppercase font-black text-[10px]">
                                            {{ $firstInitial }}{{ $lastInitial }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-[#0b2f52] truncate">{{ $worker->full_name_th ?: '-' }}</p>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase mt-0.5">{{ $worker->full_name_en ?: '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-5 font-mono text-xs font-bold text-slate-700">
                                    {{ $worker->passport_number ?: '-' }}
                                </td>
                                <td class="px-4 py-5 font-mono text-xs font-bold text-slate-700">
                                    {{ $worker->wp_number ?: '-' }}
                                </td>
                                <td class="px-4 py-5 font-bold text-slate-500">
                                    {{ $worker->employer?->company_name ?? '-' }}
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('staff.portal.workers.show', $worker) }}" target="_blank"
                                            class="grid h-9 w-9 place-items-center rounded-lg bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-[#c9a227] transition-all" title="ดูรายละเอียด">
                                            <i data-lucide="eye" class="h-4 w-4"></i>
                                        </a>
                                        <a href="{{ route('manager.workers.edit', $worker) }}" 
                                            class="grid h-9 w-9 place-items-center rounded-lg bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-amber-600 transition-all" title="แก้ไข แรงงาน Data">
                                            <i data-lucide="edit-3" class="h-4 w-4"></i>
                                        </a>
                                        <form action="{{ route('manager.workers.destroy', $worker) }}" method="POST" onsubmit="return confirm('ยืนยันลบแรงงานนี้หรือไม่? เอกสารที่เกี่ยวข้องจะถูกลบด้วย');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="grid h-9 w-9 place-items-center rounded-lg bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-rose-600 transition-all" title="ลบ แรงงาน Record">
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
                                        <i data-lucide="users" class="h-8 w-8"></i>
                                    </div>
                                    <h3 class="mt-4 text-lg font-bold text-[#0b2f52]">ไม่พบข้อมูลแรงงาน</h3>
                                    <p class="text-slate-500">ลองปรับเงื่อนไขการค้นหา</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($workers->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/50 px-8 py-4">
                    {{ $workers->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
