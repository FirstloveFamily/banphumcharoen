@extends('layouts.staff-portal', ['title' => 'เพิ่มผู้ใช้งาน', 'pageTitle' => 'จัดการผู้ใช้งานระบบ'])

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
    <div class="space-y-8 max-w-5xl mx-auto">
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-5">
                <a href="{{ route('staff.portal.users.index') }}"
                    class="grid h-12 w-12 place-items-center rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-slate-600 transition-all shadow-sm">
                    <i data-lucide="arrow-left" class="h-6 w-6"></i>
                </a>
                <div>
                    <h2 class="text-3xl font-bold tracking-tight text-slate-900">เพิ่มผู้ใช้งานใหม่</h2>
                    <p class="mt-1 text-slate-500">สร้างบัญชีใหม่และกำหนดสิทธิ์การเข้าระบบ</p>
                </div>
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

        @if (session('error'))
            <div class="rounded-2xl bg-rose-50 p-4 border border-rose-100 flex items-center gap-3 text-rose-700 font-bold text-sm shadow-sm">
                <i data-lucide="alert-circle" class="h-5 w-5"></i>
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('staff.portal.users.store') }}" method="POST" class="space-y-8">
            @csrf

            <section class="glass-card rounded-3xl p-8 shadow-sm">
                <div class="flex items-center gap-3 mb-8 pb-5 border-b border-slate-50">
                    <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                        <i data-lucide="user-round-plus" class="h-4 w-4"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">ข้อมูลบัญชี</h3>
                </div>

                <div class="grid gap-8 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">ชื่อผู้ใช้งาน <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="portal-input h-12 w-full px-4 text-sm font-medium" placeholder="เช่น Somchai Admin">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">อีเมล <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="portal-input h-12 w-full px-4 text-sm font-medium" placeholder="example@company.com">
                    </div>
                </div>

                <div class="mt-8 grid gap-8 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">บทบาท <span class="text-rose-500">*</span></label>
                        <select name="role" required class="portal-select h-12 w-full px-4 text-sm font-medium">
                            <option value="">-- เลือกบทบาท --</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}" @selected(old('role') === $role->name)>
                                    {{ $roleLabels[$role->name] ?? ucfirst(str_replace('_', ' ', $role->name)) }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500">ระบบจะกำหนดสิทธิ์ตามบทบาทที่เลือก</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">คำแนะนำ</label>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                            สามารถสร้างบัญชีได้เฉพาะ `Staff` และ `Employer`
                        </div>
                    </div>
                </div>

                <div id="employer-wrapper" class="mt-8 space-y-2 hidden">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500">ผูกกับนายจ้าง <span class="text-rose-500">*</span></label>
                    <select name="employer_id" class="portal-select h-12 w-full px-4 text-sm font-medium">
                        <option value="">-- เลือกนายจ้าง --</option>
                        @foreach ($employers as $employer)
                            <option value="{{ $employer->id }}" @selected((string) old('employer_id') === (string) $employer->id)>
                                {{ $employer->company_name }} ({{ $employer->company_code }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-500">บัญชีประเภท Employer จะเห็นข้อมูลของนายจ้างที่เลือกเท่านั้น</p>
                </div>
            </section>

            <section class="glass-card rounded-3xl p-8 shadow-sm">
                <div class="flex items-center gap-3 mb-8 pb-5 border-b border-slate-50">
                    <div class="h-8 w-8 rounded-lg bg-rose-100 flex items-center justify-center text-rose-600">
                        <i data-lucide="lock-keyhole" class="h-4 w-4"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">ตั้งรหัสผ่าน</h3>
                </div>

                <div class="grid gap-8 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">รหัสผ่าน <span class="text-rose-500">*</span></label>
                        <input type="password" name="password" required
                            class="portal-input h-12 w-full px-4 text-sm font-medium" placeholder="อย่างน้อย 8 ตัวอักษร">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">ยืนยันรหัสผ่าน <span class="text-rose-500">*</span></label>
                        <input type="password" name="password_confirmation" required
                            class="portal-input h-12 w-full px-4 text-sm font-medium" placeholder="พิมพ์รหัสผ่านอีกครั้ง">
                    </div>
                </div>
            </section>

            <div class="flex items-center justify-end gap-4 pb-12">
                <a href="{{ route('staff.portal.users.index') }}"
                    class="portal-btn-secondary inline-flex h-12 items-center px-6 text-sm font-bold">
                    ยกเลิก
                </a>
                <button type="submit"
                    class="portal-btn-primary h-12 px-10 flex items-center justify-center gap-3 text-sm font-black uppercase tracking-[0.18em]">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    บันทึกผู้ใช้งาน
                </button>
            </div>
        </form>
    </div>

    <script>
        const roleSelect = document.querySelector('select[name="role"]');
        const employerWrapper = document.getElementById('employer-wrapper');
        const employerSelect = document.querySelector('select[name="employer_id"]');

        function toggleEmployerField() {
            const isEmployer = roleSelect?.value === 'employer';
            employerWrapper?.classList.toggle('hidden', !isEmployer);
            if (employerSelect) {
                employerSelect.required = isEmployer;
                if (!isEmployer) {
                    employerSelect.value = '';
                }
            }
        }

        roleSelect?.addEventListener('change', toggleEmployerField);
        toggleEmployerField();
    </script>
@endsection
