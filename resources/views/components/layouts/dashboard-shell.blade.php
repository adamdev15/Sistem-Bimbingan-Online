@props(['title' => 'Dashboard'])

@php
    $menuItems = \App\View\DashboardNavigation::menuItems();
    $roleBadge = \App\View\DashboardNavigation::roleBadgeLabel();
@endphp

<x-layouts.dashboard :title="$title">
    <div class="flex min-h-screen" x-data="{ sidebarOpen: true, mobileSidebar: false, profileOpen: false, bellOpen: false }" @keydown.escape.window="mobileSidebar = false; profileOpen = false; bellOpen = false">
        {{-- Desktop sidebar --}}
        <aside
            :class="sidebarOpen ? 'w-64' : 'w-20'"
            class="hidden shrink-0 border-r border-blue-900/50 bg-blue-950 text-blue-50 transition-all duration-200 lg:flex lg:flex-col lg:min-h-screen"
        >
            <div class="flex h-16 items-center justify-center border-b border-blue-900 px-2">
                <a href="{{ route('dashboard') }}" class="flex items-center justify-center">
                    <img
                        src="{{ asset('image/logo-bimbel.png') }}"
                        alt="eBimbel"
                        :class="sidebarOpen ? 'h-10 w-auto' : 'h-8 w-auto'"
                        class="max-w-[9rem] object-contain"
                    >
                </a>
            </div>
            <nav class="flex-1 space-y-1 overflow-y-auto p-3 text-sm">
                @foreach ($menuItems as $item)
                    <a
                        href="{{ $item['href'] }}"
                        @class([
                            'flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors',
                            'bg-blue-800/80 text-white shadow-inner' => $item['active'],
                            'text-blue-100 hover:bg-blue-900/80' => ! $item['active'],
                        ])
                    >
                        @switch($item['label'])
                            @case('Dashboard')
                                <svg class="h-5 w-5 shrink-0 text-current opacity-90" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                                @break
                            @case('Cabang')
                                <svg class="h-5 w-5 shrink-0 text-current opacity-90" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                                @break
                            @case('Siswa')
                                <svg class="h-5 w-5 shrink-0 text-current opacity-90" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                                @break
                            @case('Tutor')
                                <svg class="h-5 w-5 shrink-0 text-current opacity-90" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                                @break
                            @case('Jadwal')
                                <svg class="h-5 w-5 shrink-0 text-current opacity-90" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5"/></svg>
                                @break
                            @case('Presensi')
                                <svg class="h-5 w-5 shrink-0 text-current opacity-90" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @break
                            @case('Pembayaran')
                                <svg class="h-5 w-5 shrink-0 text-current opacity-90" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 5.25h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                @break
                            @case('Laporan')
                                <svg class="h-5 w-5 shrink-0 text-current opacity-90" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                                @break
                            @case('Mata pelajaran')
                                <svg class="h-5 w-5 shrink-0 text-current opacity-90" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                                @break
                            @case('Gaji tutor')
                                <svg class="h-5 w-5 shrink-0 text-current opacity-90" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                                @break
                            @case('Profil')
                                <svg class="h-5 w-5 shrink-0 text-current opacity-90" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                @break
                            @default
                                <svg class="h-5 w-5 shrink-0 text-current opacity-90" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                        @endswitch
                        <span x-show="sidebarOpen" x-transition.opacity class="truncate">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
            <div class="mt-auto border-t border-blue-900 p-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-blue-200 transition-colors hover:bg-blue-900/80 hover:text-white">
                        <svg class="h-5 w-5 shrink-0 opacity-90" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
                        <span x-show="sidebarOpen" x-transition.opacity>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Mobile drawer --}}
        <div x-show="mobileSidebar" x-cloak class="fixed inset-0 z-50 lg:hidden">
            <div class="absolute inset-0 bg-slate-900/50" @click="mobileSidebar = false" x-transition.opacity></div>
            <aside
                x-show="mobileSidebar"
                x-transition:enter="transform transition ease-out duration-300"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="absolute left-0 top-0 flex h-full w-[70vw] max-w-sm flex-col bg-blue-950 text-blue-50 shadow-xl"
            >
                <div class="flex items-center justify-between border-b border-blue-900 p-4">
                    <img src="{{ asset('image/logo-bimbel.png') }}" alt="eBimbel" class="h-9 w-auto">
                    <button type="button" @click="mobileSidebar = false" class="rounded-lg p-2 hover:bg-blue-900" aria-label="Tutup menu">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <nav class="flex-1 space-y-1 overflow-y-auto p-3 text-sm">
                    @foreach ($menuItems as $item)
                        <a
                            href="{{ $item['href'] }}"
                            @click="mobileSidebar = false"
                            @class([
                                'flex items-center gap-3 rounded-lg px-3 py-2.5',
                                'bg-blue-800/80 text-white' => $item['active'],
                                'text-blue-100 hover:bg-blue-900/80' => ! $item['active'],
                            ])
                        >
                            @switch($item['label'])
                                @case('Dashboard')
                                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                                    @break
                                @case('Cabang')
                                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                                    @break
                                @case('Siswa')
                                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                                    @break
                                @case('Tutor')
                                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                                    @break
                                @case('Jadwal')
                                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5"/></svg>
                                    @break
                                @case('Presensi')
                                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @break
                                @case('Pembayaran')
                                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 5.25h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    @break
                                @case('Laporan')
                                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                                    @break
                                @case('Mata pelajaran')
                                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                                    @break
                                @case('Gaji tutor')
                                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                                    @break
                                @case('Profil')
                                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                    @break
                            @endswitch
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                    <form method="POST" action="{{ route('logout') }}" class="mt-4 border-t border-blue-900/50 pt-4">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-blue-200 hover:bg-blue-900/80 hover:text-white">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
                            Keluar
                        </button>
                    </form>
                </nav>
            </aside>
        </div>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white">
                <div class="flex h-16 items-center justify-between gap-3 px-4 sm:px-6">
                    <div class="flex min-w-0 items-center gap-2">
                        <button type="button" @click="mobileSidebar = true" class="rounded-lg border border-slate-200 p-2 text-slate-600 hover:bg-slate-50 lg:hidden" aria-label="Buka menu">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <button type="button" @click="sidebarOpen = !sidebarOpen" class="hidden rounded-lg border border-slate-200 p-2 text-slate-600 hover:bg-slate-50 lg:inline-flex" aria-label="Ciutkan sidebar">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12h18"/></svg>
                        </button>
                        <div class="relative hidden min-w-0 sm:block">
                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                            </span>
                            <input type="search" placeholder="Cari siswa, tutor, jadwal..." class="w-full min-w-[12rem] max-w-md rounded-lg border border-slate-200 bg-slate-50 py-2 pl-10 pr-4 text-sm outline-none ring-blue-500 focus:border-blue-300 focus:ring-2 sm:w-72 lg:w-96" />
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-1 sm:gap-2">
                        <div class="relative">
                            <button type="button" @click="bellOpen = !bellOpen" class="relative rounded-full border border-slate-200 bg-white p-2 text-slate-600 hover:bg-slate-50" aria-label="Notifikasi" :aria-expanded="bellOpen.toString()">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                                @if (($bellUnreadCount ?? 0) > 0)
                                    <span class="absolute right-1 top-1 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-500 px-0.5 text-[10px] font-bold text-white ring-2 ring-white">{{ $bellUnreadCount > 9 ? '9+' : $bellUnreadCount }}</span>
                                @endif
                            </button>
                            <div
                                x-show="bellOpen"
                                x-cloak
                                x-transition
                                @click.outside="bellOpen = false"
                                class="absolute right-0 z-50 mt-2 w-[min(100vw-2rem,22rem)] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl ring-1 ring-slate-900/5"
                            >
                                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-4 py-2.5">
                                    <span class="text-sm font-semibold text-slate-900">Notifikasi</span>
                                    @if (($bellNotifications ?? collect())->isNotEmpty())
                                        <form method="POST" action="{{ route('notifications.read-all') }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-xs font-semibold text-blue-600 hover:text-blue-800">Tandai dibaca</button>
                                        </form>
                                    @endif
                                </div>
                                <ul class="max-h-80 overflow-y-auto py-1 text-sm">
                                    @forelse ($bellNotifications ?? [] as $n)
                                        <li class="border-b border-slate-50 last:border-0">
                                            <form method="POST" action="{{ route('notifications.read', $n) }}" class="block">
                                                @csrf
                                                <button type="submit" class="flex w-full flex-col gap-0.5 px-4 py-3 text-left transition hover:bg-slate-50 {{ $n->read_at ? 'opacity-75' : 'bg-blue-50/40' }}">
                                                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ str_replace('.', ' · ', $n->type) }}</span>
                                                    <span class="font-semibold text-slate-900">{{ $n->title }}</span>
                                                    @if ($n->body)
                                                        <span class="line-clamp-2 text-xs text-slate-600">{{ $n->body }}</span>
                                                    @endif
                                                    <span class="mt-1 text-[11px] text-slate-500">
                                                        @if ($n->sender)
                                                            {{ $n->sender->name }} · {{ $n->sender->email }}
                                                        @else
                                                            Sistem
                                                        @endif
                                                        @if ($n->subject_type)
                                                            · {{ class_basename($n->subject_type) }} #{{ $n->subject_id }}
                                                        @endif
                                                    </span>
                                                    <span class="text-[11px] font-medium text-blue-600">Buka tautan terkait →</span>
                                                </button>
                                            </form>
                                        </li>
                                    @empty
                                        <li class="px-4 py-8 text-center text-slate-500">Belum ada notifikasi.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                        <button type="button" class="rounded-full border border-slate-200 bg-white p-2 text-slate-600 hover:bg-slate-50" aria-label="Pesan">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-3.977-.163a48.424 48.424 0 01-3.498 0 48.64 48.64 0 01-3.498-.163c-1.133-.093-1.98-.957-1.98-2.193V10.61c0-.97.616-1.813 1.5-2.097V6.75A2.25 2.25 0 0111.25 4.5h1.5a2.25 2.25 0 012.25 2.25v1.761z"/></svg>
                        </button>
                        <button type="button" class="rounded-full border border-slate-200 bg-white p-2 text-slate-600 hover:bg-slate-50" aria-label="Pengaturan">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01-1.413-.613l-.008-.005c-.09-.185-.133-.38-.132-.581v-.22c0-.25.045-.5.13-.736l.002-.004.017-.043a1.125 1.125 0 01.621-.637l1.218-.456c.355-.133.75-.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.496.645-.87l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </button>

                        <span class="hidden rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-800 lg:inline">{{ $roleBadge }}</span>

                        <div class="relative">
                            <button type="button" @click="profileOpen = !profileOpen" class="flex items-center gap-2 rounded-full border border-slate-200 px-2 py-1 hover:bg-slate-50" aria-expanded="false" aria-haspopup="true">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-xs font-semibold text-blue-800">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
                                <span class="hidden max-w-[10rem] truncate text-sm font-medium text-slate-700 sm:block">{{ auth()->user()->name }}</span>
                                <svg class="hidden h-4 w-4 text-slate-400 sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="profileOpen" @click.outside="profileOpen = false" x-transition x-cloak class="absolute right-0 z-40 mt-2 w-48 rounded-xl border border-slate-200 bg-white py-1 shadow-lg">
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Profil</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50">Keluar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6">
                {{ $slot }}
            </main>

            <footer class="border-t border-slate-200 bg-white px-6 py-3 text-xs text-slate-500">
                &copy; {{ date('Y') }} eBimbel. Dashboard management system.
            </footer>
        </div>
    </div>
</x-layouts.dashboard>
