@extends('layouts.staff-portal', ['title' => 'คำขอเพิ่มแรงงาน', 'pageTitle' => 'คำขอเพิ่มแรงงาน'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div><h2 class="text-3xl font-bold text-slate-900">คำขอเพิ่มแรงงาน</h2><p class="mt-1 text-slate-500">ตรวจสอบข้อมูลก่อนสร้างแรงงานเข้าระบบ</p></div>
        <div class="flex gap-2">@foreach(['pending' => 'รอตรวจสอบ', 'approved' => 'อนุมัติแล้ว', 'rejected' => 'ตีกลับ', '' => 'ทั้งหมด'] as $value => $label)<a href="{{ route('staff.portal.worker-registration-requests.index', ['status' => $value]) }}" class="rounded-2xl px-4 py-2 text-sm font-bold {{ $status === $value ? 'bg-blue-100 text-blue-700' : 'bg-white text-slate-500 border border-slate-200' }}">{{ $label }}</a>@endforeach</div>
    </div>
    <div class="space-y-4">
        @forelse($registrationRequests as $registrationRequest)
            @php($data = $registrationRequest->data ?? [])
            <article class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
                <div class="flex flex-col justify-between gap-4 md:flex-row"><div><h3 class="text-lg font-bold text-slate-900">{{ trim(($data['first_name_th'] ?? '') . ' ' . ($data['last_name_th'] ?? '')) ?: '-' }}</h3><p class="text-sm text-slate-500">{{ trim(($data['first_name_en'] ?? '') . ' ' . ($data['last_name_en'] ?? '')) }} · {{ $registrationRequest->employer?->company_name }}</p><p class="mt-2 text-xs text-slate-400">ส่งคำขอเมื่อ {{ $registrationRequest->created_at?->format('d/m/Y H:i') }} น. โดย {{ $registrationRequest->requester?->name ?: '-' }}</p></div><span class="h-fit rounded-full px-3 py-1 text-xs font-bold {{ ['pending' => 'bg-amber-50 text-amber-700', 'approved' => 'bg-emerald-50 text-emerald-700', 'rejected' => 'bg-rose-50 text-rose-700'][$registrationRequest->status] ?? 'bg-slate-100 text-slate-600' }}">{{ ['pending' => 'รอตรวจสอบ', 'approved' => 'อนุมัติแล้ว', 'rejected' => 'ตีกลับ'][$registrationRequest->status] ?? $registrationRequest->status }}</span></div>
                <div class="mt-5 grid gap-3 text-sm text-slate-600 md:grid-cols-4"><div>สัญชาติ: {{ $nationalityNames->get($data['nationality_id'] ?? null, '-') }}</div><div>วันเกิด: {{ $data['birth_date'] ?? '-' }}</div><div>Passport: {{ $data['passport_number'] ?? '-' }}</div><div>Work Permit: {{ $data['wp_number'] ?? '-' }}</div></div>
                @if($registrationRequest->status === 'pending')
                    <div class="mt-6 flex flex-col gap-4 border-t border-slate-100 pt-5 md:flex-row md:items-end md:justify-between"><form action="{{ route('staff.portal.worker-registration-requests.approve', $registrationRequest) }}" method="POST">@csrf<button class="rounded-2xl bg-emerald-600 px-6 py-3 font-bold text-white">อนุมัติและสร้างแรงงาน</button></form><form action="{{ route('staff.portal.worker-registration-requests.reject', $registrationRequest) }}" method="POST" class="flex flex-1 gap-2 md:max-w-xl">@csrf<input name="review_note" required placeholder="เหตุผลที่ตีกลับ" class="h-12 min-w-0 flex-1 rounded-2xl border border-slate-200 px-4"><button class="rounded-2xl bg-rose-600 px-6 py-3 font-bold text-white">ตีกลับ</button></form></div>
                @elseif($registrationRequest->review_note)
                    <p class="mt-4 rounded-2xl bg-rose-50 p-4 text-sm text-rose-700">เหตุผล: {{ $registrationRequest->review_note }}</p>
                @endif
            </article>
        @empty
            <div class="rounded-3xl bg-white p-16 text-center text-slate-500">ไม่มีคำขอในสถานะนี้</div>
        @endforelse
    </div>
    {{ $registrationRequests->links() }}
</div>
@endsection
