<x-filament-panels::page>
    @php
        $kpis = $this->getKpis();
        $expiringDocs = $this->getExpiringDocuments(50);
        $today = \Carbon\Carbon::now();

        $expiredCount = $expiringDocs
            ->filter(fn($d) => $d->expiry_date && \Carbon\Carbon::parse($d->expiry_date)->isPast())
            ->count();
        $within7 = $expiringDocs
            ->filter(
                fn($d) => $d->expiry_date &&
                    \Carbon\Carbon::parse($d->expiry_date)->between($today, $today->copy()->addDays(7)),
            )
            ->count();
        $within30 = $expiringDocs
            ->filter(
                fn($d) => $d->expiry_date &&
                    \Carbon\Carbon::parse($d->expiry_date)->between($today, $today->copy()->addDays(30)),
            )
            ->count();
        $totalDocs = max(1, $expiringDocs->count());
        $percentAtRisk = (int) round((($expiredCount + $within7 + $within30) / $totalDocs) * 100);
        $groups = $expiringDocs->groupBy(fn($d) => $d->documentMaster?->name ?? 'อื่นๆ');
    @endphp

    <div class="space-y-6">
        <!-- Header title -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">รายงานเอกสารใกล้ครบกำหนด</h1>
                <p class="text-sm text-slate-500">ตรวจสอบสถานะเอกสารแรงงานที่ต้องดำเนินการ - ข้อมูล ณ วันที่
                    {{ now()->format('d/m/Y') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-2 px-3 py-2 rounded-full bg-rose-50 text-rose-600">หมดอายุ:
                    {{ $expiredCount }}</span>
                <span class="inline-flex items-center gap-2 px-3 py-2 rounded-full bg-amber-50 text-amber-600">วิกฤต:
                    {{ $within7 }}</span>
                <span
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-full bg-yellow-50 text-yellow-700">ใกล้ครบกำหนด:
                    {{ $within30 }}</span>
                <span class="inline-flex items-center gap-2 px-3 py-2 rounded-full bg-green-50 text-emerald-700">ปลอดภัย:
                    {{ max(0, $totalDocs - ($expiredCount + $within7 + $within30)) }}</span>
            </div>
        </div>

        <!-- KPI cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($kpis as $kpi)
                <div class="rounded-2xl p-4 shadow-sm overflow-hidden relative"
                    style="background: linear-gradient(90deg, rgba(255,255,255,0.06), rgba(255,255,255,0.03)), var(--tw-gradient-stops);">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs text-white/80">{{ $kpi['label'] }}</p>
                            <p class="mt-2 text-2xl font-bold text-white">{{ number_format($kpi['value']) }}</p>
                        </div>
                        <div class="h-12 w-12 rounded-lg bg-white/10 flex items-center justify-center">
                            <x-filament::icon icon="heroicon-o-clock" class="h-6 w-6 text-white" />
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Progress bar -->
        <div class="bg-white rounded-2xl p-4 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="text-sm text-slate-700">สัดส่วนสถานะเอกสารทั้งหมด</div>
                <div class="text-sm text-slate-500">เอกสารทั้งหมด {{ $totalDocs }} รายการ</div>
            </div>
            <div class="w-full bg-gray-100 h-3 rounded-full overflow-hidden">
                <div class="h-3 bg-rose-500" style="width: {{ $percentAtRisk }}%"></div>
            </div>
        </div>

        <!-- Main content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 space-y-4">
                @foreach ($groups as $groupName => $items)
                    <div class="bg-white rounded-2xl p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-lg bg-slate-50 flex items-center justify-center">📄</div>
                                <div>
                                    <h4 class="font-semibold text-slate-900">{{ $groupName }}</h4>
                                    <div class="text-sm text-slate-500">{{ $items->count() }} รายการต้องดำเนินการ</div>
                                </div>
                            </div>
                            <div class="text-sm text-slate-500">{{ $this->formatDate($items->first()?->expiry_date) }}
                            </div>
                        </div>

                        <div class="overflow-x-auto mt-4">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs text-gray-500">#</th>
                                        <th class="px-4 py-2 text-left text-xs text-gray-500">ชื่อแรงงาน</th>
                                        <th class="px-4 py-2 text-left text-xs text-gray-500">สัญชาติ</th>
                                        <th class="px-4 py-2 text-left text-xs text-gray-500">นายจ้าง</th>
                                        <th class="px-4 py-2 text-left text-xs text-gray-500">เลขที่เอกสาร</th>
                                        <th class="px-4 py-2 text-left text-xs text-gray-500">วันหมดอายุ</th>
                                        <th class="px-4 py-2 text-left text-xs text-gray-500">จำนวนวัน</th>
                                        <th class="px-4 py-2 text-left text-xs text-gray-500">สถานะ</th>
                                        <th class="px-4 py-2"></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @foreach ($items as $i => $doc)
                                        @php $c = $this->getColorForDate($doc->expiry_date); @endphp
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-4 py-2 text-sm">{{ $i + 1 }}</td>
                                            <td class="px-4 py-2 text-sm">
                                                {{ $doc->worker?->full_name_th ?: $doc->worker?->full_name_en }}</td>
                                            <td class="px-4 py-2 text-sm">
                                                {{ $doc->worker?->nationality?->name_th ?? '-' }}</td>
                                            <td class="px-4 py-2 text-sm">
                                                {{ $doc->worker?->employer?->company_name ?? '-' }}</td>
                                            <td class="px-4 py-2 text-sm">{{ $doc->document_number ?? '-' }}</td>
                                            <td class="px-4 py-2 text-sm">{{ $this->formatDate($doc->expiry_date) }}
                                            </td>
                                            <td class="px-4 py-2 text-sm">
                                                @if ($doc->expiry_date)
                                                    {{ \Carbon\Carbon::parse($doc->expiry_date)->diffInDays(now()) }}
                                                    วัน
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-sm">
                                                <span class="inline-flex items-center gap-2">
                                                    <span
                                                        class="w-2 h-2 rounded-full bg-{{ $c }}-500"></span>
                                                    <span>
                                                        @if ($c == 'red')
                                                            หมดอายุ
                                                        @elseif($c == 'amber')
                                                            วิกฤต
                                                        @elseif($c == 'blue')
                                                            เตือน
                                                        @else
                                                            ปกติ
                                                        @endif
                                                    </span>
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-right text-sm">
                                                <a href="{{ route('staff.portal.workers.show', $doc->worker_id) }}"
                                                    class="text-indigo-600 hover:underline">ดู</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="space-y-4">
                <div class="bg-white rounded-2xl p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h4 class="font-semibold text-slate-900">การกระทำด่วน</h4>
                        <div class="text-sm text-slate-500">เครื่องมือ</div>
                    </div>
                    <div class="mt-3 flex flex-col gap-3">
                        <a href="#"
                            class="inline-flex items-center justify-center gap-2 px-3 py-2 bg-amber-600 text-white rounded-lg">ส่งออก
                            CSV</a>
                        <a href="#"
                            class="inline-flex items-center justify-center gap-2 px-3 py-2 bg-slate-100 rounded-lg">กรอง</a>
                        <a href="#"
                            class="inline-flex items-center justify-center gap-2 px-3 py-2 bg-red-50 text-red-700 rounded-lg">แจ้งเตือน</a>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-4 shadow-sm">
                    <h4 class="font-semibold text-slate-900">กิจกรรมล่าสุด</h4>
                    <div class="mt-3 space-y-3 text-sm text-slate-700">
                        @foreach ($this->getRecentActivities(8) as $act)
                            <div>
                                <div class="font-medium">{{ $act->user?->name ?? 'ระบบ' }}</div>
                                <div class="text-slate-500">{{ $act->description }}</div>
                                <div class="text-xs text-slate-400">{{ $act->created_at->diffForHumans() }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
