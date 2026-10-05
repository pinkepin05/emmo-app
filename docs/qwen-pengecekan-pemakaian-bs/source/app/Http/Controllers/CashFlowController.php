<?php

namespace App\Http\Controllers;

use App\Exports\CashFlowExport;
use App\Models\StockCashFlowSnapshot;
use App\Models\Periode;
use App\Services\StockCashFlowService;
use App\Services\PeriodService;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class CashFlowController extends Controller
{
    public function __construct(private StockCashFlowService $cashFlowService, private PeriodService $periods) {}

    public function index()
    {
        $userId = (int) auth()->id();
        $currentPeriod = $this->periods->current();
        $snapshots = $this->snapshots($userId, $currentPeriod);
        $currentSnapshot = $snapshots->firstWhere('periode', $currentPeriod);
        return view('cash-flow.index', compact('snapshots', 'currentSnapshot', 'currentPeriod'));
    }

    public function show(string $periode)
    {
        $periode = $this->periods->normalize($periode);
        $userId = (int) auth()->id();
        $snapshot = $this->cashFlowService->recalculateSnapshot($userId, $periode);
        $status = Periode::where('periode', $periode)->value('status') ?? 'OPEN';
        $transactions = $this->cashFlowService->details($userId, $periode);

        /* ==========================================================
           PAGINATION SERVER-SIDE untuk Uang Masuk & Uang Keluar
        ========================================================== */
        $perPage = 10;

        // 1) Sortir tanggal terbaru agar halaman 1 = transaksi teranyar
        $masukAll  = collect($transactions['masuk'])->sortByDesc('date')->values();
        $keluarAll = collect($transactions['keluar'])->sortByDesc('date')->values();

        // 2) Total GRAND dari SEMUA data (bukan hanya isi halaman aktif)
        $totalMasuk       = $masukAll->sum('nominal');
        $totalKeluar      = $keluarAll->sum('nominal');
        $totalPenyesuaian = collect($transactions['penyesuaian'])->sum('nominal');

        // 3) Baca nomor halaman aktif dari query string
        $masukPage  = LengthAwarePaginator::resolveCurrentPage('masuk_page');
        $keluarPage = LengthAwarePaginator::resolveCurrentPage('keluar_page');

        // 4) Potong data per halaman, bungkus jadi paginator independen
        $pagedMasuk = new LengthAwarePaginator(
            $masukAll->forPage($masukPage, $perPage)->values(),
            $masukAll->count(),
            $perPage,
            $masukPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'pageName' => 'masuk_page']
        );

        $pagedKeluar = new LengthAwarePaginator(
            $keluarAll->forPage($keluarPage, $perPage)->values(),
            $keluarAll->count(),
            $perPage,
            $keluarPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'pageName' => 'keluar_page']
        );

        return view('cash-flow.show', compact(
            'snapshot', 'status', 'transactions', 'periode',
            'pagedMasuk', 'pagedKeluar',
            'totalMasuk', 'totalKeluar', 'totalPenyesuaian'
        ));
    }

    public function export()
    {
        $snapshots = $this->snapshots((int) auth()->id(), $this->periods->current());
        $statuses = Periode::whereIn('periode', $snapshots->pluck('periode'))->pluck('status', 'periode')->all();

        return Excel::download(new CashFlowExport($snapshots, $statuses), 'stok-kas-semua-periode.xlsx');
    }

    public function exportPeriod(string $periode)
    {
        $periode = $this->periods->normalize($periode);
        $userId = (int) auth()->id();
        $snapshot = $this->cashFlowService->recalculateSnapshot($userId, $periode);
        $status = Periode::where('periode', $periode)->value('status') ?? 'OPEN';
        $transactions = $this->cashFlowService->details($userId, $periode);

        return Excel::download(
            new CashFlowExport(collect([$snapshot]), [$periode => $status], $transactions),
            'stok-kas-'.$this->periods->key($periode).'.xlsx'
        );
    }

    private function snapshots(int $userId, string $currentPeriod)
    {
        $periods = Periode::orderBy('periode')->pluck('periode')->push($currentPeriod)->unique()->sort();
        foreach ($periods as $period) $this->cashFlowService->recalculateSnapshot($userId, $period);

        return StockCashFlowSnapshot::where('user_id', $userId)->orderByDesc('periode')->get();
    }

   

public function transactionsPartial(Request $request, string $periode)
{
    $periode = $this->periods->normalize($periode);
    $userId = (int) auth()->id();
    $transactions = $this->cashFlowService->details($userId, $periode);

    $section = $request->input('section', 'masuk'); // 'masuk' atau 'keluar'
    $perPage = 10;

    // Ambil data sesuai section yang diminta
    $allItems = collect($transactions[$section] ?? [])
        ->sortByDesc('date')
        ->values();

    $total = $allItems->sum('nominal');

    // Baca halaman dari query string section-nya
    $pageName = $section . '_page';
    $page = LengthAwarePaginator::resolveCurrentPage($pageName);

    $paginator = new LengthAwarePaginator(
        $allItems->forPage($page, $perPage)->values(),
        $allItems->count(),
        $perPage,
        $page,
        [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]
    );

    // Ambil halaman section LAIN untuk appends
    $otherPageName = $section === 'masuk' ? 'keluar_page' : 'masuk_page';
    $otherPage = LengthAwarePaginator::resolveCurrentPage($otherPageName);

    $totalLabel = $section === 'masuk' ? $total : -$total; // tanda minus untuk keluar
    $sectionId  = 'sec-' . $section;

    return view('cash-flow.partials.transaction-section', [
        'section'      => $section,
        'paginator'    => $paginator,
        'total'        => $total,
        'totalLabel'   => $totalLabel,
        'sectionId'    => $sectionId,
        'otherPage'    => $otherPage,
        'otherSection' => $section === 'masuk' ? 'keluar' : 'masuk',
    ]);
}
}