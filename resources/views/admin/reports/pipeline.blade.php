@extends('layouts.manager', ['title' => 'วิเคราะห์งาน', 'pageTitle' => 'Operations Pipeline'])

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
        $statusClasses = [
            'pending' => 'bg-[#fff9e8] text-[#0b2f52] ring-[#c9a227]/30',
            'processing' => 'bg-[#fff9e8] text-[#0b2f52] ring-[#c9a227]/30',
            'waiting_document' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            'completed' => 'bg-[#0b2f52] text-white',
            'cancelled' => 'bg-slate-50 text-slate-500 ring-slate-400/20',
            'rejected' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        ];
        $priorityLabels = ['' => 'ทุกระดับความสำคัญ', 'urgent' => 'ด่วนพิเศษ', 'high' => 'สูง', 'medium' => 'ปานกลาง', 'low' => 'ต่ำ'];
        $priorityClasses = [
            'urgent' => 'bg-rose-600 text-white',
            'high' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
            'medium' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            'low' => 'bg-slate-50 text-slate-600 ring-slate-400/10',
        ];
        $maxสถานะCount = max($statusCounts->max('count') ?: 1, 1);
    @endphp

    <div class="space-y-8">
        <!-- Header Section -->
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-[#0b2f52] italic">Operations Pipeline</h2>
                <p class="mt-1 text-slate-500 text-lg font-medium">วิเคราะห์กระบวนการทำงานและติดตามรายการที่ค้างชำระ/ดำเนินการ</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('manager.job-orders.index') }}" 
                    class="flex items-center gap-2 rounded-lg bg-white border border-slate-200 px-6 py-3 text-sm font-bold text-slate-600 hover:text-[#c9a227] transition-all shadow-sm">
                    <i data-lucide="list-todo" class="h-4 w-4"></i>
                    Job ทะเบียน
                </a>
            </div>
        </header>

        <!-- Stats Grid -->
        <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <article class="glass-card hover-shadow rounded-lg p-6 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-[#fff9e8] opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">รวม Lifecycle</p>
                    <h3 class="text-3xl font-black text-[#0b2f52] mt-2">{{ number_format($total) }}</h3>
                </div>
            </article>

            <article class="glass-card hover-shadow rounded-lg p-6 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-[#fff9e8] opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">งานที่กำลังดำเนินการ</p>
                    <h3 class="text-3xl font-black text-[#0b2f52] mt-2">{{ number_format($open) }}</h3>
                </div>
            </article>

            <article class="glass-card hover-shadow rounded-lg p-6 relative overflow-hidden group border-rose-100">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-rose-50 opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Critical เกินกำหนด</p>
                    <h3 class="text-3xl font-black text-rose-600 mt-2">{{ number_format($overdue) }}</h3>
                </div>
            </article>

            <article class="glass-card hover-shadow rounded-lg p-6 relative overflow-hidden group border-emerald-100">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-emerald-50 opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="relative">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Successfully Closed</p>
                    <h3 class="text-3xl font-black text-emerald-600 mt-2">{{ number_format($completed) }}</h3>
                </div>
            </article>
        </section>

        <!-- ค้นหา & กรอง -->
        <section class="glass-card rounded-lg p-8 shadow-sm">
            <form method="GET" class="grid gap-6 xl:grid-cols-[repeat(2,180px)_1fr_repeat(2,180px)_auto] xl:items-end">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">วันที่เริ่มต้น</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">วันที่สิ้นสุด</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">นายจ้าง / ลูกค้า</label>
                    <select name="employer_id" class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all appearance-none">
                        <option value="">ทั้งหมด นายจ้าง</option>
                        @foreach ($employers as $id => $name)
                            <option value="{{ $id }}" @selected((string) request('employer_id') === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">Assigned Staff</label>
                    <select name="assigned_user_id" class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all appearance-none">
                        <option value="">ทั้งหมด Staff</option>
                        @foreach ($assignedUsers as $id => $name)
                            <option value="{{ $id }}" @selected((string) request('assigned_user_id') === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">ความสำคัญ</label>
                    <select name="priority" class="h-12 w-full rounded-lg border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-[#c9a227] focus:bg-white transition-all appearance-none text-center">
                        @foreach ($priorityLabels as $value => $label)
                            <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="h-12 px-6 rounded-lg bg-[#0b2f52] text-white hover:bg-[#123e68] transition-all shadow-lg shadow-[#0b2f52]/20">
                        <i data-lucide="filter" class="h-5 w-5"></i>
                    </button>
                    <a href="{{ route('manager.reports.pipeline') }}" class="grid h-12 w-12 place-items-center rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition-all">
                        <i data-lucide="refresh-cw" class="h-5 w-5"></i>
                    </a>
                </div>
            </form>
        </section>

        <div class="grid gap-8 lg:grid-cols-3">
            <!-- ขั้นตอนงาน Distribution -->
            <section class="glass-card rounded-lg p-8 shadow-sm lg:col-span-2">
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-black text-[#0b2f52] uppercase tracking-tighter italic">ขั้นตอนงาน Distribution</h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Live status tracking across pipeline</p>
                    </div>
                    <i data-lucide="kanban" class="h-6 w-6 text-[#0b2f52]"></i>
                </div>
                <div class="grid gap-6">
                    @foreach ($statusCounts as $item)
                        @php $width = max(4, round(($item['count'] / $maxสถานะCount) * 100)); @endphp
                        <div class="group">
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <span @class([
                                    'rounded-lg px-3 py-0.5 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset',
                                    $statusClasses[$item['status']] ?? 'bg-slate-50 text-slate-500'
                                ])>{{ $item['label'] }}</span>
                                <span class="text-sm font-black text-[#0b2f52] italic">{{ number_format($item['count']) }}</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-[#0b2f52] transition-all duration-1000 ease-out group-hover:bg-[#fff9e8]0" style="width: {{ $width }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <!-- ความสำคัญ Analysis -->
            <section class="glass-card rounded-lg p-8 shadow-sm">
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-black text-[#0b2f52] uppercase tracking-tighter italic">Urgency Metrics</h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Business criticality levels</p>
                    </div>
                    <i data-lucide="bar-chart-2" class="h-6 w-6 text-rose-500"></i>
                </div>
                <div class="space-y-4">
                    @foreach ($priorityCounts as $item)
                        <div class="flex items-center justify-between p-4 rounded-lg border border-slate-100 bg-slate-50/50 hover:bg-white hover:shadow-sm transition-all group">
                            <span @class([
                                'rounded-lg px-2.5 py-1 text-[10px] font-black uppercase tracking-wider',
                                $priorityClasses[$item['value']] ?? 'bg-slate-100 text-slate-500'
                            ])>{{ $item['label'] }}</span>
                            <span class="text-lg font-black text-[#0b2f52] italic">{{ number_format($item['count']) }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <!-- Attention คิว Table -->
        <section class="glass-card overflow-hidden rounded-lg shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-8 py-5">
                <div>
                    <h3 class="text-lg font-black text-[#0b2f52] uppercase tracking-tighter italic text-rose-600">ความสำคัญ Attention คิว</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">เกินกำหนด cases and high-priority bottlenecks</p>
                </div>
                <i data-lucide="alert-octagon" class="h-6 w-6 text-rose-500 animate-pulse"></i>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px] text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                            <th class="px-8 py-5">เลขที่ใบงาน</th>
                            <th class="px-4 py-5">ลูกค้า / แรงงาน</th>
                            <th class="px-4 py-5">ขั้นตอนงาน</th>
                            <th class="px-4 py-5">Urgency</th>
                            <th class="px-4 py-5">Bottlenecks</th>
                            <th class="px-8 py-5 text-right">Administrative</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($attentionJobs as $job)
                            <tr class="group hover:bg-rose-50/30 transition-colors">
                                <td class="px-8 py-5">
                                    <p class="font-black text-[#0b2f52] uppercase tracking-tighter">{{ $job->job_number }}</p>
                                    <p @class([
                                        'text-[10px] font-black uppercase mt-1',
                                        'text-rose-600 animate-pulse' => $job->due_date && $job->due_date->isPast(),
                                        'text-slate-400' => !$job->due_date || !$job->due_date->isPast(),
                                    ])>กำหนด: {{ $job->due_date?->format('d M Y') ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-5 min-w-[220px]">
                                    <p class="font-bold text-[#0b2f52] truncate">{{ $job->employer?->company_name ?? '-' }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase mt-0.5 truncate">{{ $job->worker?->full_name_th ?: '-' }}</p>
                                </td>
                                <td class="px-4 py-5">
                                    <span @class([
                                        'rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset inline-block min-w-[90px] text-center',
                                        $statusClasses[$job->status] ?? 'bg-slate-50 text-slate-500'
                                    ])>{{ $statusLabels[$job->status] ?? $job->status }}</span>
                                </td>
                                <td class="px-4 py-5">
                                    <span @class([
                                        'rounded-lg px-2.5 py-1 text-[10px] font-black uppercase tracking-wider inline-block min-w-[80px] text-center',
                                        $priorityClasses[$job->priority] ?? 'bg-slate-100 text-slate-500'
                                    ])>{{ $priorityLabels[$job->priority] ?? $job->priority }}</span>
                                </td>
                                <td class="px-4 py-5">
                                    <div class="flex flex-wrap gap-2">
                                        @if($job->received_documents_count)
                                            <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase text-[#0b2f52] bg-[#fff9e8] px-2 py-0.5 rounded-lg ring-1 ring-[#c9a227]/30">
                                                <i data-lucide="file-check" class="h-3 w-3"></i> {{ $job->received_documents_count }} Wait ตรวจสอบ
                                            </span>
                                        @endif
                                        @if($job->pending_documents_count)
                                            <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase text-rose-600 bg-rose-50 px-2 py-0.5 rounded-lg ring-1 ring-rose-100">
                                                <i data-lucide="file-warning" class="h-3 w-3"></i> {{ $job->pending_documents_count }} Missing
                                            </span>
                                        @endif
                                        @if($job->pending_payments_count)
                                            <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase text-amber-600 bg-amber-50 px-2 py-0.5 rounded-lg ring-1 ring-amber-100">
                                                <i data-lucide="receipt" class="h-3 w-3"></i> Slip รอดำเนินการ
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <a href="{{ route('manager.job-orders.show', $job) }}"
                                        class="grid h-9 w-9 place-items-center rounded-lg bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-[#c9a227] hover:border-[#c9a227]/40 transition-all opacity-0 group-hover:opacity-100 ml-auto" title="Audit Case">
                                        <i data-lucide="chevron-right" class="h-5 w-5"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-20 text-center">
                                    <p class="text-slate-400 font-bold uppercase tracking-widest italic text-xs">ไม่ critical รายการ requiring immediate attention</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
