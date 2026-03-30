<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>BimbelPro Login</title>
        <link rel="icon" href="{{ asset('image/logo-bimbel.png') }}" type="image/png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-700 to-sky-500 p-4 sm:p-6">
            <div class="mx-auto grid min-h-[calc(100vh-2rem)] w-full max-w-6xl items-stretch overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-blue-100 lg:grid-cols-2">
                <section class="hidden flex-col justify-between bg-blue-900 p-10 text-white lg:flex">
                    <div>
                        <a href="{{ route('landing') }}" class="inline-flex items-center gap-3">
                            <img src="{{ asset('image/logo-bimbel.png') }}" alt="Logo Bimbel" class="h-12 w-auto">
                        </a>
                        <h1 class="mt-8 text-4xl font-extrabold leading-tight">Selamat datang di Bimbel Management System</h1>
                        <p class="mt-4 max-w-md text-blue-100">Kelola operasional bimbel multi-cabang lebih cepat, rapi, dan profesional dalam satu platform.</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4">
                        <img src="{{ asset('image/hero.png') }}" alt="Ilustrasi Dashboard" class="w-full rounded-xl">
                    </div>
                </section>

                <section class="flex items-center justify-center px-5 py-8 sm:px-8">
                    <div class="w-full max-w-md">
                        <div class="mb-6 text-center lg:text-left">
                            <a href="{{ route('landing') }}" class="inline-flex items-center justify-center lg:justify-start">
                                <img src="{{ asset('image/logo-bimbel.png') }}" alt="Logo Bimbel" class="h-12 w-auto">
                            </a>
                        </div>
                        {{ $slot }}
                    </div>
                </section>
            </div>
        </div>
    </body>
</html>
