@php
    $isSiswa = auth()->user()->hasRole('siswa');
    $isAdmin = auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']);
    $canSnap = $isSiswa && filled(config('midtrans.client_key')) && filled(config('midtrans.server_key'));
@endphp
<x-layouts.dashboard-shell title="Pembayaran ? eBimbel">
    <div
        x-data="{
            massOpen: false,
            pendingOpen: false,
            payLoading: null,
            async pay(id) {
                if (! window.snap || ! window.payWithMidtrans) return;
                this.payLoading = id;
                try {
                    await window.payWithMidtrans(id);
                } finally {
                    this.payLoading = null;
                }
            }
        }"
        class="space-y-6"
    >
        <x-module-page-header
            title="Pembayaran & tagihan"
            :description="$isSiswa
                ? 'Tagihan biaya (pendaftaran, SPP, dll.) ? bayar aman lewat Midtrans Snap.'
                : ($isAdmin
                    ? (auth()->user()->hasRole('admin_cabang')
                        ? 'Kelola tagihan siswa cabang Anda. Kirim pengingat jatuh tempo dan tandai lunas bila perlu.'
                        : 'Kelola tagihan semua cabang, Midtrans otomatis saat siswa membayar, monitoring status di sini.')
                    : '')"
        >
            <x-slot name="actions">
                @if ($isAdmin)
                    <form method="POST" action="{{ route('pembayaran.notify-due-bulk') }}" class="inline" onsubmit="return confirm('Kirim notifikasi in-app ke semua siswa dengan tagihan jatuh tempo hari ini atau sebelumnya?');">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-900 shadow-sm hover:bg-amber-100 disabled:opacity-50" @disabled(($dueBulkCount ?? 0) === 0)>
                            Pengingat jatuh tempo ({{ $dueBulkCount ?? 0 }})
                        </button>
                    </form>
                    <button @click="massOpen = true" type="button" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm ring-1 ring-blue-600/20 hover:bg-blue-700">
                        Buat tagihan massal
                    </button>
                @endif
                @if ($isSiswa && ($summary['belum_count'] ?? 0) > 0)
                    <button type="button" @click="pendingOpen = true" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 shadow-sm hover:bg-slate-50">
                        Menunggu bayar ({{ $summary['belum_count'] }})
                    </button>
                @endif
            </x-slot>
        </x-module-page-header>

        @if (session('status'))
            <p class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</p>
        @endif

        @if ($isSiswa && ! $canSnap)
            <p class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">Pembayaran online belum aktif: pastikan <code class="rounded bg-white px-1">MIDTRANS_CLIENT_KEY</code> dan <code class="rounded bg-white px-1">MIDTRANS_SERVER_KEY</code> di <code class="rounded bg-white px-1">.env</code> sudah benar.</p>
        @endif

        <form method="GET" class="flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm ring-1 ring-slate-900/5">
            @if ($isSiswa)
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Periode</label>
                    <input type="month" name="bulan" value="{{ $filters['bulan'] ?? '' }}" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                </div>
            @endif
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
                <select name="status" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                    <option value="">Semua status</option>
                    <option value="lunas" @selected(($filters['status'] ?? '') === 'lunas')>Lunas</option>
                    <option value="belum" @selected(($filters['status'] ?? '') === 'belum')>Belum lunas</option>
                </select>
            </div>
            @if ($isAdmin)
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Siswa</label>
                    <select name="student_id" class="min-w-[200px] rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                        <option value="">Semua siswa</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" @selected(($filters['student_id'] ?? null) == $student->id)>{{ $student->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bulan transaksi</label>
                    <input type="month" name="bulan" value="{{ $filters['bulan'] ?? '' }}" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                </div>
            @endif
            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">Terapkan</button>
            <a href="{{ route('pembayaran.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
        </form>

        <div class="grid gap-4 sm:grid-cols-3">
            @if ($isSiswa)
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm ring-1 ring-slate-900/5">
                    <p class="text-xs font-medium text-slate-500">Total tagihan (filter)</p>
                    <p class="mt-1 text-xl font-bold text-slate-900">Rp {{ number_format((int) ($summary['total'] ?? 0), 0, ',', '.') }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50/80 p-4 shadow-sm">
                    <p class="text-xs font-medium text-emerald-800">Sudah dibayar</p>
                    <p class="mt-1 text-xl font-bold text-emerald-800">Rp {{ number_format((int) ($summary['paid'] ?? 0), 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-emerald-700">{{ $summary['lunas_count'] ?? 0 }} transaksi lunas</p>
                </div>
                <div class="rounded-2xl border border-amber-100 bg-amber-50/80 p-4 shadow-sm">
                    <p class="text-xs font-medium text-amber-800">Menunggu pembayaran</p>
                    <p class="mt-1 text-xl font-bold text-amber-900">Rp {{ number_format((int) ($summary['outstanding'] ?? 0), 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-amber-800">{{ $summary['belum_count'] ?? 0 }} tagihan belum lunas</p>
                </div>
            @else
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm ring-1 ring-slate-900/5">
                    <p class="text-xs font-medium text-slate-500">Total terbit (filter)</p>
                    <p class="mt-1 text-xl font-bold text-slate-900">Rp {{ number_format((int) ($summary['total'] ?? 0), 0, ',', '.') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm ring-1 ring-slate-900/5">
                    <p class="text-xs font-medium text-slate-500">Sudah masuk</p>
                    <p class="mt-1 text-xl font-bold text-emerald-700">Rp {{ number_format((int) ($summary['paid'] ?? 0), 0, ',', '.') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm ring-1 ring-slate-900/5">
                    <p class="text-xs font-medium text-slate-500">Outstanding</p>
                    <p class="mt-1 text-xl font-bold text-amber-700">Rp {{ number_format((int) ($summary['outstanding'] ?? 0), 0, ',', '.') }}</p>
                </div>
            @endif
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/90">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3.5">Referensi</th>
                            @if (! $isSiswa)
                                <th class="px-4 py-3.5">Siswa</th>
                            @endif
                            <th class="px-4 py-3.5">Item biaya</th>
                            <th class="px-4 py-3.5">Terbit</th>
                            <th class="px-4 py-3.5">Jatuh tempo</th>
                            <th class="px-4 py-3.5">Nominal</th>
                            <th class="px-4 py-3.5">Status</th>
                            @if (! $isSiswa)
                                <th class="px-4 py-3.5">Midtrans</th>
                            @endif
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($payments as $pay)
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-4 py-3.5 font-mono text-xs">
                                    INV-{{ str_pad((string) $pay->id, 5, '0', STR_PAD_LEFT) }}
                                    @if ($pay->invoice_period)
                                        <span class="mt-0.5 block text-[10px] font-normal text-slate-500">{{ $pay->invoice_period }}</span>
                                    @endif
                                </td>
                                @if (! $isSiswa)
                                    <td class="px-4 py-3.5 font-medium">{{ optional($pay->siswa)->nama }}</td>
                                @endif
                                <td class="px-4 py-3.5">{{ optional($pay->fee)->nama_biaya }}</td>
                                <td class="px-4 py-3.5">{{ optional($pay->tanggal_bayar)->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3.5">{{ $pay->due_date ? $pay->due_date->translatedFormat('d M Y') : '?' }}</td>
                                <td class="px-4 py-3.5 font-medium">Rp {{ number_format((int) $pay->nominal, 0, ',', '.') }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $pay->status === 'lunas' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $pay->status === 'lunas' ? 'Lunas' : 'Belum lunas' }}
                                    </span>
                                </td>
                                @if (! $isSiswa)
                                    <td class="px-4 py-3.5 text-xs text-slate-600">{{ $pay->midtrans_transaction_status ?? '?' }}</td>
                                @endif
                                <td class="px-4 py-3.5 text-right">
                                    @if ($isSiswa)
                                        @if ($pay->status === 'lunas')
                                            <span class="text-xs text-slate-500">Selesai{{ $pay->paid_at ? ' ? '.$pay->paid_at->translatedFormat('d M Y H:i') : '' }}</span>
                                        @elseif ($canSnap)
                                            <button type="button" @click="pay({{ $pay->id }})" :disabled="payLoading === {{ $pay->id }}" class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-50">
                                                <span x-show="payLoading !== {{ $pay->id }}">Bayar</span>
                                                <span x-show="payLoading === {{ $pay->id }}" x-cloak>Memuat?</span>
                                            </button>
                                        @else
                                            <span class="text-xs text-slate-400">Bayar (nonaktif)</span>
                                        @endif
                                    @else
                                        @if ($pay->status === 'lunas')
                                            <span class="text-xs text-slate-500">?</span>
                                        @else
                                            <form method="POST" action="{{ route('pembayaran.mark-lunas', $pay) }}" class="inline" onsubmit="return confirm('Tandai tagihan ini lunas? Siswa akan menerima notifikasi.');">
                                                @csrf
                                                <button type="submit" class="text-xs font-semibold text-emerald-700 hover:underline">Tandai lunas</button>
                                            </form>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isSiswa ? 7 : 9 }}" class="px-4 py-10 text-center text-slate-500">Belum ada data pembayaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 bg-slate-50/50 px-4 py-3">{{ $payments->links() }}</div>
        </div>

        @if ($isSiswa && ($summary['belum_count'] ?? 0) > 0)
            <div x-show="pendingOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]">
                <div @click.outside="pendingOpen = false" class="max-h-[min(85vh,520px)] w-full max-w-lg overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-xl ring-1 ring-slate-900/5">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-lg font-bold text-slate-900">Tagihan menunggu pembayaran</h3>
                        <button type="button" @click="pendingOpen = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100" aria-label="Tutup">?</button>
                    </div>
                    <ul class="mt-4 space-y-3">
                        @foreach ($unpaidQuick ?? [] as $p)
                            <li class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-2.5">
                                <div>
                                    <p class="font-medium text-slate-900">{{ optional($p->fee)->nama_biaya }}</p>
                                    <p class="text-xs text-slate-500">Rp {{ number_format((int) $p->nominal, 0, ',', '.') }} @if ($p->due_date) ? jt {{ $p->due_date->translatedFormat('d M Y') }} @endif</p>
                                </div>
                                @if ($canSnap)
                                    <button type="button" @click="pay({{ $p->id }}); pendingOpen = false" class="shrink-0 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">Bayar</button>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @if ($isAdmin)
            <div x-show="massOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]">
                <div @click.outside="massOpen = false" class="max-h-[min(90vh,640px)] w-full max-w-2xl overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-xl ring-1 ring-slate-900/5">
                    <h3 class="text-lg font-bold text-slate-900">Buat tagihan massal</h3>
                    <p class="mt-1 text-sm text-slate-500">Pilih siswa, jenis biaya, nominal, tanggal terbit dan jatuh tempo. Opsional: periode SPP (YYYY-MM) untuk deduplikasi job bulanan.</p>
                    <form method="POST" action="{{ route('pembayaran.mass.store') }}" class="mt-5 space-y-4">
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="text-xs font-semibold uppercase text-slate-500">Jenis biaya</label>
                                <select name="biaya_id" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                                    @foreach ($fees as $fee)
                                        <option value="{{ $fee->id }}">{{ $fee->nama_biaya }} ? Rp {{ number_format((int) $fee->nominal, 0, ',', '.') }} ({{ $fee->tipe }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-semibold uppercase text-slate-500">Nominal</label>
                                <input name="nominal" type="number" min="0" step="0.01" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            </div>
                            <div>
                                <label class="text-xs font-semibold uppercase text-slate-500">Periode invoice (opsional)</label>
                                <input name="invoice_period" type="month" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-500/15" placeholder="YYYY-MM">
                            </div>
                            <div>
                                <label class="text-xs font-semibold uppercase text-slate-500">Tanggal terbit</label>
                                <input name="tanggal_bayar" type="date" value="{{ now()->format('Y-m-d') }}" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            </div>
                            <div>
                                <label class="text-xs font-semibold uppercase text-slate-500">Jatuh tempo</label>
                                <input name="due_date" type="date" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-semibold uppercase text-slate-500">Siswa</label>
                            <div class="mt-2 max-h-52 overflow-y-auto rounded-xl border border-slate-200 p-3">
                                @foreach ($students as $student)
                                    <label class="mb-2 flex items-center gap-2 text-sm last:mb-0">
                                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        {{ $student->nama }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex items-center gap-2 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                            <input type="checkbox" name="skip_notification" value="1" id="skipn" class="rounded border-slate-300 text-slate-600 focus:ring-blue-500">
                            <label for="skipn" class="text-sm text-slate-700">Jangan kirim notifikasi in-app (centang untuk menonaktifkan)</label>
                        </div>
                        <div class="flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-4">
                            <button type="button" @click="massOpen = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                            <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Buat tagihan</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>

    @if ($isSiswa && $canSnap)
        @push('scripts')
            <script src="{{ $midtransSnapJsUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
            <script>
                window.payWithMidtrans = async function (paymentId) {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const res = await fetch('{{ url('/pembayaran') }}/' + paymentId + '/snap-token', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        alert(data.message || 'Gagal memulai pembayaran.');
                        return;
                    }
                    if (typeof snap === 'undefined' || !data.token) {
                        alert('Midtrans Snap belum termuat.');
                        return;
                    }
                    snap.pay(data.token, {
                        onSuccess: function () { window.location.reload(); },
                        onPending: function () { window.location.reload(); },
                        onClose: function () {},
                    });
                };
            </script>
        @endpush
    @endif
</x-layouts.dashboard-shell>
