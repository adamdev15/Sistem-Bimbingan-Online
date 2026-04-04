@php
    $invRef = 'INV-'.str_pad((string) $payment->id, 5, '0', STR_PAD_LEFT);
    $nominalFmt = 'Rp '.number_format((int) round((float) $payment->nominal), 0, ',', '.');
@endphp
<x-layouts.dashboard-shell title="Panduan bayar manual - eBimbel">
    <div
        class="space-y-6"
        x-data="{
            openCat: null,
            openItem: null,
            toggleCat(id) {
                this.openCat = this.openCat === id ? null : id;
                this.openItem = null;
            },
            toggleItem(id) {
                this.openItem = this.openItem === id ? null : id;
            },
        }"
    >
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <a href="{{ route('pembayaran.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-blue-700 hover:text-blue-900">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    Kembali ke pembayaran
                </a>
                <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900">Panduan transfer manual</h1>
                <p class="mt-1 max-w-2xl text-sm text-slate-600">Lakukan pembayaran sesuai nominal di bawah, lalu tunggu verifikasi dari admin. Simpan bukti transfer jika diminta.</p>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm ring-1 ring-slate-900/5 lg:col-span-1">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Referensi</p>
                <p class="mt-1 font-mono text-lg font-bold text-slate-900">{{ $invRef }}</p>
                @if ($payment->invoice_period)
                    <p class="mt-2 text-xs text-slate-500">Periode: <span class="font-medium text-slate-700">{{ $payment->invoice_period }}</span></p>
                @endif
                <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-slate-500">Item</p>
                <p class="mt-1 font-medium text-slate-900">{{ optional($payment->fee)->nama_biaya ?? '—' }}</p>
                <div class="mt-5 border-t border-slate-100 pt-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-800">Total transfer</p>
                    <p class="mt-1 text-2xl font-bold text-amber-950">{{ $nominalFmt }}</p>
                </div>
                @if ($payment->due_date)
                    <p class="mt-3 text-xs text-slate-500">Jatuh tempo: <span class="font-medium text-slate-800">{{ $payment->due_date->translatedFormat('d M Y') }}</span></p>
                @endif
            </div>

            <div class="space-y-4 lg:col-span-2">
                @if (! $hasAnyManualDestination)
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-950">
                        <p class="font-semibold">Metode pembayaran belum dikonfigurasi</p>
                        <p class="mt-1 text-amber-900/90">Hubungi admin cabang atau pusat bimbingan untuk mendapat nomor rekening / e-wallet. Anda tetap dapat mentransfer sesuai nominal di samping.</p>
                    </div>
                @endif

                <div class="rounded-2xl border border-slate-200/80 bg-slate-50/50 p-4 text-sm text-slate-700 ring-1 ring-slate-900/5">
                    <p class="font-semibold text-slate-900">Catatan penting</p>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-slate-600">
                        <li>Pastikan nominal transfer <span class="font-semibold text-slate-800">tepat {{ $nominalFmt }}</span> agar verifikasi cepat.</li>
                        <li>Disarankan mencantumkan keterangan berisi <span class="font-mono text-xs font-semibold text-slate-800">{{ $invRef }}</span> atau nama Anda.</li>
                        <li>Setelah transfer, status lunas diperbarui oleh admin setelah konfirmasi.</li>
                    </ul>
                </div>

                @if (count($manualBanks) > 0 || count($manualEwallets) > 0 || $qrisUrl)
                    <div class="space-y-3">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Pilih metode</h2>

                        @if (count($manualBanks) > 0)
                            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5">
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between gap-3 px-4 py-4 text-left transition hover:bg-slate-50/80"
                                    @click="toggleCat('bank')"
                                    :aria-expanded="openCat === 'bank'"
                                >
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L12 2.25 21.75 18M12 9v9"/></svg>
                                        </span>
                                        <div>
                                            <p class="font-semibold text-slate-900">Transfer bank</p>
                                            <p class="text-xs text-slate-500">BRI, BCA, BNI</p>
                                        </div>
                                    </div>
                                    <svg class="h-5 w-5 shrink-0 text-slate-400 transition" :class="openCat === 'bank' ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                                </button>
                                <div x-show="openCat === 'bank'" x-cloak x-transition class="border-t border-slate-100">
                                    <div class="space-y-2 p-3">
                                        @foreach ($manualBanks as $key => $bank)
                                            @php $itemId = 'bank-'.$key; @endphp
                                            <div class="overflow-hidden rounded-xl border border-slate-100 bg-slate-50/60">
                                                <button
                                                    type="button"
                                                    class="flex w-full items-center justify-between gap-3 px-3 py-3 text-left hover:bg-white/80"
                                                    @click="toggleItem('{{ $itemId }}')"
                                                >
                                                    <div class="flex min-w-0 items-center gap-3">
                                                        <x-pembayaran.payment-brand-icon :code="$key" />
                                                        <span class="truncate font-semibold text-slate-900">{{ $bank['label'] ?? strtoupper($key) }}</span>
                                                    </div>
                                                    <svg class="h-4 w-4 shrink-0 text-slate-400 transition" :class="openItem === '{{ $itemId }}' ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                                                </button>
                                                <div x-show="openItem === '{{ $itemId }}'" x-collapse x-cloak class="border-t border-slate-100 bg-white px-3 py-3">
                                                    <dl class="space-y-3 text-sm">
                                                        <div>
                                                            <dt class="text-xs font-semibold uppercase text-slate-500">Nomor rekening</dt>
                                                            <dd class="mt-1 flex flex-wrap items-center gap-2">
                                                                <span class="font-mono text-base font-semibold text-slate-900" x-ref="bankNum{{ $key }}">{{ $bank['account_number'] }}</span>
                                                                <button
                                                                    type="button"
                                                                    class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                                                                    x-data="{ ok: false }"
                                                                    @click="navigator.clipboard.writeText($refs.bankNum{{ $key }}.textContent.trim()).then(() => { ok = true; setTimeout(() => ok = false, 2000) })"
                                                                >
                                                                    <span x-show="!ok">Salin</span>
                                                                    <span x-show="ok" x-cloak class="text-emerald-700">Disalin</span>
                                                                </button>
                                                            </dd>
                                                        </div>
                                                        @if (filled($bank['account_name'] ?? null))
                                                            <div>
                                                                <dt class="text-xs font-semibold uppercase text-slate-500">Atas nama</dt>
                                                                <dd class="mt-1 flex flex-wrap items-center gap-2">
                                                                    <span class="font-medium text-slate-900" x-ref="bankName{{ $key }}">{{ $bank['account_name'] }}</span>
                                                                    <button
                                                                        type="button"
                                                                        class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                                                                        x-data="{ ok: false }"
                                                                        @click="navigator.clipboard.writeText($refs.bankName{{ $key }}.textContent.trim()).then(() => { ok = true; setTimeout(() => ok = false, 2000) })"
                                                                    >
                                                                        <span x-show="!ok">Salin</span>
                                                                        <span x-show="ok" x-cloak class="text-emerald-700">Disalin</span>
                                                                    </button>
                                                                </dd>
                                                            </div>
                                                        @endif
                                                    </dl>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if (count($manualEwallets) > 0)
                            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5">
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between gap-3 px-4 py-4 text-left transition hover:bg-slate-50/80"
                                    @click="toggleCat('ewallet')"
                                >
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-600 text-white">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 12m18 0v6.75A2.25 2.25 0 0118.75 21H5.25A2.25 2.25 0 013 18.75V12m18 0h-4.5M3 12h4.5"/></svg>
                                        </span>
                                        <div>
                                            <p class="font-semibold text-slate-900">E-wallet</p>
                                            <p class="text-xs text-slate-500">DANA, GoPay, ShopeePay</p>
                                        </div>
                                    </div>
                                    <svg class="h-5 w-5 shrink-0 text-slate-400 transition" :class="openCat === 'ewallet' ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                                </button>
                                <div x-show="openCat === 'ewallet'" x-cloak x-transition class="border-t border-slate-100">
                                    <div class="space-y-2 p-3">
                                        @foreach ($manualEwallets as $key => $ew)
                                            @php $itemId = 'ewallet-'.$key; @endphp
                                            <div class="overflow-hidden rounded-xl border border-slate-100 bg-slate-50/60">
                                                <button
                                                    type="button"
                                                    class="flex w-full items-center justify-between gap-3 px-3 py-3 text-left hover:bg-white/80"
                                                    @click="toggleItem('{{ $itemId }}')"
                                                >
                                                    <div class="flex min-w-0 items-center gap-3">
                                                        <x-pembayaran.payment-brand-icon :code="$key" />
                                                        <span class="truncate font-semibold text-slate-900">{{ $ew['label'] ?? strtoupper($key) }}</span>
                                                    </div>
                                                    <svg class="h-4 w-4 shrink-0 text-slate-400 transition" :class="openItem === '{{ $itemId }}' ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                                                </button>
                                                <div x-show="openItem === '{{ $itemId }}'" x-collapse x-cloak class="border-t border-slate-100 bg-white px-3 py-3">
                                                    <dl class="space-y-3 text-sm">
                                                        <div>
                                                            <dt class="text-xs font-semibold uppercase text-slate-500">ID / nomor</dt>
                                                            <dd class="mt-1 flex flex-wrap items-center gap-2">
                                                                <span class="font-mono text-base font-semibold text-slate-900" x-ref="ewId{{ $key }}">{{ $ew['account_id'] }}</span>
                                                                <button
                                                                    type="button"
                                                                    class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                                                                    x-data="{ ok: false }"
                                                                    @click="navigator.clipboard.writeText($refs.ewId{{ $key }}.textContent.trim()).then(() => { ok = true; setTimeout(() => ok = false, 2000) })"
                                                                >
                                                                    <span x-show="!ok">Salin</span>
                                                                    <span x-show="ok" x-cloak class="text-emerald-700">Disalin</span>
                                                                </button>
                                                            </dd>
                                                        </div>
                                                        @if (filled($ew['account_name'] ?? null))
                                                            <div>
                                                                <dt class="text-xs font-semibold uppercase text-slate-500">Nama</dt>
                                                                <dd class="mt-1 flex flex-wrap items-center gap-2">
                                                                    <span class="font-medium text-slate-900" x-ref="ewName{{ $key }}">{{ $ew['account_name'] }}</span>
                                                                    <button
                                                                        type="button"
                                                                        class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                                                                        x-data="{ ok: false }"
                                                                        @click="navigator.clipboard.writeText($refs.ewName{{ $key }}.textContent.trim()).then(() => { ok = true; setTimeout(() => ok = false, 2000) })"
                                                                    >
                                                                        <span x-show="!ok">Salin</span>
                                                                        <span x-show="ok" x-cloak class="text-emerald-700">Disalin</span>
                                                                    </button>
                                                                </dd>
                                                            </div>
                                                        @endif
                                                    </dl>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($qrisUrl)
                            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5">
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between gap-3 px-4 py-4 text-left transition hover:bg-slate-50/80"
                                    @click="toggleCat('qris')"
                                >
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h4v4H4V4zm6 0h4v4h-4V4zm6 0h4v4h-4V4zM4 10h4v4H4v-4zm12 0h4v4h-4v-4zM4 16h4v4H4v-4zm6 0h4v4h-4v-4zm6 0h4v4h-4v-4z"/></svg>
                                        </span>
                                        <div>
                                            <p class="font-semibold text-slate-900">QRIS</p>
                                            <p class="text-xs text-slate-500">Scan untuk bayar</p>
                                        </div>
                                    </div>
                                    <svg class="h-5 w-5 shrink-0 text-slate-400 transition" :class="openCat === 'qris' ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                                </button>
                                <div x-show="openCat === 'qris'" x-cloak x-transition class="border-t border-slate-100 px-4 py-5">
                                    <div class="mx-auto max-w-[220px] rounded-2xl border border-slate-200 bg-white p-4 shadow-inner">
                                        <img src="{{ $qrisUrl }}" alt="QRIS pembayaran" class="h-auto w-full object-contain" loading="lazy" width="220" height="220">
                                    </div>
                                    <p class="mt-4 text-center text-xs text-slate-500">Pastikan nominal di aplikasi e-banking sesuai <span class="font-semibold text-slate-800">{{ $nominalFmt }}</span>.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.dashboard-shell>
