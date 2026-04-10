<x-layouts.dashboard-shell title="Pengaturan Website - eBimbel">
    <div class="space-y-6">
        <x-module-page-header title="Pengaturan Website" description="Kelola Website dengan Ubah konten, text copywriting, dan informasi terkait Website." />

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
            <form method="POST" action="{{ route('settings.website.update') }}" enctype="multipart/form-data" class="space-y-8">
                @csrf
                @method('PUT')

                <!-- Logo & Hero -->
                <div class="border-b border-slate-200 pb-8">
                    <h2 class="text-lg font-bold text-slate-800">Logo & Visual</h2>
                    <p class="text-sm text-slate-500 mb-4">Pengaturan gambar cover dan logo website.</p>
                    <div class="grid sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Logo Bimbel (PNG)</label>
                            <input type="file" name="logo" accept="image/png" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            @if(isset($settings['logo_filename']))
                            <p class="text-xs text-slate-500 mt-2">Aktif: {{ $settings['logo_filename'] }}</p>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Gambar Hero Utama (PNG/JPG)</label>
                            <input type="file" name="hero_image" accept="image/png, image/jpeg" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            @if(isset($settings['hero_filename']))
                            <p class="text-xs text-slate-500 mt-2">Aktif: {{ $settings['hero_filename'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Text Copywriting -->
                <div class="border-b border-slate-200 pb-8">
                    <h2 class="text-lg font-bold text-slate-800">Teks Utama & Copywriting</h2>
                    <p class="text-sm text-slate-500 mb-4">Pengaturan judul utama pada hero section dan halaman utama.</p>
                    <div class="grid gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Tagline Atas (Badge)</label>
                            <input type="text" name="tagline" value="{{ $settings['tagline'] ?? 'Bimbel Terpercaya di Tegal untuk Masa Depan Anak Lebih Cerah' }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Judul Utama (Hero Title)</label>
                            <input type="text" name="hero_title" value="{{ $settings['hero_title'] ?? 'Platform bimbel online profesional untuk cabang, tutor, dan siswa.' }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Deskripsi Hero</label>
                            <textarea name="hero_desc" rows="3" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">{{ $settings['hero_desc'] ?? 'Kelola jadwal, presensi, pembayaran, dan komunikasi WhatsApp dalam satu dashboard yang rapi, responsif, dan siap berkembang.' }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Deskripsi "Apa Itu Bimbel?"</label>
                            <textarea name="about_us" rows="4" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">{{ $settings['about_us'] ?? 'eBimbel merupakan software aplikasi sistem informasi online berbasis web untuk membantu mengelola manajemen dan administrasi bimbel secara real time.' }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Teks Syarat & Ketentuan Pendaftaran (HTML disarankan)</label>
                            <textarea name="registration_terms" rows="6" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-mono focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">{{ $settings['registration_terms'] ?? '<ol><li>Mengisi form pendaftaran siswa secara lengkap.</li><li>Mengisi data orang tua atau wali siswa.</li><li>Lakukan pengecekan secara berkala.</li></ol>' }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Gallery / Suasana Belajar -->
                <div class="border-b border-slate-200 pb-8">
                    <h2 class="text-lg font-bold text-slate-800">Galeri & Suasana Belajar</h2>
                    <p class="text-sm text-slate-500 mb-4">Pengaturan foto galeri untuk section Suasana Belajar (Maksimum 4 foto).</p>
                    <div class="grid sm:grid-cols-2 gap-6">
                        @for ($i = 1; $i <= 4; $i++)
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Foto Suasana Belajar {{ $i }} (PNG/JPG)</label>
                            <input type="file" name="gallery_{{ $i }}" accept="image/png, image/jpeg" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            @if(isset($settings['gallery_'.$i]))
                            <p class="text-xs text-slate-500 mt-2">Aktif: {{ $settings['gallery_'.$i] }}</p>
                            <img src="{{ asset('image/' . $settings['gallery_'.$i]) }}" alt="Gallery {{ $i }}" class="mt-2 h-16 w-16 object-cover rounded-lg border border-slate-200">
                            @endif
                        </div>
                        @endfor
                    </div>
                </div>

                <!-- Footer & Kontak -->
                <div class="pb-2">
                    <h2 class="text-lg font-bold text-slate-800">Kontak & Footer</h2>
                    <p class="text-sm text-slate-500 mb-4">Alamat, no handphone, website, dll.</p>
                    <div class="grid sm:grid-cols-2 gap-6">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700">Alamat Footer</label>
                            <input type="text" name="footer_address" value="{{ $settings['footer_address'] ?? 'Graha Indoweb, Jl. Kahuripan 47, Kediri, Jawa Timur.' }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Phone 1 (CS)</label>
                            <input type="text" name="footer_phone1" value="{{ $settings['footer_phone1'] ?? '6281233640003' }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Phone 2</label>
                            <input type="text" name="footer_phone2" value="{{ $settings['footer_phone2'] ?? '6282210880003' }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Email Footer</label>
                            <input type="email" name="footer_email" value="{{ $settings['footer_email'] ?? 'esekolahnet@gmail.com' }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Website URL</label>
                            <input type="url" name="footer_web" value="{{ $settings['footer_web'] ?? 'https://ebimbel.co.id' }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700">Nomor WhatsApp Link Global (CTA Bawah)</label>
                            <input type="text" name="whatsapp_number" value="{{ $settings['whatsapp_number'] ?? '6281200000000' }}" placeholder="Format 62xxx" class="mt-1 w-full sm:w-1/2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-200 flex justify-end">
                    <button type="submit" class="rounded-xl inline-flex items-center gap-2 bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-sm ring-1 ring-blue-600/20 transition hover:bg-blue-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3"/></svg>
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.dashboard-shell>
