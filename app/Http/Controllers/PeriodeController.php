<?php

namespace App\Http\Controllers;

use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeriodeController extends Controller
{
    public function index()
    {
        $periods = Periode::orderBy('periode', 'desc')->get();
        $currentMonth = \App\Support\AppTime::now()->format('Y-m'); // 🎯 Untuk Guard UI di View

        return view('periode.index', compact('periods', 'currentMonth'));
    }

    /** 🎯 RIWAYAT: semua transaksi dalam satu periode */
    public function show(string $periode)
    {
        $periode = app(\App\Services\PeriodService::class)->normalize($periode);

        $row    = Periode::where('periode', $periode)->first();
        $status = $row?->status ?? ($periode < \App\Support\AppTime::now()->format('Y-m') ? 'CLOSED' : 'OPEN');

        // Audit: siapa yang menutup
        $closedByName = $row?->closed_by
            ? DB::table('user')->where('id', $row->closed_by)->value('nama')
            : null;

        // OPR/PD follow their saved accounting period, just like period closing.
        // Event activity continues to follow the transaction date.
        $list = function (string $table) use ($periode) {
            $query = DB::table($table . ' as h')
                ->leftJoin('user as u', 'u.id', '=', 'h.user_id')
                ->orderBy('h.tanggal', 'desc')
                ->select(['h.*', 'u.nama as user_nama']);
            if (in_array($table, ['transaksi_operasional_area_sales_header', 'transaksi_perjalanan_dinas_header'], true)) {
                $query->where('h.periode', $periode);
            } else {
                $query->where('h.tanggal', '>=', $periode.'-01')
                    ->where('h.tanggal', '<', app(\App\Services\PeriodService::class)->next($periode).'-01');
            }

            return $query->get();
        };

        $bs          = $list('pengajuan_bs'); // ⚠️ sesuaikan nama tabel BS jika berbeda
        $operasional = $list('transaksi_operasional_area_sales_header');
        $perdin      = $list('transaksi_perjalanan_dinas_header');
        $eventDalam  = $list('perongkosan_event_dalam_kota');
        $eventLuar   = $list('perongkosan_event_luar_kota');

        // Total nominal (header join detail untuk OPS & PERDIN)
        $sumDetail = function (string $head, string $detail) use ($periode) {
            return DB::table($head . ' as h')
                ->join($detail . ' as d', 'd.transaksi_id', '=', 'h.transaksi_id')
                ->where('h.periode', $periode)
                ->where('h.status', 'VALIDATED')
                ->when($detail === 'transaksi_perjalanan_dinas_detail', fn ($q) => $q->whereNull('d.deleted_at'))
                ->sum('d.nominal');
        };

        $totals = [
            'bsApproved'  => \App\Models\ApprovalBsFinance::where('tanggal_cair', '>=', $periode.'-01')->where('tanggal_cair', '<', app(\App\Services\PeriodService::class)->next($periode).'-01')->sum('nominal_approved'),
            'operasional' => $sumDetail('transaksi_operasional_area_sales_header', 'transaksi_operasional_area_sales_detail'),
            'perdin'      => $sumDetail('transaksi_perjalanan_dinas_header', 'transaksi_perjalanan_dinas_detail'),
            'eventDalam'  => $eventDalam->sum('nominal'),
            'eventLuar'   => $eventLuar->sum('nominal'),
        ];

        $review = app(\App\Services\PeriodClosingReviewService::class);
        $closingBlockers = $status === 'OPEN' ? $review->blockers($periode) : collect();
        $closingReviewToken = $review->token($periode, $closingBlockers);
        $closingHistory = \App\Models\PeriodClosingLog::where('periode', $periode)->latest('id')->get();
        $canAttemptClose = $row && $status === 'OPEN' && $periode < app(\App\Services\PeriodService::class)->current();

        return view('periode.show', compact(
            'periode', 'status', 'row', 'closedByName',
            'bs', 'operasional', 'perdin', 'eventDalam', 'eventLuar',
            'totals', 'closingBlockers', 'canAttemptClose', 'closingReviewToken', 'closingHistory'
        ));
    }

    public function close(Request $request, Periode $periode)
    {
        $data = $request->validate([
            'exclude_pending' => 'sometimes|boolean',
            'reason' => 'nullable|required_if:exclude_pending,1|string|min:5|max:1000',
            'confirm_exclusions' => 'exclude_unless:exclude_pending,1|required|accepted',
            'review_token' => 'nullable|required_if:exclude_pending,1|string|size:64',
        ]);
        $excludePending = $request->boolean('exclude_pending');
        app(\App\Services\BsPeriodClosingService::class)->close(
            $periode->id, $excludePending, $data['reason'] ?? null, $data['review_token'] ?? null
        );
        $message = $excludePending
            ? "Periode {$periode->periode} ditutup. Transaksi tertunda dikecualikan dari pengeluaran dan dikunci; rinciannya tersimpan dalam riwayat penutupan. Saldo diteruskan ke periode berikutnya."
            : "Periode {$periode->periode} berhasil ditutup dan saldo diteruskan ke periode berikutnya.";

        return redirect()->route('periode.show', $periode->periode)->with('success', $message);
    }

    public function reopen(Request $request, Periode $periode)
    {
        $request->validate(['reason' => 'required|string|min:5|max:255']);
        app(\App\Services\BsPeriodClosingService::class)->reopen($periode->id, $request->reason);
        return back()->with('success', "Periode {$periode->periode} dibuka kembali.");
    }
}
