<?php

namespace App\Http\Controllers;

use App\Services\BsFundingRecapService;
use App\Services\BsUsageDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BsFundingRecapController extends Controller
{
    public function index(Request $request, BsFundingRecapService $funding, BsUsageDashboardService $usage)
    {
        $request->validate([
    'tahap' => 'nullable|in:diajukan,disetujui,dicairkan,pending,ditolak',
]);

$stage = $request->query('tahap') ?: 'diajukan';

// Kompatibilitas tautan Total Disetujui yang lama.
if ($stage === 'disetujui') {
    $stage = 'dicairkan';
}

$filters = array_intersect_key(
    $usage->filters($request),
    array_flip(['pemakaian_periode', 'pemakaian_referensi'])
);

$query = $funding->stageQuery($request->user(), $filters, $stage);

$total = DB::query()
    ->fromSub((clone $query)->toBase(), 'rekap')
    ->sum('nominal_rekap');

$rows = $query->with('user')
    ->orderByDesc('tanggal')
    ->orderByDesc('id')
    ->paginate(15)
    ->appends(['tahap' => $stage] + $filters);

// Semua histori sekarang memakai satu baris per pengajuan BS.
$isPayment = false;

return view('transaction-recap.funding-history', compact(
    'stage', 'filters', 'isPayment', 'total', 'rows'
));

        return view('transaction-recap.funding-history', compact('stage', 'filters', 'isPayment', 'total', 'rows'));
    }
}
