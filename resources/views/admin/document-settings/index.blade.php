@extends('layouts.manager', ['title' => 'ตั้งค่าเอกสาร', 'pageTitle' => 'ตั้งค่าเอกสาร'])

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-[#0b2f52]">ตั้งค่าเอกสาร</p>
                <h2 class="mt-1 text-2xl font-bold text-[#0b2f52]">ตั้งค่าเอกสาร</h2>
                <p class="mt-1 text-sm text-slate-500">จัดการประเภทเอกสารและสถานะการใช้งาน</p>
            </div>
            <a href="{{ route('manager.document-settings.create') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-[#c9a227] px-4 text-sm font-semibold text-[#0b2f52] hover:bg-[#f3d06f]">
                <i data-lucide="plus" class="h-4 w-4"></i> เพิ่มการตั้งค่า
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">{{ session('error') }}</div>
        @endif

        <form action="{{ route('manager.document-settings.index') }}" method="GET" class="portal-card rounded-lg bg-white p-5">
            <div class="grid gap-4 md:grid-cols-[1fr_220px_auto] md:items-end">
                <div>
                    <label for="search" class="text-sm font-semibold text-[#0b2f52]">ค้นหา</label>
                    <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="ชื่อเอกสาร, รหัส, คำอธิบาย"
                        class="mt-2 h-11 w-full rounded-lg border border-slate-100 bg-white px-3 text-sm text-[#0b2f52] outline-none focus:border-[#c9a227] focus:ring-4 focus:ring-[#c9a227]/20">
                </div>
                <div>
                    <label for="is_active" class="text-sm font-semibold text-[#0b2f52]">สถานะ</label>
                    <select id="is_active" name="is_active" class="mt-2 h-11 w-full rounded-lg border border-slate-100 bg-white px-3 text-sm text-[#0b2f52] outline-none focus:border-[#c9a227] focus:ring-4 focus:ring-[#c9a227]/20">
                        <option value="">ทั้งหมด</option>
                        <option value="true" @selected(request('is_active') === 'true')>เปิดใช้งาน</option>
                        <option value="false" @selected(request('is_active') === 'false')>ปิดใช้งาน</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-[#123e68] px-4 text-sm font-semibold text-white hover:bg-[#0b2f52]"><i data-lucide="filter" class="h-4 w-4"></i>กรอง</button>
                    <a href="{{ route('manager.document-settings.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c9a227]/40 px-4 text-sm font-semibold text-[#0b2f52] hover:bg-[#fff9e8]">ล้าง</a>
                </div>
            </div>
        </form>

        <section class="portal-card overflow-hidden rounded-lg bg-white">
            <div class="flex items-center justify-between border-b border-slate-100 bg-[#fff9e8]/70 px-5 py-4">
                <div><h3 class="font-semibold text-[#0b2f52]">รายการตั้งค่าเอกสาร</h3><p class="mt-1 text-sm text-slate-500">แสดง {{ number_format($documentSettings->firstItem() ?? 0) }}-{{ number_format($documentSettings->lastItem() ?? 0) }} จาก {{ number_format($documentSettings->total()) }} รายการ</p></div>
                <i data-lucide="file-cog" class="h-5 w-5 text-[#c9a227]"></i>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[960px] text-left text-sm">
                    <thead class="border-b border-slate-100 bg-white text-xs uppercase tracking-wide text-slate-500">
                        <tr><th class="px-5 py-3 font-semibold">ชื่อเอกสาร</th><th class="px-5 py-3 font-semibold">รหัส</th><th class="px-5 py-3 font-semibold">การใช้งาน</th><th class="px-5 py-3 font-semibold">สถานะ</th><th class="px-5 py-3 text-right font-semibold">ดำเนินการ</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($documentSettings as $setting)
                            <tr class="hover:bg-[#fff9e8]/40">
                                <td class="px-5 py-4"><p class="font-semibold text-[#0b2f52]">{{ $setting->name }}</p><p class="mt-1 max-w-lg truncate text-slate-500">{{ Str::limit($setting->description ?: '-', 100) }}</p></td>
                                <td class="px-5 py-4 font-semibold text-slate-600">{{ $setting->code }}</td>
                                <td class="px-5 py-4 text-slate-600">บริการ {{ number_format($setting->service_checklists_count) }} · เอกสาร {{ number_format($setting->worker_documents_count) }} · Checklist {{ number_format($setting->job_order_checklists_count) }}</td>
                                <td class="px-5 py-4"><span class="rounded-lg px-3 py-1 text-xs font-semibold {{ $setting->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">{{ $setting->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}</span></td>
                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex gap-2">
                                        <a href="{{ route('manager.document-settings.edit', $setting) }}" class="inline-flex h-9 items-center gap-2 rounded-lg border border-[#c9a227]/40 px-3 text-sm font-semibold text-[#0b2f52] hover:bg-[#fff9e8]"><i data-lucide="edit" class="h-4 w-4"></i>แก้ไข</a>
                                        <form action="{{ route('manager.document-settings.destroy', $setting) }}" method="POST" onsubmit="return confirm('คุณแน่ใจหรือ?');">@csrf @method('DELETE')<button class="inline-flex h-9 items-center gap-2 rounded-lg border border-rose-200 px-3 text-sm font-semibold text-rose-700 hover:bg-rose-50"><i data-lucide="trash-2" class="h-4 w-4"></i>ลบ</button></form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-14 text-center text-sm text-slate-500">ไม่พบข้อมูลการตั้งค่าเอกสาร</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($documentSettings->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $documentSettings->links() }}</div>@endif
        </section>
    </div>
@endsection
