<x-layouts.dashboard-shell title="Profil Siswa — eBimbel">
    <x-module-page-header :title="'Profil siswa: '.$siswa->nama" description="Detail profil siswa, cabang, dan histori pembayaran." />

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="lg:col-span-2 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <dl class="grid gap-4 sm:grid-cols-2 text-sm">
                <div><dt class="text-slate-500">Email</dt><dd class="font-medium text-slate-900">{{ $siswa->email }}</dd></div>
                <div><dt class="text-slate-500">NIK</dt><dd class="font-medium text-slate-900">{{ $siswa->nik ?? '-' }}</dd></div>
                <div><dt class="text-slate-500">No HP</dt><dd class="font-medium text-slate-900">{{ $siswa->no_hp }}</dd></div>
                <div><dt class="text-slate-500">Cabang</dt><dd class="font-medium text-slate-900">{{ optional($siswa->cabang)->nama_cabang }}</dd></div>
                <div><dt class="text-slate-500">Status</dt><dd class="font-medium text-slate-900">{{ ucfirst($siswa->status) }}</dd></div>
            </dl>
        </section>
        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-slate-900">Pembayaran terakhir</h2>
            <ul class="mt-3 space-y-2 text-sm text-slate-600">
                @forelse($siswa->payments->take(5) as $pay)
                    <li>{{ optional($pay->tanggal_bayar)->format('d M Y') }} — {{ optional($pay->fee)->nama_biaya }} ({{ $pay->status }})</li>
                @empty
                    <li>Belum ada data pembayaran.</li>
                @endforelse
            </ul>
        </section>
    </div>
</x-layouts.dashboard-shell>
