<?php

namespace App\Http\Controllers;

use App\Services\DocumentNumberService;

use Illuminate\Http\Request;
use App\Models\TransaksiPerjalananDinasHeader;
use App\Models\TransaksiPerjalananDinasDetail;
use App\Models\User;
use App\Models\SettingPlafonPerjalananDinas;
use App\Models\CostCategory;
use App\Models\WilayahDistance;
use App\Models\Periode;
use App\Services\BonSementaraService;
use App\Services\PlafonService;
use App\Notifications\TransaksiOverPlafon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TransaksiPerjalananDinasController extends Controller
{
    public function create()
    {
        $cost_categories = CostCategory::all();
        $dbPeriods = Periode::pluck('status', 'periode')->toArray();
        $currentMonth = \App\Support\AppTime::now()->format('Y-m');

        $openPeriods = [];
        foreach ($dbPeriods as $periode => $status) {
            if ($status === 'OPEN') {
                $openPeriods[] = $periode;
            }
        }

        if (!isset($dbPeriods[$currentMonth])) {
            $openPeriods[] = $currentMonth;
        }

        return view('transaksi_perjalanan_dinas.create', compact('cost_categories', 'openPeriods'));
    }

    public function getProvinsiByTeritory(Request $request)
    {
        $teritory = $request->get('teritory');
        $provinsis = \App\Models\Wilayah::where('teritory', $teritory)
            ->whereNull('parent_id')
            ->select('id', 'nama')
            ->orderBy('nama', 'asc')
            ->get();
        return response()->json($provinsis);
    }

    public function getKotaByProvinsi(Request $request)
    {
        $provinsiId = $request->get('provinsi_id');
        $kotas = \App\Models\Wilayah::where('parent_id', $provinsiId)
            ->select('id', 'nama')
            ->orderBy('nama', 'asc')
            ->get();
        return response()->json($kotas);
    }

    public function checkPlafonRealtime(Request $request, PlafonService $plafonService)
    {
        $request->validate([
            'tujuan_dinas_id' => 'required|exists:wilayah,id',
            'total_nominal'    => 'required|numeric|decimal:0,2|min:0|max:9999999999999.99',
        ]);

        $user = Auth::user();
        $tujuanDinasId = $request->tujuan_dinas_id;
        $totalNominal = (float) $request->total_nominal;
        $tanggal = $request->tanggal ?? \App\Support\AppTime::now()->format('Y-m-d');

        $cek = $plafonService->checkPerjalananDinas($user, $tujuanDinasId, $tanggal, $totalNominal);
        return response()->json($cek);
    }

    public function getPlafonInfo(Request $request, PlafonService $plafonService)
    {
        $request->validate(['tujuan_dinas_id' => 'required|exists:wilayah,id']);

        $user = Auth::user();
        $tujuanDinasId = $request->tujuan_dinas_id;
        $tanggal = $request->tanggal ?? \App\Support\AppTime::now()->format('Y-m-d');

        $plafon = $plafonService->getPlafonPerjalananDinas($user, $tujuanDinasId, $tanggal);

        if ($plafon) {
            return response()->json([
                'found'         => true,
                'plafon_value'  => (float) $plafon->default_value,
                'description'   => $plafon->description,
                'tanggal_berlaku' => $plafon->tanggal_berlaku,
                'message'       => '✅ Plafon ditemukan: Rp ' . number_format($plafon->default_value, 0, ',', '.'),
            ]);
        }

        return response()->json([
            'found'         => false,
            'plafon_value'  => 0,
            'message'       => '⚠️ Plafon untuk rute & level ini belum disetting.',
        ]);
    }

    public function store(Request $request, BonSementaraService $bsService, PlafonService $plafonService)
    {
        $request->validate([
            'save_action' => 'sometimes|required|in:draft,confirm',
            'tanggal' => 'required|date',
            'description' => 'required|string|max:255',
            'tujuan_dinas_id' => 'required|exists:wilayah,id',
            'teritory' => 'required|in:Jawa,Luar Jawa',
            'jarak_km' => 'required|numeric|decimal:0,2|min:0|max:99999999.99',
            'durasi_jam' => 'required|numeric|min:0|max:24',
            ...\App\Services\TransactionFundingService::rules(),
            'details' => 'required|array|min:1',
            'details.*.cost_category_id' => 'required|exists:cost_category,id',
            'details.*.remark' => 'required|string|max:255',
            'details.*.nominal' => 'required|numeric|decimal:0,2|min:0|max:9999999999999.99',
        ]);

        $tujuanWilayah = \App\Models\Wilayah::find($request->tujuan_dinas_id);
        if ($tujuanWilayah && $tujuanWilayah->teritory !== $request->teritory) {
            return redirect()->back()
                ->withErrors(['tujuan_dinas_id' => '❌ Wilayah tujuan tidak sesuai dengan Territory yang dipilih!'])
                ->withInput();
        }

        $bsOwnerId = app(\App\Services\TransactionFundingService::class)->resolve($request, true);

        try {
            DB::beginTransaction();
            $activeUserId = Auth::id();
            $user = auth()->user();

            foreach ($request->details as $index => $detail) {
                $costCategory = CostCategory::find($detail['cost_category_id']);
                $categoryName = strtolower($costCategory->name ?? $costCategory->nama ?? $costCategory->description ?? '');

                if (str_contains($categoryName, 'lumpsum') || str_contains($categoryName, 'uang saku')) {
                    if ($request->jarak_km <= 200) {
                        DB::rollBack();
                        return redirect()->back()
                            ->withErrors([
                                'details.' . $index . '.cost_category_id' =>
                                ' Lumpsum HANYA untuk perjalanan > 200 KM. Jarak Anda: ' . $request->jarak_km . ' KM'
                            ])
                            ->withInput();
                    }
                }

                if (str_contains($categoryName, 'hotel') || str_contains($categoryName, 'akomodasi')) {
                    if ($request->durasi_jam <= 8) {
                        DB::rollBack();
                        return redirect()->back()
                            ->withErrors([
                                'details.' . $index . '.cost_category_id' =>
                                ' Hotel/Akomodasi HANYA untuk perjalanan > 8 jam. Durasi Anda: ' . $request->durasi_jam . ' jam'
                            ])
                            ->withInput();
                    }
                }
            }

            $header = TransaksiPerjalananDinasHeader::where('tanggal', $request->tanggal)
                ->where('user_id', $activeUserId)
                ->first();

            if ($header) {
                throw \Illuminate\Validation\ValidationException::withMessages(['tanggal' => 'Transaksi pada tanggal ini sudah ada. Buka transaksi tersebut untuk menambah rincian draft.']);
            } else {
                $transaksi_id = app(DocumentNumberService::class)->next('PD', $request->tanggal);
                $header = TransaksiPerjalananDinasHeader::create([
                    'transaksi_id'    => $transaksi_id,
                    'tanggal'         => $request->tanggal,
                    'user_id'         => $activeUserId,
                    'bs_owner_id' => $bsOwnerId,
                    'periode'         => app(\App\Services\PeriodService::class)->ofDate($request->tanggal),
                    'status'          => 'DRAFT',
                    'description'     => $request->description,
                    'tujuan_dinas_id' => $request->tujuan_dinas_id,
                    'jarak_km'        => $request->jarak_km,
                    'durasi_jam'      => $request->durasi_jam,
                ]);
                $pesan_sukses = 'Draft Perjalanan Dinas (' . $transaksi_id . ') disimpan. Buka detail untuk Konfirmasi.';
            }

            foreach ($request->details as $detail) {
                TransaksiPerjalananDinasDetail::create([
                    'transaksi_id'     => $transaksi_id,
                    'cost_category_id' => $detail['cost_category_id'],
                    'remark'           => $detail['remark'],
                    'nominal'          => $detail['nominal'],
                ]);
            }

            $header->refresh();
            $totalBaru = $header->details()->sum('nominal');

            $cek = $plafonService->checkPerjalananDinas($user, $header->tujuan_dinas_id, $header->tanggal, $totalBaru);
            $wasOver = (bool) $header->is_over_plafon;

            $header->update([
                'is_over_plafon' => $cek['is_over'] ? 1 : 0,
                'plafon_value'   => $cek['plafon_value'],
                'selisih_plafon' => $cek['selisih'],
            ]);

            if ($cek['is_over'] && !$wasOver && $user->atasan_user_id) {
                $atasan = User::find($user->atasan_user_id);
                \App\Services\BsNotifications::send($atasan, new TransaksiOverPlafon(
                    $transaksi_id . ' (Perjalanan Dinas)',
                    $totalBaru, $cek['plafon_value'], $cek['selisih'],
                    route('transaksi_perjalanan_dinas.show', $transaksi_id)
                ));
            }

            if ($request->input('save_action', 'draft') === 'confirm') {
                $status = app(\App\Services\BsValidationService::class)->finalize($header);
                $pesan_sukses = $status === 'PENDING_OWNER'
                    ? 'Permintaan pemakaian BS dikirim kepada pemohon. Saldo belum berkurang sampai disetujui.'
                    : 'Transaksi Perjalanan Dinas (' . $transaksi_id . ') berhasil disimpan dan dikonfirmasi. Realisasi BS telah dibukukan.';
            }

            DB::commit();
            return redirect()->route('transaksi_perjalanan_dinas.index')->with('success', $pesan_sukses);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'Transaksi Gagal: ' . $e->getMessage()])->withInput();
        }
    }

    public function validateTransaction($id)
    {
        $transaksi = TransaksiPerjalananDinasHeader::where('transaksi_id', $id)->firstOrFail();
        $status = app(\App\Services\BsValidationService::class)->finalize($transaksi);
        return redirect()->route('transaksi_perjalanan_dinas.show', $transaksi->transaksi_id)
            ->with('success', $status === 'PENDING_OWNER'
                ? 'Permintaan pemakaian BS dikirim kepada pemohon. Saldo belum berkurang sampai disetujui.'
                : 'Transaksi berhasil dikonfirmasi. Realisasi BS telah dibukukan.');
    }

    public function withdraw(Request $request, $id)
    {
        return $this->returnToDraft($request, $id, false);
    }

    public function revise(Request $request, $id)
    {
        return $this->returnToDraft($request, $id, true);
    }

    private function returnToDraft(Request $request, $id, bool $rejected)
    {
        $request->validate(['approval_version' => 'required|integer|min:1']);
        $transaksi = TransaksiPerjalananDinasHeader::where('transaksi_id', $id)->firstOrFail();
        app(\App\Services\BsValidationService::class)->returnToDraft($transaksi, (int) $request->approval_version, $rejected);
        return redirect()->route('transaksi_perjalanan_dinas.show', $id)
            ->with('success', 'Transaksi kembali menjadi draft. Perbaiki rincian lalu ajukan kembali untuk persetujuan.');
    }

    public function index()
    {
        $query = TransaksiPerjalananDinasHeader::with(['user', 'tujuanDinas'])->withExists('financeChecks');

        if (!auth()->user()->canSeeAllTransactions()) {
            $query->visibleTo(auth()->user());
        }

        $transaksis = $query->orderBy('tanggal', 'desc')->paginate(10);
        return view('transaksi_perjalanan_dinas.index', compact('transaksis'));
    }

    public function show($id)
    {
        $transaksi = TransaksiPerjalananDinasHeader::with(['user', 'details', 'tujuanDinas.parent', 'bonSementara', 'bsOwner', 'approvalLogs.actor'])->withExists('financeChecks')
            ->where('transaksi_id', $id)->firstOrFail();

        if (!auth()->user()->canViewBsTransaction($transaksi)) {
            abort(403, 'Anda tidak memiliki akses ke transaksi ini.');
        }

        $cost_categories = CostCategory::all();

    

        $currentTerritory = old('teritory', $transaksi->tujuanDinas->teritory ?? 'Jawa');
        if (!in_array($currentTerritory, ['Jawa', 'Luar Jawa'], true)) {
            $currentTerritory = $transaksi->tujuanDinas->teritory ?? 'Jawa';
        }
        $provinces = \App\Models\Wilayah::where('teritory', $currentTerritory)->whereNull('parent_id')->get();

        $currentProvinceId = old('provinsi_id', $transaksi->tujuanDinas->parent_id ?? null);
        $cities = [];
        if ($currentProvinceId) {
            $cities = \App\Models\Wilayah::where('parent_id', $currentProvinceId)->get();
        }

        return view('transaksi_perjalanan_dinas.show', compact(
            'transaksi', 'cost_categories', 'provinces', 'cities', 'currentTerritory', 'currentProvinceId'
        ));
    }

    // 🎯 PERBAIKAN: Tambah guard status DRAFT
    public function update(Request $request, $id, BonSementaraService $bsService, PlafonService $plafonService)
    {
        $transaksi = TransaksiPerjalananDinasHeader::where('transaksi_id', $id)->firstOrFail();

        if (!auth()->user()->canAccessTransaction($transaksi)) {
            abort(403);
        }

        // 🎯 GUARD: Hanya DRAFT yang bisa di-edit
        if ($transaksi->status !== 'DRAFT') {
            return back()->withErrors(['error' => 'Hanya transaksi dengan status DRAFT yang dapat diedit. Transaksi ini sudah dikonfirmasi.']);
        }

        // 🎯 GUARD: Periode harus OPEN
        app(\App\Services\PeriodService::class)->assertOpen($transaksi->periode);

        $request->validate([
            'tanggal' => 'required|date',
            'description' => 'required|string|max:255',
            'teritory' => 'required|in:Jawa,Luar Jawa',
            'tujuan_dinas_id' => 'required|exists:wilayah,id',
            'jarak_km' => 'required|numeric|decimal:0,2|min:0|max:99999999.99',
            'durasi_jam' => 'required|numeric|min:0|max:24',
            ...\App\Services\TransactionFundingService::rules(),
            'details' => 'nullable|array',
            'details.*.cost_category_id' => 'required_with:details|exists:cost_category,id',
            'details.*.remark' => 'nullable|string|max:255',
            'details.*.nominal' => 'required_with:details|numeric|decimal:0,2|min:0|max:9999999999999.99',
            'new_details' => 'nullable|array',
            'new_details.*.cost_category_id' => 'required_with:new_details|exists:cost_category,id',
            'new_details.*.remark' => 'nullable|string|max:255',
            'new_details.*.nominal' => 'required_with:new_details|numeric|decimal:0,2|min:0|max:9999999999999.99',
            'delete_details' => 'nullable|array',
            'delete_details.*' => 'integer|distinct',
        ]);

        app(\App\Services\PeriodService::class)->assertOpen(\Carbon\Carbon::parse($request->tanggal)->format('Y-m'));

        $tujuanWilayah = \App\Models\Wilayah::find($request->tujuan_dinas_id);
        if ($tujuanWilayah && $tujuanWilayah->teritory !== $request->teritory) {
            return back()->withErrors(['tujuan_dinas_id' => 'Wilayah tujuan tidak sesuai dengan Territory yang dipilih.']);
        }

        $bsOwnerId = app(\App\Services\TransactionFundingService::class)->resolve($request, true, $transaksi);

        DB::beginTransaction();
        try {
            $transaksi->update([
                'tanggal' => $request->tanggal,
                'description' => $request->description,
                'tujuan_dinas_id' => $request->tujuan_dinas_id,
                'jarak_km' => $request->jarak_km,
                'durasi_jam' => $request->durasi_jam,
                'bs_owner_id' => $bsOwnerId,
                'periode' => \Carbon\Carbon::parse($request->tanggal)->format('Y-m'),
            ]);

            $validateRules = function($catId, $jarak, $durasi) {
                $cat = CostCategory::find($catId);
                $name = strtolower($cat->name ?? $cat->description ?? '');
                if ((str_contains($name, 'lumpsum') || str_contains($name, 'uang saku')) && $jarak <= 200) {
                    throw new \Exception("Lumpsum hanya untuk jarak > 200 KM.");
                }
                if ((str_contains($name, 'hotel') || str_contains($name, 'akomodasi')) && $durasi <= 8) {
                    throw new \Exception("Hotel hanya untuk durasi > 8 Jam.");
                }
            };

            if ($request->has('details')) {
                foreach ($request->details as $detailId => $data) {
                    $detail = TransaksiPerjalananDinasDetail::find($detailId);
                    if ($detail && $detail->transaksi_id == $transaksi->transaksi_id) {
                        $validateRules($data['cost_category_id'], $request->jarak_km, $request->durasi_jam);
                        $detail->update([
                            'cost_category_id' => $data['cost_category_id'],
                            'remark' => $data['remark'],
                            'nominal' => $data['nominal'],
                        ]);
                    }
                }
            }

            if ($request->has('delete_details') && is_array($request->delete_details)) {
                TransaksiPerjalananDinasDetail::whereIn('id', $request->delete_details)
                    ->where('transaksi_id', $transaksi->transaksi_id)
                    ->delete();
            }

            if ($request->has('new_details')) {
                foreach ($request->new_details as $data) {
                    $validateRules($data['cost_category_id'], $request->jarak_km, $request->durasi_jam);
                    TransaksiPerjalananDinasDetail::create([
                        'transaksi_id' => $transaksi->transaksi_id,
                        'cost_category_id' => $data['cost_category_id'],
                        'remark' => $data['remark'],
                        'nominal' => $data['nominal'],
                    ]);
                }
            }

            $transaksi->refresh();
            $totalBaru = $transaksi->details()->sum('nominal');
            $cek = $plafonService->checkPerjalananDinas($transaksi->user, $transaksi->tujuan_dinas_id, $transaksi->tanggal, $totalBaru);

            $transaksi->update([
                'is_over_plafon' => $cek['is_over'] ? 1 : 0,
                'plafon_value' => $cek['plafon_value'],
                'selisih_plafon' => $cek['selisih'],
            ]);

            DB::commit();
            return redirect()->route('transaksi_perjalanan_dinas.show', $transaksi->transaksi_id)
                ->with('success', 'Transaksi berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal update: ' . $e->getMessage()])->withInput();
        }
    }

    // 🎯 PERBAIKAN: Tambah guard status DRAFT
    public function storeDetail(Request $request, $id, BonSementaraService $bsService, PlafonService $plafonService)
    {
        $request->validate([
            'details' => 'required|array|min:1',
            'details.*.cost_category_id' => 'required|exists:cost_category,id',
            'details.*.remark' => 'required|string|max:255',
            'details.*.nominal' => 'required|numeric|decimal:0,2|min:0|max:9999999999999.99',
        ]);

        try {
            DB::beginTransaction();
            $header = TransaksiPerjalananDinasHeader::where('transaksi_id', $id)->firstOrFail();

            if (!auth()->user()->canAccessTransaction($header)) {
                abort(403, 'Anda tidak memiliki akses ke transaksi ini.');
            }

            // 🎯 GUARD: Hanya DRAFT yang bisa ditambah detail
            if ($header->status !== 'DRAFT') {
                return back()->withErrors(['error' => 'Tidak dapat menambah rincian. Transaksi sudah dikonfirmasi.']);
            }

            // 🎯 GUARD: Periode harus OPEN
            app(\App\Services\PeriodService::class)->assertOpen($header->periode);

            

            foreach ($request->details as $index => $detail) {
                $costCategory = CostCategory::find($detail['cost_category_id']);
                $categoryName = strtolower($costCategory->name ?? $costCategory->nama ?? $costCategory->description ?? '');

                if (str_contains($categoryName, 'lumpsum') || str_contains($categoryName, 'uang saku')) {
                    if ($header->jarak_km <= 200) {
                        DB::rollBack();
                        return redirect()->back()->withErrors([
                            'details.' . $index . '.cost_category_id' =>
                            '❌ Lumpsum HANYA untuk perjalanan > 200 KM. Jarak transaksi ini: ' . $header->jarak_km . ' KM'
                        ])->withInput();
                    }
                }

                if (str_contains($categoryName, 'hotel') || str_contains($categoryName, 'akomodasi')) {
                    if ($header->durasi_jam <= 8) {
                        DB::rollBack();
                        return redirect()->back()->withErrors([
                            'details.' . $index . '.cost_category_id' =>
                            '❌ Hotel/Akomodasi HANYA untuk perjalanan > 8 jam. Durasi transaksi ini: ' . $header->durasi_jam . ' jam'
                        ])->withInput();
                    }
                }
            }

            foreach ($request->details as $detail) {
                TransaksiPerjalananDinasDetail::create([
                    'transaksi_id'     => $header->transaksi_id,
                    'cost_category_id' => $detail['cost_category_id'],
                    'remark'           => $detail['remark'],
                    'nominal'          => $detail['nominal'],
                ]);
            }

            $header->refresh();
            $totalBaru = $header->details()->sum('nominal');

            $cek = $plafonService->checkPerjalananDinas($header->user, $header->tujuan_dinas_id, $header->tanggal, $totalBaru);
            $wasOver = (bool) $header->is_over_plafon;

            $header->update([
                'is_over_plafon' => $cek['is_over'] ? 1 : 0,
                'plafon_value'   => $cek['plafon_value'],
                'selisih_plafon' => $cek['selisih'],
            ]);

            if ($cek['is_over'] && !$wasOver && $header->user->atasan_user_id) {
                $atasan = User::find($header->user->atasan_user_id);
                \App\Services\BsNotifications::send($atasan, new TransaksiOverPlafon(
                    $header->transaksi_id . ' (Perjalanan Dinas)',
                    $totalBaru, $cek['plafon_value'], $cek['selisih'],
                    route('transaksi_perjalanan_dinas.show', $header->transaksi_id)
                ));
            }

            DB::commit();
            return redirect()->back()->with('success', 'Rincian biaya berhasil ditambahkan ke Transaksi (' . $id . ')!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'Gagal Menambah Rincian: ' . $e->getMessage()]);
        }
    }

    // 🎯 PERBAIKAN: Tambah guard status DRAFT
    public function destroy($id)
    {
        $transaksi = TransaksiPerjalananDinasHeader::where('transaksi_id', $id)->firstOrFail();

        if (!auth()->user()->canAccessTransaction($transaksi)) {
            abort(403, 'Anda tidak memiliki akses ke transaksi ini.');
        }

        // 🎯 GUARD: Hanya DRAFT yang bisa dihapus
        if ($transaksi->status !== 'DRAFT') {
            return back()->withErrors(['error' => 'Tidak dapat menghapus transaksi yang sudah dikonfirmasi.']);
        }

            $periode = $transaksi->periode;

        $transaksi->details()->delete();
        $transaksi->delete();

        return redirect()->route('transaksi_perjalanan_dinas.index')
            ->with('success', 'Transaksi Perjalanan Dinas berhasil dihapus!');
    }

    public function getAutoNominal(Request $request, PlafonService $plafonService)
    {
        $costCategoryId = $request->cost_category_id;
        $jarakKm = (float) $request->jarak_km;
        $durasiJam = (float) $request->durasi_jam;
        $tujuanDinasId = $request->tujuan_dinas_id;
        $user = Auth::user();

        $costCategory = CostCategory::find($costCategoryId);
        $categoryName = strtolower($costCategory->name ?? $costCategory->description ?? '');

        if (str_contains($categoryName, 'lumpsum') || str_contains($categoryName, 'uang saku')) {
            if ($jarakKm <= 200) {
                return response()->json([
                    'nominal' => 0, 'is_valid' => false, 'is_readonly' => false,
                    'message' => "❌ Lumpsum hanya untuk jarak > 200 KM. Jarak Anda: {$jarakKm} KM"
                ]);
            }
        } elseif (str_contains($categoryName, 'hotel') || str_contains($categoryName, 'akomodasi') || str_contains($categoryName, 'menginap')) {
            if ($durasiJam <= 8) {
                return response()->json([
                    'nominal' => 0, 'is_valid' => false, 'is_readonly' => false,
                    'message' => "❌ Hotel hanya untuk durasi > 8 Jam. Durasi Anda: {$durasiJam} Jam"
                ]);
            }
        }

        if ($tujuanDinasId) {
            $plafon = $plafonService->getPlafonPerjalananDinas($user, $tujuanDinasId, now());
            if ($plafon) {
                return response()->json([
                    'nominal' => 0, 'is_valid' => true, 'is_readonly' => false,
                    'plafon_total' => (float) $plafon->default_value,
                    'message' => "ℹ️ Silakan input nominal manual. Plafon Total Perjalanan: Rp " . number_format($plafon->default_value, 0, ',', '.')
                ]);
            }
        }

        return response()->json([
            'nominal' => 0, 'is_valid' => true, 'is_readonly' => false,
            'message' => "ℹ️ Kategori Umum. Silakan input nominal secara manual."
        ]);
    }

    public function export(Request $request)
    {
        $periode = $request->get('periode');
        $query = TransaksiPerjalananDinasHeader::with(['user', 'details', 'tujuanDinas', 'bonSementara', 'bsOwner']);

        if (!auth()->user()->canSeeAllTransactions()) {
            $query->visibleTo(auth()->user());
        }

        if ($periode) $query->where('periode', $periode);

        $headers = $query->where('status', 'VALIDATED')->orderBy('tanggal')->get();
        $categories = \App\Models\CostCategory::pluck('nama', 'id');

        $headings = ['Transaksi ID', 'Tanggal', 'Periode', 'User', 'Tujuan Dinas', 'Jarak (KM)', 'Durasi (Jam)', 'Pemilik Saldo BS', 'Cost Category', 'Remark', 'Nominal', 'Plafon', 'Status Plafon'];
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
                    $h->tujuanDinas->nama ?? '',
                    (float) $h->jarak_km,
                    (float) $h->durasi_jam,
                    $h->bsOwner?->nama ?? $h->bonSementara?->user?->nama ?? 'Arsip tanpa BS',
                    $categories[$d->cost_category_id] ?? '',
                    $d->remark,
                    (float) $d->nominal,
                    $h->plafon_value ? (float) $h->plafon_value : '',
                    $h->is_over_plafon ? 'OVER' : ($h->plafon_value ? 'SESUAI' : '-'),
                ];
            }
        }
        $rows[] = ['TOTAL', '', '', '', '', '', '', '', '', '', (float) $total, '', ''];

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LaporanTableExport($headings, $rows, ['K', 'L']),
            'laporan-perjalanan-dinas-' . ($periode ?: 'semua-periode') . '.xlsx'
        );
    }

    // 🎯 PERBAIKAN: Tambah guard status header
    public function destroyDetail(Request $request, $detailId)
    {
        $request->validate(['reason' => 'required|string|max:255']);

        $detail = TransaksiPerjalananDinasDetail::with(['header', 'costCategory'])->findOrFail($detailId);
        $header = $detail->header;
        $user = auth()->user();

        if (!$user->canAccessTransaction($header)) {
            return response()->json(['message' => 'Anda tidak memiliki akses untuk menghapus detail ini.'], 403);
        }

        // 🎯 GUARD: Hanya DRAFT yang bisa dihapus detailnya
        if ($header->status !== 'DRAFT') {
            return response()->json([
                'message' => 'Gagal: Transaksi sudah dikonfirmasi. Detail tidak dapat dihapus.'
            ], 403);
        }

        $periodService = app(\App\Services\PeriodService::class);
        if ($periodService->statusOf($header->periode) !== 'OPEN') {
            return response()->json([
                'message' => 'Gagal: Periode transaksi ini sudah CLOSE. Detail tidak dapat dihapus.'
            ], 403);
        }

        

        try {
            DB::beginTransaction();

            $oldValue = [
                'cost_category' => $detail->costCategory->name ?? $detail->cost_category_id,
                'remark'        => $detail->remark,
                'nominal'       => $detail->nominal,
            ];

            $detail->delete();

            $newTotal = $header->details()->sum('nominal');
            $plafonService = app(PlafonService::class);
            $cek = $plafonService->checkPerjalananDinas($header->user, $header->tujuan_dinas_id, $header->tanggal, $newTotal);

            $header->update([
                'is_over_plafon' => $cek['is_over'] ? 1 : 0,
                'plafon_value'   => $cek['plafon_value'] ?? 0,
                'selisih_plafon' => $cek['selisih'] ?? 0,
            ]);

            \App\Models\AuditLog::create([
                'user_id'    => $user->id,
                'action'     => 'DELETE_DETAIL',
                'table_name' => 'transaksi_perjalanan_dinas_detail',
                'record_id'  => $detail->id,
                'reason'     => $request->reason,
                'old_value'  => json_encode($oldValue),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Detail berhasil dihapus. Total draft telah diperbarui.',
                'new_total' => $newTotal,
                'is_over' => $cek['is_over'],
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal hapus detail transaksi: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus detail: ' . $e->getMessage()], 500);
        }
    }
}
