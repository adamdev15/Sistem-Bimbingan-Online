@php
    $settings = \App\Models\Setting::pluck('value', 'setting_key')->toArray();
@endphp
<x-layouts.landing title="Pendaftaran Siswa Baru - eBimbel">
    <div
        x-data="{
            showTop: false,
            mobileMenu: false,
            step: 1,
            agreed: false,
            // Form Data
            form: {
                // Step 2 Data Siswa
                name: '', email: '', password: '', password_confirmation: '', jenis_kelamin: '', no_hp: '', alamat: '', cabang_id: '', tempat_lahir: '', tanggal_lahir: '', asal_sekolah: '', nis: '', materi_les_id: '',
                // Step 3 Data Orang Tua
                nama_ayah: '', pekerjaan_ayah: '', tempat_lahir_ayah: '', tanggal_lahir_ayah: '',
                nama_ibu: '', pekerjaan_ibu: '', tempat_lahir_ibu: '', tanggal_lahir_ibu: '', no_hp_orang_tua: ''
            },
            nextStep() {
                if(this.step === 1) {
                    this.step = 2;
                } else if(this.step === 2) {
                    if(!this.form.name || !this.form.email || !this.form.password || !this.form.cabang_id || !this.form.no_hp) {
                        window.Toast.fire({ icon: 'warning', title: 'Data penting di Data Siswa (Nama, Email, Password, Cabang, No HP) wajib diisi!' });
                        return;
                    }
                    if(this.form.password !== this.form.password_confirmation) {
                        window.Toast.fire({ icon: 'warning', title: 'Konfirmasi password tidak cocok!' });
                        return;
                    }
                    this.step = 3;
                }
            },
            prevStep() {
                if(this.step > 1) {
                    this.step--;
                }
            },
            submitForm() {
                if(!this.form.nama_ayah && !this.form.nama_ibu) {
                    window.Toast.fire({ icon: 'warning', title: 'Mohon isi setidaknya nama salah satu orang tua!' });
                    return;
                }
                this.$refs.wizardForm.submit();
            }
        }"
        x-effect="document.body.classList.toggle('overflow-hidden', mobileMenu)"
        @keydown.escape.window="mobileMenu = false"
        class="min-h-screen bg-slate-50 flex flex-col"
    >
        <header class="sticky top-0 z-40 border-b border-blue-100/80 bg-white/95 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
                <a href="{{ route('landing') }}" class="flex items-center gap-2">
                    <img src="{{ isset($settings['logo_filename']) ? asset('image/' . $settings['logo_filename']) : asset('image/logo-bimbel.png') }}" alt="Logo Bimbel" class="h-16 w-auto">
                </a>
                <nav class="hidden items-center gap-1 text-sm font-semibold text-slate-600 md:flex" aria-label="Navigasi utama desktop">
                    <a href="{{ route('landing') }}" class="rounded-lg px-3 py-2 transition hover:bg-blue-50 hover:text-blue-800">Beranda</a>
                </nav>
                <div class="hidden items-center gap-3 md:flex">
                    <a href="{{ route('login') }}" class="rounded-lg border border-blue-200 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50">Login</a>
                </div>
            </div>
        </header>

        <main class="flex-1 max-w-4xl w-full mx-auto p-4 sm:p-6 lg:p-8 mt-10 mb-20">
            
            <div class="bg-white rounded-2xl shadow-xl ring-1 ring-slate-900/5 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-700 to-blue-900 px-6 py-8 text-white text-center">
                    <h1 class="text-3xl font-bold">Pendaftaran Siswa Baru</h1>
                    <p class="text-blue-100 mt-2">Mari bergabung dan tingkatkan prestasi belajarmu bersama kami.</p>
                </div>

                <!-- Stepper UI -->
                <div class="border-b border-slate-100 px-8 py-5 bg-slate-50">
                    <div class="flex items-center justify-between text-sm font-medium relative">
                        <div class="absolute left-0 top-1/2 -z-10 w-full h-1 bg-slate-200 -translate-y-1/2"></div>
                        <div class="absolute left-0 top-1/2 -z-10 h-1 bg-blue-600 transition-all duration-300 -translate-y-1/2" :style="'width: ' + ((step - 1) / 2 * 100) + '%'"></div>
                        
                        <div class="flex flex-col items-center gap-2 bg-slate-50 px-2" :class="step >= 1 ? 'text-blue-700' : 'text-slate-400'">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold relative z-10 transition-colors" :class="step >= 1 ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-200 text-slate-500'">1</div>
                            <span class="text-xs font-semibold hidden sm:block">Ketentuan</span>
                        </div>
                        <div class="flex flex-col items-center gap-2 bg-slate-50 px-2" :class="step >= 2 ? 'text-blue-700' : 'text-slate-400'">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold relative z-10 transition-colors" :class="step >= 2 ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-200 text-slate-500'">2</div>
                            <span class="text-xs font-semibold hidden sm:block">Data Siswa</span>
                        </div>
                        <div class="flex flex-col items-center gap-2 bg-slate-50 px-2" :class="step >= 3 ? 'text-blue-700' : 'text-slate-400'">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold relative z-10 transition-colors" :class="step >= 3 ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-200 text-slate-500'">3</div>
                            <span class="text-xs font-semibold hidden sm:block">Data Orang Tua</span>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form x-ref="wizardForm" method="POST" action="{{ route('register') }}" class="p-6 sm:p-10">
                    @csrf
                    
                    @if ($errors->any())
                        <div class="mb-6 rounded-xl bg-red-50 p-4 border border-red-200">
                            <ul class="list-inside list-disc text-sm text-red-600">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Step 1: Ketentuan -->
                    <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                        <h2 class="text-xl font-bold text-slate-900 mb-4">Ketentuan & Langkah Pendaftaran</h2>
                        <div class="prose prose-blue max-w-none text-slate-600 bg-blue-50/50 p-6 rounded-xl border border-blue-100 prose-ol:pl-4">
                            {!! $settings['registration_terms'] ?? '<ol><li>Mengisi form data siswa secara lengkap dan benar.</li><li>Mengisi form data orang tua/wali.</li><li>Setelah mendaftar, lakukan login dengan email dan password.</li></ol>' !!}
                        </div>
                        
                        <div class="mt-6 flex items-center gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-sm cursor-pointer" @click="agreed = !agreed">
                            <input type="checkbox" id="agreed" x-model="agreed" @click.stop class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            <label for="agreed" class="text-sm font-medium text-slate-700 cursor-pointer select-none">
                                Saya telah membaca, mengerti, dan menyetujui ketentuan pendaftaran di atas.
                            </label>
                        </div>
                        
                        <div class="mt-8 flex justify-end">
                            <button type="button" @click="nextStep" :disabled="!agreed" :class="!agreed ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm ring-1 ring-blue-600/20 transition focus:outline-none focus:ring-4 focus:ring-blue-500/30">
                                Lanjutkan ke Data Siswa
                                <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Data Siswa -->
                    <div x-show="step === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" style="display: none;">
                        <h2 class="text-xl font-bold text-slate-900 mb-6">Informasi Data Siswa</h2>
                        
                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="form.name" placeholder="Misal: Budi Santoso" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Email Akun (Login) <span class="text-red-500">*</span></label>
                                <input type="email" name="email" x-model="form.email" placeholder="contoh@gmail.com" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Password <span class="text-red-500">*</span></label>
                                <input type="password" name="password" x-model="form.password" placeholder="Min. 8 karakter" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Konfirmasi Password <span class="text-red-500">*</span></label>
                                <input type="password" name="password_confirmation" x-model="form.password_confirmation" placeholder="Ulangi password" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">
                            </div>

                            <div class="sm:col-span-2 pt-4 pb-2 border-b border-slate-100">
                                <h3 class="font-bold text-slate-800">Detail Profil</h3>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Tempat Lahir</label>
                                <input type="text" name="tempat_lahir" x-model="form.tempat_lahir" placeholder="Kota lahir" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" x-model="form.tanggal_lahir" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Jenis Kelamin</label>
                                <select name="jenis_kelamin" x-model="form.jenis_kelamin" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 text-slate-700 bg-white">
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="laki_laki">Laki-Laki</option>
                                    <option value="perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Asal Sekolah</label>
                                <input type="text" name="asal_sekolah" x-model="form.asal_sekolah" placeholder="Misal: SDN 1 Tegal" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">NIS (Nomor Induk Siswa)</label>
                                <input type="text" name="nis" x-model="form.nis" placeholder="Opsional" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Nomor HP/WA <span class="text-red-500">*</span></label>
                                <input type="text" name="no_hp" x-model="form.no_hp" placeholder="08123xxx" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Pilih Cabang Bimbel <span class="text-red-500">*</span></label>
                                <select name="cabang_id" x-model="form.cabang_id" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 bg-white">
                                    <option value="">Pilih Cabang</option>
                                    @foreach($cabangs as $c)
                                        <option value="{{ $c->id }}">{{ $c->nama_cabang }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Materi Les Pilihan</label>
                                <select name="materi_les_id" x-model="form.materi_les_id" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 bg-white">
                                    <option value="">Pilih Materi</option>
                                    @foreach($materiLes as $m)
                                        <option value="{{ $m->id }}">{{ $m->nama_materi }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700">Alamat Lengkap</label>
                                <textarea name="alamat" x-model="form.alamat" rows="2" placeholder="Nama jalan, RT/RW, Kecamatan..." class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10"></textarea>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-between">
                            <button type="button" @click="prevStep" class="inline-flex items-center px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 rounded-xl">
                                Kembali
                            </button>
                            <button type="button" @click="nextStep" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm ring-1 ring-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30">
                                Lanjutkan ke Data Orang Tua
                                <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Data Orang Tua -->
                    <div x-show="step === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" style="display: none;">
                        <h2 class="text-xl font-bold text-slate-900 mb-6">Informasi Data Orang Tua</h2>
                        
                        <div class="grid sm:grid-cols-2 gap-5">
                            <div class="sm:col-span-2 pb-2 border-b border-slate-100">
                                <h3 class="font-bold text-slate-800 text-blue-800">Data Ayah</h3>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Nama Ayah</label>
                                <input type="text" name="nama_ayah" x-model="form.nama_ayah" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Pekerjaan Ayah</label>
                                <input type="text" name="pekerjaan_ayah" x-model="form.pekerjaan_ayah" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Tempat Lahir Ayah</label>
                                <input type="text" name="tempat_lahir_ayah" x-model="form.tempat_lahir_ayah" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Tanggal Lahir Ayah</label>
                                <input type="date" name="tanggal_lahir_ayah" x-model="form.tanggal_lahir_ayah" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">
                            </div>

                            <div class="sm:col-span-2 pb-2 border-b border-slate-100 mt-4">
                                <h3 class="font-bold text-slate-800 text-blue-800">Data Ibu</h3>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Nama Ibu</label>
                                <input type="text" name="nama_ibu" x-model="form.nama_ibu" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Pekerjaan Ibu</label>
                                <input type="text" name="pekerjaan_ibu" x-model="form.pekerjaan_ibu" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Tempat Lahir Ibu</label>
                                <input type="text" name="tempat_lahir_ibu" x-model="form.tempat_lahir_ibu" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Tanggal Lahir Ibu</label>
                                <input type="date" name="tanggal_lahir_ibu" x-model="form.tanggal_lahir_ibu" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">
                            </div>

                            <div class="sm:col-span-2 pb-2 border-b border-slate-100 mt-4">
                                <h3 class="font-bold text-slate-800">Kontak Orang Tua</h3>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700">No HP/WA Orang Tua</label>
                                <input type="text" name="no_hp_orang_tua" x-model="form.no_hp_orang_tua" placeholder="08123xxx" class="mt-1 w-full sm:w-1/2 rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">
                                <p class="text-xs text-slate-400 mt-2">Nomor yang bisa dihubungi untuk urusan administrasi dan laporan.</p>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-between">
                            <button type="button" @click="prevStep" class="inline-flex items-center px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 rounded-xl">
                                Kembali
                            </button>
                            <button type="button" @click="submitForm" class="inline-flex items-center justify-center rounded-xl bg-emerald-500 px-8 py-3 text-sm font-semibold text-white shadow-sm ring-1 ring-emerald-500/20 transition hover:bg-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-500/30">
                                Daftar Sekarang!
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </main>
        
        <footer class="bg-[#060b16] py-10 text-slate-300">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 md:grid-cols-2 lg:grid-cols-3 lg:px-8">
                <div>
                    <img src="{{ isset($settings['logo_filename']) ? asset('image/' . $settings['logo_filename']) : asset('image/logo-bimbel.png') }}" alt="Logo eBimbel" class="mb-4 h-12 w-auto">
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
                        <li>{{ $settings['footer_address'] ?? 'Graha Indoweb, Jl. Kahuripan 47, Kediri, Jawa Timur.' }}</li>
                        <li><span class="text-blue-400">Phone:</span> {{ $settings['footer_phone1'] ?? '6281233640003' }}</li>
                        <li><span class="text-blue-400">Phone:</span> {{ $settings['footer_phone2'] ?? '6282210880003' }}</li>
                        <li><span class="text-blue-400">Email:</span> {{ $settings['footer_email'] ?? 'esekolahnet@gmail.com' }}</li>
                        <li><span class="text-blue-400">Web:</span> {{ $settings['footer_web'] ?? 'https://ebimbel.co.id' }}</li>
                    </ul>
                </div>
            </div>
            <div class="mx-auto mt-8 flex max-w-7xl flex-col items-center justify-between gap-2 border-t border-slate-800 px-4 pt-5 text-xs text-slate-500 sm:flex-row sm:px-6 lg:px-8">
                <p>&copy; {{ date('Y') }} eBimbel. All rights reserved.</p>
                <p>Privacy Policy</p>
            </div>
        </footer>
    </div>
</x-layouts.landing>
