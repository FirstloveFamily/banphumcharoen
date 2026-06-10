@extends('layouts.staff-portal', ['title' => $deliverySheet->sheet_number, 'pageTitle' => 'ใบส่งงาน'])

@section('content')
    <div class="space-y-8">
        <header class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex items-start gap-4">
                <a href="{{ route('staff.portal.delivery-sheets.index') }}"
                    class="grid h-12 w-12 place-items-center rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-slate-600 transition-all shadow-sm">
                    <i data-lucide="arrow-left" class="h-6 w-6"></i>
                </a>
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.3em] text-slate-400">Delivery Sheet</p>
                    <h1 class="mt-1 text-3xl font-black tracking-tight text-[#0b2f52]">{{ $deliverySheet->sheet_number }}</h1>
                    <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-slate-500">
                        <span>{{ $deliverySheet->employer?->company_name ?? '-' }}</span>
                        <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                        <span>{{ $deliverySheet->sheet_date?->format('d/m/Y') ?? '-' }}</span>
                        <span class="inline-flex rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset {{ $deliverySheet->status_badge_class }}">
                            {{ $deliverySheet->status_label }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <form action="{{ route('staff.portal.delivery-sheets.destroy', $deliverySheet) }}" method="POST" onsubmit="return confirm('ต้องการลบใบส่งงานนี้ใช่ไหม?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="portal-btn-danger inline-flex h-11 items-center gap-2 px-4 text-sm font-bold">
                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                        ลบใบส่งงาน
                    </button>
                </form>
            </div>
        </header>

        @if ($errors->any())
            <div class="rounded-3xl bg-rose-50 p-6 border border-rose-100">
                <div class="flex gap-3">
                    <i data-lucide="alert-circle" class="h-5 w-5 text-rose-500 shrink-0"></i>
                    <div>
                        <h4 class="text-sm font-bold text-rose-800 uppercase tracking-wide">กรุณาตรวจสอบข้อผิดพลาด</h4>
                        <ul class="mt-2 list-disc pl-5 text-sm text-rose-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid gap-4 md:grid-cols-4">
            <article class="portal-card p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">ใบงานในชุด</p>
                <p class="mt-2 text-3xl font-black text-[#0b2f52]">{{ number_format($jobOrderCount) }}</p>
            </article>
            <article class="portal-card p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">หลักฐานแนบ</p>
                <p class="mt-2 text-3xl font-black text-[#0b2f52]">{{ number_format($attachmentCount) }}</p>
            </article>
            <article class="portal-card p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">ผู้สร้าง</p>
                <p class="mt-2 text-base font-bold text-[#0b2f52]">{{ $deliverySheet->createdBy?->name ?? '-' }}</p>
            </article>
            <article class="portal-card p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">สถานะล่าสุด</p>
                <p class="mt-2 text-base font-bold text-[#0b2f52]">{{ $deliverySheet->status_label }}</p>
            </article>
        </div>

        <section class="grid gap-8 lg:grid-cols-[360px_1fr]">
            <div class="space-y-6">
                <form action="{{ route('staff.portal.delivery-sheets.update', $deliverySheet) }}" method="POST" class="portal-card space-y-5 p-6">
                    @csrf
                    @method('PUT')
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">วันที่ใบส่งงาน</label>
                        <input type="date" name="sheet_date" value="{{ old('sheet_date', $deliverySheet->sheet_date?->format('Y-m-d')) }}" class="portal-input h-12 w-full px-4 text-sm">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">สถานะ</label>
                        <select name="status" class="portal-select h-12 w-full px-4 text-sm">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $deliverySheet->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">หมายเหตุ</label>
                        <textarea name="note" rows="4" class="portal-textarea w-full px-4 py-3 text-sm">{{ old('note', $deliverySheet->note) }}</textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="portal-btn-primary inline-flex h-11 items-center gap-2 px-4 text-sm font-bold">
                            <i data-lucide="save" class="h-4 w-4"></i>
                            บันทึก
                        </button>
                    </div>
                </form>

                <section class="portal-card p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-bold text-[#0b2f52]">อัปโหลดหลักฐาน</h2>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">PDF / JPG / PNG / WEBP</span>
                    </div>
                    <form action="{{ route('staff.portal.delivery-sheets.attachments.store', $deliverySheet) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500">ไฟล์หลักฐาน</label>
                            <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#0b2f52] file:text-white hover:file:bg-[#123e68] transition-all border border-slate-100 rounded-2xl p-1 bg-slate-50/50">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500">หมายเหตุ</label>
                            <textarea name="note" rows="3" class="portal-textarea w-full px-4 py-3 text-sm" placeholder="เช่น รูปถ่ายตอนส่งงาน">{{ old('note') }}</textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="portal-btn-secondary inline-flex h-11 items-center gap-2 px-4 text-sm font-bold">
                                <i data-lucide="upload" class="h-4 w-4"></i>
                                เพิ่มหลักฐาน
                            </button>
                        </div>
                    </form>
                </section>
            </div>

            <div class="space-y-8">
                <section class="portal-card overflow-hidden">
                    <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-[#0b2f52]">ใบงานในใบส่งงาน</h2>
                            <p class="text-sm text-slate-500">กดลบได้ถ้าต้องการปรับชุดงานก่อนส่งจริง</p>
                        </div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ number_format($jobOrderCount) }} งาน</span>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse($deliverySheet->items as $item)
                            <article class="p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-bold text-[#0b2f52]">{{ $item->jobOrder?->job_number }}</span>
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset {{ $item->jobOrder?->status_badge_class }}">
                                                {{ $item->jobOrder?->status_label }}
                                            </span>
                                        </div>
                                        <p class="mt-1 text-sm font-medium text-slate-700">{{ $item->jobOrder?->worker?->full_name_th ?: $item->jobOrder?->worker?->full_name_en }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $item->jobOrder?->service?->name ?? '-' }} · Due {{ $item->jobOrder?->due_date?->format('d/m/Y') ?? '-' }}</p>
                                        @if($item->note)
                                            <p class="mt-2 text-xs text-slate-500">{{ $item->note }}</p>
                                        @endif
                                    </div>
                                    <form action="{{ route('staff.portal.delivery-sheets.items.destroy', [$deliverySheet, $item]) }}" method="POST" onsubmit="return confirm('ต้องการลบใบงานนี้ออกจากใบส่งงานใช่ไหม?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-xl bg-rose-50 px-3 text-xs font-black uppercase tracking-wider text-rose-600 hover:bg-rose-600 hover:text-white transition-all">
                                            <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                            ลบ
                                        </button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <div class="p-10 text-center text-sm text-slate-500">
                                ยังไม่มีใบงานในใบส่งงานนี้
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="portal-card overflow-hidden">
                    <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-[#0b2f52]">หลักฐานแนบ</h2>
                            <p class="text-sm text-slate-500">เก็บรูป/ไฟล์ที่ใช้เป็นหลักฐานการส่งงาน</p>
                        </div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ number_format($attachmentCount) }} ไฟล์</span>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse($deliverySheet->attachments as $attachment)
                            @php
                                $isImage = str_starts_with((string) $attachment->mime_type, 'image/')
                                    || in_array(strtolower(pathinfo($attachment->file_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                            @endphp
                            <article class="p-5">
                                <div class="space-y-4">
                                    @if($isImage)
                                        <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" class="block overflow-hidden rounded-2xl border border-slate-100 bg-slate-50">
                                            <img src="{{ asset('storage/' . $attachment->file_path) }}" alt="{{ $attachment->file_name }}" class="h-56 w-full object-cover">
                                        </a>
                                    @else
                                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-5 text-center">
                                            <i data-lucide="file-text" class="mx-auto h-8 w-8 text-slate-300"></i>
                                            <p class="mt-2 text-sm font-bold text-slate-700">{{ $attachment->file_name }}</p>
                                            <p class="mt-1 text-xs text-slate-500">ไฟล์แนบชนิดอื่น สามารถกดเปิดดูได้</p>
                                        </div>
                                    @endif

                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <h3 class="text-sm font-bold text-[#0b2f52]">{{ $attachment->file_name }}</h3>
                                            <p class="mt-1 text-xs text-slate-500">{{ $attachment->created_at?->format('d/m/Y H:i') }} · {{ $attachment->uploadedBy?->name ?? '-' }}</p>
                                            @if($attachment->note)
                                                <p class="mt-2 text-xs text-slate-500">{{ $attachment->note }}</p>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" class="inline-flex h-9 items-center gap-2 rounded-xl bg-blue-50 px-3 text-xs font-black uppercase tracking-wider text-blue-700 hover:bg-blue-600 hover:text-white transition-all">
                                                <i data-lucide="external-link" class="h-3.5 w-3.5"></i>
                                                เปิด
                                            </a>
                                            <form action="{{ route('staff.portal.delivery-sheets.attachments.destroy', [$deliverySheet, $attachment]) }}" method="POST" onsubmit="return confirm('ต้องการลบหลักฐานนี้ใช่ไหม?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-xl bg-rose-50 px-3 text-xs font-black uppercase tracking-wider text-rose-600 hover:bg-rose-600 hover:text-white transition-all">
                                                    <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                                    ลบ
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="p-10 text-center text-sm text-slate-500">
                                ยังไม่มีหลักฐานแนบ
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>
        </section>
    </div>
@endsection
