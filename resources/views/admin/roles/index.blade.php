@extends('layouts.app', ['title' => 'จัดการบทบาท'])

@section('content')
<div class="max-w-7xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">จัดการบทบาท</h1>
            <p class="text-sm text-slate-600 mt-1">จัดการบทบาทและสิทธิ์ในระบบ</p>
        </div>
        <a href="{{ route('admin-manage.roles.create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-700 text-white px-4 py-2 text-sm font-semibold hover:bg-blue-800">
            <i data-lucide="plus" class="w-4 h-4"></i>
            เพิ่มบทบาท
        </a>
    </div>

    <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200">
            <div class="flex items-center gap-4">
                <div class="relative flex-1">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400"></i>
                    <input type="text" placeholder="ค้นหาบทบาท..." 
                        class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">ชื่อบทบาท</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">สิทธิ์</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">จำนวนผู้ใช้</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">สร้างเมื่อ</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($roles as $role)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-lg bg-purple-100 flex items-center justify-center">
                                    <i data-lucide="shield" class="h-4 w-4 text-purple-600"></i>
                                </div>
                                <span class="text-sm font-medium text-slate-900">{{ $role->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @if($role->permissions->count() > 0)
                                    @foreach($role->permissions->take(3) as $permission)
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700">
                                            {{ $permission->name }}
                                        </span>
                                    @endforeach
                                    @if($role->permissions->count() > 3)
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-600">
                                            +{{ $role->permissions->count() - 3 }} อื่นๆ
                                        </span>
                                    @endif
                                @else
                                    <span class="text-sm text-slate-400">ไม่มีสิทธิ์</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-slate-600">{{ $role->users_count ?? 0 }} คน</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                            {{ $role->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin-manage.roles.permissions', $role->id) }}"
                                    class="p-2 text-slate-600 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-colors"
                                    title="จัดการสิทธิ์">
                                    <i data-lucide="key" class="h-4 w-4"></i>
                                </a>
                                <a href="{{ route('admin-manage.roles.edit', $role->id) }}"
                                    class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                    <i data-lucide="pencil" class="h-4 w-4"></i>
                                </a>
                                <form method="POST" action="{{ route('admin-manage.roles.destroy', $role->id) }}" 
                                    onsubmit="return confirm('คุณต้องการลบบทบาทนี้ใช่หรือไม่?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center">
                                    <i data-lucide="shield" class="h-6 w-6 text-slate-400"></i>
                                </div>
                                <p class="text-sm text-slate-600">ยังไม่มีบทบาทในระบบ</p>
                                <a href="{{ route('admin-manage.roles.create') }}" 
                                    class="text-sm text-blue-600 hover:underline">เพิ่มบทบาทแรก</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($roles->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-between">
            <p class="text-sm text-slate-600">
                แสดง {{ $roles->firstItem() }} ถึง {{ $roles->lastItem() }} จาก {{ $roles->total() }} รายการ
            </p>
            {{ $roles->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
