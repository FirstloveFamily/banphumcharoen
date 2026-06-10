@php($title = $newsPost->title)
@extends('layouts.app')

@push('head')
    <style>
        .news-content h2,
        .news-content h3 {
            color: #172554;
            font-weight: 700;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }

        .news-content p,
        .news-content li {
            color: #475569;
            line-height: 1.9;
        }

        .news-content ul,
        .news-content ol {
            margin: 1rem 0;
            padding-left: 1.5rem;
        }

        .news-content a {
            color: #1d4ed8;
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
    <section class="bg-bp-blue py-12">
        <div class="max-w-4xl mx-auto px-4 text-center text-white sm:px-6 lg:px-8">
            <div class="flex flex-wrap justify-center gap-3 text-sm text-blue-100">
                @if ($newsPost->category)
                    <span class="rounded-full bg-white/10 px-3 py-1">{{ $newsPost->category->name }}</span>
                @endif
                <span>{{ $newsPost->published_at?->format('d/m/Y') }}</span>
                <span>{{ number_format($newsPost->views_count) }} ครั้งที่อ่าน</span>
            </div>
            <h1 class="mt-4 text-3xl font-bold leading-snug">{{ $newsPost->title }}</h1>
            @if ($newsPost->excerpt)
                <p class="mt-4 text-lg leading-8 text-blue-100">{{ $newsPost->excerpt }}</p>
            @endif
        </div>
    </section>

    <article class="bg-slate-50 py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('news.index') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-blue-700 hover:text-blue-900">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                กลับไปหน้าข่าวสาร
            </a>

            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                <img src="{{ $newsPost->image_cover ? asset('storage/' . $newsPost->image_cover) : 'https://images.unsplash.com/photo-1560264280-88b68371db39?auto=format&fit=crop&w=1400&q=85' }}"
                    alt="{{ $newsPost->title }}" class="aspect-[16/8] w-full bg-slate-200 object-cover">

                <div class="news-content p-6 sm:p-8">
                    {!! $newsPost->content !!}
                </div>
            </div>

            @if ($relatedPosts->isNotEmpty())
                <section class="mt-10">
                    <h2 class="text-xl font-bold text-blue-950">ข่าวที่เกี่ยวข้อง</h2>
                    <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-3">
                        @foreach ($relatedPosts as $post)
                            <a href="{{ route('news.show', $post->slug) }}"
                                class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition hover:shadow-md">
                                <p class="text-xs text-slate-500">{{ $post->published_at?->format('d/m/Y') }}</p>
                                <h3 class="mt-2 text-sm font-bold leading-6 text-blue-950 hover:text-blue-700">
                                    {{ $post->title }}
                                </h3>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </article>
@endsection
