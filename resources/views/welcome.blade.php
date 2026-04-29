<x-layouts.landing title="Bimbel Jarimatrik" >
    <div
        x-data="{
            showTop: false,
            mobileMenu: false,
            aboutActive: 0,
            scrollToSection(id) {
                const el = document.getElementById(id);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        }"
        x-init="
            window.addEventListener('scroll', () => showTop = window.scrollY > 250);
            @if(session('status'))
                window.Swal.fire({
                    icon: 'success',
                    title: 'Pendaftaran Berhasil!',
                    text: '{{ session('status') }}',
                    confirmButtonText: 'Oke',
                    confirmButtonColor: '#2563eb',
                    customClass: {
                        popup: 'rounded-2xl',
                        confirmButton: 'rounded-xl px-5 py-2.5'
                    }
                });
            @endif
        "
        x-effect="document.body.classList.toggle('overflow-hidden', mobileMenu)"
        @keydown.escape.window="mobileMenu = false"
    >
        <header class="sticky top-0 z-40 border-b border-blue-100/80 bg-white/95 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
                <a href="{{ route('landing') }}" class="flex items-center gap-2">
                    <img src="{{ isset($settings['logo_filename']) ? asset('image/' . $settings['logo_filename']) : asset('image/logo-bimbel.png') }}" alt="Logo Bimbel" class="h-16 w-auto">
                </a>
                <nav class="hidden items-center gap-1 text-sm font-semibold text-slate-600 md:flex" aria-label="Navigasi utama desktop">
                    <a href="#hero" @click.prevent="scrollToSection('hero')" class="rounded-lg px-3 py-2 transition hover:bg-blue-50 hover:text-blue-800">Beranda</a>
                    <a href="#about" @click.prevent="scrollToSection('about')" class="rounded-lg px-3 py-2 transition hover:bg-blue-50 hover:text-blue-800">Tentang Bimbel</a>
                    <a href="#programs" @click.prevent="scrollToSection('programs')" class="rounded-lg px-3 py-2 transition hover:bg-blue-50 hover:text-blue-800">Program</a>
                    <a href="#galeri" @click.prevent="scrollToSection('galeri')" class="rounded-lg px-3 py-2 transition hover:bg-blue-50 hover:text-blue-800">Galeri</a>
                    <a href="#faq" @click.prevent="scrollToSection('faq')" class="rounded-lg px-3 py-2 transition hover:bg-blue-50 hover:text-blue-800">FAQ</a>
                </nav>
                <div class="hidden items-center gap-3 md:flex">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Dashboard</a>
                    @else
                        <a href="{{ route('register') }}" class="rounded-lg border border-blue-200 bg-blue-500 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-600">Daftar Sekarang</a>
                        <a href="{{ route('login') }}" class="rounded-lg border border-blue-200 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50">Login</a>
                    @endauth
                </div>
                <button
                    type="button"
                    @click="mobileMenu = true"
                    :aria-expanded="mobileMenu"
                    aria-controls="landing-mobile-drawer"
                    aria-label="Buka menu navigasi"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200/90 bg-white text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/80 hover:text-blue-800 md:hidden"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                    </svg>
                </button>
            </div>
        </header>

        {{-- Drawer di luar <header>: backdrop-blur/sticky header membuat fixed anak terikat → tinggi tidak penuh --}}
        <div
            id="landing-mobile-drawer"
            x-show="mobileMenu"
            x-cloak
            class="fixed inset-0 z-[100] md:hidden"
            style="height: 100dvh; min-height: 100dvh;"
            role="dialog"
            aria-modal="true"
            aria-labelledby="landing-drawer-title"
        >
            <div
                x-show="mobileMenu"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                style="height: 100dvh; min-height: 100dvh;"
                @click="mobileMenu = false"
                aria-hidden="true"
            ></div>

            <aside
                x-show="mobileMenu"
                x-transition:enter="transform transition ease-out duration-300"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                @click.stop
                class="absolute left-0 top-0 flex w-[min(100%,19rem)] flex-col border-r border-white/10 bg-gradient-to-b from-blue-950 via-blue-900 to-slate-950 shadow-2xl shadow-black/40"
                style="height: 100dvh; min-height: 100dvh; max-height: 100dvh;"
            >
                <div class="flex shrink-0 items-center justify-between gap-3 border-b border-white/10 px-4 py-4">
                    <a href="{{ route('landing') }}" class="flex shrink-0 items-center rounded-lg bg-white px-2.5 py-1.5 shadow-md ring-1 ring-white/30" @click="mobileMenu = false">
                        <img src="{{ isset($settings['logo_filename']) ? asset('image/' . $settings['logo_filename']) : asset('image/logo-bimbel.png') }}" alt="Jarimatrik" class="h-7 w-auto">
                    </a>
                    <button
                        type="button"
                        @click="mobileMenu = false"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-white transition hover:bg-white/20"
                        aria-label="Tutup menu"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <p id="landing-drawer-title" class="sr-only">Menu navigasi</p>
                <nav class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-3 py-4" aria-label="Navigasi utama">
                    <p class="mb-3 px-3 text-[10px] font-bold uppercase tracking-[0.22em] text-blue-200/70">Menu</p>
                    <ul class="flex flex-col gap-0.5">
                        <li>
                            <a
                                href="#hero"
                                @click.prevent="mobileMenu = false; scrollToSection('hero')"
                                class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                            >
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-500/30 text-blue-100 ring-1 ring-white/10">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                                </span>
                                Beranda
                            </a>
                        </li                        <li>
                            <a
                                href="#about"
                                @click.prevent="mobileMenu = false; scrollToSection('about')"
                                class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-blue-50/95 transition hover:bg-white/10 hover:text-white"
                            >
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/10 text-blue-100 ring-1 ring-white/5">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                                </span>
                                Tentang Bimbel
                            </a>
                        </li>
                        <li>
                            <a
                                href="#programs"
                                @click.prevent="mobileMenu = false; scrollToSection('programs')"
                                class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-blue-50/95 transition hover:bg-white/10 hover:text-white"
                            >
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/10 text-blue-100 ring-1 ring-white/5">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                </span>
                                Program
                            </a>
                        </li>

                        <li>
                            <a
                                href="#galeri"
                                @click.prevent="mobileMenu = false; scrollToSection('galeri')"
                                class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-blue-50/95 transition hover:bg-white/10 hover:text-white"
                            >
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/10 text-blue-100 ring-1 ring-white/5">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                </span>
                                Galeri
                            </a>
                        </li>
                        <li>
                            <a
                                href="#faq"
                                @click.prevent="mobileMenu = false; scrollToSection('faq')"
                                class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-blue-50/95 transition hover:bg-white/10 hover:text-white"
                            >
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/10 text-blue-100 ring-1 ring-white/5">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/></svg>
                                </span>
                                FAQ
                            </a>
                        </li>i>
                    </ul>
                </nav>

                <div class="mt-auto shrink-0 border-t border-white/10 bg-black/25 p-4 pb-[max(1rem,env(safe-area-inset-bottom))]">
                    @auth
                        <a href="{{ route('dashboard') }}" @click="mobileMenu = false" class="flex w-full items-center justify-center rounded-xl bg-white px-4 py-3 text-sm font-bold text-blue-900 shadow-lg transition hover:bg-blue-50">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" @click="mobileMenu = false" class="flex w-full items-center justify-center rounded-xl bg-blue-500 px-4 py-3 text-sm font-bold text-white shadow-lg ring-1 ring-white/20 transition hover:bg-blue-400">Login</a>
                    @endauth
                </div>
            </aside>
        </div>

        <main>
            <section id="hero" class="bg-gradient-to-br from-blue-800 via-blue-600 to-blue-900">
                <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-16 text-white sm:px-6 lg:grid-cols-2 lg:px-8 lg:py-20">
                    <div class="space-y-6">
                        <p class="inline-flex rounded-full bg-white/20 px-3 py-1 text-xs font-semibold uppercase tracking-wide">{{ $settings['tagline'] ?? 'Solusi Modern Mengelola Bimbel' }}</p>
                        <h1 class="text-3xl font-extrabold leading-tight sm:text-4xl lg:text-5xl">{{ $settings['hero_title'] ?? 'Platform bimbel online profesional untuk cabang, tutor, dan siswa.' }}</h1>
                        <p class="max-w-xl text-blue-50">{{ $settings['hero_desc'] ?? 'Kelola jadwal, presensi, pembayaran, dan komunikasi WhatsApp dalam satu dashboard yang rapi, responsif, dan siap berkembang.' }}</p>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('register') }}" class="rounded-lg bg-white px-5 py-3 text-sm font-bold text-blue-700">Daftar Sekarang</a>
                            <a href="https://wa.me/{{ $settings['whatsapp_number'] ?? '6281200000000' }}" target="_blank" class="rounded-lg border border-white/50 px-5 py-3 text-sm font-bold text-white">Konsultasi Gratis</a>
                        </div>
                    </div>
                    <div class="backdrop-blur">
                        <img src="{{ isset($settings['hero_filename']) ? asset('image/' . $settings['hero_filename']) : asset('image/hero.png') }}" alt="Hero Bimbel" class="mx-auto w-full rounded-xl">
                    </div>
                </div>
            </section>

            <section id="about" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="grid items-center gap-10 lg:grid-cols-2">
                    <div class="order-2 lg:order-1">
                        <img src="{{ asset('image/whats.png') }}" alt="Apa itu Jarimatrik" class="mx-auto w-full max-w-lg">
                    </div>
                    <div class="order-1 space-y-4 lg:order-2">
                        <h2 class="text-3xl font-bold text-blue-950">Tentang Jarimatrik</h2>
                        <p class="text-slate-700">{{ $settings['about_us'] ?? 'Jarimatrik merupakan lembaga pendidikan pilihan dengan metode terstruktur, pendekatan personal bagi minat anak, dan sistem evaluasi transparan.' }}</p>
                        <div class="space-y-2">
                            @foreach ([
                                ['title' => 'Meningkatkan Kualitas Bimbel', 'desc' => 'Memberikan kesan profesional dan modern pada lembaga Anda.'],
                                ['title' => 'Menyederhanakan Administrasi Bimbel', 'desc' => 'Data pembayaran, presensi, jadwal, dan laporan lebih tertata.'],
                            ] as $i => $item)
                                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
                                    <button @click="aboutActive = aboutActive === {{ $i }} ? -1 : {{ $i }}" class="flex w-full items-center justify-between px-4 py-3 text-left font-medium text-slate-700">
                                        <span>{{ $item['title'] }}</span>
                                        <span x-text="aboutActive === {{ $i }} ? '-' : '+'"></span>
                                    </button>
                                    <div x-show="aboutActive === {{ $i }}" x-transition class="border-t border-slate-100 px-4 py-3 text-sm text-slate-600">
                                        {{ $item['desc'] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section id="programs" class="bg-slate-50 py-16">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <h2 class="text-center text-3xl font-bold text-blue-950">Program Belajar Kami</h2>
                    <p class="text-center text-slate-600 mt-3 max-w-2xl mx-auto">Kami menyediakan berbagai program materi les yang disesuaikan dengan jenjang dan kebutuhan pemahaman siswa.</p>
                    <div class="mt-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                        @forelse ($programs ?? [] as $program)
                            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 hover:shadow-md transition">
                                <div class="relative w-full aspect-video rounded-xl bg-slate-100 overflow-hidden mb-4">
                                    @if($program->foto)
                                        <img src="{{ asset('image/materi/' . $program->foto) }}" alt="{{ $program->nama_materi }}" class="absolute inset-0 w-full h-full object-cover">
                                    @else
                                        <div class="absolute inset-0 flex items-center justify-center text-slate-300">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                        </div>
                                    @endif
                                    <div class="absolute top-2 right-2 rounded-lg bg-white/90 backdrop-blur px-2.5 py-1 text-xs font-bold text-blue-700 shadow-sm border border-white/50">
                                        {{ $program->pertemuan_per_minggu }}x Seminggu
                                    </div>
                                </div>
                                <div class="p-2 sm:p-3">
                                <h3 class="font-bold text-blue-900 text-lg">{{ $program->nama_materi }}</h3>
                                <p class="mt-2 text-sm text-slate-500 line-clamp-2" title="{{ $program->deskripsi }}">{{ $program->deskripsi ?? 'Belum ada deskripsi.' }}</p>
                                <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                                    <div>
                                        <span class="text-xs text-slate-500 block">Biaya Pendaftaran</span>
                                        <span class="font-semibold text-blue-900 text-sm">{{ $program->biaya_daftar > 0 ? 'Rp ' . number_format($program->biaya_daftar, 0, ',', '.') : 'Gratis' }}</span>
                                    </div>
                                    <a href="{{ route('register') }}" class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700 hover:bg-blue-100 transition">Pilih</a>
                                </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-slate-500 text-center col-span-full">Program sedang dipersiapkan.</p>
                        @endforelse
                    </div>
                </div>
            </section>


            <section id="galeri" class="bg-slate-50 py-16">
                <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                    <h2 class="text-3xl font-bold text-blue-950">Suasana Belajar yang Nyaman</h2>
                    <p class="mt-3 text-slate-600 max-w-2xl mx-auto">Kami merancang fasilitas dan lingkungan belajar terbaik demi mendorong konsentrasi dan meningkatkan prestasi setiap siswa.</p>
                    
                    <div class="mt-10 grid grid-cols-2 gap-4 lg:gap-6 md:grid-cols-4">
                        @for ($i = 1; $i <= 4; $i++)
                            @if(isset($settings['gallery_'.$i]))
                                <div class="overflow-hidden rounded-2xl shadow-sm ring-1 ring-slate-200 aspect-[4/3]">
                                    <img src="{{ asset('image/' . $settings['gallery_'.$i]) }}" class="w-full h-full object-cover transition duration-300 hover:scale-110" alt="Suasana Kelas {{ $i }}">
                                </div>
                            @else
                                <div class="overflow-hidden rounded-2xl shadow-sm ring-1 ring-slate-200 aspect-[4/3] bg-slate-200 flex items-center justify-center">
                                    <span class="text-slate-400 text-sm font-medium">Foto {{ $i }}</span>
                                </div>
                            @endif
                        @endfor
                    </div>
                </div>
            </section>

            <section class="bg-gradient-to-br from-blue-800 to-blue-900 py-14 text-white">
                <div class="mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
                    <h2 class="text-3xl font-bold mb-6">Yuk Daftarkan Anak Anda Sekarang!</h2>
                    <p class="text-blue-100 mb-8 text-lg">Konsultasikan kebutuhan belajar anak secara gratis bersama admin kami untuk memetakan minat dan potensinya.</p>
                    <div class="flex flex-wrap justify-center gap-4">
                        <a href="{{ route('register') }}" class="rounded-xl border-2 border-white px-8 py-3 font-bold text-white hover:bg-white hover:text-blue-900 transition">Isi Form Pendaftaran</a>
                        <a href="https://wa.me/{{ $settings['whatsapp_number'] ?? '6281200000000' }}" target="_blank" class="flex items-center gap-2 rounded-xl bg-green-500 px-8 py-3 font-bold text-white shadow-lg hover:bg-green-600 transition">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2C6.58 2 2.16 6.42 2.16 11.88c0 1.93.56 3.81 1.6 5.41L2 22l4.86-1.67a9.8 9.8 0 0 0 5.18 1.5h.01c5.46 0 9.88-4.42 9.88-9.88S17.5 2 12.04 2m5.74 13.97c-.24.67-1.4 1.29-1.93 1.37-.49.07-1.1.1-1.78-.12-.41-.13-.93-.3-1.6-.59-2.82-1.22-4.66-4.09-4.8-4.28-.13-.19-1.14-1.52-1.14-2.9 0-1.38.72-2.06.98-2.34.26-.28.56-.35.74-.35.19 0 .37.01.53.01.17.01.4-.06.62.46.24.58.83 2 .9 2.14.07.14.12.31.02.5-.1.19-.14.31-.28.48-.14.17-.29.38-.41.5-.14.14-.28.29-.12.56.17.28.75 1.23 1.61 1.99 1.1.98 2.03 1.28 2.31 1.42.28.14.45.12.62-.07.17-.19.71-.83.9-1.11.19-.28.38-.24.64-.14.26.1 1.66.78 1.94.92.28.14.46.21.53.33.07.12.07.7-.17 1.37"/></svg>
                            Hubungi via WhatsApp
                        </a>
                    </div>
                </div>
            </section>

            <section id="faq" class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8" x-data="{active: 0}">
    <h2 class="text-center text-3xl font-bold text-blue-950">Pertanyaan yang Sering Diajukan</h2>
    <div class="mt-8 space-y-3">
        @foreach ([
            [
                'q' => 'Berapa rata-rata biaya pendaftaran di lembaga ini?',
                'a' => 'Biaya pendaftaran cukup terjangkau dan kami memberikan promo diskon spesial setiap pendaftaran semester baru. Silakan hubungi admin kami (WhatsApp) untuk detail pricelist program.'
            ],
            [
                'q' => 'Bagaimana jadwal belajarnya?',
                'a' => 'Jadwal sangat fleksibel menyesuaikan jam sekolah anak Anda (sistem ganjil-genap pagi/sore hari).'
            ],
            [
                'q' => 'Lokasi bimbel berada dimana saja?',
                'a' => 'Pusat kami berlokasi di area strategis tengah kota. Segera buka peta / panduan alamat di laman informasi pendaftaran cabang.'
            ],
            [
                'q' => 'Bagaimana progres anak dipantau?',
                'a' => 'Tutor secara periodik mengevaluasi hasil Try Out dan latihan mandiri, lalu melaporkannya secara digital kepada orang tua untuk dievaluasi.'
            ],
        ] as $index => $faq)
            <div class="overflow-hidden rounded-xl border border-blue-100 bg-white">
                <button @click="active = active === {{ $index }} ? -1 : {{ $index }}" class="flex w-full items-center justify-between px-5 py-4 text-left text-sm font-semibold text-blue-900">
                    <span>{{ $faq['q'] }}</span>
                    <span x-text="active === {{ $index }} ? '-' : '+'"></span>
                </button>
                <div x-show="active === {{ $index }}" x-transition class="border-t border-blue-50 px-5 py-4 text-sm text-slate-600">
                    {{ $faq['a'] }}
                </div>
            </div>
        @endforeach
    </div>
</section>
        </main>

        <footer class="bg-[#060b16] py-10 text-slate-300">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 md:grid-cols-2 lg:grid-cols-3 lg:px-8">
                <div>
                    <img src="{{ isset($settings['logo_filename']) ? asset('image/' . $settings['logo_filename']) : asset('image/logo-bimbel.png') }}" alt="Logo Jarimatrik" class="mb-4 h-12 w-auto">
                    <p class="text-sm text-slate-400">Bimbel Terbaik di Tegal untuk Masa Depan Anak Lebih Cerah
Bimbel Jarimatrik Tegal, Bantu anak lebih memahami pelajaran dengan metode belajar modern, tutor berpengalaman, dan suasana belajar yang nyaman.</p>
                </div>
                <div>
                    <h3 class="mb-3 text-xl font-semibold text-white">Tentang Jarimatrik</h3>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li>Bimbel Jarimatrik, solusi digital terpadu untuk manajemen bimbel modern.</li>
                        <li>Kelola siswa, tutor, jadwal, dan pembayaran dalam satu platform.</li>
                        <li>Tingkatkan efisiensi operasional dengan sistem yang praktis dan terintegrasi.</li>
                        <li>Monitoring jadwal dan presensi lebih akurat dan real-time.</li>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-3 text-xl font-semibold text-white">Alamat</h3>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li>{{ $settings['footer_address'] ?? 'Graha Indoweb, Jl. Kahuripan 47, Kediri, Jawa Timur.' }}</li>
                        <li><span class="text-blue-400">Phone:</span> {{ $settings['footer_phone1'] ?? '6281233640003' }}</li>
                        <li><span class="text-blue-400">Phone:</span> {{ $settings['footer_phone2'] ?? '6282210880003' }}</li>
                        <li><span class="text-blue-400">Email:</span> {{ $settings['footer_email'] ?? 'esekolahnet@gmail.com' }}</li>
                        <li><span class="text-blue-400">Web:</span> {{ $settings['footer_web'] ?? 'https://jarimatrik.co.id' }}</li>
                    </ul>
                </div>
            </div>
            <div class="mx-auto mt-8 flex max-w-7xl flex-col items-center justify-between gap-2 border-t border-slate-800 px-4 pt-5 text-xs text-slate-500 sm:flex-row sm:px-6 lg:px-8">
                <p>&copy; {{ date('Y') }} Bimbel Jarimatrik. All rights reserved.</p>
                <p>Privacy Policy</p>
            </div>
        </footer>

        <a href="https://wa.me/{{ $settings['whatsapp_number'] ?? '6281200000000' }}" target="_blank" class="fixed bottom-6 right-6 inline-flex h-12 w-12 items-center justify-center rounded-full bg-green-500 text-white shadow-lg ring-4 ring-green-100">
            <span class="sr-only">WhatsApp</span>
            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2C6.58 2 2.16 6.42 2.16 11.88c0 1.93.56 3.81 1.6 5.41L2 22l4.86-1.67a9.8 9.8 0 0 0 5.18 1.5h.01c5.46 0 9.88-4.42 9.88-9.88S17.5 2 12.04 2m5.74 13.97c-.24.67-1.4 1.29-1.93 1.37-.49.07-1.1.1-1.78-.12-.41-.13-.93-.3-1.6-.59-2.82-1.22-4.66-4.09-4.8-4.28-.13-.19-1.14-1.52-1.14-2.9 0-1.38.72-2.06.98-2.34.26-.28.56-.35.74-.35.19 0 .37.01.53.01.17.01.4-.06.62.46.24.58.83 2 .9 2.14.07.14.12.31.02.5-.1.19-.14.31-.28.48-.14.17-.29.38-.41.5-.14.14-.28.29-.12.56.17.28.75 1.23 1.61 1.99 1.1.98 2.03 1.28 2.31 1.42.28.14.45.12.62-.07.17-.19.71-.83.9-1.11.19-.28.38-.24.64-.14.26.1 1.66.78 1.94.92.28.14.46.21.53.33.07.12.07.7-.17 1.37"/></svg>
        </a>

        <a
            x-show="showTop"
            x-transition
            href="#hero"
            @click.prevent="scrollToSection('hero')"
            class="fixed bottom-20 right-6 z-[60] rounded-full bg-blue-600 p-3 text-white shadow-lg"
        >
            <span class="sr-only">Back to top</span>
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 15 7-7 7 7"/></svg>
        </a>
    </div>
</x-layouts.landing>
