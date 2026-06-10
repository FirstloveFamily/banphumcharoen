<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - บ้านพุ่มเจริญ จำกัด</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Kanit', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 25%, #3b82f6 50%, #dc2626 75%, #991b1b 100%);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(220, 38, 38, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .portal-option {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .portal-option:hover {
            transform: translateY(-2px);
        }

        .portal-option input:checked+div {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            border-color: #1e3a8a;
        }

        .portal-option input:checked+div i,
        .portal-option input:checked+div span {
            color: white;
        }

        .feature-card {
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateX(8px);
            background: rgba(255, 255, 255, 0.15);
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

        .animate-delay-200 {
            animation-delay: 0.2s;
        }

        .animate-delay-300 {
            animation-delay: 0.3s;
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 text-slate-900 antialiased">
    <main class="grid min-h-screen grid-cols-1 lg:grid-cols-[1.2fr_0.8fr]">

        <!-- Left Panel - Branding & Features -->
        <section
            class="relative hidden overflow-hidden gradient-bg text-white lg:flex flex-col justify-between px-12 py-12 xl:px-16">
            <!-- Decorative Elements -->
            <div class="absolute inset-0 opacity-30 pointer-events-none">
                <div class="absolute -left-32 -top-32 h-[500px] w-[500px] rounded-full bg-white/10 blur-3xl"></div>
                <div
                    class="absolute bottom-[-10rem] right-[-8rem] h-[400px] w-[400px] rounded-full bg-red-500/20 blur-3xl">
                </div>
                <div
                    class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[600px] w-[600px] rounded-full bg-blue-400/10 blur-3xl">
                </div>
            </div>

            <!-- Grid Pattern -->
            <div
                class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.05)_1px,transparent_1px)] bg-[size:50px_50px] opacity-40">
            </div>

            <!-- Logo -->
            <div class="relative z-10 animate-fade-in-up">
                <a href="{{ url('/') }}"
                    class="inline-flex items-center gap-3 rounded-full border border-white/20 bg-white/10 px-5 py-3 shadow-2xl backdrop-blur-xl transition-all duration-300 hover:bg-white/20 hover:scale-105">
                    <span
                        class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-full bg-white shadow-lg">
                        <img src="{{ Storage::url('images/logo.jpeg') }}" alt="บ้านพุ่มเจริญ จำกัด"
                            class="h-full w-full object-contain">
                    </span>
                    <span class="font-semibold text-base tracking-wide pr-2">บ้านพุ่มเจริญ จำกัด</span>
                </a>
            </div>

            <!-- Main Content -->
            <div class="relative z-10 my-auto max-w-2xl pr-8">
                <h1
                    class="text-5xl font-bold leading-[1.2] tracking-tight xl:text-6xl animate-fade-in-up animate-delay-100">
                    จัดการระบบแรงงาน
                    <span class="block text-red-300 mt-2">ถูกต้อง รวดเร็ว เชื่อถือได้</span>
                </h1>
                <p class="mt-6 text-lg leading-relaxed text-white/90 font-light animate-fade-in-up animate-delay-200">
                    ระบบพอร์ทัลลูกค้าสำหรับติดตามสถานะงาน แจ้งงานใหม่
                    และบริหารจัดการเอกสารแรงงานต่างด้าวของคุณอย่างเป็นระบบในที่เดียว
                </p>

                <div class="mt-12 space-y-4 animate-fade-in-up animate-delay-300">
                    <div
                        class="feature-card flex items-start gap-4 rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm shadow-lg">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/20 text-white border border-white/20 shadow-lg">
                            <i data-lucide="badge-check" class="h-6 w-6"></i>
                        </div>
                        <div>
                            <h2 class="font-semibold text-lg text-white">ติดตามสถานะงานแบบเรียลไทม์</h2>
                            <p class="text-sm text-white/80 mt-1 font-light">
                                อัปเดตสถานะเอกสารทุกขั้นตอนผ่านระบบออนไลน์</p>
                        </div>
                    </div>

                    <div
                        class="feature-card flex items-start gap-4 rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm shadow-lg">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/20 text-white border border-white/20 shadow-lg">
                            <i data-lucide="file-text" class="h-6 w-6"></i>
                        </div>
                        <div>
                            <h2 class="font-semibold text-lg text-white">แจ้งงานใหม่ได้ตลอด 24 ชั่วโมง</h2>
                            <p class="text-sm text-white/80 mt-1 font-light">ยื่นคำร้อง อัปโหลด
                                และแนบเอกสารผ่านระบบได้ทันที</p>
                        </div>
                    </div>

                    <div
                        class="feature-card flex items-start gap-4 rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm shadow-lg">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/20 text-white border border-white/20 shadow-lg">
                            <i data-lucide="lock-keyhole" class="h-6 w-6"></i>
                        </div>
                        <div>
                            <h2 class="font-semibold text-lg text-white">ข้อมูลปลอดภัยด้วยระบบเข้ารหัส</h2>
                            <p class="text-sm text-white/80 mt-1 font-light">
                                ข้อมูลส่วนบุคคลและเอกสารสำคัญได้รับการคุ้มครองสูงสุด</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="relative z-10 grid grid-cols-3 gap-6 border-t border-white/10 pt-10 mt-8">
                <div class="text-center lg:text-left">
                    <div class="text-3xl font-bold tracking-tight text-white xl:text-4xl">500+</div>
                    <div class="text-sm text-white/80 font-light mt-1">ผู้ประกอบการไว้วางใจ</div>
                </div>
                <div class="text-center lg:text-left">
                    <div class="text-3xl font-bold tracking-tight text-white xl:text-4xl">98%</div>
                    <div class="text-sm text-white/80 font-light mt-1">อัตราการอนุมัติสำเร็จ</div>
                </div>
                <div class="text-center lg:text-left">
                    <div class="text-3xl font-bold tracking-tight text-white xl:text-4xl">24/7</div>
                    <div class="text-sm text-white/80 font-light mt-1">ระบบพร้อมใช้งานเสมอ</div>
                </div>
            </div>
        </section>

        <!-- Right Panel - Login Form -->
        <section
            class="flex min-h-screen items-center justify-center px-4 py-12 sm:px-6 lg:px-12 bg-gradient-to-br from-slate-50 to-blue-50">
            <div class="w-full max-w-[480px]">
                <div class="text-center animate-fade-in-up">
                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50/80 px-4 py-2 text-sm font-semibold text-blue-700 shadow-sm">
                        <i data-lucide="user-round" class="h-4 w-4"></i>
                        Manager, Staff & Client Portal
                    </span>
                    <h1 class="mt-6 text-4xl font-bold tracking-tight text-slate-900">ยินดีต้อนรับ</h1>
                    <p class="mt-3 text-base text-slate-600">ลงชื่อเข้าใช้งานเพื่อบริหารจัดการงานเอกสารของคุณ</p>
                </div>

                <form action="{{ route('login.store') }}" method="POST"
                    class="mt-10 glass-card rounded-3xl p-8 shadow-2xl shadow-slate-300/50 sm:p-10 animate-fade-in-up animate-delay-100">
                    @csrf

                    <!-- Portal Selection -->
                    <div>
                        <label
                            class="text-xs font-bold uppercase tracking-wider text-slate-500">เลือกประเภทบัญชีผู้ใช้</label>
                        <div class="mt-4 grid grid-cols-3 gap-3" role="radiogroup" aria-label="เลือกประเภทบัญชี">
                            <label class="portal-option group relative cursor-pointer">
                                <input class="sr-only" type="radio" name="portal" value="customer"
                                    @checked(old('portal') === 'customer')>
                                <div
                                    class="rounded-xl border-2 border-slate-200 bg-white p-4 text-center shadow-sm transition-all duration-300 hover:border-blue-300 hover:shadow-md">
                                    <i data-lucide="user-round"
                                        class="mx-auto h-6 w-6 text-slate-400 transition-colors"></i>
                                    <span
                                        class="mt-2 block text-sm font-semibold text-slate-600 transition-colors">ลูกค้า</span>
                                </div>
                            </label>

                            <label class="portal-option group relative cursor-pointer">
                                <input class="sr-only" type="radio" name="portal" value="admin"
                                    @checked(old('portal', 'admin') === 'admin')>
                                <div
                                    class="rounded-xl border-2 border-slate-200 bg-white p-4 text-center shadow-sm transition-all duration-300 hover:border-blue-300 hover:shadow-md">
                                    <i data-lucide="clipboard-list"
                                        class="mx-auto h-6 w-6 text-slate-400 transition-colors"></i>
                                    <span
                                        class="mt-2 block text-sm font-semibold text-slate-600 transition-colors">แมนเจอร์</span>
                                </div>
                            </label>

                            <label class="portal-option group relative cursor-pointer">
                                <input class="sr-only" type="radio" name="portal" value="staff"
                                    @checked(old('portal') === 'staff')>
                                <div
                                    class="rounded-xl border-2 border-slate-200 bg-white p-4 text-center shadow-sm transition-all duration-300 hover:border-blue-300 hover:shadow-md">
                                    <i data-lucide="briefcase-business"
                                        class="mx-auto h-6 w-6 text-slate-400 transition-colors"></i>
                                    <span
                                        class="mt-2 block text-sm font-semibold text-slate-600 transition-colors">เจ้าหน้าที่</span>
                                </div>
                            </label>
                        </div>
                        @error('portal')
                            <p class="mt-3 text-sm text-red-600 flex items-center gap-2 font-medium"><i
                                    data-lucide="alert-circle" class="h-4 w-4"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    <div class="my-8 flex items-center gap-4">
                        <div class="h-px flex-1 bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div>
                        <span
                            class="text-xs font-semibold text-slate-400 uppercase tracking-wider">หรือระบุข้อมูลเข้าใช้งาน</span>
                        <div class="h-px flex-1 bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div>
                    </div>

                    <!-- Form Fields -->
                    <div class="space-y-5">
                        <div>
                            <label for="email" class="text-sm font-semibold text-slate-700">ที่อยู่อีเมล <span
                                    class="text-red-600">*</span></label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required
                                autofocus autocomplete="email"
                                class="mt-2 h-12 w-full rounded-xl border-2 border-slate-200 px-4 text-sm text-slate-900 outline-none transition-all duration-200 input-focus focus:border-red-500">
                            @error('email')
                                <p class="mt-2 text-sm text-red-600 flex items-center gap-2 font-medium"><i
                                        data-lucide="alert-circle" class="h-4 w-4"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="text-sm font-semibold text-slate-700">รหัสผ่าน <span
                                    class="text-red-600">*</span></label>
                            <div
                                class="mt-2 flex h-12 overflow-hidden rounded-xl border-2 border-slate-200 transition-all duration-200 focus-within:border-red-500 focus-within:ring-4 focus-within:ring-red-500/10">
                                <input id="password" name="password" type="password" required
                                    autocomplete="current-password"
                                    class="min-w-0 flex-1 border-0 px-4 text-sm outline-none">
                                <button type="button" id="togglePassword"
                                    class="flex w-14 items-center justify-center border-l border-slate-100 text-slate-400 hover:text-red-600 transition-colors"
                                    aria-label="แสดงหรือซ่อนรหัสผ่าน">
                                    <i id="eyeIcon" data-lucide="eye" class="h-5 w-5"></i>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-2 text-sm text-red-600 flex items-center gap-2 font-medium"><i
                                        data-lucide="alert-circle" class="h-4 w-4"></i> {{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-between">
                        <label
                            class="inline-flex items-center gap-3 cursor-pointer text-sm text-slate-600 select-none font-medium">
                            <input type="checkbox" name="remember" value="1"
                                class="h-5 w-5 rounded border-slate-300 text-red-600 focus:ring-red-500/20">
                            จดจำเซสชันนี้
                        </label>
                    </div>

                    <button type="submit"
                        class="btn-primary mt-8 h-12 w-full rounded-xl text-base font-semibold text-white shadow-lg">
                        เข้าสู่ระบบ
                    </button>
                </form>

                <div class="mt-8 text-center animate-fade-in-up animate-delay-200">
                    <a href="{{ url('/') }}"
                        class="inline-flex items-center gap-2 text-base font-semibold text-slate-500 hover:text-red-600 transition-colors">
                        <i data-lucide="arrow-left" class="h-5 w-5"></i>
                        กลับสู่เว็บไซต์หลัก
                    </a>
                </div>
            </div>
        </section>
    </main>

    <script>
        // เรียกใช้งาน Lucide Icons
        lucide.createIcons();

        // สคริปต์สลับการแสดงผลรหัสผ่านและเปลี่ยนไอคอน
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword?.addEventListener('click', () => {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';

            // สลับไอคอนระหว่าง eye และ eye-off เพื่อความสมบูรณ์แบบ
            eyeIcon.setAttribute('data-lucide', isPassword ? 'eye-off' : 'eye');
            lucide.createIcons();
        });
    </script>
</body>

</html>
