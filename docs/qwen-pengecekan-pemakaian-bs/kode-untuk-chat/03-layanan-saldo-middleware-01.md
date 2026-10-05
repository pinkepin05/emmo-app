# Kode existing: layanan-saldo-middleware, bagian 1

Snapshot referensi. Bukan implementasi fitur baru. Semua file di bawah disertakan utuh.

## FILE: app/Events/CashFlowChanged.php

SHA-256: `6cccee7cc6b8ab1d8a37c543d72b3cd4c2509ba88e7c7aa7bbbfbba22c413b41`

~~~~php
<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CashFlowChanged
{
    use Dispatchable, SerializesModels;

    public int $userId;
    public string $periode;

    public function __construct(int $userId, string $periode)
    {
        $this->userId = $userId;
        $this->periode = $periode;
    }
}
~~~~

## FILE: app/Http/Middleware/AtomicBsRequest.php

SHA-256: `85a0e66fc5f4774d22bc2d12655b7e25585a7ea171c7d41d00281adb0b35fc4f`

~~~~php
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

~~~~

## FILE: app/Http/Middleware/AtomicEventRequest.php

SHA-256: `ca32507a3f6c233896e40a65dac7756737059d957ff003d91bdb7471eb6facee`

~~~~php
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

~~~~

## FILE: app/Http/Middleware/CheckActiveAtasan.php

SHA-256: `9cc18bcf10c99df501963e06b6cb386ff5d6fd0d1b195763460cff64a74a0e78`

~~~~php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveAtasan
{
    /**
     * 🎯 L3 - Atasan Nonaktif
     * Menolak submit transaksi jika atasan user sedang nonaktif.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // ✅ Atasan aktif (atau user tidak punya atasan) -> lanjut
        if ($user && $user->hasActiveAtasan()) {
            return $next($request);
        }

        // ❌ Atasan non-aktif -> tolak submit
        $message = 'Atasan user tidak aktif. Hubungi Administrator untuk perbaikan data atasan.';

        // Request AJAX -> JSON 403
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        // Request form POST -> kembalikan ke form + tampilkan error + pertahankan isian
        return redirect()
            ->back()
            ->withErrors(['atasan' => $message])
            ->with('error', $message)
            ->withInput();
    }
}
~~~~

## FILE: app/Http/Middleware/CheckActiveCompany.php

SHA-256: `de525d05a762592abcf82868c8406dd9131db9ae2b026ce1117deeabae254b02`

~~~~php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveCompany
{
    /**
     * 🎯 L2 - Company Nonaktif Tidak Bisa Dipakai
     * Halaman form tetap terbuka; yang ditolak adalah proses SIMPAN transaksi.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // ✅ Company aktif -> lanjut simpan
        if ($user && $user->hasActiveCompany()) {
            return $next($request);
        }

        // ❌ Company non-aktif -> tolak submit
        $message = 'Company tidak aktif. Transaksi tidak dapat dilakukan.';

        // Request AJAX -> JSON 403
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        // Request form POST -> kembalikan ke form + tampilkan error + pertahankan isian
        return redirect()
            ->back()
            ->withErrors(['company' => $message])
            ->with('error', $message)
            ->withInput();
    }
}
~~~~

## FILE: app/Http/Middleware/InjectNotificationBell.php

SHA-256: `695d26f0ca4c319b4554ff6c9610d619258f645ec273565df191c505a3110d00`

~~~~php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectNotificationBell
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Suntikkan lonceng hanya jika: user login + respons HTML + belum ada lonceng
        if (
            auth()->check() &&
            $response instanceof \Illuminate\Http\Response &&
            str_contains((string) $response->headers->get('Content-Type'), 'text/html') &&
            str_contains($response->getContent(), '</body>') &&
            !str_contains($response->getContent(), 'id="notifBell"') // 🎯 cegah lonceng ganda
        ) {
            $bell = view('partials.notification-bell')->render();
            $response->setContent(
                str_replace('</body>', $bell . '</body>', $response->getContent())
            );
        }

        return $response;
    }
}
~~~~

## FILE: app/Listeners/UpdateCashFlowSnapshot.php

SHA-256: `9165b5535afba7134ad5cc68bb267171d48c7344f813f811389c90d9b237b841`

~~~~php
<?php

namespace App\Listeners;

use App\Events\CashFlowChanged;
use App\Services\CashFlowService;

class UpdateCashFlowSnapshot
{
    protected CashFlowService $cashFlowService;

    public function __construct(CashFlowService $cashFlowService)
    {
        $this->cashFlowService = $cashFlowService;
    }

    public function handle(CashFlowChanged $event): void
    {
        $this->cashFlowService->recalculateSnapshot(
            $event->userId,
            $event->periode
        );
    }
}
~~~~

## FILE: app/Observers/RoleObserver.php

SHA-256: `79148faf35f262ec493b61f4d328e1c3faa4c9c2b9f6213e5fe0c620b68e738e`

~~~~php
<?php

namespace App\Observers;

use App\Models\Role;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

class RoleObserver
{
    /**
     * Role master baru dibuat -> otomatis muncul di Access Management.
     */
    public function created(Role $role): void
    {
        if ($role->code) {
            SpatieRole::firstOrCreate(['name' => $role->code, 'guard_name' => 'web']);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    /**
     * Code role master diubah -> Spatie role ikut diganti namanya.
     */
    public function updating(Role $role): void
    {
        if ($role->isDirty('code')) {
            $old = SpatieRole::where('name', $role->getOriginal('code'))->first();

            if ($old) {
                $old->update(['name' => $role->code]);
            } else {
                SpatieRole::firstOrCreate(['name' => $role->code, 'guard_name' => 'web']);
            }

            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
}
~~~~

## FILE: app/Observers/UserObserver.php

SHA-256: `4007be3caf7e772f979dbb42ae8845e5cdf976bc9f00ff6220de9b3f67bd724f`

~~~~php
<?php

namespace App\Observers;

use App\Models\User;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

class UserObserver
{
    public function created(User $user): void
    {
        $this->syncSpatieRole($user);
    }

    public function updated(User $user): void
    {
        if ($user->wasChanged('roles_id')) {
            $this->syncSpatieRole($user);
        }
    }

    /**
     * SESUAI DOKUMEN rev4:
     * User.User Right Group ID -> roles.code -> Spatie role bernama sama.
     */
    protected function syncSpatieRole(User $user): void
    {
        $code = $user->role?->code;

        if (!$code) {
            $user->syncRoles([]);
            return;
        }

        $spatieRole = SpatieRole::firstOrCreate(['name' => $code, 'guard_name' => 'web']);
        $user->syncRoles([$spatieRole]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
~~~~

## FILE: app/Services/BonSementaraService.php

SHA-256: `eac2026302a1694ab6aaea079bc80342c39ce9830f5d99ac562be5a10d24ee92`

~~~~php
<?php

namespace App\Services;

use App\Models\Periode;

class BonSementaraService
{
        /**
     * 🎯 AUTO-ROLL: Periode aktif adalah periode OPEN terbaru.
     * Jika bulan di dunia nyata belum ada di database, sistem otomatis membuatnya.
     */
    public function getActivePeriode(): string
    {
        $currentMonth = \App\Support\AppTime::now()->format('Y-m');
        
        // 1. Pastikan bulan saat ini (dunia nyata) PASTI ada di database dan berstatus OPEN
        Periode::firstOrCreate(
            ['periode' => $currentMonth], 
            ['status' => 'OPEN']
        );

        // 2. Ambil periode OPEN paling baru (mendukung multiple OPEN untuk settlement periode lalu)
        $latestOpen = Periode::where('status', 'OPEN')->orderBy('periode', 'desc')->first();
        
        return $latestOpen ? $latestOpen->periode : $currentMonth;
    }

    public function getSaldoAwal($userId, $periode)
    {
        return app(BsCashFlowService::class)->calculate((int) $userId, $periode)['saldo_awal'];
    }

    public function getRealtimeSisaSaldo($userId, $periode)
    {
        $service = app(BsCashFlowService::class);
        return $service->availableBalance($service->calculate((int) $userId, $periode));
    }

}

~~~~

## FILE: app/Services/BsCashFlowService.php

SHA-256: `e720ec88244cd9e7dfe7d77c3f5cef3aa5a9fcddd1ebe53f5a967fb918646c62`

~~~~php
<?php

namespace App\Services;

use App\Models\ApprovalBsFinance;
use App\Models\BsCashFlowSnapshot;
use App\Models\PengajuanBs;
use App\Models\Periode;
use App\Models\TransaksiOperasionalHeader;
use App\Models\TransaksiPerjalananDinasHeader;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class BsCashFlowService
{
    public function __construct(private PeriodService $periods) {}

    public function receipts(int $userId, string $periode)
    {
        $periode = $this->periods->normalize($periode);
        return ApprovalBsFinance::whereHas('pengajuanBs', fn ($q) => $q->where('user_id', $userId))
            ->where('tanggal_cair', '>=', $periode.'-01')
            ->where('tanggal_cair', '<', $this->periods->next($periode).'-01');
    }

    public function expenses(string $model, int $userId, string $periode)
    {
        return $model::forBsOwner($userId)
            ->where('periode', $this->periods->normalize($periode))
            ->where('status', 'VALIDATED');
    }

    /** Separate existing settlement adjustments; this service does not perform settlement. */
    public function adjustment(int $userId, string $periode): int
    {
        $query = PengajuanBs::where('user_id', $userId)->where('status', 'SETTLED')->where('settled_periode', $periode);
        return Money::minor((clone $query)->sum('nominal_reimburse')) - Money::minor($query->sum('nominal_pengembalian'));
    }

    public function calculate(int $userId, string $periode): array
    {
        $periode = $this->periods->normalize($periode);
        $previous = $this->periods->previous($periode);
        $previousRecord = Periode::where('periode', $previous)->first();
        $opening = 0;
        $ready = !Periode::where('periode', '<', $periode)->exists();

        if ($previousRecord?->status === 'CLOSED') {
            $prior = BsCashFlowSnapshot::where('user_id', $userId)->where('periode', $previous)->first();
            if ($prior && $prior->saldo_akhir !== null) {
                $opening = Money::minor($prior->saldo_akhir);
                $ready = $prior->opening_final;
            } else {
                // Bootstrap the new BS-only summary without rewriting legacy snapshots.
                $prior = $this->calculate($userId, $previous);
                $opening = Money::minor($prior['saldo_akhir']);
                $ready = $prior['opening_final'];
            }
        }

        $incoming = Money::minor($this->receipts($userId, $periode)->sum('nominal_approved'));
        $outgoing = 0;
        foreach ([TransaksiOperasionalHeader::class, TransaksiPerjalananDinasHeader::class] as $model) {
            foreach ($this->expenses($model, $userId, $periode)->withSum('details', 'nominal')->get() as $header) {
                $outgoing += Money::minor($header->details_sum_nominal);
            }
        }
        $adjustment = $this->adjustment($userId, $periode);

        return [
            'saldo_awal' => Money::decimal($opening),
            'total_masuk' => Money::decimal($incoming),
            'total_keluar' => Money::decimal($outgoing),
            'penyesuaian' => Money::decimal($adjustment),
            'saldo_akhir' => Periode::where('periode', $periode)->where('status', 'CLOSED')->exists()
                ? Money::decimal($opening + $incoming - $outgoing + $adjustment)
                : null,
            'opening_final' => (bool) $ready,
        ];
    }

    /** Funds available for transaction validation, separate from the closing balance. */
    public function availableBalance(array $balance): string
    {
        return Money::decimal(
            Money::minor($balance['saldo_awal']) + Money::minor($balance['total_masuk'])
            - Money::minor($balance['total_keluar']) + Money::minor($balance['penyesuaian'])
        );
    }

    public function recalculateSnapshot(int $userId, string $periode): BsCashFlowSnapshot
    {
        $periode = $this->periods->normalize($periode);
        return DB::transaction(function () use ($userId, $periode) {
            $period = Periode::where('periode', $periode)->lockForUpdate()->first();
            DB::table('user')->where('id', $userId)->lockForUpdate()->first();
            $existing = BsCashFlowSnapshot::where('user_id', $userId)->where('periode', $periode)->first();
            if ($period?->status === 'CLOSED' && $existing?->saldo_akhir !== null) return $existing;

            return BsCashFlowSnapshot::updateOrCreate(
                ['user_id' => $userId, 'periode' => $periode],
                $this->calculate($userId, $periode) + ['last_updated' => now()]
            );
        }, 3);
    }

    public function owners(): array
    {
        return PengajuanBs::pluck('user_id')->merge(BsCashFlowSnapshot::pluck('user_id'))
            ->merge(TransaksiOperasionalHeader::whereNotNull('bs_owner_id')->pluck('bs_owner_id'))
            ->merge(TransaksiPerjalananDinasHeader::whereNotNull('bs_owner_id')->pluck('bs_owner_id'))
            ->unique()->sort()->values()->all();
    }

    public function ensureSnapshotForPeriod(string $periode): void
    {
        foreach ($this->owners() as $owner) $this->recalculateSnapshot((int) $owner, $periode);
    }

    public function details(int $userId, string $periode): array
    {
        $result = ['masuk' => [], 'keluar' => [], 'penyesuaian' => []];
        foreach ($this->receipts($userId, $periode)->with('pengajuanBs')->orderBy('tanggal_cair')->get() as $receipt) {
            $result['masuk'][] = ['type' => 'BS Dicairkan', 'date' => $receipt->tanggal_cair,
                'description' => $receipt->pengajuanBs?->document_no ?? '-', 'nominal' => $receipt->nominal_approved,
                'url' => route('pengajuan-bs.show', $receipt->pengajuan_bs_id)];
        }
        foreach ([TransaksiOperasionalHeader::class => 'Operasional Area Sales', TransaksiPerjalananDinasHeader::class => 'Perjalanan Dinas'] as $model => $label) {
            foreach ($this->expenses($model, $userId, $periode)->with(['user', 'bonSementara'])->withSum('details', 'nominal')->orderBy('tanggal')->get() as $header) {
                $result['keluar'][] = ['type' => $label, 'date' => $header->tanggal,
                    'description' => $header->transaksi_id.' (diinput: '.($header->user?->nama ?? '-').')',
                    'nominal' => $header->details_sum_nominal,
                    'url' => $header instanceof TransaksiPerjalananDinasHeader
                        ? route('transaksi_perjalanan_dinas.show', $header->transaksi_id)
                        : route('transaksi_operasional.show', $header->id)];
            }
        }
        foreach (PengajuanBs::where('user_id', $userId)->where('status', 'SETTLED')->where('settled_periode', $periode)->get() as $bs) {
            $amount = Money::minor($bs->nominal_reimburse) - Money::minor($bs->nominal_pengembalian);
            if ($amount !== 0) $result['penyesuaian'][] = ['description' => $bs->document_no, 'nominal' => Money::decimal($amount), 'date' => $bs->settled_at?->toDateString(),
                'url' => route('pengajuan-bs.show', $bs->id)];
        }
        return $result;
    }
}

~~~~

## FILE: app/Services/BsNotifications.php

SHA-256: `8526e6fc5c79a8c69ef31bbe4b9f6f68813133c03ecf71a9933bf5fe43800fa4`

~~~~php
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Throwable;

class BsNotifications
{
    public static function send(?\App\Models\User $recipient, \Illuminate\Notifications\Notification $notification): void
    {
        self::afterCommit(fn () => $recipient?->notify($notification));
    }

    public static function afterCommit(callable $send): void
    {
        DB::afterCommit(function () use ($send) {
            try { $send(); } catch (Throwable $e) { report($e); }
        });
    }
}

~~~~

## FILE: app/Services/BsPeriodClosingService.php

SHA-256: `b99478eacc4a3181f8fd301e66e55f77e59b8e2c7b7622390f3d26c166197215`

~~~~php
<?php

namespace App\Services;

use App\Models\ApprovalBsFinance;
use App\Models\BsCashFlowSnapshot;
use App\Models\CashFlowSnapshot;
use App\Models\StockCashFlowSnapshot;
use App\Models\PengajuanBs;
use App\Models\Periode;
use App\Models\TransaksiOperasionalHeader;
use App\Models\TransaksiPerjalananDinasHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BsPeriodClosingService
{
    public function __construct(private PeriodService $periods, private BsCashFlowService $cash) {}

    public function close(int $id): void
    {
        DB::transaction(function () use ($id) {
            $records = Periode::orderBy('periode')->lockForUpdate()->get();
            $period = $records->firstWhere('id', $id);
            abort_unless($period, 404);
            $this->require($period->status === 'OPEN', 'Periode sudah ditutup sebelumnya.');
            $this->require($period->periode < $this->periods->current(), 'Periode baru dapat ditutup setelah bulannya berakhir.');
            $prior = $records->where('periode', '<', $period->periode);
            $this->require(!$prior->contains('status', 'OPEN'), 'Tutup periode sebelumnya terlebih dahulu.');
            $this->require($prior->isEmpty() || $prior->last()->periode === $this->periods->previous($period->periode), 'Ada bulan yang belum tercatat di antara periode. Lengkapi urutan periode terlebih dahulu.');
            $this->require(!$records->where('periode', '>', $period->periode)->contains('status', 'CLOSED'), 'Periode berikutnya sudah ditutup; lakukan rekonsiliasi urutan tutup buku.');
            foreach ([TransaksiOperasionalHeader::class, TransaksiPerjalananDinasHeader::class] as $model) {
                $this->require(!$model::where('periode', $period->periode)->whereIn('status', ['DRAFT', 'PENDING_OWNER'])->exists(), 'Masih ada transaksi draft atau menunggu persetujuan pemohon pada periode ini. Selesaikan transaksi sebelum tutup buku.');
            }

            $this->cash->ensureSnapshotForPeriod($period->periode);
            $this->require(!BsCashFlowSnapshot::where('periode', $period->periode)->where('opening_final', false)->exists(), 'Saldo awal belum final; periksa penutupan periode sebelumnya.');
            $stock = app(StockCashFlowService::class);
            $stock->ensureSnapshotForPeriod($period->periode);
            $this->require(!StockCashFlowSnapshot::where('periode', $period->periode)->where('opening_final', false)->exists(), 'Saldo awal Stok Kas belum final; periksa penutupan periode sebelumnya.');
            $period->update(['status' => 'CLOSED', 'closed_at' => now(), 'closed_by' => auth()->id()]);
            $this->cash->ensureSnapshotForPeriod($period->periode);
            $stock->ensureSnapshotForPeriod($period->periode);
            // Maintain the existing non-BS summary without changing its source rules.
            app(CashFlowService::class)->ensureSnapshotForPeriod($period->periode);

            $next = $this->periods->next($period->periode);
            Periode::firstOrCreate(['periode' => $next], ['status' => 'OPEN']);
            $this->cash->ensureSnapshotForPeriod($next);
            $stock->ensureSnapshotForPeriod($next);
            foreach (CashFlowSnapshot::where('periode', $period->periode)->pluck('user_id') as $owner) {
                app(CashFlowService::class)->recalculateSnapshot((int) $owner, $next);
            }
        }, 3);
    }

    public function reopen(int $id, string $reason): void
    {
        DB::transaction(function () use ($id, $reason) {
            $records = Periode::orderBy('periode')->lockForUpdate()->get();
            $period = $records->firstWhere('id', $id);
            abort_unless($period, 404);
            $this->require($period->status === 'CLOSED', 'Hanya periode CLOSED yang dapat dibuka kembali.');
            $next = $this->periods->next($period->periode);
            $this->require(!$records->where('periode', '>', $period->periode)->contains('status', 'CLOSED'), 'Periode berikutnya sudah ditutup.');
            $this->require(!ApprovalBsFinance::where('tanggal_cair', '>=', $next.'-01')->exists(), 'Periode berikutnya sudah memiliki pencairan.');
            $this->require(!PengajuanBs::where('settled_periode', '>=', $next)->exists(), 'Periode berikutnya sudah memiliki penyelesaian BS.');
            // Preserve checks on shared-period consumers outside the BS workstream.
            foreach (['pengajuan_bs', 'transaksi_operasional_area_sales_header', 'transaksi_perjalanan_dinas_header', 'perongkosan_event_dalam_kota', 'perongkosan_event_luar_kota'] as $table) {
                $this->require(!DB::table($table)->where('tanggal', '>=', $next.'-01')->exists(), 'Periode berikutnya sudah memiliki transaksi.');
            }
            $period->update(['status' => 'OPEN', 'reopened_at' => now(), 'reopen_reason' => $reason]);
            $this->cash->ensureSnapshotForPeriod($period->periode);
            $stock = app(StockCashFlowService::class);
            $stock->ensureSnapshotForPeriod($period->periode);
            $stock->ensureSnapshotForPeriod($next);
            foreach (CashFlowSnapshot::where('periode', $period->periode)->pluck('user_id') as $owner) {
                app(CashFlowService::class)->recalculateSnapshot((int) $owner, $period->periode);
            }
            $this->cash->ensureSnapshotForPeriod($next);
            foreach (CashFlowSnapshot::where('periode', $next)->pluck('user_id') as $owner) {
                app(CashFlowService::class)->recalculateSnapshot((int) $owner, $next);
            }
        }, 3);
    }

    private function require(bool $condition, string $message): void
    {
        if (!$condition) throw ValidationException::withMessages(['error' => $message]);
    }
}

~~~~

## FILE: app/Services/BsValidationService.php

SHA-256: `e196edddf4f587e0e8b356a46a14180380f23ee93ea8b0e605b87507cb218fbe`

~~~~php
<?php

namespace App\Services;

use App\Models\BsUsageApprovalLog;
use App\Models\TransaksiOperasionalHeader;
use App\Models\TransaksiPerjalananDinasHeader;
use App\Models\User;
use App\Notifications\BsUsageApprovalUpdated;
use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BsValidationService
{
    public function finalize(Model $document): string
    {
        return $this->withLockedDocument($document, function ($locked, $owner) {
            abort_unless(auth()->user()->canAccessTransaction($locked), 403);
            $this->requireStatus($locked, 'DRAFT');
            $amount = $this->amount($locked);
            $travel = $locked instanceof TransaksiPerjalananDinasHeader;
            app(TransactionFundingService::class)->assertAccess((int) $locked->user_id, $owner, $travel);
            if ($travel && $owner !== (int) $locked->user_id) {
                $locked->update([
                    'bs_owner_id' => $owner, 'status' => 'PENDING_OWNER',
                    'approval_version' => (int) $locked->approval_version + 1,
                    'submitted_at' => now(), 'submitted_by' => auth()->id(),
                    'owner_reviewed_at' => null, 'owner_reviewed_by' => null, 'owner_rejection_reason' => null,
                ]);
                $this->reviewLog($locked, 'SUBMITTED', $amount);
                $this->notify($locked, 'SUBMITTED', $owner);
                return 'PENDING_OWNER';
            }
            $this->post($locked, $owner, $amount);
            return 'VALIDATED';
        });
    }

    public function approve(TransaksiPerjalananDinasHeader $document, int $version): void
    {
        $this->withLockedDocument($document, function ($locked, $owner) use ($version) {
            $this->requireOwner($locked, $owner);
            $this->requireStatus($locked, 'PENDING_OWNER', $version);
            app(TransactionFundingService::class)->assertAccess((int) $locked->user_id, $owner, true);
            $amount = $this->amount($locked);
            $this->post($locked, $owner, $amount);
            $locked->update(['owner_reviewed_at' => now(), 'owner_reviewed_by' => auth()->id(), 'owner_rejection_reason' => null]);
            $this->reviewLog($locked, 'APPROVED', $amount);
            $this->notify($locked, 'APPROVED', (int) $locked->user_id);
        });
    }

    public function reject(TransaksiPerjalananDinasHeader $document, int $version, string $reason): void
    {
        if (trim($reason) === '' || mb_strlen($reason) > 1000) {
            throw ValidationException::withMessages(['reason' => 'Alasan penolakan wajib diisi, maksimal 1000 karakter.']);
        }
        $this->withLockedDocument($document, function ($locked, $owner) use ($version, $reason) {
            $this->requireOwner($locked, $owner);
            $this->requireStatus($locked, 'PENDING_OWNER', $version);
            $locked->update(['status' => 'REJECTED_OWNER', 'owner_reviewed_at' => now(),
                'owner_reviewed_by' => auth()->id(), 'owner_rejection_reason' => trim($reason)]);
            $this->reviewLog($locked, 'REJECTED', $this->amount($locked), trim($reason));
            $this->notify($locked, 'REJECTED', (int) $locked->user_id);
        });
    }

    public function returnToDraft(TransaksiPerjalananDinasHeader $document, int $version, bool $rejected = false): void
    {
        $this->withLockedDocument($document, function ($locked, $owner) use ($version, $rejected) {
            abort_unless((int) auth()->id() === (int) $locked->user_id, 403);
            $this->requireStatus($locked, $rejected ? 'REJECTED_OWNER' : 'PENDING_OWNER', $version);
            $action = $rejected ? 'REVISED' : 'WITHDRAWN';
            $this->reviewLog($locked, $action, $this->amount($locked));
            $locked->update(['status' => 'DRAFT', 'submitted_at' => null, 'submitted_by' => null,
                'owner_reviewed_at' => null, 'owner_reviewed_by' => null, 'owner_rejection_reason' => null]);
            if (!$rejected) $this->notify($locked, 'WITHDRAWN', $owner);
        });
    }

    private function withLockedDocument(Model $document, callable $action): mixed
    {
        abort_unless($document instanceof TransaksiOperasionalHeader || $document instanceof TransaksiPerjalananDinasHeader, 400);
        $owner = $document->bsCashOwnerId() ?? (int) $document->user_id;
        return DB::transaction(function () use ($document, $owner, $action) {
            app(PeriodService::class)->lockOpen($document->periode);
            foreach (collect([$owner, (int) $document->user_id])->unique()->sort() as $userId) {
                DB::table('user')->where('id', $userId)->lockForUpdate()->first();
            }
            $locked = $document->newQuery()->whereKey($document->getKey())->lockForUpdate()->firstOrFail();
            if ($locked->periode !== $document->periode || ($locked->bsCashOwnerId() ?? (int) $locked->user_id) !== $owner
                || (int) $locked->user_id !== (int) $document->user_id) {
                throw ValidationException::withMessages(['error' => 'Transaksi berubah. Muat ulang halaman.']);
            }
            return $action($locked, $owner);
        }, 3);
    }

    private function requireStatus(Model $document, string $status, ?int $version = null): void
    {
        if ($document->status !== $status || ($version !== null && (int) $document->approval_version !== $version)) {
            throw ValidationException::withMessages(['error' => 'Status atau versi permintaan berubah. Muat ulang dan periksa rincian terbaru sebelum melanjutkan.']);
        }
    }

    private function requireOwner(TransaksiPerjalananDinasHeader $document, int $owner): void
    {
        abort_unless((int) auth()->id() === $owner && $owner !== (int) $document->user_id, 403,
            'Hanya pemilik saldo yang dapat menyetujui atau menolak permintaan anggota.');
    }

    private function amount(Model $document): int
    {
        $amount = Money::minor($document->details()->sum('nominal'));
        if ($amount <= 0) throw ValidationException::withMessages(['details' => 'Tambahkan rincian biaya dengan total lebih dari nol.']);
        return $amount;
    }

    private function post(Model $document, int $owner, int $amount): void
    {
        $cash = app(BsCashFlowService::class);
        $available = Money::minor($cash->availableBalance($cash->calculate($owner, $document->periode)));
        if ($amount > $available) {
            throw ValidationException::withMessages(['bs_balance' => 'Total biaya melebihi saldo BS gabungan yang tersedia pada periode ini. Sisa: Rp '.number_format($available / 100, 2, ',', '.').'. Saldo bulan sebelumnya tersedia setelah tutup buku.']);
        }
        $document->update(['bs_owner_id' => $owner, 'status' => 'VALIDATED', 'validated_at' => now(), 'validated_by' => auth()->id()]);
        DB::table('bs_usage_logs')->insert([
            'transaction_type' => $document instanceof TransaksiPerjalananDinasHeader ? 'perjalanan_dinas' : 'operasional_sales',
            'transaction_id' => $document->id, 'document_no' => $document->transaksi_id,
            'bs_owner_id' => $owner, 'inputter_id' => $document->user_id, 'actor_id' => auth()->id(),
            'periode' => $document->periode, 'nominal' => Money::decimal($amount), 'created_at' => now(),
        ]);
        $cash->recalculateSnapshot($owner, $document->periode);
        app(StockCashFlowService::class)->recalculateSnapshot($owner, $document->periode);
    }

    private function reviewLog(TransaksiPerjalananDinasHeader $document, string $action, int $amount, ?string $reason = null): void
    {
        BsUsageApprovalLog::create([
            'transaction_id' => $document->id, 'document_no' => $document->transaksi_id,
            'approval_version' => $document->approval_version, 'bs_owner_id' => $document->bsCashOwnerId(),
            'inputter_id' => $document->user_id, 'actor_id' => auth()->id(), 'action' => $action,
            'nominal' => Money::decimal($amount), 'reason' => $reason, 'created_at' => now(),
        ]);
    }

    private function notify(TransaksiPerjalananDinasHeader $document, string $action, int $recipient): void
    {
        BsNotifications::send(User::find($recipient), new BsUsageApprovalUpdated($document, $action));
    }
}

~~~~

## FILE: app/Services/CashFlowService.php

SHA-256: `10a1957734510df90f4bec4242322d2be91d72f5a84d2dff5d6883b5c6e778a9`

~~~~php
<?php

namespace App\Services;

use App\Models\CashFlowSnapshot;
use App\Models\PengajuanBs;
use App\Models\ApprovalBsFinance;
use App\Models\Periode;
use App\Models\TransaksiOperasionalHeader;
use App\Models\TransaksiPerjalananDinasHeader;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashFlowService
{
    public function recalculateSnapshot(int $userId, string $periode): void
    {
        $periode = app(PeriodService::class)->normalize($periode);
        $isClosed = Periode::where('periode', $periode)->where('status', 'CLOSED')->exists();
        if ($isClosed && CashFlowSnapshot::where('user_id', $userId)->where('periode', $periode)->whereNotNull('saldo_akhir')->exists()) {
            return;
        }

        $saldoAwal   = $this->getSaldoAwal($userId, $periode);
        $totalMasuk  = $this->calculateTotalMasuk($userId, $periode);
        $totalKeluar = $this->calculateTotalKeluar($userId, $periode);
        
        // Rumus: Saldo Awal + Masuk + Pengembalian + Reimburse - Keluar
        $pengembalian = $this->calculatePengembalian($userId, $periode);
        $reimburse    = $this->calculateReimburse($userId, $periode);
        
        $saldoAkhir = $isClosed ? $saldoAwal + $totalMasuk + $pengembalian + $reimburse - $totalKeluar : null;

        CashFlowSnapshot::updateOrCreate(
            ['user_id' => $userId, 'periode' => $periode],
            [
                'saldo_awal'   => $saldoAwal,
                'total_masuk'  => $totalMasuk,
                'total_keluar' => $totalKeluar,
                'saldo_akhir'  => $saldoAkhir,
                'last_updated' => now(),
            ]
        );
    }

    protected function getSaldoAwal(int $userId, string $periode): float
    {
        $prevPeriode = app(PeriodService::class)->previous($periode);
        $prevRecord  = Periode::where('periode', $prevPeriode)->first();

        if ($prevRecord && $prevRecord->status === 'CLOSED') {
            $prevSnapshot = CashFlowSnapshot::where('user_id', $userId)
                ->where('periode', $prevPeriode)
                ->first();
            return $prevSnapshot ? (float) $prevSnapshot->saldo_akhir : 0;
        }
        return 0;
    }

    protected function calculateTotalMasuk(int $userId, string $periode): float
    {
        $bsIds = PengajuanBs::where('user_id', $userId)->pluck('id');
        return (float) ApprovalBsFinance::whereIn('pengajuan_bs_id', $bsIds)
            ->where('periode', $periode)
            ->sum('nominal_approved');
    }

    protected function calculateTotalKeluar(int $userId, string $periode): float
    {
        $cash = app(BsCashFlowService::class);
        $total = 0;
        foreach ([TransaksiOperasionalHeader::class, TransaksiPerjalananDinasHeader::class] as $model) {
            $total += $cash->expenses($model, $userId, $periode)->withSum('details', 'nominal')->get()->sum('details_sum_nominal');
        }
        return (float) ($total + $this->calculateEventKeluar($userId, $periode));
    }

    // 🎯 BARU: Hitung pengembalian sisa BS di periode ini
    protected function calculatePengembalian(int $userId, string $periode): float
    {
        return (float) PengajuanBs::where('user_id', $userId)
            ->where('settled_periode', $periode)
            ->sum('nominal_pengembalian');
    }

    // 🎯 BARU: Hitung reimburse di periode ini
    protected function calculateReimburse(int $userId, string $periode): float
    {
        return (float) PengajuanBs::where('user_id', $userId)
            ->where('settled_periode', $periode)
            ->sum('nominal_reimburse');
    }

    protected function calculateEventKeluar(int $userId, string $periode): float
    {
        $dalam = DB::table('perongkosan_event_dalam_kota')
            ->where('user_id', $userId)
            ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$periode])
            ->sum('nominal');

        $luar = DB::table('perongkosan_event_luar_kota')
            ->where('user_id', $userId)
            ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$periode])
            ->sum('nominal');

        return (float) ($dalam + $luar);
    }

    public function ensureSnapshotForPeriod(string $periode): void
    {
        foreach ($this->usersWithActivityIn($periode) as $userId) {
            $this->recalculateSnapshot($userId, $periode);
        }
    }

    protected function usersWithActivityIn(string $periode): array
    {
        $owners = PengajuanBs::where('periode', $periode)->pluck('user_id');

        $bsIdsUsed = TransaksiOperasionalHeader::where('periode', $periode)->whereNotNull('bs_id')->pluck('bs_id')
            ->merge(TransaksiPerjalananDinasHeader::where('periode', $periode)->whereNotNull('bs_id')->pluck('bs_id'));
        $ownersViaTrx = PengajuanBs::whereIn('id', $bsIdsUsed)->pluck('user_id');

        $eventUsers = DB::table('perongkosan_event_dalam_kota')->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$periode])->pluck('user_id')
            ->merge(DB::table('perongkosan_event_luar_kota')->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$periode])->pluck('user_id'));

        $snapshotOwners = CashFlowSnapshot::whereIn('periode', [$periode, app(PeriodService::class)->previous($periode)])->pluck('user_id');
        return $owners->merge($ownersViaTrx)->merge(app(BsCashFlowService::class)->owners())->merge($eventUsers)->merge($snapshotOwners)->unique()->values()->all();
    }

    public function recalculateAllPeriods(int $userId): void
    {
        $periods = Periode::orderBy('periode', 'asc')->pluck('periode');
        foreach ($periods as $periode) {
            $this->recalculateSnapshot($userId, $periode);
        }
        $this->recalculateSnapshot($userId, \App\Support\AppTime::now()->format('Y-m'));
    }

    public function getSnapshot(int $userId, string $periode): ?CashFlowSnapshot
    {
        return CashFlowSnapshot::where('user_id', $userId)->where('periode', $periode)->first();
    }
}

~~~~

## FILE: app/Services/PlafonService.php

SHA-256: `1c828a73e3ebc85b5218765c88af11f3e33b4006959a75a0e12074d631db158b`

~~~~php
<?php

namespace App\Services;

use App\Models\Wilayah;
use App\Models\SettingPlafonPerjalananDinas;
use Carbon\Carbon;

class PlafonService
{
    /**
     * Resolve wilayah ke level Provinsi.
     */
    public function resolveProvinsiId($wilayahId)
    {
        if (!$wilayahId) return null;

        $w = Wilayah::find($wilayahId);
        if (!$w) return null;

        return $w->parent_id ?? $w->id;
    }

    /**
     * Sesuai ENUM database: Reguler / Sabtu / Libur
     */
    public function mapHariEvent($tanggal, $isLibur)
    {
        if ($isLibur) return 'Libur';
        return Carbon::parse($tanggal)->isSaturday() ? 'Sabtu' : 'Reguler';
    }

    /**
     * PLAFON PERJALANAN DINAS (Level + Prov Asal + Prov Tujuan + Effective Date)
     */
    public function getPlafonPerjalananDinas($user, $tujuanWilayahId, $tanggal)
    {
        if (!$user->level_id) return null;

        $provTujuan = $this->resolveProvinsiId($tujuanWilayahId);
        if (!$provTujuan) return null;

        $provAsal = $this->resolveProvinsiId($user->homebase_id);
        if (!$provAsal) return null;

        // Level 1: Exact match
        $plafon = SettingPlafonPerjalananDinas::where('level_id', $user->level_id)
            ->where('provinsi_awal_id', $provAsal) // sesuaikan nama kolom asli DB Anda
            ->where('provinsi_tujuan_id', $provTujuan)
            ->whereDate('tanggal_berlaku', '<=', $tanggal)
            ->orderBy('tanggal_berlaku', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        // Level 2: Fallback - semua tujuan dari provinsi asal
        if (!$plafon) {
            $plafon = SettingPlafonPerjalananDinas::where('level_id', $user->level_id)
                ->where('provinsi_awal_id', $provAsal)
                ->whereNull('provinsi_tujuan_id')
                ->whereDate('tanggal_berlaku', '<=', $tanggal)
                ->orderBy('tanggal_berlaku', 'desc')
                ->orderBy('id', 'desc')
                ->first();
        }

        // Level 3: Fallback - default level (semua rute)
        if (!$plafon) {
            $plafon = SettingPlafonPerjalananDinas::where('level_id', $user->level_id)
                ->whereNull('provinsi_awal_id')
                ->whereNull('provinsi_tujuan_id')
                ->whereDate('tanggal_berlaku', '<=', $tanggal)
                ->orderBy('tanggal_berlaku', 'desc')
                ->orderBy('id', 'desc')
                ->first();
        }

        return $plafon;
    }


    public function resolveKelompokHariKerjaId($user)
{
    if (!$user->hari_kerja_user) return null;

    // Jika sudah berupa angka (ID), pakai langsung
    if (is_numeric($user->hari_kerja_user)) {
        return (int) $user->hari_kerja_user;
    }

    // Jika berupa code seperti '5 HK', cari ID-nya di tabel weekday_group
    $wg = \App\Models\WeekdayGroup::where('code', $user->hari_kerja_user)->first();
    return $wg ? $wg->id : null;
}
    /**
     * 🎯 PERBAIKAN UTAMA: PLAFON EVENT MARKETING
     * Sekarang memfilter berdasarkan Kelompok Hari Kerja user (5 HK / 6 HK)
     */
    public function getPlafonEvent($modelClass, $user, $tanggal, $isLibur, $jamKerjaEventId)
{
    if (!$user->level_id) return null;

    $kelompokId = $this->resolveKelompokHariKerjaId($user);
    if (!$kelompokId) return null; // graceful: profil hari kerja kosong / tidak dikenali

    return $modelClass::where('level_id', $user->level_id)
        ->where('kelompok_hari_kerja_id', $kelompokId) // ✅ PAKAI HASIL RESOLVER (ID ketemu ID)
        ->where('hari_event', $this->mapHariEvent($tanggal, $isLibur))
        ->where('jam_kerja_event_id', $jamKerjaEventId)
        ->whereDate('tanggal_berlaku', '<=', $tanggal)
        ->orderBy('tanggal_berlaku', 'desc')
        ->orderBy('id', 'desc')
        ->first();
}

    /**
     * SOFT CONTROL: bandingkan nominal dengan plafon
     */
    public function check($plafon, $nominal): array
    {
        if (!$plafon) {
            return [
                'is_over'      => false,
                'plafon_value' => null,
                'selisih'      => 0,
                'sisa'         => 0,
                'message'      => '⚠️ Plafon untuk kombinasi ini belum disetting.',
            ];
        }

        $plafonValue = (float) $plafon->default_value;
        $over = $nominal > $plafonValue;

        return [
            'is_over'      => $over,
            'plafon_value' => $plafonValue,
            'selisih'      => $over ? ($nominal - $plafonValue) : 0,
            'sisa'         => $over ? 0 : ($plafonValue - $nominal),
            'message'      => $over ? '⚠️ Melebihi plafon!' : '✅ Masih dalam plafon.',
        ];
    }

    public function checkPerjalananDinas($user, $tujuanWilayahId, $tanggal, $nominal): array
    {
        return $this->check(
            $this->getPlafonPerjalananDinas($user, $tujuanWilayahId, $tanggal),
            $nominal
        );
    }

    public function checkEvent($modelClass, $user, $tanggal, $isLibur, $jamKerjaEventId, $nominal): array
    {
        return $this->check(
            $this->getPlafonEvent($modelClass, $user, $tanggal, $isLibur, $jamKerjaEventId),
            $nominal
        );
    }
}
~~~~

## FILE: app/Services/StockCashFlowService.php

SHA-256: `3b5cb5a6de5a3e97b5733663eb7e53aca39526992cf97be12befc7be44660e0f`

~~~~php
<?php

namespace App\Services;

use App\Models\Periode;
use App\Models\PerongkosanEventDalamKota;
use App\Models\PerongkosanEventLuarKota;
use App\Models\StockCashFlowSnapshot;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class StockCashFlowService
{
    private const EVENTS = [
        PerongkosanEventDalamKota::class => 'Event Dalam Kota',
        PerongkosanEventLuarKota::class => 'Event Luar Kota',
    ];

    public function __construct(private PeriodService $periods, private BsCashFlowService $bsCash) {}

    public function events(string $model, int $userId, string $periode)
    {
        $periode = $this->periods->normalize($periode);
        return $model::where('user_id', $userId)
            ->where('tanggal', '>=', $periode.'-01')
            ->where('tanggal', '<', $this->periods->next($periode).'-01');
    }

    public function recalculateSnapshot(int $userId, string $periode): StockCashFlowSnapshot
    {
        $periode = $this->periods->normalize($periode);
        return DB::transaction(function () use ($userId, $periode) {
            $period = Periode::where('periode', $periode)->lockForUpdate()->first();
            DB::table('user')->where('id', $userId)->lockForUpdate()->first();
            $existing = StockCashFlowSnapshot::where('user_id', $userId)->where('periode', $periode)->first();
            if ($period?->status === 'CLOSED' && $existing?->saldo_akhir !== null) return $existing;

            $bs = $this->bsCash->recalculateSnapshot($userId, $periode);
            $opening = Money::minor($bs->saldo_awal);
            $ready = $bs->opening_final;
            $previous = $this->periods->previous($periode);
            if (Periode::where('periode', $previous)->where('status', 'CLOSED')->exists()) {
                $prior = $this->recalculateSnapshot($userId, $previous);
                $opening = Money::minor($prior->saldo_akhir);
                $ready = $ready && $prior->opening_final;
            }

            $local = Money::minor($this->events(PerongkosanEventDalamKota::class, $userId, $periode)->sum('nominal'));
            $outOfTown = Money::minor($this->events(PerongkosanEventLuarKota::class, $userId, $periode)->sum('nominal'));
            return StockCashFlowSnapshot::updateOrCreate(
                ['user_id' => $userId, 'periode' => $periode],
                [
                    'saldo_awal' => Money::decimal($opening),
                    'total_masuk' => $bs->total_masuk,
                    // Retain obsolete columns for compatibility with existing databases.
                    'total_masuk_reimbursement' => '0.00',
                    'total_keluar_bs' => $bs->total_keluar,
                    'total_event_dalam' => Money::decimal($local),
                    'total_event_luar' => Money::decimal($outOfTown),
                    'total_keluar_pribadi' => '0.00',
                    'total_keluar' => Money::decimal(Money::minor($bs->total_keluar) + $local + $outOfTown),
                    'includes_personal_funds' => false,
                    'penyesuaian' => $bs->penyesuaian,
                    // Preserve the frozen BS closing balance when bootstrapping historical periods.
                    // Carry prior event cash forward once while keeping the BS ledger separate.
                    'saldo_akhir' => $period?->status === 'CLOSED'
                        ? Money::decimal(Money::minor($bs->saldo_akhir) + $opening - Money::minor($bs->saldo_awal) - $local - $outOfTown)
                        : null,
                    'opening_final' => $ready,
                    'last_updated' => now(),
                ]
            );
        }, 3);
    }

    public function owners(): array
    {
        $owners = collect($this->bsCash->owners())->merge(StockCashFlowSnapshot::pluck('user_id'));
        foreach (array_keys(self::EVENTS) as $model) $owners = $owners->merge($model::distinct()->pluck('user_id'));
        return $owners->unique()->sort()->values()->all();
    }

    public function ensureSnapshotForPeriod(string $periode): void
    {
        foreach ($this->owners() as $owner) $this->recalculateSnapshot((int) $owner, $periode);
    }

    public function details(int $userId, string $periode): array
    {
        $this->recalculateSnapshot($userId, $periode);
        $result = $this->bsCash->details($userId, $periode);
        foreach (self::EVENTS as $model => $label) {
            foreach ($this->events($model, $userId, $periode)->orderBy('tanggal')->orderBy('id')->get() as $event) {
                $result['keluar'][] = [
                    'type' => $label,
                    'date' => $event->tanggal,
                    'description' => ($event->transaksi_id ?? 'Event #'.$event->id).($event->description ? ' / '.$event->description : ''),
                    'nominal' => $event->nominal,
                    'url' => $event instanceof PerongkosanEventDalamKota
                        ? route('perongkosan_event_dalam_kota.show', $event->id)
                        : route('perongkosan_event_luar_kota.show', $event->id),
                ];
            }
        }
        $result['masuk'] = collect($result['masuk'])->sortBy('date')->values()->all();
        $result['keluar'] = collect($result['keluar'])->sortBy('date')->values()->all();
        return $result;
    }
}

~~~~

## FILE: app/Services/TransactionFundingService.php

SHA-256: `182ab8b5da6b6fece9d686396c679d25dbaee32098f2b56b1f7fefa63a6f5e23`

~~~~php
<?php

namespace App\Services;

use App\Models\PengajuanBs;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransactionFundingService
{
    public static function rules(): array
    {
        return [
            'funding_source' => 'sometimes|in:BS',
            'bs_id' => 'prohibited',
            'bs_owner_id' => 'nullable|integer|exists:user,id',
        ];
    }

    public function owners(int $inputterId, bool $travel)
    {
        $ids = collect([$inputterId]);
        if ($travel) {
            $ids = $ids->merge($this->sharedBs($inputterId)->pluck('user_id'));
        }
        return User::whereIn('id', $ids->unique())->orderBy('nama')->get();
    }

    public function resolve(Request $request, bool $travel, ?Model $document = null): int
    {
        $inputterId = (int) ($document?->user_id ?? $request->user()->id);
        $ownerId = (int) ($request->input('bs_owner_id') ?: ($document?->bsCashOwnerId() ?? $inputterId));
        $this->assertAccess($inputterId, $ownerId, $travel);
        return $ownerId;
    }

    public function assertAccess(int $inputterId, int $ownerId, bool $travel): void
    {
        if ($ownerId === $inputterId) return;
        if (!$travel || !$this->sharedBs($inputterId)->where('user_id', $ownerId)->exists()) {
            throw ValidationException::withMessages(['bs_owner_id' => 'Anda tidak berhak menggunakan saldo BS pemilik ini. Saldo grup hanya dapat dipakai anggota untuk Perjalanan Dinas.']);
        }
    }

    private function sharedBs(int $inputterId)
    {
        return PengajuanBs::where('tipe_pengajuan', 'perjalanan_dinas')->where('status', 'DISBURSED')
            ->whereHas('financeApprovals')
            ->whereHas('members', fn ($q) => $q->where('user_id', $inputterId));
    }
}

~~~~
