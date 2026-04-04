<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Payment;
use App\Models\Siswa;
use App\Models\User;
use App\Services\Midtrans\MidtransService;
use App\Services\Notifications\InAppBellNotifier;
use App\Services\SuperAdmin\ManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class PembayaranController extends Controller
{
    public function __construct(
        private readonly ManagementService $service,
        private readonly MidtransService $midtrans,
        private readonly InAppBellNotifier $bell,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $unpaidQuick = collect();

        if ($user && $user->hasRole('siswa')) {
            $siswaId = Siswa::query()->where('user_id', $user->id)->value('id')
                ?? Siswa::query()->where('email', $user->email)->value('id');
            if ($siswaId) {
                $unpaidQuick = Payment::query()
                    ->with('fee:id,nama_biaya')
                    ->where('student_id', $siswaId)
                    ->belum()
                    ->latest()
                    ->limit(50)
                    ->get();
            }
        }

        return view('modules.pembayaran.index', [
            'payments' => $this->service->pembayaranIndex($request),
            'summary' => $this->service->pembayaranSummary($request),
            'fees' => $this->service->feesForSelect(),
            'students' => $this->service->studentsForSelect(),
            'filters' => $request->only(['status', 'student_id', 'bulan']),
            'midtransClientKey' => config('midtrans.client_key'),
            'midtransSnapJsUrl' => config('midtrans.is_production')
                ? 'https://app.midtrans.com/snap/snap.js'
                : 'https://app.sandbox.midtrans.com/snap/snap.js',
            'dueBulkCount' => $this->service->pembayaranDueBulkCount($request),
            'unpaidQuick' => $unpaidQuick,
        ]);
    }

    public function snapToken(Request $request, Payment $payment): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->hasRole('siswa')) {
            return response()->json(['message' => 'Hanya akun siswa yang dapat membayar tagihan ini.'], 403);
        }

        $siswaId = Siswa::query()->where('user_id', $user->id)->value('id')
            ?? Siswa::query()->where('email', $user->email)->value('id');

        if (! $siswaId || (int) $payment->student_id !== (int) $siswaId) {
            return response()->json(['message' => 'Tagihan ini bukan milik Anda. Pastikan profil siswa terhubung ke akun (user_id / email).'], 403);
        }

        if ($payment->isLunas()) {
            return response()->json(['message' => 'Tagihan sudah lunas.'], 422);
        }

        if ($payment->grossAmountIdr() < 1) {
            return response()->json(['message' => 'Nominal tagihan tidak valid (minimal Rp 1).'], 422);
        }

        try {
            $token = $this->midtrans->createSnapToken($payment->fresh());
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'token' => $token,
            'client_key' => config('midtrans.client_key'),
        ]);
    }

    /**
     * Tarik status terbaru dari API Midtrans (Server Key) dan update tagihan.
     * Wajib dipanggil setelah Snap selesai di localhost / tanpa webhook publik.
     */
    public function syncMidtrans(Request $request, Payment $payment): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->hasRole('siswa')) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        $siswaId = Siswa::query()->where('user_id', $user->id)->value('id')
            ?? Siswa::query()->where('email', $user->email)->value('id');

        if (! $siswaId || (int) $payment->student_id !== (int) $siswaId) {
            return response()->json(['message' => 'Tagihan ini bukan milik Anda.'], 403);
        }

        $payment->refresh();
        if (! is_string($payment->order_id) || $payment->order_id === '') {
            return response()->json(['message' => 'Belum ada sesi pembayaran Midtrans. Klik Bayar terlebih dahulu.'], 422);
        }

        try {
            $remote = $this->midtrans->fetchTransactionStatus($payment->order_id);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal mengambil status dari Midtrans.',
                'detail' => config('app.debug') ? $e->getMessage() : null,
            ], 502);
        }

        $beforeLunas = $payment->isLunas();
        $this->midtrans->syncPaymentFromMidtransStatus($payment, $remote);
        $payment->refresh();

        if (! $beforeLunas && $payment->isLunas()) {
            $this->bell->paymentSettled($payment);
        }

        return response()->json([
            'payment_status' => $payment->status,
            'midtrans_transaction_status' => $payment->midtrans_transaction_status,
            'is_lunas' => $payment->isLunas(),
        ]);
    }

    public function massStore(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->hasAnyRole(['super_admin', 'admin_cabang']), 403);

        $data = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'exists:siswas,id'],
            'biaya_id' => ['required', 'exists:fees,id'],
            'nominal' => ['required', 'numeric', 'min:1'],
            'tanggal_bayar' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'invoice_period' => ['nullable', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        if ($user->hasRole('admin_cabang')) {
            $cabangId = Cabang::query()->where('user_id', $user->id)->value('id');
            $invalid = Siswa::query()
                ->whereIn('id', $data['student_ids'])
                ->where('cabang_id', '!=', $cabangId)
                ->exists();
            abort_if($invalid || ! $cabangId, 403, 'Siswa tidak termasuk cabang Anda.');
        }

        $notify = ! $request->boolean('skip_notification');
        $count = 0;

        foreach ($data['student_ids'] as $studentId) {
            $payment = Payment::query()->create([
                'student_id' => $studentId,
                'biaya_id' => $data['biaya_id'],
                'invoice_period' => $data['invoice_period'] ?? null,
                'nominal' => $data['nominal'],
                'tanggal_bayar' => $data['tanggal_bayar'],
                'due_date' => $data['due_date'] ?? null,
                'status' => 'belum',
                'created_by' => $user->id,
            ]);

            if ($notify) {
                $this->bell->paymentInvoiceCreated($payment, $user);
            }
            $count++;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Tagihan massal berhasil dibuat.',
                'count' => $count,
            ]);
        }

        return back()->with('status', $count.' tagihan berhasil dibuat.');
    }

    public function notifyDueBulk(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->hasAnyRole(['super_admin', 'admin_cabang']), 403);

        $query = Payment::query()
            ->belum()
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', now());

        if ($user->hasRole('admin_cabang')) {
            $cabangId = Cabang::query()->where('user_id', $user->id)->value('id');
            abort_unless($cabangId, 403);
            $query->whereHas('siswa', fn ($q) => $q->where('cabang_id', $cabangId));
        }

        $ids = $query->pluck('id')->all();
        $sent = $this->bell->paymentDueReminderBulk($ids, $user);

        return back()->with('status', 'Notifikasi jatuh tempo terkirim ke '.$sent.' siswa (in-app). Integrasi WA dapat ditambahkan pada hook berikutnya.');
    }

    public function markLunas(Request $request, Payment $payment): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->hasAnyRole(['super_admin', 'admin_cabang']), 403);
        $this->assertCanManagePayment($user, $payment);

        abort_if($payment->isLunas(), 422);

        $payment->forceFill([
            'status' => 'lunas',
            'paid_at' => now(),
            'midtrans_transaction_status' => $payment->midtrans_transaction_status ?? 'manual',
        ])->save();

        $this->bell->paymentSettled($payment, $user);

        return back()->with('status', 'Tagihan ditandai lunas dan siswa menerima notifikasi.');
    }

    private function assertCanManagePayment(User $user, Payment $payment): void
    {
        if ($user->hasRole('super_admin')) {
            return;
        }

        $cabangId = Cabang::query()->where('user_id', $user->id)->value('id');
        abort_unless($cabangId, 403);
        $payment->loadMissing('siswa');
        abort_unless((int) $payment->siswa?->cabang_id === (int) $cabangId, 403);
    }
}
