@extends('layouts.staff-portal', ['title' => 'การแจ้งเตือน', 'pageTitle' => 'ระบบแจ้งเตือนข่าวสาร'])

@push('head')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
    .unread-indicator {
        position: relative;
    }
    .unread-indicator::before {
        content: '';
        position: absolute;
        left: -1rem;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 70%;
        background-color: #3b82f6;
        border-radius: 0 4px 4px 0;
    }
</style>
@endpush

@section('content')
    <div class="space-y-8 max-w-5xl mx-auto">
        <!-- Header Section -->
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">การแจ้งเตือน</h2>
                <p class="mt-1 text-slate-500 text-lg">ติดตามความเคลื่อนไหวและรายการที่ต้องดำเนินการ</p>
            </div>
            @if(auth()->user()->notifications()->unread()->exists())
                <form action="{{ route('staff.portal.notifications.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 rounded-2xl bg-white border border-slate-200 px-5 py-2.5 text-xs font-bold text-slate-600 hover:text-blue-600 hover:border-blue-100 transition-all shadow-sm">
                        <i data-lucide="check-check" class="h-4 w-4"></i>
                        อ่านทั้งหมดแล้ว
                    </button>
                </form>
            @endif
        </header>

        <!-- Alerts -->
        @if (session('success'))
            <div class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 flex items-center gap-3 text-emerald-700 font-bold text-sm">
                <i data-lucide="check-circle" class="h-5 w-5"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Notifications List -->
        <section class="space-y-4">
            @forelse ($notifications as $notification)
                <article @class([
                    'glass-card rounded-3xl p-6 shadow-sm transition-all hover:shadow-md relative overflow-hidden',
                    'unread-indicator bg-blue-50/30' => ! $notification->is_read,
                    'opacity-80' => $notification->is_read,
                ])>
                    <div class="flex gap-6">
                        <div @class([
                            'h-12 w-12 shrink-0 rounded-2xl flex items-center justify-center transition-colors',
                            'bg-blue-600 text-white shadow-lg shadow-blue-500/20' => ! $notification->is_read,
                            'bg-slate-100 text-slate-400' => $notification->is_read,
                        ])>
                            <i data-lucide="{{ ! $notification->is_read ? 'bell-ring' : 'bell' }}" class="h-5 w-5"></i>
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2">
                                <h3 @class([
                                    'text-lg font-bold truncate',
                                    'text-slate-900' => ! $notification->is_read,
                                    'text-slate-600 font-semibold' => $notification->is_read,
                                ])>
                                    {{ $notification->title }}
                                </h3>
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 whitespace-nowrap">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                            </div>
                            
                            <p @class([
                                'mt-2 text-sm leading-relaxed',
                                'text-slate-600 font-medium' => ! $notification->is_read,
                                'text-slate-500' => $notification->is_read,
                            ])>
                                {{ $notification->message }}
                            </p>

                            <div class="mt-4 flex items-center gap-4">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">
                                    {{ $notification->created_at->format('d M Y · H:i') }}
                                </p>
                                
                                @if(! $notification->is_read)
                                    <div class="h-1 w-1 rounded-full bg-slate-300"></div>
                                    <form action="{{ route('staff.portal.notifications.read', $notification) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-[10px] font-black uppercase text-blue-600 hover:text-blue-800 transition-colors">
                                            ทำเครื่องหมายว่าอ่านแล้ว
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="glass-card rounded-3xl py-24 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-50 text-slate-300">
                        <i data-lucide="bell-off" class="h-8 w-8"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-slate-900">ไม่มีการแจ้งเตือนใหม่</h3>
                    <p class="text-slate-500">คุณได้รับการแจ้งเตือนครบถ้วนแล้วในขณะนี้</p>
                </div>
            @endforelse

            @if($notifications->hasPages())
                <div class="mt-8">
                    {{ $notifications->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
