<?php

namespace App\Http\Middleware;

use App\Models\PengajuanBs;
use App\Models\TransaksiOperasionalHeader;
use App\Models\TransaksiPerjalananDinasDetail;
use App\Models\TransaksiPerjalananDinasHeader;
use App\Services\PeriodService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AtomicBsRequest
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethodSafe()) {
            return $next($request);
        }

        $periods = app(PeriodService::class);
        $name = $request->route()->getName();
        $header = null;
        $id = $request->route('id');
        if (str_starts_with($name, 'bs-usage-approvals.')) {
            $header = TransaksiPerjalananDinasHeader::findOrFail($id);
        } elseif (str_starts_with($name, 'transaksi_operasional.')) {
            $id ??= $request->route('transaksi_operasional');
            if ($id) $header = TransaksiOperasionalHeader::findOrFail($id);
        } elseif (str_starts_with($name, 'transaksi_perjalanan_dinas.')) {
            $id ??= $request->route('transaksi_perjalanan_dinas');
            if ($name === 'transaksi_perjalanan_dinas.destroy_detail') {
                $header = TransaksiPerjalananDinasDetail::findOrFail($id)->header;
            } elseif ($id) {
                $header = TransaksiPerjalananDinasHeader::where('transaksi_id', $id)->firstOrFail();
            }
        }

        $keys = [];
        if ($header) $keys[] = $header->periode;
        if ($request->has('tanggal')) {
            $request->validate(['tanggal' => 'required|date_format:Y-m-d']);
            $keys[] = $periods->ofDate($request->tanggal);
        }
        if (!$keys) $keys[] = $periods->current();
        $keys = array_unique(array_map($periods->normalize(...), $keys));
        sort($keys);
        $bs = $request->route('bs');
        $request->validate(['bs_owner_id' => 'nullable|integer|exists:user,id']);
        $bsIds = array_filter([$bs instanceof PengajuanBs ? $bs->id : null, $header?->bs_id]);
        // Resolve owner IDs before BEGIN; acquire locks before any consistent-read
        // balance query so MySQL REPEATABLE READ cannot retain a pre-lock balance.
        $owners = PengajuanBs::whereIn('id', $bsIds)->pluck('user_id')
            ->push($request->user()->id, $header?->user_id, $header?->bsCashOwnerId(), $request->input('bs_owner_id'))
            ->filter()->map(fn ($id) => (int) $id)->unique()->sort();

        return DB::transaction(function () use ($request, $next, $periods, $keys, $header, $bs, $bsIds, $owners, $name) {
            foreach ($keys as $key) $periods->lockOpen($key);

            foreach ($owners as $owner) DB::table('user')->where('id', $owner)->lockForUpdate()->first();
            foreach (PengajuanBs::whereIn('id', $bsIds)->orderBy('id')->lockForUpdate()->get() as $locked) {
                if ($bs instanceof PengajuanBs && (int) $bs->id === (int) $locked->id) {
                    $request->route()->setParameter('bs', $locked);
                }
            }

            if ($header) {
                $locked = $header->newQuery()->whereKey($header->getKey())->lockForUpdate()->firstOrFail();
                if (!in_array($locked->periode, $keys, true) || (string) $locked->bs_id !== (string) $header->bs_id
                    || $locked->bsCashOwnerId() !== $header->bsCashOwnerId() || (int) $locked->user_id !== (int) $header->user_id) {
                    throw ValidationException::withMessages(['error' => 'Transaksi berubah. Muat ulang halaman dan ulangi tindakan.']);
                }
                $requiredStatus = 'DRAFT';
                if (in_array($name, ['bs-usage-approvals.approve', 'bs-usage-approvals.reject'], true)) {
                    abort_unless((int) $request->user()->id === $locked->bsCashOwnerId() && $locked->needsOwnerApproval(), 403);
                    $requiredStatus = 'PENDING_OWNER';
                } elseif (in_array($name, ['transaksi_perjalanan_dinas.withdraw', 'transaksi_perjalanan_dinas.revise'], true)) {
                    abort_unless((int) $request->user()->id === (int) $locked->user_id, 403);
                    $requiredStatus = str_ends_with($name, '.withdraw') ? 'PENDING_OWNER' : 'REJECTED_OWNER';
                } else {
                    abort_unless($request->user()->canAccessTransaction($locked), 403);
                }
                if ($locked->status !== $requiredStatus) {
                    throw ValidationException::withMessages(['error' => 'Status transaksi berubah atau tindakan tidak tersedia. Muat ulang dan periksa rincian terbaru.']);
                }
            }

            $response = $next($request);
            // Laravel's routing pipeline may render an exception into a response.
            // Roll back the outer unit of work when a controller failed.
            if ($response->getStatusCode() >= 400) {
                throw new \Illuminate\Http\Exceptions\HttpResponseException($response);
            }
            return $response;
        }, 3);
    }
}
