@extends('layouts.staff-portal', ['title' => 'ตั้งค่าการใช้งาน', 'pageTitle' => 'Account Settings'])

@push('head')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
</style>
@endpush

@section('content')
    <div class="space-y-8 max-w-4xl mx-auto">
        <!-- Header Section -->
        <header>
            <h2 class="text-3xl font-bold tracking-tight text-slate-900">ตั้งค่าการใช้งาน</h2>
            <p class="mt-1 text-slate-500 text-lg font-medium">จัดการข้อมูลส่วนตัวและระบบการแจ้งเตือนของคุณ</p>
        </header>

        @if (session('success'))
            <div class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 flex items-center gap-3 text-emerald-700 font-bold text-sm shadow-sm">
                <i data-lucide="check-circle" class="h-5 w-5"></i>
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('staff.portal.settings.update') }}" method="POST" class="space-y-8">
            @csrf
            
            <!-- Profile Info (Read Only for now) -->
            <section class="glass-card rounded-[2rem] p-8 shadow-sm">
                <div class="flex items-center gap-3 mb-8 pb-5 border-b border-slate-50">
                    <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                        <i data-lucide="user" class="h-4 w-4"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 uppercase tracking-tighter">Personal Information</h3>
                </div>

                <div class="grid md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">Full Name</label>
                        <div class="h-12 w-full rounded-2xl bg-slate-50 border border-slate-100 px-4 flex items-center text-sm font-bold text-slate-700">
                            {{ $user->name }}
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">Email Address</label>
                        <div class="h-12 w-full rounded-2xl bg-slate-50 border border-slate-100 px-4 flex items-center text-sm font-bold text-slate-700">
                            {{ $user->email }}
                        </div>
                    </div>
                </div>
            </section>

            <!-- LINE Messaging Settings -->
            <section class="glass-card rounded-[2rem] p-8 shadow-sm border-l-4 border-l-emerald-500">
                <div class="flex items-center gap-3 mb-8 pb-5 border-b border-slate-50">
                    <div class="h-8 w-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <i data-lucide="message-circle" class="h-4 w-4"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 uppercase tracking-tighter">LINE Messaging</h3>
                </div>

                <div class="space-y-6">
                    <p class="text-sm text-slate-500 leading-relaxed">
                        เชื่อมต่อระบบแจ้งเตือนเข้ากับ LINE ของคุณเพื่อรับรายงานวันหมดอายุของแรงงานและสรุปงานประจำวันแบบอัตโนมัติ
                    </p>
                    
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">LINE User ID</label>
                        <input type="text" name="line_user_id" value="{{ $user->line_user_id }}"
                            placeholder="ใส่ LINE User ID ของคุณที่นี่ (เช่น Uxxxxxxxxxxxxx)"
                            class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-sm font-bold outline-none focus:border-emerald-400 focus:bg-white transition-all font-mono">
                    </div>

                    <div class="bg-blue-50 rounded-2xl p-5 border border-blue-100">
                        <h4 class="text-xs font-black uppercase text-blue-700 mb-2">วิธีหา LINE User ID:</h4>
                        <ol class="text-xs text-blue-800 space-y-1 list-decimal pl-4">
                            <li>เพิ่ม LINE Official Account ของบริษัทเป็นเพื่อนก่อน</li>
                            <li>ขอ LINE User ID จากผู้ดูแลระบบ (Admin) หรือดูได้จาก <a href="https://developers.line.biz/console/" target="_blank" class="underline font-bold">LINE Developers Console</a></li>
                            <li>LINE User ID จะขึ้นต้นด้วย "U" ตามด้วยตัวอักษร 32 ตัว</li>
                        </ol>
                    </div>
                </div>
            </section>

            <!-- Email Notification Settings -->
            <section class="glass-card rounded-[2rem] p-8 shadow-sm">
                <div class="flex items-center gap-3 mb-8 pb-5 border-b border-slate-50">
                    <div class="h-8 w-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                        <i data-lucide="mail" class="h-4 w-4"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 uppercase tracking-tighter">Email Preferences</h3>
                </div>

                <div class="space-y-6">
                    <label class="group flex items-center gap-4 cursor-pointer">
                        <input type="hidden" name="enable_email_notifications" value="0">
                        <div class="relative">
                            <input type="checkbox" name="enable_email_notifications" value="1" @checked($user->enable_email_notifications)
                                class="peer sr-only">
                            <div class="h-7 w-12 rounded-full bg-slate-200 transition-colors peer-checked:bg-blue-600"></div>
                            <div class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white transition-transform peer-checked:translate-x-5 shadow-sm"></div>
                        </div>
                        <div class="min-w-0">
                            <span class="block text-sm font-black uppercase tracking-widest text-slate-700 group-hover:text-blue-600 transition-colors">Daily Email Summary</span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">รับอีเมลสรุปภาพรวมธุรกิจและยอดเงินทุกเช้า</span>
                        </div>
                    </label>
                </div>
            </section>

            <div class="flex items-center justify-end gap-4 pb-12">
                <button type="submit"
                    class="h-12 px-10 flex items-center justify-center gap-3 rounded-2xl bg-slate-900 text-xs font-black uppercase tracking-[0.2em] text-white shadow-xl shadow-slate-900/20 hover:bg-blue-600 transition-all focus:ring-4 focus:ring-blue-100">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    Save Settings
                </button>
            </div>
        </form>
    </div>
@endsection
