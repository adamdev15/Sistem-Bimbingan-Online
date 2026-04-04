<x-layouts.landing title="Bimbel Management System">
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
        x-init="window.addEventListener('scroll', () => showTop = window.scrollY > 250)"
        x-effect="document.body.classList.toggle('overflow-hidden', mobileMenu)"
        @keydown.escape.window="mobileMenu = false"
    >
        <header class="sticky top-0 z-40 border-b border-blue-100/80 bg-white/95 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
                <a href="{{ route('landing') }}" class="flex items-center gap-2">
                    <img src="{{ asset('image/logo-bimbel.png') }}" alt="Logo Bimbel" class="h-16 w-auto">
                </a>
                <nav class="hidden items-center gap-1 text-sm font-semibold text-slate-600 md:flex" aria-label="Navigasi utama desktop">
                    <a href="#hero" @click.prevent="scrollToSection('hero')" class="rounded-lg px-3 py-2 transition hover:bg-blue-50 hover:text-blue-800">Beranda</a>
                    <a href="#about" @click.prevent="scrollToSection('about')" class="rounded-lg px-3 py-2 transition hover:bg-blue-50 hover:text-blue-800">Apa Itu Bimbel</a>
                    <a href="#features" @click.prevent="scrollToSection('features')" class="rounded-lg px-3 py-2 transition hover:bg-blue-50 hover:text-blue-800">Keunggulan</a>
                    <a href="#services" @click.prevent="scrollToSection('services')" class="rounded-lg px-3 py-2 transition hover:bg-blue-50 hover:text-blue-800">Layanan</a>
                    <a href="#faq" @click.prevent="scrollToSection('faq')" class="rounded-lg px-3 py-2 transition hover:bg-blue-50 hover:text-blue-800">FAQ</a>
                </nav>
                <div class="hidden items-center gap-3 md:flex">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Dashboard</a>
                    @else
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
                        <img src="{{ asset('image/logo-bimbel.png') }}" alt="eBimbel" class="h-7 w-auto">
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
                        </li>
                        <li>
                            <a
                                href="#about"
                                @click.prevent="mobileMenu = false; scrollToSection('about')"
                                class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-blue-50/95 transition hover:bg-white/10 hover:text-white"
                            >
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/10 text-blue-100 ring-1 ring-white/5">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                                </span>
                                Apa Itu Bimbel
                            </a>
                        </li>
                        <li>
                            <a
                                href="#features"
                                @click.prevent="mobileMenu = false; scrollToSection('features')"
                                class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-blue-50/95 transition hover:bg-white/10 hover:text-white"
                            >
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/10 text-blue-100 ring-1 ring-white/5">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/></svg>
                                </span>
                                Keunggulan
                            </a>
                        </li>
                        <li>
                            <a
                                href="#services"
                                @click.prevent="mobileMenu = false; scrollToSection('services')"
                                class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-blue-50/95 transition hover:bg-white/10 hover:text-white"
                            >
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/10 text-blue-100 ring-1 ring-white/5">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.184 2.94-.335.787-.5 1.25-.5 1.858 0 .607.165 1.071.5 1.858.397.904 1.184 1.846 1.184 2.94v4.25M20.25 14.15c-1.5-.75-3.75-1.5-7.5-1.5s-6 1.5-7.5 1.5M20.25 14.15V9.75M3.75 14.15v4.25c0 1.094.787 2.036 1.184 2.94.335.787.5 1.25.5 1.858 0 .607-.165 1.071-.5 1.858-.397.904-1.184 1.846-1.184 2.94v4.25M3.75 14.15c1.5-.75 3.75-1.5 7.5-1.5s6 1.5 7.5 1.5M3.75 14.15V9.75"/></svg>
                                </span>
                                Layanan
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
                        </li>
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
                        <p class="inline-flex rounded-full bg-white/20 px-3 py-1 text-xs font-semibold uppercase tracking-wide">Solusi Modern Mengelola Bimbel</p>
                        <h1 class="text-3xl font-extrabold leading-tight sm:text-4xl lg:text-5xl">Platform bimbel online profesional untuk cabang, tutor, dan siswa.</h1>
                        <p class="max-w-xl text-blue-50">Kelola jadwal, presensi, pembayaran, dan komunikasi WhatsApp dalam satu dashboard yang rapi, responsif, dan siap berkembang.</p>
                        <div class="flex flex-wrap gap-3">
                            <a href="#services" @click.prevent="scrollToSection('services')" class="rounded-lg bg-white px-5 py-3 text-sm font-bold text-blue-700">Lihat Layanan</a>
                            <a href="#faq" @click.prevent="scrollToSection('faq')" class="rounded-lg border border-white/50 px-5 py-3 text-sm font-bold text-white">Pelajari Lebih Lanjut</a>
                        </div>
                    </div>
                    <div class="p-4 backdrop-blur">
                        <img src="{{ asset('image/hero.png') }}" alt="Hero Bimbel" class="mx-auto w-full rounded-xl">
                    </div>
                </div>
            </section>

            <section id="about" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="grid items-center gap-10 lg:grid-cols-2">
                    <div class="order-2 lg:order-1">
                        <img src="{{ asset('image/whats.png') }}" alt="Apa itu eBimbel" class="mx-auto w-full max-w-lg">
                    </div>
                    <div class="order-1 space-y-4 lg:order-2">
                        <h2 class="text-3xl font-bold text-blue-950">Apa itu eBimbel?</h2>
                        <p class="text-slate-700">eBimbel merupakan software aplikasi sistem informasi online berbasis web untuk membantu mengelola manajemen dan administrasi bimbel secara real time. Sehingga bimbel Anda menjadi lebih maju, profesional, dan siap go digital.</p>
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

            <section id="features" class="bg-white py-16">
                    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <h2 class="text-center text-3xl font-bold text-blue-950">Keunggulan Aplikasi eBimbel</h2>

                        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ([
                                [
                                    'title' => 'Multi Cabang',
                                    'desc' => 'Satu sistem untuk banyak cabang dan kelas.',
                                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7h6v6H3V7zm12 0h6v6h-6V7zM9 17h6v4H9v-4z"/>'
                                ],
                                [
                                    'title' => 'Realtime Dashboard',
                                    'desc' => 'Pantau performa harian dari satu layar.',
                                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 17l6-6 4 4 8-8"/>'
                                ],
                                [
                                    'title' => 'Notifikasi WA',
                                    'desc' => 'Pengingat pembayaran dan jadwal otomatis.',
                                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10h8m-8 4h5m-9 6l-2-5a9 9 0 1116 0l-2 5-4-2H8l-4 2z"/>'
                                ],
                                [
                                    'title' => 'Laporan Cepat',
                                    'desc' => 'Ekspor data keuangan, presensi, dan gaji tutor.',
                                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6M7 4h6l4 4v12H7V4z"/>'
                                ],
                            ] as $feature)

                                <article class="rounded-xl border border-blue-100 bg-blue-50/50 p-5 hover:shadow-md transition">
                                    
                                    <svg class="mb-4 h-10 w-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        {!! $feature['icon'] !!}
                                    </svg>

                                    <h3 class="text-lg font-semibold text-blue-900">
                                        {{ $feature['title'] }}
                                    </h3>

                                    <p class="mt-2 text-sm text-slate-600">
                                        {{ $feature['desc'] }}
                                    </p>
                                </article>

                            @endforeach
                        </div>
                    </div>
                </section>

            <section id="services" class="bg-slate-50 py-16">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <h2 class="text-center text-3xl font-bold text-blue-950">Layanan Aplikasi eBimbel</h2>
                    <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ([
                            ['name' => 'Manajemen Siswa', 'img' => 'layanan.png'],
                            ['name' => 'Manajemen Tutor', 'img' => 'hero.png'],
                            ['name' => 'Jadwal Mengajar', 'img' => 'layanan.png'],
                            ['name' => 'Presensi Digital', 'img' => 'hero.png'],
                            ['name' => 'Pembayaran SPP', 'img' => 'layanan.png'],
                            ['name' => 'Laporan Cabang', 'img' => 'hero.png'],
                        ] as $service)
                            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                                <img src="{{ asset('image/' . $service['img']) }}" alt="{{ $service['name'] }}" class="mb-4 h-36 w-full rounded-lg bg-slate-100 object-cover object-top">
                                <h3 class="font-semibold text-blue-900">{{ $service['name'] }}</h3>
                                <p class="mt-2 text-sm text-slate-600">Dirancang untuk workflow bimbel yang cepat, rapi, dan mudah dipakai tim operasional.</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="bg-gradient-to-br from-blue-800 to-blue-900 py-14 text-white">
                <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                    <img src="{{ asset('image/logo-bimbel.png') }}" alt="Logo eBimbel" class="mx-auto mb-4 h-14 w-auto">
                    <h2 class="text-3xl font-bold">Satu aplikasi untuk semua kebutuhan bimbel Anda</h2>
                    <p class="mt-3 text-blue-100">Siap digunakan untuk operasional harian, monitoring manajemen, dan pengembangan bisnis belajar.</p>
                </div>
            </section>

            <section id="faq" class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8" x-data="{active: 0}">
    <h2 class="text-center text-3xl font-bold text-blue-950">Pertanyaan yang Sering Diajukan</h2>
    <div class="mt-8 space-y-3">
        @foreach ([
            [
                'q' => 'Apakah eBimbelPro mendukung multi cabang?',
                'a' => 'Ya, eBimbelPro dirancang untuk mendukung banyak cabang dengan pengelolaan data terpisah namun tetap terintegrasi dalam satu sistem pusat.'
            ],
            [
                'q' => 'Bagaimana sistem presensi dilakukan?',
                'a' => 'Presensi dilakukan langsung oleh tutor berdasarkan jadwal kelas, sehingga data kehadiran siswa tercatat secara real-time dan akurat.'
            ],
            [
                'q' => 'Apakah tersedia fitur pembayaran online?',
                'a' => 'Ya, sistem terintegrasi dengan Midtrans sehingga siswa dapat melakukan pembayaran secara online dengan berbagai metode seperti transfer bank dan e-wallet.'
            ],
            [
                'q' => 'Apakah sistem menyediakan notifikasi otomatis?',
                'a' => 'Ya, eBimbelPro mendukung notifikasi otomatis melalui WhatsApp untuk pengingat jadwal, tagihan, dan konfirmasi pembayaran.'
            ],
            [
                'q' => 'Apakah dashboard dapat diakses melalui perangkat mobile?',
                'a' => 'Tentu, sistem dirancang responsif dan mobile-friendly sehingga dapat digunakan dengan nyaman melalui smartphone maupun desktop.'
            ],
            [
                'q' => 'Siapa saja yang dapat menggunakan sistem ini?',
                'a' => 'Sistem ini memiliki beberapa role seperti Super Admin, Admin Cabang, Tutor, dan Siswa yang masing-masing memiliki akses sesuai kebutuhan.'
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
                    <img src="{{ asset('image/logo-bimbel.png') }}" alt="Logo eBimbel" class="mb-4 h-12 w-auto">
                    <p class="text-sm text-slate-400">Dengan eBimbel, Anda merasakan revolusi pengelolaan bimbel yang lebih modern, cepat, dan efisien untuk admin, guru, serta siswa.</p>
                </div>
                <div>
                    <h3 class="mb-3 text-xl font-semibold text-white">Latest News</h3>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li>Aplikasi bimbel gratis untuk manajemen lembaga kursus.</li>
                        <li>Integrasi online terdepan untuk bimbel dan kursus era digital.</li>
                        <li>Solusi modern efisiensi kerja manajemen lembaga bimbel.</li>
                        <li>Solusi jadwal dan absensi digital.</li>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-3 text-xl font-semibold text-white">Alamat</h3>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li>Graha Indoweb, Jl. Kahuripan 47, Kediri, Jawa Timur.</li>
                        <li><span class="text-blue-400">Phone:</span> 6281233640003</li>
                        <li><span class="text-blue-400">Phone:</span> 6282210880003</li>
                        <li><span class="text-blue-400">Email:</span> esekolahnet@gmail.com</li>
                        <li><span class="text-blue-400">Web:</span> https://ebimbel.co.id</li>
                    </ul>
                </div>
            </div>
            <div class="mx-auto mt-8 flex max-w-7xl flex-col items-center justify-between gap-2 border-t border-slate-800 px-4 pt-5 text-xs text-slate-500 sm:flex-row sm:px-6 lg:px-8">
                <p>&copy; {{ date('Y') }} eBimbel. All rights reserved.</p>
                <p>Privacy Policy</p>
            </div>
        </footer>

        <a href="https://wa.me/6281200000000" target="_blank" class="fixed bottom-6 right-6 inline-flex h-12 w-12 items-center justify-center rounded-full bg-green-500 text-white shadow-lg ring-4 ring-green-100">
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
