<?php

namespace App\Http\Controllers;

use App\Services\BsUsageCheckService;
use App\Services\BsUsageCheckFormService;
use App\Services\BsUsageDashboardService;
use App\Services\BsValidationService;
use App\Services\PeriodService;
use App\Support\AppTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionRecapController extends Controller
{
    public function index(Request $request, BsUsageDashboardService $dashboard)
    {
        $filters = $dashboard->filters($request);
        $type = $filters['pemakaian_jenis'] ?? null;
        unset($filters['pemakaian_jenis']);
        if ($type) {
            return redirect()->route('transaction-recap.module', ['jenis' => $type] + $filters);
        }
        unset($filters['pemakaian_page']);

        return redirect()->to(route('dashboard.transaksi', $filters).'#pemakaian-bs');
    }

    public function module(Request $request, string $jenis, BsUsageDashboardService $dashboard)
    {
        // The route fixes the module; query parameters cannot switch the list.
        $request->query->set('pemakaian_jenis', $jenis);

        return view('transaction-recap.index', $dashboard->data($request) + ['moduleType' => $jenis]);
    }

    public function show(Request $request, string $jenis, int $source, BsUsageCheckService $service, BsUsageDashboardService $dashboard, BsUsageCheckFormService $form)
{
    $transaction = $service->source($jenis, $source);
    abort_unless($request->user()->can('akses.approval-finance') || $request->user()->canSeeAllTransactions() || (int) $transaction->user_id === (int) $request->user()->id, 403);
    $dashboard->filters($request);

    $validated = $request->validate([
        'pemeriksaan' => 'sometimes|boolean',
        'hasil' => 'nullable|integer|min:1',
        'dari' => 'nullable|in:dashboard,laporan,pengecekan,periode,monitoring',
        'pemakaian_periode' => ['nullable', 'string', 'regex:/^$|^\d{4}-?(0[1-9]|1[0-2])$/D'],
        'company' => ['nullable', 'integer', 'min:1', 'required_if:dari,monitoring'],
        'user' => ['nullable', 'integer', 'min:1', 'required_if:dari,monitoring'],
        'page' => 'nullable|integer|min:1',
        'jenis_filter' => 'nullable|in:OPR,PD,EDK,ELK',
    ]);

    $transaction->load('user');
    $monitoringContext = null;

    if (($validated['dari'] ?? null) === 'monitoring') {
        $companyId = (int) $validated['company'];
        $userId = (int) $validated['user'];
        abort_unless((int) $transaction->user_id === $userId, 403, 'Konteks monitoring tidak sesuai dengan user transaksi.');
        abort_unless($transaction->user !== null && (int) $transaction->user->company_id === $companyId, 403, 'Konteks monitoring tidak sesuai dengan company user transaksi.');

        $periode = !empty($validated['pemakaian_periode']) ? app(PeriodService::class)->normalize($validated['pemakaian_periode']) : null;
        $monitoringContext = [
            'company' => $companyId, 'user' => $userId, 'periode' => $periode,
            'page' => (int) ($validated['page'] ?? 1), 'jenis_filter' => $validated['jenis_filter'] ?? null,
        ];
    }

    $fromMonitoring = $monitoringContext !== null;
    $reviewMode = $request->boolean('pemeriksaan');
    $isFinance = $request->user()->can('akses.approval-finance');
    abort_if($reviewMode && !$isFinance, 403);

    if (BsUsageCheckService::isEvent($transaction)) {
        $transaction->load('jamKerja');
        if ($jenis === 'ELK') $transaction->load('wilayah.parent');
    } else {
        $transaction->load(['bsOwner', 'bonSementara.user']);
        if ($jenis === 'PD') $transaction->load('tujuanDinas.parent');
    }

    $items = $service->snapshot($transaction)['items'];
    $financeResult = $dashboard->sourceResult($request->user(), $jenis, $source);
    $financeHistory = $transaction->financeChecks()->with('details')->orderBy('id')->paginate(10, ['*'], 'riwayat_page')->withQueryString()->fragment('decision-history-title');
    $latestDecision = $transaction->financeChecks()->latest('id')->first();
    $editUrl = $this->editUrl($jenis, $transaction);
    $canCorrect = (int) $transaction->user_id === (int) $request->user()->id && $request->user()->can($this->modulePermission($jenis));
    $accountingPeriod = app(\App\Services\FinanceBsExpenseService::class)->period($transaction);
    $periodClosed = !app(PeriodService::class)->isOpen($accountingPeriod);
    $closingExclusion = $periodClosed ? \App\Models\PeriodClosingLog::activeExclusions($accountingPeriod)->first(fn ($item) => $item['type'] === $jenis && (int) $item['id'] === $source) : null;
    $canReview = $isFinance && !$periodClosed && $transaction->status === 'VALIDATED' && !BsUsageCheckService::awaitingResubmission($transaction);
    $formData = $reviewMode && $canReview ? $form->data($transaction, $jenis) : null;
    $selectedDecision = $request->filled('hasil') ? $transaction->financeChecks()->with('details')->findOrFail($request->integer('hasil')) : $latestDecision?->load('details');
    $outdatedResult = $selectedDecision && ($financeResult?->needs_recheck || $transaction->status !== 'VALIDATED');
    $displayDecision = $request->filled('hasil') || !$outdatedResult ? $selectedDecision : null;
    $historicalItems = $displayDecision && ($displayDecision->id !== $latestDecision?->id || $outdatedResult);
    $costDetails = $reviewMode ? null : app(\App\Services\TransactionRecapItemsService::class)->build(
        $transaction, $items, $displayDecision, $isFinance, !$historicalItems
    );

    return view('transaction-recap.show', compact('transaction', 'items', 'jenis', 'financeResult', 'financeHistory', 'latestDecision', 'editUrl', 'canCorrect', 'periodClosed', 'reviewMode', 'isFinance', 'canReview', 'formData', 'selectedDecision', 'closingExclusion', 'fromMonitoring', 'monitoringContext', 'costDetails', 'displayDecision', 'historicalItems', 'outdatedResult'));
}

    public function correct(Request $request, string $jenis, int $source, BsUsageCheckService $service, BsValidationService $validation)
    {
        abort_unless(in_array($jenis, ['OPR', 'PD'], true), 404);
        abort_unless($request->user()->can($this->modulePermission($jenis)), 403);
        $data = $request->validate(['check_id' => 'required|integer|min:1']);
        $transaction = $service->source($jenis, $source);
        abort_unless((int) $transaction->user_id === (int) $request->user()->id, 403);
        $validation->reopenAfterFinanceRejection($transaction, (int) $data['check_id']);

        return redirect()->to($this->editUrl($jenis, $transaction))
            ->with('success', 'Pengajuan dibuka sebagai draft perbaikan. Simpan perubahan lalu konfirmasikan kembali. Keputusan Finance sebelumnya tetap tersimpan.');
    }

    private function modulePermission(string $type): string
    {
        return match ($type) {
            'OPR' => 'akses.transaksi-operasional', 'PD' => 'akses.transaksi-perjalanan-dinas',
            'EDK' => 'akses.event-dalam-kota', 'ELK' => 'akses.event-luar-kota',
        };
    }

    private function editUrl(string $type, $source): string
    {
        return match ($type) {
            'OPR' => route('transaksi_operasional.show', $source->id).'?edit=1',
            'PD' => route('transaksi_perjalanan_dinas.show', $source->transaksi_id).'?edit=1',
            'EDK' => route('perongkosan_event_dalam_kota.edit', $source->id),
            'ELK' => route('perongkosan_event_luar_kota.edit', $source->id),
        };
    }

    public function confirmEvent(Request $request, string $jenis, int $source, BsUsageCheckService $service)
    {
        abort_unless(in_array($jenis, ['EDK', 'ELK'], true), 404);
        abort_unless($request->user()->can($jenis === 'EDK' ? 'akses.event-dalam-kota' : 'akses.event-luar-kota'), 403);
        $reference = $service->source($jenis, $source);
        $period = app(PeriodService::class)->ofDate($reference->tanggal);
        DB::transaction(function () use ($request, $jenis, $source, $service, $reference, $period) {
            app(PeriodService::class)->lockOpen($period);
            DB::table('user')->where('id', $reference->user_id)->lockForUpdate()->first();
            $transaction = $service->source($jenis, $source, true);
            if ($transaction->tanggal !== $reference->tanggal || (int) $transaction->user_id !== (int) $reference->user_id) {
                throw \Illuminate\Validation\ValidationException::withMessages(['source_token' => 'Event berubah. Muat ulang sebelum konfirmasi.']);
            }
            abort_unless((int) $transaction->user_id === (int) $request->user()->id, 403);
            if ($transaction->status === 'VALIDATED') return;
            $request->validate(['source_token' => 'required|string']);
            $snapshot = $service->snapshot($transaction);
            try {
                $token = json_decode(\Illuminate\Support\Facades\Crypt::decryptString($request->source_token), true, flags: JSON_THROW_ON_ERROR);
            } catch (\Illuminate\Contracts\Encryption\DecryptException|\JsonException $exception) {
                $token = null;
            }
            if ($token !== [$jenis, $source, $snapshot['fingerprint']]) {
                throw \Illuminate\Validation\ValidationException::withMessages(['source_token' => 'Event berubah. Muat ulang dan periksa rincian sebelum konfirmasi.']);
            }
            $transaction->forceFill(['status' => 'VALIDATED', 'validated_at' => AppTime::now(),
                'validated_by' => $request->user()->id, 'submission_version' => $transaction->submission_version + 1])->save();
        });

        return back()->with('success', 'Pengajuan Event dikonfirmasi dan siap diperiksa Finance.');
    }
}
