<x-filament-panels::page class="fi-dashboard-page">
    @php
        $kpis = $this->getKpis();
        $pipeline = $this->getPipeline();
        $summary = $this->getOperationsSummary();
        $urgentOrders = $this->getUrgentJobOrders();
        $expiringDocuments = $this->getExpiringDocuments();
        $topEmployers = $this->getTopEmployers();
        $activities = $this->getRecentActivities();
    @endphp

    <style>
        .hero-section {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card {
            background: white;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-color: #cbd5e1;
        }

        .alert-card {
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .alert-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            border-color: #cbd5e1;
        }

        .alert-critical {
            border-left: 4px solid #ef4444;
        }

        .alert-warning {
            border-left: 4px solid #f59e0b;
        }

        .alert-info {
            border-left: 4px solid #3b82f6;
        }

        .badge {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .badge-critical { background: #fef2f2; border-color: #fecaca; color: #dc2626; }
        .badge-warning { background: #fffbeb; border-color: #fde68a; color: #b45309; }
        .badge-info { background: #eff6ff; border-color: #bfdbfe; color: #2563eb; }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>

    <div class="space-y-8">
        <!-- Hero Section -->
        <section class="hero-section rounded-2xl p-8 lg:p-12">
            <div class="animate-fade-in">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 text-sm font-medium text-white mb-6">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    Document Expiry Alerts
                </div>
                <h1 class="text-4xl lg:text-5xl font-bold text-white mb-4">
                    เอกสารใกล้หมดอายุ
                </h1>
                <p class="text-lg text-slate-300 max-w-2xl leading-relaxed">
                    ตรวจสอบและจัดการเอกสารที่ใกล้หมดอายุ เพื่อป้องกันปัญหาที่อาจเกิดขึ้น
                </p>
                
                <div class="grid grid-cols-3 gap-4 mt-8">
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-5 border border-white/10">
                        <p class="text-xs font-medium text-slate-300 uppercase tracking-wide">หมดอายุแล้ว</p>
                        <p class="mt-2 text-3xl font-bold text-red-400">{{ \App\Models\WorkerDocument::query()->whereNotNull('expiry_date')->whereDate('expiry_date', '<', now())->count() }}</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-5 border border-white/10">
                        <p class="text-xs font-medium text-slate-300 uppercase tracking-wide">ใกล้หมดอายุ (30 วัน)</p>
                        <p class="mt-2 text-3xl font-bold text-amber-400">{{ \App\Models\WorkerDocument::query()->whereNotNull('expiry_date')->whereBetween('expiry_date', [now(), now()->addDays(30)])->count() }}</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-5 border border-white/10">
                        <p class="text-xs font-medium text-slate-300 uppercase tracking-wide">ใกล้หมดอายุ (45 วัน)</p>
                        <p class="mt-2 text-3xl font-bold text-blue-400">{{ \App\Models\WorkerDocument::query()->whereNotNull('expiry_date')->whereBetween('expiry_date', [now(), now()->addDays(45)])->count() }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Expiring Documents List -->
        <section class="card rounded-2xl p-6 animate-fade-in">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-xl font-semibold text-slate-900">รายการเอกสารใกล้หมดอายุ</h3>
                    <p class="text-sm text-slate-500 mt-1">แสดงเอกสารที่ใกล้หมดอายุภายใน 45 วัน</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center">
                    <x-filament::icon icon="heroicon-o-document-magnifying-glass" class="h-6 w-6 text-slate-600" />
                </div>
            </div>
            
            <div class="space-y-4">
                @php
                    $allExpiring = \App\Models\WorkerDocument::query()
                        ->with(['worker', 'documentMaster'])
                        ->whereNotNull('expiry_date')
                        ->whereDate('expiry_date', '<=', now()->addDays(45))
                        ->orderBy('expiry_date')
                        ->get();
                @endphp
                
                @forelse ($allExpiring as $document)
                    @php
                        $isExpired = $document->expiry_date?->isPast();
                        $isCritical = $document->expiry_date?->diffInDays(now()) <= 7;
                        $isWarning = $document->expiry_date?->diffInDays(now()) <= 30;
                    @endphp
                    
                    <div class="alert-card rounded-xl p-5 @if($isCritical) alert-critical @elseif($isWarning) alert-warning @else alert-info @endif">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="badge @if($isExpired) badge-critical @elseif($isCritical) badge-critical @elseif($isWarning) badge-warning @else badge-info @endif rounded-lg px-3 py-1 text-sm font-semibold">
                                        @if($isExpired)
                                            หมดอายุแล้ว
                                        @elseif($isCritical)
                                            ใกล้หมดอายุ ({{ $document->expiry_date?->diffInDays(now()) }} วัน)
                                        @elseif($isWarning)
                                            ใกล้หมดอายุ ({{ $document->expiry_date?->diffInDays(now()) }} วัน)
                                        @else
                                            ใกล้หมดอายุ ({{ $document->expiry_date?->diffInDays(now()) }} วัน)
                                        @endif
                                    </span>
                                </div>
                                <h4 class="text-lg font-semibold text-slate-900">{{ $document->worker?->full_name_th ?: $document->worker?->full_name_en }}</h4>
                                <p class="text-sm text-slate-600 mt-1">{{ $document->documentMaster?->name ?? 'เอกสาร' }}</p>
                                <p class="text-sm text-slate-500 mt-2">วันหมดอายุ: {{ $this->formatDate($document->expiry_date) }}</p>
                            </div>
                            <div class="flex flex-col gap-2">
                                @if($document->file_path)
                                    <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank"
                                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200 transition">
                                        <x-filament::icon icon="heroicon-o-eye" class="h-4 w-4" />
                                        ดูเอกสาร
                                    </a>
                                @endif
                                <a href="{{ route('staff.portal.workers.show', $document->worker_id) }}"
                                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition">
                                    <x-filament::icon icon="heroicon-o-pencil" class="h-4 w-4" />
                                    แก้ไข
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                            <x-filament::icon icon="heroicon-o-check-circle" class="h-8 w-8 text-slate-400" />
                        </div>
                        <h4 class="text-lg font-semibold text-slate-900">ไม่มีเอกสารใกล้หมดอายุ</h4>
                        <p class="text-sm text-slate-500 mt-2">ทุกเอกสารยังอยู่ในสถานะปกติ</p>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Quick Stats -->
        <section class="grid gap-5 md:grid-cols-3">
            <div class="card rounded-2xl p-6 animate-fade-in">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">เอกสารทั้งหมด</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format(\App\Models\WorkerDocument::count()) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center">
                        <x-filament::icon icon="heroicon-o-document" class="h-6 w-6 text-slate-600" />
                    </div>
                </div>
            </div>
            <div class="card rounded-2xl p-6 animate-fade-in">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">แรงงานทั้งหมด</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format(\App\Models\Worker::count()) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center">
                        <x-filament::icon icon="heroicon-o-users" class="h-6 w-6 text-slate-600" />
                    </div>
                </div>
            </div>
            <div class="card rounded-2xl p-6 animate-fade-in">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">นายจ้างทั้งหมด</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format(\App\Models\Employer::count()) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center">
                        <x-filament::icon icon="heroicon-o-building-office-2" class="h-6 w-6 text-slate-600" />
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page>
