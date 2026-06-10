@extends('layouts.staff-portal', ['title' => $service->name, 'pageTitle' => 'จัดการเอกสารของบริการ'])

@section('content')
    <div class="space-y-8">
        <header class="overflow-hidden rounded-lg bg-[#0b2f52] text-white shadow-xl shadow-[#0b2f52]/15">
            <div class="flex flex-col gap-4 px-5 py-6 sm:px-7 lg:flex-row lg:items-end lg:justify-between lg:px-8">
                <div>
                    <div class="mb-4 h-1 w-24 rounded-full bg-gradient-to-r from-[#b91c1c] to-transparent"></div>
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-[#fecaca]">Service Documents</p>
                    <h2 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">
                        {{ $service->name }}
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                        รหัส {{ $service->code }} · ตั้งค่าเอกสารที่ใช้กับบริการนี้
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('staff.portal.services.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-white/10 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/20">
                        <i data-lucide="arrow-left" class="h-4 w-4"></i>
                        กลับรายการ
                    </a>
                    <a href="{{ route('staff.portal.services.edit', $service) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-[#0b2f52] via-[#123e68] to-[#b91c1c] px-4 py-3 text-sm font-extrabold text-white transition hover:opacity-95">
                        <i data-lucide="square-pen" class="h-4 w-4"></i>
                        แก้ไขบริการ
                    </a>
                    <form method="POST" action="{{ route('staff.portal.services.destroy', $service) }}" onsubmit="return confirm('ต้องการลบบริการนี้ใช่หรือไม่?')" class="inline-flex">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-white/10 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/20">
                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                            ลบบริการ
                        </button>
                    </form>
                </div>
            </div>
        </header>

        @if (session('success'))
            <div class="manager-card flex items-center gap-3 border-l-4 border-emerald-500 px-4 py-3 text-sm font-bold text-emerald-700">
                <i data-lucide="check-circle-2" class="h-5 w-5"></i>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="manager-card flex items-center gap-3 border-l-4 border-rose-500 px-4 py-3 text-sm font-bold text-rose-700">
                <i data-lucide="alert-triangle" class="h-5 w-5"></i>
                {{ session('error') }}
            </div>
        @endif

        <section class="grid gap-4 sm:grid-cols-3">
            <article class="manager-card manager-card-hover p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">เอกสารทั้งหมด</p>
                <h3 class="mt-3 text-3xl font-extrabold text-[#0b2f52]">{{ number_format($summary['documents_total']) }}</h3>
            </article>
            <article class="manager-card manager-card-hover p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">เอกสารบังคับ</p>
                <h3 class="mt-3 text-3xl font-extrabold text-[#0b2f52]">{{ number_format($summary['documents_required']) }}</h3>
            </article>
            <article class="manager-card manager-card-hover p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">ใบงานที่ใช้บริการนี้</p>
                <h3 class="mt-3 text-3xl font-extrabold text-[#0b2f52]">{{ number_format($summary['job_orders']) }}</h3>
            </article>
        </section>

        @if ($errors->any())
            <div class="manager-card border-l-4 border-rose-500 p-5 text-sm text-rose-700">
                <p class="font-bold">พบข้อผิดพลาด</p>
                <ul class="mt-3 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[360px_minmax(0,1fr)]">
            <section class="manager-card overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                    <h3 class="text-base font-extrabold text-[#0b2f52]">เพิ่มเอกสารที่ใช้กับบริการ</h3>
                    <p class="mt-1 text-sm text-slate-500">เลือกเอกสารจากรายการที่มีอยู่ แล้วกำหนดลำดับการแสดงผล</p>
                </div>
                <form method="POST" action="{{ route('staff.portal.services.documents.store', $service) }}" class="space-y-4 p-5">
                    @csrf
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">รายการเอกสาร</label>
                        <select name="document_name" class="portal-select h-11 w-full px-4 text-sm font-medium" required>
                            <option value="">เลือกเอกสาร</option>
                            @foreach($documentMasters as $documentMaster)
                                <option value="{{ $documentMaster->name }}" @selected(old('document_name') === $documentMaster->name)>{{ $documentMaster->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">ลำดับ</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="portal-input h-11 w-full px-4 text-sm font-medium">
                    </div>
                    <label class="flex items-center gap-3 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                        <input type="hidden" name="is_required" value="0">
                        <input type="checkbox" name="is_required" value="1" @checked(old('is_required', true)) class="h-4 w-4 rounded border-slate-300 text-[#0b2f52] focus:ring-[#b91c1c]/20">
                        <span class="text-sm font-bold text-slate-700">เอกสารนี้จำเป็น</span>
                    </label>
                    <button type="submit" class="portal-btn-primary inline-flex w-full items-center justify-center gap-2 px-5 py-3 text-sm font-bold">
                        <i data-lucide="plus" class="h-4 w-4"></i>
                        เพิ่มเอกสาร
                    </button>
                </form>
            </section>

            <section class="manager-card overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                    <h3 class="text-base font-extrabold text-[#0b2f52]">เอกสารที่ใช้ในบริการนี้</h3>
                    <p class="mt-1 text-sm text-slate-500">แก้ไขลำดับ ความจำเป็น และลบรายการที่ไม่ต้องการ</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="portal-table-head text-xs font-bold uppercase tracking-wider">
                                <th class="px-5 py-4">เอกสาร</th>
                                <th class="px-4 py-4 text-center">ลำดับ</th>
                                <th class="px-4 py-4 text-center">จำเป็น</th>
                                <th class="px-5 py-4 text-right">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($service->checklists as $checklist)
                                <tr class="portal-row-hover transition-colors">
                                    <td class="px-5 py-4">
                                        <p class="font-bold text-[#0b2f52]">{{ $checklist->document_name }}</p>
                                    </td>
                                    <td class="px-4 py-4 text-center font-mono text-xs font-bold text-slate-700">{{ number_format($checklist->sort_order) }}</td>
                                    <td class="px-4 py-4 text-center">
                                        <span @class([
                                            'inline-flex rounded-full px-3 py-1 text-[11px] font-bold ring-1 ring-inset',
                                            'bg-emerald-50 text-emerald-700 ring-emerald-600/20' => $checklist->is_required,
                                            'bg-slate-100 text-slate-500 ring-slate-400/20' => ! $checklist->is_required,
                                        ])>
                                            {{ $checklist->is_required ? 'จำเป็น' : 'ไม่จำเป็น' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('staff.portal.services.documents.edit', [$service, $checklist]) }}" class="grid h-9 w-9 place-items-center rounded-lg bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-[#b91c1c] transition-all" title="แก้ไข">
                                                <i data-lucide="square-pen" class="h-4 w-4"></i>
                                            </a>
                                            <form method="POST" action="{{ route('staff.portal.services.documents.destroy', [$service, $checklist]) }}" onsubmit="return confirm('ต้องการลบเอกสารนี้ออกจากบริการใช่หรือไม่?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="grid h-9 w-9 place-items-center rounded-lg bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-rose-600 transition-all" title="ลบ">
                                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-14 text-center text-slate-400">ยังไม่มีเอกสารที่ผูกกับบริการนี้</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
@endsection
