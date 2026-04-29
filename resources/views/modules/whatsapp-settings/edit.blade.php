<x-layouts.dashboard-shell title="Notifikasi WhatsApp — Bimbel Jarimatrik">
    <div x-data="{ testOpen: false, testPhone: '' }" class="space-y-6">
        <x-module-page-header
            title="Notifikasi WhatsApp"
            description="Layanan Gateway WhatsApp menggunakan API."
        />

        @if (session('status'))
            <p class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</p>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('whatsapp-settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Konfigurasi API</h2>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">
                            Whatsapp Gateway
                        </span>
                    </div>
                    <button type="button" @click="testOpen = true" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/10">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        Test Koneksi
                    </button>
                </div>
                
                <div class="mt-4 space-y-4">
                    @foreach ($settings as $s)
                        <input type="hidden" name="rows[{{ $s->id }}][key]" value="{{ $s->setting_key }}">
                        @if ($s->setting_key === 'whatsapp.enabled')
                            <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-100">
                                <div>
                                    <label class="text-xs font-bold uppercase tracking-wider text-slate-700">Status Layanan</label>
                                    <p class="text-[11px] text-slate-500">Aktifkan untuk mulai mengirim notifikasi otomatis.</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-bold {{ $s->value == '1' ? 'text-emerald-600' : 'text-slate-400' }}">
                                        {{ $s->value == '1' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                    <label class="relative inline-flex cursor-pointer items-center">
                                        <input type="hidden" name="rows[{{ $s->id }}][value]" value="0">
                                        <input type="checkbox" name="rows[{{ $s->id }}][value]" value="1" class="peer sr-only" {{ $s->value == '1' ? 'checked' : '' }}>
                                        <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white focus:outline-none"></div>
                                    </label>
                                </div>
                            </div>
                        @endif
                        
                        @if ($s->setting_key === 'whatsapp.token')
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Token / API Key</label>
                                <div class="mt-1.5 relative group">
                                    <input type="password" 
                                        name="rows[{{ $s->id }}][value]" 
                                        value="{{ old('rows.'.$s->id.'.value', $s->value) }}" 
                                        placeholder="Masukkan token Anda..."
                                        class="w-full rounded-xl border border-slate-200 px-4 py-3 font-mono text-sm shadow-sm transition focus:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-500/10">
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500 border-b border-slate-100 pb-4">Template Pesan Otomatis</h2>
                <div class="mt-4 grid gap-6 md:grid-cols-2">
                    @foreach ($settings as $s)
                        @if (! str_starts_with($s->setting_key, 'wa.template'))
                            @continue
                        @endif
                        <div class="space-y-2">
                            <input type="hidden" name="rows[{{ $s->id }}][key]" value="{{ $s->setting_key }}">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-700">{{ $s->name }}</label>
                            </div>
                            <textarea name="rows[{{ $s->id }}][value]" rows="5" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm shadow-sm transition focus:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-500/10">{{ old('rows.'.$s->id.'.value', $s->value) }}</textarea>
                            @if (! empty($placeholdersHelp[$s->setting_key] ?? null))
                                <div class="flex flex-wrap gap-1">
                                    @foreach(explode(', ', $placeholdersHelp[$s->setting_key]) as $tag)
                                        <span class="inline-flex items-center rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-600 border border-slate-200">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 rounded-2xl border border-slate-200/80 bg-slate-50/50 p-4 shadow-sm ring-1 ring-slate-900/5 transition hover:bg-slate-50">
                <a href="{{ route('dashboard') }}" class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:text-slate-900">Batal</a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-8 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:ring-4 focus:ring-slate-900/10">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>

        {{-- MODAL TEST --}}
        <template x-if="testOpen">
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div @click="testOpen = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
                
                <div class="relative w-full max-w-md transform overflow-hidden rounded-3xl bg-white p-8 shadow-2xl ring-1 ring-slate-900/5 transition-all">
                    <div class="mb-6 flex items-center justify-between">
                        <h3 class="text-xl font-bold text-slate-900">Test Notifikasi</h3>
                        <button @click="testOpen = false" class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form action="{{ route('whatsapp-settings.test') }}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Nomor HP Tujuan</label>
                            <div class="mt-1.5 relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                    <span class="text-slate-400 text-sm font-mono">+</span>
                                </div>
                                <input type="text" name="target" placeholder="628xxxxxxxx" class="block w-full rounded-2xl border border-slate-200 pl-8 pr-4 py-3 text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-500/10" required>
                            </div>
                            <p class="mt-2 text-[11px] text-slate-500">Pastikan Token Anda sudah disimpan sebelum melakukan test.</p>
                        </div>

                        <div class="flex gap-3">
                            <button type="button" @click="testOpen = false" class="flex-1 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Batal</button>
                            <button type="submit" class="flex-1 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 hover:bg-blue-700 transition active:scale-[0.98]">Kirim Test</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>
</x-layouts.dashboard-shell>
