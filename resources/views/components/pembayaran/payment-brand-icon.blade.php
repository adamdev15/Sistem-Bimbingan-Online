@props(['code' => ''])

@php
    $c = strtolower((string) $code);
@endphp

<div {{ $attributes->merge(['class' => 'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200/80 bg-white shadow-sm']) }} aria-hidden="true">
    @switch($c)
        @case('bri')
            <svg class="h-6 w-6" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="32" height="32" rx="8" fill="#00529C"/>
                <path d="M8 12h16v2H8v-2zm0 4h10v2H8v-2zm0 4h14v2H8v-2z" fill="#fff"/>
            </svg>
            @break
        @case('bca')
            <svg class="h-6 w-6" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="32" height="32" rx="8" fill="#0066AE"/>
                <path d="M10 10h12v3H10V10zm0 5h12v2H10v-2zm0 4h8v2h-8v-2z" fill="#fff"/>
            </svg>
            @break
        @case('bni')
            <svg class="h-6 w-6" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="32" height="32" rx="8" fill="#FF6600"/>
                <path d="M9 11h14v2H9v-2zm0 4h10v2H9v-2zm0 4h12v2H9v-2z" fill="#fff"/>
            </svg>
            @break
        @case('dana')
            <svg class="h-6 w-6" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="32" height="32" rx="8" fill="#118EEA"/>
                <circle cx="16" cy="16" r="7" stroke="#fff" stroke-width="2" fill="none"/>
                <path d="M13 16h6" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
            </svg>
            @break
        @case('gopay')
            <svg class="h-6 w-6" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="32" height="32" rx="8" fill="#00AA13"/>
                <path d="M11 20c0-3 2.5-6 5-6s5 3 5 6" stroke="#fff" stroke-width="2" stroke-linecap="round" fill="none"/>
                <circle cx="16" cy="12" r="2" fill="#fff"/>
            </svg>
            @break
        @case('shopeepay')
            <svg class="h-6 w-6" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="32" height="32" rx="8" fill="#EE4D2D"/>
                <path d="M10 14h12v2H10v-2zm2 4h8v2h-8v-2z" fill="#fff"/>
            </svg>
            @break
        @default
            <svg class="h-5 w-5 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 15.75h19.5M12 3v18"/>
            </svg>
    @endswitch
</div>
