# Kode existing: inti-model-route-penomoran-izin, bagian 1

Snapshot referensi. Bukan implementasi fitur baru. Semua file di bawah disertakan utuh.

## FILE: app/Models/ApprovalBsFinance.php

SHA-256: `ade8ff3f1242ab1ae67b66b9a4bd6bb4bb9726eb665e8376e83eff0bb89dbc65`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalBsFinance extends Model
{
    use HasFactory;

    // 🎯 PERBAIKAN: Tambahkan baris ini agar Laravel mencari tabel yang benar
    protected $table = 'approval_bs_finance'; 
    
    protected $guarded = ['id'];

    public function pengajuanBs() { 
        return $this->belongsTo(PengajuanBs::class, 'pengajuan_bs_id'); 
    }
    
    public function financeUser() { 
        return $this->belongsTo(User::class, 'finance_user_id'); 
    }
}
~~~~

## FILE: app/Models/Concerns/UsesBsBalance.php

SHA-256: `34cc29f7a4fd975649c3215ec258eaa6b55bc788ac10c354da99216596538694`

~~~~php
<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait UsesBsBalance
{
    public function bsOwner()
    {
        return $this->belongsTo(User::class, 'bs_owner_id');
    }

    public function bsCashOwnerId(): ?int
    {
        $owner = $this->bs_owner_id ?? $this->bonSementara?->user_id;
        return $owner === null ? null : (int) $owner;
    }

    public function scopeForBsOwner(Builder $query, int $ownerId): Builder
    {
        return $query->where(fn ($q) => $q->where('bs_owner_id', $ownerId)
            ->orWhere(fn ($legacy) => $legacy->whereNull('bs_owner_id')
                ->whereHas('bonSementara', fn ($bs) => $bs->where('user_id', $ownerId))));
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->when(!$user->canSeeAllTransactions(), fn ($q) => $q
            ->where(fn ($visible) => $visible->where('user_id', $user->id)
                ->orWhere(fn ($owned) => $owned->forBsOwner((int) $user->id))));
    }
}

~~~~

## FILE: app/Models/CostCategory.php

SHA-256: `37e44f93e4c64dff71bce6f768b5b5248b02cad23da7003531ece9167a991279`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CostCategory extends Model
{
    //

    protected $table = 'cost_category';

    public $timestamps = false;

    protected $fillable = [
        'nama',
        'description',
        'is_active'
    ];
}

~~~~

## FILE: app/Models/PengajuanBs.php

SHA-256: `3441409a184ac9ff6fda89a6336d839b5ef8267b289e23b7e529ecdacf76eb6c`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanBs extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_bs';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal'    => 'datetime',
        'settled_at' => 'datetime',
    ];

    /* ================= RELATIONS ================= */

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function atasan()
    {
        return $this->belongsTo(User::class, 'atasan_user_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'pengajuan_bs_members', 'pengajuan_bs_id', 'user_id');
    }

    public function financeApprovals()
    {
        return $this->hasMany(ApprovalBsFinance::class, 'pengajuan_bs_id');
    }

    public function operasionalSales()
    {
        return $this->hasMany(TransaksiOperasionalHeader::class, 'bs_id');
    }

    public function perjalananDinas()
    {
        return $this->hasMany(TransaksiPerjalananDinasHeader::class, 'bs_id');
    }

    public function logs()
    {
        return $this->hasMany(BsActivityLog::class, 'pengajuan_bs_id');
    }

    /* ================= ACCESSORS ================= */

    /** Label tampilan tipe pengajuan */
    public function getTipeLabelAttribute(): string
    {
        return $this->tipe_pengajuan === 'perjalanan_dinas'
            ? 'Perjalanan Dinas'
            : 'Operasional Sales';
    }

    /** Apakah BS ini tipe perjalanan dinas? */
    public function isPerjalananDinas(): bool
    {
        return $this->tipe_pengajuan === 'perjalanan_dinas';
    }
}
~~~~

## FILE: app/Models/Periode.php

SHA-256: `6c3984f42ce6ed90a868fd28b4ed46696fb9cf2d7447a981b8ac07eccd3ae003`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periode extends Model
{
    protected $table = 'periode';
    protected $guarded = ['id'];

    protected $casts = [
        'closed_at'   => 'datetime',
        'reopened_at' => 'datetime',
    ];
}
~~~~

## FILE: app/Models/Role.php

SHA-256: `1e877a928e709981e698a3650b6741c6afa6fa1e70f9aa23360541bceee0a90f`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // 1. Definisikan nama tabel asli di database
    protected $table = 'roles';

    // 2. Tentukan primary key tabel
    protected $primaryKey = 'id';

    // 3. Matikan timestamps karena tidak ada kolom created_at & updated_at
    public $timestamps = false;

    // 4. Daftarkan kolom yang boleh diisi secara massal (Mass Assignment)
    protected $fillable = [
        'code',
        'nama',
        'is_active',
    ];

    /**
     * Relasi ke tabel User (One-to-Many)
     * Satu Role bisa dimiliki oleh banyak User
     */
    public function users()
    {
        // Hubungkan kembali ke model User melalui foreign key 'roles_id'
        return $this->hasMany(User::class, 'roles_id', 'id');
    }

//     public function role()
// {
//     // User terhubung ke Role melalui kolom 'roles_id'
//     return $this->belongsTo(Role::class, 'roles_id', 'id');
// }
}
~~~~

## FILE: app/Models/TransaksiOperasionalDetail.php

SHA-256: `80add31c2d2b067ec121603c72d26dc59f27eb90c3bac22038d46a814104aade`

~~~~php
<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiOperasionalDetail extends Model
{
    //

    use HasFactory;

    protected $table = 'transaksi_operasional_area_sales_detail';

    public $timestamps = false;

    protected $fillable = [
        'transaksi_id',
        'cost_category_id',
        'remark',
        'nominal'
    ];


    // Relasi ke Header

    public function header()
    {
        return $this->belongsTo(TransaksiOperasionalHeader::class, 'transaksi_id', 'transaksi_id');
    }

    public function costCategory()
    {
        return $this->belongsTo(CostCategory::class, 'cost_category_id', 'id');
    }


}

~~~~

## FILE: app/Models/TransaksiOperasionalHeader.php

SHA-256: `10c789cfe834f9864ebfc209f50f835151db12be2fe05054156fe9828385084e`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiOperasionalHeader extends Model
{
    use HasFactory, \App\Models\Concerns\UsesBsBalance;

    protected $table = 'transaksi_operasional_area_sales_header';
    public $timestamps = false;

    protected $casts = ['validated_at' => 'datetime'];

    // 🎯 PERBAIKAN 3: Tambahkan 'bs_id' agar bisa di-save via create() / update()
    protected $fillable = [
        'validated_at', 'validated_by', 'bs_owner_id',
        'transaksi_id',
        'tanggal',
        'user_id',
        'bs_id', 
        'periode',
        'description',
        'status', // 🎯 TAMBAHKAN INI
    ];

    public function details()
    {
        return $this->hasMany(TransaksiOperasionalDetail::class, 'transaksi_id', 'transaksi_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function bonSementara() {
        return $this->belongsTo(PengajuanBs::class, 'bs_id');
    }
}

~~~~

## FILE: app/Models/TransaksiPerjalananDinasDetail.php

SHA-256: `5ff69546d8f602ba3303a834258adec468190b06df4e5dfc558807060f22c556`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // 🎯 TAMBAHKAN

class TransaksiPerjalananDinasDetail extends Model
{
    use HasFactory, SoftDeletes; // 🎯 TAMBAHKAN SoftDeletes

    protected $table = 'transaksi_perjalanan_dinas_detail';
    public $timestamps = false;
    
    // 🎯 PENTING: Beritahu Laravel hanya pakai deleted_at, jangan cari updated_at
    const DELETED_AT = 'deleted_at'; 

    protected $fillable = [
        'transaksi_id',
        'cost_category_id',
        'remark',
        'nominal'
    ];

    public function header()
    {
        return $this->belongsTo(TransaksiPerjalananDinasHeader::class, 'transaksi_id', 'transaksi_id');
    }

    public function costCategory()
    {
        return $this->belongsTo(CostCategory::class, 'cost_category_id', 'id');
    }
}
~~~~

## FILE: app/Models/TransaksiPerjalananDinasHeader.php

SHA-256: `d8c2d798481fb739da006f268fdc07b51d883e2def2bb33b58e2033ca4c66f35`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiPerjalananDinasHeader extends Model
{
    use HasFactory, \App\Models\Concerns\UsesBsBalance;

    protected $table = 'transaksi_perjalanan_dinas_header';
    public $timestamps = false;
    protected $casts = ['validated_at' => 'datetime', 'submitted_at' => 'datetime', 'owner_reviewed_at' => 'datetime', 'approval_version' => 'integer'];
    protected $attributes = ['status' => 'DRAFT'];

    protected $fillable = [
        'status', 'validated_at', 'validated_by', 'bs_owner_id',
        'approval_version', 'submitted_at', 'submitted_by', 'owner_reviewed_at', 'owner_reviewed_by', 'owner_rejection_reason',
        'transaksi_id',
        'document_no',
        'tanggal',
        'no_referensi_bs',
        'bs_id',
        'user_id',
        'tujuan_dinas_id',   // ✅ diperbaiki dari 'tujuan_dinas'
        'jarak_km',          // ✅ ditambah
        'durasi_jam',        // ✅ ditambah
        'description',
        'periode',
        'is_over_plafon',
        'plafon_value',      // ✅ ditambah (dipakai di $header->update())
        'selisih_plafon',    // ✅ ditambah
    ];

    public function details()
    {
        return $this->hasMany(TransaksiPerjalananDinasDetail::class, 'transaksi_id', 'transaksi_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function bonSementara()
    {
        return $this->belongsTo(PengajuanBs::class, 'bs_id', 'id');
    }

    public function tujuanDinas()
    {
        return $this->belongsTo(Wilayah::class, 'tujuan_dinas_id', 'id');
    }

    public function approvalLogs()
    {
        return $this->hasMany(BsUsageApprovalLog::class, 'transaction_id')->orderBy('id');
    }

    public function needsOwnerApproval(): bool
    {
        return $this->bsCashOwnerId() !== null && $this->bsCashOwnerId() !== (int) $this->user_id;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'DRAFT' => 'Draft',
            'PENDING_OWNER' => 'Menunggu Persetujuan Pemohon',
            'REJECTED_OWNER' => 'Ditolak Pemohon',
            'VALIDATED' => 'Dikonfirmasi',
            default => $this->status,
        };
    }
}

~~~~

## FILE: app/Models/User.php

SHA-256: `8b277bda2ddb959555e52a1e9bd354b593efa46267c91e1ff94867755437990f`

~~~~php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use App\Models\Company;
use App\Models\Role;
use App\Models\Level;
use App\Models\Position;
use App\Models\Wilayah;

class User extends Authenticatable
{
    use HasRoles, Notifiable;

    protected $table = 'user';
    public $timestamps = false;

    protected $fillable = [
        'NIK', 'nama', 'company_id', 'roles_id', 'level_id', 'position_id',
        'homebase_id', 'kost', 'bbm', 'uang_makan', 'hari_kerja_user',
        'atasan_user_id', 'username', 'password', 'is_active',
    ];

    public function company() { return $this->belongsTo(Company::class, 'company_id'); }
    public function role() { return $this->belongsTo(Role::class, 'roles_id'); }
    public function level() { return $this->belongsTo(Level::class, 'level_id', 'id'); }
    public function position() { return $this->belongsTo(Position::class, 'position_id'); }
    public function homebase() { return $this->belongsTo(Wilayah::class, 'homebase_id', 'id'); }
    public function atasan() { return $this->belongsTo(User::class, 'atasan_user_id'); }
    public function bawahan() { return $this->hasMany(User::class, 'atasan_user_id'); }
    public function pengajuanBs() { return $this->hasMany(PengajuanBs::class, 'user_id'); }
    public function bsMembers() { return $this->belongsToMany(PengajuanBs::class, 'pengajuan_bs_members', 'user_id', 'pengajuan_bs_id'); }

    public function canAccessPortal(): bool
    {
        return in_array($this->role?->code, ['ADMIN', 'ADMINISTRATOR'], true);
    }

    public function dashboardRouteName(): string
    {
        return $this->canAccessPortal() ? 'dashboard' : 'dashboard.transaksi';
    }

    public function canSeeAllTransactions(): bool
    {
         return in_array($this->role->code ?? '', ['ADMIN', 'DIR', 'FINANCE']);
    }

    public function canAccessTransaction($transaksi): bool
    {
        return $this->canSeeAllTransactions() || $transaksi->user_id === $this->id;
    }

    public function canViewBsTransaction($transaksi): bool
    {
        return $this->canAccessTransaction($transaksi)
            || $transaksi->bsCashOwnerId() === (int) $this->id;
    }

    public function hasActiveCompany(): bool
    {
        if (!$this->relationLoaded('company')) {
            $this->load('company');
        }

        return $this->company !== null && $this->company->isActive();
    }

    public function isActive(): bool
    {
        $value = $this->is_active;

        if (is_string($value)) {
            return in_array(strtoupper(trim($value)), ['Y', 'YES', '1', 'TRUE', 'AKTIF'], true);
        }

        return (bool) $value;
    }

    public function hasActiveAtasan(): bool
    {
        if (!$this->relationLoaded('atasan')) {
            $this->load('atasan');
        }

        if (!$this->atasan) {
            return true;
        }

        return $this->atasan->isActive();
    }
}

~~~~

## FILE: app/Providers/AppServiceProvider.php

SHA-256: `4d828d71f56d6c64c1541f8e51ba01c6d2f53ab19f75c1b51c2cf54ba1a836fe`

~~~~php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use App\Models\User;
use App\Models\Role;
use App\Observers\UserObserver;
use App\Observers\RoleObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Observer: sinkronisasi otomatis role master <-> Spatie
        User::observe(UserObserver::class);
        Role::observe(RoleObserver::class);

        // 🎯 HAK APPROVE ATASAN = BERASAL DARI RELASI, BUKAN DARI ROLE
        // Bernilai true hanya jika user ini merupakan atasan dari minimal 1 user lain
        Gate::define('approval-atasan', function (User $user) {
            return User::where('atasan_user_id', $user->id)->exists();
        });

        // 🎯 Pagination bergaya Bootstrap 5 (bukan default Tailwind Laravel)
        Paginator::useBootstrapFive();
    }
}
~~~~

## FILE: app/Services/DocumentNumberService.php

SHA-256: `45b6a475ec1e7fa7de0e75c8065e84048b5bd7f4d8552f741999b0fa78c471f5`

~~~~php
<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

class DocumentNumberService
{
    public const TYPES = [
        'BS' => ['pengajuan_bs', 'document_no', 'tanggal'],
        'CAIR' => ['approval_bs_finance', 'document_no', 'tanggal_cair'],
        'OAS' => ['transaksi_operasional_area_sales_header', 'transaksi_id', 'tanggal'],
        'PD' => ['transaksi_perjalanan_dinas_header', 'transaksi_id', 'tanggal'],
    ];

    /** Reserve and save the document in the same transaction. */
    public function next(string $type, DateTimeInterface|string $date): string
    {
        if (!isset(self::TYPES[$type])) {
            throw new InvalidArgumentException('Jenis nomor dokumen tidak dikenal.');
        }
        if (DB::transactionLevel() === 0) {
            throw new LogicException('Nomor dokumen harus dibuat dalam transaksi database.');
        }

        [$table, $column] = self::TYPES[$type];
        $date = CarbonImmutable::parse($date);
        $long = in_array($type, ['BS', 'CAIR'], true);
        $prefix = $long ? $type.'-'.$date->format('Ymd').'-' : $type.$date->format('ymd');
        $key = ['type' => $type, 'date' => $date->toDateString()];

        // A no-op upsert takes an exclusive lock even for an existing key.
        // INSERT IGNORE takes shared duplicate-key locks that can deadlock
        // when simultaneous callers later upgrade to FOR UPDATE.
        DB::table('document_number_sequences')->upsert([$key + ['last_number' => 0]], ['type', 'date'], ['date']);
        $sequence = DB::table('document_number_sequences')->where($key)->lockForUpdate()->first();
        $last = (int) $sequence->last_number;

        // Seed from legacy numbers, including numbers whose date was later edited.
        // Use a locking read to see the latest committed rows on MySQL.
        foreach (DB::table($table)->where($column, 'like', $prefix.'%')->lockForUpdate()->pluck($column) as $number) {
            $suffix = substr($number, strlen($prefix));
            if (ctype_digit($suffix)) {
                $last = max($last, (int) $suffix);
            }
        }

        DB::table('document_number_sequences')->where($key)->update(['last_number' => ++$last]);

        return $prefix.str_pad((string) $last, $long ? 3 : 2, '0', STR_PAD_LEFT);
    }
}

~~~~

## FILE: app/Services/PeriodService.php

SHA-256: `d5baf731e6492d374ae3061b1c7f2c5aceadf1691cc1161328dadfbe8adf4d5d`

~~~~php
<?php

namespace App\Services;

use App\Models\Periode;
use Illuminate\Validation\ValidationException;

class PeriodService
{
    public function normalize(string $periode): string
    {
        if (!preg_match('/^(\d{4})-?(0[1-9]|1[0-2])$/D', $periode, $parts)) {
            throw ValidationException::withMessages(['periode' => 'Periode harus berupa YYYYMM atau YYYY-MM dengan bulan yang valid.']);
        }
        return $parts[1].'-'.$parts[2];
    }

    public function key(string $periode): string
    {
        return str_replace('-', '', $this->normalize($periode));
    }

    public function previous(string $periode): string
    {
        return \Carbon\CarbonImmutable::createFromFormat('!Y-m', $this->normalize($periode))->subMonth()->format('Y-m');
    }

    public function next(string $periode): string
    {
        return \Carbon\CarbonImmutable::createFromFormat('!Y-m', $this->normalize($periode))->addMonth()->format('Y-m');
    }

    /** Caller must hold a database transaction until its write has completed. */
    public function lockOpen(string $periode): Periode
    {
        $periode = $this->normalize($periode);
        $record = Periode::where('periode', $periode)->lockForUpdate()->first();
        if (!$record) {
            if ($periode < $this->current()) {
                throw ValidationException::withMessages(['tanggal' => "Periode {$periode} belum tersedia atau sudah ditutup."]);
            }
            $record = Periode::create(['periode' => $periode, 'status' => 'OPEN']);
        }
        if ($record->status !== 'OPEN') {
            throw ValidationException::withMessages(['tanggal' => "Periode {$periode} sudah ditutup."]);
        }
        return $record;
    }
    /** Bulan riil saat ini, format YYYY-MM */
    public function current(): string
    {
        return \App\Support\AppTime::now()->format('Y-m');
    }

    /** Turunkan periode dari tanggal transaksi (business rule rev4) */
    public function ofDate(string $tanggal): string
    {
        return substr($tanggal, 0, 7);
    }

    /**
     * Status sebuah periode.
     * Jika barisnya belum ada (auto-roll belum menyentuh):
     *  - bulan lampau      => dianggap CLOSED (mencegah input ke bulan liar)
     *  - bulan ini / depan => dianggap OPEN
     */
    public function statusOf(string $periode): string
    {
        $periode = $this->normalize($periode);
        $row = Periode::where('periode', $periode)->first();

        return $row ? $row->status : ($periode < $this->current() ? 'CLOSED' : 'OPEN');
    }

    public function isOpen(string $periode): bool
    {
        return $this->statusOf($periode) === 'OPEN';
    }

    /** 🎯 GATE BACKEND: lempar error validasi jika periode tertutup */
    public function assertOpen(string $periode): void
    {
        if (! $this->isOpen($periode)) {
            throw ValidationException::withMessages([
                'tanggal' => "Periode {$periode} sudah DITUTUP (tutup buku). Transaksi tidak dapat disimpan — pilih tanggal pada periode yang masih OPEN.",
            ]);
        }
    }
}

~~~~

## FILE: app/Support/AppTime.php

SHA-256: `531959067437a244e87c594e9fe10672cd253808e45408e37fe5d1fcb48f617a`

~~~~php
<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use DateTimeInterface;

final class AppTime
{
    public static function timezone(): string
    {
        return config('app.display_timezone', 'Asia/Jakarta');
    }

    /** Local calendar time for business dates, periods, and document numbers. */
    public static function now(): CarbonImmutable
    {
        return CarbonImmutable::now(self::timezone());
    }

    /** Stored timestamps use the application's UTC clock, including legacy rows. */
    public static function format(DateTimeInterface|string|null $value, string $format = 'd M Y, H:i:s'): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $local = CarbonImmutable::parse($value, config('app.timezone'))->setTimezone(self::timezone());
        $label = match (self::timezone()) {
            'Asia/Jakarta', 'Asia/Pontianak' => 'WIB',
            'Asia/Makassar' => 'WITA',
            'Asia/Jayapura' => 'WIT',
            default => $local->format('T'),
        };

        return $local->format($format).' '.$label;
    }
}

~~~~

## FILE: app/Support/Money.php

SHA-256: `9b51f058206f18068afa0b6635fb7e3c8016fecb495298bdabd3b7e7c32f2e20`

~~~~php
<?php

namespace App\Support;

use InvalidArgumentException;

final class Money
{
    public static function minor(int|float|string|null $amount): int
    {
        $value = is_float($amount) ? number_format($amount, 2, '.', '') : (string) ($amount ?? '0');
        if (!preg_match('/^(-?)(\d{1,13})(?:\.(\d{1,2}))?$/D', $value, $parts)) {
            throw new InvalidArgumentException('Nominal harus berupa angka dengan maksimal dua angka desimal.');
        }
        $minor = ((int) $parts[2] * 100) + (int) str_pad($parts[3] ?? '', 2, '0');
        return ($parts[1] === '-' ? -1 : 1) * $minor;
    }

    public static function decimal(int $minor): string
    {
        return ($minor < 0 ? '-' : '').intdiv(abs($minor), 100).'.'.str_pad((string) (abs($minor) % 100), 2, '0', STR_PAD_LEFT);
    }
}

~~~~

## FILE: bootstrap/app.php

SHA-256: `84089c20a2d2a2538d9571b0ae971a690d2491d3d5e270dddcfd1586d5f3c96a`

~~~~php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
        ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectUsersTo(fn (\Illuminate\Http\Request $request) => route($request->user()->dashboardRouteName()));

        $middleware->alias([
            'bs.atomic' => \App\Http\Middleware\AtomicBsRequest::class,
            'event.atomic' => \App\Http\Middleware\AtomicEventRequest::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'company.active' => \App\Http\Middleware\CheckActiveCompany::class,
            'atasan.active'  => \App\Http\Middleware\CheckActiveAtasan::class, // 🎯 BARU
        ]);
        

         $middleware->web(append: [
            \App\Http\Middleware\InjectNotificationBell::class, // 🎯 Lonceng di semua halaman
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

~~~~

## FILE: bootstrap/providers.php

SHA-256: `f720e185207a343d4c19fc99dbb317a4187efbcd7d38a303a695bbdd77ffb943`

~~~~php
<?php

use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
];

~~~~

## FILE: composer.json

SHA-256: `2a52c538f1a8006668e6d6fa26ca97d91e9f78abd957b67712b11ec2db787df1`

~~~~json
{
    "$schema": "https://getcomposer.org/schema.json",
    "name": "laravel/laravel",
    "type": "project",
    "description": "The skeleton application for the Laravel framework.",
    "keywords": ["laravel", "framework"],
    "license": "MIT",
    "require": {
        "php": "^8.2",
        "laravel/framework": "^12.0",
        "laravel/sanctum": "^4.0",
        "laravel/tinker": "^2.10.1",
        "maatwebsite/excel": "^3.1",
        "spatie/laravel-permission": "^6.25"
    },
    "require-dev": {
        "fakerphp/faker": "^1.23",
        "laravel/pail": "^1.2.2",
        "laravel/pint": "^1.24",
        "laravel/sail": "^1.41",
        "mockery/mockery": "^1.6",
        "nunomaduro/collision": "^8.6",
        "phpunit/phpunit": "^11.5.50"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    },
    "scripts": {
        "setup": [
            "composer install",
            "@php -r \"file_exists('.env') || copy('.env.example', '.env');\"",
            "@php artisan key:generate",
            "@php artisan migrate --force",
            "npm install",
            "npm run build"
        ],
        "dev": [
            "Composer\\Config::disableProcessTimeout",
            "npx concurrently -c \"#93c5fd,#c4b5fd,#fb7185,#fdba74\" \"php artisan serve\" \"php artisan queue:listen --tries=1 --timeout=0\" \"php artisan pail --timeout=0\" \"npm run dev\" --names=server,queue,logs,vite --kill-others"
        ],
        "test": [
            "@php artisan config:clear --ansi",
            "@php artisan test"
        ],
        "post-autoload-dump": [
            "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
            "@php artisan package:discover --ansi"
        ],
        "post-update-cmd": [
            "@php artisan vendor:publish --tag=laravel-assets --ansi --force"
        ],
        "post-root-package-install": [
            "@php -r \"file_exists('.env') || copy('.env.example', '.env');\""
        ],
        "post-create-project-cmd": [
            "@php artisan key:generate --ansi",
            "@php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\"",
            "@php artisan migrate --graceful --ansi"
        ],
        "pre-package-uninstall": [
            "Illuminate\\Foundation\\ComposerScripts::prePackageUninstall"
        ]
    },
    "extra": {
        "laravel": {
            "dont-discover": []
        }
    },
    "config": {
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "sort-packages": true,
        "allow-plugins": {
            "pestphp/pest-plugin": true,
            "php-http/discovery": true
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}

~~~~

## FILE: config/bs.php

SHA-256: `d3a16edde46a9bc50374eb78d30f97685e4f578bd05b2ec115c232ed7d61d633`

~~~~php
<?php

return [
    // Hide report navigation temporarily; keep existing reports and data available.
    'report_menu_enabled' => false,

    // Pause new settlements; previously recorded adjustments remain part of balances.
    'settlement_enabled' => false,
];

~~~~

## FILE: config/permission.php

SHA-256: `076b01b09d2d494632675623410c176c93b27b5a1d10f4b6dff5ea70c4f3aa15`

~~~~php
<?php

return [

    'models' => [
        'permission' => Spatie\Permission\Models\Permission::class,
        'role' => Spatie\Permission\Models\Role::class,
    ],

    'table_names' => [
        'roles' => 'spatie_roles',
        'permissions' => 'spatie_permissions',
        'model_has_permissions' => 'spatie_model_has_permissions',
        'model_has_roles' => 'spatie_model_has_roles',
        'role_has_permissions' => 'spatie_role_has_permissions',
    ],

    'column_names' => [
        'role_pivot_key' => null,
        'permission_pivot_key' => null,
        'model_morph_key' => 'model_id',
        'team_foreign_key' => 'team_id',
    ],

    'register_permission_check_method' => true,
    'register_octane_reset_listener' => false,
    'events_enabled' => false,
    'teams' => false,
    'team_resolver' => \Spatie\Permission\DefaultTeamResolver::class,
    'use_passport_client_credentials' => false,
    'display_permission_in_exception' => false,
    'display_role_in_exception' => false,
    'enable_wildcard_permission' => false,

    'cache' => [
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
        'key' => 'spatie.permission.cache',
        'store' => 'default',
    ],
];
~~~~

## FILE: database/seeders/PermissionSeeder.php

SHA-256: `e9c3fe10a44041926f2cb8dede723646ea4742a379540d4944be42109f12fea2`

~~~~php
<?php

namespace Database\Seeders;

use App\Models\Role as MasterRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // ==========================================
        // 1. DAFTAR RESMI PERMISSION (format dokumen rev4)
        // ==========================================
        $canonical = [
            'akses.pengajuan-bs',
           
            'akses.approval-finance',
            'akses.transaksi-operasional',
            'akses.transaksi-perjalanan-dinas',
            'akses.event-dalam-kota',
            'akses.event-luar-kota',
            'akses.laporan-bs',
             'akses.settle-bs',
            'akses.cash-flow',
            'akses.master-data',
            'akses.access-management',
        ];

        foreach ($canonical as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // ==========================================
        // 2. 🧹 CLEANUP: HAPUS PERMISSION LAMA DI LUAR DAFTAR RESMI
        //    (mis. sisa format lama: akses.transaksi.operasional,
        //     akses.approval.atasan, akses.master.data, dll)
        //    Pivot di role_has_permissions & model_has_permissions
        //    ikut terhapus otomatis karena ON DELETE CASCADE.
        // ==========================================
        Permission::whereNotIn('name', $canonical)->get()->each->delete();

        // ==========================================
        // 3. CERMINKAN MASTER ROLES (User Right Group) KE SPATIE ROLES
        // ==========================================
        $masterCodes = MasterRole::pluck('code')->filter()->values();

        foreach ($masterCodes as $code) {
            SpatieRole::firstOrCreate(['name' => $code, 'guard_name' => 'web']);
        }

        if ($masterCodes->isNotEmpty()) {
            SpatieRole::whereNotIn('name', $masterCodes)->get()->each->delete();
        }

        // ==========================================
        // 4. MATRIKS DEFAULT (hanya jika role masih kosong)
        // ==========================================
        $all = Permission::pluck('name')->toArray();

        $matrix = [
            'SALES' => [
                'akses.pengajuan-bs',
                'akses.transaksi-operasional',
                'akses.transaksi-perjalanan-dinas',
            ],
            'MARKETING' => [
                'akses.pengajuan-bs',
                'akses.event-dalam-kota',
                'akses.event-luar-kota',
            ],
            'OFFICE' => [
                'akses.pengajuan-bs',
                'akses.transaksi-operasional',
                'akses.laporan-bs',
            ],
            'FINANCE' => [
                'akses.approval-finance',
                'akses.laporan-bs',
                'akses.settle-bs', 
                'akses.cash-flow',
                
            ],
            'DIRECTOR' => [
                'akses.laporan-bs',
                'akses.settle-bs', 
                'akses.cash-flow',
            ],
            'DIR' => [
                'akses.laporan-bs',
                'akses.cash-flow',
            ],
            'HR' => [
                'akses.master-data',
            ],
            'ADMINISTRATOR' => $all,
            'ADMIN' => $all,
        ];

        foreach ($matrix as $code => $perms) {
            $role = SpatieRole::where('name', $code)->first();
            if ($role && $role->permissions()->count() === 0) {
                $role->syncPermissions($perms);
            }
        }

        // ==========================================
        // 5. SYNC SEMUA USER KE ROLE MASTER
        // ==========================================
        User::with('role')->get()->each(function (User $user) {
            $code = $user->role?->code;

            if (!$code) {
                $user->syncRoles([]);
                return;
            }

            $spatieRole = SpatieRole::firstOrCreate(['name' => $code, 'guard_name' => 'web']);
            $user->syncRoles([$spatieRole]);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
~~~~

## FILE: phpunit.xml

SHA-256: `493369cfb7feea104da4bec214c2cc6526ca2f1b9d937c50a2961fc59cb61aaf`

~~~~xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
>
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>app</directory>
        </include>
    </source>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="APP_MAINTENANCE_DRIVER" value="file"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="BROADCAST_CONNECTION" value="null"/>
        <env name="CACHE_STORE" value="array"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="DB_URL" value=""/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="PULSE_ENABLED" value="false"/>
        <env name="TELESCOPE_ENABLED" value="false"/>
        <env name="NIGHTWATCH_ENABLED" value="false"/>
    </php>
</phpunit>

~~~~

## FILE: routes/web.php

SHA-256: `5b24bea796d00efb9a5b93c516c68be4bfc91eff8b7d1e2f0bced7b25ffcdc1f`

~~~~php
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\CostCategoryController;
use App\Http\Controllers\WilayahController;
use App\Http\Controllers\EventHourGroupController;
use App\Http\Controllers\SettingPlafonPerjalananDinasController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WeekdayGroupController;
use App\Http\Controllers\PlafonEventDalamKotaController;
use App\Http\Controllers\PlafonEventLuarKotaController;
use App\Http\Controllers\TransaksiOperasionalController;
use App\Http\Controllers\TransaksiPerjalananDinasController;
use App\Http\Controllers\PerongkosanEventDalamKotaController;
use App\Http\Controllers\PerongkosanEventLuarKotaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PengajuanBsController;
use App\Http\Controllers\ApprovalAtasanController;
use App\Http\Controllers\ApprovalFinanceController;
use App\Http\Controllers\PeriodeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\LaporanBsController;
use App\Http\Controllers\CashFlowController;
use App\Http\Controllers\AccessManagementController;

Route::get('/', function () {
    return view('welcome');
});

// ==========================================
// 1. BLOK TAMU (Hanya untuk yang BELUM Login)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('proses_login');
});

// ==========================================
// 2. BLOK KARYAWAN UMUM (Harus Login)
// ==========================================
Route::middleware('auth')->group(function () {

    Route::get('/api/bs-balance', [\App\Http\Controllers\BsBalanceController::class, 'balances'])->name('bs-balance.balances');
    Route::get('/saldo-bs', [\App\Http\Controllers\BsBalanceController::class, 'show'])
        ->middleware('permission:akses.cash-flow')->name('bs-balance.show');

    Route::middleware(['permission:akses.pengajuan-bs|akses.transaksi-perjalanan-dinas|akses.cash-flow', 'bs.atomic'])
        ->prefix('persetujuan-pemakaian-bs')->name('bs-usage-approvals.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BsUsageApprovalController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\BsUsageApprovalController::class, 'show'])->whereNumber('id')->name('show');
            Route::post('/{id}/approve', [\App\Http\Controllers\BsUsageApprovalController::class, 'approve'])->whereNumber('id')->name('approve');
            Route::post('/{id}/reject', [\App\Http\Controllers\BsUsageApprovalController::class, 'reject'])->whereNumber('id')->name('reject');
        });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        if (!auth()->user()->canAccessPortal()) {
            return redirect()->route('dashboard.transaksi');
        }

        return view('dashboard.portal');
    })->name('dashboard');

    Route::get('/dashboard/transaksi', function () {
        $user = auth()->user();
        $canSeeAll = $user->canSeeAllTransactions();
        $uid = $user->id;

        $scope = fn ($table) => $canSeeAll
            ? DB::table($table)
            : DB::table($table)->where($table . '.user_id', $uid);

        $awalBulan  = now()->startOfMonth()->toDateString();
        $akhirBulan = now()->endOfMonth()->toDateString();

        $totalTransaksi =
            $scope('transaksi_operasional_area_sales_header')->count() +
            $scope('transaksi_perjalanan_dinas_header')->count() +
            $scope('perongkosan_event_dalam_kota')->count() +
            $scope('perongkosan_event_luar_kota')->count();

        $sumOperasional = $scope('transaksi_operasional_area_sales_header')
            ->join('transaksi_operasional_area_sales_detail', 'transaksi_operasional_area_sales_header.transaksi_id', '=', 'transaksi_operasional_area_sales_detail.transaksi_id')
            ->sum('transaksi_operasional_area_sales_detail.nominal');

        $sumPerdin = $scope('transaksi_perjalanan_dinas_header')
            ->join('transaksi_perjalanan_dinas_detail', 'transaksi_perjalanan_dinas_header.transaksi_id', '=', 'transaksi_perjalanan_dinas_detail.transaksi_id')
            ->sum('transaksi_perjalanan_dinas_detail.nominal');

        $totalNominal =
            $sumOperasional +
            $sumPerdin +
            $scope('perongkosan_event_dalam_kota')->sum('nominal') +
            $scope('perongkosan_event_luar_kota')->sum('nominal');

        $transaksiBulanIni =
            $scope('transaksi_operasional_area_sales_header')->whereBetween('tanggal', [$awalBulan, $akhirBulan])->count() +
            $scope('transaksi_perjalanan_dinas_header')->whereBetween('tanggal', [$awalBulan, $akhirBulan])->count() +
            $scope('perongkosan_event_dalam_kota')->whereBetween('tanggal', [$awalBulan, $akhirBulan])->count() +
            $scope('perongkosan_event_luar_kota')->whereBetween('tanggal', [$awalBulan, $akhirBulan])->count();

        $overPlafon =
            $scope('transaksi_perjalanan_dinas_header')->where('is_over_plafon', 1)->count() +
            $scope('perongkosan_event_dalam_kota')->where('is_over_plafon', 1)->count() +
            $scope('perongkosan_event_luar_kota')->where('is_over_plafon', 1)->count();

        return view('dashboard.transaksi', compact(
            'totalTransaksi', 'totalNominal', 'transaksiBulanIni', 'overPlafon'
        ));
    })->name('dashboard.transaksi');

    Route::get('/api/periode/status', function (Request $request) {
        $periode = $request->query('periode', now()->format('Y-m'));
        $status  = app(\App\Services\PeriodService::class)->statusOf($periode);

        return response()->json([
            'periode' => $periode,
            'status'  => $status,
            'open'    => $status === 'OPEN',
        ]);
    });

    Route::get('/api/wilayah-provinsi', [TransaksiPerjalananDinasController::class, 'getProvinsiByTeritory'])->name('api.wilayah.provinsi');
    Route::get('/api/wilayah-kota', [TransaksiPerjalananDinasController::class, 'getKotaByProvinsi'])->name('api.wilayah.kota');
    Route::get('/api/event-luar-wilayah-provinsi', [PerongkosanEventLuarKotaController::class, 'getProvinsiByTeritory'])->name('api.event_luar.wilayah.provinsi');
    Route::get('/api/event-luar-wilayah-kota', [PerongkosanEventLuarKotaController::class, 'getKotaByProvinsi'])->name('api.event_luar.wilayah.kota');
    Route::get('/api/plafon-wilayah-provinsi', [SettingPlafonPerjalananDinasController::class, 'getProvinsiByTeritory'])->name('api.plafon.wilayah.provinsi');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');

    // ==========================================
    // MODUL PENGAJUAN BS (permission: akses.pengajuan-bs)
    // ==========================================
    Route::middleware(['permission:akses.pengajuan-bs', 'bs.atomic'])->group(function () {
        Route::get('/pengajuan-bs/export', [PengajuanBsController::class, 'export'])->name('pengajuan-bs.export');

        Route::resource('pengajuan-bs', PengajuanBsController::class)
            ->except(['show', 'edit', 'update', 'destroy', 'store']);

        Route::get('/pengajuan-bs/{bs}', [PengajuanBsController::class, 'show'])->name('pengajuan-bs.show');

        Route::post('/pengajuan-bs', [PengajuanBsController::class, 'store'])
            ->name('pengajuan-bs.store')
            ->middleware(['company.active', 'atasan.active']);
    });

    // ==========================================
    // MODUL TRANSAKSI OPERASIONAL (permission: akses.transaksi-operasional)
    // ==========================================
    Route::middleware(['permission:akses.transaksi-operasional', 'bs.atomic'])->group(function () {
        Route::get('/transaksi-operasional/export', [TransaksiOperasionalController::class, 'export'])->name('transaksi_operasional.export');

        Route::resource('transaksi-operasional', TransaksiOperasionalController::class)
            ->names('transaksi_operasional')
            ->except(['store', 'update']);

        Route::post('/transaksi-operasional', [TransaksiOperasionalController::class, 'store'])
            ->name('transaksi_operasional.store')
            ->middleware(['company.active', 'atasan.active']);

        Route::put('/transaksi-operasional/{transaksi_operasional}', [TransaksiOperasionalController::class, 'update'])
            ->name('transaksi_operasional.update')
            ->middleware(['company.active', 'atasan.active']);

        Route::post('/transaksi-operasional/{id}/add-detail', [TransaksiOperasionalController::class, 'storeDetail'])
            ->name('transaksi_operasional.store_detail')
            ->middleware(['company.active', 'atasan.active']);

        Route::post('/transaksi-operasional/{id}/validate', [TransaksiOperasionalController::class, 'validateTransaction'])
            ->name('transaksi_operasional.validate')
            ->middleware(['company.active', 'atasan.active']);
    });

    // ==========================================
    // MODUL TRANSAKSI PERJALANAN DINAS (permission: akses.transaksi-perjalanan-dinas)
    // ==========================================
    Route::middleware(['permission:akses.transaksi-perjalanan-dinas', 'bs.atomic'])->group(function () {
        Route::get('/transaksi-perjalanan-dinas/export', [TransaksiPerjalananDinasController::class, 'export'])->name('transaksi_perjalanan_dinas.export');

        Route::resource('transaksi-perjalanan-dinas', TransaksiPerjalananDinasController::class)
            ->parameters(['transaksi-perjalanan-dinas' => 'transaksi_perjalanan_dinas'])
            ->names('transaksi_perjalanan_dinas')
            ->except(['store', 'update']);

        Route::post('/transaksi-perjalanan-dinas', [TransaksiPerjalananDinasController::class, 'store'])
            ->name('transaksi_perjalanan_dinas.store')
            ->middleware(['company.active', 'atasan.active']);

        Route::put('/transaksi-perjalanan-dinas/{transaksi_perjalanan_dinas}', [TransaksiPerjalananDinasController::class, 'update'])
            ->name('transaksi_perjalanan_dinas.update')
            ->middleware(['company.active', 'atasan.active']);

        Route::delete('/transaksi-perjalanan-dinas/detail/{id}', [TransaksiPerjalananDinasController::class, 'destroyDetail'])
            ->name('transaksi_perjalanan_dinas.destroy_detail')
            ->middleware(['company.active', 'atasan.active']);

        Route::post('/transaksi-perjalanan-dinas/{id}/validate', [TransaksiPerjalananDinasController::class, 'validateTransaction'])
            ->name('transaksi_perjalanan_dinas.validate')
            ->middleware(['company.active', 'atasan.active']);

        Route::post('/transaksi-perjalanan-dinas/{id}/withdraw', [TransaksiPerjalananDinasController::class, 'withdraw'])
            ->name('transaksi_perjalanan_dinas.withdraw');
        Route::post('/transaksi-perjalanan-dinas/{id}/revise', [TransaksiPerjalananDinasController::class, 'revise'])
            ->name('transaksi_perjalanan_dinas.revise');

        Route::post('/transaksi-perjalanan-dinas/{id}/add-detail', [TransaksiPerjalananDinasController::class, 'storeDetail'])
            ->name('transaksi_perjalanan_dinas.store_detail')
            ->middleware(['company.active', 'atasan.active']);
    });

    Route::post('/api/calculate-cost-nominal', [TransaksiPerjalananDinasController::class, 'getAutoNominal'])->name('api.calculate_cost_nominal');

    // ==========================================
    // MODUL EVENT DALAM KOTA (permission: akses.event-dalam-kota)
    // ==========================================
    Route::middleware(['permission:akses.event-dalam-kota', 'event.atomic'])->group(function () {
        Route::get('/event-dalam-kota/export', [PerongkosanEventDalamKotaController::class, 'export'])->name('perongkosan_event_dalam_kota.export');

        Route::resource('event-dalam-kota', PerongkosanEventDalamKotaController::class)
            ->names('perongkosan_event_dalam_kota')
            ->except(['store', 'update']);

        Route::post('/event-dalam-kota', [PerongkosanEventDalamKotaController::class, 'store'])
            ->name('perongkosan_event_dalam_kota.store')
            ->middleware(['company.active', 'atasan.active']);

        Route::put('/event-dalam-kota/{event_dalam_kota}', [PerongkosanEventDalamKotaController::class, 'update'])
            ->name('perongkosan_event_dalam_kota.update')
            ->middleware(['company.active', 'atasan.active']);
    });

    // ==========================================
    // MODUL EVENT LUAR KOTA (permission: akses.event-luar-kota)
    // ==========================================
    Route::middleware(['permission:akses.event-luar-kota', 'event.atomic'])->group(function () {
        Route::get('/event-luar-kota/export', [PerongkosanEventLuarKotaController::class, 'export'])->name('perongkosan_event_luar_kota.export');

        Route::resource('event-luar-kota', PerongkosanEventLuarKotaController::class)
            ->names('perongkosan_event_luar_kota')
            ->except(['store', 'update']);

        Route::post('/event-luar-kota', [PerongkosanEventLuarKotaController::class, 'store'])
            ->name('perongkosan_event_luar_kota.store')
            ->middleware(['company.active', 'atasan.active']);

        Route::put('/event-luar-kota/{event_luar_kota}', [PerongkosanEventLuarKotaController::class, 'update'])
            ->name('perongkosan_event_luar_kota.update')
            ->middleware(['company.active', 'atasan.active']);
    });

    // ==========================================
    // APPROVAL ATASAN (dikunci RELASI atasan, bukan role)
    // ==========================================
    Route::middleware(['can:approval-atasan', 'bs.atomic'])->group(function () {
        Route::prefix('approval-atasan')->name('approval-atasan.')->group(function () {
            Route::get('/', [ApprovalAtasanController::class, 'index'])->name('index');
            Route::get('/{bs}/detail', [ApprovalAtasanController::class, 'detail'])->name('detail');
            Route::post('/{bs}/approve', [ApprovalAtasanController::class, 'approve'])->name('approve');
            Route::post('/{bs}/reject', [ApprovalAtasanController::class, 'reject'])->name('reject');
        });
    });

    // ==========================================
    // APPROVAL FINANCE (permission divisi Finance)
    // ==========================================
    Route::middleware(['permission:akses.approval-finance', 'bs.atomic'])->group(function () {
        Route::prefix('approval-finance')->name('approval-finance.')->group(function () {
            Route::get('/', [ApprovalFinanceController::class, 'index'])->name('index');
            Route::get('/{bs}/detail', [ApprovalFinanceController::class, 'detail'])->name('detail');
            Route::post('/{bs}/disburse', [ApprovalFinanceController::class, 'disburse'])->name('disburse');
            Route::post('/{bs}/reject', [ApprovalFinanceController::class, 'reject'])->name('reject');
        });
    });

    // ==========================================
    // CASH FLOW
    // ==========================================
    Route::middleware(['permission:akses.cash-flow'])->group(function () {
        Route::get('/cash-flow', [CashFlowController::class, 'index'])->name('cash-flow.index');
        Route::get('/cash-flow/export', [CashFlowController::class, 'export'])->name('cash-flow.export');
        Route::get('/cash-flow/{periode}/export', [CashFlowController::class, 'exportPeriod'])->name('cash-flow.export-period');
        Route::get('/cash-flow/{periode}', [CashFlowController::class, 'show'])->name('cash-flow.show');

        /* ==========================================================
           🆕 HTMX PARTIAL: Pagination transaksi tanpa reload halaman
           Dipanggil oleh link paginator di HTMX (hx-get),
           mengembalikan hanya potongan HTML section Uang Masuk/Keluar.
        ========================================================== */
        Route::get('/cash-flow/{periode}/transactions', [CashFlowController::class, 'transactionsPartial'])
            ->name('cash-flow.transactions-partial');
    });

    // NOTIFIKASI
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read_all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');

    // ==========================================
    // LAPORAN PERTANGGUNGJAWABAN BS
    // ==========================================
    Route::middleware(['permission:akses.laporan-bs'])->group(function () {
        Route::get('/laporan-bs', [LaporanBsController::class, 'index'])->name('laporan-bs.index');
        Route::get('/laporan-bs/export', [LaporanBsController::class, 'export'])->name('laporan-bs.export');
        Route::post('/laporan-bs/{bs}/settle', [LaporanBsController::class, 'settle'])->name('laporan-bs.settle');
    });

    // API UNTUK INFO PLAFON
    Route::post('/api/get-plafon-info', [TransaksiPerjalananDinasController::class, 'getPlafonInfo'])->name('api.get_plafon_info');
    Route::post('/api/check-plafon-realtime', [TransaksiPerjalananDinasController::class, 'checkPlafonRealtime'])->name('api.check_plafon_realtime');
    Route::post('/api/event-dalam-kota/plafon-info', [PerongkosanEventDalamKotaController::class, 'getPlafonInfo'])->name('api.event_dalam_kota.plafon_info');
    Route::post('/api/event-luar-kota/plafon-info', [PerongkosanEventLuarKotaController::class, 'getPlafonInfo'])->name('api.event_luar_kota.plafon_info');
});

// ==========================================
// 3. BLOK VVIP ADMINISTRATOR
// ==========================================
Route::middleware(['auth', 'permission:akses.master-data'])->group(function () {

    Route::get('/dashboard/master-data', function () {
        $counts = [
            'roles'             => DB::table('roles')->count(),
            'users'             => DB::table('user')->count(),
            'companies'         => DB::table('company')->count(),
            'levels'            => DB::table('level')->count(),
            'positions'         => DB::table('position')->count(),
            'wilayah'           => DB::table('wilayah')->count(),
            'cost_categories'   => DB::table('cost_category')->count(),
            'week_day_groups'   => DB::table('weekday_group')->count(),
            'event_hours'       => DB::table('event_hour_group')->count(),
            'plafon_dinas'      => DB::table('setting_plafon_perjalanan_dinas')->count(),
            'plafon_dalam'      => DB::table('setting_plafon_event_marketing_dalam_kota')->count(),
            'plafon_luar'       => DB::table('setting_plafon_event_marketing_luar_kota')->count(),
            'periode'           => DB::table('periode')->count(),
        ];

        $periods = DB::table('periode')->orderBy('periode', 'desc')->get();

        return view('dashboard.master_data', compact('counts', 'periods'));
    })->name('dashboard.master_data');

    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::resource('companies', CompanyController::class);
    Route::resource('levels', LevelController::class);
    Route::resource('positions', PositionController::class);
    Route::resource('cost_categories', CostCategoryController::class);
    Route::resource('wilayah', WilayahController::class);
    Route::get('/api/wilayah-search', [WilayahController::class, 'search'])->name('api.wilayah.search');
    Route::resource('event_hour_groups', EventHourGroupController::class);
    Route::resource('setting_plafon_perjalanan_dinas', SettingPlafonPerjalananDinasController::class);
    Route::resource('weekday_groups', WeekdayGroupController::class);
    Route::resource('plafon-event-dalam-kota', PlafonEventDalamKotaController::class);
    Route::resource('plafon-event-luar-kota', PlafonEventLuarKotaController::class);

    // ACCESS MANAGEMENT (halaman pengaturan hak akses menu per Role / User Right Group)
    Route::middleware(['permission:akses.access-management'])->group(function () {
        Route::get('/access-management', [AccessManagementController::class, 'index'])->name('access-management.index');
        Route::put('/access-management/{role}', [AccessManagementController::class, 'update'])->name('access-management.update');
    });

    Route::get('/periode', [PeriodeController::class, 'index'])->name('periode.index');
    Route::post('/periode/{periode}/close', [PeriodeController::class, 'close'])->name('periode.close');
    Route::get('/periode/{periode}', [PeriodeController::class, 'show'])->name('periode.show');
    Route::post('/periode/{periode}/reopen', [PeriodeController::class, 'reopen'])->name('periode.reopen');
});
~~~~
