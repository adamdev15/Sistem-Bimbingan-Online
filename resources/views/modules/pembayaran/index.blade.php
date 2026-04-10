@php
    $isSiswa = auth()->user()->hasRole('siswa');
    $isAdmin = auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']);
    $canSnap = $isSiswa && filled(config('midtrans.client_key')) && filled(config('midtrans.server_key'));
@endphp

<x-layouts.dashboard-shell title="Pembayaran - eBimbel">
    <div
        x-data="{
            massOpen: false,
            pendingOpen: false,
            payLoading: null,
            detailOpen: false,
            detail: null,
            openPaymentDetail(payload) {
                this.detail = payload;
                this.detailOpen = true;
            },
            closePaymentDetail() {
                this.detailOpen = false;
                this.detail = null;
            },
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
            title="Pembayaran & Tagihan"
            :description="$isSiswa
                ? 'Kelola riwayat pembayaran dan lunasi tagihan aktif Anda.'
                : 'Pusat kendali keuangan untuk memantau arus kas dari tagihan siswa.'"
        >
        </x-module-page-header>

        @if (session('status'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-900 shadow-sm animate-in fade-in slide-in-from-top-2">
                <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('status') }}
            </div>
        @endif

        {{-- KPI STATS --}}
        <div class="grid gap-6 sm:grid-cols-3">
            @if ($isSiswa)
                <div class="group relative overflow-hidden rounded-3xl border border-blue-100 bg-white p-6 shadow-sm ring-1 ring-slate-900/5 transition hover:shadow-xl">
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-blue-50 transition group-hover:scale-110"></div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Total Tagihan</p>
                    <p class="mt-2 text-3xl font-black text-slate-900">Rp {{ number_format((int) ($summary['total'] ?? 0), 0, ',', '.') }}</p>
                </div>
                <div class="group relative overflow-hidden rounded-3xl border border-emerald-100 bg-white p-6 shadow-sm ring-1 ring-emerald-900/5 transition hover:shadow-xl">
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-emerald-50 transition group-hover:scale-110"></div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-500">Sudah Dibayar</p>
                    <p class="mt-2 text-3xl font-black text-emerald-600">Rp {{ number_format((int) ($summary['paid'] ?? 0), 0, ',', '.') }}</p>
                </div>
                <div class="group relative overflow-hidden rounded-3xl border border-rose-100 bg-white p-6 shadow-sm ring-1 ring-rose-900/5 transition hover:shadow-xl">
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-rose-50 transition group-hover:scale-110"></div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-rose-500">Sisa Tagihan</p>
                    <p class="mt-2 text-3xl font-black text-rose-600">Rp {{ number_format((int) ($summary['outstanding'] ?? 0), 0, ',', '.') }}</p>
                </div>
            @else
                <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-700 to-blue-900 p-6 text-white shadow-lg shadow-blue-200 transition hover:shadow-blue-300/50">
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-200">Total Terbit (Pemasukan)</p>
                    <p class="mt-2 text-3xl font-black">Rp {{ number_format((int) ($summary['total'] ?? 0), 0, ',', '.') }}</p>
                </div>
                <div class="group relative overflow-hidden rounded-3xl bg-white border border-slate-200 p-6 shadow-sm ring-1 ring-slate-900/5 transition hover:shadow-xl">
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-blue-50 transition group-hover:scale-110"></div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-500">Pembayaran Masuk</p>
                    <p class="mt-2 text-3xl font-black text-slate-900">Rp {{ number_format((int) ($summary['paid'] ?? 0), 0, ',', '.') }}</p>
                </div>
                <div class="group relative overflow-hidden rounded-3xl bg-white border border-slate-200 p-6 shadow-sm ring-1 ring-slate-900/5 transition hover:shadow-xl">
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-amber-50 transition group-hover:scale-110"></div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-500">Pembayaran Pending</p>
                    <p class="mt-2 text-3xl font-black text-slate-900">Rp {{ number_format((int) ($summary['outstanding'] ?? 0), 0, ',', '.') }}</p>
                </div>
            @endif
        </div>

        {{-- MAIN CONTENT CARD --}}
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm ring-1 ring-slate-900/5">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 p-6">
                <form method="GET" class="flex flex-wrap items-center gap-3">
                    @if ($isSiswa || $isAdmin)
                        <div class="min-w-[140px]">
                            <label class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-400">Periode</label>
                            <input type="month" name="bulan" value="{{ $filters['bulan'] ?? '' }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                        </div>
                    @endif
                    <div class="min-w-[140px]">
                        <label class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-400">Status</label>
                        <select name="status" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                            <option value="">Semua Status</option>
                            <option value="lunas" @selected(($filters['status'] ?? '') === 'lunas')>Lunas</option>
                            <option value="belum" @selected(($filters['status'] ?? '') === 'belum')>Belum Lunas</option>
                        </select>
                    </div>
                    <div class="flex gap-2 pt-5">
                        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white transition hover:bg-slate-800">Filter</button>
                        <a href="{{ route('pembayaran.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Reset</a>
                    </div>
                </form>

                <div class="flex flex-wrap items-center gap-2">
                    @if ($isAdmin)
                        <form method="POST" action="{{ route('pembayaran.notify-due-bulk') }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs font-bold text-amber-900 hover:bg-amber-100 disabled:opacity-50" @disabled(($dueBulkCount ?? 0) === 0)>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                Reminder ({{ $dueBulkCount ?? 0 }})
                            </button>
                        </form>
                        <button @click="massOpen = true" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-black tracking-widest text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Buat Tagihan
                        </button>
                    @endif
                    @if ($isSiswa && ($summary['belum_count'] ?? 0) > 0)
                        <button @click="pendingOpen = true" class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-xs font-bold text-blue-700 hover:bg-blue-100">
                            Bayar Sekarang ({{ $summary['belum_count'] }})
                        </button>
                    @endif
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50/50">
                        <tr class="text-left text-[10px] font-black uppercase tracking-widest text-slate-400">
                            <th class="px-6 py-4">Invoice</th>
                            @if (! $isSiswa) <th class="px-6 py-4">Siswa</th> @endif
                            <th class="px-6 py-4">Biaya</th>
                            <th class="px-6 py-4">Jatuh Tempo</th>
                            <th class="px-6 py-4 text-right">Nominal</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($payments as $pay)
                            <tr class="group transition hover:bg-slate-50/60">
                                <td class="px-6 py-4">
                                    <span class="font-mono text-xs font-bold text-slate-500">#{{ str_pad((string) $pay->id, 5, "0", STR_PAD_LEFT) }}</span>
                                    @if ($pay->invoice_period)
                                        <span class="block text-[10px] text-blue-500 font-bold uppercase">{{ $pay->invoice_period }}</span>
                                    @endif
                                </td>
                                @if (! $isSiswa)
                                    <td class="px-6 py-4 font-bold text-slate-900">{{ optional($pay->siswa)->nama }}</td>
                                @endif
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-800">{{ optional($pay->fee)->nama_biaya }}</div>
                                    <div class="text-[10px] text-slate-400 italic">Terbit: {{ optional($pay->tanggal_bayar)->translatedFormat("d M Y") }}</div>
                                </td>
                                <td class="px-6 py-4 mt-0.5">
                                    <span @class([
                                        "text-xs font-bold",
                                        "text-rose-500" => $pay->status !== "lunas" && $pay->due_date && $pay->due_date->isPast(),
                                        "text-slate-600" => !($pay->status !== "lunas" && $pay->due_date && $pay->due_date->isPast())
                                    ])>
                                        {{ $pay->due_date ? $pay->due_date->translatedFormat("d M Y") : "—" }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-black text-slate-900">
                                    Rp {{ number_format((int) $pay->nominal, 0, ",", ".") }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span @class([
                                        "inline-flex rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest",
                                        "bg-emerald-100 text-emerald-700" => $pay->status === "lunas",
                                        "bg-amber-100 text-amber-700 shadow-sm" => $pay->status !== "lunas",
                                    ])>
                                        {{ $pay->status === "lunas" ? "LUNAS" : "PENDING" }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($isSiswa)
                                            @if ($pay->status === "lunas")
                                                <div class="text-[10px] font-bold text-slate-400 uppercase">Paid {{ $pay->paid_at?->translatedFormat("d/m/y") }}</div>
                                            @else
                                                @if ($canSnap)
                                                    <button @click="pay({{ $pay->id }})" :disabled="payLoading === {{ $pay->id }}" class="rounded-xl bg-blue-600 px-4 py-1.5 text-xs font-bold text-white hover:bg-blue-700 shadow-md shadow-blue-200">
                                                        <span x-show="payLoading !== {{ $pay->id }}">Bayar Online</span>
                                                        <span x-show="payLoading === {{ $pay->id }}" x-cloak>Wait...</span>
                                                    </button>
                                                @endif
                                                <a href="{{ route("pembayaran.manual", $pay) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50">Manual</a>
                                            @endif
                                        @else
                                            @if ($pay->status === "lunas")
                                                @php
                                                    $s = $pay->siswa;
                                                    $detailPayload = [
                                                        'referensi' => 'INV-'.str_pad((string) $pay->id, 5, '0', STR_PAD_LEFT),
                                                        'invoice_period' => $pay->invoice_period,
                                                        'item_biaya' => $pay->fee?->nama_biaya,
                                                        'fee_tipe' => $pay->fee?->tipe,
                                                        'fee_master_nominal' => $pay->fee ? (int) $pay->fee->nominal : null,
                                                        'nominal_dibayar' => (int) round((float) $pay->nominal),
                                                        'tanggal_terbit_fmt' => optional($pay->tanggal_bayar)?->translatedFormat('l, d M Y'),
                                                        'due_date_fmt' => $pay->due_date?->translatedFormat('l, d M Y'),
                                                        'paid_at_fmt' => $pay->paid_at
                                                            ? $pay->paid_at->timezone(config('app.timezone'))->translatedFormat('l, d M Y — H:i:s').' ('.config('app.timezone').')'
                                                            : null,
                                                        'order_id' => $pay->order_id,
                                                        'midtrans_txn_id' => $pay->midtrans_transaction_id,
                                                        'midtrans_status' => $pay->midtrans_transaction_status,
                                                        'midtrans_payment_type' => $pay->midtrans_payment_type,
                                                        'siswa' => [
                                                            'nama' => $s?->nama,
                                                            'email' => $s?->email,
                                                            'no_hp' => $s?->no_hp,
                                                            'nik' => $s?->nik,
                                                            'jenis_kelamin' => $s?->jenis_kelamin,
                                                            'alamat' => $s?->alamat,
                                                            'cabang' => $s?->cabang?->nama_cabang,
                                                            'akun_nama' => $s?->user?->name,
                                                            'akun_email' => $s?->user?->email,
                                                        ],
                                                        'dicatat_oleh' => $pay->creator
                                                            ? ['nama' => $pay->creator->name, 'email' => $pay->creator->email]
                                                            : null,
                                                    ];
                                                @endphp
                                                <button @click="openPaymentDetail({{ \Illuminate\Support\Js::from($detailPayload) }})" class="text-xs font-bold text-blue-600 hover:underline">Detail</button>
                                            @else
                                                <form method="POST" action="{{ route("pembayaran.mark-lunas", $pay) }}" onsubmit="return confirm(\'Tandai lunas secara manual?\');">
                                                    @csrf
                                                    <button type="submit" class="text-xs font-bold text-emerald-600 hover:underline">Tandai Lunas</button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-6 py-20 text-center text-slate-400 italic">Belum ada data pembayaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-50 bg-slate-50/30 px-6 py-4">{{ $payments->links() }}</div>
        </div>

        {{-- LATEST REPORTS (ADMIN) --}}
        @if ($isAdmin)
            <div class="rounded-3xl bg-slate-900 p-8 text-white">
                <div class="flex flex-wrap items-center justify-between gap-6">
                    <div>
                        <h3 class="text-xl font-black tracking-tight">Export Laporan Keuangan</h3>
                        <p class="mt-1 text-sm text-slate-400">Unduh data transaksi pembayaran dalam format dokumen pdf dan excel.</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route("pembayaran.export.ringkasan.pdf") }}" class="rounded-2xl bg-rose-500 px-6 py-3 text-sm font-black  tracking-widest hover:bg-rose-600 transition shadow-lg shadow-rose-900/40">PDF Report</a>
                        <a href="{{ route("pembayaran.export.ringkasan.excel") }}" class="rounded-2xl bg-emerald-500 px-6 py-3 text-sm font-black  tracking-widest hover:bg-emerald-600 transition shadow-lg shadow-emerald-900/40">Excel Report</a>
                    </div>
                </div>
            </div>
        @endif
    
    {{-- MODALS --}}
    @if ($isAdmin)
        {{-- MODAL: MASS TAGIHAN --}}
        <div x-show="massOpen" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]">
            <div @click.outside="massOpen = false" class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl bg-white shadow-2xl animate-in zoom-in-95 duration-200">
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-100 bg-white/80 p-6 backdrop-blur-md">
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Buat Tagihan</h3>
                    <button @click="massOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>
                <form method="POST" action="{{ route("pembayaran.mass.store") }}" class="p-5 space-y-2">
                    @csrf
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-400">Jenis Biaya</label>
                            <select name="biaya_id" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                                @foreach ($fees as $fee)
                                    <option value="{{ $fee->id }}">{{ $fee->nama_biaya }} (Rp {{ number_format((int)$fee->nominal,0,",",".") }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-400">Jumlah Nominal</label>
                            <input name="nominal" type="number" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-400">Pilih Periode</label>
                            <input name="invoice_period" type="month" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                        </div>
                    </div>
                    <div x-data="{ studentSearch: '' }" class="space-y-3">
                         <div class="flex items-center justify-between">
                             <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400">Pilih Siswa</label>
                             <span class="text-[10px] font-bold text-blue-500 uppercase">Multi-select Aktif</span>
                         </div>
                         
                         {{-- Search Input --}}
                         <div class="relative">
                             <input 
                                x-model="studentSearch"
                                type="text" 
                                placeholder="Cari nama siswa..." 
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-10 py-2.5 text-sm font-semibold outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition"
                             >
                             <svg class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                             <button x-show="studentSearch" @click="studentSearch = ''" type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                 <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                             </button>
                         </div>

                         <div class="max-h-60 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 divide-y divide-slate-50">
                            @foreach ($students as $student)
                                <label 
                                    x-show="!studentSearch || '{{ strtolower($student->nama) }}'.includes(studentSearch.toLowerCase())"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-blue-50 cursor-pointer group"
                                >
                                    <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500/20">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-slate-700 group-hover:text-blue-700 transition">{{ $student->nama }}</p>
                                        <p class="text-[10px] font-medium text-slate-400 uppercase tracking-tight">{{ optional($student->cabang)->nama_cabang }}</p>
                                    </div>
                                </label>
                            @endforeach
                            
                            {{-- No Results --}}
                            <div x-show="studentSearch && !([...$el.parentElement.children].some(child => child.style.display !== 'none'))" x-cloak class="py-10 text-center">
                                <svg class="mx-auto h-8 w-8 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Siswa tidak ditemukan</p>
                            </div>
                         </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="massOpen = false" class="rounded-xl border border-slate-200 px-6 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Batal</button>
                        <button type="submit" class="rounded-xl bg-blue-600 px-8 py-3 text-sm font-black tracking-[0.1em] text-white shadow-lg shadow-blue-200 hover:bg-blue-700 transition active:scale-95">Buat Tagihan</button>
                    </div>
                </form>
            </div>
        </div>
        {{-- Modal: detail transaksi lunas (admin / cabang) --}}
        <div
            x-show="detailOpen"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]"
            role="dialog"
            aria-modal="true"
            aria-labelledby="pay-detail-title"
            @keydown.escape.window="closePaymentDetail()"
        >
            <div
                @click.outside="closePaymentDetail()"
                class="max-h-[min(92vh,720px)] w-full max-w-3xl overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-xl ring-1 ring-slate-900/5"
            >
                <div class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-slate-100 bg-white px-6 py-4">
                    <div>
                        <h2 id="pay-detail-title" class="text-lg font-bold tracking-tight text-slate-900">Detail pembayaran lunas</h2>
                        <p class="mt-0.5 text-sm text-slate-500">Ringkasan transaksi Midtrans &amp; data siswa.</p>
                    </div>
                    <button type="button" @click="closePaymentDetail()" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-6 p-6" x-show="detail">
                    <section class="rounded-xl border border-blue-100 bg-blue-50/50 p-4">
                        <h4 class="text-xs font-bold uppercase tracking-wide text-blue-900">Transaksi</h4>
                        <dl class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div>
                                <dt class="text-[11px] font-semibold uppercase text-slate-500">No. referensi</dt>
                                <dd class="mt-0.5 font-mono text-sm font-semibold text-slate-900" x-text="detail?.referensi || '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase text-slate-500">Order ID Midtrans</dt>
                                <dd class="mt-0.5 break-all font-mono text-xs text-slate-800" x-text="detail?.order_id || '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase text-slate-500">Waktu lunas</dt>
                                <dd class="mt-0.5 text-sm font-semibold text-emerald-800" x-text="detail?.paid_at_fmt || '— (belum tercatat / manual)'"></dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase text-slate-500">Nominal dibayar</dt>
                                <dd class="mt-0.5 text-sm font-bold text-slate-900" x-text="detail?.nominal_dibayar != null ? 'Rp ' + Number(detail.nominal_dibayar).toLocaleString('id-ID') : '—'"></dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-[11px] font-semibold uppercase text-slate-500">Item biaya</dt>
                                <dd class="mt-0.5 text-sm text-slate-900">
                                    <span x-text="detail?.item_biaya || '—'"></span>
                                    <span class="text-slate-500" x-show="detail?.fee_tipe"> · <span x-text="detail?.fee_tipe"></span></span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase text-slate-500">Periode invoice</dt>
                                <dd class="mt-0.5 text-sm text-slate-800" x-text="detail?.invoice_period || '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase text-slate-500">Master fee (referensi)</dt>
                                <dd class="mt-0.5 text-sm text-slate-800" x-text="detail?.fee_master_nominal != null ? 'Rp ' + Number(detail.fee_master_nominal).toLocaleString('id-ID') : '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase text-slate-500">Tanggal terbit tagihan</dt>
                                <dd class="mt-0.5 text-sm text-slate-800" x-text="detail?.tanggal_terbit_fmt || '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase text-slate-500">Jatuh tempo</dt>
                                <dd class="mt-0.5 text-sm text-slate-800" x-text="detail?.due_date_fmt || '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase text-slate-500">Status Midtrans</dt>
                                <dd class="mt-0.5 text-sm font-medium text-slate-800" x-text="detail?.midtrans_status || '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase text-slate-500">Metode / tipe</dt>
                                <dd class="mt-0.5 text-sm text-slate-800" x-text="detail?.midtrans_payment_type || '—'"></dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-[11px] font-semibold uppercase text-slate-500">Transaction ID Midtrans</dt>
                                <dd class="mt-0.5 break-all font-mono text-xs text-slate-700" x-text="detail?.midtrans_txn_id || '—'"></dd>
                            </div>
                        </dl>
                    </section>

                    <section class="rounded-xl border border-slate-200 bg-slate-50/40 p-4">
                        <h4 class="text-xs font-bold uppercase tracking-wide text-slate-800">Data siswa</h4>
                        <dl class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <dt class="text-[11px] font-semibold uppercase text-slate-500">Nama</dt>
                                <dd class="mt-0.5 text-sm font-semibold text-slate-900" x-text="detail?.siswa?.nama || '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase text-slate-500">Email (data siswa)</dt>
                                <dd class="mt-0.5 break-all text-sm text-slate-800" x-text="detail?.siswa?.email || '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase text-slate-500">No. HP</dt>
                                <dd class="mt-0.5 text-sm text-slate-800" x-text="detail?.siswa?.no_hp || '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase text-slate-500">NIK</dt>
                                <dd class="mt-0.5 font-mono text-sm text-slate-800" x-text="detail?.siswa?.nik || '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase text-slate-500">Jenis kelamin</dt>
                                <dd class="mt-0.5 text-sm text-slate-800" x-text="detail?.siswa?.jenis_kelamin || '—'"></dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-[11px] font-semibold uppercase text-slate-500">Alamat</dt>
                                <dd class="mt-0.5 text-sm leading-relaxed text-slate-800" x-text="detail?.siswa?.alamat || '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase text-slate-500">Cabang</dt>
                                <dd class="mt-0.5 text-sm font-medium text-slate-800" x-text="detail?.siswa?.cabang || '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase text-slate-500">Akun login</dt>
                                <dd class="mt-0.5 text-sm text-slate-800">
                                    <span x-text="detail?.siswa?.akun_nama || '—'"></span>
                                    <span class="block break-all text-xs text-slate-500" x-text="detail?.siswa?.akun_email ? '(' + detail.siswa.akun_email + ')' : ''"></span>
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section x-show="detail?.dicatat_oleh" class="rounded-xl border border-amber-100 bg-amber-50/40 px-4 py-3">
                        <h4 class="text-xs font-bold uppercase tracking-wide text-amber-900">Tagihan dibuat oleh</h4>
                        <p class="mt-1 text-sm text-amber-950">
                            <span x-text="detail?.dicatat_oleh?.nama"></span>
                            <span class="block break-all text-xs text-amber-800/90" x-text="detail?.dicatat_oleh?.email"></span>
                        </p>
                    </section>

                    <div class="flex justify-end border-t border-slate-100 pt-4">
                        <button type="button" @click="closePaymentDetail()" class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    </div>
    
    @if ($isSiswa && $canSnap)
        @push("scripts")
            <script src="{{ $midtransSnapJsUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
            <script>
                (function () {
                    var payBase = @json(rtrim(url("/pembayaran"), "/"));
                    window.payWithMidtrans = async function (paymentId) {
                        var res = await fetch(payBase + "/" + paymentId + "/snap-token", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector(\'meta[name="csrf-token"]\')?.getAttribute("content")
                            }
                        });
                        var data = await res.json();
                        if (data.token) {
                            snap.pay(data.token, {
                                onSuccess: () => window.location.reload(),
                                onPending: () => window.location.reload(),
                                onError: () => alert("Pembayaran gagal.")
                            });
                        }
                    };
                })();
            </script>
        @endpush
    @endif
</x-layouts.dashboard-shell>
