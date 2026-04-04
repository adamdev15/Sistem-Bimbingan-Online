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
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
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
            ->whereHas('jadwals', fn ($j) => $j->where('tutor_id', $tutorId))
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
            ->join('student_class', 'student_class.student_id', '=', 'siswas.id')
            ->join('jadwals', 'jadwals.id', '=', 'student_class.jadwal_id')
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
                $q->whereHas('jadwals', fn ($j) => $j->where('tutor_id', $tutorId));
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

        $mapelDiikuti = (int) (Siswa::query()->find($siswaId)?->jadwals()->count() ?? 0);

        $hariBesok = self::jadwalHariFromCarbon(Carbon::tomorrow());
        $jadwalBesok = Jadwal::query()
            ->where('hari', $hariBesok)
            ->whereHas('siswas', fn ($s) => $s->where('siswas.id', $siswaId))
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

        $siswa = Siswa::query()->find($siswaId);
        if (! $siswa) {
            return collect();
        }

        return $siswa->jadwals()
            ->with('mataPelajaran:id,nama')
            ->join('mata_pelajarans as _mp', '_mp.id', '=', 'jadwals.mata_pelajaran_id')
            ->orderBy('_mp.nama')
            ->select('jadwals.*')
            ->get();
    }

    /**
     * Opsi filter kelas untuk rekap presensi (super admin / admin cabang).
     *
     * @return SupportCollection<int, Jadwal>
     */
    public function presensiJadwalFilterOptionsForRekap(): SupportCollection
    {
        $cabangId = $this->actorCabangId();
        $tutorId = $this->actorTutorId();

        return Jadwal::query()
            ->with(['mataPelajaran:id,nama', 'cabang:id,nama_cabang'])
            ->when($tutorId, fn ($q) => $q->where('tutor_id', $tutorId))
            ->when($cabangId && ! $tutorId, fn ($q) => $q->where('cabang_id', $cabangId))
            ->orderByRaw("CASE hari WHEN 'senin' THEN 1 WHEN 'selasa' THEN 2 WHEN 'rabu' THEN 3 WHEN 'kamis' THEN 4 WHEN 'jumat' THEN 5 WHEN 'sabtu' THEN 6 WHEN 'minggu' THEN 7 ELSE 8 END")
            ->orderBy('jam_mulai')
            ->get();
    }

    /**
     * Konteks form presensi tutor: kelas + tanggal + daftar peserta.
     *
     * @return array{jadwal: Jadwal, tanggal: CarbonInterface, rows: SupportCollection<int, array{siswa: Siswa, status_saat_ini: string}>, peserta_kosong: bool, ada_data_presensi: bool}|null
     */
    public function presensiTutorKelasContext(Request $request): ?array
    {
        if (! $request->filled('kelas_jadwal_id') || ! $request->filled('kelas_tanggal')) {
            return null;
        }

        $jadwalId = $request->integer('kelas_jadwal_id');
        $tanggal = $request->date('kelas_tanggal');

        $jadwal = Jadwal::query()
            ->with(['mataPelajaran', 'cabang', 'tutor'])
            ->find($jadwalId);

        if (! $jadwal) {
            return null;
        }

        $tutorRecordId = $this->actorTutorId();
        if ($tutorRecordId && (int) $jadwal->tutor_id !== (int) $tutorRecordId) {
            return null;
        }

        $enrolled = $jadwal->siswas()->orderBy('nama')->get(['siswas.id', 'siswas.nama']);

        $existing = Kehadiran::query()
            ->where('jadwal_id', $jadwal->id)
            ->whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('student_id');

        $rows = $enrolled->map(function (Siswa $s) use ($existing) {
            $k = $existing->get($s->id);

            return [
                'siswa' => $s,
                'status_saat_ini' => $k?->status ?? 'hadir',
            ];
        });

        return [
            'jadwal' => $jadwal,
            'tanggal' => $tanggal,
            'rows' => $rows,
            'peserta_kosong' => $enrolled->isEmpty(),
            'ada_data_presensi' => $existing->isNotEmpty(),
        ];
    }

    public function cabangIndex(Request $request): LengthAwarePaginator
    {
        $cabangId = $this->actorCabangId();

        return Cabang::query()
            ->with('user:id,name,email')
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
            ->with(['cabang:id,nama_cabang', 'user:id,name,email'])
            ->withCount('jadwals')
            ->when($tutorId, fn ($q) => $q->where('id', $tutorId))
            ->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))
            ->when($request->string('search')->toString(), function ($q, $s) {
                $like = "%{$s}%";
                $q->where(function ($w) use ($like) {
                    $w->where('nama', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
            })
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
            ->with(['tutor:id,nama', 'cabang:id,nama_cabang', 'mataPelajaran:id,nama,kode'])
            ->withCount('siswas')
            ->when($tutorId, fn ($q) => $q->with([
                'siswas' => fn ($s) => $s->orderBy('nama')->select('siswas.id', 'siswas.nama'),
            ]))
            ->when($siswaId, fn ($q) => $q->whereHas('siswas', fn ($s) => $s->where('siswas.id', $siswaId)))
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
                'creator:id,name',
                'jadwal:id,mata_pelajaran_id,tutor_id,cabang_id,hari,jam_mulai,jam_selesai',
                'jadwal.mataPelajaran:id,nama',
                'jadwal.tutor:id,nama',
                'jadwal.cabang:id,nama_cabang',
            ])
            ->when($tutorId, function ($q) use ($request, $tutorId) {
                $q->whereHas('jadwal', fn ($j) => $j->where('tutor_id', $tutorId));
                if ($request->filled('kelas_jadwal_id') && $request->filled('kelas_tanggal')) {
                    $q->where('jadwal_id', $request->integer('kelas_jadwal_id'))
                        ->whereDate('tanggal', $request->date('kelas_tanggal'));
                }
            })
            ->when($cabangId && ! $tutorId && ! $siswaId, fn ($q) => $q->whereHas('siswa', fn ($s) => $s->where('cabang_id', $cabangId)))
            ->when($siswaId, fn ($q) => $q->where('student_id', $siswaId))
            ->when(! $tutorId && $request->filled('tanggal'), fn ($q) => $q->whereDate('tanggal', $request->date('tanggal')))
            ->when(! $tutorId && $request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->when(! $tutorId && $request->filled('jadwal_id'), fn ($q) => $q->where('jadwal_id', $request->integer('jadwal_id')))
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
            ->when($request->filled('tanggal'), fn ($q) => $q->whereDate('tanggal', $request->date('tanggal')))
            ->when($request->filled('jadwal_id'), fn ($q) => $q->where('jadwal_id', $request->integer('jadwal_id')));

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

    /**
     * Query dasar pembayaran dengan filter halaman (status, siswa, bulan, scope cabang).
     *
     * @return Builder<Payment>
     */
    public function pembayaranFilteredQuery(Request $request): Builder
    {
        $cabangId = $this->actorCabangId();
        $siswaId = $this->actorSiswaId();

        return Payment::query()
            ->when($cabangId && ! $siswaId, fn ($q) => $q->whereHas('siswa', fn ($s) => $s->where('cabang_id', $cabangId)))
            ->when($siswaId, fn ($q) => $q->where('payments.student_id', $siswaId))
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('payments.status', $status))
            ->when($request->filled('student_id') && ! $siswaId, fn ($q) => $q->where('payments.student_id', $request->integer('student_id')))
            ->when($request->filled('bulan'), function ($q) use ($request) {
                $b = $request->string('bulan')->toString();
                if (preg_match('/^(\d{4})-(\d{2})$/', $b, $m)) {
                    $q->whereYear('payments.tanggal_bayar', (int) $m[1])->whereMonth('payments.tanggal_bayar', (int) $m[2]);
                }
            });
    }

    public function pembayaranIndex(Request $request): LengthAwarePaginator
    {
        return $this->pembayaranFilteredQuery($request)
            ->with([
                'siswa:id,nama,email,no_hp,nik,alamat,jenis_kelamin,cabang_id,user_id',
                'siswa.cabang:id,nama_cabang',
                'siswa.user:id,name,email',
                'fee:id,nama_biaya,nominal,tipe',
                'creator:id,name,email',
            ])
            ->latest('tanggal_bayar')
            ->paginate(12)
            ->withQueryString();
    }

    public function pembayaranSummary(Request $request): array
    {
        $cabangId = $this->actorCabangId();
        $siswaId = $this->actorSiswaId();

        $base = $this->pembayaranFilteredQuery($request);

        $total = (clone $base)->sum('payments.nominal');
        $paid = (clone $base)->where('payments.status', 'lunas')->sum('payments.nominal');

        return [
            'total' => $total,
            'paid' => $paid,
            'outstanding' => max($total - $paid, 0),
            'belum_count' => (int) (clone $base)->where('payments.status', 'belum')->count(),
            'lunas_count' => (int) (clone $base)->where('payments.status', 'lunas')->count(),
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

        return array_merge(
            compact('paymentByFee', 'rankingCabang', 'trx', 'start', 'end'),
            $this->laporanAnalytics($request, $start, $end),
        );
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
        $cabangId = $this->actorCabangId();

        return Tutor::query()
            ->select('id', 'nama', 'cabang_id')
            ->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))
            ->orderBy('nama')
            ->get();
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

    /**
     * Query gaji dengan filter halaman (status, tutor, scope cabang).
     *
     * @return Builder<Salary>
     */
    public function salaryFilteredQuery(Request $request): Builder
    {
        $cabangId = $this->actorCabangId();

        return Salary::query()
            ->when(
                $cabangId && ! Auth::user()?->hasRole('super_admin'),
                fn ($q) => $q->whereHas('tutor', fn ($t) => $t->where('tutors.cabang_id', $cabangId))
            )
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('salaries.status', $s))
            ->when($request->filled('tutor_id'), fn ($q) => $q->where('salaries.tutor_id', $request->integer('tutor_id')));
    }

    public function salaryIndex(Request $request): LengthAwarePaginator
    {
        return $this->salaryFilteredQuery($request)
            ->with(['tutor:id,nama,cabang_id', 'creator:id,name'])
            ->latest('salaries.id')
            ->paginate(12)
            ->withQueryString();
    }

    /**
     * Payload laporan ekspor gaji tutor (ringkasan, agregat, detail, insight).
     *
     * @return array<string, mixed>
     */
    public function salaryReportPayload(Request $request): array
    {
        $user = Auth::user();
        $isSuper = (bool) $user?->hasRole('super_admin');
        $base = $this->salaryFilteredQuery($request);

        $perTutor = (clone $base)
            ->join('tutors', 'tutors.id', '=', 'salaries.tutor_id')
            ->leftJoin('cabangs', 'cabangs.id', '=', 'tutors.cabang_id')
            ->select([
                'tutors.id as tutor_id',
                'tutors.nama as tutor_nama',
                'cabangs.nama_cabang',
                DB::raw('SUM(salaries.total_jam) as total_jam'),
                DB::raw('SUM(salaries.total_gaji) as total_gaji'),
                DB::raw('COUNT(salaries.id) as entri_count'),
            ])
            ->groupBy('tutors.id', 'tutors.nama', 'cabangs.id', 'cabangs.nama_cabang')
            ->orderByDesc(DB::raw('SUM(salaries.total_jam)'))
            ->get();

        $perPeriode = (clone $base)
            ->select([
                'salaries.periode',
                DB::raw('SUM(salaries.total_jam) as total_jam'),
                DB::raw('SUM(salaries.total_gaji) as total_gaji'),
                DB::raw('COUNT(salaries.id) as entri_count'),
            ])
            ->groupBy('salaries.periode')
            ->orderBy('salaries.periode')
            ->get();

        $detailRows = (clone $base)
            ->with(['tutor.cabang:id,nama_cabang', 'creator:id,name'])
            ->orderByDesc('salaries.id')
            ->get();

        $totalGaji = (float) (clone $base)->sum('salaries.total_gaji');
        $totalJam = (int) (clone $base)->sum('salaries.total_jam');

        $topJam = $perTutor->sortByDesc('total_jam')->first();
        $topGaji = $perTutor->sortByDesc('total_gaji')->first();

        $parts = [];
        if ($request->string('status')->toString() !== '') {
            $parts[] = 'Status '.$request->string('status');
        }
        if ($request->filled('tutor_id')) {
            $parts[] = 'Tutor ID '.$request->integer('tutor_id');
        }
        $filterLabel = $parts === [] ? 'Semua entri (sesuai hak akses cabang)' : implode(' · ', $parts);

        return [
            'generated_at' => now(),
            'filter_label' => $filterLabel,
            'is_super_admin' => $isSuper,
            'per_tutor' => $perTutor,
            'per_periode' => $perPeriode,
            'detail_rows' => $detailRows,
            'total_gaji' => $totalGaji,
            'total_jam' => $totalJam,
            'entri_count' => $detailRows->count(),
            'insight_aktif' => $topJam
                ? 'Tutor paling aktif (total jam mengajar pada filter): '.$topJam->tutor_nama.' ('.(int) $topJam->total_jam.' jam).'
                : 'Belum ada data jam mengajar pada filter ini.',
            'insight_biaya' => $totalGaji > 0
                ? 'Total beban gaji (operasional tutor) pada filter: Rp '.number_format((int) round($totalGaji), 0, ',', '.').' — pantau proporsi terhadap pendapatan di menu Laporan.'
                : 'Tidak ada nominal gaji pada filter ini.',
            'insight_top_gaji' => $topGaji && (float) $topGaji->total_gaji > 0
                ? 'Tutor dengan total nominal gaji tertinggi: '.$topGaji->tutor_nama.' (Rp '.number_format((int) round((float) $topGaji->total_gaji), 0, ',', '.').').'
                : '',
        ];
    }

    /**
     * Data grafik & KPI analitik untuk halaman laporan.
     *
     * @return array<string, mixed>
     */
    public function laporanAnalytics(Request $request, CarbonInterface $start, CarbonInterface $end): array
    {
        $cabangId = $this->actorCabangId();
        $driver = DB::getDriverName();
        $payDateExpr = match ($driver) {
            'sqlite' => 'date(payments.tanggal_bayar)',
            default => 'DATE(payments.tanggal_bayar)',
        };

        $revMode = $request->string('rev_mode', 'bulan')->toString();
        if (! in_array($revMode, ['minggu', 'bulan', 'tahun'], true)) {
            $revMode = 'bulan';
        }

        $revenueChart = $this->buildRevenueChartSeries($cabangId, $payDateExpr, $revMode, now());

        $khDimensi = $request->string('kh_dimensi', 'cabang')->toString();
        if (! in_array($khDimensi, ['cabang', 'tutor', 'mapel'], true)) {
            $khDimensi = 'cabang';
        }

        $kehadiranChart = $this->buildKehadiranChartByDimensi($cabangId, $khDimensi);

        $cvWindow = $request->string('cv_window', 'bulan')->toString();
        if (! in_array($cvWindow, ['bulan', 'tahun'], true)) {
            $cvWindow = 'bulan';
        }
        $cvEnd = now()->endOfDay();
        $cvStart = $cvWindow === 'tahun'
            ? now()->copy()->subYear()->startOfDay()
            : now()->copy()->subMonth()->startOfDay();

        $conversionChart = $this->buildPaymentConversionSeries($cvStart, $cvEnd, $cabangId, $cvWindow);

        return [
            'revenue_chart' => $revenueChart,
            'kehadiran_chart' => $kehadiranChart,
            'conversion_chart' => $conversionChart,
            'rev_mode' => $revMode,
            'kh_dimensi' => $khDimensi,
            'cv_window' => $cvWindow,
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<float>, title: string, subtitle: string, trend: string}
     */
    private function buildRevenueChartSeries(?int $cabangId, string $payDateExpr, string $revMode, CarbonInterface $revRef): array
    {
        $paymentBase = function () use ($cabangId) {
            return Payment::query()
                ->when($cabangId, fn ($q) => $q->whereHas('siswa', fn ($s) => $s->where('cabang_id', $cabangId)));
        };

        if ($revMode === 'minggu') {
            $weekStart = $revRef->copy()->locale('id')->startOfWeek(Carbon::MONDAY)->startOfDay();
            $weekEnd = $revRef->copy()->locale('id')->endOfWeek(Carbon::SUNDAY)->endOfDay();
            $shortDays = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
            $labels = [];
            $keys = [];
            for ($i = 0; $i < 7; $i++) {
                $d = $weekStart->copy()->addDays($i);
                $labels[] = $shortDays[$i];
                $keys[] = $d->format('Y-m-d');
            }
            $sums = $paymentBase()
                ->whereBetween('payments.tanggal_bayar', [$weekStart, $weekEnd])
                ->selectRaw("{$payDateExpr} as bucket, SUM(payments.nominal) as total")
                ->groupBy(DB::raw($payDateExpr))
                ->pluck('total', 'bucket');
            $values = [];
            foreach ($keys as $k) {
                $values[] = (float) ($sums[$k] ?? 0);
            }
            $title = 'Pendapatan (minggu)';
            $subtitle = $weekStart->translatedFormat('d M').' – '.$weekEnd->translatedFormat('d M Y');
        } elseif ($revMode === 'tahun') {
            $yStart = $revRef->copy()->startOfYear()->startOfDay();
            $yEnd = $revRef->copy()->endOfYear()->endOfDay();
            $monthExpr = match (DB::getDriverName()) {
                'sqlite' => "cast(strftime('%m', payments.tanggal_bayar) as integer)",
                default => 'MONTH(payments.tanggal_bayar)',
            };
            $rows = $paymentBase()
                ->whereBetween('payments.tanggal_bayar', [$yStart, $yEnd])
                ->selectRaw("{$monthExpr} as m, SUM(payments.nominal) as total")
                ->groupBy(DB::raw((string) $monthExpr))
                ->pluck('total', 'm');
            $byMonth = [];
            foreach ($rows as $k => $v) {
                $byMonth[(int) $k] = (float) $v;
            }
            $labels = [];
            $values = [];
            for ($m = 1; $m <= 12; $m++) {
                $labels[] = self::ID_MONTH_SHORT[$m] ?? (string) $m;
                $values[] = $byMonth[$m] ?? 0.0;
            }
            $title = 'Pendapatan (tahun)';
            $subtitle = (string) $revRef->year;
        } else {
            $mStart = $revRef->copy()->startOfMonth()->startOfDay();
            $mEnd = $revRef->copy()->endOfMonth()->endOfDay();
            $daysInMonth = (int) $mEnd->day;
            $sums = $paymentBase()
                ->whereBetween('payments.tanggal_bayar', [$mStart, $mEnd])
                ->selectRaw("{$payDateExpr} as bucket, SUM(payments.nominal) as total")
                ->groupBy(DB::raw($payDateExpr))
                ->pluck('total', 'bucket');
            $labels = [];
            $values = [];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dayDate = $mStart->copy()->addDays($d - 1);
                $key = $dayDate->format('Y-m-d');
                $labels[] = (string) $d;
                $values[] = (float) ($sums[$key] ?? 0);
            }
            $title = 'Pendapatan (bulan)';
            $subtitle = $mStart->translatedFormat('F Y');
        }

        $n = count($values);
        $trend = 'Cukup data untuk tren setelah periode berjalan.';
        if ($n >= 2) {
            $first = array_slice($values, 0, (int) floor($n / 2));
            $second = array_slice($values, (int) floor($n / 2));
            $a = array_sum($first) / max(count($first), 1);
            $b = array_sum($second) / max(count($second), 1);
            if ($b > $a * 1.05) {
                $trend = 'Tren nominal pendapatan cenderung naik di paruh kedua periode tampilan.';
            } elseif ($b < $a * 0.95) {
                $trend = 'Tren nominal pendapatan cenderung turun di paruh kedua periode tampilan.';
            } else {
                $trend = 'Tren stabil di sepanjang periode tampilan.';
            }
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'title' => $title,
            'subtitle' => $subtitle,
            'trend' => $trend,
        ];
    }

    /**
     * Grafik kehadiran: bar bertumpuk per cabang / tutor / mapel (hingga 12 entitas terbanyak).
     *
     * @return array<string, mixed>
     */
    private function buildKehadiranChartByDimensi(?int $actorCabangId, string $dimensi): array
    {
        $agg = [
            DB::raw("SUM(CASE WHEN kehadirans.status = 'hadir' THEN 1 ELSE 0 END) as c_hadir"),
            DB::raw("SUM(CASE WHEN kehadirans.status = 'izin' THEN 1 ELSE 0 END) as c_izin"),
            DB::raw("SUM(CASE WHEN kehadirans.status = 'sakit' THEN 1 ELSE 0 END) as c_sakit"),
            DB::raw("SUM(CASE WHEN kehadirans.status = 'alfa' THEN 1 ELSE 0 END) as c_alfa"),
            DB::raw('COUNT(kehadirans.id) as c_total'),
        ];

        $q = Kehadiran::query()
            ->join('jadwals', 'jadwals.id', '=', 'kehadirans.jadwal_id')
            ->when($actorCabangId, fn ($qq) => $qq->where('jadwals.cabang_id', $actorCabangId));

        if ($dimensi === 'cabang') {
            $q->join('cabangs', 'cabangs.id', '=', 'jadwals.cabang_id');
            $rows = $q->select(array_merge([DB::raw('cabangs.nama_cabang as dim_label')], $agg))
                ->groupBy('cabangs.id', 'cabangs.nama_cabang')
                ->orderByDesc('c_total')
                ->limit(12)
                ->get();
            $dimensiLabel = 'cabang';
        } elseif ($dimensi === 'tutor') {
            $q->join('tutors', 'tutors.id', '=', 'kehadirans.tutor_id');
            $rows = $q->select(array_merge([DB::raw('tutors.nama as dim_label')], $agg))
                ->groupBy('tutors.id', 'tutors.nama')
                ->orderByDesc('c_total')
                ->limit(12)
                ->get();
            $dimensiLabel = 'tutor';
        } else {
            $q->leftJoin('mata_pelajarans', 'mata_pelajarans.id', '=', 'jadwals.mata_pelajaran_id');
            $rows = $q->select(array_merge([
                DB::raw("COALESCE(MAX(mata_pelajarans.nama), 'Tanpa mapel') as dim_label"),
            ], $agg))
                ->groupBy('jadwals.mata_pelajaran_id')
                ->orderByDesc('c_total')
                ->limit(12)
                ->get();
            $dimensiLabel = 'mata pelajaran';
        }

        $totalHadir = (int) $rows->sum('c_hadir');
        $totalAll = (int) $rows->sum('c_total');
        $hadirPct = $totalAll > 0 ? round(100 * $totalHadir / $totalAll, 1) : 0.0;

        $labels = $rows->pluck('dim_label')->map(fn ($l) => (string) $l)->all();

        return [
            'chart_kind' => 'stacked_bar',
            'labels' => $labels,
            'datasets' => [
                ['label' => 'Hadir', 'data' => $rows->pluck('c_hadir')->map(fn ($v) => (int) $v)->all(), 'backgroundColor' => '#059669'],
                ['label' => 'Izin', 'data' => $rows->pluck('c_izin')->map(fn ($v) => (int) $v)->all(), 'backgroundColor' => '#3b82f6'],
                ['label' => 'Sakit', 'data' => $rows->pluck('c_sakit')->map(fn ($v) => (int) $v)->all(), 'backgroundColor' => '#8b5cf6'],
                ['label' => 'Alfa', 'data' => $rows->pluck('c_alfa')->map(fn ($v) => (int) $v)->all(), 'backgroundColor' => '#e11d48'],
            ],
            'hadir_pct' => $hadirPct,
            'subtitle' => $rows->isEmpty()
                ? 'Belum ada data presensi.'
                : 'Hingga 12 '.$dimensiLabel.' dengan presensi terbanyak · keseluruhan data.',
            'dimensi_label' => $dimensiLabel,
        ];
    }

    /**
     * @return array{lunas_nominal: float, belum_nominal: float, lunas_count: int, belum_count: int, total_count: int, paid_rate_pct: float, subtitle: string, window_label: string}
     */
    private function buildPaymentConversionSeries(CarbonInterface $start, CarbonInterface $end, ?int $cabangId, string $cvWindow): array
    {
        $base = Payment::query()
            ->when($cabangId, fn ($q) => $q->whereHas('siswa', fn ($s) => $s->where('cabang_id', $cabangId)))
            ->whereBetween('payments.tanggal_bayar', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);

        $lunasNom = (float) (clone $base)->where('payments.status', 'lunas')->sum('payments.nominal');
        $belumNom = (float) (clone $base)->where('payments.status', 'belum')->sum('payments.nominal');
        $lunasCount = (int) (clone $base)->where('payments.status', 'lunas')->count();
        $belumCount = (int) (clone $base)->where('payments.status', 'belum')->count();
        $totalCount = $lunasCount + $belumCount;
        $paidRate = $totalCount > 0 ? round(100 * $lunasCount / $totalCount, 1) : 0.0;

        $windowLabel = $cvWindow === 'tahun' ? '1 tahun terakhir' : '1 bulan terakhir';

        return [
            'lunas_nominal' => $lunasNom,
            'belum_nominal' => $belumNom,
            'lunas_count' => $lunasCount,
            'belum_count' => $belumCount,
            'total_count' => $totalCount,
            'paid_rate_pct' => $paidRate,
            'window_label' => $windowLabel,
            'subtitle' => $totalCount === 0
                ? 'Belum ada tagihan terbit pada '.$windowLabel.'.'
                : $windowLabel.' · '.(int) $paidRate.'% transaksi lunas · nominal lunas Rp '.number_format((int) round($lunasNom), 0, ',', '.'),
        ];
    }

    /**
     * @return list<string>
     */
    public function assignableRoleNames(): array
    {
        return ['super_admin', 'admin_cabang', 'tutor', 'siswa'];
    }

    public function adminUserIndex(Request $request): LengthAwarePaginator
    {
        $search = $request->string('search')->toString();
        $role = $request->string('role')->toString();
        $verified = $request->string('verified')->toString();

        return User::query()
            ->with('roles:name')
            ->when($search !== '', function ($q) use ($search) {
                $like = '%'.$search.'%';
                $q->where(function ($w) use ($like) {
                    $w->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
            })
            ->when($role !== '', fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', $role)))
            ->when($verified === '1', fn ($q) => $q->whereNotNull('email_verified_at'))
            ->when($verified === '0', fn ($q) => $q->whereNull('email_verified_at'))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();
    }
}
