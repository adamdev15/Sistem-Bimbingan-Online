<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Exports\PembayaranOutstandingExport;
use App\Exports\PembayaranRingkasanExport;
use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Payment;
use App\Models\Siswa;
use App\Models\User;
use App\Services\Midtrans\MidtransService;
use App\Services\Notifications\InAppBellNotifier;
use App\Services\PembayaranReportService;
use App\Services\SuperAdmin\ManagementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PembayaranController extends Controller
{
    public function __construct(
        private readonly ManagementService $service,
        private readonly PembayaranReportService $reports,
        private readonly MidtransService $midtrans,
        private readonly InAppBellNotifier $bell,
        private readonly \App\Services\WhatsApp\WhatsAppNotifier $whatsapp,
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

        $outstandingPaginator = null;
        if ($user && $user->hasAnyRole(['super_admin', 'admin_cabang'])) {
            $outstandingPaginator = $this->reports->outstandingPaginator($request);
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
            'outstandingPaginator' => $outstandingPaginator,
            'siswaHasManualPaymentDestinations' => $user && $user->hasRole('siswa')
                ? $this->siswaHasConfiguredManualDestinations()
                : false,
            'unpaidPayments' => $user && $user->hasAnyRole(['super_admin', 'admin_cabang']) 
                ? $this->getUnpaidPaymentsForReminder($user) 
                : collect(),
        ]);
    }

    public function manualPayment(Request $request, Payment $payment): View
    {
        $this->assertSiswaOwnsPayment($request, $payment);
        abort_if($payment->isLunas(), 403, 'Tagihan sudah lunas.');

        $payment->load(['fee', 'siswa.cabang']);

        $banks = collect(config('payment_manual.banks', []))
            ->filter(fn (array $b) => filled($b['account_number'] ?? null))
            ->all();

        $ewallets = collect(config('payment_manual.ewallets', []))
            ->filter(fn (array $e) => filled($e['account_id'] ?? null))
            ->all();

        $qrisRelative = config('payment_manual.qris.public_path', 'images/payment/qris.svg');
        $qrisPath = public_path($qrisRelative);
        $qrisUrl = is_file($qrisPath) ? asset($qrisRelative) : null;

        return view('modules.pembayaran.manual-siswa', [
            'payment' => $payment,
            'manualBanks' => $banks,
            'manualEwallets' => $ewallets,
            'qrisUrl' => $qrisUrl,
            'hasAnyManualDestination' => count($banks) > 0 || count($ewallets) > 0 || $qrisUrl !== null,
        ]);
    }

    public function exportRingkasanPdf(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user && $user->hasAnyRole(['super_admin', 'admin_cabang']), 403);

        $payload = $this->reports->ringkasanPayload($request);
        $name = 'pembayaran-ringkasan-'.now()->format('Y-m-d-His').'.pdf';

        return Pdf::loadView('exports.pembayaran-ringkasan-pdf', $payload)
            ->setPaper('a4', 'landscape')
            ->download($name);
    }

    public function exportRingkasanExcel(Request $request): BinaryFileResponse
    {
        $user = $request->user();
        abort_unless($user && $user->hasAnyRole(['super_admin', 'admin_cabang']), 403);

        $payload = $this->reports->ringkasanPayload($request);
        $name = 'pembayaran-ringkasan-'.now()->format('Y-m-d-His').'.xlsx';

        return Excel::download(new PembayaranRingkasanExport($payload), $name);
    }

    public function exportOutstandingPdf(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user && $user->hasAnyRole(['super_admin', 'admin_cabang']), 403);

        $payload = $this->reports->outstandingPayloadForPdf($request);
        $name = 'pembayaran-outstanding-'.now()->format('Y-m-d-His').'.pdf';

        return Pdf::loadView('exports.pembayaran-outstanding-pdf', $payload)
            ->setPaper('a4', 'landscape')
            ->download($name);
    }

    public function exportOutstandingExcel(Request $request): BinaryFileResponse
    {
        $user = $request->user();
        abort_unless($user && $user->hasAnyRole(['super_admin', 'admin_cabang']), 403);

        $rows = $this->reports->outstandingForExport($request)->map(function (Payment $p) {
            $m = $this->reports->agingMeta($p);

            return [
                'payment' => $p,
                'aging_hari' => $m['hari_telat'],
                'aging_label' => $m['label'],
            ];
        });

        $name = 'pembayaran-outstanding-'.now()->format('Y-m-d-His').'.xlsx';

        return Excel::download(new PembayaranOutstandingExport($rows), $name);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $query = $this->service->pembayaranFilteredQuery($request);
        $name = 'pembayaran-export-' . now()->format('Y-m-d-His') . '.xlsx';
        
        return Excel::download(new \App\Exports\PembayaranExport($query), $name);
    }

    public function snapToken(Request $request, Payment $payment): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->hasRole('siswa')) {
            return response()->json(['message' => 'Hanya akun siswa yang dapat membayar tagihan ini.'], 403);
        }

        $siswaId = $this->siswaIdForAuthenticatedStudent($request);

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

        $siswaId = $this->siswaIdForAuthenticatedStudent($request);

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
            $fee = \App\Models\Fee::find($data['biaya_id']);
            $orderId = Str::upper(Str::slug($fee->nama_biaya)) . '-' . random_int(100000000000000, 999999999999999);

            $payment = Payment::query()->create([
                'order_id' => $orderId,
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
                $this->whatsapp->notifySiswaInvoiceCreated($payment);
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

        $data = $request->validate([
            'payment_ids' => ['required', 'array', 'min:1'],
            'payment_ids.*' => ['required', 'exists:payments,id'],
        ]);

        $query = Payment::query()
            ->belum()
            ->whereIn('id', $data['payment_ids']);

        if ($user->hasRole('admin_cabang')) {
            $cabangId = Cabang::query()->where('user_id', $user->id)->value('id');
            abort_unless($cabangId, 403);
            $query->whereHas('siswa', fn ($q) => $q->where('cabang_id', $cabangId));
        }

        $payments = $query->get();
        $sentCount = 0;

        foreach ($payments as $payment) {
            $this->whatsapp->notifySiswaPaymentDueTomorrow($payment);
            $sentCount++;
        }

        return back()->with('status', 'Notifikasi pengingat pembayaran telah dikirim ke ' . $sentCount . ' siswa.');
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
        $this->whatsapp->notifySiswaPaymentSuccess($payment);

        return back()->with('status', 'Tagihan ditandai lunas dan siswa menerima notifikasi WhatsApp.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user && $user->hasAnyRole(['super_admin', 'admin_cabang']), 403);
        $this->assertCanManagePayment($user, $payment);

        $payment->delete();

        return back()->with('status', 'Data pembayaran berhasil dihapus.');
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

    private function siswaIdForAuthenticatedStudent(Request $request): ?int
    {
        $user = $request->user();
        if (! $user || ! $user->hasRole('siswa')) {
            return null;
        }

        $id = Siswa::query()->where('user_id', $user->id)->value('id')
            ?? Siswa::query()->where('email', $user->email)->value('id');

        return $id !== null ? (int) $id : null;
    }

    private function assertSiswaOwnsPayment(Request $request, Payment $payment): void
    {
        $siswaId = $this->siswaIdForAuthenticatedStudent($request);
        abort_unless($siswaId !== null && (int) $payment->student_id === $siswaId, 403, 'Tagihan ini bukan milik Anda.');
    }

    private function siswaHasConfiguredManualDestinations(): bool
    {
        $banks = collect(config('payment_manual.banks', []))
            ->contains(fn (array $b) => filled($b['account_number'] ?? null));
        $ewallets = collect(config('payment_manual.ewallets', []))
            ->contains(fn (array $e) => filled($e['account_id'] ?? null));
        $qrisRelative = config('payment_manual.qris.public_path', 'images/payment/qris.svg');
        $qrisOk = is_file(public_path($qrisRelative));

        return $banks || $ewallets || $qrisOk;
    }

    private function getUnpaidPaymentsForReminder(User $user): \Illuminate\Support\Collection
    {
        $query = Payment::query()
            ->belum()
            ->with(['siswa:id,nama,cabang_id', 'fee:id,nama_biaya'])
            ->whereNotNull('due_date');

        if ($user->hasRole('admin_cabang')) {
            $cabangId = Cabang::query()->where('user_id', $user->id)->value('id');
            if ($cabangId) {
                $query->whereHas('siswa', fn ($s) => $s->where('cabang_id', $cabangId));
            }
        }

        return $query->latest('due_date')->get();
    }
}
