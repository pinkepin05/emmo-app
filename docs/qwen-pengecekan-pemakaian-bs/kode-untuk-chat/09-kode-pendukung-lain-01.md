# Kode existing: kode-pendukung-lain, bagian 1

Snapshot referensi. Bukan implementasi fitur baru. Semua file di bawah disertakan utuh.

## FILE: .editorconfig

SHA-256: `f78cc5a2ef8ba61ad956d0f5db453748a981270f6a1b9f4a5a00df8690e7946d`

~~~~text
root = true

[*]
charset = utf-8
end_of_line = lf
indent_size = 4
indent_style = space
insert_final_newline = true
trim_trailing_whitespace = true

[*.md]
trim_trailing_whitespace = false

[*.{yml,yaml}]
indent_size = 2

[compose.yaml]
indent_size = 4

~~~~

## FILE: .env.example

SHA-256: `f038b39a024961b073fd20a11c938bfab3b2c4fa11197edf6a27ad7bfcbedf41`

~~~~text
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost
APP_DISPLAY_TIMEZONE=Asia/Jakarta

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

# PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
# CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

~~~~

## FILE: README.md

SHA-256: `d8b15dfcda5b6e61a96730afedc1ed5376e46ba367e21749a9d789cc222009c4`

~~~~markdown
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

~~~~

## FILE: app/Console/Commands/RecalculateCashFlow.php

SHA-256: `d02521799bd2013e1a8e1978864dbfef881cc81e22e5722d503aadc6fe8e0752`

~~~~php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CashFlowService;
use App\Models\User;

class RecalculateCashFlow extends Command
{
    protected $signature = 'cashflow:recalculate {--user= : Specific user ID}';
    protected $description = 'Recalculate all cash flow snapshots';

    public function handle(CashFlowService $service)
    {
        if ($userId = $this->option('user')) {
            $service->recalculateAllPeriods($userId);
            $this->info("✅ Recalculated for user ID: {$userId}");
        } else {
            $users = User::where('is_active', 1)->get();
            $this->info("🔄 Recalculating for {$users->count()} active users...");
            
            foreach ($users as $user) {
                $service->recalculateAllPeriods($user->id);
                $this->info("  ✓ {$user->nama} (ID: {$user->id})");
            }
        }
        
        $this->info('🎉 Selesai!');
    }
}
~~~~

## FILE: app/Console/Commands/RepairDocumentNumbers.php

SHA-256: `52ce4b194f3ea0b301ae56bc8463fe08acb90b4fcb67f583829b7792c21ebab4`

~~~~php
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

~~~~

## FILE: app/Exports/CashFlowExport.php

SHA-256: `42d1cc5959e3b1e1530b497e0b7069843da5b7331d702fb33d295f6d53667d91`

~~~~php
<?php

namespace App\Exports;

use App\Models\StockCashFlowSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CashFlowExport implements WithMultipleSheets
{
    public function __construct(
        private Collection $snapshots,
        private array $statuses,
        private ?array $transactions = null
    ) {}

    public function sheets(): array
    {
        $rows = $this->snapshots->map(function (StockCashFlowSnapshot $snapshot) {
            $status = $this->statuses[$snapshot->periode] ?? 'OPEN';

            return [
                str_replace('-', '', $snapshot->periode),
                $status,
                (float) $snapshot->saldo_awal,
                (float) $snapshot->total_masuk,
                (float) $snapshot->total_keluar_bs,
                (float) $snapshot->total_event_dalam,
                (float) $snapshot->total_event_luar,
                (float) $snapshot->total_keluar,
                (float) $snapshot->penyesuaian,
                $status === 'CLOSED' && $snapshot->saldo_akhir !== null ? (float) $snapshot->saldo_akhir : null,
                $snapshot->opening_final ? 'Final' : 'Menunggu penutupan periode sebelumnya',
            ];
        })->all();

        $sheets = [new CashFlowSheetExport('Ringkasan Stok Kas', [
            'Periode', 'Status Periode', 'Saldo Awal (Rp)', 'Total Masuk (Rp)',
            'Pengeluaran BS (Rp)', 'Event Dalam Kota (Rp)', 'Event Luar Kota (Rp)',
            'Total Keluar (Rp)', 'Penyesuaian BS (Rp)', 'Saldo Akhir (Rp)', 'Status Saldo Awal',
        ], $rows, ['C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'])];

        if ($this->transactions !== null) {
            foreach (['masuk' => 'Uang Masuk', 'keluar' => 'Uang Keluar'] as $key => $title) {
                $rows = array_map(fn (array $transaction) => [
                    Carbon::parse($transaction['date'])->format('Y-m-d'),
                    $transaction['type'],
                    $transaction['description'],
                    (float) $transaction['nominal'],
                ], $this->transactions[$key]);

                $sheets[] = new CashFlowSheetExport($title, [
                    'Tanggal', 'Jenis', 'Keterangan', 'Nominal (Rp)',
                ], $rows, ['D']);
            }

            $rows = array_map(fn (array $transaction) => [
                $transaction['description'], (float) $transaction['nominal'],
            ], $this->transactions['penyesuaian']);
            $sheets[] = new CashFlowSheetExport('Penyesuaian BS', [
                'Dokumen BS', 'Penyesuaian (Rp)',
            ], $rows, ['B']);
        }

        return $sheets;
    }
}

~~~~

## FILE: app/Exports/CashFlowSheetExport.php

SHA-256: `deedf5d4915f1a4761f2b01383faf9c846edde718182f501fd2e47ee7dcf44cf`

~~~~php
<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CashFlowSheetExport extends LaporanTableExport implements WithCustomValueBinder, WithStrictNullComparison, WithTitle
{
    public function __construct(
        private string $sheetTitle,
        array $headings,
        array $rows,
        private array $moneyColumns = []
    ) {
        parent::__construct($headings, $rows, $moneyColumns);
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function styles(Worksheet $sheet): array
    {
        $styles = parent::styles($sheet);
        foreach ($this->moneyColumns as $column) {
            $styles[$column]['numberFormat']['formatCode'] = '#,##0.00;[Red]-#,##0.00';
        }

        return $styles;
    }

    public function bindValue(Cell $cell, $value): bool
    {
        // Document numbers and descriptions must remain literal text in Excel.
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return (new DefaultValueBinder)->bindValue($cell, $value);
    }
}

~~~~

## FILE: app/Exports/LaporanTableExport.php

SHA-256: `b910c57f8d14098f71dcab84bf41469a427cd94c49a2226e8ab886f0dc6459ab`

~~~~php
<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanTableExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithEvents
{
    public function __construct(
        private array $headings,
        private array $rows,
        private array $numberColumns = [] // contoh: ['H'] = kolom Nominal
    ) {}

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        return $this->rows;
    }

    /** Header tebal berwarna gelap + format angka ribuan */
    public function styles(Worksheet $sheet): array
    {
        $styles = [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0F172A'],
                ],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];

        foreach ($this->numberColumns as $col) {
            $styles[$col] = ['numberFormat' => ['formatCode' => '#,##0']];
        }

        return $styles;
    }

    /** AutoFilter (panah filter) + Freeze header */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $lastCol = Coordinate::stringFromColumnIndex(count($this->headings));
                $lastRow = count($this->rows) + 1;

                if ($lastRow > 1) {
                    $event->sheet->getDelegate()->setAutoFilter("A1:{$lastCol}{$lastRow}");
                }
                $event->sheet->getDelegate()->freezePane('A2');
            },
        ];
    }
}
~~~~

## FILE: app/Http/Controllers/ApprovalAtasanController.php

SHA-256: `98c5cb8cff76bd72c6f992f7d0d472cd5fd7633a6cf17f1f34e130f644821e68`

~~~~php
<?php

namespace App\Http\Controllers;

use App\Models\PengajuanBs;
use App\Models\BsActivityLog;
use Illuminate\Http\Request;

class ApprovalAtasanController extends Controller
{
    public function index(Request $request)
    {
        // 🛡️ Proteksi lapis 1: permission fitur (lapis 2 sudah di middleware route)
        abort_unless(auth()->user()->can('approval-atasan'), 403);

        $statuses = [
            'PENDING_ATASAN', 'APPROVED_ATASAN', 'REJECTED_ATASAN',
            'DISBURSED', 'REJECTED_FINANCE', 'SETTLED',
        ];

        $scope = fn($q) => $q->where('atasan_user_id', auth()->id())->whereIn('status', $statuses);

        $bulanExpr = "DATE_FORMAT(tanggal, '%Y-%m')";

        $availablePeriods = $scope(PengajuanBs::query())
            ->selectRaw("$bulanExpr as bulan")
            ->distinct()
            ->orderBy('bulan', 'desc')
            ->pluck('bulan')->values();

        $currentPeriod = \App\Support\AppTime::now()->format('Y-m');
        $defaultPeriod = $availablePeriods->contains($currentPeriod)
            ? $currentPeriod
            : ($availablePeriods->first() ?? $currentPeriod);

        $selectedPeriod = $request->query('periode', $defaultPeriod);
        if (!$availablePeriods->contains($selectedPeriod)) {
            $selectedPeriod = $defaultPeriod;
        }

        $data = $scope(PengajuanBs::query())
            ->whereRaw("$bulanExpr = ?", [$selectedPeriod])
            ->with([
                'user', 'atasan',
                'logs' => fn ($q) => $q->whereIn('activity_code', ['APPROVED_ATASAN', 'REJECTED_ATASAN'])->latest(),
            ])
            ->orderByRaw("CASE status
                            WHEN 'PENDING_ATASAN'   THEN 1
                            WHEN 'APPROVED_ATASAN'  THEN 2
                            WHEN 'DISBURSED'        THEN 3
                            WHEN 'SETTLED'          THEN 4
                            WHEN 'REJECTED_ATASAN'  THEN 5
                            WHEN 'REJECTED_FINANCE' THEN 6
                          END")
            ->latest()
            ->get();

        return view('approval-atasan.index', compact('data', 'availablePeriods', 'selectedPeriod'));
    }

    public function detail(PengajuanBs $bs)
    {
       abort_unless(auth()->user()->can('approval-atasan'), 403);

        // 🛡️ Proteksi lapis 2: KONTEKSTUAL — hanya atasan langsung (sesuai note 4-5 dokumen)
        if ($bs->atasan_user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        $bs->load(['user', 'atasan', 'members']);

        $logs = BsActivityLog::where('pengajuan_bs_id', $bs->id)
                             ->with('actor')
                             ->orderBy('created_at', 'asc')
                             ->get();

        return view('approval-atasan.detail', compact('bs', 'logs'));
    }

    public function approve(Request $request, PengajuanBs $bs)
    {
        abort_unless(auth()->user()->can('approval-atasan'), 403);

        if ($bs->atasan_user_id !== auth()->id()) {
            abort(403, 'Anda tidak berhak menyetujui pengajuan ini.');
        }

        if ($bs->status !== 'PENDING_ATASAN') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $request->validate(['approval_message' => 'nullable|string|max:1000']);
        $message = $request->approval_message ?: null;

        $bs->update([
            'status'           => 'APPROVED_ATASAN',
            'approval_message' => $message,
        ]);

        $logDesc = 'Pengajuan disetujui oleh ' . auth()->user()->nama . ' dan diteruskan ke Finance.';
        if ($message) $logDesc .= ' Pesan: ' . $message;

        BsActivityLog::record($bs->id, 'APPROVED_ATASAN', 'Disetujui Atasan', $logDesc);

        \App\Services\BsNotifications::afterCommit(fn () => \App\Models\User::permission('akses.approval-finance')
            ->get()
            ->each(fn($u) => $u->notify(new \App\Notifications\BsApproved($bs))));

        return back()->with('success', 'BS berhasil disetujui.' . ($message ? ' Pesan Anda akan diteruskan ke pemohon.' : ''));
    }

    public function reject(Request $request, PengajuanBs $bs)
    {
        abort_unless(auth()->user()->can('approval-atasan'), 403);

        if ($bs->atasan_user_id !== auth()->id()) {
            abort(403, 'Anda tidak berhak menolak pengajuan ini.');
        }

        if ($bs->status !== 'PENDING_ATASAN') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $request->validate(['rejection_reason' => 'required|string|max:1000']);

        $bs->update([
            'status'           => 'REJECTED_ATASAN',
            'rejection_reason' => $request->rejection_reason,
        ]);

        BsActivityLog::record(
            $bs->id,
            'REJECTED_ATASAN',
            'Ditolak Atasan',
            'Pengajuan ditolak oleh ' . auth()->user()->nama . ' dengan alasan: ' . $request->rejection_reason
        );

        \App\Services\BsNotifications::afterCommit(fn () => $bs->user?->notify(new \App\Notifications\BsRejected($bs)));

        return back()->with('success', 'BS berhasil ditolak.');
    }
}

~~~~

## FILE: app/Http/Controllers/AuthController.php

SHA-256: `8626c6ceb13d70887dd54e267ba78c8ff838b4e605fc7f957687932caca50ed7`

~~~~php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Memanggil fitur "Satpam" bawaan Laravel 

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        //1. Catat apa yang diketik tamu
        $kredensial = [
            'username' => $request->username,
            'password' => $request->password
        ];

        // 2. Suruh "Satpan" (Auth::attempt) mengecek ke Buku Daftar Tamu (Database)
        if (Auth::attempt($kredensial)) {
            // JIKA COCOK: Beri gelang VIP dan masukkan ke halaman dashboard
            $request->session()->regenerate();
            $request->session()->forget('url.intended');
            return redirect()->route($request->user()->dashboardRouteName());
        }

        // JIKA GAGAL: Kembalikan ke pintu depan dan beri pesan error
        return back()->with('error', 'Username atau Password salah, silahkan coba lagi!');
    }

    public function logout(Request $request){

    // 1. Bersihkan gelang VIP (Sesssion)
    Auth::logout();

    //2. Hancurkan sesi yang tersisa agar tidak bisa dipakai ulang
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login')->with('success', 'Anda telah berhasil logout.');

    }
}

~~~~

## FILE: app/Http/Controllers/CashFlowController.php

SHA-256: `bc05d13be153b755c79f5edce2baca83e652240ccb603518113fd8c1622a0d75`

~~~~php
<?php

namespace App\Http\Controllers;

use App\Exports\CashFlowExport;
use App\Models\StockCashFlowSnapshot;
use App\Models\Periode;
use App\Services\StockCashFlowService;
use App\Services\PeriodService;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class CashFlowController extends Controller
{
    public function __construct(private StockCashFlowService $cashFlowService, private PeriodService $periods) {}

    public function index()
    {
        $userId = (int) auth()->id();
        $currentPeriod = $this->periods->current();
        $snapshots = $this->snapshots($userId, $currentPeriod);
        $currentSnapshot = $snapshots->firstWhere('periode', $currentPeriod);
        return view('cash-flow.index', compact('snapshots', 'currentSnapshot', 'currentPeriod'));
    }

    public function show(string $periode)
    {
        $periode = $this->periods->normalize($periode);
        $userId = (int) auth()->id();
        $snapshot = $this->cashFlowService->recalculateSnapshot($userId, $periode);
        $status = Periode::where('periode', $periode)->value('status') ?? 'OPEN';
        $transactions = $this->cashFlowService->details($userId, $periode);

        /* ==========================================================
           PAGINATION SERVER-SIDE untuk Uang Masuk & Uang Keluar
        ========================================================== */
        $perPage = 10;

        // 1) Sortir tanggal terbaru agar halaman 1 = transaksi teranyar
        $masukAll  = collect($transactions['masuk'])->sortByDesc('date')->values();
        $keluarAll = collect($transactions['keluar'])->sortByDesc('date')->values();

        // 2) Total GRAND dari SEMUA data (bukan hanya isi halaman aktif)
        $totalMasuk       = $masukAll->sum('nominal');
        $totalKeluar      = $keluarAll->sum('nominal');
        $totalPenyesuaian = collect($transactions['penyesuaian'])->sum('nominal');

        // 3) Baca nomor halaman aktif dari query string
        $masukPage  = LengthAwarePaginator::resolveCurrentPage('masuk_page');
        $keluarPage = LengthAwarePaginator::resolveCurrentPage('keluar_page');

        // 4) Potong data per halaman, bungkus jadi paginator independen
        $pagedMasuk = new LengthAwarePaginator(
            $masukAll->forPage($masukPage, $perPage)->values(),
            $masukAll->count(),
            $perPage,
            $masukPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'pageName' => 'masuk_page']
        );

        $pagedKeluar = new LengthAwarePaginator(
            $keluarAll->forPage($keluarPage, $perPage)->values(),
            $keluarAll->count(),
            $perPage,
            $keluarPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'pageName' => 'keluar_page']
        );

        return view('cash-flow.show', compact(
            'snapshot', 'status', 'transactions', 'periode',
            'pagedMasuk', 'pagedKeluar',
            'totalMasuk', 'totalKeluar', 'totalPenyesuaian'
        ));
    }

    public function export()
    {
        $snapshots = $this->snapshots((int) auth()->id(), $this->periods->current());
        $statuses = Periode::whereIn('periode', $snapshots->pluck('periode'))->pluck('status', 'periode')->all();

        return Excel::download(new CashFlowExport($snapshots, $statuses), 'stok-kas-semua-periode.xlsx');
    }

    public function exportPeriod(string $periode)
    {
        $periode = $this->periods->normalize($periode);
        $userId = (int) auth()->id();
        $snapshot = $this->cashFlowService->recalculateSnapshot($userId, $periode);
        $status = Periode::where('periode', $periode)->value('status') ?? 'OPEN';
        $transactions = $this->cashFlowService->details($userId, $periode);

        return Excel::download(
            new CashFlowExport(collect([$snapshot]), [$periode => $status], $transactions),
            'stok-kas-'.$this->periods->key($periode).'.xlsx'
        );
    }

    private function snapshots(int $userId, string $currentPeriod)
    {
        $periods = Periode::orderBy('periode')->pluck('periode')->push($currentPeriod)->unique()->sort();
        foreach ($periods as $period) $this->cashFlowService->recalculateSnapshot($userId, $period);

        return StockCashFlowSnapshot::where('user_id', $userId)->orderByDesc('periode')->get();
    }

   

public function transactionsPartial(Request $request, string $periode)
{
    $periode = $this->periods->normalize($periode);
    $userId = (int) auth()->id();
    $transactions = $this->cashFlowService->details($userId, $periode);

    $section = $request->input('section', 'masuk'); // 'masuk' atau 'keluar'
    $perPage = 10;

    // Ambil data sesuai section yang diminta
    $allItems = collect($transactions[$section] ?? [])
        ->sortByDesc('date')
        ->values();

    $total = $allItems->sum('nominal');

    // Baca halaman dari query string section-nya
    $pageName = $section . '_page';
    $page = LengthAwarePaginator::resolveCurrentPage($pageName);

    $paginator = new LengthAwarePaginator(
        $allItems->forPage($page, $perPage)->values(),
        $allItems->count(),
        $perPage,
        $page,
        [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]
    );

    // Ambil halaman section LAIN untuk appends
    $otherPageName = $section === 'masuk' ? 'keluar_page' : 'masuk_page';
    $otherPage = LengthAwarePaginator::resolveCurrentPage($otherPageName);

    $totalLabel = $section === 'masuk' ? $total : -$total; // tanda minus untuk keluar
    $sectionId  = 'sec-' . $section;

    return view('cash-flow.partials.transaction-section', [
        'section'      => $section,
        'paginator'    => $paginator,
        'total'        => $total,
        'totalLabel'   => $totalLabel,
        'sectionId'    => $sectionId,
        'otherPage'    => $otherPage,
        'otherSection' => $section === 'masuk' ? 'keluar' : 'masuk',
    ]);
}
}
~~~~

## FILE: app/Http/Controllers/CompanyController.php

SHA-256: `7b0f6944519920e24e514ed7efc3f9fa8a8fe819a77a390e022df0c6c2772596`

~~~~php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->create();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //Minta asisten gudang ambil semua data dari tabel company
        $companies = Company::all();

        return view('companies.create', compact('companies'));

    }

    /**
     * Menyimpan data baru dari form ke Database
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'code' => 'required|unique:company,code',
            'nama' => 'required',
        ]);

        //Minta assiten gedung untuk membuat data baru di database
        Company::create([
            'code' => $request->code,
            'nama' => $request->nama,
            // Jika checkbox dicentang maka 1, jika tidak maka 0
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        //Jika berhasil, kembalika user ke halaman form dengan pesan sukses
        return redirect()->route('companies.create')->with('success', 'Master Company berhasil ditambahkan!');
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }

    /**
     * Menampilkan Form Edit yang sudah terisi data lama
     */
    public function edit($id)
    {
        $company = Company::findOrFail($id);
        return view('companies.edit', compact('company'));
    }
    /**
 * Menyimpan Data Hasil Editan ke Database
 */
public function update(Request $request, $id)
{
    // Validasi: code dan nama wajib diisi. 
    // unique:company,code,$id artinya "unik di tabel company, kolom code, abaikan baris dengan id ini"
    $request->validate([
        'code' => 'required|unique:company,code,' . $id,
        'nama' => 'required',
    ]);

    $company = Company::findOrFail($id);
    
    // Update semua field yang diizinkan, termasuk code
    $company->update([
        'code' => $request->code,
        'nama' => $request->nama,
        'is_active' => $request->has('is_active') ? 1 : 0 
    ]);

    return redirect()->route('companies.create')->with('success', 'Master Company berhasil diperbarui!');
}

    /**
     * Menghapus data dari database 
     */
    public function destroy($id)
    {
        $company = Company::findOrFail($id);
        $company->delete();

        return redirect()->route('companies.create')->with('success', 'Master Company berhasil dihapus!');
    }
}

~~~~

## FILE: app/Http/Controllers/Controller.php

SHA-256: `25d1c1ef8e6cc8a376553faacfba2b07d9dfaee9bdbb84f14f77517580e9deb1`

~~~~php
<?php

namespace App\Http\Controllers;

abstract class Controller
{
    //
}

~~~~

## FILE: app/Http/Controllers/CostCategoryController.php

SHA-256: `edc153fcaccebb98925dc95dbecd0b4ae3343695175078adcef13692664d86b6`

~~~~php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CostCategory;
use Illuminate\Database\QueryException;

class CostCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->create();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $costCategories = CostCategory::all();
        return view('cost_categories.create', compact('costCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'code' => 'required|unique:cost_category,code',
            'description' => 'required',
        ]);

        CostCategory::create([
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);
        return redirect()->route('cost_categories.create')->with('success', 'Master Cost Category berhasil ditambahkan!');


    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $costCategories = CostCategory::findOrFail($id);
        return view('cost_categories.edit', compact('costCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validasi code dengan mengabaikan ID record yang sedang diedit
        $request->validate([
            'code' => 'required|unique:cost_category,code,' . $id,
            'description' => 'required',
        ]);

        $costCategory = CostCategory::findOrFail($id);
        $costCategory->update([
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('cost_categories.create')->with('success', 'Master Cost Category berhasil diupdate!');
    }
    /**
     * Remove the specified resource from storage.
     */
   public function destroy($id)
    {
        try {
            $costCategory = CostCategory::findOrFail($id);
            $costCategory->delete();

            return redirect()->back()->with('success', 'Cost Category berhasil dihapus!');

        } catch (QueryException $e) {
            // Cek apakah errornya adalah kode 23000 (Integrity Constraint Violation / Foreign Key)
            if ($e->getCode() == "23000") {
                return redirect()->back()->withErrors(['error' => 'GAGAL MENGHAPUS: Kategori Biaya ini tidak bisa dihapus karena sudah digunakan pada riwayat Transaksi.']);
            }
            
            // Tangkap error database lainnya
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan database: ' . $e->getMessage()]);
        }
    }
}

~~~~

## FILE: app/Http/Controllers/DashboardMasterController.php

SHA-256: `f5a7e07ad59b226944e9b3e12595ad22dab94b4687243490c91882d51d027508`

~~~~php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Company;
use App\Models\Level;
use App\Models\Position;
use App\Models\Wilayah;
use App\Models\EventHourGroup;
use App\Models\PlafonEventDalamKota;
use App\Models\PlafonEventLuarKota;
use App\Models\User;
use App\Models\CostCategory;
use App\Models\WeekDayGroup;

class DashboardMasterController extends Controller
{
    public function index()
    {
        // Menghitung jumlah total data di database untuk statistik kartu
        $counts = [
            'users' => User::count(),
            'roles' => Role::count(),
            'companies' => Company::count(),
            'levels' => Level::count(),
            'positions' => Position::count(),
            'wilayah' => Wilayah::count(),
            'event_hours' => EventHourGroup::count(),
            'plafon_dalam' => PlafonEventDalamKota::count(),
            'plafon_luar' => PlafonEventLuarKota::count(),
            'cost_categories' => CostCategory::count(),
            'week_day_groups' => WeekDayGroup::count(),
          ];

        return view('dashboard.master_data', compact('counts'));
    }
}
~~~~

## FILE: app/Http/Controllers/EventHourGroupController.php

SHA-256: `8a98efd2f58b728c01556db3fa153fe6bbd01a2e7c777955abe1dc2cc6d8f995`

~~~~php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EventHourGroup;

class EventHourGroupController extends Controller
{
    public function create()
    {
        $eventHourGroups = EventHourGroup::all();
        return view('event_hour_groups.create', compact('eventHourGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            // Hapus aturan unique-nya
            'code' => 'nullable|in:8 Jam,12 Jam,16 Jam', 
            'description' => 'nullable|string|max:255',
        ]);

        EventHourGroup::create([
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('event_hour_groups.create')->with('success', 'Master Event Hour Group berhasil ditambahkan!');
    }

    public function edit(string $id)
    {
        $eventHourGroup = EventHourGroup::findOrFail($id);
        return view('event_hour_groups.edit', compact('eventHourGroup'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'code' => 'nullable|in:8 Jam,12 Jam,16 Jam|unique:event_hour_group,code,' . $id,
            'description' => 'nullable|string|max:255',
        ]);

        $eventHourGroup = EventHourGroup::findOrFail($id);
        $eventHourGroup->update([
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('event_hour_groups.create')->with('success', 'Master Event Hour Group berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        $eventHourGroup = EventHourGroup::findOrFail($id);
        $eventHourGroup->delete();

        return redirect()->route('event_hour_groups.create')->with('success', 'Master Event Hour Group berhasil dihapus!');
    }

    // Biarkan index dan show kosong
    public function index()
    {
        return $this->create();
    }

    public function show(string $id) {}
}

~~~~

## FILE: app/Http/Controllers/LevelController.php

SHA-256: `944de7df1cdafd7d065fbb756f460c281fe55477c252d27bdd37763289367703`

~~~~php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Level;

class LevelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->create();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //

        $levels = Level::all();

        return view('levels.create', compact('levels'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        $request->validate([
            'code' => 'required|unique:level,code',
            'nama' => 'required',
        ]);

        Level::create([
            'code' => $request->code,
            'nama' => $request->nama,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('levels.create')->with('success', 'Master Level berhasil ditambahkan!');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $level = Level::findOrfail($id);
        return view('levels.edit', compact('level'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //

        $request->validate([
            'nama' => 'required',
        ]);

        $levels = Level::findOrFail($id);
        $levels->update([
            'nama' => $request->nama,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('levels.create')->with('success', 'Master Level berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $levels = Level::findOrFail($id);
        $levels->delete();

        return redirect()->route('levels.create')->with('success', 'Master Level berhasil dihapus!');
    }


}

~~~~

## FILE: app/Http/Controllers/NotificationController.php

SHA-256: `4667c5390446af301d190f5ab69e8929622aedd973c7b4e6e00219d4d83d83b7`

~~~~php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function unread()
    {
        $user = auth()->user();
        $query = $user->unreadNotifications()->where('type', '!=', 'App\\Notifications\\ReimbursementUpdated');
        $notifs = (clone $query)->take(8)->get();

        return response()->json([
            'count' => $query->count(),
            'items' => $notifs->map(fn($n) => [
                'id'      => $n->id,
                'title'   => $n->data['title'] ?? 'Notifikasi',
                'message' => $n->data['message'] ?? '',
                'url'     => $n->data['url'] ?? '#',
                'time'    => $n->created_at->diffForHumans(),
            ]),
        ]);
    }

    public function readAll()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }

    public function read($id)
    {
        $n = auth()->user()->notifications()->findOrFail($id);
        $n->markAsRead();
        return response()->json(['success' => true]);
    }
}

~~~~

## FILE: app/Http/Controllers/PengajuanBsController.php

SHA-256: `ae921625dded2b8285eb6aa3c78653ef465a7074973bf8f320c1593d71ddfdd4`

~~~~php
<?php

namespace App\Http\Controllers;

use App\Models\PengajuanBs;
use App\Models\User;
use App\Models\BsActivityLog;
use App\Services\BonSementaraService;
use App\Services\DocumentNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengajuanBsController extends Controller
{
    public function index()
    {
        $data = PengajuanBs::where('user_id', auth()->id())->latest()->get();
        return view('pengajuan-bs.index', compact('data'));
    }

    public function create(BonSementaraService $bsService)
    {
        $periode   = app(\App\Services\PeriodService::class)->current();
        $sisaSaldo = $bsService->getRealtimeSisaSaldo(auth()->id(), $periode);
        $members   = User::where('id', '!=', auth()->id())->where('is_active', 1)->get();

        return view('pengajuan-bs.create', compact('sisaSaldo', 'members', 'periode'));
    }

    public function store(Request $request, BonSementaraService $bsService)
    {
        $request->validate([
            'tipe_pengajuan'    => 'required|in:operasional_sales,perjalanan_dinas',
            'nominal_pengajuan' => 'required|numeric|decimal:0,2|min:1|max:9999999999999.99',
            'description'       => 'nullable|string|max:1000',
            'member_ids'        => 'nullable|array',
         'member_ids.*'      => 'distinct|exists:user,id',
        ]);

        $periode  = app(\App\Services\PeriodService::class)->current();
        $snapshot = $bsService->getRealtimeSisaSaldo(auth()->id(), $periode);

        DB::transaction(function () use ($request, $periode, $snapshot) {
            $tanggal = \App\Support\AppTime::now()->toDateString();
            $docNo = app(DocumentNumberService::class)->next('BS', $tanggal);

            $bs = PengajuanBs::create([
                'document_no'              => $docNo,
                'tanggal'                  => $tanggal,
                'user_id'                  => auth()->id(),
                'atasan_user_id'           => auth()->user()->atasan_user_id,
                'periode'                  => $periode,
                'tipe_pengajuan'           => $request->tipe_pengajuan,
                'nominal_sisa_bs_snapshot' => $snapshot,
                'nominal_pengajuan'        => $request->nominal_pengajuan,
                'description'              => $request->description,
                'status'                   => 'PENDING_ATASAN',
            ]);

            // 🎯 AUDIT TRAIL
            $tipeLabel = $bs->tipe_pengajuan === 'perjalanan_dinas' ? 'Perjalanan Dinas' : 'Operasional Sales';
            BsActivityLog::record(
                $bs->id,
                'CREATED',
                'Pengajuan Dibuat',
                "Pengajuan BS ({$tipeLabel}) dibuat untuk keperluan: " . ($bs->description ?? '-'),
                $bs->nominal_pengajuan
            );

            // 🎯 Members HANYA untuk Perjalanan Dinas
            if ($request->tipe_pengajuan === 'perjalanan_dinas' && $request->filled('member_ids')) {
                $bs->members()->attach($request->member_ids);
            }

            // 🎯 Notifikasi ke Atasan
            if ($bs->atasan_user_id) {
                \App\Services\BsNotifications::afterCommit(fn () => $bs->atasan?->notify(new \App\Notifications\BsCreated($bs)));
            }
        });

        return redirect()->route('pengajuan-bs.index')->with('success', 'Pengajuan BS berhasil dibuat.');
    }

    public function show(PengajuanBs $bs)
    {
        // 🎯 PRIVACY GUARD
        $isAuthorized = auth()->user()->canSeeAllTransactions() ||
                        $bs->user_id === auth()->id() ||
                        $bs->members->contains(auth()->id());

        if (!$isAuthorized) {
            abort(403, 'Anda tidak memiliki akses ke riwayat BS ini.');
        }

        $logs = BsActivityLog::where('pengajuan_bs_id', $bs->id)
                    ->with('actor')
                    ->orderBy('created_at', 'asc')
                    ->get();

        return view('pengajuan-bs.show', compact('bs', 'logs'));
    }
}

~~~~

## FILE: app/Http/Controllers/PeriodeController.php

SHA-256: `351bb1517a5e494683831b3f578cc50965e867470a8cfd283f04f35304241bdc`

~~~~php
<?php

namespace App\Http\Controllers;

use App\Models\Periode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeriodeController extends Controller
{
    public function index()
    {
        $periods = Periode::orderBy('periode', 'desc')->get();
        $currentMonth = \App\Support\AppTime::now()->format('Y-m'); // 🎯 Untuk Guard UI di View

        return view('periode.index', compact('periods', 'currentMonth'));
    }

    /** 🎯 RIWAYAT: semua transaksi dalam satu periode */
    public function show(string $periode)
    {
        $periode = app(\App\Services\PeriodService::class)->normalize($periode);

        $row    = Periode::where('periode', $periode)->first();
        $status = $row?->status ?? ($periode < \App\Support\AppTime::now()->format('Y-m') ? 'CLOSED' : 'OPEN');

        // Audit: siapa yang menutup
        $closedByName = $row?->closed_by
            ? DB::table('user')->where('id', $row->closed_by)->value('nama')
            : null;

        // Ambil semua transaksi bulan itu + nama user (satu pola untuk semua tabel)
        $list = function (string $table) use ($periode) {
            return DB::table($table . ' as h')
                ->leftJoin('user as u', 'u.id', '=', 'h.user_id')
                ->whereRaw("DATE_FORMAT(h.tanggal, '%Y-%m') = ?", [$periode])
                ->orderBy('h.tanggal', 'desc')
                ->select(['h.*', 'u.nama as user_nama'])
                ->get();
        };

        $bs          = $list('pengajuan_bs'); // ⚠️ sesuaikan nama tabel BS jika berbeda
        $operasional = $list('transaksi_operasional_area_sales_header');
        $perdin      = $list('transaksi_perjalanan_dinas_header');
        $eventDalam  = $list('perongkosan_event_dalam_kota');
        $eventLuar   = $list('perongkosan_event_luar_kota');

        // Total nominal (header join detail untuk OPS & PERDIN)
        $sumDetail = function (string $head, string $detail) use ($periode) {
            return DB::table($head . ' as h')
                ->join($detail . ' as d', 'd.transaksi_id', '=', 'h.transaksi_id')
                ->whereRaw("DATE_FORMAT(h.tanggal, '%Y-%m') = ?", [$periode])
                ->where('h.status', 'VALIDATED')
                ->when($detail === 'transaksi_perjalanan_dinas_detail', fn ($q) => $q->whereNull('d.deleted_at'))
                ->sum('d.nominal');
        };

        $totals = [
            'bsApproved'  => \App\Models\ApprovalBsFinance::where('tanggal_cair', '>=', $periode.'-01')->where('tanggal_cair', '<', app(\App\Services\PeriodService::class)->next($periode).'-01')->sum('nominal_approved'),
            'operasional' => $sumDetail('transaksi_operasional_area_sales_header', 'transaksi_operasional_area_sales_detail'),
            'perdin'      => $sumDetail('transaksi_perjalanan_dinas_header', 'transaksi_perjalanan_dinas_detail'),
            'eventDalam'  => $eventDalam->sum('nominal'),
            'eventLuar'   => $eventLuar->sum('nominal'),
        ];

        return view('periode.show', compact(
            'periode', 'status', 'row', 'closedByName',
            'bs', 'operasional', 'perdin', 'eventDalam', 'eventLuar',
            'totals'
        ));
    }

    public function close(Periode $periode)
    {
        app(\App\Services\BsPeriodClosingService::class)->close($periode->id);
        return back()->with('success', "Periode {$periode->periode} berhasil ditutup dan saldo diteruskan ke periode berikutnya.");
    }

    public function reopen(Request $request, Periode $periode)
    {
        $request->validate(['reason' => 'required|string|min:5|max:255']);
        app(\App\Services\BsPeriodClosingService::class)->reopen($periode->id, $request->reason);
        return back()->with('success', "Periode {$periode->periode} dibuka kembali.");
    }
}

~~~~

## FILE: app/Http/Controllers/PerongkosanEventDalamKotaController.php

SHA-256: `54213462897a5ec1759078f1620e275a63f6d99c27285c6e777379b9eef37bfd`

~~~~php
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

~~~~

## FILE: app/Http/Controllers/PerongkosanEventLuarKotaController.php

SHA-256: `8bc0fb4849cc6c724325d476cad7ce8bc542a69b99902b42c0bbfbb63a67678e`

~~~~php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PerongkosanEventLuarKota;
use App\Models\PlafonEventLuarKota;
use App\Models\EventHourGroup;
use App\Models\Wilayah;
use App\Models\User;
use App\Services\PlafonService;
use App\Notifications\TransaksiOverPlafon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerongkosanEventLuarKotaController extends Controller
{
    public function create()
    {
        $jam_kerja = EventHourGroup::all();
        return view('perongkosan_event_luar_kota.create', compact('jam_kerja'));
    }

    public function getProvinsiByTeritory(Request $request)
    {
        $teritory = $request->get('teritory');
        $provinsis = Wilayah::where('teritory', $teritory)
                       ->whereNull('parent_id')
                       ->select('id', 'nama')
                       ->orderBy('nama', 'asc')
                       ->get();
        return response()->json($provinsis);
    }

    public function getKotaByProvinsi(Request $request)
    {
        $provinsiId = $request->get('provinsi_id');
        $kotas = Wilayah::where('parent_id', $provinsiId)
                    ->select('id', 'nama')
                    ->orderBy('nama', 'asc')
                    ->get();
        return response()->json($kotas);
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
            PlafonEventLuarKota::class,
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
            'lokasi_event_id'    => 'required|exists:wilayah,id',
            'teritory'           => 'required|in:Jawa,Luar Jawa',
            'is_libur'           => 'required|in:0,1',
            'jam_kerja_event_id' => 'required|exists:event_hour_group,id',
            'description'        => 'required|string|max:255',
            'nominal'            => 'required|numeric|min:1',
            'foto_event_1'       => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'foto_event_2'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_event_3'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $lokasi = Wilayah::find($request->lokasi_event_id);
        if ($lokasi && $lokasi->teritory !== $request->teritory) {
            return redirect()->back()
                ->withErrors(['lokasi_event_id' => '❌ Lokasi event tidak sesuai dengan Territory yang dipilih!'])
                ->withInput();
        }

        $data = $request->except(['foto_event_1', 'foto_event_2', 'foto_event_3', 'teritory']);
        $data['user_id'] = Auth::id();
        $data['jarak_km'] = 0;
        $data['durasi_jam'] = 0;

        $data['periode'] = app(\App\Services\PeriodService::class)->ofDate($request->tanggal);

        $prefix = 'EV-LKR-';
        $datePrefix = \App\Support\AppTime::now()->format('ymd');
        $todayCount = PerongkosanEventLuarKota::where('transaksi_id', 'like', $prefix . $datePrefix . '%')->count();
        $sequence = str_pad($todayCount + 1, 3, '0', STR_PAD_LEFT);
        $data['transaksi_id'] = $prefix . $datePrefix . '-' . $sequence;

        foreach (['foto_event_1', 'foto_event_2', 'foto_event_3'] as $fotoField) {
            if ($request->hasFile($fotoField)) {
                $data[$fotoField] = $request->file($fotoField)->store('events_luar_kota', 'public');
            }
        }

        $event = PerongkosanEventLuarKota::create($data);
        $user = Auth::user();

        $cek = $plafonService->checkEvent(
            PlafonEventLuarKota::class,
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
                $event->transaksi_id . ' (Event Luar Kota)',
                $event->nominal, $cek['plafon_value'], $cek['selisih'],
                route('perongkosan_event_luar_kota.index')
            ));
        }

        // 🎯 TRIGGER CASH FLOW
        event(new \App\Events\CashFlowChanged($event->user_id, $event->periode));

        return redirect()->route('perongkosan_event_luar_kota.index')
            ->with('success', 'Transaksi Event Luar Kota (' . $event->transaksi_id . ') berhasil disimpan!');
    }

    public function index()
    {
        $query = PerongkosanEventLuarKota::with(['user', 'wilayah', 'jamKerja']);

        if (!auth()->user()->canSeeAllTransactions()) {
            $query->where('user_id', auth()->id());
        }

        $events = $query->orderBy('tanggal', 'desc')->paginate(10);

        return view('perongkosan_event_luar_kota.index', compact('events'));
    }

    public function show($id)
    {
        $event = PerongkosanEventLuarKota::with(['user', 'wilayah', 'jamKerja'])->findOrFail($id);
        abort_unless(auth()->user()->canAccessTransaction($event), 403, 'Anda tidak memiliki akses untuk melihat transaksi ini.');

        return view('perongkosan_event_luar_kota.show', compact('event'));
    }

    public function edit($id)
    {
        $event = PerongkosanEventLuarKota::with('wilayah')->findOrFail($id);

        if (!auth()->user()->canAccessTransaction($event)) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit transaksi ini.');
        }

        $jam_kerja = EventHourGroup::all();
        return view('perongkosan_event_luar_kota.edit', compact('event', 'jam_kerja'));
    }

    public function update(Request $request, $id, PlafonService $plafonService)
    {
        $event = PerongkosanEventLuarKota::findOrFail($id);

        if (!auth()->user()->canAccessTransaction($event)) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah transaksi ini.');
        }

        $request->validate([
            'tanggal'            => 'required|date',
            'lokasi_event_id'    => 'required|exists:wilayah,id',
            'teritory'           => 'required|in:Jawa,Luar Jawa',
            'is_libur'           => 'required|in:0,1',
            'jam_kerja_event_id' => 'required|exists:event_hour_group,id',
            'description'        => 'required|string|max:255',
            'nominal'            => 'required|numeric|min:1',
            'foto_event_1'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_event_2'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_event_3'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $lokasi = Wilayah::find($request->lokasi_event_id);
        if ($lokasi && $lokasi->teritory !== $request->teritory) {
            return redirect()->back()
                ->withErrors(['lokasi_event_id' => '❌ Lokasi event tidak sesuai dengan Territory yang dipilih!'])
                ->withInput();
        }

        $data = $request->except(['foto_event_1', 'foto_event_2', 'foto_event_3', 'teritory']);
        $data['lokasi_event_id'] = $request->lokasi_event_id;
        $data['user_id'] = $event->user_id;
        $data['jarak_km'] = $event->jarak_km;
        $data['durasi_jam'] = $event->durasi_jam;

        foreach (['foto_event_1', 'foto_event_2', 'foto_event_3'] as $field) {
            if ($request->hasFile($field)) {
                if ($event->$field && Storage::disk('public')->exists($event->$field)) {
                    $oldPhoto = $event->$field;
                    DB::afterCommit(fn () => Storage::disk('public')->delete($oldPhoto));
                }
                $data[$field] = $request->file($field)->store('events_luar_kota', 'public');
            }
        }

        // 🎯 Simpan periode LAMA sebelum update
        $oldPeriode = app(\App\Services\PeriodService::class)->ofDate($event->tanggal);

        $wasOver = (bool) $event->is_over_plafon;
        $event->update($data);

        // Hitung ulang periode berdasarkan tanggal baru
        $newPeriode = substr($event->tanggal, 0, 7);
        if ($newPeriode !== $event->periode) {
            $event->update(['periode' => $newPeriode]);
        }

        $user = $event->user;
        $cek = $plafonService->checkEvent(
            PlafonEventLuarKota::class,
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
                $event->transaksi_id . ' (Event Luar Kota)',
                $event->nominal, $cek['plafon_value'], $cek['selisih'],
                route('perongkosan_event_luar_kota.index')
            ));
        }

        // 🎯 TRIGGER CASH FLOW — periode baru
        event(new \App\Events\CashFlowChanged($event->user_id, $event->periode));

        // 🎯 Jika periode berubah, recalculate periode LAMA juga
        if ($oldPeriode !== $event->periode) {
            event(new \App\Events\CashFlowChanged($event->user_id, $oldPeriode));
        }

        return redirect()->route('perongkosan_event_luar_kota.index')->with('success', 'Data berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $event = PerongkosanEventLuarKota::findOrFail($id);

        if (!auth()->user()->canAccessTransaction($event)) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus transaksi ini.');
        }

        // 🎯 Simpan data untuk trigger SEBELUM dihapus
        $userId = $event->user_id;
        $periode = app(\App\Services\PeriodService::class)->ofDate($event->tanggal);

        foreach (['foto_event_1', 'foto_event_2', 'foto_event_3'] as $field) {
            if ($event->$field && Storage::disk('public')->exists($event->$field)) {
                $oldPhoto = $event->$field;
                DB::afterCommit(fn () => Storage::disk('public')->delete($oldPhoto));
            }
        }

        $event->delete();

        // 🎯 TRIGGER CASH FLOW
        event(new \App\Events\CashFlowChanged($userId, $periode));

        return redirect()->route('perongkosan_event_luar_kota.index')->with('success', 'Data berhasil dihapus!');
    }

    public function export(Request $request)
    {
        $periode = $request->get('periode');

        $query = PerongkosanEventLuarKota::with(['user', 'wilayah']);
        if (!auth()->user()->canSeeAllTransactions()) {
            $query->where('user_id', auth()->id());
        }
        if ($periode) $query->where('periode', $periode);

        $events = $query->orderBy('tanggal')->get();
        $jams = \App\Models\EventHourGroup::pluck('code', 'id');

        $headings = ['Transaksi ID', 'Tanggal', 'Periode', 'User', 'Lokasi (Provinsi)', 'Hari Libur', 'Jam Kerja Event', 'Deskripsi', 'Nominal', 'Plafon', 'Status Plafon'];
        $rows = [];
        $total = 0;

        foreach ($events as $e) {
            $total += (float) $e->nominal;
            $rows[] = [
                $e->transaksi_id,
                \Carbon\Carbon::parse($e->tanggal)->format('d/m/Y'),
                $e->periode ?? '',
                $e->user->nama ?? '',
                $e->wilayah->nama ?? '',
                $e->is_libur ? 'Ya' : 'Tidak',
                $jams[$e->jam_kerja_event_id] ?? '',
                $e->description,
                (float) $e->nominal,
                $e->plafon_value ? (float) $e->plafon_value : '',
                $e->is_over_plafon ? 'OVER' : ($e->plafon_value ? 'SESUAI' : '-'),
            ];
        }
        $rows[] = ['TOTAL', '', '', '', '', '', '', '', (float) $total, '', ''];

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LaporanTableExport($headings, $rows, ['I', 'J']),
            'laporan-event-luar-kota-' . ($periode ?: 'semua-periode') . '.xlsx'
        );
    }
}

~~~~

## FILE: app/Http/Controllers/PlafonEventDalamKotaController.php

SHA-256: `d0756334f714b894bdc20c4a4cb5d48812330e25c6645cf25d2adbe0f3f6c807`

~~~~php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlafonEventDalamKota;
use App\Models\Level;
use App\Models\WeekdayGroup;
use App\Models\EventHourGroup;

class PlafonEventDalamKotaController extends Controller
{
    public function index()
    {
        return $this->create();
    }

    public function create()
    {
        $plafons = PlafonEventDalamKota::with(['level', 'weekdayGroup', 'eventHourGroup'])->get();
        $levels = Level::all();
        $weekdayGroups = WeekdayGroup::all();
        $eventHourGroups = EventHourGroup::all();

        return view('plafon_event_dalam_kota.create', compact('plafons', 'levels', 'weekdayGroups', 'eventHourGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_berlaku' => 'required|date',
            'level_id' => 'required',
            'kelompok_hari_kerja_id' => 'required',
            'hari_event' => 'nullable|in:Reguler,Sabtu,Libur',
            'jam_kerja_event_id' => 'required',
            'description' => 'required|string|max:255',
            'default_value' => 'required|numeric'
        ]);

         // ✅ GUARD: cegah kombinasi dimensi + tanggal yang sama persis
    $duplikat = PlafonEventDalamKota::where('level_id', $request->level_id)
        ->where('kelompok_hari_kerja_id', $request->kelompok_hari_kerja_id)
        ->where('hari_event', $request->hari_event)
        ->where('jam_kerja_event_id', $request->jam_kerja_event_id)
        ->whereDate('tanggal_berlaku', $request->tanggal_berlaku)
        ->exists();

    if ($duplikat) {
        return back()->withErrors([
            'default_value' => '❌ Kombinasi Level + Kelompok Hari + Hari Event + Jam Kerja + Tanggal Berlaku yang sama sudah ada. '
                             . 'Edit baris lama, atau gunakan Tanggal Berlaku yang berbeda (effective dating).'
        ])->withInput();
    }

        PlafonEventDalamKota::create($request->all());

        return redirect()->route('plafon-event-dalam-kota.create')->with('success', 'Setting Plafon Dalam Kota berhasil ditambahkan!');
    }

    // 🎯 METHOD BARU: Form Edit
    public function edit(string $id)
    {
        $plafon = PlafonEventDalamKota::findOrFail($id);
        $levels = Level::all();
        $weekdayGroups = WeekdayGroup::all();
        $eventHourGroups = EventHourGroup::all();

        return view('plafon_event_dalam_kota.edit', compact('plafon', 'levels', 'weekdayGroups', 'eventHourGroups'));
    }

    // 🎯 METHOD BARU: Proses Update
    public function update(Request $request, string $id)
    {
        $request->validate([
            'tanggal_berlaku' => 'required|date',
            'level_id' => 'required',
            'kelompok_hari_kerja_id' => 'required',
            'hari_event' => 'nullable|in:Reguler,Sabtu,Libur',
            'jam_kerja_event_id' => 'required',
            'description' => 'required|string|max:255',
            'default_value' => 'required|numeric'
        ]);

        $plafon = PlafonEventDalamKota::findOrFail($id);
        $plafon->update($request->all());

        return redirect()->route('plafon-event-dalam-kota.create')
                         ->with('success', 'Setting Plafon Dalam Kota berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        $plafon = PlafonEventDalamKota::findOrFail($id);
        $plafon->delete();

        return redirect()->route('plafon-event-dalam-kota.create')->with('success', 'Setting Plafon Dalam Kota berhasil dihapus!');
    }
}

~~~~

## FILE: app/Http/Controllers/PlafonEventLuarKotaController.php

SHA-256: `215da098d4f877990b2fc7c765944667f63875f1b32f42e1c85db970858bc558`

~~~~php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlafonEventLuarKota;
use App\Models\Level;
use App\Models\WeekdayGroup;
use App\Models\EventHourGroup;

class PlafonEventLuarKotaController extends Controller
{
    public function index()
    {
        return $this->create();
    }

    public function create()
    {
        $plafons = PlafonEventLuarKota::with(['level', 'weekdayGroup', 'eventHourGroup'])
            ->orderBy('tanggal_berlaku', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $levels = Level::all();
        $weekdayGroups = WeekdayGroup::all();
        $eventHourGroups = EventHourGroup::all();

        return view('plafon_event_luar_kota.create', compact(
            'plafons',
            'levels',
            'weekdayGroups',
            'eventHourGroups'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_berlaku'          => 'required|date',
            'level_id'                 => 'required|exists:level,id',
            'kelompok_hari_kerja_id'   => 'required|exists:weekday_group,id',
            'hari_event'               => 'required|in:Reguler,Sabtu,Libur',
            'jam_kerja_event_id'       => 'required|exists:event_hour_group,id',
            'description'              => 'required|string|max:255',
            'default_value'            => 'required|numeric|min:0',
        ]);

        // Cegah duplikasi kombinasi plafon yang sama persis
        $duplikat = PlafonEventLuarKota::where('tanggal_berlaku', $request->tanggal_berlaku)
            ->where('level_id', $request->level_id)
            ->where('kelompok_hari_kerja_id', $request->kelompok_hari_kerja_id)
            ->where('hari_event', $request->hari_event)
            ->where('jam_kerja_event_id', $request->jam_kerja_event_id)
            ->exists();

        if ($duplikat) {
            return back()
                ->withErrors([
                    'default_value' => 'Kombinasi Tanggal Berlaku, Level, Kelompok Hari Kerja, Hari Event, dan Jam Kerja Event sudah ada. Gunakan Edit atau buat Tanggal Berlaku yang berbeda.'
                ])
                ->withInput();
        }

        PlafonEventLuarKota::create($request->only([
            'tanggal_berlaku',
            'level_id',
            'kelompok_hari_kerja_id',
            'hari_event',
            'jam_kerja_event_id',
            'description',
            'default_value',
        ]));

        return redirect()
            ->route('plafon-event-luar-kota.create')
            ->with('success', 'Setting Plafon Luar Kota berhasil ditambahkan!');
    }

    public function edit(string $id)
    {
        $plafon = PlafonEventLuarKota::findOrFail($id);
        $levels = Level::all();
        $weekdayGroups = WeekdayGroup::all();
        $eventHourGroups = EventHourGroup::all();

        return view('plafon_event_luar_kota.edit', compact(
            'plafon',
            'levels',
            'weekdayGroups',
            'eventHourGroups'
        ));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'tanggal_berlaku'          => 'required|date',
            'level_id'                 => 'required|exists:level,id',
            'kelompok_hari_kerja_id'   => 'required|exists:weekday_group,id',
            'hari_event'               => 'required|in:Reguler,Sabtu,Libur',
            'jam_kerja_event_id'       => 'required|exists:event_hour_group,id',
            'description'              => 'required|string|max:255',
            'default_value'            => 'required|numeric|min:0',
        ]);

        $plafon = PlafonEventLuarKota::findOrFail($id);

        // Cegah duplikasi selain record yang sedang diedit
        $duplikat = PlafonEventLuarKota::where('tanggal_berlaku', $request->tanggal_berlaku)
            ->where('level_id', $request->level_id)
            ->where('kelompok_hari_kerja_id', $request->kelompok_hari_kerja_id)
            ->where('hari_event', $request->hari_event)
            ->where('jam_kerja_event_id', $request->jam_kerja_event_id)
            ->where('id', '!=', $plafon->id)
            ->exists();

        if ($duplikat) {
            return back()
                ->withErrors([
                    'default_value' => 'Kombinasi Tanggal Berlaku, Level, Kelompok Hari Kerja, Hari Event, dan Jam Kerja Event sudah ada pada data lain.'
                ])
                ->withInput();
        }

        $plafon->update($request->only([
            'tanggal_berlaku',
            'level_id',
            'kelompok_hari_kerja_id',
            'hari_event',
            'jam_kerja_event_id',
            'description',
            'default_value',
        ]));

        return redirect()
            ->route('plafon-event-luar-kota.create')
            ->with('success', 'Setting Plafon Luar Kota berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        $plafon = PlafonEventLuarKota::findOrFail($id);
        $plafon->delete();

        return redirect()
            ->route('plafon-event-luar-kota.create')
            ->with('success', 'Setting Plafon Luar Kota berhasil dihapus!');
    }
}

~~~~

## FILE: app/Http/Controllers/PositionController.php

SHA-256: `b043d688326531c794faa21fd97b153058c841d1b4ce9632b5398e7377ac9f32`

~~~~php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Position;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->create();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $positions = Position::all();

        return view('positions.create', compact('positions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        $request->validate([
            'code' => 'required|unique:position,code',
            'nama' => 'required',
        ]);

        Position::create([
            'code' => $request->code,
            'nama' => $request->nama,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('positions.create')->with('success', 'Master Positions berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $positions = Position::findOrFail($id);
        return view('positions.edit', compact('positions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $request->validate([
            'nama' => 'required',
            // 'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        $positions = Position::findOrFail($id);
        $positions->update([
            'nama' => $request->nama,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('positions.create')->with('success', 'Master Position berhasil diperbarui!');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $positions = Position::findOrFail($id);

        $positions->delete();

        return redirect()->route('positions.create')->with('success', 'Master Position berhasil dihapus!');
    }
}

~~~~

## FILE: app/Http/Controllers/ProfileController.php

SHA-256: `0db4dedcc6fb6457b550f3d8876a61ee0ab25282f98e80d221495fe58baf9bcb`

~~~~php
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        // Ambil data user yang sedang login beserta relasinya
        $user = Auth::user()->load(['role', 'company', 'level', 'position', 'homebase', 'atasan.position']);
        $bawahan = $user->bawahan()
            ->select(['id', 'atasan_user_id', 'nama', 'NIK', 'position_id', 'homebase_id', 'is_active'])
            ->with(['position', 'homebase'])
            ->orderBy('nama')
            ->orderBy('id')
            ->get();

        return view('profile.index', compact('user', 'bawahan'));
    }
}

~~~~

## FILE: app/Http/Controllers/RoleController.php

SHA-256: `93be561fc3dc6514706fab9a54915fe5e82e642087f3f870eac6351e0eddfa6a`

~~~~php
<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        return $this->create();
    }

    // 1. Tampilkan Halaman Form Tambah Data
    public function create()
    {
        $roles = Role::all();
        return view('roles.create', compact('roles'));
    }

    // 2. Proses Simpan Data dari Form ke MySQL
    public function store(Request $request)
    {
        // Validasi input data dari pengguna
        $request->validate([
            'code' => 'required|unique:roles,code|max:50',
            'nama' => 'nullable|string|max:255',
        ]);

        // Simpan ke database menggunakan Eloquent Model
        Role::create([
            'code' => $request->code,
            'nama' => $request->nama,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        // Kembalikan ke halaman form dengan pesan sukses
        return redirect()->route('roles.create')->with('success', 'Master Role berhasil ditambahkan!');
    }

    public function edit($id){

        $role = Role::findOrFail($id);

        return view('roles.edit',compact('role'));

    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required',
        ]);
        
        $role = Role::findOrFail($id);

        $role->update([
            'nama' => $request->nama,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('roles.create')->with('success', 'Role berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        $role->delete();

        return redirect()->route('roles.create')->with('success', "Role berhasil dihapus secara permanen!");
    }
}

~~~~

## FILE: app/Http/Controllers/SettingPlafonPerjalananDinasController.php

SHA-256: `10adc871ac66a2aff237d5c19117c63e356d1bd0b81417664f4d21f567dd9375`

~~~~php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SettingPlafonPerjalananDinas;
use App\Models\Wilayah;
use App\Models\Level;

class SettingPlafonPerjalananDinasController extends Controller
{
    public function create()
    {
        $data = SettingPlafonPerjalananDinas::with(['wilayahAwal', 'wilayahTujuan', 'level'])->paginate(20);
        $levels = Level::all();     
        
        return view('setting_plafon_perjalanan_dinas.create', compact('data', 'levels'));
    }

    /**
     * API: Ambil Provinsi berdasarkan Teritory
     */
    public function getProvinsiByTeritory(Request $request)
    {
        $teritory = $request->get('teritory');
        
        $provinsis = Wilayah::where('teritory', $teritory)
                       ->whereNull('parent_id') // Hanya ambil level Provinsi
                       ->select('id', 'nama')
                       ->orderBy('nama', 'asc')
                       ->get();
        
        return response()->json($provinsis);
    }

    public function store(Request $request)
    {
        $request->validate([
            'teritory_awal' => 'required|in:Jawa,Luar Jawa',
            'provinsi_awal_id' => 'required|exists:wilayah,id',
            'teritory_tujuan' => 'required|in:Jawa,Luar Jawa',
            'provinsi_tujuan_id' => 'required|exists:wilayah,id',
            'level_id' => 'required|exists:level,id',
            'description' => 'required|string|max:255',
            'default_value' => 'nullable|integer',
            'tanggal_berlaku' => 'nullable|date',
        ]);

        // 🎯 VALIDASI KEAMANAN: Pastikan provinsi sesuai dengan territory yang dipilih
        $provAwal = Wilayah::find($request->provinsi_awal_id);
        if ($provAwal && $provAwal->teritory !== $request->teritory_awal) {
            return redirect()->back()->withErrors(['provinsi_awal_id' => '❌ Provinsi Awal tidak sesuai dengan Territory yang dipilih!'])->withInput();
        }

        $provTujuan = Wilayah::find($request->provinsi_tujuan_id);
        if ($provTujuan && $provTujuan->teritory !== $request->teritory_tujuan) {
            return redirect()->back()->withErrors(['provinsi_tujuan_id' => '❌ Provinsi Tujuan tidak sesuai dengan Territory yang dipilih!'])->withInput();
        }

        // 🎯 SIMPAN DATA SECARA EKPLISIT (Lebih aman daripada $request->all())
        SettingPlafonPerjalananDinas::create([
            'provinsi_awal_id' => $request->provinsi_awal_id,
            'provinsi_tujuan_id' => $request->provinsi_tujuan_id,
            'level_id' => $request->level_id,
            'description' => $request->description,
            'default_value' => $request->default_value,
            'tanggal_berlaku' => $request->tanggal_berlaku,
        ]);

        return redirect()->route('setting_plafon_perjalanan_dinas.create')
                         ->with('success', 'Data Plafon Perjalanan Dinas berhasil ditambahkan!');
    }

    public function edit($id) 
    {
        $item = SettingPlafonPerjalananDinas::with(['wilayahAwal', 'wilayahTujuan'])->findOrFail($id);
        $levels = Level::all();
        
        return view('setting_plafon_perjalanan_dinas.edit', compact('item', 'levels'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'teritory_awal' => 'required|in:Jawa,Luar Jawa',
            'provinsi_awal_id' => 'required|exists:wilayah,id',
            'teritory_tujuan' => 'required|in:Jawa,Luar Jawa',
            'provinsi_tujuan_id' => 'required|exists:wilayah,id',
            'level_id' => 'required|exists:level,id',
            'description' => 'required|string|max:255',
            'default_value' => 'nullable|integer',
            'tanggal_berlaku' => 'nullable|date',
        ]);

        $provAwal = Wilayah::find($request->provinsi_awal_id);
        if ($provAwal && $provAwal->teritory !== $request->teritory_awal) {
            return redirect()->back()->withErrors(['provinsi_awal_id' => '❌ Provinsi Awal tidak sesuai dengan Territory yang dipilih!'])->withInput();
        }

        $provTujuan = Wilayah::find($request->provinsi_tujuan_id);
        if ($provTujuan && $provTujuan->teritory !== $request->teritory_tujuan) {
            return redirect()->back()->withErrors(['provinsi_tujuan_id' => '❌ Provinsi Tujuan tidak sesuai dengan Territory yang dipilih!'])->withInput();
        }

        $item = SettingPlafonPerjalananDinas::findOrFail($id);
        $item->update([
            'provinsi_awal_id' => $request->provinsi_awal_id,
            'provinsi_tujuan_id' => $request->provinsi_tujuan_id,
            'level_id' => $request->level_id,
            'description' => $request->description,
            'default_value' => $request->default_value,
            'tanggal_berlaku' => $request->tanggal_berlaku,
        ]);

        return redirect()->route('setting_plafon_perjalanan_dinas.create')
                         ->with('success', 'Data Plafon Perjalanan Dinas berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        $item = SettingPlafonPerjalananDinas::findOrFail($id);
        $item->delete();

        return redirect()->route('setting_plafon_perjalanan_dinas.create')
                         ->with('success', 'Data Plafon Perjalanan Dinas berhasil dihapus!');
    }

    public function index()
    {
        return $this->create();
    }

    public function show(string $id) {}
}

~~~~

## FILE: app/Http/Controllers/TransaksiController.php

SHA-256: `bec9118194cffb8f00707728e67d4814d2cf9311ef834778c5e716338cdcf5a1`

~~~~php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiOperasionalHeader;
use App\Models\TransaksiOperasionalDetail;
use Illuminate\Support\Facades\DB; // WAJIB DIPANGGIL UNTUK TRANSAKSI

class TransaksiController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Dasar
        $request->validate([
            'tanggal' => 'required|date',
            'user_id' => 'required|integer',
            'description' => 'required|string',
            'details' => 'required|array', // Pastikan details berupa array
        ]);

        // 2. Mulai Mode Transaksi Database (All or Nothing)
        DB::beginTransaction();

        try {
            // 3. Buat Transaksi ID unik (Karena Anda pakai varchar, bukan Auto Increment)
            // Contoh format: OP-20260622-123456
            $transaksiId = 'OP-' . \App\Support\AppTime::now()->format('Ymd') . '-' . rand(1000, 9999);

            // 4. Simpan Data ke Tabel Header
            $header = TransaksiOperasionalHeader::create([
                'transaksi_id' => $transaksiId,
                'tanggal'      => $request->tanggal,
                'user_id'      => $request->user_id,
                'description'  => $request->description,
            ]);

            // 5. Simpan Data ke Tabel Detail secara Looping (Berulang)
            // Kita pecah array 'details' yang dikirim dari form tadi
            foreach ($request->details as $itemDetail) {
                
                // Pastikan nominal diisi agar baris kosong tidak ikut tersimpan
                if (!empty($itemDetail['nominal'])) {
                    TransaksiOperasionalDetail::create([
                        'transaksi_id'     => $transaksiId, // Ambil dari ID Header di atas
                        'cost_category_id' => $itemDetail['cost_category_id'],
                        'remark'           => $itemDetail['remark'],
                        'nominal'          => $itemDetail['nominal'],
                    ]);
                }
            }

            // 6. Jika semua proses di atas sukses tanpa error, KUNCI data ke database!
            DB::commit();

            return redirect()->back()->with('success', 'Transaksi berhasil disimpan!');

        } catch (\Exception $e) {
            // 7. Jika ada ERROR di tengah jalan (misal: internet putus, kolom database tidak cocok)
            // BATALKAN semua yang sudah terlanjur disimpan agar database tidak kotor
            DB::rollBack();
            
            // Tampilkan pesan errornya agar kita tahu letak salahnya
            return redirect()->back()->withErrors('Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
~~~~

## FILE: app/Http/Controllers/UserController.php

SHA-256: `d06dc38ce8220275674b6096e8f76965e176c8dd2a35294264ba32d41fe48931`

~~~~php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Models\Level;
use App\Models\Position;
use App\Models\Wilayah;

class UserController extends Controller
{
    public function create()
    {
        $data = User::with(['company', 'level', 'position', 'homebase'])
                    ->orderBy('id', 'desc')
                    ->take(50)
                    ->get();
        
        $companies = Company::all();
        $roles = Role::all();
        $levels = Level::all();
        $positions = Position::all();
        
        // HAPUS query $wilayahs di sini karena membuat beban memori penuh
        
        $atasans = User::where('is_active', 1)
                       ->orderBy('nama')
                       ->take(100)
                       ->get();
        
        // Hapus 'wilayahs' dari fungsi compact()
        return view('users.create', compact('data', 'companies', 'roles', 'levels', 'positions', 'atasans'));
    }
public function store(Request $request)
{
    // 1. Validasi
    $validatedData = $request->validate([
        'NIK' => 'nullable|string|max:20|unique:user,NIK',
        'nama' => 'required|string|max:255',
        'company_id' => 'required|exists:company,id',
        'roles_id' => 'required|exists:roles,id',
        'level_id' => 'required|exists:level,id',
        'position_id' => 'required|exists:position,id',
        'homebase_id' => 'required|exists:wilayah,id',
        'kost' => 'nullable|integer',
        'bbm' => 'nullable|integer',
        'uang_makan' => 'nullable|integer',
        'hari_kerja_user' => 'nullable|in:5 HK,6 HK',
        'atasan_user_id' => 'nullable|exists:user,id',
        'username' => 'required|string|max:20',
        'password' => 'required|string|min:6', // Hapus max:20, ganti min:6 agar user aman
    ]);

    // 2. Enkripsi Password sebelum disimpan
    $validatedData['password'] = bcrypt($validatedData['password']);

    // 3. Simpan data yang sudah divalidasi dan dienkripsi
    User::create($validatedData);

    return redirect()->route('users.create')->with('success', 'Data User berhasil ditambahkan!');
}

    public function edit(string $id)
    {
        $item = User::findOrFail($id);
        $companies = Company::all();
        
        // PERBAIKAN: Ubah Roles::all() menjadi Role::all()
        $roles = Role::all(); 
        
        $levels = Level::all();
        $positions = Position::all();
        
        // HAPUS query $wilayahs di sini
        
        $atasans = User::where('id', '!=', $id)->get(); 
        
        // Hapus 'wilayahs' dari fungsi compact()
        return view('users.edit', compact('item', 'companies', 'roles', 'levels', 'positions', 'atasans'));
    }

    public function update(Request $request, string $id)
    {
         // (Isi fungsi update biarkan sama persis seperti kode Anda sebelumnya)
         $request->validate([
            'NIK' => 'nullable|string|max:20|unique:user,NIK,' . $id . ',id',
            'nama' => 'required|string|max:255',
            'company_id' => 'required|exists:company,id',
            'roles_id' => 'required|exists:roles,id',
            'level_id' => 'required|exists:level,id',
            'position_id' => 'required|exists:position,id',
            'homebase_id' => 'required|exists:wilayah,id',
            'kost' => 'nullable|integer',
            'bbm' => 'nullable|integer',
            'uang_makan' => 'nullable|integer',
            'hari_kerja_user' => 'nullable|in:5 HK,6 HK',
            'atasan_user_id' => 'nullable|exists:user,id',
            'username' => 'required|string|max:20',
            'password' => 'nullable|string|max:20', 
        ]);

        $item = User::findOrFail($id);
        $data = $request->all();
        
        if (!empty($data['password'])) {
        $data['password'] = bcrypt($data['password']);
    } else {
        // Jika password kosong, jangan ubah password di database
        unset($data['password']);
    }
    
        $item->update($data);

        return redirect()->route('users.create')->with('success', 'Data User berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        $item = User::findOrFail($id);
        $item->delete();

        return redirect()->route('users.create')->with('success', 'Data User berhasil dihapus!');
    }

    public function index()
    {
        return $this->create();
    }

    public function show(string $id) {}
}

~~~~

## FILE: app/Http/Controllers/WeekdayGroupController.php

SHA-256: `1dde0723aed4302abc6c4746c39701067c65a93405a0afff6c091bdcd4e4c096`

~~~~php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WeekdayGroup;

class WeekdayGroupController extends Controller
{
    public function create()
    {
        $weekdayGroups = WeekdayGroup::all();
        return view('weekday_groups.create', compact('weekdayGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'nullable|unique:weekday_group,code|in:5 HK,6 HK',
            'description' => 'nullable|string|max:255',
        ]);

        WeekdayGroup::create([
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('weekday_groups.create')->with('success', 'Master Weekday Group berhasil ditambahkan!');
    }

    public function edit(string $id)
    {
        $weekdayGroup = WeekdayGroup::findOrFail($id);
        return view('weekday_groups.edit', compact('weekdayGroup'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'code' => 'nullable|in:5 HK,6 HK|unique:weekday_group,code,' . $id,
            'description' => 'nullable|string|max:255',
        ]);

        $weekdayGroup = WeekdayGroup::findOrFail($id);
        $weekdayGroup->update([
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('weekday_groups.create')->with('success', 'Master Weekday Group berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        $weekdayGroup = WeekdayGroup::findOrFail($id);
        $weekdayGroup->delete();

        return redirect()->route('weekday_groups.create')->with('success', 'Master Weekday Group berhasil dihapus!');
    }

    public function index()
    {
        return $this->create();
    }

    public function show(string $id) {}
}

~~~~

## FILE: app/Http/Controllers/WilayahController.php

SHA-256: `f132bb8efc539e915c4d3c70aab04a200ac24c535737f36644bd349057d7fd96`

~~~~php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wilayah;

class WilayahController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->create($request);
    }

    /**
     * Show the form for creating a new resource.
     */
   public function create(Request $request)
{
    // Ambil keyword dari URL (?search=xxx) dan bersihkan spasi di ujung
    $search = trim((string) $request->get('search', ''));

    $query = Wilayah::with('parent'); // Eager load parent untuk performa

    // 🎯 PENCARIAN HANYA BERDASARKAN NAMA WILAYAH
    // (TIDAK mencari di code maupun teritory)
    if ($search !== '' && $search !== null) {
        $query->where('nama', 'LIKE', "%{$search}%");
    }

    $wilayahs = $query->orderBy('code', 'asc')
                      ->paginate(20)
                      ->withQueryString(); // ✅ Pertahankan ?search= di pagination

    return view('wilayah.create', compact('wilayahs', 'search'));
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            //Validasi code boleh kosong (nullable) di SQL), tapi jika diisi harus unique
            'code' => 'nullable|unique:wilayah,code|max:50',
            'nama' => 'required',
            'teritory' => 'nullable|in:Jawa,Luar Jawa'
        ]);

        Wilayah::create([
            'code' => $request->code,
            'nama' => $request->nama,
            'parent_id' => $request->parent_id,
            'teritory' => $request->teritory,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('wilayah.create')->with('success', 'Master Wilayah berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // 1. Ambil data wilayah yang mau diedit berdasarkan ID
        $wilayah = Wilayah::findOrFail($id);

        // 2. Ambil semua data wilayah untuk pilihan di dropdown parent
        // Agar tidak membingungkan, kita saring agar wilayah ini tidak bisa memilih dirinya sendiri sebagai parent
        $allWilayah = Wilayah::where('id', '!=', $id)->get();

        return view('wilayah.edit', compact('wilayah', 'allWilayah'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // 1. Validasi inputan (ingat kolomnya 'nama', bukan 'description')
        // Aturan unique:wilayah,code,$id artinya abaikan pengecekan unik jika kodenya milik dia sendiri
        $request->validate([
            'code' => 'nullable|max:50|unique:wilayah,code,' . $id,
            'nama' => 'required',
            'teritory' => 'nullable|in:Jawa,Luar Jawa'
        ]);

        $wilayah = Wilayah::findOrFail($id);

        // 2. Logika Pengamanan: Cegah jika user mencoba mengakali sistem 
        // dengan memilih dirinya sendiri sebagai parent lewat inspect element
        if ($request->parent_id == $id) {
            return redirect()->back()->withErrors(['parent_id' => 'Wilayah tidak boleh menjadi induk dari dirinya sendiri!'])->withInput();
        }

        // 3. Eksekusi Update
        $wilayah->update([
            'code' => $request->code,
            'nama' => $request->nama,
            'parent_id' => $request->parent_id,
            'teritory' => $request->teritory,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('wilayah.create')->with('success', 'Master Wilayah berhasil diperbarui!');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $wilayah = Wilayah::findOrFail($id);

        // Logika Pengamanan Tambahan: Jika wilayah ini punya "anak" (menjadi parent bagi wilayah lain),
        // ubah parent_id anak-anaknya menjadi NULL terlebih dahulu agar database tidak error (Foreign Key Constraint)
        Wilayah::where('parent_id', $id)->update(['parent_id' => null]);

        // Eksekusi Hapus
        $wilayah->delete();

        return redirect()->route('wilayah.create')->with('success', 'Master Wilayah berhasil dihapus!');
    }

   // GANTI kode search() Anda menjadi ini:
public function search(Request $request)
    {
        $term = trim($request->get('q', ''));
        
        // Jika kosong, kembalikan array kosong
        if (empty($term)) {
            return response()->json([]);
        }

        $wilayahs = Wilayah::where('nama', 'LIKE', "%{$term}%")
                          ->select('id', 'nama')
                          ->orderByRaw('LENGTH(nama) ASC') // Prioritaskan nama yang lebih pendek/tepat
                          ->limit(50) // Batasi 50 hasil agar response cepat (cukup untuk kebutuhan user)
                          ->get()
                          ->map(function($w) {
                              // Format ini WAJIB sesuai standar Select2: 'id' dan 'text'
                              return [
                                  'id' => $w->id, 
                                  'text' => $w->nama
                              ];
                          });
                          
        return response()->json($wilayahs);
    }
}

~~~~

## FILE: app/Models/AuditLog.php

SHA-256: `22fd59f5e3cd04d01962a57ecead1997a85bc4e1ad300077b5936cf87772577d`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';
    
    protected $fillable = [
        'user_id',
        'action',
        'table_name',
        'record_id',
        'reason',
        'old_value',
    ];

    protected $casts = [
        'old_value' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

~~~~

## FILE: app/Models/BsActivityLog.php

SHA-256: `6fdcbac106735e7bcf2e62c0ae1258229a9f0492f935b7132aa151009da6344e`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BsActivityLog extends Model
{
    protected $table = 'bs_activity_logs';
    protected $guarded = ['id'];

    // 🎯 HELPER STATIK: Memudahkan pencatatan log dari controller manapun
    public static function record($pengajuan_bs_id, $code, $name, $description = null, $nominal = null)
    {
        self::create([
            'pengajuan_bs_id' => $pengajuan_bs_id,
            'activity_code'   => $code,
            'activity_name'   => $name,
            'actor_user_id'   => auth()->id(), // Otomatis mencatat siapa yang sedang login
            'description'     => $description,
            'nominal'         => $nominal,
        ]);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
~~~~

## FILE: app/Models/BsCashFlowSnapshot.php

SHA-256: `369e2254e589ec1299ac4e84a29362e020977017d71822396722fd65187f3720`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BsCashFlowSnapshot extends Model
{
    protected $table = 'bs_cash_flow_snapshots';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = [
        'saldo_awal' => 'decimal:2', 'total_masuk' => 'decimal:2',
        'total_keluar' => 'decimal:2', 'saldo_akhir' => 'decimal:2',
        'penyesuaian' => 'decimal:2', 'opening_final' => 'boolean',
        'last_updated' => 'datetime',
    ];
}

~~~~

## FILE: app/Models/BsUsageApprovalLog.php

SHA-256: `875f12d9900c5e3eeef33c250e3d23bda7e76a8a4f2d2195b645fdd94c9f6b76`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BsUsageApprovalLog extends Model
{
    protected $guarded = ['id'];
    public $timestamps = false;
    protected $casts = ['created_at' => 'datetime'];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'SUBMITTED' => 'Diajukan kepada pemohon',
            'APPROVED' => 'Disetujui dan dikonfirmasi',
            'REJECTED' => 'Ditolak pemohon',
            'WITHDRAWN' => 'Ditarik kembali oleh anggota',
            'REVISED' => 'Dibuka untuk perbaikan',
            default => $this->action,
        };
    }
}

~~~~

## FILE: app/Models/CashFlowSnapshot.php

SHA-256: `1a22c06beb8097fb695066e1303ed21ee84e214571fda63f36bcc14b1273b993`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashFlowSnapshot extends Model
{
    protected $table = 'cash_flow_snapshots';
    public $timestamps = false;
    
    protected $fillable = [
        'user_id',
        'periode',
        'saldo_awal',
        'total_masuk',
        'total_keluar',
        'saldo_akhir',
        'last_updated',
    ];

    protected $casts = [
        'saldo_awal' => 'decimal:2',
        'total_masuk' => 'decimal:2',
        'total_keluar' => 'decimal:2',
        'saldo_akhir' => 'decimal:2',
        'last_updated' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
~~~~

## FILE: app/Models/Company.php

SHA-256: `866906fed9f76f1db6d5e552a741ba5836f45282629c4b1a9f9641d1e3566aa2`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    //

    use HasFactory;

    //1. Memberitahu Laravel nama tabelnya adalah 'company'

    protected $table = 'company';

    //2. Mematikan fitur pengisian waktu otomatis (timestamps)
    public $timestamps = false;

    //3. Daftarkan kolom yang diizinkan untuk diisi data (Mass Assignment)

    protected $fillable = [
        'code',
        'nama',
        'is_active'
    ];


    
    // ==========================================
    // 🎯 TAMBAHKAN METHOD INI (Untuk L2 Test Case)
    // ==========================================
    public function isActive(): bool
    {
        $value = $this->is_active;

        // Mendukung format 'Y'/'N', '1'/'0', 1/0, true/false
        if (is_string($value)) {
            return in_array(strtoupper(trim($value)), ['Y', 'YES', '1', 'TRUE', 'AKTIF'], true);
        }

        return (bool) $value;
    }
    }

~~~~

## FILE: app/Models/EventHourGroup.php

SHA-256: `eb7d739c99838058ae38941c48e17888e763a080bb5202276471a9ed541a5429`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventHourGroup extends Model
{
    protected $table = 'event_hour_group';
    public $timestamps = false;
    protected $fillable = ['code', 'description', 'is_active'];
}
~~~~

## FILE: app/Models/Level.php

SHA-256: `d5cbb7c09f966589e084146f424dbc37b451f0150f8d31615af632ba3b061cec`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    //

    protected $table = 'level';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'nama',
        'is_active'

    ];
}

~~~~

## FILE: app/Models/PerongkosanEventDalamKota.php

SHA-256: `429d7318908ed618bee9b7bca04b39cf08f74530130b966b20bcb40b8585dbb2`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerongkosanEventDalamKota extends Model
{
    use HasFactory;

    protected $table = 'perongkosan_event_dalam_kota';
    public $timestamps = false; 

    protected $fillable = [
        'transaksi_id',
        'tanggal',
        'is_libur',
        'jam_kerja_event_id',
        'user_id',
        'description',
        'nominal',
        'periode',
        'is_over_plafon', 
        'plafon_value', 
        'selisih_plafon',
        'foto_event_1',
        'foto_event_2',
        'foto_event_3'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function jamKerja()
    {
        return $this->belongsTo(EventHourGroup::class, 'jam_kerja_event_id', 'id');
    }

    // public function index() {
    // $events = PerongkosanEventDalamKota::with(['user', 'jamKerja'])->orderBy('tanggal', 'desc')->paginate(10);
    // return view('perongkosan_event_dalam_kota.index', compact('events'));

}
~~~~

## FILE: app/Models/PerongkosanEventLuarKota.php

SHA-256: `56565d418e1fbe6f83bcec61320384415ceb7d1b4779b76c73026272eb1873ae`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerongkosanEventLuarKota extends Model
{
    use HasFactory;

    protected $table = 'perongkosan_event_luar_kota';
    public $timestamps = false; 

    protected $fillable = [
    'transaksi_id',
    'tanggal',
    'lokasi_event_id', 
    'jarak_km',       // ✅ Tetap ada (untuk backward compatibility)
    'durasi_jam',     // ✅ Tetap ada
    'is_libur',
    'jam_kerja_event_id',
    'user_id',
    'description',
    'nominal',
    'periode',        // ✅ Tetap ada
    'is_over_plafon', 
    'plafon_value', 
    'selisih_plafon',
    'foto_event_1',
    'foto_event_2',
    'foto_event_3'
];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'lokasi_event_id', 'id');
    }

    public function jamKerja()
    {
        return $this->belongsTo(EventHourGroup::class, 'jam_kerja_event_id', 'id');
    }
}
~~~~

## FILE: app/Models/PlafonEventDalamKota.php

SHA-256: `eea85a03759da1195e490e7961fd9e1f8f0e40cd16a93d148d2e9cf648cdb739`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlafonEventDalamKota extends Model
{
    protected $table = 'setting_plafon_event_marketing_dalam_kota';
    public $timestamps = false;
    
    protected $fillable = [
        'tanggal_berlaku', 
        'level_id', 
        'kelompok_hari_kerja_id', 
        'hari_event', 
        'jam_kerja_event_id', 
        'description', 
        'default_value'
    ];

    // Relasi ke tabel Level
    public function level() { 
        return $this->belongsTo(Level::class, 'level_id'); 
    }

    // Relasi ke tabel Weekday Group
    public function weekdayGroup() { 
        return $this->belongsTo(WeekdayGroup::class, 'kelompok_hari_kerja_id'); 
    }

    // Relasi ke tabel Event Hour Group
    public function eventHourGroup() { 
        return $this->belongsTo(EventHourGroup::class, 'jam_kerja_event_id'); 
    }
}
~~~~

## FILE: app/Models/PlafonEventLuarKota.php

SHA-256: `2ae6419dd9c67b09716564cb4e68ab3781683995bafced3beda424679f9b412b`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlafonEventLuarKota extends Model
{
    protected $table = 'setting_plafon_event_marketing_luar_kota';
    public $timestamps = false;
    
    protected $fillable = [
        'tanggal_berlaku', 
        'level_id', 
        'kelompok_hari_kerja_id', 
        'hari_event', 
        'jam_kerja_event_id', 
        'description', 
        'default_value'
    ];

    // Relasi ke tabel Level
    public function level() { 
        return $this->belongsTo(Level::class, 'level_id'); 
    }

    // Relasi ke tabel Weekday Group
    public function weekdayGroup() { 
        return $this->belongsTo(WeekdayGroup::class, 'kelompok_hari_kerja_id'); 
    }

    // Relasi ke tabel Event Hour Group
    public function eventHourGroup() { 
        return $this->belongsTo(EventHourGroup::class, 'jam_kerja_event_id'); 
    }
}
~~~~

## FILE: app/Models/Position.php

SHA-256: `33389a3242fb1a4c1d93dc0fc6fd58ae9b5381b6ec7ecd755dfbd107771eed35`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    //

    protected $table = 'position';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'nama',
        'is_active'
    ];


}

~~~~

## FILE: app/Models/ReimbursementClaim.php

SHA-256: `5a87764a2b4a05ee55ba31b8732eebbf4b1350b7db71d07ac85e6eba97bafdb9`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReimbursementClaim extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2', 'items' => 'array', 'expense_date' => 'date',
        'submitted_at' => 'datetime', 'approved_at' => 'datetime',
        'rejected_at' => 'datetime', 'paid_at' => 'datetime', 'payment_date' => 'date',
        'payment_proofs' => 'array',
    ];

    public const LABELS = [
        'PENDING_FINANCE' => 'Menunggu Finance',
        'APPROVED' => 'Disetujui, belum dibayar',
        'REJECTED' => 'Ditolak Finance',
        'PAID' => 'Sudah dibayar',
    ];

    public function user() { return $this->belongsTo(User::class, 'user_id'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
    public function payer() { return $this->belongsTo(User::class, 'paid_by'); }
    public function logs() { return $this->hasMany(ReimbursementLog::class)->orderBy('id'); }

}

~~~~

## FILE: app/Models/ReimbursementLog.php

SHA-256: `d7c0bcc18439cd3767a129f428a8b4d1ecfae6dce9a0ec079f1d28d5d6e3e424`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReimbursementLog extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = ['created_at' => 'datetime', 'amount' => 'decimal:2'];

    public function actor() { return $this->belongsTo(User::class, 'actor_id'); }
}

~~~~

## FILE: app/Models/SettingPlafonPerjalananDinas.php

SHA-256: `cbd6dbae19b2f2cac1650608deeac4dd2274f80125bd0f9905bd782e68b888fe`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingPlafonPerjalananDinas extends Model
{
   protected $table = 'setting_plafon_perjalanan_dinas';
public $timestamps = false;
    
    // Kolom yang boleh diisi massal (memudahkan kita di Controller)
    protected $fillable = [
        'provinsi_awal_id', 
        'provinsi_tujuan_id', 
        'level_id', 
        'description', 
        'default_value', 
        'tanggal_berlaku'
    ];

    // Relasi ke tabel Wilayah (sebagai provinsi awal)
    public function wilayahAwal()
    {
        return $this->belongsTo(Wilayah::class, 'provinsi_awal_id');
    }

    // Relasi ke tabel Wilayah (sebagai provinsi tujuan)
    public function wilayahTujuan()
    {
        return $this->belongsTo(Wilayah::class, 'provinsi_tujuan_id');
    }

    // Relasi ke tabel Level
    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id');
    }
}
~~~~

## FILE: app/Models/StockCashFlowSnapshot.php

SHA-256: `eb8381a7934cd374656dd396ca96ec0bf8f1ccbddb47d5edf56c568a0519a02e`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockCashFlowSnapshot extends Model
{
    protected $table = 'stock_cash_flow_snapshots';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = [
        'saldo_awal' => 'decimal:2', 'total_masuk' => 'decimal:2',
        'total_keluar_bs' => 'decimal:2', 'total_event_dalam' => 'decimal:2',
        'total_event_luar' => 'decimal:2', 'total_keluar' => 'decimal:2',
        'penyesuaian' => 'decimal:2', 'saldo_akhir' => 'decimal:2',
        'total_keluar_pribadi' => 'decimal:2', 'total_masuk_reimbursement' => 'decimal:2',
        'includes_personal_funds' => 'boolean',
        'opening_final' => 'boolean', 'last_updated' => 'datetime',
    ];
}

~~~~

## FILE: app/Models/WeekdayGroup.php

SHA-256: `883f800fb3c465f05caa6983e02b2292d615ef3c3445b0d005c9ae13d253b485`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeekdayGroup extends Model
{
    protected $table = 'weekday_group';
    public $timestamps = false;
    protected $fillable = ['code', 'description', 'is_active'];
}
~~~~

## FILE: app/Models/Wilayah.php

SHA-256: `3f513622512cd291fce7ebd7b9cb0331f25c3c363743d0b95b0d2c3073013ceb`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    //

    // 1. Nama Tabel
    protected $table = 'wilayah';

    // 2. Matikan Timestamps karena tidak ada
    public $timestamps = false;

    //3. Kolom yang boleh diisi (Mass Assignment)

    protected $fillable = [
        'code',
        'nama',
        'parent_id',
        'teritory',
        'is_active'
    ];

    //4. Logika Baru: Mengambil data parent (Orang Tua)
    // Ini membantu kita menampilkan nama parent di table nanti

    public function parent()
{
    return $this->belongsTo(Wilayah::class, 'parent_id', 'id');
}
}

~~~~

## FILE: app/Models/WilayahDistance.php

SHA-256: `ccc1cf42cb2df63efdd68dc88f3a3ec8db84c5facdf68729df107a9674f8a1b8`

~~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WilayahDistance extends Model
{
    use HasFactory;

    protected $table = 'wilayah_distances';

    protected $fillable = [
        'from_wilayah_id',
        'to_wilayah_id',
        'distance_km',
    ];

    public function fromWilayah()
    {
        return $this->belongsTo(Wilayah::class, 'from_wilayah_id');
    }

    public function toWilayah()
    {
        return $this->belongsTo(Wilayah::class, 'to_wilayah_id');
    }
}
~~~~

## FILE: app/Notifications/BsApproved.php

SHA-256: `3f5043095f0b6e5a4d04116bb17e2b57afa238f86ce1ff1eaca9510617bc02e2`

~~~~php
<?php

namespace App\Notifications;

use App\Models\PengajuanBs;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BsApproved extends Notification
{
    use Queueable;

    public function __construct(public PengajuanBs $bs) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => '💼 BS Menunggu Pencairan',
            'message' => 'BS ' . $this->bs->document_no . ' milik ' . $this->bs->user->nama .
                         ' telah disetujui atasan. Segera proses pencairan.',
            'url'     => route('approval-finance.index'),
        ];
    }
}
~~~~

## FILE: app/Notifications/BsCreated.php

SHA-256: `3c5e4987906505849c0aaaea11051b1c334250ee4a719b366814137551d99a71`

~~~~php
<?php

namespace App\Notifications;

use App\Models\PengajuanBs;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BsCreated extends Notification
{
    use Queueable;

    public function __construct(public PengajuanBs $bs) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => '🆕 Pengajuan BS Baru',
            'message' => $this->bs->user->nama . ' mengajukan BS ' . $this->bs->document_no .
                         ' sebesar Rp ' . number_format($this->bs->nominal_pengajuan, 0, ',', '.') .
                         '. Menunggu approval Anda.',
            'url'     => route('approval-atasan.index'),
        ];
    }
}
~~~~

## FILE: app/Notifications/BsDisbursed.php

SHA-256: `013feac16c69367820035700e6541f2955f7ff0cd9a20325603fa244fd00a120`

~~~~php
<?php

namespace App\Notifications;

use App\Models\PengajuanBs;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BsDisbursed extends Notification
{
    use Queueable;

    public function __construct(public PengajuanBs $bs, public $nominal) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => '💰 BS Telah Dicairkan',
            'message' => 'BS ' . $this->bs->document_no . ' cair sebesar Rp ' .
                         number_format($this->nominal, 0, ',', '.') .
                         '. Saldo BS Anda kini aktif untuk realisasi.',
            'url'     => route('pengajuan-bs.index'),
        ];
    }
}
~~~~

## FILE: app/Notifications/BsRejected.php

SHA-256: `b1788383230dde338e78bd93b5755520dae60ce488c581ada61ab49595e93af3`

~~~~php
<?php

namespace App\Notifications;

use App\Models\PengajuanBs;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BsRejected extends Notification
{
    use Queueable;

    public function __construct(public PengajuanBs $bs) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => '❌ Pengajuan BS Ditolak',
            'message' => 'Pengajuan BS ' . $this->bs->document_no . ' Anda ditolak oleh atasan.',
            'url'     => route('pengajuan-bs.index'),
        ];
    }
}
~~~~

## FILE: app/Notifications/BsSettled.php

SHA-256: `508357d7fda3d6532c2dc55bd50a16b8f38d2a8206ccc9210482c2adbef266fb`

~~~~php
<?php

namespace App\Notifications;

use App\Models\PengajuanBs;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BsSettled extends Notification
{
    use Queueable;

    public function __construct(public PengajuanBs $bs) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        $ket = $this->bs->nominal_pengembalian > 0
            ? 'Pengembalian Rp ' . number_format($this->bs->nominal_pengembalian, 0, ',', '.') . ' telah dicatat.'
            : ($this->bs->nominal_reimburse > 0
                ? 'Reimburse Rp ' . number_format($this->bs->nominal_reimburse, 0, ',', '.') . ' telah dicatat.'
                : 'Lunas tanpa selisih.');

        return [
            'title'   => '📁 BS Ditutup (Settled)',
            'message' => 'BS ' . $this->bs->document_no . ' telah ditutup oleh Finance. ' . $ket,
            'url'     => route('pengajuan-bs.show', $this->bs->id),
        ];
    }
}
~~~~

## FILE: app/Notifications/BsUsageApprovalUpdated.php

SHA-256: `6e36c23b99bc2764d2c822348c924e2844d7f74593cbc15053b3163eebc31fe7`

~~~~php
<?php

namespace App\Notifications;

use App\Models\TransaksiPerjalananDinasHeader;
use Illuminate\Notifications\Notification;

class BsUsageApprovalUpdated extends Notification
{
    public function __construct(public TransaksiPerjalananDinasHeader $document, public string $action) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        $ownerMessage = in_array($this->action, ['SUBMITTED', 'WITHDRAWN'], true);
        $title = match ($this->action) {
            'SUBMITTED' => 'Permintaan Pemakaian BS',
            'APPROVED' => 'Pemakaian BS Disetujui',
            'REJECTED' => 'Pemakaian BS Ditolak',
            'WITHDRAWN' => 'Permintaan Pemakaian BS Ditarik',
        };
        return [
            'title' => $title,
            'message' => e($this->document->transaksi_id).' - '.e($this->document->user?->nama ?? '-').'. '.$title.'.',
            'url' => $ownerMessage ? route('bs-usage-approvals.show', $this->document->id)
                : route('transaksi_perjalanan_dinas.show', $this->document->transaksi_id),
        ];
    }
}

~~~~

## FILE: app/Notifications/TransaksiOverPlafon.php

SHA-256: `ad0ee3f02948415adbebd2aaecbaecc15017fcc0677df285da25e21ac839d33d`

~~~~php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TransaksiOverPlafon extends Notification
{
    use Queueable;

    public function __construct(
        public string $judulTransaksi,
        public float $nominal,
        public float $plafon,
        public float $selisih,
        public string $url
    ) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => '⚠️ Transaksi Melebihi Plafon',
            'message' => $this->judulTransaksi . ' sebesar Rp ' . number_format($this->nominal, 0, ',', '.')
                . ' melebihi plafon Rp ' . number_format($this->plafon, 0, ',', '.')
                . ' (selisih Rp ' . number_format($this->selisih, 0, ',', '.') . '). Mohon ditinjau.',
            'url'     => $this->url,
        ];
    }
}
~~~~

## FILE: app/Providers/EventServiceProvider.php

SHA-256: `5341515b03afdfd58688ca2f77cad05bc8df2f5a97ea5517f5117163d3487e45`

~~~~php
protected $listen = [
    \App\Events\CashFlowChanged::class => [
        \App\Listeners\UpdateCashFlowSnapshot::class,
    ],
    // ... listener lain yang sudah ada biarkan saja
];
~~~~

## FILE: app/Traits/FiltersByPeriod.php

SHA-256: `44517072e947baffcfa0c807902e01014c9b11ffd2d2d1a55c2fe2fdd3b5c58e`

~~~~php
<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FiltersByPeriod
{
    /**
     * Filter query berdasarkan periode (YYYY-MM) dari kolom tanggal.
     * Jika tabel Anda sudah punya kolom `periode` (sesuai dok rev4),
     * ganti isi whereRaw dengan: $query->where('periode', $periode);
     */
    protected function filterPeriod(Builder $query, ?string $periode, string $column = 'tanggal'): Builder
    {
        if ($periode && preg_match('/^\d{4}-\d{2}$/', $periode)) {
            $query->whereRaw("DATE_FORMAT({$column}, '%Y-%m') = ?", [$periode]);
        }

        return $query;
    }
}
~~~~

## FILE: artisan

SHA-256: `f91a517c0c5d3a904c3f390933964a5e08ec1fc68146882730daac4aca4ffcec`

~~~~text
#!/usr/bin/env php
<?php

use Illuminate\Foundation\Application;
use Symfony\Component\Console\Input\ArgvInput;

define('LARAVEL_START', microtime(true));

// Register the Composer autoloader...
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the command...
/** @var Application $app */
$app = require_once __DIR__.'/bootstrap/app.php';

$status = $app->handleCommand(new ArgvInput);

exit($status);

~~~~
