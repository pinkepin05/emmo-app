<?php

namespace App\Console\Commands;

use App\Services\DocumentNumberService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class RepairDocumentNumbers extends Command
{
    protected $signature = 'bs:repair-document-numbers {--apply : Simpan koreksi beserta cadangan nomor lama}';

    protected $description = 'Pratinjau/koreksi nomor BS, pencairan, dan OAS yang terlanjur memakai ULID atau karakter acak';

    public function handle(DocumentNumberService $numbers): int
    {
        $apply = (bool) $this->option('apply');
        $changes = [];
        DB::beginTransaction();
        try {
            // Follow the same lock order as the transaction middleware.
            DB::table('periode')->orderBy('periode')->lockForUpdate()->get();
            foreach (['BS', 'CAIR', 'OAS'] as $type) {
                [$table, $column, $date] = DocumentNumberService::TYPES[$type];
                foreach (DB::table($table)->orderBy($date)->orderBy('id')->lockForUpdate()->get() as $row) {
                    $old = $row->$column;
                    $random = $type === 'OAS'
                        ? preg_match('/^OAS[A-Z0-9]{8}$/D', $old) && !preg_match('/^OAS[0-9]{8}$/D', $old)
                        : preg_match('/^'.$type.'-[0-7][0-9A-HJKMNP-TV-Z]{25}$/D', $old);
                    if (!$random) {
                        continue;
                    }

                    $new = $numbers->next($type, $row->$date);
                    $change = ['table' => $table, 'id' => $row->id, 'column' => $column, 'old' => $old, 'new' => $new, 'before' => (array) $row];
                    if ($type === 'OAS') {
                        $change['details_before'] = DB::table('transaksi_operasional_area_sales_detail')->where('transaksi_id', $old)->get()->toArray();
                    }
                    DB::table($table)->where('id', $row->id)->update([$column => $new]);
                    if ($type === 'OAS') {
                        // Also supports legacy schemas without ON UPDATE CASCADE,
                        // and includes soft-deleted detail rows.
                        DB::table('transaksi_operasional_area_sales_detail')->where('transaksi_id', $old)->update(['transaksi_id' => $new]);
                    }
                    $changes[] = $change;
                    $bsId = match ($type) {
                        'BS' => $row->id,
                        'CAIR' => $row->pengajuan_bs_id,
                        'OAS' => $row->bs_id,
                    };
                    if ($bsId) {
                        DB::table('bs_activity_logs')->insert([
                            'pengajuan_bs_id' => $bsId,
                            'activity_code' => 'NUMBER_CORRECTED',
                            'activity_name' => 'Nomor dokumen diperbaiki',
                            'description' => $old.' -> '.$new,
                            'actor_user_id' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            $this->table(['Tabel', 'ID', 'Nomor lama', 'Nomor baru'], array_map(
                fn ($change) => [$change['table'], $change['id'], $change['old'], $change['new']], $changes
            ));
            if ($apply && $changes) {
                $path = 'document-number-backups/'.now()->format('Ymd_His_u').'.json';
                if (!Storage::disk('local')->put($path, json_encode([
                    'database' => DB::connection()->getDatabaseName(),
                    'created_at' => now()->toIso8601String(),
                    'changes' => $changes,
                ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR))) {
                    throw new RuntimeException('Cadangan nomor gagal disimpan; koreksi dibatalkan.');
                }
                DB::commit();
                $this->info(count($changes).' nomor diperbaiki. Cadangan: '.Storage::disk('local')->path($path));
            } else {
                DB::rollBack();
                $this->info($changes ? 'Pratinjau saja; gunakan --apply untuk menyimpan.' : 'Tidak ada nomor acak yang perlu diperbaiki.');
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
}
