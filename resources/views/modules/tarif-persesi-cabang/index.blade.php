<x-layouts.dashboard-shell title="Tarif Persesi per Cabang - Jarimatrik">
    <div class="space-y-6">
        <x-module-page-header title="Tarif Persesi per Cabang" description="Sesuaikan tarif bayar per sesi untuk tutor di masing-masing cabang. Tarif ini akan otomatis muncul di form input gaji tutor.">
        </x-module-page-header>

        @if(session('status'))
            <div class="rounded-xl bg-emerald-50 p-4 border border-emerald-200">
                <p class="text-sm font-medium text-emerald-800">{{ session('status') }}</p>
            </div>
        @endif

        <form action="{{ route('tarif-persesi-cabang.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-100">
                        <h3 class="font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                            <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Tarif Persesi Tutor
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">Tarif ini digunakan sebagai acuan dalam perhitungan gaji tutor per sesi kehadiran.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50/50">
                                <tr class="text-left text-xs font-bold text-slate-500 uppercase tracking-widest">
                                    <th class="px-6 py-3">Cabang</th>
                                    <th class="px-6 py-3">Kota</th>
                                    <th class="px-6 py-3 w-64">Tarif per Sesi (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($cabangs as $cabang)
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100 text-blue-700 font-bold text-sm">
                                                    {{ strtoupper(substr($cabang->nama_cabang, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-slate-800">{{ $cabang->nama_cabang }}</p>
                                                    <p class="text-xs text-slate-400">ID: {{ $cabang->id }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600">{{ $cabang->kota ?? '-' }}</td>
                                        <td class="px-6 py-4">
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">Rp</span>
                                                <input
                                                    type="number"
                                                    name="prices[{{ $cabang->id }}][nominal]"
                                                    value="{{ old('prices.'.$cabang->id.'.nominal', $cabang->tarifPersesiCabang?->nominal ?? 0) }}"
                                                    min="0"
                                                    step="1000"
                                                    class="w-full rounded-xl border border-slate-200 pl-10 pr-4 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15 font-mono"
                                                    placeholder="0"
                                                >
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm ring-1 ring-blue-600/20 transition hover:bg-blue-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Simpan Tarif
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.dashboard-shell>
