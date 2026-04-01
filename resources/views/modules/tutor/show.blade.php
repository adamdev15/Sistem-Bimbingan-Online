<x-layouts.dashboard-shell title="Profil Tutor — eBimbel">
    <x-module-page-header :title="'Profil tutor: '.$tutor->nama" description="Detail tutor, cabang, dan jadwal aktif." />

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="lg:col-span-2 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <dl class="grid gap-4 sm:grid-cols-2 text-sm">
                <div><dt class="text-slate-500">Email</dt><dd class="font-medium text-slate-900">{{ $tutor->email }}</dd></div>
                <div><dt class="text-slate-500">NIK</dt><dd class="font-medium text-slate-900">{{ $tutor->nik ?? '-' }}</dd></div>
                <div><dt class="text-slate-500">No HP</dt><dd class="font-medium text-slate-900">{{ $tutor->no_hp }}</dd></div>
                <div><dt class="text-slate-500">Cabang</dt><dd class="font-medium text-slate-900">{{ optional($tutor->cabang)->nama_cabang }}</dd></div>
                <div><dt class="text-slate-500">Status</dt><dd class="font-medium text-slate-900">{{ ucfirst($tutor->status) }}</dd></div>
            </dl>
        </section>
        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-slate-900">Jadwal aktif</h2>
            <ul class="mt-3 space-y-2 text-sm text-slate-600">
                @forelse($tutor->jadwals->take(5) as $jadwal)
                    <li>{{ ucfirst($jadwal->hari) }} {{ substr($jadwal->jam_mulai,0,5) }}-{{ substr($jadwal->jam_selesai,0,5) }} · {{ $jadwal->mapel }}</li>
                @empty
                    <li>Belum ada jadwal.</li>
                @endforelse
            </ul>
        </section>
    </div>
</x-layouts.dashboard-shell>
