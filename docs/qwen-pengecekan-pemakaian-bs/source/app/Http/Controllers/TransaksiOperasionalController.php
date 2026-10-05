<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiOperasionalHeader; 
use App\Models\TransaksiOperasionalDetail; 
use App\Models\User;
use App\Models\CostCategory;

use App\Services\BonSementaraService; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use App\Traits\FiltersByPeriod;
use App\Services\PeriodService;
use App\Services\DocumentNumberService;
use Carbon\Carbon;

class TransaksiOperasionalController extends Controller
{
    use FiltersByPeriod;

    public function create()
    {
        $cost_categories = CostCategory::all();
        
        // 🎯 LOGIC: Ambil daftar bulan yang statusnya OPEN
        $dbPeriods = \App\Models\Periode::pluck('status', 'periode')->toArray(); // Sesuaikan 'period' jika nama kolom Anda 'periode'
        $currentMonth = \App\Support\AppTime::now()->format('Y-m');
        
        $openPeriods = [];
        foreach ($dbPeriods as $periode => $status) {
            if ($status === 'OPEN') {
                $openPeriods[] = $periode;
            }
        }
        
        // 🎯 Jika bulan saat ini belum ada record di database, default-nya dianggap OPEN
        if (!isset($dbPeriods[$currentMonth])) {
            $openPeriods[] = $currentMonth;
        }

        return view('transaksi_operasional.create', compact('cost_categories', 'openPeriods'));
    }
public function store(Request $request, BonSementaraService $bsService)
{
    // 1. Validasi Input Dasar
    $request->validate([
        'save_action' => 'sometimes|required|in:draft,confirm',
        'tanggal' => 'required|date',
        'description' => 'nullable|string|max:255',
        ...\App\Services\TransactionFundingService::rules(),
        'details' => 'required|array|min:1',
        'details.*.cost_category_id' => 'required|exists:cost_category,id',
        'details.*.remark' => 'required|string|max:255',
        'details.*.nominal' => 'required|numeric|decimal:0,2|min:0|max:9999999999999.99',
    ]);

    // 2. Validasi Periode (Pastikan periode OPEN)
    app(PeriodService::class)->assertOpen(substr($request->input('tanggal'), 0, 7));

    $activeUserId = Auth::id();

    // 3. 🎯 VALIDASI E2: 1 Transaksi per Tanggal per User (STRICT REJECT)
    $headerAda = TransaksiOperasionalHeader::where('tanggal', $request->tanggal)
                                           ->where('user_id', $activeUserId) 
                                           ->first();

    if ($headerAda) {
        return back()->withErrors([
            'tanggal' => 'Anda sudah memiliki transaksi Operasional Area Sales pada tanggal ' . $request->tanggal . '. Silakan buka transaksi yang sudah ada (ID: ' . $headerAda->transaksi_id . ') untuk menambahkan detail biaya.'
        ])->withInput();
    }
// 4. 🎯 VALIDASI BS (BON SEMENTARA)
$bsOwnerId = app(\App\Services\TransactionFundingService::class)->resolve($request, false);

    // 🎯 CATATAN: Validasi saldo BS TIDAK dilakukan saat draft
    // Saldo diperiksa saat konfirmasi, termasuk Simpan & Konfirmasi.

    try {
        DB::beginTransaction();

        $transaksi_id = app(DocumentNumberService::class)->next('OAS', $request->tanggal);
        
        $header = TransaksiOperasionalHeader::create([
            'transaksi_id' => $transaksi_id,
            'tanggal'      => $request->tanggal,
            'user_id'      => $activeUserId,
            'bs_owner_id' => $bsOwnerId,
            'periode'      => substr($request->tanggal, 0, 7), 
            'description'  => $request->description ?: null,
            'status'       => 'DRAFT', // 🎯 SIMPAN SEBAGAI DRAFT
        ]);

        foreach ($request->details as $detail) {
            TransaksiOperasionalDetail::create([
                'transaksi_id'     => $transaksi_id,
                'cost_category_id' => $detail['cost_category_id'],
                'remark'           => $detail['remark'],
                'nominal'          => $detail['nominal'],
            ]);
        }
        
        $confirm = $request->input('save_action', 'draft') === 'confirm';
        if ($confirm) {
            app(\App\Services\BsValidationService::class)->finalize($header);
        }

        DB::commit();
        
        return redirect()->route('transaksi_operasional.index')
            ->with('success', $confirm
                ? 'Transaksi Operasional (' . $transaksi_id . ') berhasil disimpan dan dikonfirmasi. Realisasi BS telah dibukukan.'
                : 'Draft Transaksi Operasional (' . $transaksi_id . ') berhasil disimpan. Klik Konfirmasi untuk membukukan pemakaian BS.');

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return redirect()->back()->withErrors($e->errors())->withInput();
    } catch (\Exception $e) {
        DB::rollback();
        return redirect()->back()->withErrors(['error' => 'Transaksi Gagal: ' . $e->getMessage()])->withInput();
    }
}

   public function show($id)
{
    $transaksi = TransaksiOperasionalHeader::with(['user', 'details', 'bonSementara', 'bsOwner'])->findOrFail($id);

    if (!auth()->user()->canViewBsTransaction($transaksi)) {
        abort(403, 'Anda tidak memiliki akses ke transaksi ini.');
    }

    $cost_categories = CostCategory::all();
    
    // 🎯 TAMBAHKAN INI: Ambil list BS untuk dropdown edit


    return view('transaksi_operasional.show', compact('transaksi', 'cost_categories'));
}


// 🎯 METHOD BARU: UPDATE TRANSAKSI (MODE EDIT)
public function update(Request $request, $id)
{
    $transaksi = TransaksiOperasionalHeader::with('details')->findOrFail($id);

    // 1. Cek Authorization
    if (!auth()->user()->canAccessTransaction($transaksi)) {
        abort(403, 'Anda tidak memiliki akses ke transaksi ini.');
    }

    // 2. Cek Status
    if ($transaksi->status !== 'DRAFT') {
        return back()->withErrors(['error' => 'Hanya transaksi dengan status DRAFT yang dapat diedit.']);
    }

    // 3. Validasi Periode (Pastikan periode lama & periode dari tanggal baru masih OPEN)
    app(PeriodService::class)->assertOpen($transaksi->periode);
    if (substr($request->tanggal, 0, 7) !== $transaksi->periode) {
        app(PeriodService::class)->assertOpen(substr($request->tanggal, 0, 7));
    }

    // 4. Validasi Input
    $request->validate([
        'tanggal' => 'required|date',
        'description' => 'nullable|string|max:255',
        ...\App\Services\TransactionFundingService::rules(),
        'details' => 'nullable|array',
        'details.*.cost_category_id' => 'required_with:details|exists:cost_category,id',
        'details.*.remark' => 'required_with:details|string|max:255',
        'details.*.nominal' => 'required_with:details|numeric|decimal:0,2|min:0|max:9999999999999.99',
        'new_details' => 'nullable|array',
        'new_details.*.cost_category_id' => 'required_with:new_details|exists:cost_category,id',
        'new_details.*.remark' => 'required_with:new_details|string|max:255',
        'new_details.*.nominal' => 'required_with:new_details|numeric|decimal:0,2|min:0|max:9999999999999.99',
    ]);

    // Periksa hak penggunaan saldo sebelum mengubah draft.
    $bsOwnerId = app(\App\Services\TransactionFundingService::class)->resolve($request, false, $transaksi);

    // 6. Cek Duplikasi Tanggal (Kecuali transaksi ini sendiri)
    $headerAda = TransaksiOperasionalHeader::where('tanggal', $request->tanggal)
        ->where('user_id', auth()->id())
        ->where('id', '!=', $transaksi->id)
        ->first();
    if ($headerAda) {
        return back()->withErrors(['tanggal' => 'Anda sudah memiliki transaksi pada tanggal ' . $request->tanggal . ' (ID: ' . $headerAda->transaksi_id . ').'])->withInput();
    }

    DB::beginTransaction();
    try {
        // A. Update Header
        $transaksi->update([
            'tanggal' => $request->tanggal,
            'bs_owner_id' => $bsOwnerId,
            'periode' => substr($request->tanggal, 0, 7),
            'description' => $request->description ?: null,
        ]);

        // B. Update Existing Details
        if ($request->has('details')) {
            foreach ($request->details as $detailId => $data) {
                $detail = TransaksiOperasionalDetail::find($detailId);
                if ($detail && $detail->transaksi_id == $transaksi->transaksi_id) {
                    $detail->update([
                        'cost_category_id' => $data['cost_category_id'],
                        'remark' => $data['remark'],
                        'nominal' => $data['nominal'],
                    ]);
                }
            }
        }

        // C. Hapus Details yang ditandai (dicoret di UI)
        if ($request->has('delete_details') && is_array($request->delete_details)) {
            TransaksiOperasionalDetail::whereIn('id', $request->delete_details)
                ->where('transaksi_id', $transaksi->transaksi_id)
                ->delete();
        }

        // D. Tambah New Details (baris baru yang ditambah saat edit)
        if ($request->has('new_details')) {
            foreach ($request->new_details as $data) {
                TransaksiOperasionalDetail::create([
                    'transaksi_id' => $transaksi->transaksi_id,
                    'cost_category_id' => $data['cost_category_id'],
                    'remark' => $data['remark'],
                    'nominal' => $data['nominal'],
                ]);
            }
        }

        DB::commit();
        // 🎯 Kembali ke halaman detail (Mode View) setelah berhasil
        return redirect()->route('transaksi_operasional.show', $transaksi->id)->with('success', 'Transaksi berhasil diperbarui!');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withErrors(['error' => 'Gagal memperbarui transaksi: ' . $e->getMessage()])->withInput();
    }
}


    /**
 * 🎯 VALIDATE: Mengubah status DRAFT menjadi VALIDATED dan trigger cash flow
 */
    public function validateTransaction($id)
    {
        $transaksi = TransaksiOperasionalHeader::findOrFail($id);
        app(\App\Services\BsValidationService::class)->finalize($transaksi);
        return redirect()->route('transaksi_operasional.show', $transaksi->id)
            ->with('success', 'Transaksi berhasil dikonfirmasi dan saldo BS diperbarui.');
    }



       public function storeDetail(Request $request, $id, BonSementaraService $bsService)
{
    $request->validate([
        'details' => 'required|array|min:1',
        'details.*.cost_category_id' => 'required|exists:cost_category,id',
        'details.*.remark' => 'required|string|max:255',
        'details.*.nominal' => 'required|numeric|decimal:0,2|min:0|max:9999999999999.99',
    ]);

    $header = TransaksiOperasionalHeader::findOrFail($id);

    // 🎯 GUARD: Hanya bisa tambah detail jika masih DRAFT
    if ($header->status !== 'DRAFT') {
        return back()->withErrors(['error' => 'Tidak dapat menambah rincian. Transaksi sudah dikonfirmasi.']);
    }

    // 🎯 VALIDASI PERIODE: Pastikan periode transaksi TIDAK di-close
    app(PeriodService::class)->assertOpen($header->periode);

    try {
        DB::beginTransaction();

        if (!auth()->user()->canAccessTransaction($header)) {
            abort(403, 'Anda tidak memiliki akses ke transaksi ini.');
        }

        // 🎯 CATATAN: Validasi saldo BS TIDAK dilakukan saat tambah detail draft
        // Validasi saldo dilakukan saat VALIDATE

        foreach ($request->details as $detail) {
            TransaksiOperasionalDetail::create([
                'transaksi_id'     => $header->transaksi_id,
                'cost_category_id' => $detail['cost_category_id'],
                'remark'           => $detail['remark'],
                'nominal'          => $detail['nominal'],
            ]);
        }

        DB::commit();
        
        // 🎯 JANGAN TRIGGER CASH FLOW SAAT TAMBAH DETAIL DRAFT!
        
        return redirect()->back()->with('success', 'Rincian biaya berhasil ditambahkan ke Draft Transaksi (' . $header->transaksi_id . '). Klik "Konfirmasi" untuk membukukan pemakaian BS.');

    } catch (\Exception $e) {
        DB::rollback();
        return redirect()->back()->withErrors(['error' => 'Gagal Menambah Rincian: ' . $e->getMessage()]);
    }
}
    public function index(Request $request)
    {
        $periode = $request->query('periode');

        $query = TransaksiOperasionalHeader::with('user')->orderBy('tanggal', 'desc');

        if (!auth()->user()->canSeeAllTransactions()) {
            $query->visibleTo(auth()->user());
        }

        $this->filterPeriod($query, $periode);

        $transaksis = $query->paginate(10)->withQueryString();

        return view('transaksi_operasional.index', compact('transaksis', 'periode'));
    }

   public function destroy($id)
{
    $transaksi = TransaksiOperasionalHeader::with('bonSementara', 'details')->findOrFail($id);

    if (!auth()->user()->canAccessTransaction($transaksi)) {
        abort(403, 'Anda tidak memiliki akses ke transaksi ini.');
    }

    // 🎯 GUARD: Hanya bisa hapus jika masih DRAFT
    if ($transaksi->status !== 'DRAFT') {
        return back()->withErrors(['error' => 'Tidak dapat menghapus transaksi yang sudah dikonfirmasi.']);
    }

    // Simpan data SEBELUM delete
    $periode = $transaksi->periode;

    DB::beginTransaction();

    try {
        $transaksi->details()->delete();
        $transaksi->delete();

        DB::commit();

        // 🎯 JANGAN TRIGGER CASH FLOW SAAT HAPUS DRAFT!
        // Karena draft tidak pernah mempengaruhi cash flow
        
        return redirect()->route('transaksi_operasional.index')
            ->with('success', 'Draft Transaksi Operasional berhasil dihapus!');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()
            ->withErrors(['error' => 'Gagal menghapus transaksi: ' . $e->getMessage()]);
    }
}
    public function export(Request $request)
    {
        $periode = $request->filled('periode') ? app(\App\Services\PeriodService::class)->normalize($request->query('periode')) : null;

        $query = TransaksiOperasionalHeader::with(['user', 'details', 'bonSementara', 'bsOwner']);
        if (!auth()->user()->canSeeAllTransactions()) {
            $query->visibleTo(auth()->user());
        }
        if ($periode) $query->where('periode', $periode);

        $headers = $query->where('status', 'VALIDATED')->orderBy('tanggal')->get();
        $categories = \App\Models\CostCategory::pluck('nama', 'id');

        $headings = ['Transaksi ID', 'Tanggal', 'Periode', 'User', 'Pemilik Saldo BS', 'Cost Category', 'Remark', 'Nominal'];
        $rows = [];
        $total = 0;

        foreach ($headers as $h) {
            foreach ($h->details as $d) {
                $total += (float) $d->nominal;
                $rows[] = [
                    $h->transaksi_id,
                    \Carbon\Carbon::parse($h->tanggal)->format('d/m/Y'),
                    str_replace('-', '', $h->periode ?? ''),
                    $h->user->nama ?? '',
                    $h->bsOwner?->nama ?? $h->bonSementara?->user?->nama ?? 'Arsip tanpa BS',
                    $categories[$d->cost_category_id] ?? '',
                    $d->remark,
                    (float) $d->nominal,
                ];
            }
        }
        $rows[] = ['TOTAL', '', '', '', '', '', '', (float) $total];

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LaporanTableExport($headings, $rows, ['H']),
            'laporan-operasional-sales-' . ($periode ?: 'semua-periode') . '.xlsx'
        );
    }
}
