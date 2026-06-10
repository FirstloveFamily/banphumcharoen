@php($title = 'สำหรับนายจ้าง')
@extends('layouts.app')

@push('head')
<style>
    .hero-gradient {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 25%, #3b82f6 50%, #dc2626 75%, #991b1b 100%);
    }

    .feature-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .feature-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
    }

    .feature-card:hover .icon-container {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        transform: scale(1.1);
    }

    .feature-card:hover .icon-container i {
        color: white;
    }

    .icon-container {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-primary {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(30, 58, 138, 0.3);
    }

    .btn-white {
        transition: all 0.3s ease;
    }

    .btn-white:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-up {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
    }

    .animate-delay-100 {
        animation-delay: 0.1s;
    }

    .animate-delay-200 {
        animation-delay: 0.2s;
    }

    .animate-delay-300 {
        animation-delay: 0.3s;
    }
</style>
@endpush

@section('content')
    <!-- Hero Section -->
    <section class="hero-gradient py-20 lg:py-28 relative overflow-hidden">
        <!-- Decorative Elements -->
        <div class="absolute inset-0 opacity-20 pointer-events-none">
            <div class="absolute -left-40 -top-40 h-[600px] w-[600px] rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute bottom-[-12rem] right-[-10rem] h-[500px] w-[500px] rounded-full bg-red-500/20 blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[700px] w-[700px] rounded-full bg-blue-400/10 blur-3xl"></div>
        </div>

        <!-- Grid Pattern -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,.04)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.04)_1px,transparent_1px)] bg-[size:60px_60px] opacity-30"></div>

        <div class="max-w-7xl mx-auto px-4 text-white sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 gap-12 lg:grid-cols-12 lg:items-center">
                <div class="lg:col-span-7 animate-fade-in-up">
                    <p class="text-sm font-semibold text-blue-200 uppercase tracking-wider">EMPLOYER PORTAL</p>
                    <h1 class="mt-4 text-5xl lg:text-6xl font-bold leading-tight">
                        สำหรับนายจ้างที่ต้องการติดตามงานแรงงานต่างด้าวอย่างเป็นระบบ
                    </h1>
                    <p class="mt-6 max-w-3xl text-xl leading-8 text-white/90">
                        แจ้งงานใหม่ ส่งเอกสาร อัปโหลดหลักฐานชำระเงิน และติดตามความคืบหน้าของแต่ละใบงานได้จากที่เดียว
                    </p>
                    <div class="mt-10 flex flex-col gap-4 sm:flex-row">
                        <a href="{{ route('login') }}"
                            class="btn-white inline-flex h-14 items-center justify-center gap-3 rounded-2xl bg-white px-8 text-base font-semibold text-blue-900 shadow-lg">
                            <i data-lucide="log-in" class="h-5 w-5"></i>
                            เข้าสู่ระบบนายจ้าง
                        </a>
                        <a href="{{ route('status.index') }}"
                            class="inline-flex h-14 items-center justify-center gap-3 rounded-2xl border border-white/30 px-8 text-base font-semibold text-white transition hover:bg-white/10 hover:shadow-lg">
                            <i data-lucide="search-check" class="h-5 w-5"></i>
                            ตรวจสอบสถานะงาน
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-5 animate-fade-in-up animate-delay-100">
                    <div class="rounded-3xl bg-white p-8 text-slate-800 shadow-2xl border border-white/20">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-6">
                            <div>
                                <p class="text-sm text-slate-500 font-medium uppercase tracking-wider">ภาพรวมพอร์ทัล</p>
                                <p class="mt-2 text-2xl font-bold text-blue-950">ติดตามงานครบวงจร</p>
                            </div>
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-700 shadow-md">
                                <i data-lucide="layout-dashboard" class="h-7 w-7"></i>
                            </div>
                        </div>
                        <div class="mt-6 grid grid-cols-2 gap-4">
                            <div class="rounded-2xl bg-slate-50 p-5 border border-slate-100">
                                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">งานที่ติดตาม</p>
                                <p class="mt-3 text-xl font-bold text-blue-950">สถานะสด</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-5 border border-slate-100">
                                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">เอกสาร</p>
                                <p class="mt-3 text-xl font-bold text-blue-950">อัปโหลดได้</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-5 border border-slate-100">
                                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">การชำระเงิน</p>
                                <p class="mt-3 text-xl font-bold text-blue-950">เช็กคงเหลือ</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-5 border border-slate-100">
                                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">แจ้งเตือน</p>
                                <p class="mt-3 text-xl font-bold text-blue-950">วันหมดอายุ</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="bg-slate-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['icon' => 'send', 'title' => 'แจ้งงานใหม่', 'text' => 'เลือกแรงงานและบริการที่ต้องการดำเนินการ พร้อมรับเลขงานทันที'],
                    ['icon' => 'file-up', 'title' => 'ส่งเอกสาร', 'text' => 'อัปโหลดเอกสารประกอบงานและดูรายการที่ยังขาดได้ชัดเจน'],
                    ['icon' => 'receipt', 'title' => 'ส่งสลิป', 'text' => 'ส่งหลักฐานชำระเงินให้เจ้าหน้าที่ตรวจสอบและอัปเดตยอดคงเหลือ'],
                    ['icon' => 'bell-ring', 'title' => 'ติดตามผล', 'text' => 'ดู timeline ความคืบหน้าและวันหมดอายุของเอกสารสำคัญ'],
                ] as $index => $item)
                    <div class="feature-card rounded-3xl border border-white/20 bg-white p-8 shadow-xl animate-fade-in-up {{ $index === 0 ? 'animate-delay-100' : ($index === 1 ? 'animate-delay-200' : ($index === 2 ? 'animate-delay-300' : '')) }}">
                        <div class="icon-container h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-700 shadow-md flex">
                            <i data-lucide="{{ $item['icon'] }}" class="h-7 w-7"></i>
                        </div>
                        <h2 class="mt-6 text-xl font-bold text-blue-950">{{ $item['title'] }}</h2>
                        <p class="mt-3 text-base leading-7 text-slate-600">{{ $item['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Workflow Section -->
    <section class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-12 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <p class="text-sm font-semibold text-blue-700 uppercase tracking-wider">WORKFLOW</p>
                    <h2 class="mt-3 text-3xl font-bold text-blue-950">ขั้นตอนการใช้งานสำหรับบริษัทนายจ้าง</h2>
                    <p class="mt-4 text-base leading-8 text-slate-600">
                        ระบบออกแบบให้ฝ่าย HR หรือผู้ประสานงานของบริษัทเห็นสถานะงานแต่ละรายการได้เร็ว ลดการตามเอกสารผ่านหลายช่องทาง
                    </p>
                    <div class="mt-8">
                        <a href="{{ route('login') }}"
                            class="btn-primary inline-flex h-14 items-center justify-center gap-3 rounded-2xl px-8 text-base font-semibold text-white shadow-lg">
                            <i data-lucide="user-round-check" class="h-5 w-5"></i>
                            เข้าใช้งานพอร์ทัล
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-7">
                    <div class="space-y-5">
                        @foreach ([
                            ['step' => '01', 'title' => 'เข้าสู่ระบบด้วยบัญชีนายจ้าง', 'text' => 'บัญชีของบริษัทจะเห็นเฉพาะแรงงานและงานของบริษัทตัวเอง'],
                            ['step' => '02', 'title' => 'สร้างใบงานหรือเปิดงานที่มีอยู่', 'text' => 'เลือกบริการ เช่น ต่อวีซ่า ต่อใบอนุญาตทำงาน หรือรายงาน 90 วัน'],
                            ['step' => '03', 'title' => 'อัปโหลดเอกสารและสลิป', 'text' => 'เจ้าหน้าที่ตรวจสอบเอกสารและการชำระเงินก่อนอัปเดตสถานะ'],
                            ['step' => '04', 'title' => 'ติดตามสถานะจนจบงาน', 'text' => 'ดูงานที่รอเอกสาร รอชำระเงิน กำลังดำเนินการ และเสร็จสิ้น'],
                        ] as $index => $item)
                            <div class="flex gap-5 rounded-3xl border border-slate-100 bg-white p-6 shadow-md hover:shadow-lg transition">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-base font-bold text-blue-700 shadow-md">
                                    {{ $item['step'] }}
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg text-blue-950">{{ $item['title'] }}</h3>
                                    <p class="mt-2 text-base leading-7 text-slate-600">{{ $item['text'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="bg-slate-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-10 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-blue-700 uppercase tracking-wider">SERVICES</p>
                    <h2 class="mt-3 text-3xl font-bold text-blue-950">บริการที่นายจ้างใช้บ่อย</h2>
                </div>
                <a href="{{ route('services.index') }}" class="text-base font-semibold text-blue-700 hover:text-blue-900 flex items-center gap-2 group">
                    ดูบริการทั้งหมด
                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                @forelse ($services as $index => $service)
                    <a href="{{ route('services.show', $service->code) }}"
                        class="feature-card rounded-3xl border border-white/20 bg-white p-8 shadow-xl animate-fade-in-up {{ $index === 0 ? 'animate-delay-100' : ($index === 1 ? 'animate-delay-200' : ($index === 2 ? 'animate-delay-300' : '')) }}">
                        <div class="icon-container h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-700 shadow-md flex">
                            <i data-lucide="file-check-2" class="h-7 w-7"></i>
                        </div>
                        <h3 class="mt-6 font-bold text-lg text-blue-950">{{ $service->name }}</h3>
                        <p class="mt-3 text-base leading-7 text-slate-600">
                            {{ \Illuminate\Support\Str::limit($service->description ?: 'ดูรายละเอียดเอกสารและขั้นตอนบริการนี้', 90) }}
                        </p>
                    </a>
                @empty
                    <div class="rounded-3xl border border-white/20 bg-white p-10 text-center shadow-xl md:col-span-2 lg:col-span-4">
                        <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="file-x" class="w-8 h-8 text-gray-400"></i>
                        </div>
                        <p class="text-lg text-gray-600 font-medium">ยังไม่มีบริการในขณะนี้</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
