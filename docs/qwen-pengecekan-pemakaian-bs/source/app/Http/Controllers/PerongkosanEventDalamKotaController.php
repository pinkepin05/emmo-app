<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PerongkosanEventDalamKota;
use App\Models\PlafonEventDalamKota;
use App\Models\EventHourGroup;
use App\Models\User;
use App\Services\BonSementaraService;
use App\Services\PlafonService;
use App\Notifications\TransaksiOverPlafon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PerongkosanEventDalamKotaController extends Controller
{
    public function index()
    {
        $query = PerongkosanEventDalamKota::with(['user']);

        if (!auth()->user()->canSeeAllTransactions()) {
            $query->where('user_id', auth()->id());
        }

        $events = $query->orderBy('tanggal', 'desc')->paginate(10);

        return view('perongkosan_event_dalam_kota.index', compact('events'));
    }

    public function create()
    {
        $jam_kerja = EventHourGroup::all();
        return view('perongkosan_event_dalam_kota.create', compact('jam_kerja'));
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
            PlafonEventDalamKota::class,
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
        'is_libur'           => 'required|in:0,1',
        'jam_kerja_event_id' => 'required|exists:event_hour_group,id',
        'description'        => 'required|string|max:255',
        'nominal'            => 'required|numeric|min:1',
        'foto_event_1'       => 'required|image|mimes:jpeg,png,jpg|max:2048',
        'foto_event_2'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'foto_event_3'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    $data = $request->except(['foto_event_1', 'foto_event_2', 'foto_event_3']);
    $data['user_id'] = Auth::id();

    // 🎯 PERBAIKAN: Pakai PeriodService::ofDate() bukan getActivePeriode()
    $periode = app(\App\Services\PeriodService::class)->ofDate($request->tanggal);
    app(\App\Services\PeriodService::class)->assertOpen($periode);
    $data['periode'] = $periode;

    $prefix = 'EV-DLM-';
    $datePrefix = \App\Support\AppTime::now()->format('ymd');
    $todayCount = PerongkosanEventDalamKota::where('transaksi_id', 'like', $prefix . $datePrefix . '%')->count();
    $sequence = str_pad($todayCount + 1, 3, '0', STR_PAD_LEFT);
    $data['transaksi_id'] = $prefix . $datePrefix . '-' . $sequence;

    foreach (['foto_event_1', 'foto_event_2', 'foto_event_3'] as $fotoField) {
        if ($request->hasFile($fotoField)) {
            $path = $request->file($fotoField)->store('events_dalam_kota', 'public');
            $data[$fotoField] = $path;
        }
    }

    $event = PerongkosanEventDalamKota::create($data);
    $user = Auth::user();

    $cek = $plafonService->checkEvent(
        PlafonEventDalamKota::class,
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
            $event->transaksi_id . ' (Event Dalam Kota)',
            $event->nominal, $cek['plafon_value'], $cek['selisih'],
            route('perongkosan_event_dalam_kota.index')
        ));
    }

    // 🎯 TRIGGER CASH FLOW
    event(new \App\Events\CashFlowChanged($event->user_id, $event->periode));

    return redirect()->route('perongkosan_event_dalam_kota.index')
                     ->with('success', 'Transaksi Event Dalam Kota (' . $event->transaksi_id . ') berhasil disimpan!');
}

    public function show($id)
    {
        $event = PerongkosanEventDalamKota::with(['user', 'jamKerja'])->findOrFail($id);

        if (!auth()->user()->canAccessTransaction($event)) {
            abort(403, 'Anda tidak memiliki akses untuk melihat transaksi ini.');
        }

        $jam_kerja = EventHourGroup::all();

        return view('perongkosan_event_dalam_kota.show', compact('event', 'jam_kerja'));
    }

    public function edit($id)
    {
        $event = PerongkosanEventDalamKota::findOrFail($id);

        if (!auth()->user()->canAccessTransaction($event)) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit transaksi ini.');
        }

        $jam_kerja = EventHourGroup::all();
        return view('perongkosan_event_dalam_kota.edit', compact('event', 'jam_kerja'));
    }

    public function update(Request $request, $id, PlafonService $plafonService)
{
    $event = PerongkosanEventDalamKota::findOrFail($id);

    if (!auth()->user()->canAccessTransaction($event)) {
        abort(403, 'Anda tidak memiliki akses untuk mengubah transaksi ini.');
    }

    $request->validate([
        'tanggal'            => 'required|date',
        'is_libur'           => 'required|in:0,1',
        'jam_kerja_event_id' => 'required|exists:event_hour_group,id',
        'description'        => 'required|string|max:255',
        'nominal'            => 'required|numeric|min:1',
        'foto_event_1'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'foto_event_2'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'foto_event_3'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    // 🎯 PERBAIKAN: Pakai PeriodService::ofDate() untuk periode baru
    $newPeriode = app(\App\Services\PeriodService::class)->ofDate($request->tanggal);
    app(\App\Services\PeriodService::class)->assertOpen($newPeriode);
    
    $oldPeriode = app(\App\Services\PeriodService::class)->ofDate($event->tanggal);

    $data = $request->except(['foto_event_1', 'foto_event_2', 'foto_event_3']);
    $data['periode'] = $newPeriode;
    $data['user_id'] = $event->user_id;

    foreach (['foto_event_1', 'foto_event_2', 'foto_event_3'] as $field) {
        if ($request->hasFile($field)) {
            if ($event->$field && Storage::disk('public')->exists($event->$field)) {
                $oldPhoto = $event->$field;
                \Illuminate\Support\Facades\DB::afterCommit(fn () => Storage::disk('public')->delete($oldPhoto));
            }
            $data[$field] = $request->file($field)->store('events_dalam_kota', 'public');
        }
    }

    $wasOver = (bool) $event->is_over_plafon;
    $event->update($data);

    $user = $event->user;
    $cek = $plafonService->checkEvent(
        PlafonEventDalamKota::class,
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
            $event->transaksi_id . ' (Event Dalam Kota)',
            $event->nominal, $cek['plafon_value'], $cek['selisih'],
            route('perongkosan_event_dalam_kota.index')
        ));
    }

    // 🎯 TRIGGER CASH FLOW — periode baru
    event(new \App\Events\CashFlowChanged($event->user_id, $event->periode));

    // 🎯 Jika periode berubah, recalculate periode LAMA juga
    if ($oldPeriode !== $event->periode) {
        event(new \App\Events\CashFlowChanged($event->user_id, $oldPeriode));
    }

    return redirect()->route('perongkosan_event_dalam_kota.index')
                     ->with('success', 'Data Event Dalam Kota berhasil diperbarui!');
}

    public function destroy($id)
    {
        $event = PerongkosanEventDalamKota::findOrFail($id);

        if (!auth()->user()->canAccessTransaction($event)) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus transaksi ini.');
        }

        // 🎯 Simpan data untuk trigger SEBELUM dihapus
        $userId = $event->user_id;
        $periode = app(\App\Services\PeriodService::class)->ofDate($event->tanggal);

        foreach (['foto_event_1', 'foto_event_2', 'foto_event_3'] as $field) {
            if ($event->$field && Storage::disk('public')->exists($event->$field)) {
                $oldPhoto = $event->$field;
                \Illuminate\Support\Facades\DB::afterCommit(fn () => Storage::disk('public')->delete($oldPhoto));
            }
        }

        $event->delete();

        // 🎯 TRIGGER CASH FLOW
        event(new \App\Events\CashFlowChanged($userId, $periode));

        return redirect()->route('perongkosan_event_dalam_kota.index')
                         ->with('success', 'Data Event Dalam Kota berhasil dihapus!');
    }

    public function export(Request $request)
    {
        $periode = $request->get('periode');

        $query = PerongkosanEventDalamKota::with(['user']);
        if (!auth()->user()->canSeeAllTransactions()) {
            $query->where('user_id', auth()->id());
        }
        if ($periode) $query->where('periode', $periode);

        $events = $query->orderBy('tanggal')->get();
        $jams = \App\Models\EventHourGroup::pluck('code', 'id');

        $headings = ['Transaksi ID', 'Tanggal', 'Periode', 'User', 'Hari Libur', 'Jam Kerja Event', 'Deskripsi', 'Nominal', 'Plafon', 'Status Plafon'];
        $rows = [];
        $total = 0;

        foreach ($events as $e) {
            $total += (float) $e->nominal;
            $rows[] = [
                $e->transaksi_id,
                \Carbon\Carbon::parse($e->tanggal)->format('d/m/Y'),
                $e->periode ?? '',
                $e->user->nama ?? '',
                $e->is_libur ? 'Ya' : 'Tidak',
                $jams[$e->jam_kerja_event_id] ?? '',
                $e->description,
                (float) $e->nominal,
                $e->plafon_value ? (float) $e->plafon_value : '',
                $e->is_over_plafon ? 'OVER' : ($e->plafon_value ? 'SESUAI' : '-'),
            ];
        }
        $rows[] = ['TOTAL', '', '', '', '', '', '', (float) $total, '', ''];

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LaporanTableExport($headings, $rows, ['H', 'I']),
            'laporan-event-dalam-kota-' . ($periode ?: 'semua-periode') . '.xlsx'
        );
    }
}
