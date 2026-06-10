@extends('layouts.staff-portal', ['title' => 'กระดานงาน', 'pageTitle' => 'กระดานติดตามสถานะใบงาน'])

@push('head')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
    .kanban-column { min-width: 320px; max-width: 320px; }
    .task-card { 
        transition: all 0.2s ease;
        border: 1px solid rgba(226, 232, 240, 0.5);
    }
    .task-card:hover { 
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.05);
        border-color: #3b82f6;
    }
    .status-panel {
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }
</style>
@endpush

@section('content')
    <div class="space-y-8 h-full flex flex-col">
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between shrink-0">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">กระดานติดตามงาน (Task Board)</h2>
                <p class="mt-1 text-slate-500 text-lg">ภาพรวมสถานะใบงานที่กำลังดำเนินการทั้งหมด</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('staff.portal.job-orders.index') }}" class="text-sm font-bold text-blue-600 hover:text-blue-700">ดูในรูปแบบตาราง →</a>
            </div>
        </header>

        <div class="flex-1 overflow-x-auto pb-8">
            @php
                $panelClasses = [
                    'pending' => 'bg-blue-50/80 border-blue-100',
                    'processing' => 'bg-indigo-50/80 border-indigo-100',
                    'waiting_document' => 'bg-amber-50/80 border-amber-100',
                    'approved' => 'bg-emerald-50/80 border-emerald-100',
                ];

                $countClasses = [
                    'pending' => 'bg-blue-100 text-blue-700',
                    'processing' => 'bg-indigo-100 text-indigo-700',
                    'waiting_document' => 'bg-amber-100 text-amber-700',
                    'approved' => 'bg-emerald-100 text-emerald-700',
                ];
            @endphp
            <div class="flex gap-6 h-full min-h-[600px]">
                @foreach($statusGroups as $status => $label)
                    <section class="kanban-column flex flex-col">
                        <div class="flex items-center justify-between mb-4 px-2">
                            <h3 class="font-black text-sm uppercase tracking-widest text-slate-400 flex items-center gap-2">
                                <span @class([
                                    'h-2 w-2 rounded-full',
                                    'bg-blue-500' => $status === 'pending',
                                    'bg-indigo-500' => $status === 'processing',
                                    'bg-amber-500' => $status === 'waiting_document',
                                    'bg-emerald-500' => $status === 'approved',
                                ])></span>
                                {{ $label }}
                            </h3>
                            <span @class([
                                'rounded-full px-2.5 py-0.5 text-xs font-bold',
                                'bg-slate-100 text-slate-500' => ! isset($countClasses[$status]),
                                $countClasses[$status] ?? '',
                            ])>
                                {{ isset($jobs[$status]) ? $jobs[$status]->count() : 0 }}
                            </span>
                        </div>

                        <div @class([
                            'status-panel flex-1 space-y-4 rounded-3xl p-2',
                            $panelClasses[$status] ?? 'bg-slate-50/70 border-slate-100',
                        ])>
                            @if(isset($jobs[$status]))
                                @foreach($jobs[$status] as $job)
                                    <a href="{{ route('staff.portal.job-orders.show', $job) }}" class="task-card block bg-white/90 backdrop-blur rounded-2xl p-5 shadow-sm">
                                        <div class="flex items-start justify-between gap-3">
                                            <span class="text-[10px] font-black uppercase text-blue-600 tracking-tighter">{{ $job->job_number }}</span>
                                            @if($job->priority === 'urgent')
                                                <span class="h-2 w-2 rounded-full bg-rose-500 animate-pulse"></span>
                                            @endif
                                        </div>
                                        <h4 class="mt-2 font-bold text-slate-900 text-sm leading-snug">{{ $job->service?->name }}</h4>
                                        <p class="mt-1 text-xs text-slate-500 truncate">{{ $job->employer?->company_name }}</p>
                                        
                                        <div class="mt-4 flex items-center justify-between border-t border-slate-50 pt-4">
                                            <div class="flex items-center gap-2">
                                                <div class="h-6 w-6 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-400">
                                                    {{ mb_substr($job->worker?->full_name_th ?: 'W', 0, 1) }}
                                                </div>
                                                <span class="text-[11px] font-bold text-slate-600 truncate max-w-[100px]">{{ $job->worker?->full_name_th }}</span>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-[9px] font-black text-slate-300 uppercase tracking-tighter">Due</p>
                                                <p class="text-[10px] font-bold text-slate-700">{{ $job->due_date?->format('d/m/Y') ?: '-' }}</p>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            @else
                                <div class="flex flex-col items-center justify-center py-12 text-slate-300">
                                    <i data-lucide="inbox" class="h-8 w-8 mb-2 opacity-20"></i>
                                    <p class="text-xs font-bold uppercase tracking-widest opacity-50">Empty</p>
                                </div>
                            @endif
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
@endsection
