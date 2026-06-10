@extends('layouts.staff-portal', ['title' => 'แก้ไขผู้ใช้งาน', 'pageTitle' => 'จัดการผู้ใช้งานระบบ'])

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
                    <h2 class="text-3xl font-bold tracking-tight text-slate-900">แก้ไขผู้ใช้งาน</h2>
                    <p class="mt-1 text-slate-500">ปรับข้อมูลบัญชี ชื่อ อีเมล และบทบาทของผู้ใช้</p>
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

        <form action="{{ route('staff.portal.users.update', $user) }}" method="POST" class="space-y-8">
            @csrf
            @method('PUT')

            <section class="glass-card rounded-3xl p-8 shadow-sm">
                <div class="flex items-center gap-3 mb-8 pb-5 border-b border-slate-50">
                    <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                        <i data-lucide="user-round" class="h-4 w-4"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">ข้อมูลบัญชี</h3>
                </div>

                <div class="grid gap-8 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">ชื่อผู้ใช้งาน <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="portal-input h-12 w-full px-4 text-sm font-medium" placeholder="เช่น Somchai Admin">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">อีเมล <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="portal-input h-12 w-full px-4 text-sm font-medium" placeholder="example@company.com">
                    </div>
                </div>

                <div class="mt-8 grid gap-8 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">บทบาท <span class="text-rose-500">*</span></label>
                        <select name="role" required class="portal-select h-12 w-full px-4 text-sm font-medium">
                            <option value="">-- เลือกบทบาท --</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}" @selected(old('role', optional($user->roles->first())->name) === $role->name)>
                                    {{ $roleLabels[$role->name] ?? ucfirst(str_replace('_', ' ', $role->name)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">สถานะบัญชี</label>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                            แก้ไขข้อมูลของผู้ใช้งาน: <span class="font-bold text-slate-900">{{ $user->name }}</span>
                        </div>
                    </div>
                </div>

                <div id="employer-wrapper" class="mt-8 space-y-2 hidden">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">ผูกกับนายจ้าง <span class="text-rose-500">*</span></label>
                    <select name="employer_id" class="portal-select h-12 w-full px-4 text-sm font-medium">
                        <option value="">-- เลือกนายจ้าง --</option>
                        @foreach ($employers as $employer)
                            <option value="{{ $employer->id }}" @selected(old('employer_id', $user->employers->first()?->id) == $employer->id)>
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
                    <h3 class="text-lg font-bold text-slate-900">เปลี่ยนรหัสผ่าน</h3>
                </div>

                <div class="grid gap-8 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">รหัสผ่านใหม่</label>
                        <input type="password" name="password"
                            class="portal-input h-12 w-full px-4 text-sm font-medium" placeholder="เว้นว่างไว้ถ้าไม่ต้องการเปลี่ยน">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">ยืนยันรหัสผ่านใหม่</label>
                        <input type="password" name="password_confirmation"
                            class="portal-input h-12 w-full px-4 text-sm font-medium" placeholder="พิมพ์รหัสผ่านอีกครั้ง">
                    </div>
                </div>
            </section>

            <div class="flex items-center justify-end gap-4 pb-6">
                <a href="{{ route('staff.portal.users.index') }}"
                    class="portal-btn-secondary inline-flex h-12 items-center px-6 text-sm font-bold">
                    ยกเลิก
                </a>
                <button type="submit"
                    class="portal-btn-primary h-12 px-10 flex items-center justify-center gap-3 text-sm font-black uppercase tracking-[0.18em]">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    บันทึกการแก้ไข
                </button>
            </div>
        </form>

        @if (! auth()->user()->is($user))
            <div class="flex justify-start pb-12">
                <form action="{{ route('staff.portal.users.destroy', $user) }}" method="POST" onsubmit="return confirm('ต้องการลบบัญชีผู้ใช้งานนี้ใช่หรือไม่?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex h-12 items-center gap-3 rounded-2xl border border-rose-200 bg-white px-6 text-sm font-bold text-rose-700 shadow-sm transition hover:bg-rose-50">
                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                        ลบบัญชีนี้
                    </button>
                </form>
            </div>
        @endif
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
