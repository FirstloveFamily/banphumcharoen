<x-filament-panels::page>
    @php
        $expiringWorkers = $this->getExpiringWorkers();
        $stats = $this->getStats();
    @endphp

    <div class="space-y-6">
        <!-- Stats Overview -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <x-filament::card class="col-span-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Expired Workers</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($stats['expired']) }}</p>
            </x-filament::card>
            <x-filament::card class="col-span-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Expiring in 7 Days</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    {{ number_format($stats['expiring_7_days']) }}</p>
            </x-filament::card>
            <x-filament::card class="col-span-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Expiring in 30 Days</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    {{ number_format($stats['expiring_30_days']) }}</p>
            </x-filament::card>
        </div>

        <!-- Workers List -->
        <x-filament::card>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">รายการแรงงานใกล้หมดอายุ</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">แสดงแรงงานที่มีเอกสารใกล้หมดอายุภายใน 30
                        วัน</p>
                </div>
                <x-filament::icon icon="heroicon-o-users" class="h-8 w-8 text-gray-400 dark:text-gray-500" />
            </div>

            <div class="bg-white shadow-sm rounded-2xl p-4">
                <!-- Toolbar -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3">
                        <h3 class="text-lg font-semibold text-slate-900">แรงงานใกล้หมดอายุ</h3>
                        <p class="text-sm text-slate-500">ปรับปรุงเมื่อ: {{ now()->format('Y-m-d H:i') }}</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <input type="search" name="q" placeholder="ค้นหาชื่อแรงงาน หรือนายจ้าง..."
                                class="w-72 pl-10 pr-4 py-2 border border-gray-200 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent" />
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-4 w-4" />
                            </div>
                        </div>

                        <a href="#"
                            class="inline-flex items-center gap-2 px-3 py-2 bg-amber-600 text-white rounded-lg text-sm hover:bg-amber-700 transition-shadow shadow-sm">
                            <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-4 w-4" />
                            ส่งออก CSV
                        </a>
                    </div>
                </div>

                <!-- Legend -->
                <div class="flex flex-wrap items-center gap-4 mb-4 text-sm">
                    <div class="flex items-center gap-2 text-slate-600">
                        <span class="inline-block w-3 h-3 rounded-full bg-red-500 shadow-sm"></span>
                        <span class="font-medium">หมดอายุ</span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-600">
                        <span class="inline-block w-3 h-3 rounded-full bg-amber-400 shadow-sm"></span>
                        <span class="font-medium">วิกฤต (≤7 วัน)</span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-600">
                        <span class="inline-block w-3 h-3 rounded-full bg-blue-400 shadow-sm"></span>
                        <span class="font-medium">เตือน (≤30 วัน)</span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-600">
                        <span class="inline-block w-3 h-3 rounded-full bg-emerald-400 shadow-sm"></span>
                        <span class="font-medium">ปกติ</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    แรงงาน</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    นายจ้าง</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    พาสปอร์ต</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ใบอนุญาตทำงาน</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    วีซ่า</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    รายงาน 90 วัน</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    การจัดการ</th>
                            </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($expiringWorkers as $worker)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-4">
                                        <div class="text-sm font-medium text-slate-900">
                                            {{ $worker->full_name_th ?: $worker->full_name_en }}</div>
                                        <div class="text-xs text-slate-500">ID: {{ $worker->id }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-slate-700">
                                        {{ $worker->employer?->company_name ?? '-' }}</td>

                                    <td class="px-4 py-4 text-sm">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="inline-block w-2 h-2 rounded-full bg-{{ $this->getColorForDate($worker->passport_expiry) }}-500"></span>
                                            <span
                                                class="text-slate-700">{{ $this->formatDate($worker->passport_expiry) }}</span>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 text-sm">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="inline-block w-2 h-2 rounded-full bg-{{ $this->getColorForDate($worker->wp_expiry) }}-500"></span>
                                            <span
                                                class="text-slate-700">{{ $this->formatDate($worker->wp_expiry) }}</span>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 text-sm">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="inline-block w-2 h-2 rounded-full bg-{{ $this->getColorForDate($worker->visa_expiry) }}-500"></span>
                                            <span
                                                class="text-slate-700">{{ $this->formatDate($worker->visa_expiry) }}</span>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 text-sm">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="inline-block w-2 h-2 rounded-full bg-{{ $this->getColorForDate($worker->report_90_days_due) }}-500"></span>
                                            <span
                                                class="text-slate-700">{{ $this->formatDate($worker->report_90_days_due) }}</span>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('staff.portal.workers.show', $worker->id) }}"
                                                class="inline-flex items-center gap-2 rounded-md px-3 py-1 text-sm font-medium border border-gray-200 hover:bg-slate-50">
                                                <x-filament::icon icon="heroicon-o-eye"
                                                    class="h-4 w-4 text-slate-600" />
                                                ดู
                                            </a>
                                            <a href="#"
                                                class="inline-flex items-center gap-2 rounded-md px-3 py-1 text-sm font-medium bg-amber-100 text-amber-700 hover:bg-amber-200">
                                                แจ้งเตือน
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                                        <div class="py-6">
                                            <x-filament::icon icon="heroicon-o-check-circle"
                                                class="h-10 w-10 text-gray-400 mx-auto mb-3" />
                                            <h4 class="text-lg font-semibold text-slate-900">No Expiring
                                                Workers</h4>
                                            <p class="text-sm text-slate-500 mt-1">All documents are
                                                currently up-to-date.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </x-filament::card>
    </div>
</x-filament-panels::page>
