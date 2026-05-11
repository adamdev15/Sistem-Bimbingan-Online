<x-layouts.dashboard-shell title="Pengaturan Harga Cabang">
    <div class="space-y-6">
        <x-module-page-header title="Harga Materi per Cabang" description="Sesuaikan biaya pendaftaran dan SPP untuk setiap materi di masing-masing cabang.">
        </x-module-page-header>

        @if(session('success'))
            <div class="rounded-xl bg-green-50 p-4 border border-green-200">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        <form action="{{ route('branch-prices.update') }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="space-y-8">
                @foreach($cabangs as $cabang)
                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5">
                        <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                                <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                Cabang: {{ $cabang->nama_cabang }}
                            </h3>
                            <span class="text-xs text-slate-500 font-medium">{{ $cabang->kota }}</span>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50/50">
                                    <tr class="text-left text-xs font-bold text-slate-500 uppercase tracking-widest">
                                        <th class="px-6 py-3">Materi</th>
                                        <th class="px-6 py-3">Biaya Pendaftaran (Rp)</th>
                                        <th class="px-6 py-3">Biaya SPP (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach($cabang->branchPrices as $price)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="font-semibold text-slate-900">{{ $price->materiLes->nama_materi }}</div>
                                                <div class="text-[10px] text-slate-400">ID: #{{ $price->id }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <input type="hidden" name="prices[{{ $price->id }}][id]" value="{{ $price->id }}">
                                                <div class="relative">
                                                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 text-xs font-bold">Rp</span>
                                                    <input type="number" name="prices[{{ $price->id }}][biaya_daftar]" value="{{ (int)$price->biaya_daftar }}" class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="relative">
                                                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 text-xs font-bold">Rp</span>
                                                    <input type="number" name="prices[{{ $price->id }}][biaya_spp]" value="{{ (int)$price->biaya_spp }}" class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-8 py-3 text-sm font-bold text-white shadow-lg transition hover:bg-slate-800 active:scale-95">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Perubahan Harga
                </button>
            </div>
        </form>
    </div>
</x-layouts.dashboard-shell>
