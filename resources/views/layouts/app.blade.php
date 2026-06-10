<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'บ้านพุ่มเจริญ จำกัด - บริการจัดการเอกสารแรงงานต่างด้าว' }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('storage/images/logo.jpeg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background-color: #f8fafc;
        }

        .bg-gradient-hero {
            background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%);
        }

        .bp-blue {
            color: #1e3a8a;
        }

        .bg-bp-blue {
            background-color: #1e3a8a;
        }

        .border-bp-blue {
            border-color: #1e3a8a;
        }
    </style>
    @stack('head')
</head>

<body class="overflow-x-hidden text-slate-800">
    {{-- Header / Navbar --}}
    @include('partials.header')

    {{-- Main content slot --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('partials.footer')

    <script>
        lucide.createIcons();
    </script>
    @stack('scripts')
</body>

</html>
