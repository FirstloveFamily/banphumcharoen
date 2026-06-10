@extends('layouts.app', ['title' => 'จัดการสิทธิ์บทบาท'])

@section('content')
<div class="max-w-3xl mx-auto p-6">
    <div class="mb-6">
        <a href="{{ route('admin-manage.roles.index') }}" 
            class="inline-flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 mb-4">
            <i data-lucide="arrow-left" class="h-4 w-4"></i>
            กลับไปหน้ารายการบทบาท
        </a>
        <h1 class="text-2xl font-bold text-slate-900">จัดการสิทธิ์บทบาท</h1>
        <p class="text-sm text-slate-600 mt-1">บทบาท: {{ $role->name }}</p>
    </div>

    <div class="rounded-2xl bg-white shadow-sm p-6">
        <form method="POST" action="{{ route('admin-manage.roles.permissions.update', $role->id) }}">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <label class="block text-sm font-medium text-slate-700">
                            เลือกสิทธิ์ที่ต้องการกำหนดให้บทบาทนี้
                        </label>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="selectAllPermissions()" 
                                class="text-xs text-blue-600 hover:text-blue-800">
                                เลือกทั้งหมด
                            </button>
                            <span class="text-slate-300">|</span>
                            <button type="button" onclick="deselectAllPermissions()" 
                                class="text-xs text-blue-600 hover:text-blue-800">
                                ยกเลิกทั้งหมด
                            </button>
                        </div>
                    </div>
                    
                    <div class="border border-slate-300 rounded-lg p-4 max-h-96 overflow-y-auto">
                        @forelse($permissions as $permission)
                        <label class="flex items-center gap-3 py-3 hover:bg-slate-50 px-2 rounded cursor-pointer border-b border-slate-100 last:border-0">
                            <input type="checkbox" 
                                name="permissions[]" 
                                value="{{ $permission->id }}"
                                {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}
                                class="permission-checkbox h-4 w-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                            <div class="flex-1">
                                <span class="text-sm font-medium text-slate-900">{{ $permission->name }}</span>
                                <p class="text-xs text-slate-500">{{ $permission->slug }}</p>
                                @if($permission->description)
                                <p class="text-xs text-slate-400 mt-1">{{ $permission->description }}</p>
                                @endif
                            </div>
                        </label>
                        @empty
                        <p class="text-sm text-slate-500 py-4">ยังไม่มีสิทธิ์ในระบบ</p>
                        @endforelse
                    </div>
                    @error('permissions')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-lg bg-blue-50 border border-blue-200 p-4">
                    <div class="flex items-start gap-3">
                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                            <i data-lucide="info" class="h-4 w-4 text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-blue-900">สิทธิ์ที่เลือก: <span id="selected-count">{{ $role->permissions->count() }}</span> รายการ</h3>
                            <p class="mt-1 text-sm text-blue-700">ผู้ใช้ที่มีบทบาทนี้จะได้รับสิทธิ์ตามที่เลือก</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <a href="{{ route('admin-manage.roles.index') }}" 
                        class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900">
                        ยกเลิก
                    </a>
                    <button type="submit" 
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-700 text-white px-6 py-2 text-sm font-semibold hover:bg-blue-800">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        บันทึกสิทธิ์
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
                <p class="mt-1 text-sm text-amber-700">การเปลี่ยนแปลงสิทธิ์จะมีผลทันทีต่อผู้ใช้ทั้งหมดที่มีบทบาทนี้ โปรดตรวจสอบให้แน่ใจก่อนบันทึก</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function selectAllPermissions() {
        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
            checkbox.checked = true;
        });
        updateSelectedCount();
    }

    function deselectAllPermissions() {
        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const count = document.querySelectorAll('.permission-checkbox:checked').length;
        document.getElementById('selected-count').textContent = count;
    }

    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
</script>
@endpush
@endsection
