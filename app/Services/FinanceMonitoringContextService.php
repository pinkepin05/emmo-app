<?php

namespace App\Services;

use App\Models\Company;
use App\Models\TransaksiOperasionalHeader;
use App\Models\TransaksiPerjalananDinasHeader;
use App\Models\PerongkosanEventDalamKota;
use App\Models\PerongkosanEventLuarKota;
use App\Models\User;

class FinanceMonitoringContextService
{
    public const TYPES = ['OPR', 'PD', 'EDK', 'ELK'];

    public function assertType(string $jenis): void
    {
        abort_unless(in_array($jenis, self::TYPES, true), 404, 'Jenis transaksi tidak dikenal.');
    }

    public function assertCompanyUser(Company $company, User $user): void
    {
        abort_unless((int) $user->company_id === (int) $company->id, 403, 'User tidak ditemukan dalam Company ini.');
    }

    /**
     * Validasi bahwa transaksi benar-benar milik user dan company dalam konteks monitoring.
     * Memanggil ini SEBELUM render agar tidak ada data bocor.
     */
    public function assertTransactionOwnership(string $jenis, $transaction, User $user, Company $company): void
    {
        abort_unless((int) $transaction->user_id === (int) $user->id, 403, 'Transaksi tidak dimiliki oleh user monitoring ini.');
        $this->assertCompanyUser($company, $user);
    }

    public function sourceModel(string $jenis): string
    {
        return match ($jenis) {
            'OPR' => TransaksiOperasionalHeader::class,
            'PD' => TransaksiPerjalananDinasHeader::class,
            'EDK' => PerongkosanEventDalamKota::class,
            'ELK' => PerongkosanEventLuarKota::class,
            default => abort(404),
        };
    }

    public function listLabel(string $jenis): string
    {
        return match ($jenis) {
            'OPR' => 'Operasional Sales',
            'PD' => 'Perjalanan Dinas',
            'EDK' => 'Event Dalam Kota',
            'ELK' => 'Event Luar Kota',
            default => abort(404),
        };
    }
}