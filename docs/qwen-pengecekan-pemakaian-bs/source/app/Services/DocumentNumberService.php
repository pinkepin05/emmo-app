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
