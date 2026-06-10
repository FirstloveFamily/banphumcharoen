<!-- Navbar -->
<nav class="sticky top-0 z-50 border-b border-gray-100 bg-white/95 backdrop-blur">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between gap-4 py-2 sm:h-20">
            <a href="{{ url('/') }}" class="flex min-w-0 items-center gap-3">
                <div class="h-11 w-11 shrink-0 overflow-hidden rounded-xl border border-blue-100 bg-white shadow-sm sm:h-12 sm:w-12">
                    <img src="{{ Storage::url('images/logo.jpeg') }}" alt="บ้านพุ่มเจริญ จำกัด"
                        class="h-full w-full object-contain">
                </div>
                <div class="min-w-0">
                    <h1 class="truncate font-bold text-base leading-tight text-slate-900 sm:text-lg bp-blue">บ้านพุ่มเจริญ จำกัด</h1>
                    <p class="hidden text-[11px] uppercase tracking-[0.2em] text-gray-500 sm:block">BANPHUMCHAROEN CO., LTD.</p>
                </div>
            </a>

            <div class="hidden items-center gap-6 text-sm font-medium text-gray-600 lg:flex">
                <a href="{{ url('/') }}"
                    class="{{ request()->is('/') ? 'text-blue-600 border-b-2 border-blue-600 pb-1' : 'hover:text-blue-600 transition' }}">หน้าหลัก</a>
                <a href="{{ route('services.index') }}"
                    class="{{ request()->routeIs('services.index') ? 'text-blue-600 border-b-2 border-blue-600 pb-1' : 'hover:text-blue-600 transition' }}">บริการของเรา</a>
                <a href="{{ route('status.index') }}"
                    class="{{ request()->routeIs('status.index') ? 'text-blue-600 border-b-2 border-blue-600 pb-1' : 'hover:text-blue-600 transition' }}">ตรวจสอบสถานะ</a>
                @guest
                    <a href="{{ route('employers.index') }}"
                        class="{{ request()->routeIs('employers.index') ? 'text-blue-600 border-b-2 border-blue-600 pb-1' : 'hover:text-blue-600 transition' }}">สำหรับนายจ้าง</a>
                @endguest
                <a href="{{ route('aboutus.index') }}"
                    class="{{ request()->routeIs('aboutus.index') ? 'text-blue-600 border-b-2 border-blue-600 pb-1' : 'hover:text-blue-600 transition' }}">เกี่ยวกับเรา</a>
                <a href="{{ route('news.index') }}"
                    class="{{ request()->routeIs('news.*') ? 'text-blue-600 border-b-2 border-blue-600 pb-1' : 'hover:text-blue-600 transition' }}">ข่าวสาร</a>
            </div>

            <div class="hidden items-center gap-3 lg:flex">
                <a href="#"
                    class="inline-flex items-center gap-2 rounded-full bg-blue-900 px-4 py-2 text-sm text-white transition hover:bg-blue-800">
                    <i data-lucide="message-circle" class="h-4 w-4"></i>
                    ติดต่อเรา LINE
                </a>
                @guest
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center gap-2 rounded-full border border-gray-200 px-4 py-2 text-sm text-gray-600 transition hover:border-blue-200 hover:text-blue-600">
                        <i data-lucide="user" class="h-4 w-4"></i>
                        เข้าสู่ระบบ
                    </a>
                @else
                    @php
                        $dashboardUrl = auth()->user()->hasAnyRole(['employer', 'hr']) || in_array(auth()->user()->role, ['employer', 'hr'], true)
                            ? route('employers.dashboard')
                            : url('/admin');
                    @endphp
                    <a href="{{ $dashboardUrl }}"
                        class="inline-flex items-center gap-2 rounded-full border border-gray-200 px-4 py-2 text-sm text-gray-600 transition hover:border-blue-200 hover:text-blue-600">
                        <i data-lucide="layout-dashboard" class="h-4 w-4"></i>
                        แดชบอร์ด
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-full border border-gray-200 px-4 py-2 text-sm text-gray-600 transition hover:border-red-200 hover:text-red-600">
                            <i data-lucide="log-out" class="h-4 w-4"></i>
                            ออกจากระบบ
                        </button>
                    </form>
                @endguest
            </div>

            <button id="mobile-nav-toggle"
                class="inline-flex items-center justify-center rounded-xl border border-gray-200 p-2 text-gray-700 transition hover:border-blue-200 hover:text-blue-600 lg:hidden"
                aria-controls="mobile-nav" aria-expanded="false" type="button">
                <i data-lucide="menu" class="h-6 w-6"></i>
            </button>
        </div>

        <div id="mobile-nav" class="hidden border-t border-gray-100 pb-4 lg:hidden">
            <div class="space-y-2 pt-4">
                <a href="{{ url('/') }}"
                    class="block rounded-2xl px-4 py-3 text-sm font-medium {{ request()->is('/') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">หน้าหลัก</a>
                <a href="{{ route('services.index') }}"
                    class="block rounded-2xl px-4 py-3 text-sm font-medium {{ request()->routeIs('services.index') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">บริการของเรา</a>
                <a href="{{ route('status.index') }}"
                    class="block rounded-2xl px-4 py-3 text-sm font-medium {{ request()->routeIs('status.index') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">ตรวจสอบสถานะ</a>
                @guest
                    <a href="{{ route('employers.index') }}"
                        class="block rounded-2xl px-4 py-3 text-sm font-medium {{ request()->routeIs('employers.index') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">สำหรับนายจ้าง</a>
                @endguest
                <a href="{{ route('aboutus.index') }}"
                    class="block rounded-2xl px-4 py-3 text-sm font-medium {{ request()->routeIs('aboutus.index') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">เกี่ยวกับเรา</a>
                <a href="{{ route('news.index') }}"
                    class="block rounded-2xl px-4 py-3 text-sm font-medium {{ request()->routeIs('news.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">ข่าวสาร</a>

                <div class="grid grid-cols-1 gap-3 pt-2 sm:grid-cols-2">
                    <a href="#"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-blue-800">
                        <i data-lucide="message-circle" class="h-4 w-4"></i>
                        ติดต่อเรา LINE
                    </a>
                    @guest
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-blue-200 hover:text-blue-600">
                            <i data-lucide="user" class="h-4 w-4"></i>
                            เข้าสู่ระบบ
                        </a>
                    @else
                        @php
                            $dashboardUrl = auth()->user()->hasAnyRole(['employer', 'hr']) || in_array(auth()->user()->role, ['employer', 'hr'], true)
                                ? route('employers.dashboard')
                                : url('/admin');
                        @endphp
                        <a href="{{ $dashboardUrl }}"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-blue-200 hover:text-blue-600">
                            <i data-lucide="layout-dashboard" class="h-4 w-4"></i>
                            แดชบอร์ด
                        </a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-red-200 hover:text-red-600">
                                <i data-lucide="log-out" class="h-4 w-4"></i>
                                ออกจากระบบ
                            </button>
                        </form>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</nav>

@push('scripts')
    <script>
        (() => {
            const toggle = document.getElementById('mobile-nav-toggle');
            const menu = document.getElementById('mobile-nav');

            if (!toggle || !menu) {
                return;
            }

            toggle.addEventListener('click', () => {
                const isHidden = menu.classList.toggle('hidden');
                toggle.setAttribute('aria-expanded', String(!isHidden));
            });

            menu.querySelectorAll('a, button').forEach((item) => {
                item.addEventListener('click', () => {
                    menu.classList.add('hidden');
                    toggle.setAttribute('aria-expanded', 'false');
                });
            });
        })();
    </script>
@endpush
