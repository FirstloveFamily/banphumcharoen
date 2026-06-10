@extends('layouts.staff-portal', ['title' => 'ข่าวสารกิจกรรม', 'pageTitle' => 'จัดการข่าวสารกิจกรรม'])

@section('content')
    @php
        $statusLabels = [
            '' => 'ทุกสถานะ',
            'draft' => 'ฉบับร่าง',
            'published' => 'เผยแพร่',
        ];
    @endphp

    <div class="space-y-8">
        <header class="overflow-hidden rounded-lg bg-[#0b2f52] text-white shadow-xl shadow-[#0b2f52]/15">
            <div class="flex flex-col gap-6 px-5 py-6 sm:px-7 lg:flex-row lg:items-end lg:justify-between lg:px-8">
                <div class="max-w-3xl">
                    <div class="mb-4 h-1 w-24 rounded-full bg-gradient-to-r from-[#b91c1c] to-transparent"></div>
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-[#fecaca]">News & Activities</p>
                    <h2 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">
                        จัดการข่าวสารกิจกรรม
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                        เพิ่ม แก้ไข และลบข่าวสารกิจกรรมที่แสดงบนหน้าเว็บไซต์หลัก
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('staff.portal.news.create') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-[#0b2f52] via-[#123e68] to-[#b91c1c] px-4 py-3 text-sm font-extrabold text-white shadow-lg shadow-black/20 transition hover:opacity-95">
                        <i data-lucide="plus" class="h-4 w-4"></i>
                        เพิ่มข่าวสาร
                    </a>
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

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="manager-card manager-card-hover p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">ข่าวทั้งหมด</p>
                <h3 class="mt-3 text-3xl font-extrabold text-[#0b2f52]">{{ number_format($summary['total']) }}</h3>
            </article>
            <article class="manager-card manager-card-hover p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">เผยแพร่แล้ว</p>
                <h3 class="mt-3 text-3xl font-extrabold text-emerald-600">{{ number_format($summary['published']) }}</h3>
            </article>
            <article class="manager-card manager-card-hover p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">ฉบับร่าง</p>
                <h3 class="mt-3 text-3xl font-extrabold text-amber-600">{{ number_format($summary['draft']) }}</h3>
            </article>
            <article class="manager-card manager-card-hover p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">ปักหมุด</p>
                <h3 class="mt-3 text-3xl font-extrabold text-rose-600">{{ number_format($summary['pinned']) }}</h3>
            </article>
        </section>

        <section class="manager-card overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h3 class="text-lg font-extrabold text-[#0b2f52]">รายการข่าวสารกิจกรรม</h3>
                    <p class="mt-1 text-sm text-slate-500">ค้นหาและจัดการข่าวสารที่เผยแพร่บนหน้าเว็บ</p>
                </div>

                <form method="GET" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <input type="text" name="q" value="{{ $keyword }}" placeholder="ค้นหาหัวข้อข่าว..."
                        class="portal-input h-11 w-full sm:w-64 px-4 text-sm font-medium">
                    <select name="status" class="portal-select h-11 px-4 text-sm font-medium">
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="category_id" class="portal-select h-11 px-4 text-sm font-medium">
                        <option value="0">ทุกหมวดหมู่</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) $categoryId === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="portal-btn-primary inline-flex h-11 items-center justify-center px-4 text-sm font-bold">
                        กรองข้อมูล
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="portal-table-head text-xs font-bold uppercase tracking-wider">
                            <th class="px-6 py-4">ข่าวสาร</th>
                            <th class="px-4 py-4">หมวดหมู่</th>
                            <th class="px-4 py-4 text-center">สถานะ</th>
                            <th class="px-4 py-4 text-center">ปักหมุด</th>
                            <th class="px-4 py-4 text-center">วันที่เผยแพร่</th>
                            <th class="px-6 py-4 text-right">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($newsPosts as $newsPost)
                            <tr class="portal-row-hover transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-start gap-4">
                                        <div class="h-14 w-20 overflow-hidden rounded-xl bg-slate-100 border border-slate-200 shrink-0">
                                            <img src="{{ $newsPost->image_cover ? asset('storage/' . $newsPost->image_cover) : asset('storage/images/logo.jpeg') }}"
                                                alt="{{ $newsPost->title }}" class="h-full w-full object-cover">
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-[#0b2f52]">{{ $newsPost->title }}</p>
                                            <p class="mt-1 line-clamp-2 text-xs text-slate-500">{{ $newsPost->excerpt ?: '-' }}</p>
                                            <p class="mt-1 text-[11px] font-mono text-slate-400">{{ $newsPost->slug }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 ring-1 ring-blue-100">
                                        {{ $newsPost->category?->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span @class([
                                        'inline-flex rounded-full px-3 py-1 text-[11px] font-bold ring-1 ring-inset',
                                        'bg-emerald-50 text-emerald-700 ring-emerald-600/20' => $newsPost->status === 'published',
                                        'bg-slate-100 text-slate-500 ring-slate-400/20' => $newsPost->status !== 'published',
                                    ])>
                                        {{ $newsPost->status === 'published' ? 'เผยแพร่' : 'ฉบับร่าง' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span @class([
                                        'inline-flex rounded-full px-3 py-1 text-[11px] font-bold ring-1 ring-inset',
                                        'bg-rose-50 text-rose-700 ring-rose-600/20' => $newsPost->is_pinned,
                                        'bg-slate-100 text-slate-500 ring-slate-400/20' => ! $newsPost->is_pinned,
                                    ])>
                                        {{ $newsPost->is_pinned ? 'ใช่' : 'ไม่ใช่' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center text-sm font-bold text-slate-700">
                                    {{ $newsPost->published_at?->format('d/m/Y H:i') ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('staff.portal.news.edit', $newsPost) }}"
                                            class="grid h-9 w-9 place-items-center rounded-lg bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-[#0b2f52] transition-all"
                                            title="แก้ไข">
                                            <i data-lucide="square-pen" class="h-4 w-4"></i>
                                        </a>
                                        <form action="{{ route('staff.portal.news.destroy', $newsPost) }}" method="POST"
                                            onsubmit="return confirm('ต้องการลบข่าวสารนี้ใช่หรือไม่?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="grid h-9 w-9 place-items-center rounded-lg bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-rose-600 transition-all"
                                                title="ลบ">
                                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-14 text-center text-slate-400">
                                    ไม่พบข้อมูลข่าวสาร
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-6 py-4">
                {{ $newsPosts->links() }}
            </div>
        </section>
    </div>
@endsection
