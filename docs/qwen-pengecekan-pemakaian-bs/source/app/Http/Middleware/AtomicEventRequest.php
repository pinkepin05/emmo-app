<?php

namespace App\Http\Middleware;

use App\Models\PerongkosanEventDalamKota;
use App\Models\PerongkosanEventLuarKota;
use App\Services\PeriodService;
use App\Services\StockCashFlowService;
use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AtomicEventRequest
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethodSafe()) return $next($request);

        $local = str_starts_with($request->route()->getName(), 'perongkosan_event_dalam_kota.');
        $model = $local ? PerongkosanEventDalamKota::class : PerongkosanEventLuarKota::class;
        $id = $request->route($local ? 'event_dalam_kota' : 'event_luar_kota')
            ?? $request->route($local ? 'event_dalam_kotum' : 'event_luar_kotum');
        if (!$request->isMethod('POST')) abort_unless($id, 404);
        $event = $id ? $model::findOrFail($id) : null;
        if ($event) abort_unless($request->user()->canAccessTransaction($event), 403);

        $periods = app(PeriodService::class);
        $keys = $event ? [$periods->ofDate($event->tanggal)] : [];
        if (!$request->isMethod('DELETE')) {
            $request->validate(['tanggal' => 'required|date_format:Y-m-d']);
            $keys[] = $periods->ofDate($request->tanggal);
        }
        $keys = array_unique($keys);
        sort($keys);
        $owner = (int) ($event?->user_id ?? $request->user()->id);

        return DB::transaction(function () use ($request, $next, $periods, $keys, $event, $owner) {
            foreach ($keys as $key) $periods->lockOpen($key);
            DB::table('user')->where('id', $owner)->lockForUpdate()->first();
            if ($event) {
                $locked = $event->newQuery()->whereKey($event->getKey())->lockForUpdate()->firstOrFail();
                if ($locked->tanggal !== $event->tanggal || (int) $locked->user_id !== $owner) {
                    throw ValidationException::withMessages(['error' => 'Transaksi berubah. Muat ulang halaman dan ulangi tindakan.']);
                }
            }

            $response = $next($request);
            if ($response->getStatusCode() >= 400) throw new HttpResponseException($response);
            foreach ($keys as $key) app(StockCashFlowService::class)->recalculateSnapshot($owner, $key);
            return $response;
        }, 3);
    }
}
