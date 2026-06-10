<x-filament-panels::page class="fi-dashboard-page">
    <style>
        .staff-dashboard-shell {
            margin: -1.5rem;
            padding: 1.5rem;
            background:
                linear-gradient(180deg, #eef4f8 0, #f8fafc 260px, #f8fafc 100%);
            color: #0f172a;
        }

        .staff-command {
            border: 1px solid rgba(15, 23, 42, .08);
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 14px 40px rgba(15, 23, 42, .08);
        }

        .staff-command-strip {
            border-radius: 12px;
            background: #0f172a;
            color: #ffffff;
        }

        .staff-chip {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            border-radius: 999px;
            border: 1px solid #dbe5ee;
            background: #f8fafc;
            padding: .4rem .7rem;
            color: #475569;
            font-size: .75rem;
            font-weight: 600;
        }

        .staff-stat-card {
            position: relative;
            overflow: hidden;
            border-radius: 14px;
            border: 1px solid #dbe5ee;
            background: #ffffff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .055);
        }

        .staff-stat-card:before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: #64748b;
        }

        .staff-stat-amber:before { background: #d97706; }
        .staff-stat-sky:before { background: #0284c7; }
        .staff-stat-emerald:before { background: #059669; }
        .staff-stat-rose:before { background: #e11d48; }

        .staff-fill-amber { background: #d97706; }
        .staff-fill-sky { background: #0284c7; }
        .staff-fill-emerald { background: #059669; }
        .staff-fill-rose { background: #e11d48; }

        .staff-stat-meta {
            border-top: 1px solid #eef2f7;
            background: #f8fafc;
        }

        .staff-icon-box {
            border-radius: 10px;
            background: #ffffff;
            padding: .5rem;
            border: 1px solid #e2e8f0;
        }

        .staff-icon-amber { color: #d97706; }
        .staff-icon-sky { color: #0284c7; }
        .staff-icon-emerald { color: #059669; }
        .staff-icon-rose { color: #e11d48; }

        .staff-panel {
            overflow: hidden;
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #dbe5ee;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
        }

        .staff-panel-header {
            background: #ffffff;
            border-bottom: 1px solid #e5edf4;
        }

        .staff-quick-link {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .03);
        }

        .staff-quick-link:hover {
            background: #f8fafc;
            border-color: #94a3b8;
            transform: translateY(-1px);
        }

        .staff-table {
            border-collapse: separate;
            border-spacing: 0 .55rem;
        }

        .staff-table tbody tr {
            background: #ffffff;
            box-shadow: 0 1px 0 #e5edf4;
        }

        .staff-table tbody td {
            border-top: 1px solid #e5edf4;
            border-bottom: 1px solid #e5edf4;
        }

        .staff-table tbody td:first-child {
            border-left: 1px solid #e5edf4;
            border-radius: 10px 0 0 10px;
        }

        .staff-table tbody td:last-child {
            border-right: 1px solid #e5edf4;
            border-radius: 0 10px 10px 0;
        }

        .staff-avatar {
            display: grid;
            width: 2rem;
            height: 2rem;
            place-items: center;
            border-radius: 999px;
            background: #e0f2fe;
            color: #075985;
            font-size: .75rem;
            font-weight: 700;
        }

        .staff-review-item {
            border-left: 3px solid #cbd5e1;
            border-radius: 10px;
            background: #f8fafc;
            padding: .75rem;
        }

        .staff-status-danger { background: #fff1f2; color: #be123c; }
        .staff-status-warning { background: #fffbeb; color: #b45309; }
        .staff-status-info { background: #eff6ff; color: #1d4ed8; }
        .staff-status-gray { background: #f1f5f9; color: #475569; }

        .dark .staff-dashboard-shell { background: #030712; color: #f8fafc; }
        .dark .staff-command,
        .dark .staff-stat-card { background: #111827; border-color: rgba(255, 255, 255, .1); }
        .dark .staff-chip,
        .dark .staff-stat-meta,
        .dark .staff-review-item { background: rgba(255, 255, 255, .04); border-color: rgba(255, 255, 255, .1); }
        .dark .staff-panel { background: #111827; border-color: rgba(255, 255, 255, .1); }
        .dark .staff-panel-header { background: #111827; border-color: rgba(255, 255, 255, .1); }
        .dark .staff-icon-box { background: #1f2937; }
        .dark .staff-quick-link { background: rgba(255, 255, 255, .04); border-color: rgba(255, 255, 255, .1); }
        .dark .staff-quick-link:hover { background: rgba(59, 130, 246, .12); }
        .dark .staff-table tbody tr { background: #111827; box-shadow: none; }
        .dark .staff-table tbody td { border-color: rgba(255, 255, 255, .1); }
    </style>

    @php
        $stats = $this->getStats();
        $expiringItems = $this->getExpiringItems();
        $pendingReviews = $this->getPendingDocumentReviews();
        $quickLinks = $this->getQuickLinks();
    @endphp

    <div class="staff-dashboard-shell space-y-6">
        <section class="staff-command p-5">
            <div class="grid gap-5 xl:grid-cols-[1fr_360px]">
                <div class="flex flex-col justify-between gap-5">
                    <div>
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <span class="staff-chip">Staff workspace</span>
                            <span class="staff-chip">Focus 45 days</span>
                        </div>
                        <h2 class="text-2xl font-semibold text-gray-950 dark:text-white">งานที่ต้องติดตามวันนี้</h2>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-gray-600 dark:text-gray-400">ติดตาม Passport, Visa, Work Permit, 90 Days Report, เอกสารแนบ และงานตรวจสอบที่ต้องปิดให้ทันเวลา</p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach ($quickLinks as $link)
                            <a href="{{ $link['url'] }}" class="staff-quick-link flex items-center gap-3 rounded-lg p-3 transition">
                                <span class="staff-icon-box">
                                    <x-filament::icon :icon="$link['icon']" class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-sm font-semibold text-gray-950 dark:text-white">{{ $link['label'] }}</span>
                                    <span class="block truncate text-xs text-gray-500 dark:text-gray-400">{{ $link['description'] }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="staff-command-strip p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-300">Operations snapshot</p>
                    <div class="mt-5 grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-3xl font-semibold">{{ number_format($stats[0]['value'] + $stats[1]['value']) }}</p>
                            <p class="mt-1 text-xs text-slate-300">expiry alerts</p>
                        </div>
                        <div>
                            <p class="text-3xl font-semibold">{{ number_format($stats[2]['value']) }}</p>
                            <p class="mt-1 text-xs text-slate-300">open jobs</p>
                        </div>
                    </div>
                    <div class="mt-5 rounded-lg bg-white/10 p-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-300">Last updated</span>
                            <span class="font-semibold text-white">{{ now()->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as $stat)
                <div class="staff-stat-card staff-stat-{{ $stat['color'] }}">
                    <div class="flex items-start justify-between gap-4 p-5">
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $stat['label'] }}</p>
                            <p class="mt-2 text-3xl font-semibold text-gray-950 dark:text-white">{{ number_format($stat['value']) }}</p>
                        </div>
                        <div class="staff-icon-box staff-icon-{{ $stat['color'] }}">
                            <x-filament::icon :icon="$stat['icon']" class="h-6 w-6" />
                        </div>
                    </div>
                    <div class="staff-stat-meta px-5 py-3 text-xs font-medium text-gray-600 dark:text-gray-400">{{ $stat['meta'] }}</div>
                </div>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[1fr_360px]">
            <div class="staff-panel">
                <div class="staff-panel-header flex items-center justify-between gap-4 px-6 py-5">
                    <div>
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">Priority expiry queue</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">รวม Passport, Work Permit, Visa, 90 Days Report และเอกสารแนบ</p>
                    </div>
                    <x-filament::icon icon="heroicon-o-calendar-days" class="h-6 w-6 text-gray-400" />
                </div>

                <div class="overflow-x-auto px-6 pb-6 pt-4">
                    <table class="staff-table w-full min-w-[760px] text-left text-sm">
                        <thead class="text-xs uppercase text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="px-4 pb-2 font-medium">แรงงาน</th>
                                <th class="px-4 pb-2 font-medium">นายจ้าง</th>
                                <th class="px-4 pb-2 font-medium">เอกสาร</th>
                                <th class="px-4 pb-2 font-medium">วันหมดอายุ</th>
                                <th class="px-4 pb-2 text-right font-medium">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($expiringItems as $item)
                                @php
                                    $color = $this->getUrgencyColor($item['expiry_date']);
                                @endphp
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <span class="staff-avatar">{{ mb_substr($item['worker'], 0, 1) }}</span>
                                            <span class="font-semibold text-gray-950 dark:text-white">{{ $item['worker'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $item['employer'] }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $item['document'] }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">{{ $item['expiry_date']->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="staff-status-{{ $color }} rounded-md px-2 py-1 text-xs font-semibold">
                                            {{ $this->getDaysLabel($item['expiry_date']) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-500 dark:text-gray-400">ยังไม่มีเอกสารใกล้หมดอายุ</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-6">
                <div class="staff-panel p-6">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Workload balance</h3>
                    <div class="mt-4 space-y-4">
                        @foreach ($stats as $stat)
                            @php
                                $width = min(100, max(8, (int) $stat['value'] * 8));
                            @endphp
                            <div>
                                <div class="mb-2 flex items-center justify-between text-sm">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $stat['label'] }}</span>
                                    <span class="text-gray-500 dark:text-gray-400">{{ number_format($stat['value']) }}</span>
                                </div>
                                <div class="h-2 rounded-full bg-gray-100 dark:bg-white/10">
                                    <div class="staff-fill-{{ $stat['color'] }} h-2 rounded-full" style="width: {{ $width }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="staff-panel p-6">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">เอกสารรอตรวจ</h3>
                    <div class="mt-4 space-y-4">
                        @forelse ($pendingReviews as $review)
                            <div class="staff-review-item flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-gray-950 dark:text-white">{{ $review->documentMaster?->name ?? 'เอกสาร' }}</p>
                                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                                        {{ $review->jobOrder?->job_number ?? '-' }} · {{ $review->jobOrder?->worker?->full_name_th ?: $review->jobOrder?->worker?->full_name_en }}
                                    </p>
                                </div>
                                <span class="staff-status-gray shrink-0 rounded-md px-2 py-1 text-xs font-semibold">
                                    {{ str($review->status)->replace('_', ' ')->title() }}
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">ไม่มีเอกสารรอตรวจ</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page>
