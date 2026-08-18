@php($title = 'ขอเพิ่มแรงงาน')
@extends('layouts.app')

@section('content')
<section class="bg-slate-50 py-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <a href="{{ route('employers.workers.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-900">
            <i data-lucide="arrow-left" class="h-4 w-4"></i> กลับรายชื่อแรงงาน
        </a>
        <div class="mt-6 rounded-3xl bg-white p-8 shadow-xl">
            <h1 class="text-3xl font-bold text-blue-950">ขอเพิ่มแรงงานใหม่</h1>
            <p class="mt-2 text-slate-500">ข้อมูลจะถูกส่งให้เจ้าหน้าที่ตรวจสอบก่อนสร้างเข้าระบบ</p>

            @if ($errors->any())
                <div class="mt-6 rounded-2xl bg-rose-50 p-4 text-sm text-rose-700">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('employers.workers.request.store') }}" method="POST" enctype="multipart/form-data" class="mt-8 space-y-8">
                @csrf
                <div class="grid gap-6 md:grid-cols-2">
                    <div><label class="text-sm font-semibold text-slate-700">ชื่อ (ไทย) *</label><input name="first_name_th" value="{{ old('first_name_th') }}" required class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4"></div>
                    <div><label class="text-sm font-semibold text-slate-700">นามสกุล (ไทย)</label><input name="last_name_th" value="{{ old('last_name_th') }}" class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4"></div>
                    <div><label class="text-sm font-semibold text-slate-700">ชื่อ (อังกฤษ) *</label><input name="first_name_en" value="{{ old('first_name_en') }}" required class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4"></div>
                    <div><label class="text-sm font-semibold text-slate-700">นามสกุล (อังกฤษ)</label><input name="last_name_en" value="{{ old('last_name_en') }}" class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4"></div>
                    <div><label class="text-sm font-semibold text-slate-700">สัญชาติ *</label><select name="nationality_id" required class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4"><option value="">เลือกสัญชาติ</option>@foreach($nationalities as $nationality)<option value="{{ $nationality->id }}" @selected(old('nationality_id') == $nationality->id)>{{ $nationality->name_th }}</option>@endforeach</select></div>
                    <div><label class="text-sm font-semibold text-slate-700">วันเกิด *</label><input type="date" name="birth_date" value="{{ old('birth_date') }}" required class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4"></div>
                    <div><label class="text-sm font-semibold text-slate-700">เพศ</label><input name="gender" value="{{ old('gender') }}" class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4"></div>
                    <div><label class="text-sm font-semibold text-slate-700">เลขที่ Passport</label><input name="passport_number" value="{{ old('passport_number') }}" class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4"></div>
                    <div><label class="text-sm font-semibold text-slate-700">วันหมดอายุ Passport</label><input type="date" name="passport_expiry" value="{{ old('passport_expiry') }}" class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4"></div>
                    <div><label class="text-sm font-semibold text-slate-700">เลขบัตรชมพู</label><input name="pink_card_number" value="{{ old('pink_card_number') }}" class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4"></div>
                    <div><label class="text-sm font-semibold text-slate-700">วันหมดอายุบัตรชมพู</label><input type="date" name="pink_card_expiry" value="{{ old('pink_card_expiry') }}" class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4"></div>
                    <div><label class="text-sm font-semibold text-slate-700">เลขที่ Work Permit</label><input name="wp_number" value="{{ old('wp_number') }}" class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4"></div>
                    <div><label class="text-sm font-semibold text-slate-700">วันหมดอายุ Work Permit</label><input type="date" name="wp_expiry" value="{{ old('wp_expiry') }}" class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4"></div>
                    <div><label class="text-sm font-semibold text-slate-700">วันหมดอายุ Visa</label><input type="date" name="visa_expiry" value="{{ old('visa_expiry') }}" class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4"></div>
                    <div><label class="text-sm font-semibold text-slate-700">วันครบกำหนด 90 วัน</label><input type="date" name="report_90_days_due" value="{{ old('report_90_days_due') }}" class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4"></div>
                </div>
                <div class="grid gap-6 md:grid-cols-2">
                    @foreach(['passport_file' => 'ไฟล์ Passport', 'pink_card_file' => 'ไฟล์บัตรชมพู', 'wp_file' => 'ไฟล์ Work Permit', 'visa_file' => 'ไฟล์ Visa', 'report_90_days_file' => 'ไฟล์ 90 วัน', 'photo_file' => 'รูปถ่าย'] as $field => $label)
                        <div><label class="text-sm font-semibold text-slate-700">{{ $label }}</label><input type="file" name="{{ $field }}" class="mt-2 block w-full rounded-2xl border border-slate-200 p-3 text-sm"></div>
                    @endforeach
                </div>
                <div><label class="text-sm font-semibold text-slate-700">หมายเหตุ</label><textarea name="note" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 p-4">{{ old('note') }}</textarea></div>
                <div class="flex justify-end gap-3"><a href="{{ route('employers.workers.index') }}" class="rounded-2xl border border-slate-200 px-6 py-3 font-semibold text-slate-600">ยกเลิก</a><button class="rounded-2xl bg-blue-900 px-7 py-3 font-semibold text-white">ส่งคำขอให้ตรวจสอบ</button></div>
            </form>
        </div>
    </div>
</section>
@endsection
