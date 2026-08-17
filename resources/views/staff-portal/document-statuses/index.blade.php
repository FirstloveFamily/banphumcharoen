@extends('layouts.staff-portal', ['title' => 'ตั้งค่าสถานะเอกสาร', 'pageTitle' => 'ตั้งค่าสถานะเอกสาร'])

@section('content')
    <div class="mx-auto max-w-5xl space-y-8">
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">ตั้งค่าสถานะเอกสาร</h2>
                <p class="mt-1 text-slate-500">แก้ไขชื่อ สี และลำดับสถานะที่ใช้กับเอกสารแรงงาน</p>
            </div>
            <a href="{{ route('staff.portal.settings') }}" class="inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-bold text-slate-600 shadow-sm ring-1 ring-slate-200 hover:text-blue-600">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                กลับหน้าตั้งค่า
            </a>
        </header>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-sm font-bold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-100 bg-rose-50 p-4 text-sm text-rose-700">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="glass-card rounded-3xl p-6 shadow-sm">
            <div class="mb-5 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-700">
                    <i data-lucide="plus" class="h-5 w-5"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">เพิ่มสถานะใหม่</h3>
                    <p class="text-xs text-slate-500">Code ใช้ตัวอักษรภาษาอังกฤษตัวเล็ก ตัวเลข และ _ เท่านั้น</p>
                </div>
            </div>
            <form action="{{ route('staff.portal.document-statuses.store') }}" method="POST" class="grid gap-4 lg:grid-cols-[180px_1fr_180px_100px_auto] lg:items-end">
                @csrf
                <div>
                    <label class="text-xs font-bold text-slate-500">Code</label>
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="waiting_external" required maxlength="40"
                        class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 font-mono text-xs outline-none focus:border-blue-400 focus:bg-white">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500">ข้อความสถานะ</label>
                    <input type="text" name="name_th" value="{{ old('name_th') }}" placeholder="รอเอกสารจากหน่วยงาน" required maxlength="100"
                        class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm font-bold outline-none focus:border-blue-400 focus:bg-white">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500">สีสถานะ</label>
                    <select name="color_class" class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs font-bold outline-none focus:border-blue-400 focus:bg-white">
                        <option value="bg-slate-100 text-slate-500">เทา</option>
                        <option value="bg-amber-50 text-amber-700">เหลือง</option>
                        <option value="bg-blue-50 text-blue-700">น้ำเงิน</option>
                        <option value="bg-emerald-50 text-emerald-700">เขียว</option>
                        <option value="bg-rose-50 text-rose-700">แดง</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500">ลำดับ</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 50) }}" min="0" max="999" required
                        class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm font-bold outline-none focus:border-blue-400 focus:bg-white">
                </div>
                <button type="submit" class="h-11 rounded-xl bg-blue-600 px-5 text-xs font-black text-white hover:bg-blue-700 transition-all">เพิ่มสถานะ</button>
            </form>
        </section>

        <section class="space-y-4">
            @foreach ($statuses as $status)
                <form action="{{ route('staff.portal.document-statuses.update', $status) }}" method="POST" class="glass-card rounded-3xl p-6 shadow-sm">
                    @csrf
                    @method('PUT')
                    <div class="grid gap-5 lg:grid-cols-[160px_1fr_220px_120px_auto] lg:items-end">
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Code</label>
                            <p class="mt-2 font-mono text-sm font-bold text-slate-700">{{ $status->code }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500">ข้อความสถานะ</label>
                            <input type="text" name="name_th" value="{{ $status->name_th }}" required maxlength="100"
                                class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm font-bold outline-none focus:border-blue-400 focus:bg-white">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500">สีสถานะ</label>
                            <select name="color_class" class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs font-bold outline-none focus:border-blue-400 focus:bg-white">
                                @foreach([
                                    'bg-slate-100 text-slate-500' => 'เทา',
                                    'bg-amber-50 text-amber-700' => 'เหลือง',
                                    'bg-blue-50 text-blue-700' => 'น้ำเงิน',
                                    'bg-emerald-50 text-emerald-700' => 'เขียว',
                                    'bg-rose-50 text-rose-700' => 'แดง',
                                ] as $class => $label)
                                    <option value="{{ $class }}" @selected($status->color_class === $class)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500">ลำดับ</label>
                            <input type="number" name="sort_order" value="{{ $status->sort_order }}" min="0" max="999" required
                                class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm font-bold outline-none focus:border-blue-400 focus:bg-white">
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="flex items-center gap-2 text-xs font-bold text-slate-600">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" @checked($status->is_active) class="h-4 w-4 rounded border-slate-300 text-blue-600">
                                ใช้งาน
                            </label>
                            <button type="submit" class="inline-flex h-11 items-center gap-2 rounded-xl bg-slate-900 px-4 text-xs font-black text-white hover:bg-blue-700 transition-all">
                                <i data-lucide="save" class="h-3.5 w-3.5"></i>
                                บันทึก
                            </button>
                        </div>
                    </div>
                </form>
            @endforeach
        </section>
    </div>
@endsection
