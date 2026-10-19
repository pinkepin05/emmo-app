<?php

namespace App\Http\Controllers;

use App\Models\TransaksiOperasionalHeader;
use App\Models\TransaksiPerjalananDinasHeader;
use App\Services\BsCashFlowService;
use App\Services\PeriodService;
use App\Services\TransactionFundingService;
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
}
