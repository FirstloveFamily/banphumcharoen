@extends('layouts.staff-portal', ['title' => 'ศูนย์รวมรายงาน', 'pageTitle' => 'สถิติและรายงานสรุป'])

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">ศูนย์รวมรายงาน (Analytics)</h2>
                <p class="mt-1 text-slate-500 text-lg">สรุปผลการดำเนินงานและสถิติภาพรวมของระบบ</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="window.print()" class="flex items-center gap-2 rounded-2xl bg-white border border-slate-200 px-5 py-2.5 text-xs font-bold text-slate-600 hover:text-blue-600 transition-all shadow-sm">
                    <i data-lucide="printer" class="h-4 w-4"></i>
                    พิมพ์รายงาน
                </button>
            </div>
        </header>

        <!-- Top Stats: Financials -->
        <section class="grid gap-6 md:grid-cols-3">
            <article class="glass-card rounded-3xl p-8 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-blue-50 opacity-50 transition-transform group-hover:scale-110"></div>
                <div class="relative">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">รายได้รวมทั้งหมด (Service Fees)</p>
                    <h3 class="text-3xl font-black text-slate-900 mt-3">฿{{ number_format($totalServiceFee, 2) }}</h3>
                </div>
            </article>
            <article class="glass-card rounded-3xl p-8 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-emerald-50 opacity-50 transition-transform group-hover:scale-110"></div>
                <div class="relative">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">ยอดชำระแล้ว (Received)</p>
                    <h3 class="text-3xl font-black text-emerald-600 mt-3">฿{{ number_format($totalPaid, 2) }}</h3>
                </div>
            </article>
            <article class="glass-card rounded-3xl p-8 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-rose-50 opacity-50 transition-transform group-hover:scale-110"></div>
                <div class="relative">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">ยอดค้างชำระรวม (Outstanding)</p>
                    <h3 class="text-3xl font-black text-rose-600 mt-3">฿{{ number_format($totalRemaining, 2) }}</h3>
                </div>
            </article>
        </section>

        <div class="grid gap-8 lg:grid-cols-2">
            <!-- Job Status Distribution Chart -->
            <section class="glass-card rounded-3xl p-8 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 mb-8 flex items-center gap-2">
                    <i data-lucide="pie-chart" class="h-5 w-5 text-blue-600"></i>
                    สัดส่วนสถานะใบงาน
                </h3>
                <div class="h-[300px] flex items-center justify-center">
                    <canvas id="jobStatusChart"></canvas>
                </div>
            </section>

            <!-- Nationality Distribution Chart -->
            <section class="glass-card rounded-3xl p-8 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 mb-8 flex items-center gap-2">
                    <i data-lucide="users" class="h-5 w-5 text-indigo-600"></i>
                    จำนวนแรงงานแยกตามสัญชาติ
                </h3>
                <div class="h-[300px] flex items-center justify-center">
                    <canvas id="nationalityChart"></canvas>
                </div>
            </section>
        </div>

        <!-- Aging Receivables Table -->
        <section class="glass-card overflow-hidden rounded-3xl shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-8 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-600 text-white shadow-lg">
                        <i data-lucide="alert-circle" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">5 อันดับนายจ้างยอดค้างสูงสุด</h3>
                        <p class="text-sm text-slate-500">รายการที่ต้องติดตามการชำระเงินเร่งด่วน</p>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 text-xs font-bold uppercase tracking-wider text-slate-400">
                            <th class="px-8 py-4">นายจ้าง / รหัส</th>
                            <th class="px-4 py-4 text-center">ใบงานที่ค้าง</th>
                            <th class="px-8 py-4 text-right">ยอดค้างชำระรวม</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($agingReceivables as $receivable)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-5">
                                    <p class="font-bold text-slate-900">{{ $receivable['name'] }}</p>
                                    <p class="text-xs font-mono text-slate-400">{{ $receivable['code'] }}</p>
                                </td>
                                <td class="px-4 py-5 text-center">
                                    <span class="inline-flex items-center justify-center rounded-lg bg-slate-100 px-3 py-1 font-bold text-slate-600">
                                        {{ $receivable['jobs_count'] }} ใบงาน
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right font-black text-rose-600 text-lg">
                                    ฿{{ number_format($receivable['remaining'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
<script>
    // Job Status Chart
    new Chart(document.getElementById('jobStatusChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($jobStats->toArray())) !!}.map(s => s.charAt(0).toUpperCase() + s.slice(1).replace('_', ' ')),
            datasets: [{
                data: {!! json_encode(array_values($jobStats->toArray())) !!},
                backgroundColor: ['#3b82f6', '#6366f1', '#f59e0b', '#10b981', '#0f172a', '#94a3b8', '#ef4444'],
                borderWidth: 0,
                spacing: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { family: 'Prompt', weight: 'bold' } } }
            },
            cutout: '70%'
        }
    });

    // Nationality Chart
    new Chart(document.getElementById('nationalityChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($nationalities->pluck('name_th')) !!},
            datasets: [{
                label: 'จำนวนแรงงาน',
                data: {!! json_encode($nationalities->pluck('workers_count')) !!},
                backgroundColor: '#6366f1',
                borderRadius: 12,
                barThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { display: false } },
                x: { grid: { display: false }, ticks: { font: { family: 'Prompt', weight: 'bold' } } }
            }
        }
    });
</script>
@endpush
