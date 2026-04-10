@php
    $openCreate = $errors->any() && old('form_context') === 'user_create';
    $openEdit = $errors->any() && old('form_context') === 'user_edit';
    $openDelete = $errors->has('user');
    $roleLabels = [
        'super_admin' => 'Super Admin',
        'admin_cabang' => 'Admin Cabang',
        'tutor' => 'Tutor',
        'siswa' => 'Siswa',
    ];
@endphp
<x-layouts.dashboard-shell title="Pengguna — eBimbel">
    <div
        x-data="{
            createOpen: @json($openCreate),
            editOpen: @json($openEdit),
            deleteOpen: @json($openDelete),
            roleLabels: @json($roleLabels),
            edit: {
                id: @json(old('edit_user_id')),
                name: @json(old('name', '')),
                email: @json(old('email', '')),
                role: @json(old('role', 'siswa')),
                emailVerified: @json((bool) old('email_verified', false)),
            },
            removeId: null,
            removeName: '',
        }"
        class="space-y-6"
    >
        <x-module-page-header
            title="Manajemen pengguna"
            description="Kelola akun login, peran akses, dan verifikasi email untuk seluruh sistem."
        >
        </x-module-page-header>

        @if (session('status'))
            <p class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
        @endif

        @if ($errors->has('user'))
            <p class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first('user') }}</p>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5 p-5">

            {{-- FILTER + ACTION --}}
            <div class="flex flex-wrap items-end gap-3 p-4 border-b border-slate-100 mb-6">

                <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap items-end gap-3 flex-1">
                    <div class="min-w-[100px] flex-1">
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Cari</label>
                        <input
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            type="search"
                            placeholder="Nama atau email..."
                            class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none ring-blue-500/0 transition focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15"
                        >
                    </div>
                    <div class="min-w-[160px]">
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Peran</label>
                        <select name="role" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            <option value="">Semua peran</option>
                            @foreach ($roleOptions as $r)
                                <option value="{{ $r }}" @selected(($filters['role'] ?? '') === $r)>{{ $roleLabels[$r] ?? $r }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-[160px]">
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Email</label>
                        <select name="verified" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            <option value="">Semua</option>
                            <option value="1" @selected(($filters['verified'] ?? '') === '1' || ($filters['verified'] ?? '') === 1)>Terverifikasi</option>
                            <option value="0" @selected(($filters['verified'] ?? '') === '0' || ($filters['verified'] ?? '') === 0)>Belum verifikasi</option>
                        </select>
                    </div>
                    <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 transition-all">Filter</button>
                    <a href="{{ route('users.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">Reset</a>
                </form>

                {{-- BUTTON RIGHT --}}
                <div class="flex flex-wrap items-center gap-2 ml-auto">
                    <button
                        type="button"
                        @click="createOpen = true"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm ring-1 ring-blue-600/20 transition-all hover:bg-blue-700"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Tambah pengguna
                    </button>
                </div>
            </div>

            {{-- TABLE --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/90 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3.5">Nama</th>
                            <th class="px-4 py-3.5">Email</th>
                            <th class="px-4 py-3.5">Peran</th>
                            <th class="px-4 py-3.5">Verifikasi</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($users as $u)
                            @php
                                $rname = $u->getRoleNames()->first();
                            @endphp
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-4 py-3.5 font-medium text-slate-900">{{ $u->name }}</td>
                                <td class="px-4 py-3.5 text-slate-600">{{ $u->email }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-800">{{ $roleLabels[$rname] ?? ($rname ?: '—') }}</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    @if ($u->email_verified_at)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">Ya</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800">Belum</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-3">
                                        <button
                                            type="button"
                                            @click="edit.id = {{ $u->id }}; edit.name = @js($u->name); edit.email = @js($u->email); edit.role = @js($rname ?? 'siswa'); edit.emailVerified = @json((bool) $u->email_verified_at); editOpen = true"
                                            class="text-yellow-600 hover:text-yellow-900 bg-yellow-50 hover:bg-yellow-100 p-2 rounded-lg transition-colors" title="Edit">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </button>
                                        @if ($u->is(auth()->user()))
                                            <span class="text-xs text-slate-400" title="Akun sedang dipakai">Anda</span>
                                        @else
                                            <button
                                                type="button"
                                                @click="deleteOpen = true; removeId = {{ $u->id }}; removeName = @js($u->name)"
                                                class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors" title="Hapus">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-slate-500">Tidak ada pengguna yang cocok dengan filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 bg-slate-50/50 px-4 py-3">{{ $users->links() }}</div>
        </div>

        {{-- Modal: create --}}
        <div
            x-show="createOpen"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]"
            role="dialog"
            aria-modal="true"
            aria-labelledby="user-create-title"
        >
            <div
                @click.outside="createOpen = false"
                @keydown.escape.window="createOpen = false"
                class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-xl ring-1 ring-slate-900/5"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 id="user-create-title" class="text-lg font-bold tracking-tight text-slate-900">Tambah pengguna</h3>
                        <p class="mt-1 text-sm text-slate-500">Buat akun baru dan tentukan peran akses di dashboard.</p>
                    </div>
                    <button type="button" @click="createOpen = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('users.store') }}" class="mt-6 space-y-4">
                    @csrf
                    <input type="hidden" name="form_context" value="user_create">
                    <div>
                        <label for="uc-name" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nama</label>
                        <input id="uc-name" name="name" value="{{ old('form_context') === 'user_create' ? old('name') : '' }}" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        @error('name')
                            @if (old('form_context') === 'user_create')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@endif
                        @enderror
                    </div>
                    <div>
                        <label for="uc-email" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</label>
                        <input id="uc-email" name="email" type="email" value="{{ old('form_context') === 'user_create' ? old('email') : '' }}" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        @error('email')
                            @if (old('form_context') === 'user_create')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@endif
                        @enderror
                    </div>
                    <div>
                        <label for="uc-role" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Peran</label>
                        <select id="uc-role" name="role" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            @foreach ($roleOptions as $r)
                                <option value="{{ $r }}" @selected(old('form_context') === 'user_create' && old('role') === $r)>{{ $roleLabels[$r] ?? $r }}</option>
                            @endforeach
                        </select>
                        @error('role')
                            @if (old('form_context') === 'user_create')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@endif
                        @enderror
                    </div>
                    <div class="flex items-center gap-2 rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-2.5">
                        <input id="uc-ev" name="email_verified" value="1" type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" @checked(old('form_context') === 'user_create' && old('email_verified')))>
                        <label for="uc-ev" class="text-sm text-slate-700">Tandai email sudah terverifikasi</label>
                    </div>
                    <div>
                        <label for="uc-pw" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kata sandi</label>
                        <input id="uc-pw" name="password" type="password" autocomplete="new-password" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        @error('password')
                            @if (old('form_context') === 'user_create')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@endif
                        @enderror
                    </div>
                    <div>
                        <label for="uc-pwc" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Konfirmasi kata sandi</label>
                        <input id="uc-pwc" name="password_confirmation" type="password" autocomplete="new-password" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                    </div>
                    <div class="flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-5">
                        <button type="button" @click="createOpen = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: edit --}}
        <div
            x-show="editOpen"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]"
            role="dialog"
            aria-modal="true"
            aria-labelledby="user-edit-title"
        >
            <div
                @click.outside="editOpen = false"
                @keydown.escape.window="editOpen = false"
                class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-xl ring-1 ring-slate-900/5"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 id="user-edit-title" class="text-lg font-bold tracking-tight text-slate-900">Edit pengguna</h3>
                        <p class="mt-1 text-sm text-slate-500">Perbarui data login; kosongkan kata sandi jika tidak diubah.</p>
                    </div>
                    <button type="button" @click="editOpen = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" :action="`{{ url('/pengguna') }}/${edit.id}`" class="mt-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_context" value="user_edit">
                    <input type="hidden" name="edit_user_id" x-model="edit.id">
                    <div>
                        <label for="ue-name" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nama</label>
                        <input id="ue-name" name="name" x-model="edit.name" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        @error('name')
                            @if (old('form_context') === 'user_edit')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@endif
                        @enderror
                    </div>
                    <div>
                        <label for="ue-email" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</label>
                        <input id="ue-email" name="email" type="email" x-model="edit.email" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        @error('email')
                            @if (old('form_context') === 'user_edit')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@endif
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Peran</label>
                        <template x-if="edit.id && edit.id === {{ auth()->id() }}">
                            <div class="mt-1.5 space-y-1.5">
                                <input type="hidden" name="role" :value="edit.role">
                                <p class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700" x-text="roleLabels[edit.role] || edit.role"></p>
                                <p class="text-xs text-slate-500">Peran akun Anda tidak dapat diubah dari halaman ini.</p>
                            </div>
                        </template>
                        <template x-if="!edit.id || edit.id !== {{ auth()->id() }}">
                            <select id="ue-role" name="role" x-model="edit.role" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                @foreach ($roleOptions as $r)
                                    <option value="{{ $r }}">{{ $roleLabels[$r] ?? $r }}</option>
                                @endforeach
                            </select>
                        </template>
                        @error('role')
                            @if (old('form_context') === 'user_edit')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@endif
                        @enderror
                    </div>
                    <div class="flex items-center gap-2 rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-2.5">
                        <input id="ue-ev" name="email_verified" value="1" type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" :checked="edit.emailVerified" @change="edit.emailVerified = $event.target.checked">
                        <label for="ue-ev" class="text-sm text-slate-700">Email terverifikasi</label>
                    </div>
                    <div>
                        <label for="ue-pw" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kata sandi baru (opsional)</label>
                        <input id="ue-pw" name="password" type="password" autocomplete="new-password" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        @error('password')
                            @if (old('form_context') === 'user_edit')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@endif
                        @enderror
                    </div>
                    <div>
                        <label for="ue-pwc" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Konfirmasi kata sandi</label>
                        <input id="ue-pwc" name="password_confirmation" type="password" autocomplete="new-password" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                    </div>
                    <div class="flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-5">
                        <button type="button" @click="editOpen = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: delete --}}
        <div
            x-show="deleteOpen"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]"
            role="dialog"
            aria-modal="true"
        >
            <div
                @click.outside="deleteOpen = false"
                @keydown.escape.window="deleteOpen = false"
                class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl ring-1 ring-slate-900/5"
            >
                <h3 class="text-lg font-bold text-slate-900">Hapus pengguna</h3>
                <p class="mt-2 text-sm text-slate-600">Yakin ingin menghapus <span class="font-semibold text-slate-900" x-text="removeName"></span>? Tindakan ini tidak dapat dibatalkan.</p>
                <form method="POST" :action="`{{ url('/pengguna') }}/${removeId}`" class="mt-6 flex flex-wrap justify-end gap-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="deleteOpen = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-rose-700">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.dashboard-shell>
