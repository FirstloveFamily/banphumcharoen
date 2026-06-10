@extends('layouts.staff-portal', ['title' => 'ใบส่งงาน', 'pageTitle' => 'ใบส่งงาน'])

@section('content')
    @php
        $statusLabels = [
            '' => 'ทุกสถานะ',
            'draft' => 'ร่าง',
            'submitted' => 'ส่งแล้ว',
            'approved' => 'อนุมัติ',
            'rejected' => 'ไม่อนุมัติ',
            'completed' => 'เสร็จสิ้น',
        ];
    @endphp

    <div class="space-y-8">
        <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.3em] text-slate-400">Delivery Workflow</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-[#0b2f52]">ใบส่งงาน</h1>
                <p class="mt-2 text-sm text-slate-500">รวมหลายใบงานไว้ในชุดเดียว พร้อมหลักฐานและไฟล์แนบ</p>
            </div>
            <a href="{{ route('staff.portal.delivery-sheets.create') }}"
                class="portal-btn-primary inline-flex h-12 items-center gap-2 px-5 text-sm font-bold">
                <i data-lucide="plus" class="h-4 w-4"></i>
                สร้างใบส่งงาน
            </a>
        </header>

        <div class="grid gap-4 md:grid-cols-4">
            <article class="portal-card p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">ทั้งหมด</p>
                <p class="mt-2 text-3xl font-black text-[#0b2f52]">{{ number_format($summary['total']) }}</p>
            </article>
            <article class="portal-card p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">ร่าง</p>
                <p class="mt-2 text-3xl font-black text-[#0b2f52]">{{ number_format($summary['draft']) }}</p>
            </article>
            <article class="portal-card p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">ส่งแล้ว</p>
                <p class="mt-2 text-3xl font-black text-[#0b2f52]">{{ number_format($summary['submitted']) }}</p>
            </article>
            <article class="portal-card p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">อนุมัติ</p>
                <p class="mt-2 text-3xl font-black text-[#0b2f52]">{{ number_format($summary['approved']) }}</p>
            </article>
        </div>

        <form method="GET" class="portal-card flex flex-col gap-4 p-5 lg:flex-row lg:items-end">
            <div class="grid flex-1 gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500">ค้นหา</label>
                    <input type="text" name="q" value="{{ $keyword }}" placeholder="เลขที่ใบส่งงาน หรือชื่อนายจ้าง"
                        class="portal-input h-12 w-full px-4 text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500">สถานะ</label>
                    <select name="status" class="portal-select h-12 w-full px-4 text-sm">
                        @foreach($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="portal-btn-primary inline-flex h-12 items-center gap-2 px-5 text-sm font-bold">
                    <i data-lucide="search" class="h-4 w-4"></i>
                    ค้นหา
                </button>
                <a href="{{ route('staff.portal.delivery-sheets.index') }}" class="portal-btn-secondary inline-flex h-12 items-center gap-2 px-5 text-sm font-bold">
                    ล้างตัวกรอง
                </a>
            </div>
        </form>

        <section class="portal-card overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-bold uppercase tracking-[0.25em] text-slate-500">รายการใบส่งงาน</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="portal-table-head text-left text-xs uppercase tracking-widest">
                        <tr>
                            <th class="px-6 py-4">เลขที่</th>
                            <th class="px-6 py-4">นายจ้าง</th>
                            <th class="px-6 py-4">วันที่</th>
                            <th class="px-6 py-4">สถานะ</th>
                            <th class="px-6 py-4 text-center">ใบงาน</th>
                            <th class="px-6 py-4 text-center">ไฟล์</th>
                            <th class="px-6 py-4 text-right">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($deliverySheets as $deliverySheet)
                            <tr class="portal-row-hover">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-[#0b2f52]">{{ $deliverySheet->sheet_number }}</div>
                                    <div class="mt-1 text-xs text-slate-400">โดย {{ $deliverySheet->createdBy?->name ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-slate-700">{{ $deliverySheet->employer?->company_name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $deliverySheet->sheet_date?->format('d/m/Y') ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset {{ $deliverySheet->status_badge_class }}">
                                        {{ $deliverySheet->status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-bold text-slate-700">{{ number_format($deliverySheet->items_count) }}</td>
                                <td class="px-6 py-4 text-center text-sm font-bold text-slate-700">{{ number_format($deliverySheet->attachments_count) }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('staff.portal.delivery-sheets.show', $deliverySheet) }}" class="portal-btn-secondary inline-flex h-10 items-center px-4 text-xs font-bold">
                                        เปิดดู
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center text-sm text-slate-500">
                                    ยังไม่มีใบส่งงานในระบบ
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-6 py-4">
                {{ $deliverySheets->links() }}
            </div>
        </section>
    </div>
@endsection
