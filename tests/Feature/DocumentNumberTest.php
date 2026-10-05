<?php

namespace Tests\Feature;

use App\Services\DocumentNumberService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LogicException;
use Tests\TestCase;

class DocumentNumberTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        foreach (DocumentNumberService::TYPES as [$name, $column]) {
            Schema::create($name, function (Blueprint $table) use ($column) {
                $table->id();
                $table->string($column)->unique();
            });
        }
        foreach (['transaksi_operasional_area_sales_detail', 'transaksi_perjalanan_dinas_detail'] as $name) {
            Schema::create($name, function (Blueprint $table) { $table->id(); $table->string('transaksi_id', 11); });
        }
        (require database_path('migrations/2026_09_17_100000_restore_readable_document_numbers.php'))->up();
    }

    public function test_numbers_follow_document_date_and_each_day_and_type_has_its_own_sequence(): void
    {
        DB::transaction(function () {
            $numbers = app(DocumentNumberService::class);
            $this->assertSame('BS-20260917-001', $numbers->next('BS', '2026-09-17'));
            $this->assertSame('BS-20260917-002', $numbers->next('BS', '2026-09-17'));
            $this->assertSame('CAIR-20260917-001', $numbers->next('CAIR', '2026-09-17'));
            $this->assertSame('OAS26090101', $numbers->next('OAS', '2026-09-01'));
            $this->assertSame('PD26090101', $numbers->next('PD', '2026-09-01'));
            $this->assertSame('CEKBS-20260917-001', $numbers->next('CEKBS', '2026-09-17'));
            $this->assertSame('CEKBS-20260917-002', $numbers->next('CEKBS', '2026-09-17'));
            $this->assertSame('BS-20260918-001', $numbers->next('BS', '2026-09-18'));
            $this->assertSame('BS-20270101-001', $numbers->next('BS', '2027-01-01'));
        });
    }

    public function test_legacy_gaps_random_ids_and_suffix_overflow_do_not_produce_duplicates(): void
    {
        DB::table('pengajuan_bs')->insert([
            ['document_no' => 'BS-20260917-001'],
            ['document_no' => 'BS-20260917-999'],
            ['document_no' => 'BS-01M2PG9NE6ZWTJYZTSW18HN5NW'],
        ]);
        DB::table('transaksi_operasional_area_sales_header')->insert([
            ['transaksi_id' => 'OAS26091799'], ['transaksi_id' => 'OASXXT5VHYO'],
        ]);
        DB::transaction(function () {
            $numbers = app(DocumentNumberService::class);
            $this->assertSame('BS-20260917-1000', $numbers->next('BS', '2026-09-17'));
            $number = $numbers->next('OAS', '2026-09-17');
            $this->assertSame('OAS260917100', $number);
            DB::table('transaksi_operasional_area_sales_header')->insert(['transaksi_id' => $number]);
            DB::table('transaksi_operasional_area_sales_header')->where('transaksi_id', $number)->delete();
            $this->assertSame('OAS260917101', $numbers->next('OAS', '2026-09-17'));
        });
    }

    public function test_rollback_does_not_consume_a_number_and_transaction_is_required(): void
    {
        $numbers = app(DocumentNumberService::class);
        DB::beginTransaction();
        $this->assertSame('BS-20260917-001', $numbers->next('BS', '2026-09-17'));
        DB::rollBack();
        $this->assertSame(0, DB::table('document_number_sequences')->count());
        DB::transaction(fn () => $this->assertSame('BS-20260917-001', $numbers->next('BS', '2026-09-17')));
        $this->expectException(LogicException::class);
        $numbers->next('BS', '2026-09-17');
    }
}
