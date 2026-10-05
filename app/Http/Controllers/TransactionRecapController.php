<?php

namespace App\Http\Controllers;

use App\Services\BsUsageCheckService;
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

    public function show(Request $request, string $jenis, int $source, BsUsageCheckService $service, BsUsageDashboardService $dashboard)
    {
        $transaction = $service->source($jenis, $source);
        abort_unless($request->user()->can('akses.approval-finance') || $request->user()->canSeeAllTransactions()
            || (int) $transaction->user_id === (int) $request->user()->id, 403);
        $transaction->load('user');
        if (BsUsageCheckService::isEvent($transaction)) {
            $transaction->load('jamKerja');
            if ($jenis === 'ELK') $transaction->load('wilayah');
        }
        $items = $service->snapshot($transaction)['items'];

        $financeResult = $dashboard->sourceResult($request->user(), $jenis, $source);
        $financeHistory = $transaction->financeChecks()->withSum('details as total_asal', 'nominal_asal')
            ->withSum('details as total_disetujui', 'nominal_disetujui')->latest('id')->paginate(10, ['*'], 'riwayat_page')->withQueryString();
        $latestDecision = $transaction->financeChecks()->latest('id')->first();
        $editUrl = $this->editUrl($jenis, $transaction);
        $canCorrect = (int) $transaction->user_id === (int) $request->user()->id
            && $request->user()->can($this->modulePermission($jenis));
        $periodClosed = $canCorrect && !app(PeriodService::class)->isOpen($transaction->periode);

        return view('transaction-recap.show', compact('transaction', 'items', 'jenis', 'financeResult',
            'financeHistory', 'latestDecision', 'editUrl', 'canCorrect', 'periodClosed'));
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
        DB::transaction(function () use ($request, $jenis, $source, $service) {
            $transaction = $service->source($jenis, $source, true);
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
