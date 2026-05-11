@php
    $settings = \App\Models\Setting::pluck('value', 'setting_key')->toArray();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Bimbel Jarimatrik Login' }}</title>
        <link rel="icon" href="{{ isset($settings['logo_filename']) ? asset('image/' . $settings['logo_filename']) : asset('image/logo-bimbel.png') }}" type="image/x-icon">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <style>[x-cloak] { display: none !important; }</style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-700 to-sky-500 p-4 sm:p-6">
            <div class="mx-auto grid min-h-[calc(100vh-2rem)] w-full max-w-6xl items-stretch overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-blue-100 lg:grid-cols-2">
                <section class="hidden flex-col justify-between bg-blue-900 p-10 text-white lg:flex">
                    <div>
                        <a href="{{ route('landing') }}" class="inline-flex items-center gap-3">
                            <img src="{{ isset($settings['logo_filename']) ? asset('image/' . $settings['logo_filename']) : asset('image/logo-bimbel.png') }}" alt="Logo Bimbel" class="h-12 w-auto">
                        </a>
                        <h1 class="mt-1 mb-2 text-4xl font-extrabold leading-tight">Selamat datang di Bimbel Jarimatik TEGAL</h1>
                        <p class="text-lg text-blue-100">{{ $settings['hero_desc'] ?? 'Bimbingan Belajar Terbaik untuk Sukses Akademik' }}</p>
                    </div>
                    <div class="rounded-2xl">
                        <img src="{{ isset($settings['hero_filename']) ? asset('image/' . $settings['hero_filename']) : asset('image/hero.png') }}" alt="Ilustrasi" class="mx-auto w-full rounded-xl">
                    </div>
                </section>

                <section class="flex items-center justify-center px-5 py-8 sm:px-8">
                    <div class="w-full max-w-md">
                        <div class="mb-6 text-center lg:text-left">
                            <a href="{{ route('landing') }}" class="inline-flex items-center justify-center lg:justify-start">
                                <img src="{{ isset($settings['logo_filename']) ? asset('image/' . $settings['logo_filename']) : asset('image/logo-bimbel.png') }}" alt="Logo Bimbel" class="h-12 w-auto">
                            </a>
                        </div>
                        {{ $slot }}
                    </div>
                </section>
            </div>
        </div>
    </body>
</html>
