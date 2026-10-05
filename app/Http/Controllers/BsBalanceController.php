<?php

namespace App\Http\Controllers;

use App\Models\Periode;
use App\Models\TransaksiOperasionalHeader;
use App\Models\TransaksiPerjalananDinasHeader;
use App\Services\BsCashFlowService;
use App\Services\PeriodService;
use App\Services\TransactionFundingService;
use App\Support\Money;
use Illuminate\Http\Request;

class BsBalanceController extends Controller
{
    public function balances(Request $request, TransactionFundingService $funding, BsCashFlowService $cash, PeriodService $periods)
    {
        $data = $request->validate([
            'module' => 'required|in:operasional_sales,perjalanan_dinas',
            'periode' => 'required|string', 'transaction_id' => 'nullable|integer',
        ]);
        $travel = $data['module'] === 'perjalanan_dinas';
        abort_unless($request->user()->can($travel ? 'akses.transaksi-perjalanan-dinas' : 'akses.transaksi-operasional'), 403);
        $inputter = (int) $request->user()->id;
        if (!empty($data['transaction_id'])) {
            $model = $travel ? TransaksiPerjalananDinasHeader::class : TransaksiOperasionalHeader::class;
            $document = $model::findOrFail($data['transaction_id']);
            abort_unless($request->user()->canAccessTransaction($document), 403);
            $inputter = (int) $document->user_id;
        }
        $period = $periods->normalize($data['periode']);
        return response()->json(['owners' => $funding->owners($inputter, $travel)->map(function ($owner) use ($cash, $period) {
            $balance = $cash->calculate((int) $owner->id, $period);
            return ['id' => $owner->id, 'name' => $owner->nama, 'balance' => $cash->availableBalance($balance), 'opening_final' => $balance['opening_final']];
        })]);
    }

    public function show(Request $request, BsCashFlowService $cash, PeriodService $periods)
    {
        $request->validate(['periode' => 'sometimes|required|string']);
        $periode = $periods->normalize($request->query('periode', $periods->current()));
        $owner = (int) $request->user()->id;
        $snapshot = $cash->recalculateSnapshot($owner, $periode);
        $available = $cash->availableBalance($snapshot->toArray());
        $periodOptions = Periode::orderByDesc('periode')->pluck('periode')->push($periods->current())->push($periode)->unique()->sortDesc();
        $details = $cash->details($owner, $periode);
        $rows = collect();
        foreach ($details['masuk'] as $entry) {
            $rows->push($entry + ['incoming' => $entry['nominal'], 'outgoing' => '0.00', 'order' => 0]);
        }
        foreach ($details['keluar'] as $entry) {
            $rows->push($entry + ['incoming' => '0.00', 'outgoing' => $entry['nominal'], 'order' => 1]);
        }
        foreach ($details['penyesuaian'] as $entry) {
            $amount = Money::minor($entry['nominal']);
            $rows->push($entry + ['type' => 'Penyesuaian BS historis',
                'incoming' => Money::decimal(max(0, $amount)), 'outgoing' => Money::decimal(max(0, -$amount)), 'order' => 2]);
        }
        $balance = Money::minor($snapshot->saldo_awal);
        $rows = $rows->map(function ($entry) use ($periode) {
            $entry['date'] = empty($entry['date']) ? null : \Carbon\Carbon::parse($entry['date'])->toDateString();
            return $entry + ['sort_date' => $entry['date'] ?? $periode.'-01'];
        })->sortBy([['sort_date', 'asc'], ['order', 'asc'], ['description', 'asc']])->map(function ($entry) use (&$balance) {
            $balance += Money::minor($entry['incoming']) - Money::minor($entry['outgoing']);
            return $entry + ['balance' => Money::decimal($balance)];
        });
        return view('bs-balance.show', compact('periode', 'snapshot', 'available', 'periodOptions', 'rows'));
    }
}
