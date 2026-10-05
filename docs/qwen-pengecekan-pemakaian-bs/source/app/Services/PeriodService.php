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
