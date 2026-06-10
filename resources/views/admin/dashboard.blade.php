@extends('layouts.app', ['title' => 'Admin Dashboard'])

@section('content')
    <div class="max-w-7xl mx-auto p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Admin Dashboard</h1>
                <p class="text-sm text-slate-600 mt-1">สรุปภาพรวมระบบและลิงก์ด่วน</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('staff.portal.employers.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-700 text-white px-4 py-2 text-sm font-semibold hover:bg-blue-800">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    สร้างนายจ้าง
                </a>
                <a href="{{ route('staff.portal.workers.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 text-white px-4 py-2 text-sm font-semibold hover:bg-emerald-700">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    สร้างแรงงาน
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <p class="text-xs text-slate-500">นายจ้างทั้งหมด</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($totalEmployers) }}</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <p class="text-xs text-slate-500">แรงงานทั้งหมด</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($totalWorkers) }}</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <p class="text-xs text-slate-500">ใบงานที่ยังเปิดอยู่</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($openJobs) }}</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <p class="text-xs text-slate-500">รออนุมัติการชำระเงิน</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($pendingPayments) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-4">
                <section class="rounded-2xl bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-slate-900">ใบงานล่าสุด</h3>
                    <div class="mt-4 divide-y">
                        @forelse($recentJobs as $job)
                            <div class="py-3 flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $job->job_number }} —
                                        {{ $job->service?->name ?? '-' }}</p>
                                    <p class="text-xs text-slate-600">{{ $job->employer?->company_name ?: '-' }} •
                                        {{ $job->worker?->full_name_th ?: '-' }}</p>
                                </div>
                                <div class="text-sm text-slate-500">{{ $job->updated_at?->format('d/m H:i') }}</div>
                            </div>
                        @empty
                            <p class="py-4 text-sm text-slate-600">ยังไม่มีใบงาน</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-2xl bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-slate-900">แรงงานล่าสุด</h3>
                    <div class="mt-4 divide-y">
                        @forelse($recentWorkers as $w)
                            <div class="py-3 flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $w->full_name_th ?: $w->full_name_en }}</p>
                                    <p class="text-xs text-slate-600">{{ $w->employer?->company_name ?: '-' }}</p>
                                </div>
                                <div class="text-sm text-slate-500">{{ $w->updated_at?->format('d/m H:i') }}</div>
                            </div>
                        @empty
                            <p class="py-4 text-sm text-slate-600">ยังไม่มีข้อมูลแรงงาน</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <aside class="space-y-4">
                <section class="rounded-2xl bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-slate-900">Activity</h3>
                    <p class="mt-3 text-sm text-slate-600">ไม่มีกิจกรรมล่าสุด</p>
                </section>

                <section class="rounded-2xl bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-slate-900">Shortcuts</h3>
                    <div class="mt-3 flex flex-col gap-2">
                        <a href="{{ route('manager.dashboard') }}" class="text-sm text-blue-700 hover:underline">Manager
                            Dashboard</a>
                        <a href="{{ route('staff.portal.employers.index') }}"
                            class="text-sm text-blue-700 hover:underline">จัดการนายจ้าง</a>
                        <a href="{{ route('staff.portal.workers.index') }}"
                            class="text-sm text-blue-700 hover:underline">จัดการแรงงาน</a>
                        <a href="{{ route('manager.reports.workers') }}"
                            class="text-sm text-blue-700 hover:underline">รายงานแรงงาน</a>
                        <a href="{{ route('staff.portal.job-orders.index') }}"
                            class="text-sm text-blue-700 hover:underline">จัดการใบงาน</a>
                        {{-- <a href="{{ route('admin-manage.permissions.index') }}"
                            class="text-sm text-blue-700 hover:underline">จัดการสิทธิ์</a>
                        <a href="{{ route('admin-manage.roles.index') }}"
                            class="text-sm text-blue-700 hover:underline">จัดการบทบาท</a> --}}
                    </div>
                </section>
            </aside>
        </div>
    </div>
@endsection
