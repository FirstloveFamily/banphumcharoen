@extends('layouts.staff-portal', ['title' => 'ปฏิทินงาน', 'pageTitle' => 'ปฏิทินงานปฏิบัติการ'])

@push('head')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        .fc { font-family: 'Prompt', sans-serif; height: 800px; }
        .fc .fc-toolbar-title { font-size: 1.5rem; font-weight: 900; color: #0f172a; }
        .fc .fc-button-primary { background-color: #0f172a; border: none; font-weight: 700; border-radius: 0.75rem; }
        .fc .fc-button-primary:hover { background-color: #1e293b; }
        .fc .fc-button-active { background-color: #3b82f6 !important; }
        
        /* Event Styles - High Contrast */
        .fc-event { border: none !important; padding: 3px 6px; border-radius: 6px; font-weight: 800; font-size: 0.75rem; margin-bottom: 2px !important; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        
        .event-passport { background-color: #f59e0b !important; color: #ffffff !important; border-left: 4px solid #b45309 !important; }
        .event-visa { background-color: #8b5cf6 !important; color: #ffffff !important; border-left: 4px solid #6d28d9 !important; }
        .event-wp { background-color: #10b981 !important; color: #ffffff !important; border-left: 4px solid #047857 !important; }
        .event-job { background-color: #3b82f6 !important; color: #ffffff !important; border-left: 4px solid #1d4ed8 !important; }
        .event-overdue { background-color: #ef4444 !important; color: #ffffff !important; border-left: 4px solid #991b1b !important; animation: pulse-red 2s infinite; }

        @keyframes pulse-red {
            0% { opacity: 1; }
            50% { opacity: 0.8; }
            100% { opacity: 1; }
        }

        .fc-daygrid-day-number { font-weight: 800; color: #64748b; padding: 8px !important; font-size: 0.9rem; }
        .fc-col-header-cell-cushion { font-weight: 900; color: #475569; text-transform: uppercase; font-size: 0.8rem; padding: 12px 0 !important; }
        .fc-day-today { background-color: #f8fafc !important; }
    </style>
@endpush

@section('content')
    <div class="space-y-8">
        <header class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">ปฏิทินปฏิบัติการ</h2>
                <p class="mt-1 text-slate-500 text-lg font-medium">จัดการตารางเวลาและวันสำคัญของคุณอย่างมืออาชีพ</p>
            </div>
            <!-- Interactive Legend -->
            <div class="flex flex-wrap items-center gap-3 bg-white p-3 rounded-2xl border border-slate-100 shadow-sm">
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-50 text-amber-700 text-[10px] font-black uppercase">
                    <span class="h-2 w-2 rounded-full bg-amber-500"></span> Passport
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-purple-50 text-purple-700 text-[10px] font-black uppercase">
                    <span class="h-2 w-2 rounded-full bg-purple-500"></span> Visa
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span> WP
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 text-[10px] font-black uppercase">
                    <span class="h-2 w-2 rounded-full bg-blue-500"></span> Job Order
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 text-[10px] font-black uppercase">
                    <span class="h-2 w-2 rounded-full bg-rose-500"></span> Overdue
                </div>
            </div>
        </header>

        <section class="glass-card rounded-[2rem] p-8 shadow-xl border-slate-200/60 overflow-hidden">
            <div id='calendar'></div>
        </section>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'th',
            firstDay: 1, // Start Monday
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            buttonText: {
                today: 'วันนี้',
                month: 'เดือน',
                week: 'สัปดาห์',
                list: 'รายการ'
            },
            events: [
                @foreach($workers as $worker)
                    @if($worker->passport_expiry)
                    {
                        title: 'PP: {{ $worker->full_name_th ?: $worker->full_name_en }}',
                        start: '{{ $worker->passport_expiry->format('Y-m-d') }}',
                        className: '{{ $worker->passport_expiry->isPast() ? 'event-overdue' : 'event-passport' }}',
                        url: '{{ route('staff.portal.workers.show', $worker) }}'
                    },
                    @endif
                    @if($worker->visa_expiry)
                    {
                        title: 'VISA: {{ $worker->full_name_th ?: $worker->full_name_en }}',
                        start: '{{ $worker->visa_expiry->format('Y-m-d') }}',
                        className: '{{ $worker->visa_expiry->isPast() ? 'event-overdue' : 'event-visa' }}',
                        url: '{{ route('staff.portal.workers.show', $worker) }}'
                    },
                    @endif
                    @if($worker->wp_expiry)
                    {
                        title: 'WP: {{ $worker->full_name_th ?: $worker->full_name_en }}',
                        start: '{{ $worker->wp_expiry->format('Y-m-d') }}',
                        className: '{{ $worker->wp_expiry->isPast() ? 'event-overdue' : 'event-wp' }}',
                        url: '{{ route('staff.portal.workers.show', $worker) }}'
                    },
                    @endif
                    @if($worker->report_90_days_due)
                    {
                        title: '90D: {{ $worker->full_name_th ?: $worker->full_name_en }}',
                        start: '{{ $worker->report_90_days_due->format('Y-m-d') }}',
                        className: '{{ $worker->report_90_days_due->isPast() ? 'event-overdue' : 'event-visa' }}',
                        url: '{{ route('staff.portal.workers.show', $worker) }}'
                    },
                    @endif
                @endforeach

                @foreach($jobs as $job)
                {
                    title: 'JOB: {{ $job->job_number }}',
                    start: '{{ $job->due_date?->format('Y-m-d') }}',
                    className: '{{ ($job->due_date && $job->due_date->isPast() && $job->status !== 'completed') ? 'event-overdue' : 'event-job' }}',
                    url: '{{ route('staff.portal.job-orders.show', $job) }}'
                },
                @endforeach
            ],
            eventClick: function(info) {
                if (info.event.url) {
                    window.open(info.event.url, "_blank");
                    info.jsEvent.preventDefault();
                }
            },
            dayMaxEvents: true, // allow "more" link when too many events
        });
        calendar.render();
    });
</script>
@endpush
