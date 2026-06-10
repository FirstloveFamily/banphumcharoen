@extends('layouts.app', ['title' => 'แก้ไขบทบาท'])

@section('content')
<div class="max-w-3xl mx-auto p-6">
    <div class="mb-6">
        <a href="{{ route('admin-manage.roles.index') }}" 
            class="inline-flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 mb-4">
            <i data-lucide="arrow-left" class="h-4 w-4"></i>
            กลับไปหน้ารายการบทบาท
        </a>
        <h1 class="text-2xl font-bold text-slate-900">แก้ไขบทบาท</h1>
        <p class="text-sm text-slate-600 mt-1">แก้ไขข้อมูลบทบาท: {{ $role->name }}</p>
    </div>

    <div class="rounded-2xl bg-white shadow-sm p-6">
        <form method="POST" action="{{ route('admin-manage.roles.update', $role->id) }}">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-2">
                        ชื่อบทบาท <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name', $role->name) }}"
                        required
                        placeholder="เช่น: Admin, Manager, Editor"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        สิทธิ์
                    </label>
                    <div class="border border-slate-300 rounded-lg p-4 max-h-64 overflow-y-auto">
                        @forelse($permissions as $permission)
                        <label class="flex items-center gap-3 py-2 hover:bg-slate-50 px-2 rounded cursor-pointer">
                            <input type="checkbox" 
                                name="permissions[]" 
                                value="{{ $permission->id }}"
                                {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}
                                {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                            <div>
                                <span class="text-sm font-medium text-slate-900">{{ $permission->name }}</span>
                                <p class="text-xs text-slate-500">{{ $permission->slug }}</p>
                            </div>
                        </label>
                        @empty
                        <p class="text-sm text-slate-500">ยังไม่มีสิทธิ์ในระบบ</p>
                        @endforelse
                    </div>
                    @error('permissions')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <a href="{{ route('admin-manage.roles.index') }}" 
                        class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900">
                        ยกเลิก
                    </a>
                    <button type="submit" 
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-700 text-white px-6 py-2 text-sm font-semibold hover:bg-blue-800">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        บันทึกการแก้ไข
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="mt-6 rounded-2xl bg-amber-50 border border-amber-200 p-4">
        <div class="flex items-start gap-3">
            <div class="h-8 w-8 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                <i data-lucide="alert-triangle" class="h-4 w-4 text-amber-600"></i>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-amber-900">ข้อมูลสำคัญ</h3>
                <p class="mt-1 text-sm text-amber-700">การแก้ไขบทบาทอาจส่งผลต่อผู้ใช้ที่มีบทบาทนี้อยู่ โปรดตรวจสอบให้แน่ใจก่อนบันทึก</p>
            </div>
        </div>
    </div>
</div>
@endsection
