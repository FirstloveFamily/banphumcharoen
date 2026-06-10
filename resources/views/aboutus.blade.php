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

    .value-card {
        transition: all 0.3s ease;
    }

    .value-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }

    .value-card:hover .value-icon {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        transform: scale(1.05);
    }

    .value-card:hover .value-icon i {
        color: white;
    }

    .value-icon {
        transition: all 0.3s ease;
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

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white relative z-10">
            <h1 class="text-5xl lg:text-6xl font-bold mb-6 leading-tight tracking-tight">เกี่ยวกับเรา</h1>
            <p class="text-xl lg:text-2xl text-white/90 font-medium max-w-3xl mx-auto leading-relaxed">
                ผู้เชี่ยวชาญด้านเอกสารแรงงานต่างด้าวและการประสานงานตรวจลงตรา (TM) ในประเทศไทย
            </p>
        </div>
    </section>

    <!-- About Section -->
    <section class="bg-slate-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-3xl border border-white/20 bg-white shadow-2xl">
                <div class="grid grid-cols-1 lg:grid-cols-2">
                    <div class="flex items-center p-8 sm:p-10 lg:p-12">
                        <div>
                            <h2 class="text-3xl lg:text-4xl font-bold leading-snug text-blue-950 mb-6">
                                เราเชี่ยวชาญ บริการครบวงจร
                                <span class="block text-blue-600 mt-2">ด้านเอกสารแรงงานต่างด้าวและการประสานงาน (TM)</span>
                            </h2>
                            <p class="mt-6 text-base leading-8 text-slate-700">
                                เรามุ่งมั่นให้บริการที่ถูกต้อง รวดเร็ว และเชื่อถือได้ เพื่อช่วยให้นายจ้างและองค์กรธุรกิจ
                                ดำเนินการจ้างแรงงานต่างด้าวได้อย่างถูกต้องตามกฎหมาย ลดความยุ่งยาก ประหยัดเวลา
                                และเพิ่มประสิทธิภาพในการดำเนินธุรกิจ
                            </p>

                            <div class="mt-10 grid grid-cols-2 gap-6 sm:grid-cols-4">
                                @foreach (($featureBlocks ?? collect([
                                    ['title' => 'ถูกต้องตามกฎหมาย', 'description' => 'ดำเนินการทุกขั้นตอนอย่างถูกต้องตามกฎหมายและระเบียบข้อบังคับ', 'icon' => 'shield-check'],
                                    ['title' => 'ทีมงานมืออาชีพ', 'description' => 'ดูแลโดยทีมงานที่มีประสบการณ์และเข้าใจงานเอกสารแรงงานเป็นอย่างดี', 'icon' => 'users'],
                                    ['title' => 'บริการครบวงจร', 'description' => 'รองรับงานเอกสารแรงงานต่างด้าวแบบครบวงจรตั้งแต่ต้นจนจบ', 'icon' => 'clipboard-list'],
                                    ['title' => 'ดูแลใกล้ชิดทุกขั้นตอน', 'description' => 'ติดตามงานอย่างใกล้ชิด เพื่อให้ลูกค้าอุ่นใจตลอดกระบวนการ', 'icon' => 'handshake'],
                                ])) as $index => $block)
                                    <div class="feature-card rounded-2xl bg-blue-50 p-6 text-center border border-blue-100 animate-fade-in-up {{ $index === 0 ? 'animate-delay-100' : ($index === 1 ? 'animate-delay-200' : ($index === 2 ? 'animate-delay-300' : '')) }}">
                                        <div class="icon-container mx-auto h-12 w-12 flex items-center justify-center rounded-xl bg-blue-100 text-blue-700 shadow-md">
                                            <i data-lucide="{{ data_get($block, 'icon', 'sparkles') }}" class="h-6 w-6"></i>
                                        </div>
                                        <p class="mt-4 text-sm font-semibold text-slate-900">{{ data_get($block, 'title') }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="h-96 lg:h-auto relative">
                        <img src="{{ Storage::url('images/logo.jpeg') }}"
                            alt="ทีมงานบริการเอกสารแรงงานต่างด้าว" class="h-full w-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-blue-900/20 to-transparent"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold text-blue-950">ค่านิยมหลักของเรา</h2>
                <p class="mt-4 text-base text-slate-500">หลักการทำงานที่เราใช้ดูแลลูกค้าทุกงาน</p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                @foreach (($valueBlocks ?? collect([
                    ['title' => 'ถูกต้อง', 'description' => 'ดำเนินการทุกขั้นตอนอย่างถูกต้องตามกฎหมายและระเบียบข้อบังคับ เพื่อความปลอดภัยของลูกค้า', 'icon' => 'file-check-2'],
                    ['title' => 'รวดเร็ว', 'description' => 'เร่งรัดทุกขั้นตอนอย่างมีประสิทธิภาพ เพื่อให้ลูกค้าได้รับบริการที่รวดเร็วและทันต่อความต้องการ', 'icon' => 'timer'],
                    ['title' => 'เชื่อถือได้', 'description' => 'ยึดมั่นในความซื่อสัตย์ โปร่งใส และรับผิดชอบ พร้อมดูแลลูกค้าอย่างต่อเนื่อง', 'icon' => 'badge-check'],
                ])) as $index => $block)
                    <div class="value-card rounded-3xl border border-slate-100 bg-white p-8 shadow-xl animate-fade-in-up {{ $index === 0 ? 'animate-delay-100' : ($index === 1 ? 'animate-delay-200' : 'animate-delay-300') }}">
                        <div class="flex items-start gap-5">
                            <div class="value-icon flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-700 shadow-md">
                                <i data-lucide="{{ data_get($block, 'icon', 'badge-check') }}" class="h-7 w-7"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-blue-950 mb-3">{{ data_get($block, 'title') }}</h3>
                                <p class="text-base leading-7 text-slate-700">
                                    {{ data_get($block, 'description') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
