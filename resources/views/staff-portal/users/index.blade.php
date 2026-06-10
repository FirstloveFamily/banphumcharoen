@extends('layouts.staff-portal', ['title' => 'ผู้ใช้งานระบบ', 'pageTitle' => 'จัดการผู้ใช้งานระบบ'])

@push('head')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
</style>
@endpush

@section('content')
    <div class="space-y-8 max-w-7xl mx-auto">
        <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">ผู้ใช้งานระบบ</h2>
                <p class="mt-1 text-slate-500">ดูรายชื่อบัญชีผู้ใช้และสร้างบัญชีใหม่สำหรับทีมงาน</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('staff.portal.users.create') }}"
                    class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-slate-900/15 transition hover:bg-blue-700">
                    <i data-lucide="user-plus" class="h-4 w-4"></i>
                    เพิ่มผู้ใช้งาน
                </a>
            </div>
        </header>

        @if (session('success'))
            <div class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 flex items-center gap-3 text-emerald-700 font-bold text-sm shadow-sm">
                <i data-lucide="check-circle" class="h-5 w-5"></i>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-2xl bg-rose-50 p-4 border border-rose-100 flex items-center gap-3 text-rose-700 font-bold text-sm shadow-sm">
                <i data-lucide="alert-circle" class="h-5 w-5"></i>
                {{ session('error') }}
            </div>
        @endif

        <section class="grid gap-4 md:grid-cols-3">
            <div class="glass-card rounded-3xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">ทั้งหมด</p>
                <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ number_format($summary['total']) }}</div>
            </div>
            <div class="glass-card rounded-3xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Staff</p>
                <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ number_format($summary['staff']) }}</div>
            </div>
            <div class="glass-card rounded-3xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Employer</p>
                <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ number_format($summary['employer']) }}</div>
            </div>
        </section>

        <section class="glass-card rounded-[2rem] p-6 shadow-sm">
            <form method="GET" action="{{ route('staff.portal.users.index') }}" class="grid gap-4 md:grid-cols-[1fr_220px_auto]">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">ค้นหา</label>
                    <input type="text" name="q" value="{{ $keyword }}"
                        placeholder="ชื่อ หรืออีเมล"
                        class="portal-input h-12 w-full px-4 text-sm font-medium">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">บทบาท</label>
                    <select name="role" class="portal-select h-12 w-full px-4 text-sm font-medium">
                        <option value="">ทั้งหมด</option>
                        @foreach ($allowedRoles as $role)
                            <option value="{{ $role }}" @selected($roleFilter === $role)>{{ $roleLabels[$role] ?? ucfirst(str_replace('_', ' ', $role)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-3">
                    <button type="submit" class="portal-btn-primary h-12 px-5 text-sm font-bold">
                        ค้นหา
                    </button>
                    <a href="{{ route('staff.portal.users.index') }}" class="portal-btn-secondary inline-flex h-12 items-center px-5 text-sm font-bold">
                        ล้าง
                    </a>
                </div>
            </form>
        </section>

        <section class="glass-card overflow-hidden rounded-[2rem] shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="portal-table-head">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-[0.18em]">ผู้ใช้งาน</th>
                            <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-[0.18em]">อีเมล</th>
                            <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-[0.18em]">บทบาท</th>
                            <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-[0.18em]">นายจ้าง</th>
                            <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-[0.18em]">สร้างเมื่อ</th>
                            <th class="px-6 py-4 text-right text-xs font-black uppercase tracking-[0.18em]">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white/50">
                        @forelse ($users as $user)
                            <tr class="portal-row-hover transition">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-900 text-sm font-black text-white">
                                            {{ mb_substr($user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900">{{ $user->name }}</div>
                                            <div class="text-xs text-slate-500">ID {{ $user->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-sm text-slate-600">{{ $user->email }}</td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($user->roles as $role)
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                                                {{ $roleLabels[$role->name] ?? ucfirst(str_replace('_', ' ', $role->name)) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-sm text-slate-600">
                                    {{ $user->employers->first()?->company_name ?: '-' }}
                                </td>
                                <td class="px-6 py-5 text-sm text-slate-600">{{ $user->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('staff.portal.users.edit', $user) }}"
                                            class="inline-flex h-10 items-center gap-2 rounded-xl bg-white px-4 text-xs font-bold text-slate-700 border border-slate-200 shadow-sm transition hover:border-amber-200 hover:text-amber-700">
                                            <i data-lucide="edit-3" class="h-4 w-4"></i>
                                            แก้ไข
                                        </a>
                                        <form action="{{ route('staff.portal.users.destroy', $user) }}" method="POST" onsubmit="return confirm('ต้องการลบบัญชีผู้ใช้งานนี้ใช่หรือไม่?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex h-10 items-center gap-2 rounded-xl bg-white px-4 text-xs font-bold text-slate-700 border border-slate-200 shadow-sm transition hover:border-rose-200 hover:text-rose-700">
                                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                                                ลบ
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-14 text-center">
                                    <div class="mx-auto max-w-sm">
                                        <i data-lucide="users" class="mx-auto h-12 w-12 text-slate-300"></i>
                                        <h3 class="mt-4 text-lg font-bold text-slate-900">ยังไม่มีผู้ใช้งาน</h3>
                                        <p class="mt-2 text-sm text-slate-500">เริ่มต้นด้วยการสร้างบัญชีผู้ใช้งานใหม่ได้เลย</p>
                                        <a href="{{ route('staff.portal.users.create') }}"
                                            class="mt-5 inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-700">
                                            <i data-lucide="user-plus" class="h-4 w-4"></i>
                                            เพิ่มผู้ใช้งาน
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-6 py-4">
                {{ $users->links() }}
            </div>
        </section>
    </div>
@endsection
