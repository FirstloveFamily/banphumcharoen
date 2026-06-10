@php($title = 'ข่าวสารกิจกรรม')
@extends('layouts.app')

@push('head')
<style>
    .hero-gradient {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 25%, #3b82f6 50%, #dc2626 75%, #991b1b 100%);
    }

    .news-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .news-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
    }

    .news-card:hover .card-image {
        transform: scale(1.05);
    }

    .card-image {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .category-btn {
        transition: all 0.3s ease;
    }

    .category-btn:hover:not(.active) {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .category-btn.active {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
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

        <div class="max-w-7xl mx-auto px-4 text-center text-white sm:px-6 lg:px-8 relative z-10">
            <p class="text-sm font-semibold text-blue-200 uppercase tracking-wider">BANPHUMCHAROEN NEWS & ACTIVITIES</p>
            <h1 class="mt-4 text-5xl lg:text-6xl font-bold leading-tight tracking-tight">ข่าวสารกิจกรรม</h1>
            <p class="mx-auto mt-6 max-w-3xl text-xl leading-8 text-white/90">
                อัปเดตข่าวสาร กิจกรรมบริษัท และข้อมูลสำคัญด้านเอกสารแรงงานต่างด้าวสำหรับนายจ้างและองค์กรธุรกิจ
            </p>
        </div>
    </section>

    <!-- News Section -->
    <section class="bg-slate-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-6 border-b border-slate-200 pb-8 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-blue-950">อัปเดตล่าสุดจากบริษัท</h2>
                    <p class="mt-3 text-base leading-7 text-slate-600">
                        รวมประกาศ ข่าวกิจกรรม และบทความที่ช่วยให้การบริหารแรงงานต่างด้าวเป็นเรื่องง่ายขึ้น
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('news.index') }}"
                        class="category-btn {{ $categorySlug === '' ? 'active text-white' : 'bg-white text-slate-700 border border-slate-200 hover:border-blue-200 hover:text-blue-700' }} rounded-full px-5 py-2.5 text-sm font-semibold">
                        ทั้งหมด
                    </a>
                    @foreach ($categories as $category)
                        <a href="{{ route('news.index', ['category' => $category->slug]) }}"
                            class="category-btn {{ $categorySlug === $category->slug ? 'active text-white' : 'bg-white text-slate-700 border border-slate-200 hover:border-blue-200 hover:text-blue-700' }} rounded-full px-5 py-2.5 text-sm font-semibold">
                            {{ $category->name }}
                            <span class="ml-1 text-xs opacity-70">{{ $category->posts_count }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            @if ($featuredPost && $categorySlug === '')
                <article class="news-card mt-10 overflow-hidden rounded-3xl border border-white/20 bg-white shadow-2xl animate-fade-in-up">
                    <div class="grid grid-cols-1 lg:grid-cols-2">
                        <a href="{{ route('news.show', $featuredPost->slug) }}" class="block h-80 bg-slate-200 lg:h-auto overflow-hidden">
                            <img src="{{ $featuredPost->image_cover ? asset('storage/' . $featuredPost->image_cover) : 'https://images.unsplash.com/photo-1560264280-88b68371db39?auto=format&fit=crop&w=1400&q=85' }}"
                                alt="{{ $featuredPost->title }}" class="card-image h-full w-full object-cover">
                        </a>
                        <div class="flex items-center p-8 sm:p-10 lg:p-12">
                            <div>
                                <div class="flex flex-wrap items-center gap-3 text-sm">
                                    <span class="rounded-full bg-blue-50 px-4 py-2 font-semibold text-blue-700">ข่าวเด่น</span>
                                    @if ($featuredPost->category)
                                        <span class="text-slate-500 font-medium">{{ $featuredPost->category->name }}</span>
                                    @endif
                                    <span class="text-slate-400 font-medium">{{ $featuredPost->published_at?->format('d/m/Y') }}</span>
                                </div>
                                <h3 class="mt-5 text-2xl lg:text-3xl font-bold leading-snug text-blue-950">
                                    <a href="{{ route('news.show', $featuredPost->slug) }}" class="hover:text-blue-700">
                                        {{ $featuredPost->title }}
                                    </a>
                                </h3>
                                <p class="mt-4 text-base leading-7 text-slate-600">
                                    {{ $featuredPost->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($featuredPost->content), 220) }}
                                </p>
                                <a href="{{ route('news.show', $featuredPost->slug) }}"
                                    class="btn-primary mt-6 inline-flex items-center gap-3 rounded-full px-6 py-3 text-base font-semibold text-white shadow-lg">
                                    อ่านรายละเอียด
                                    <i data-lucide="arrow-right" class="h-5 w-5"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            @endif

            <div class="mt-10 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($newsPosts as $index => $post)
                    <article class="news-card flex overflow-hidden rounded-3xl border border-white/20 bg-white shadow-xl">
                        <div class="flex w-full flex-col">
                            <a href="{{ route('news.show', $post->slug) }}" class="block aspect-[16/10] bg-slate-200 overflow-hidden">
                                <img src="{{ $post->image_cover ? asset('storage/' . $post->image_cover) : 'https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=1200&q=85' }}"
                                    alt="{{ $post->title }}" class="card-image h-full w-full object-cover">
                            </a>
                            <div class="flex flex-1 flex-col p-6">
                                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                    @if ($post->category)
                                        <span class="rounded-full bg-blue-50 px-3 py-1.5 font-semibold text-blue-700">{{ $post->category->name }}</span>
                                    @endif
                                    <span class="font-medium">{{ $post->published_at?->format('d/m/Y') }}</span>
                                </div>
                                <h3 class="mt-4 text-lg font-bold leading-snug text-blue-950">
                                    <a href="{{ route('news.show', $post->slug) }}" class="hover:text-blue-700">{{ $post->title }}</a>
                                </h3>
                                <p class="mt-3 flex-1 text-base leading-7 text-slate-600">
                                    {{ \Illuminate\Support\Str::limit($post->excerpt ?: strip_tags($post->content), 130) }}
                                </p>
                                <a href="{{ route('news.show', $post->slug) }}"
                                    class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-blue-700 hover:text-blue-900">
                                    อ่านเพิ่มเติม
                                    <i data-lucide="arrow-right" class="h-4 w-4"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-3xl border border-dashed border-slate-200 bg-white px-8 py-16 text-center shadow-xl">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-blue-700 shadow-lg">
                            <i data-lucide="newspaper" class="h-8 w-8"></i>
                        </div>
                        <h3 class="mt-6 text-2xl font-bold text-blue-950">ยังไม่มีข่าวสารในขณะนี้</h3>
                        <p class="mt-3 text-base text-slate-500">เมื่อมีข่าวหรือกิจกรรมใหม่ ระบบจะแสดงที่หน้านี้อัตโนมัติ</p>
                    </div>
                @endforelse
            </div>

            @if ($newsPosts->hasPages())
                <div class="mt-12">
                    {{ $newsPosts->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
