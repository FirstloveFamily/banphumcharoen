@extends('layouts.manager', ['title' => $document ? 'แก้ไขเอกสาร' : 'เพิ่มเอกสาร', 'pageTitle' => $document ? 'แก้ไขเอกสาร' : 'เพิ่มเอกสาร'])

@push('head')
    <style>
        .hero-gradient {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 30%, #2563eb 60%, #dc2626 100%);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .animate-delay-100 {
            animation-delay: 0.1s;
        }
    </style>
@endpush

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="hero-gradient rounded-lg p-8 text-white mb-8 shadow-2xl animate-fade-in-up">
            <h1 class="text-3xl font-bold tracking-tight">
                {{ $document ? 'แก้ไขข้อมูลเอกสาร' : 'เพิ่มเอกสารใหม่' }}
            </h1>
            <p class="mt-2 text-white/90">
                {{ $document ? 'อัปเดตแรงงาน ประเภทเอกสาร ไฟล์ และวันหมดอายุ' : 'แนบไฟล์เอกสารให้แรงงานและกำหนดวันหมดอายุ' }}
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-8 animate-fade-in-up animate-delay-100">
            <form action="{{ $document ? route('manager.documents.update', $document) : route('manager.documents.store') }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @if ($document)
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">แรงงาน <span class="text-rose-500">*</span></label>
                        <select name="worker_id"
                            class="w-full px-4 py-2 border @error('worker_id') border-rose-500 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-[#c9a227]/30"
                            required>
                            <option value="">-- เลือกแรงงาน --</option>
                            @foreach ($workers as $worker)
                                <option value="{{ $worker->id }}" @selected(old('worker_id', $document?->worker_id) == $worker->id)>
                                    {{ $worker->full_name_th ?: $worker->full_name_en }}{{ $worker->passport_number ? ' - ' . $worker->passport_number : '' }}{{ $worker->employer ? ' (' . $worker->employer->company_name . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('worker_id')
                            <p class="text-rose-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">ประเภทเอกสาร <span class="text-rose-500">*</span></label>
                        <select name="document_master_id"
                            class="w-full px-4 py-2 border @error('document_master_id') border-rose-500 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-[#c9a227]/30"
                            required>
                            <option value="">-- เลือกประเภทเอกสาร --</option>
                            @foreach ($documentMasters as $id => $name)
                                <option value="{{ $id }}" @selected(old('document_master_id', $document?->document_master_id) == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('document_master_id')
                            <p class="text-rose-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">วันหมดอายุ</label>
                        <input type="date" name="expiry_date"
                            value="{{ old('expiry_date', $document?->expiry_date?->format('Y-m-d')) }}"
                            class="w-full px-4 py-2 border @error('expiry_date') border-rose-500 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-[#c9a227]/30">
                        @error('expiry_date')
                            <p class="text-rose-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            ไฟล์เอกสาร @if (!$document)
                                <span class="text-rose-500">*</span>
                            @endif
                        </label>
                        <input type="file" name="file_path" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                            class="w-full px-4 py-2 border @error('file_path') border-rose-500 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-[#c9a227]/30"
                            @required(!$document)>
                        <p class="text-xs text-slate-500 mt-1">รองรับ PDF, JPG, PNG, DOC, DOCX (สูงสุด 5MB)</p>
                        @error('file_path')
                            <p class="text-rose-500 text-sm mt-1">{{ $message }}</p>
                        @enderror

                        @if ($document?->file_path)
                            <a href="{{ Storage::url($document->file_path) }}" target="_blank"
                                class="inline-flex items-center gap-1 mt-2 text-sm text-[#0b2f52] hover:text-[#c9a227] font-medium">
                                <i data-lucide="download" class="h-4 w-4"></i> เปิดไฟล์ปัจจุบัน
                            </a>
                        @endif
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">หมายเหตุ</label>
                        <textarea name="note" rows="4"
                            class="w-full px-4 py-2 border @error('note') border-rose-500 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-[#c9a227]/30"
                            placeholder="รายละเอียดเพิ่มเติม">{{ old('note', $document?->note) }}</textarea>
                        @error('note')
                            <p class="text-rose-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex gap-3 justify-end">
                    <a href="{{ route('manager.documents.index') }}"
                        class="px-6 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition font-medium">
                        ยกเลิก
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-[#0b2f52] text-white rounded-lg hover:bg-[#123e68] transition font-medium">
                        {{ $document ? 'บันทึกการแก้ไข' : 'เพิ่มเอกสาร' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
@endsection
