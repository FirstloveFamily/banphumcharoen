@extends('layouts.manager', ['title' => 'จัดการเอกสาร', 'pageTitle' => 'จัดการเอกสาร'])

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-[#0b2f52]">เอกสาร</p>
                <h2 class="mt-1 text-2xl font-bold text-[#0b2f52]">จัดการเอกสาร</h2>
                <p class="mt-1 text-sm text-slate-500">ค้นหาและจัดการไฟล์เอกสารแรงงาน</p>
            </div>
            <a href="{{ route('manager.documents.create') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-[#c9a227] px-4 text-sm font-semibold text-[#0b2f52] hover:bg-[#f3d06f]">
                <i data-lucide="plus" class="h-4 w-4"></i> เพิ่มเอกสาร
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>
        @endif

        <form action="{{ route('manager.documents.index') }}" method="GET" class="portal-card rounded-lg bg-white p-5">
            <div class="grid gap-4 xl:grid-cols-[1fr_260px_220px_auto] xl:items-end">
                <div>
                    <label for="search" class="text-sm font-semibold text-[#0b2f52]">ค้นหา</label>
                    <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="ชื่อแรงงาน, เอกสาร, พาสปอร์ต"
                        class="mt-2 h-11 w-full rounded-lg border border-slate-100 bg-white px-3 text-sm text-[#0b2f52] outline-none focus:border-[#c9a227] focus:ring-4 focus:ring-[#c9a227]/20">
                </div>
                <div>
                    <label for="worker_id" class="text-sm font-semibold text-[#0b2f52]">แรงงาน</label>
                    <select id="worker_id" name="worker_id" class="mt-2 h-11 w-full rounded-lg border border-slate-100 bg-white px-3 text-sm text-[#0b2f52] outline-none focus:border-[#c9a227] focus:ring-4 focus:ring-[#c9a227]/20">
                        <option value="">ทั้งหมด</option>
                        @foreach ($workers as $worker)
                            <option value="{{ $worker->id }}" @selected(request('worker_id') == $worker->id)>{{ $worker->full_name_th ?: $worker->full_name_en }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="document_master_id" class="text-sm font-semibold text-[#0b2f52]">ประเภทเอกสาร</label>
                    <select id="document_master_id" name="document_master_id" class="mt-2 h-11 w-full rounded-lg border border-slate-100 bg-white px-3 text-sm text-[#0b2f52] outline-none focus:border-[#c9a227] focus:ring-4 focus:ring-[#c9a227]/20">
                        <option value="">ทั้งหมด</option>
                        @foreach ($documentMasters as $id => $name)
                            <option value="{{ $id }}" @selected(request('document_master_id') == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-[#123e68] px-4 text-sm font-semibold text-white hover:bg-[#0b2f52]"><i data-lucide="filter" class="h-4 w-4"></i>กรอง</button>
                    <a href="{{ route('manager.documents.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c9a227]/40 px-4 text-sm font-semibold text-[#0b2f52] hover:bg-[#fff9e8]">ล้าง</a>
                </div>
            </div>
        </form>

        <section class="portal-card overflow-hidden rounded-lg bg-white">
            <div class="flex items-center justify-between border-b border-slate-100 bg-[#fff9e8]/70 px-5 py-4">
                <div><h3 class="font-semibold text-[#0b2f52]">รายการเอกสาร</h3><p class="mt-1 text-sm text-slate-500">แสดง {{ number_format($documents->firstItem() ?? 0) }}-{{ number_format($documents->lastItem() ?? 0) }} จาก {{ number_format($documents->total()) }} รายการ</p></div>
                <i data-lucide="folder-check" class="h-5 w-5 text-[#c9a227]"></i>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[920px] text-left text-sm">
                    <thead class="border-b border-slate-100 bg-white text-xs uppercase tracking-wide text-slate-500">
                        <tr><th class="px-5 py-3 font-semibold">แรงงาน</th><th class="px-5 py-3 font-semibold">เอกสาร</th><th class="px-5 py-3 font-semibold">วันหมดอายุ</th><th class="px-5 py-3 font-semibold">ไฟล์</th><th class="px-5 py-3 text-right font-semibold">ดำเนินการ</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($documents as $document)
                            <tr class="hover:bg-[#fff9e8]/40">
                                <td class="px-5 py-4"><p class="font-semibold text-[#0b2f52]">{{ $document->worker?->full_name_th ?: $document->worker?->full_name_en ?: '-' }}</p><p class="mt-1 text-slate-500">{{ $document->worker?->employer?->company_name ?? '-' }}</p></td>
                                <td class="px-5 py-4 text-slate-600">{{ $document->documentMaster?->name ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    @if ($document->expiry_date)
                                        @php $expired = $document->expiry_date->isPast(); $days = now()->startOfDay()->diffInDays($document->expiry_date->copy()->startOfDay(), false); @endphp
                                        <span class="rounded-lg px-3 py-1 text-xs font-semibold {{ $expired ? 'bg-rose-100 text-rose-700' : ($days <= 30 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">{{ $document->expiry_date->format('d/m/Y') }}</span>
                                    @else
                                        <span class="text-slate-400">ไม่ระบุ</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4"><a href="{{ Storage::url($document->file_path) }}" target="_blank" class="inline-flex items-center gap-2 font-semibold text-[#0b2f52] hover:text-[#c9a227]"><i data-lucide="external-link" class="h-4 w-4"></i>เปิดไฟล์</a></td>
                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex gap-2">
                                        <a href="{{ route('manager.documents.edit', $document) }}" class="inline-flex h-9 items-center gap-2 rounded-lg border border-[#c9a227]/40 px-3 text-sm font-semibold text-[#0b2f52] hover:bg-[#fff9e8]"><i data-lucide="edit" class="h-4 w-4"></i>แก้ไข</a>
                                        <form action="{{ route('manager.documents.destroy', $document) }}" method="POST" onsubmit="return confirm('คุณแน่ใจหรือ?');">@csrf @method('DELETE')<button class="inline-flex h-9 items-center gap-2 rounded-lg border border-rose-200 px-3 text-sm font-semibold text-rose-700 hover:bg-rose-50"><i data-lucide="trash-2" class="h-4 w-4"></i>ลบ</button></form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-14 text-center text-sm text-slate-500">ไม่พบข้อมูลเอกสาร</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($documents->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $documents->links() }}</div>@endif
        </section>
    </div>
@endsection
