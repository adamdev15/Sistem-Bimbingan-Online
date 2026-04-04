<x-layouts.dashboard-shell title="Peserta kelas — eBimbel">
    <div class="space-y-6">
        <x-module-page-header
            title="Peserta kelas"
            :description="'Atur siswa yang terdaftar di: '.$jadwal->mapel.' — '.optional($jadwal->cabang)->nama_cabang.' ('.ucfirst($jadwal->hari).' '.substr($jadwal->jam_mulai, 0, 5).').'"
        >
            <x-slot name="actions">
                <a href="{{ route('jadwal.index') }}" class="inline-flex rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">Kembali ke jadwal</a>
            </x-slot>
        </x-module-page-header>

        @if (session('status'))
            <p class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
            <p class="text-sm text-slate-600">
                Tutor: <span class="font-semibold text-slate-900">{{ optional($jadwal->tutor)->nama ?? '—' }}</span>
            </p>
            <p class="mt-2 text-xs text-slate-500">Hanya siswa <strong>aktif</strong> dari cabang yang sama dengan kelas yang dapat dipilih.</p>

            <form method="POST" action="{{ route('jadwal.peserta.update', $jadwal) }}" class="mt-6 space-y-4">
                @csrf
                @method('PUT')
                <fieldset>
                    <legend class="text-xs font-semibold uppercase tracking-wide text-slate-500">Daftar peserta</legend>
                    <div class="mt-3 max-h-[min(28rem,60vh)] space-y-2 overflow-y-auto rounded-xl border border-slate-100 bg-slate-50/50 p-4">
                        @php $selected = $jadwal->siswas->pluck('id')->all(); @endphp
                        @forelse ($siswaCandidates as $s)
                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-transparent px-2 py-2 hover:border-slate-200 hover:bg-white">
                                <input type="checkbox" name="student_ids[]" value="{{ $s->id }}" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" @checked(in_array($s->id, $selected, true))>
                                <span class="text-sm font-medium text-slate-900">{{ $s->nama }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-slate-500">Tidak ada siswa aktif di cabang ini.</p>
                        @endforelse
                    </div>
                </fieldset>
                <div class="flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-5">
                    <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Simpan peserta</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.dashboard-shell>
