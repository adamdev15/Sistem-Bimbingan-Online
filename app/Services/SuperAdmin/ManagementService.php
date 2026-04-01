<?php

namespace App\Services\SuperAdmin;

use App\Models\Cabang;
use App\Models\Fee;
use App\Models\Jadwal;
use App\Models\Kehadiran;
use App\Models\MataPelajaran;
use App\Models\Payment;
use App\Models\Salary;
use App\Models\Siswa;
use App\Models\Tutor;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManagementService
{
    private const ID_MONTH_SHORT = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    private const ID_WEEKDAY = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    private function actorCabangId(): ?int
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        if ($user->hasRole('admin_cabang')) {
            return Cabang::query()->where('user_id', $user->id)->value('id') ?: 0;
        }

        if ($user->hasRole('tutor')) {
            return Tutor::query()->where('user_id', $user->id)->value('cabang_id')
                ?: Tutor::query()->where('email', $user->email)->value('cabang_id');
        }

        if ($user->hasRole('siswa')) {
            return Siswa::query()->where('user_id', $user->id)->value('cabang_id')
                ?: Siswa::query()->where('email', $user->email)->value('cabang_id');
        }

        return null;
    }

    private function actorTutorId(): ?int
    {
        $user = Auth::user();
        if (! $user || ! $user->hasRole('tutor')) {
            return null;
        }

        return Tutor::query()->where('user_id', $user->id)->value('id')
            ?: Tutor::query()->where('email', $user->email)->value('id');
    }

    private function actorSiswaId(): ?int
    {
        $user = Auth::user();
        if (! $user || ! $user->hasRole('siswa')) {
            return null;
        }

        return Siswa::query()->where('user_id', $user->id)->value('id')
            ?: Siswa::query()->where('email', $user->email)->value('id');
    }

    public function dashboardStats(): array
    {
        $cabangId = $this->actorCabangId();

        $paymentMonth = Payment::query()
            ->when($cabangId, fn ($q) => $q->whereHas('siswa', fn ($s) => $s->where('cabang_id', $cabangId)))
            ->whereMonth('tanggal_bayar', now()->month)
            ->whereYear('tanggal_bayar', now()->year)
            ->sum('nominal');

        $moduleSiswa = Siswa::query()->where('status', 'aktif')->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))->count();
        $moduleJadwal = Jadwal::query()->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))->count();
        $moduleTagihan = Payment::query()->where('status', 'belum')->when($cabangId, fn ($q) => $q->whereHas('siswa', fn ($s) => $s->where('cabang_id', $cabangId)))->count();

        $monthlyRevenue = collect(range(7, 0))->map(function ($offset) use ($cabangId) {
            $date = now()->subMonths($offset);
            $value = Payment::query()
                ->when($cabangId, fn ($q) => $q->whereHas('siswa', fn ($s) => $s->where('cabang_id', $cabangId)))
                ->whereYear('tanggal_bayar', $date->year)
                ->whereMonth('tanggal_bayar', $date->month)
                ->sum('nominal');

            return [
                'label' => $date->isoFormat('MMM'),
                'value' => (int) $value,
            ];
        })->values();

        return [
            'total_siswa' => Siswa::query()->where('status', 'aktif')->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))->count(),
            'total_tutor' => Tutor::query()->where('status', 'aktif')->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))->count(),
            'pembayaran_bulan' => $paymentMonth,
            'sesi_hari_ini' => Jadwal::query()->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))->count(),
            'pembayaran_terbaru' => Payment::query()
                ->with(['siswa:id,nama', 'fee:id,nama_biaya'])
                ->when($cabangId, fn ($q) => $q->whereHas('siswa', fn ($s) => $s->where('cabang_id', $cabangId)))
                ->latest('tanggal_bayar')
                ->limit(5)
                ->get(),
            'module_cards' => [
                'siswa' => $moduleSiswa,
                'jadwal' => $moduleJadwal,
                'tagihan' => $moduleTagihan,
            ],
            'monthly_revenue' => $monthlyRevenue,
        ];
    }

    /**
     * Data grafik dashboard admin cabang: jenis kelamin, presensi multi-periode, laporan pendapatan tahunan.
     */
    public function adminCabangChartData(Request $request): array
    {
        $cabangId = $this->actorCabangId();
        $emptyPresensi = ['7d' => [], '1m' => [], '1y' => []];

        if (! $cabangId) {
            return [
                'siswa_jenis_kelamin' => ['laki_laki' => 0, 'perempuan' => 0],
                'presensi_series' => $emptyPresensi,
                'laporan_bulanan_tahun' => [],
                'presensi_range' => '7d',
            ];
        }

        $jkGroups = Siswa::query()
            ->where('cabang_id', $cabangId)
            ->where('status', 'aktif')
            ->selectRaw('jenis_kelamin, COUNT(*) as c')
            ->groupBy('jenis_kelamin')
            ->pluck('c', 'jenis_kelamin');

        $siswaJk = [
            'laki_laki' => (int) ($jkGroups['laki_laki'] ?? 0),
            'perempuan' => (int) ($jkGroups['perempuan'] ?? 0),
        ];

        $presensi7 = [];
        $start7 = Carbon::now()->subDays(6)->startOfDay();
        for ($i = 0; $i < 7; $i++) {
            $d = $start7->copy()->addDays($i);
            $presensi7[] = [
                'label' => self::ID_WEEKDAY[$d->dayOfWeek],
                'value' => $this->kehadiranHadirCountForCabangOnDate($cabangId, $d),
            ];
        }

        $presensi1m = [];
        $monthStart = Carbon::now()->startOfMonth();
        $daysInMonth = (int) Carbon::now()->daysInMonth;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $d = $monthStart->copy()->day($day);
            $presensi1m[] = [
                'label' => (string) $day,
                'value' => $this->kehadiranHadirCountForCabangOnDate($cabangId, $d),
            ];
        }

        $year = (int) Carbon::now()->year;
        $presensi1y = [];
        for ($m = 1; $m <= 12; $m++) {
            $presensi1y[] = [
                'label' => self::ID_MONTH_SHORT[$m],
                'value' => $this->kehadiranHadirCountForCabangInMonth($cabangId, $year, $m),
            ];
        }

        $laporanBulanan = [];
        for ($m = 1; $m <= 12; $m++) {
            $nominal = Payment::query()
                ->whereHas('siswa', fn ($s) => $s->where('cabang_id', $cabangId))
                ->whereYear('tanggal_bayar', $year)
                ->whereMonth('tanggal_bayar', $m)
                ->sum('nominal');
            $laporanBulanan[] = [
                'label' => self::ID_MONTH_SHORT[$m],
                'value' => (int) $nominal,
            ];
        }

        $range = $request->query('presensi_range', '7d');
        if (! in_array($range, ['7d', '1m', '1y'], true)) {
            $range = '7d';
        }

        return [
            'siswa_jenis_kelamin' => $siswaJk,
            'presensi_series' => [
                '7d' => $presensi7,
                '1m' => $presensi1m,
                '1y' => $presensi1y,
            ],
            'laporan_bulanan_tahun' => $laporanBulanan,
            'presensi_range' => $range,
            'laporan_tahun_label' => (string) $year,
        ];
    }

    private function kehadiranHadirCountForCabangOnDate(int $cabangId, Carbon $date): int
    {
        return (int) Kehadiran::query()
            ->join('siswas', 'siswas.id', '=', 'kehadirans.student_id')
            ->where('siswas.cabang_id', $cabangId)
            ->whereDate('kehadirans.tanggal', $date->toDateString())
            ->where('kehadirans.status', 'hadir')
            ->count();
    }

    private function kehadiranHadirCountForCabangInMonth(int $cabangId, int $year, int $month): int
    {
        return (int) Kehadiran::query()
            ->join('siswas', 'siswas.id', '=', 'kehadirans.student_id')
            ->where('siswas.cabang_id', $cabangId)
            ->whereYear('kehadirans.tanggal', $year)
            ->whereMonth('kehadirans.tanggal', $month)
            ->where('kehadirans.status', 'hadir')
            ->count();
    }

    private static function jadwalHariFromCarbon(Carbon $d): string
    {
        $keys = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

        return $keys[$d->dayOfWeek];
    }

    private function kehadiranHadirCountForTutorOnDate(int $tutorId, Carbon $date): int
    {
        return (int) Kehadiran::query()
            ->whereHas('jadwal', fn ($j) => $j->where('tutor_id', $tutorId))
            ->whereDate('tanggal', $date->toDateString())
            ->where('status', 'hadir')
            ->count();
    }

    private function kehadiranHadirCountForTutorInMonth(int $tutorId, int $year, int $month): int
    {
        return (int) Kehadiran::query()
            ->whereHas('jadwal', fn ($j) => $j->where('tutor_id', $tutorId))
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->where('status', 'hadir')
            ->count();
    }

    /**
     * Dashboard tutor: kartu ringkas, jadwal, siswa perlu perhatian, grafik JK & presensi.
     */
    public function tutorDashboardData(Request $request): array
    {
        $tutorId = $this->actorTutorId();
        $emptyPresensi = ['7d' => [], '1m' => [], '1y' => []];

        if (! $tutorId) {
            return [
                'tutor_cards' => [
                    'sesi_hari_ini' => 0,
                    'siswa_bimbingan' => 0,
                    'alfa_hari_ini' => 0,
                    'hadir_7_hari' => 0,
                ],
                'jadwal_mengajar' => collect(),
                'siswa_perhatian' => collect(),
                'siswa_jenis_kelamin' => ['laki_laki' => 0, 'perempuan' => 0],
                'presensi_series' => $emptyPresensi,
                'presensi_range' => '7d',
            ];
        }

        $hariIni = self::jadwalHariFromCarbon(Carbon::now());

        $sesiHariIni = (int) Jadwal::query()->where('tutor_id', $tutorId)->where('hari', $hariIni)->count();

        $siswaBimbingan = (int) Siswa::query()
            ->where('status', 'aktif')
            ->whereHas('kehadirans', fn ($k) => $k->whereHas('jadwal', fn ($j) => $j->where('tutor_id', $tutorId)))
            ->count();

        $alfaHariIni = (int) Kehadiran::query()
            ->whereHas('jadwal', fn ($j) => $j->where('tutor_id', $tutorId))
            ->whereDate('tanggal', Carbon::today())
            ->where('status', 'alfa')
            ->count();

        $hadir7Hari = (int) Kehadiran::query()
            ->whereHas('jadwal', fn ($j) => $j->where('tutor_id', $tutorId))
            ->where('status', 'hadir')
            ->whereDate('tanggal', '>=', Carbon::today()->subDays(6))
            ->count();

        $jadwalMengajar = Jadwal::query()
            ->where('tutor_id', $tutorId)
            ->with(['cabang:id,nama_cabang', 'mataPelajaran:id,nama'])
            ->orderByRaw("CASE hari WHEN 'senin' THEN 1 WHEN 'selasa' THEN 2 WHEN 'rabu' THEN 3 WHEN 'kamis' THEN 4 WHEN 'jumat' THEN 5 WHEN 'sabtu' THEN 6 WHEN 'minggu' THEN 7 ELSE 8 END")
            ->orderBy('jam_mulai')
            ->limit(12)
            ->get();

        $siswaPerhatian = DB::table('kehadirans')
            ->join('jadwals', 'jadwals.id', '=', 'kehadirans.jadwal_id')
            ->join('mata_pelajarans', 'mata_pelajarans.id', '=', 'jadwals.mata_pelajaran_id')
            ->join('siswas', 'siswas.id', '=', 'kehadirans.student_id')
            ->where('jadwals.tutor_id', $tutorId)
            ->where('kehadirans.status', 'alfa')
            ->where('kehadirans.tanggal', '>=', Carbon::now()->subDays(30))
            ->select('siswas.nama', 'mata_pelajarans.nama as mapel', DB::raw('COUNT(*) as cnt'))
            ->groupBy('siswas.id', 'siswas.nama', 'mata_pelajarans.nama')
            ->orderByDesc('cnt')
            ->limit(6)
            ->get()
            ->map(fn ($r) => [
                'nama' => $r->nama,
                'mapel' => $r->mapel,
                'detail' => (int) $r->cnt === 1 ? '1× alpa (30 hari)' : (int) $r->cnt.'× alpa (30 hari)',
            ]);

        $jkRows = DB::table('siswas')
            ->join('kehadirans', 'kehadirans.student_id', '=', 'siswas.id')
            ->join('jadwals', 'jadwals.id', '=', 'kehadirans.jadwal_id')
            ->where('jadwals.tutor_id', $tutorId)
            ->where('siswas.status', 'aktif')
            ->select('siswas.jenis_kelamin', DB::raw('COUNT(DISTINCT siswas.id) as c'))
            ->groupBy('siswas.jenis_kelamin')
            ->pluck('c', 'jenis_kelamin');

        $siswaJk = [
            'laki_laki' => (int) ($jkRows['laki_laki'] ?? 0),
            'perempuan' => (int) ($jkRows['perempuan'] ?? 0),
        ];

        $presensi7 = [];
        $start7 = Carbon::now()->subDays(6)->startOfDay();
        for ($i = 0; $i < 7; $i++) {
            $d = $start7->copy()->addDays($i);
            $presensi7[] = [
                'label' => self::ID_WEEKDAY[$d->dayOfWeek],
                'value' => $this->kehadiranHadirCountForTutorOnDate($tutorId, $d),
            ];
        }

        $presensi1m = [];
        $monthStart = Carbon::now()->startOfMonth();
        $daysInMonth = (int) Carbon::now()->daysInMonth;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $d = $monthStart->copy()->day($day);
            $presensi1m[] = [
                'label' => (string) $day,
                'value' => $this->kehadiranHadirCountForTutorOnDate($tutorId, $d),
            ];
        }

        $year = (int) Carbon::now()->year;
        $presensi1y = [];
        for ($m = 1; $m <= 12; $m++) {
            $presensi1y[] = [
                'label' => self::ID_MONTH_SHORT[$m],
                'value' => $this->kehadiranHadirCountForTutorInMonth($tutorId, $year, $m),
            ];
        }

        $range = $request->query('presensi_range', '7d');
        if (! in_array($range, ['7d', '1m', '1y'], true)) {
            $range = '7d';
        }

        return [
            'tutor_cards' => [
                'sesi_hari_ini' => $sesiHariIni,
                'siswa_bimbingan' => $siswaBimbingan,
                'alfa_hari_ini' => $alfaHariIni,
                'hadir_7_hari' => $hadir7Hari,
            ],
            'jadwal_mengajar' => $jadwalMengajar,
            'siswa_perhatian' => $siswaPerhatian,
            'siswa_jenis_kelamin' => $siswaJk,
            'presensi_series' => [
                '7d' => $presensi7,
                '1m' => $presensi1m,
                '1y' => $presensi1y,
            ],
            'presensi_range' => $range,
        ];
    }

    public function tutorSiswaIndex(Request $request): LengthAwarePaginator
    {
        $tutorId = $this->actorTutorId();

        return Siswa::query()
            ->with('cabang:id,nama_cabang')
            ->when($tutorId, function ($q) use ($tutorId) {
                $q->whereHas('kehadirans', fn ($k) => $k->whereHas('jadwal', fn ($j) => $j->where('tutor_id', $tutorId)));
            }, fn ($q) => $q->whereRaw('1 = 0'))
            ->when($request->string('search')->toString(), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('jenis_kelamin'), fn ($q) => $q->where('jenis_kelamin', $request->string('jenis_kelamin')->toString()))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->orderBy('nama')
            ->paginate(12)
            ->withQueryString();
    }

    /**
     * Dashboard siswa: kartu, jadwal besok, rincian pembayaran, aktivitas.
     */
    public function siswaDashboardData(): array
    {
        $siswaId = $this->actorSiswaId();
        if (! $siswaId) {
            return [
                'siswa_cards' => [
                    'pct_kehadiran_bulan' => 0,
                    'tagihan_belum' => 0,
                    'sesi_minggu_ini' => 0,
                    'mapel_diikuti' => 0,
                    'kehadiran_sub' => '—',
                    'tagihan_sub' => '—',
                ],
                'jadwal_besok' => collect(),
                'pembayaran_rincian' => collect(),
                'aktivitas_terkini' => collect(),
                'pembayaran_alert' => ['ada_tagihan' => false, 'outstanding' => 0, 'pesan' => ''],
            ];
        }

        $now = Carbon::now();
        $monthKehadiran = Kehadiran::query()
            ->where('student_id', $siswaId)
            ->whereYear('tanggal', $now->year)
            ->whereMonth('tanggal', $now->month);
        $totalBulan = (int) (clone $monthKehadiran)->count();
        $hadirBulan = (int) (clone $monthKehadiran)->where('status', 'hadir')->count();
        $pctBulan = $totalBulan > 0 ? (int) round($hadirBulan / $totalBulan * 100) : 0;

        $tagihanBelum = Payment::query()
            ->where('student_id', $siswaId)
            ->where('status', 'belum');
        $jumlahTagihan = (int) (clone $tagihanBelum)->count();
        $outstandingNom = (int) (clone $tagihanBelum)->sum('nominal');
        $nextDue = (clone $tagihanBelum)->orderBy('tanggal_bayar')->first();

        $startWeek = Carbon::now()->startOfWeek();
        $endWeek = Carbon::now()->endOfWeek();
        $sesiMingguIni = (int) Kehadiran::query()
            ->where('student_id', $siswaId)
            ->whereBetween('tanggal', [$startWeek->toDateString(), $endWeek->toDateString()])
            ->count();

        $mapelDiikuti = (int) Jadwal::query()
            ->whereHas('kehadirans', fn ($k) => $k->where('student_id', $siswaId))
            ->count();

        $hariBesok = self::jadwalHariFromCarbon(Carbon::tomorrow());
        $jadwalBesok = Jadwal::query()
            ->where('hari', $hariBesok)
            ->whereHas('kehadirans', fn ($k) => $k->where('student_id', $siswaId))
            ->with(['tutor:id,nama', 'cabang:id,nama_cabang', 'mataPelajaran:id,nama'])
            ->orderBy('jam_mulai')
            ->get();

        $pembayaranRincian = Payment::query()
            ->where('student_id', $siswaId)
            ->with('fee:id,nama_biaya')
            ->latest('tanggal_bayar')
            ->limit(6)
            ->get();

        $presensiLines = Kehadiran::query()
            ->where('student_id', $siswaId)
            ->with(['jadwal:id,mata_pelajaran_id', 'jadwal.mataPelajaran:id,nama'])
            ->latest('tanggal')
            ->limit(5)
            ->get()
            ->map(fn (Kehadiran $k) => [
                'at' => $k->tanggal,
                'teks' => 'Presensi — '.(optional($k->jadwal)->mapel ?? 'Sesi').' — '.ucfirst((string) $k->status),
            ]);

        $paymentLines = Payment::query()
            ->where('student_id', $siswaId)
            ->with('fee:id,nama_biaya')
            ->latest('tanggal_bayar')
            ->limit(4)
            ->get()
            ->map(fn (Payment $p) => [
                'at' => $p->tanggal_bayar,
                'teks' => 'Pembayaran — '.(optional($p->fee)->nama_biaya ?? 'Biaya').' — '.($p->status === 'lunas' ? 'Lunas' : 'Belum lunas'),
            ]);

        $aktivitas = $presensiLines->concat($paymentLines)
            ->sortByDesc(fn (array $a) => $a['at']?->timestamp ?? 0)
            ->take(8)
            ->values();

        $tagihanSub = $jumlahTagihan > 0
            ? 'Rp '.number_format($outstandingNom, 0, ',', '.').($nextDue ? ' · jatuh tempo '.$nextDue->tanggal_bayar->translatedFormat('d M') : '')
            : 'Tidak ada tunggakan';

        return [
            'siswa_cards' => [
                'pct_kehadiran_bulan' => $pctBulan,
                'tagihan_belum' => $jumlahTagihan,
                'sesi_minggu_ini' => $sesiMingguIni,
                'mapel_diikuti' => $mapelDiikuti,
                'kehadiran_sub' => $totalBulan > 0 ? $hadirBulan.' dari '.$totalBulan.' sesi tercatat' : 'Belum ada presensi bulan ini',
                'tagihan_sub' => $tagihanSub,
            ],
            'jadwal_besok' => $jadwalBesok,
            'pembayaran_rincian' => $pembayaranRincian,
            'aktivitas_terkini' => $aktivitas,
            'pembayaran_alert' => [
                'ada_tagihan' => $jumlahTagihan > 0,
                'outstanding' => $outstandingNom,
                'pesan' => $jumlahTagihan > 0
                    ? 'Anda memiliki '.$jumlahTagihan.' tagihan yang belum lunas. Total estimasi Rp '.number_format($outstandingNom, 0, ',', '.').'.'
                    : 'Semua tagihan terbaru sudah lunas. Terima kasih!',
            ],
        ];
    }

    public function presensiJadwalFilterOptionsForSiswa(): SupportCollection
    {
        $siswaId = $this->actorSiswaId();
        if (! $siswaId) {
            return collect();
        }

        return Jadwal::query()
            ->with('mataPelajaran:id,nama')
            ->whereHas('kehadirans', fn ($k) => $k->where('student_id', $siswaId))
            ->join('mata_pelajarans as _mp', '_mp.id', '=', 'jadwals.mata_pelajaran_id')
            ->orderBy('_mp.nama')
            ->select('jadwals.*')
            ->get();
    }

    public function cabangIndex(Request $request): LengthAwarePaginator
    {
        $cabangId = $this->actorCabangId();

        return Cabang::query()
            ->when($cabangId && ! Auth::user()?->hasRole('super_admin'), fn ($q) => $q->where('id', $cabangId))
            ->when($request->string('search')->toString(), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_cabang', 'like', "%{$search}%")
                        ->orWhere('kota', 'like', "%{$search}%");
                });
            })
            ->when($request->string('kota')->toString(), fn ($q, $kota) => $q->where('kota', $kota))
            ->when($request->boolean('active_only'), fn ($q) => $q->where('status', 'aktif'))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();
    }

    public function siswaIndex(Request $request): LengthAwarePaginator
    {
        $cabangId = $this->actorCabangId();
        $siswaId = $this->actorSiswaId();

        return Siswa::query()
            ->with(['cabang:id,nama_cabang'])
            ->withCount(['payments as lunas_count' => fn ($q) => $q->where('status', 'lunas')])
            ->when($siswaId, fn ($q) => $q->where('id', $siswaId))
            ->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))
            ->when($request->string('search')->toString(), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('cabang_id'), fn ($q) => $q->where('cabang_id', $request->integer('cabang_id')))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();
    }

    public function tutorIndex(Request $request): LengthAwarePaginator
    {
        $cabangId = $this->actorCabangId();
        $tutorId = $this->actorTutorId();

        return Tutor::query()
            ->with('cabang:id,nama_cabang')
            ->withCount('jadwals')
            ->when($tutorId, fn ($q) => $q->where('id', $tutorId))
            ->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))
            ->when($request->string('search')->toString(), fn ($q, $s) => $q->where('nama', 'like', "%{$s}%"))
            ->when($request->filled('cabang_id'), fn ($q) => $q->where('cabang_id', $request->integer('cabang_id')))
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();
    }

    public function jadwalIndex(Request $request): LengthAwarePaginator
    {
        $cabangId = $this->actorCabangId();
        $tutorId = $this->actorTutorId();
        $siswaId = $this->actorSiswaId();

        return Jadwal::query()
            ->with(['tutor:id,nama', 'cabang:id,nama_cabang', 'mataPelajaran:id,nama'])
            ->when($siswaId, fn ($q) => $q->whereHas('kehadirans', fn ($k) => $k->where('student_id', $siswaId)))
            ->when($cabangId && ! $siswaId, fn ($q) => $q->where('cabang_id', $cabangId))
            ->when($tutorId, fn ($q) => $q->where('tutor_id', $tutorId))
            ->when($request->filled('cabang_id'), fn ($q) => $q->where('cabang_id', $request->integer('cabang_id')))
            ->when($request->string('hari')->toString(), fn ($q, $hari) => $q->where('hari', $hari))
            ->orderByRaw("CASE hari WHEN 'senin' THEN 1 WHEN 'selasa' THEN 2 WHEN 'rabu' THEN 3 WHEN 'kamis' THEN 4 WHEN 'jumat' THEN 5 WHEN 'sabtu' THEN 6 WHEN 'minggu' THEN 7 ELSE 8 END")
            ->orderBy('jam_mulai')
            ->paginate(12)
            ->withQueryString();
    }

    public function presensiIndex(Request $request): LengthAwarePaginator
    {
        $cabangId = $this->actorCabangId();
        $siswaId = $this->actorSiswaId();
        $tutorId = $this->actorTutorId();

        return Kehadiran::query()
            ->with([
                'siswa:id,nama',
                'tutor:id,nama',
                'jadwal:id,mata_pelajaran_id,tutor_id,cabang_id',
                'jadwal.mataPelajaran:id,nama',
                'jadwal.tutor:id,nama',
                'jadwal.cabang:id,nama_cabang',
            ])
            ->when($tutorId, fn ($q) => $q->whereHas('jadwal', fn ($j) => $j->where('tutor_id', $tutorId)))
            ->when($cabangId && ! $tutorId && ! $siswaId, fn ($q) => $q->whereHas('siswa', fn ($s) => $s->where('cabang_id', $cabangId)))
            ->when($siswaId, fn ($q) => $q->where('student_id', $siswaId))
            ->when($request->filled('tanggal'), fn ($q) => $q->whereDate('tanggal', $request->date('tanggal')))
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->when($request->filled('jadwal_id'), fn ($q) => $q->where('jadwal_id', $request->integer('jadwal_id')))
            ->latest('tanggal')
            ->paginate(15)
            ->withQueryString();
    }

    public function presensiSummary(Request $request): array
    {
        $cabangId = $this->actorCabangId();
        $siswaId = $this->actorSiswaId();
        $tutorId = $this->actorTutorId();

        if ($siswaId) {
            return $this->presensiSummaryForSiswa($request, $siswaId);
        }

        $base = Kehadiran::query()
            ->when($tutorId, fn ($q) => $q->whereHas('jadwal', fn ($j) => $j->where('tutor_id', $tutorId)))
            ->when($cabangId && ! $tutorId, fn ($q) => $q->whereHas('siswa', fn ($s) => $s->where('cabang_id', $cabangId)))
            ->when($request->filled('tanggal'), fn ($q) => $q->whereDate('tanggal', $request->date('tanggal')));

        return [
            'hadir' => (clone $base)->where('status', 'hadir')->count(),
            'izin' => (clone $base)->where('status', 'izin')->count(),
            'alfa' => (clone $base)->where('status', 'alfa')->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presensiSummaryForSiswa(Request $request, int $siswaId): array
    {
        $now = Carbon::now();
        $monthBase = Kehadiran::query()
            ->where('student_id', $siswaId)
            ->whereYear('tanggal', $now->year)
            ->whereMonth('tanggal', $now->month);
        $totalBulan = (int) (clone $monthBase)->count();
        $hadirBulan = (int) (clone $monthBase)->where('status', 'hadir')->count();
        $pctBulan = $totalBulan > 0 ? (int) round($hadirBulan / $totalBulan * 100) : 0;

        $filterBase = Kehadiran::query()
            ->where('student_id', $siswaId)
            ->when($request->filled('tanggal'), fn ($q) => $q->whereDate('tanggal', $request->date('tanggal')))
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->when($request->filled('jadwal_id'), fn ($q) => $q->where('jadwal_id', $request->integer('jadwal_id')));

        $sesiTercatat = (int) (clone $filterBase)->count();

        $last = Kehadiran::query()
            ->where('student_id', $siswaId)
            ->latest('tanggal')
            ->first();

        return [
            'siswa_mode' => true,
            'pct_bulan' => $pctBulan,
            'sesi_tercatat' => $sesiTercatat,
            'hadir_bulan_ini' => $hadirBulan,
            'total_bulan_ini' => $totalBulan,
            'terakhir_label' => $last?->tanggal?->translatedFormat('d M Y') ?? '—',
        ];
    }

    public function pembayaranIndex(Request $request): LengthAwarePaginator
    {
        $cabangId = $this->actorCabangId();
        $siswaId = $this->actorSiswaId();

        return Payment::query()
            ->with(['siswa:id,nama', 'fee:id,nama_biaya'])
            ->when($cabangId && ! $siswaId, fn ($q) => $q->whereHas('siswa', fn ($s) => $s->where('cabang_id', $cabangId)))
            ->when($siswaId, fn ($q) => $q->where('student_id', $siswaId))
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->when($request->filled('student_id') && ! $siswaId, fn ($q) => $q->where('student_id', $request->integer('student_id')))
            ->when($request->filled('bulan'), function ($q) use ($request) {
                $b = $request->string('bulan')->toString();
                if (preg_match('/^(\d{4})-(\d{2})$/', $b, $m)) {
                    $q->whereYear('tanggal_bayar', (int) $m[1])->whereMonth('tanggal_bayar', (int) $m[2]);
                }
            })
            ->latest('tanggal_bayar')
            ->paginate(12)
            ->withQueryString();
    }

    public function pembayaranSummary(Request $request): array
    {
        $cabangId = $this->actorCabangId();
        $siswaId = $this->actorSiswaId();

        $base = Payment::query()
            ->when($cabangId && ! $siswaId, fn ($q) => $q->whereHas('siswa', fn ($s) => $s->where('cabang_id', $cabangId)))
            ->when($siswaId, fn ($q) => $q->where('student_id', $siswaId))
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->when($request->filled('student_id') && ! $siswaId, fn ($q) => $q->where('student_id', $request->integer('student_id')))
            ->when($request->filled('bulan'), function ($q) use ($request) {
                $b = $request->string('bulan')->toString();
                if (preg_match('/^(\d{4})-(\d{2})$/', $b, $m)) {
                    $q->whereYear('tanggal_bayar', (int) $m[1])->whereMonth('tanggal_bayar', (int) $m[2]);
                }
            });

        $total = (clone $base)->sum('nominal');
        $paid = (clone $base)->where('status', 'lunas')->sum('nominal');

        return [
            'total' => $total,
            'paid' => $paid,
            'outstanding' => max($total - $paid, 0),
            'belum_count' => (int) (clone $base)->where('status', 'belum')->count(),
            'lunas_count' => (int) (clone $base)->where('status', 'lunas')->count(),
            'pie' => Fee::query()
                ->select('nama_biaya', DB::raw('SUM(payments.nominal) as total_nominal'))
                ->join('payments', 'payments.biaya_id', '=', 'fees.id')
                ->when($cabangId && ! $siswaId, fn ($q) => $q->join('siswas', 'siswas.id', '=', 'payments.student_id')->where('siswas.cabang_id', $cabangId))
                ->when($siswaId, fn ($q) => $q->where('payments.student_id', $siswaId))
                ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('payments.status', $status))
                ->when($request->filled('student_id') && ! $siswaId, fn ($q) => $q->where('payments.student_id', $request->integer('student_id')))
                ->when($request->filled('bulan'), function ($q) use ($request) {
                    $b = $request->string('bulan')->toString();
                    if (preg_match('/^(\d{4})-(\d{2})$/', $b, $m)) {
                        $q->whereYear('payments.tanggal_bayar', (int) $m[1])->whereMonth('payments.tanggal_bayar', (int) $m[2]);
                    }
                })
                ->groupBy('nama_biaya')
                ->orderByDesc('total_nominal')
                ->limit(4)
                ->get(),
        ];
    }

    public function laporanData(Request $request): array
    {
        $cabangId = $this->actorCabangId();
        $start = $request->date('start_date') ?? now()->startOfMonth();
        $end = $request->date('end_date') ?? now()->endOfMonth();

        $paymentByFee = Fee::query()
            ->select('nama_biaya', DB::raw('SUM(payments.nominal) as total_nominal'))
            ->join('payments', 'payments.biaya_id', '=', 'fees.id')
            ->when($cabangId, fn ($q) => $q->join('siswas', 'siswas.id', '=', 'payments.student_id')->where('siswas.cabang_id', $cabangId))
            ->whereBetween('payments.tanggal_bayar', [$start, $end])
            ->groupBy('nama_biaya')
            ->orderByDesc('total_nominal')
            ->get();

        $rankingCabang = Cabang::query()
            ->select('cabangs.nama_cabang', DB::raw('AVG(CASE WHEN kehadirans.status = "hadir" THEN 100 ELSE 0 END) as hadir_pct'))
            ->join('siswas', 'siswas.cabang_id', '=', 'cabangs.id')
            ->join('kehadirans', 'kehadirans.student_id', '=', 'siswas.id')
            ->when($cabangId, fn ($q) => $q->where('cabangs.id', $cabangId))
            ->whereBetween('kehadirans.tanggal', [$start, $end])
            ->groupBy('cabangs.nama_cabang')
            ->orderByDesc('hadir_pct')
            ->limit(5)
            ->get();

        $trx = Payment::query()
            ->with(['siswa.cabang:id,nama_cabang', 'fee:id,nama_biaya'])
            ->when($cabangId, fn ($q) => $q->whereHas('siswa', fn ($s) => $s->where('cabang_id', $cabangId)))
            ->whereBetween('tanggal_bayar', [$start, $end])
            ->latest('tanggal_bayar')
            ->limit(20)
            ->get();

        return compact('paymentByFee', 'rankingCabang', 'trx', 'start', 'end');
    }

    public function studentsForSelect(): Collection
    {
        $q = Siswa::query()->select('id', 'nama', 'cabang_id')->orderBy('nama');

        $user = Auth::user();
        if ($user && $user->hasRole('admin_cabang')) {
            $cid = $this->actorCabangId();
            if ($cid) {
                $q->where('cabang_id', $cid);
            }
        }

        return $q->get();
    }

    /**
     * Jumlah tagihan belum lunas yang sudah lewat / sama dengan jatuh tempo (untuk aksi massal pengingat).
     */
    public function pembayaranDueBulkCount(Request $request): int
    {
        $cabangId = $this->actorCabangId();
        $siswaId = $this->actorSiswaId();
        if ($siswaId !== null) {
            return 0;
        }

        $q = Payment::query()
            ->belum()
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', now());

        $user = Auth::user();
        if ($user && $user->hasRole('admin_cabang') && $cabangId) {
            $q->whereHas('siswa', fn ($s) => $s->where('cabang_id', $cabangId));
        }

        return (int) $q->count();
    }

    public function feesForSelect(): Collection
    {
        return Fee::query()->select('id', 'nama_biaya', 'nominal', 'tipe')->orderBy('nama_biaya')->get();
    }

    public function tutorsForSelect(): Collection
    {
        return Tutor::query()->select('id', 'nama')->orderBy('nama')->get();
    }

    public function cabangForSelect(): Collection
    {
        $cabangId = $this->actorCabangId();

        return Cabang::query()
            ->select('id', 'nama_cabang')
            ->when($cabangId, fn ($q) => $q->where('id', $cabangId))
            ->orderBy('nama_cabang')
            ->get();
    }

    public function mataPelajaranForSelect(): Collection
    {
        return MataPelajaran::query()->orderBy('nama')->get(['id', 'nama', 'kode']);
    }

    public function salaryIndex(Request $request): LengthAwarePaginator
    {
        $cabangId = $this->actorCabangId();

        return Salary::query()
            ->with(['tutor:id,nama,cabang_id', 'creator:id,name'])
            ->when(
                $cabangId && ! Auth::user()?->hasRole('super_admin'),
                fn ($q) => $q->whereHas('tutor', fn ($t) => $t->where('cabang_id', $cabangId))
            )
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->when($request->filled('tutor_id'), fn ($q) => $q->where('tutor_id', $request->integer('tutor_id')))
            ->latest('id')
            ->paginate(12)
            ->withQueryString();
    }
}
