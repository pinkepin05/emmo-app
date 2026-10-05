<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PerongkosanEventLuarKota;
use App\Models\PlafonEventLuarKota;
use App\Models\EventHourGroup;
use App\Models\Wilayah;
use App\Models\User;
use App\Services\PlafonService;
use App\Notifications\TransaksiOverPlafon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerongkosanEventLuarKotaController extends Controller
{
    public function create()
    {
        $jam_kerja = EventHourGroup::all();
        return view('perongkosan_event_luar_kota.create', compact('jam_kerja'));
    }

    public function getProvinsiByTeritory(Request $request)
    {
        $teritory = $request->get('teritory');
        $provinsis = Wilayah::where('teritory', $teritory)
                       ->whereNull('parent_id')
                       ->select('id', 'nama')
                       ->orderBy('nama', 'asc')
                       ->get();
        return response()->json($provinsis);
    }

    public function getKotaByProvinsi(Request $request)
    {
        $provinsiId = $request->get('provinsi_id');
        $kotas = Wilayah::where('parent_id', $provinsiId)
                    ->select('id', 'nama')
                    ->orderBy('nama', 'asc')
                    ->get();
        return response()->json($kotas);
    }

    public function getPlafonInfo(Request $request, PlafonService $plafonService)
    {
        $request->validate([
            'tanggal'            => 'required|date',
            'is_libur'           => 'required|in:0,1',
            'jam_kerja_event_id' => 'required|exists:event_hour_group,id',
        ]);

        $user = Auth::user();

        $plafon = $plafonService->getPlafonEvent(
            PlafonEventLuarKota::class,
            $user,
            $request->tanggal,
            (bool) $request->is_libur,
            $request->jam_kerja_event_id
        );

        if ($plafon) {
            return response()->json([
                'found'        => true,
                'plafon_value' => (float) $plafon->default_value,
                'hari_event'   => $plafon->hari_event,
                'message'      => '✅ Plafon ditemukan: Rp ' . number_format($plafon->default_value, 0, ',', '.'),
            ]);
        }

        return response()->json([
            'found'        => false,
            'plafon_value' => 0,
            'message'      => '⚠️ Plafon untuk Level, Kelompok Hari Kerja, Hari Event & Jam Kerja ini belum disetting.',
        ]);
    }

    public function store(Request $request, PlafonService $plafonService)
    {
        $request->validate([
            'tanggal'            => 'required|date',
            'lokasi_event_id'    => 'required|exists:wilayah,id',
            'teritory'           => 'required|in:Jawa,Luar Jawa',
            'is_libur'           => 'required|in:0,1',
            'jam_kerja_event_id' => 'required|exists:event_hour_group,id',
            'description'        => 'required|string|max:255',
            'nominal'            => 'required|numeric|min:1',
            'foto_event_1'       => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'foto_event_2'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_event_3'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $lokasi = Wilayah::find($request->lokasi_event_id);
        if ($lokasi && $lokasi->teritory !== $request->teritory) {
            return redirect()->back()
                ->withErrors(['lokasi_event_id' => '❌ Lokasi event tidak sesuai dengan Territory yang dipilih!'])
                ->withInput();
        }

        $data = $request->except(['foto_event_1', 'foto_event_2', 'foto_event_3', 'teritory']);
        $data['user_id'] = Auth::id();
        $data['jarak_km'] = 0;
        $data['durasi_jam'] = 0;

        $data['periode'] = app(\App\Services\PeriodService::class)->ofDate($request->tanggal);

        $prefix = 'EV-LKR-';
        $datePrefix = \App\Support\AppTime::now()->format('ymd');
        $todayCount = PerongkosanEventLuarKota::where('transaksi_id', 'like', $prefix . $datePrefix . '%')->count();
        $sequence = str_pad($todayCount + 1, 3, '0', STR_PAD_LEFT);
        $data['transaksi_id'] = $prefix . $datePrefix . '-' . $sequence;

        foreach (['foto_event_1', 'foto_event_2', 'foto_event_3'] as $fotoField) {
            if ($request->hasFile($fotoField)) {
                $data[$fotoField] = $request->file($fotoField)->store('events_luar_kota', 'public');
            }
        }

        $event = PerongkosanEventLuarKota::create($data);
        $user = Auth::user();

        $cek = $plafonService->checkEvent(
            PlafonEventLuarKota::class,
            $user, $event->tanggal, (bool) $event->is_libur,
            $event->jam_kerja_event_id, $event->nominal
        );

        $wasOver = false;

        $event->update([
            'is_over_plafon' => $cek['is_over'] ? 1 : 0,
            'plafon_value'   => $cek['plafon_value'],
            'selisih_plafon' => $cek['selisih'],
        ]);

        if ($cek['is_over'] && !$wasOver && $user->atasan_user_id) {
            $atasan = User::find($user->atasan_user_id);
            $atasan?->notify(new TransaksiOverPlafon(
                $event->transaksi_id . ' (Event Luar Kota)',
                $event->nominal, $cek['plafon_value'], $cek['selisih'],
                route('perongkosan_event_luar_kota.index')
            ));
        }

        // 🎯 TRIGGER CASH FLOW
        event(new \App\Events\CashFlowChanged($event->user_id, $event->periode));

        return redirect()->route('perongkosan_event_luar_kota.index')
            ->with('success', 'Transaksi Event Luar Kota (' . $event->transaksi_id . ') berhasil disimpan!');
    }

    public function index()
    {
        $query = PerongkosanEventLuarKota::with(['user', 'wilayah', 'jamKerja']);

        if (!auth()->user()->canSeeAllTransactions()) {
            $query->where('user_id', auth()->id());
        }

        $events = $query->orderBy('tanggal', 'desc')->paginate(10);

        return view('perongkosan_event_luar_kota.index', compact('events'));
    }

    public function show($id)
    {
        $event = PerongkosanEventLuarKota::with(['user', 'wilayah', 'jamKerja'])->findOrFail($id);
        abort_unless(auth()->user()->canAccessTransaction($event), 403, 'Anda tidak memiliki akses untuk melihat transaksi ini.');

        return view('perongkosan_event_luar_kota.show', compact('event'));
    }

    public function edit($id)
    {
        $event = PerongkosanEventLuarKota::with('wilayah')->findOrFail($id);

        if (!auth()->user()->canAccessTransaction($event)) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit transaksi ini.');
        }

        $jam_kerja = EventHourGroup::all();
        return view('perongkosan_event_luar_kota.edit', compact('event', 'jam_kerja'));
    }

    public function update(Request $request, $id, PlafonService $plafonService)
    {
        $event = PerongkosanEventLuarKota::findOrFail($id);

        if (!auth()->user()->canAccessTransaction($event)) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah transaksi ini.');
        }

        $request->validate([
            'tanggal'            => 'required|date',
            'lokasi_event_id'    => 'required|exists:wilayah,id',
            'teritory'           => 'required|in:Jawa,Luar Jawa',
            'is_libur'           => 'required|in:0,1',
            'jam_kerja_event_id' => 'required|exists:event_hour_group,id',
            'description'        => 'required|string|max:255',
            'nominal'            => 'required|numeric|min:1',
            'foto_event_1'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_event_2'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_event_3'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $lokasi = Wilayah::find($request->lokasi_event_id);
        if ($lokasi && $lokasi->teritory !== $request->teritory) {
            return redirect()->back()
                ->withErrors(['lokasi_event_id' => '❌ Lokasi event tidak sesuai dengan Territory yang dipilih!'])
                ->withInput();
        }

        $data = $request->except(['foto_event_1', 'foto_event_2', 'foto_event_3', 'teritory']);
        $data['lokasi_event_id'] = $request->lokasi_event_id;
        $data['user_id'] = $event->user_id;
        $data['jarak_km'] = $event->jarak_km;
        $data['durasi_jam'] = $event->durasi_jam;

        foreach (['foto_event_1', 'foto_event_2', 'foto_event_3'] as $field) {
            if ($request->hasFile($field)) {
                if ($event->$field && Storage::disk('public')->exists($event->$field)) {
                    $oldPhoto = $event->$field;
                    DB::afterCommit(fn () => Storage::disk('public')->delete($oldPhoto));
                }
                $data[$field] = $request->file($field)->store('events_luar_kota', 'public');
            }
        }

        // 🎯 Simpan periode LAMA sebelum update
        $oldPeriode = app(\App\Services\PeriodService::class)->ofDate($event->tanggal);

        $wasOver = (bool) $event->is_over_plafon;
        $event->update($data);
        $event->forceFill(['status' => 'DRAFT', 'validated_at' => null, 'validated_by' => null,
            'submission_version' => $event->submission_version + 1])->save();

        // Hitung ulang periode berdasarkan tanggal baru
        $newPeriode = substr($event->tanggal, 0, 7);
        if ($newPeriode !== $event->periode) {
            $event->update(['periode' => $newPeriode]);
        }

        $user = $event->user;
        $cek = $plafonService->checkEvent(
            PlafonEventLuarKota::class,
            $user, $event->tanggal, (bool) $event->is_libur,
            $event->jam_kerja_event_id, $event->nominal
        );

        $event->update([
            'is_over_plafon' => $cek['is_over'] ? 1 : 0,
            'plafon_value'   => $cek['plafon_value'],
            'selisih_plafon' => $cek['selisih'],
        ]);

        if ($cek['is_over'] && !$wasOver && $user->atasan_user_id) {
            $atasan = User::find($user->atasan_user_id);
            $atasan?->notify(new TransaksiOverPlafon(
                $event->transaksi_id . ' (Event Luar Kota)',
                $event->nominal, $cek['plafon_value'], $cek['selisih'],
                route('perongkosan_event_luar_kota.index')
            ));
        }

        // 🎯 TRIGGER CASH FLOW — periode baru
        event(new \App\Events\CashFlowChanged($event->user_id, $event->periode));

        // 🎯 Jika periode berubah, recalculate periode LAMA juga
        if ($oldPeriode !== $event->periode) {
            event(new \App\Events\CashFlowChanged($event->user_id, $oldPeriode));
        }

        return redirect()->route('perongkosan_event_luar_kota.index')->with('success', 'Data berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $event = PerongkosanEventLuarKota::findOrFail($id);

        if (!auth()->user()->canAccessTransaction($event)) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus transaksi ini.');
        }

        // 🎯 Simpan data untuk trigger SEBELUM dihapus
        $userId = $event->user_id;
        $periode = app(\App\Services\PeriodService::class)->ofDate($event->tanggal);

        foreach (['foto_event_1', 'foto_event_2', 'foto_event_3'] as $field) {
            if ($event->$field && Storage::disk('public')->exists($event->$field)) {
                $oldPhoto = $event->$field;
                DB::afterCommit(fn () => Storage::disk('public')->delete($oldPhoto));
            }
        }

        $event->delete();

        // 🎯 TRIGGER CASH FLOW
        event(new \App\Events\CashFlowChanged($userId, $periode));

        return redirect()->route('perongkosan_event_luar_kota.index')->with('success', 'Data berhasil dihapus!');
    }

    public function export(Request $request)
    {
        $periode = $request->get('periode');

        $query = PerongkosanEventLuarKota::with(['user', 'wilayah']);
        if (!auth()->user()->canSeeAllTransactions()) {
            $query->where('user_id', auth()->id());
        }
        if ($periode) $query->where('periode', $periode);

        $events = $query->orderBy('tanggal')->get();
        $jams = \App\Models\EventHourGroup::pluck('code', 'id');

        $headings = ['Transaksi ID', 'Tanggal', 'Periode', 'User', 'Lokasi (Provinsi)', 'Hari Libur', 'Jam Kerja Event', 'Deskripsi', 'Nominal', 'Plafon', 'Status Plafon'];
        $rows = [];
        $total = 0;

        foreach ($events as $e) {
            $total += (float) $e->nominal;
            $rows[] = [
                $e->transaksi_id,
                \Carbon\Carbon::parse($e->tanggal)->format('d/m/Y'),
                $e->periode ?? '',
                $e->user->nama ?? '',
                $e->wilayah->nama ?? '',
                $e->is_libur ? 'Ya' : 'Tidak',
                $jams[$e->jam_kerja_event_id] ?? '',
                $e->description,
                (float) $e->nominal,
                $e->plafon_value ? (float) $e->plafon_value : '',
                $e->is_over_plafon ? 'OVER' : ($e->plafon_value ? 'SESUAI' : '-'),
            ];
        }
        $rows[] = ['TOTAL', '', '', '', '', '', '', '', (float) $total, '', ''];

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LaporanTableExport($headings, $rows, ['I', 'J']),
            'laporan-event-luar-kota-' . ($periode ?: 'semua-periode') . '.xlsx'
        );
    }
}
