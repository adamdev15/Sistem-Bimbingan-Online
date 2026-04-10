<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @php
            $settings = \App\Models\Setting::pluck('value', 'setting_key')->toArray();
        @endphp
        <link rel="icon" href="{{ isset($settings['logo_filename']) ? asset('image/' . $settings['logo_filename']) : asset('image/logo-bimbel.png') }}" type="image/x-icon">
        <title>{{ $title ?? config('app.name', 'BMS') }}</title>
        <style>[x-cloak]{display:none!important;}</style>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
        {{ $slot }}
    </body>
</html>
