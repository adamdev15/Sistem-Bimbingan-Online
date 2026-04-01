@php $isSiswaJadwal = auth()->user()->hasRole('siswa'); @endphp
<x-layouts.dashboard-shell title="Jadwal ? eBimbel">
    <div x-data="{createOpen:false,editOpen:false,deleteOpen:false,edit:{id:null,tutor_id:'',cabang_id:'',mata_pelajaran_id:'',hari:'senin',jam_mulai:'08:00',jam_selesai:'09:00'},removeId:null}">
        <x-module-page-header
            title="{{ $isSiswaJadwal ? 'Jadwal sesi saya' : 'Jadwal & sesi kelas' }}"
            :description="$isSiswaJadwal ? 'Sesi kelas yang terhubung dengan akun Anda (berdasarkan riwayat kehadiran).' : 'Kalender mingguan, tutor, cabang, dan daftar sesi.'"
        >
            
        </x-module-page-header>

        <form method="GET" class="mb-6 grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
            <div class="flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                @if (auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']))
                    <select name="cabang_id" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <option value="">Cabang - semua</option>
                        @foreach ($cabangs as $cabang)
                            <option value="{{ $cabang->id }}" @selected(($filters['cabang_id'] ?? null) == $cabang->id)>{{ $cabang->nama_cabang }}</option>
                        @endforeach
                    </select>
                @endif
                <select name="hari" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    <option value="">Hari - semua</option>
                    @foreach (['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'] as $hari)
                        <option value="{{ $hari }}" @selected(($filters['hari'] ?? '') === $hari)>{{ ucfirst($hari) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Terapkan</button>
                <a href="{{ route('jadwal.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Reset</a>
                @if (auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']))
                    <button @click="createOpen = true" type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">
                        Buat sesi baru
                    </button>
                @endif
            </div>
        </form>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="grid grid-cols-7 divide-x divide-slate-100 border-b border-slate-200 bg-slate-50 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">
                @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $d)
                    <div class="px-2 py-3">{{ $d }}</div>
                @endforeach
            </div>
            <div class="grid min-h-[220px] grid-cols-7 divide-x divide-slate-100 text-sm">
                @foreach (range(0, 6) as $i)
                    <div class="space-y-2 p-2 align-top">
                        @foreach ($jadwals->where('hari', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'][$i])->take(2) as $item)
                            <div class="rounded-lg border border-blue-100 bg-blue-50/80 p-2">
                                <p class="font-semibold text-slate-900">{{ substr($item->jam_mulai, 0, 5) }} {{ $item->mapel }}</p>
                                <p class="text-xs text-slate-600">{{ optional($item->cabang)->nama_cabang }} - {{ optional($item->tutor)->nama }}</p>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        <section class="mt-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-semibold text-slate-900">Daftar sesi</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="text-left text-xs font-semibold uppercase text-slate-500">
                        <tr>
                            <th class="py-2 pr-4">Hari</th>
                            <th class="py-2 pr-4">Waktu</th>
                            <th class="py-2 pr-4">Mapel</th>
                            <th class="py-2 pr-4">Tutor</th>
                            <th class="py-2 pr-4">Cabang</th>
                            @if (! $isSiswaJadwal)
                                <th class="py-2">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($jadwals as $j)
                            <tr>
                                <td class="py-3 pr-4">{{ ucfirst($j->hari) }}</td>
                                <td class="py-3 pr-4">{{ substr($j->jam_mulai, 0, 5) }}-{{ substr($j->jam_selesai, 0, 5) }}</td>
                                <td class="py-3 pr-4">{{ $j->mapel }}</td>
                                <td class="py-3 pr-4">{{ optional($j->tutor)->nama }}</td>
                                <td class="py-3 pr-4">{{ optional($j->cabang)->nama_cabang }}</td>
                                @if (! $isSiswaJadwal)
                                    <td class="py-3 space-x-3">
                                        @if (auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']))
                                            <button @click="editOpen = true; edit = {id: {{ $j->id }}, tutor_id: '{{ $j->tutor_id }}', cabang_id: '{{ $j->cabang_id }}', mata_pelajaran_id: '{{ $j->mata_pelajaran_id }}', hari: @js($j->hari), jam_mulai: @js(substr($j->jam_mulai, 0, 5)), jam_selesai: @js(substr($j->jam_selesai, 0, 5))}" type="button" class="text-blue-600 hover:underline">Edit</button>
                                            <button @click="deleteOpen = true; removeId = {{ $j->id }}" type="button" class="text-rose-600 hover:underline">Delete</button>
                                        @else
                                            <span class="text-slate-400">Lihat saja</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ $isSiswaJadwal ? 5 : 6 }}" class="py-6 text-center text-slate-500">Belum ada sesi jadwal.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $jadwals->links() }}</div>
        </section>

        @if (auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']))
        <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
            <div @click.outside="createOpen = false" class="w-full max-w-xl rounded-xl bg-white p-6">
                <h3 class="text-lg font-semibold">Buat Sesi</h3>
                <form method="POST" action="{{ route('jadwal.store') }}" class="mt-4 grid gap-3">
                    @csrf
                    
                    <select name="tutor_id" class="rounded-lg border px-3 py-2">
                        <option value="">Pilih tutor</option>
                        @foreach($tutors as $tutor)
                            <option value="{{ $tutor->id }}">{{ $tutor->nama }}</option>
                        @endforeach
                    </select>
                    <select name="cabang_id" class="rounded-lg border px-3 py-2">
                        <option value="">Pilih cabang</option>
                        @foreach($cabangs as $cabang)
                            <option value="{{ $cabang->id }}">{{ $cabang->nama_cabang }}</option>
                        @endforeach
                    </select>
                    <select name="mata_pelajaran_id" class="rounded-lg border px-3 py-2" required>
                        <option value="">Pilih mata pelajaran</option>
                        @foreach ($mataPelajarans as $mp)
                            <option value="{{ $mp->id }}">{{ $mp->nama }}@if($mp->kode) ({{ $mp->kode }})@endif</option>
                        @endforeach
                    </select>
                    <select name="hari" class="rounded-lg border px-3 py-2">@foreach(['senin','selasa','rabu','kamis','jumat','sabtu','minggu'] as $h)<option value="{{ $h }}">{{ ucfirst($h) }}</option>@endforeach</select>
                    <div class="grid grid-cols-2 gap-3"><input name="jam_mulai" type="time" class="rounded-lg border px-3 py-2"><input name="jam_selesai" type="time" class="rounded-lg border px-3 py-2"></div>
                    <div class="flex justify-end gap-2"><button type="button" @click="createOpen = false" class="rounded border px-3 py-2">Batal</button><button class="rounded bg-blue-600 px-3 py-2 text-white">Simpan</button></div>
                </form>
            </div>
        </div>

        <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
            <div @click.outside="editOpen = false" class="w-full max-w-xl rounded-xl bg-white p-6">
                <h3 class="text-lg font-semibold">Edit Sesi</h3>
                <form method="POST" :action="`{{ url('/jadwal') }}/${edit.id}`" class="mt-4 grid gap-3">
                    @csrf @method('PUT')
                    <select name="tutor_id" x-model="edit.tutor_id" class="rounded-lg border px-3 py-2">@foreach($tutors as $tutor)<option value="{{ $tutor->id }}">{{ $tutor->nama }}</option>@endforeach</select>
                    <select name="cabang_id" x-model="edit.cabang_id" class="rounded-lg border px-3 py-2">@foreach($cabangs as $cabang)<option value="{{ $cabang->id }}">{{ $cabang->nama_cabang }}</option>@endforeach</select>
                    <select name="mata_pelajaran_id" x-model="edit.mata_pelajaran_id" class="rounded-lg border px-3 py-2" required>
                        @foreach ($mataPelajarans as $mp)
                            <option value="{{ $mp->id }}">{{ $mp->nama }}@if($mp->kode) ({{ $mp->kode }})@endif</option>
                        @endforeach
                    </select>
                    <select name="hari" x-model="edit.hari" class="rounded-lg border px-3 py-2">@foreach(['senin','selasa','rabu','kamis','jumat','sabtu','minggu'] as $h)<option value="{{ $h }}">{{ ucfirst($h) }}</option>@endforeach</select>
                    <div class="grid grid-cols-2 gap-3"><input name="jam_mulai" type="time" x-model="edit.jam_mulai" class="rounded-lg border px-3 py-2"><input name="jam_selesai" type="time" x-model="edit.jam_selesai" class="rounded-lg border px-3 py-2"></div>
                    <div class="flex justify-end gap-2"><button type="button" @click="editOpen = false" class="rounded border px-3 py-2">Batal</button><button class="rounded bg-blue-600 px-3 py-2 text-white">Update</button></div>
                </form>
            </div>
        </div>

        <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
            <div @click.outside="deleteOpen = false" class="w-full max-w-md rounded-xl bg-white p-6">
                <h3 class="text-lg font-semibold">Hapus Sesi</h3>
                <form method="POST" :action="`{{ url('/jadwal') }}/${removeId}`" class="mt-4 flex justify-end gap-2">
                    @csrf @method('DELETE')
                    <button type="button" @click="deleteOpen = false" class="rounded border px-3 py-2">Batal</button>
                    <button class="rounded bg-rose-600 px-3 py-2 text-white">Hapus</button>
                </form>
            </div>
        </div>
        @endif
    </div>
</x-layouts.dashboard-shell>
