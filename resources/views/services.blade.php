@php($title = 'บริการของเรา')
@extends('layouts.app')

@push('head')
<style>
    .hero-gradient {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 25%, #3b82f6 50%, #dc2626 75%, #991b1b 100%);
    }

    .service-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .service-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
    }

    .service-card:hover .icon-container {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        transform: scale(1.1);
    }

    .service-card:hover .icon-container svg {
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

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center text-white">
            <h1 class="text-5xl lg:text-6xl font-bold mb-6 leading-tight tracking-tight">บริการของเรา</h1>
            <p class="text-xl lg:text-2xl text-white/90 font-medium max-w-3xl mx-auto leading-relaxed">
                บริการจัดการเอกสารและสิทธิของแรงงานต่างด้าวแบบครบวงจร ถูกต้องตามกฎหมาย
                รวดเร็ว และตรวจสอบสถานะได้ตลอด 24 ชั่วโมง
            </p>
        </div>
    </section>

    <!-- Services Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 relative z-20 pb-20">
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($services as $index => $service)
                <div class="service-card bg-white rounded-3xl shadow-xl border border-white/20 p-8 flex flex-col justify-between animate-fade-in-up {{ $index === 0 ? 'animate-delay-100' : ($index === 1 ? 'animate-delay-200' : 'animate-delay-300') }}">
                    <div>
                        <!-- Icon Container -->
                        <div class="icon-container w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 mx-auto mb-6 shadow-lg">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>

                        <!-- Service Title -->
                        <h3 class="text-2xl font-bold text-gray-900 mb-4 text-center">
                            {{ $service->name }}
                        </h3>

                        <!-- Service Description -->
                        @if ($service->description)
                            <p class="text-gray-600 text-base leading-relaxed mb-6 text-center">
                                {{ \Illuminate\Support\Str::limit($service->description, 140) }}
                            </p>
                        @endif
                    </div>

                    <!-- Read More Button -->
                    <div>
                        <a href="{{ route('services.show', $service->code) }}"
                            class="btn-primary inline-flex items-center justify-center w-full py-3.5 px-6 rounded-2xl text-base font-semibold text-white shadow-lg">
                            ดูรายละเอียดเพิ่มเติม
                            <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-3 bg-white rounded-2xl shadow-xl border border-gray-100 p-12 text-center">
                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </div>
                    <p class="text-gray-600 text-lg font-medium">ยังไม่มีบริการในขณะนี้</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
