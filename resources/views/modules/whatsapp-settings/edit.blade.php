<x-layouts.dashboard-shell title="Pengaturan Notifikasi WhatsApp — eBimbel">
    <div class="space-y-6">
        <x-module-page-header
            title="Integrasi Notifikasi WhatsApp"
            description="Kelola Pesan Notifikasi Otomatis WhatsApp"
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
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Koneksi</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    @foreach ($settings as $s)
                        @if (str_starts_with($s->setting_key, 'wa.template'))
                            @continue
                        @endif
                        <input type="hidden" name="rows[{{ $s->id }}][key]" value="{{ $s->setting_key }}">
                        @if ($s->setting_key === 'whatsapp.enabled')
                            <div class="sm:col-span-2">
                                <label class="text-xs font-semibold uppercase text-slate-500">{{ $s->name }}</label>
                                <select name="rows[{{ $s->id }}][value]" class="mt-1.5 w-full max-w-xs rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                                    <option value="0" @selected(old('rows.'.$s->id.'.value', $s->value) === '0' || old('rows.'.$s->id.'.value', $s->value) === '')>Nonaktif</option>
                                    <option value="1" @selected(old('rows.'.$s->id.'.value', $s->value) === '1')>Aktif</option>
                                </select>
                                <p class="mt-1 text-xs text-slate-500">Bila belum ada baris di database, aktif/nonaktif bisa dibantu <code class="rounded bg-slate-100 px-1">WHATSAPP_ENABLED</code> di .env.</p>
                            </div>
                        @elseif ($s->setting_key === 'whatsapp.token')
                            <div class="sm:col-span-2">
                                <label class="text-xs font-semibold uppercase text-slate-500">{{ $s->name }}</label>
                                <input type="password" name="rows[{{ $s->id }}][value]" value="{{ old('rows.'.$s->id.'.value', $s->value) }}" autocomplete="new-password" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 font-mono text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            </div>
                        @else
                            <div class="sm:col-span-2">
                                <label class="text-xs font-semibold uppercase text-slate-500">{{ $s->name }}</label>
                                <input type="text" name="rows[{{ $s->id }}][value]" value="{{ old('rows.'.$s->id.'.value', $s->value) }}" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Template pesan</h2>
                <p class="mt-1 text-xs text-slate-500">Gunakan placeholder seperti <code class="rounded bg-slate-100 px-1">:nama</code> sesuai keterangan di tiap bidang.</p>
                <div class="mt-4 space-y-5">
                    @foreach ($settings as $s)
                        @if (! str_starts_with($s->setting_key, 'wa.template'))
                            @continue
                        @endif
                        <div>
                            <input type="hidden" name="rows[{{ $s->id }}][key]" value="{{ $s->setting_key }}">
                            <label class="text-xs font-semibold text-slate-700">{{ $s->name }}</label>
                            @if (! empty($placeholdersHelp[$s->setting_key] ?? null))
                                <p class="mt-0.5 text-[11px] text-slate-500">Placeholder: {{ $placeholdersHelp[$s->setting_key] }}</p>
                            @endif
                            <textarea name="rows[{{ $s->id }}][value]" rows="5" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-500/15">{{ old('rows.'.$s->id.'.value', $s->value) }}</textarea>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">Simpan pengaturan</button>
                <a href="{{ route('dashboard') }}" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
            </div>
        </form>
    </div>
</x-layouts.dashboard-shell>
