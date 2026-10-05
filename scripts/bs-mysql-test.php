<?php
[$app, $directory, $state] = require __DIR__.'/bs-verification-bootstrap.php';

use App\Models\User;
use App\Models\PengajuanBs;
use App\Models\Periode;
use App\Models\TransaksiOperasionalHeader;
use App\Models\TransaksiPerjalananDinasHeader;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

function check(bool $value, string $message): void { if (!$value) throw new RuntimeException($message); }
function makeUser(string $tag, ?int $manager = null): User {
    $template = User::where('is_active', 1)->whereHas('company', fn ($q) => $q->where('is_active', 1))->firstOrFail();
    $user = $template->replicate();
    $user->username = 'bs_'.substr($tag, 0, 8).'_'.bin2hex(random_bytes(3));
    $user->NIK = $user->username;
    $user->nama = 'Uji BS '.$tag;
    $user->roles_id = DB::table('roles')->whereNotIn('code', ['ADMIN', 'DIR', 'FINANCE'])->value('id') ?? $template->roles_id;
    $user->atasan_user_id = $manager;
    $user->password = \Illuminate\Support\Facades\Hash::make(bin2hex(random_bytes(12)));
    $user->save();
    foreach (['akses.pengajuan-bs','akses.transaksi-operasional','akses.transaksi-perjalanan-dinas','akses.cash-flow','akses.approval-finance','akses.master-data'] as $permission) {
        \Spatie\Permission\Models\Permission::findOrCreate($permission, 'web');
        $user->givePermissionTo($permission);
    }
    return $user;
}
function makeBs(User $owner, string $status = 'DISBURSED', string $type = 'operasional_sales', string $period = '2026-09'): PengajuanBs {
    $bs = PengajuanBs::create(['document_no' => 'VERIFY-'.\Illuminate\Support\Str::ulid(), 'tanggal' => $period.'-01', 'periode' => $period, 'user_id' => $owner->id,
        'atasan_user_id' => $owner->atasan_user_id, 'status' => $status, 'tipe_pengajuan' => $type, 'nominal_pengajuan' => 1000000, 'nominal_approved' => $status === 'DISBURSED' ? 1000000 : 0]);
    if ($status === 'DISBURSED') DB::table('approval_bs_finance')->insert(['document_no' => 'VERIFY-CAIR-'.$bs->id, 'pengajuan_bs_id' => $bs->id, 'tanggal_cair' => $period.'-01', 'periode' => $period,
        'finance_user_id' => $owner->atasan_user_id, 'nominal_pengajuan_bs' => 1000000, 'nominal_sisa_bs' => 0, 'nominal_approved' => 1000000]);
    return $bs;
}
function expense(PengajuanBs $bs, int $userId, int $amount = 700000, bool $travel = false) {
    $class = $travel ? TransaksiPerjalananDinasHeader::class : TransaksiOperasionalHeader::class;
    $attributes = ['transaksi_id' => 'V'.strtoupper(bin2hex(random_bytes(5))), 'tanggal' => $bs->periode.'-16', 'periode' => $bs->periode, 'user_id' => $userId, 'bs_id' => $bs->id, 'status' => 'DRAFT', 'description' => 'Pengujian konkurensi BS'];
    if ($travel) $attributes += ['tujuan_dinas_id' => DB::table('wilayah')->value('id'), 'jarak_km' => 300, 'durasi_jam' => 10];
    $header = $class::create($attributes);
    $header->details()->create(['transaksi_id' => $header->transaksi_id, 'cost_category_id' => DB::table('cost_category')->value('id'), 'remark' => 'Biaya pengujian', 'nominal' => $amount]);
    return $header;
}
function race(string $name, array $jobs): array {
    global $directory;
    $run = $directory.'/race-'.$name.'-'.bin2hex(random_bytes(4));
    mkdir($run, 0700);
    $processes = [];
    foreach ($jobs as $i => $job) {
        $job += ['ready' => $run.'/ready-'.$i, 'locked' => $run.'/locked-'.$i, 'go' => $run.'/go', 'hold_ms' => 700];
        file_put_contents($run.'/job-'.$i.'.json', json_encode($job));
        $process = new Process([PHP_BINARY, __DIR__.'/bs-concurrency-worker.php', $run.'/job-'.$i.'.json'], base_path());
        $process->setTimeout(45)->start();
        $processes[] = $process;
    }
    $deadline = microtime(true) + 20;
    while (count(glob($run.'/ready-*')) !== count($jobs)) {
        foreach ($processes as $p) if (!$p->isRunning()) throw new RuntimeException('Worker exited before barrier: '.$p->getErrorOutput().$p->getOutput());
        if (microtime(true) > $deadline) throw new RuntimeException('Workers not ready');
        usleep(10000);
    }
    file_put_contents($run.'/go', 'go');
    $results = [];
    foreach ($processes as $p) {
        $p->wait();
        check($p->isSuccessful(), 'Worker failed: '.$p->getErrorOutput().$p->getOutput());
        $result = json_decode(trim($p->getOutput()), true, flags: JSON_THROW_ON_ERROR);
        check($result['transaction_level'] === 0, 'Leaked transaction');
        check($result['status'] < 500, 'Server error: '.json_encode($result));
        $results[] = $result;
    }
    check(max(array_column($results, 'seconds')) >= 0.6, 'Lock hold not observed');
    echo $name.': '.json_encode($results).PHP_EOL;
    return $results;
}

\Carbon\Carbon::setTestNow('2026-09-16 10:00:00');
Periode::updateOrCreate(['periode' => '2026-09'], ['status' => 'OPEN']);
$manager = makeUser('manager');
$results = [];
foreach ([false, true] as $travel) {
    $owner = makeUser($travel ? 'travel' : 'ops', $manager->id);
    $member = $travel ? makeUser('member', $manager->id) : $owner;
    $bs = makeBs($owner, type: $travel ? 'perjalanan_dinas' : 'operasional_sales');
    if ($travel) $bs->members()->attach($member->id);
    $one = expense($bs, $owner->id, travel: $travel);
    $two = expense($bs, $member->id, travel: $travel);
    $url = fn ($h) => $travel ? '/transaksi-perjalanan-dinas/'.$h->transaksi_id.'/validate' : '/transaksi-operasional/'.$h->id.'/validate';
    $responses = race($travel ? 'travel_member_balance' : 'operational_balance', [['url' => $url($one), 'user_id' => $owner->id], ['url' => $url($two), 'user_id' => $member->id]]);
    check(count(array_filter([$one->fresh()->status, $two->fresh()->status], fn ($s) => $s === 'VALIDATED')) === 1, 'Two expenses consumed the same funds');
    check(app(\App\Services\BsCashFlowService::class)->calculate($owner->id, '202609')['saldo_akhir'] === null, 'Open period has a closing balance');
    check(app(\App\Services\BonSementaraService::class)->getRealtimeSisaSaldo($owner->id, '202609') === '300000.00', 'Wrong available balance');
    $results[] = ['case' => $travel ? 'travel_member_balance' : 'operational_balance', 'passed' => true, 'responses' => $responses];
}
$owner = makeUser('finance', $manager->id);
$bs = makeBs($owner, 'APPROVED_ATASAN');
$job = ['user_id' => $manager->id, 'url' => '/approval-finance/'.$bs->id.'/disburse', 'data' => ['nominal_approved' => 1000000]];
$responses = race('duplicate_disbursement', [$job, $job]);
check(DB::table('approval_bs_finance')->where('pengajuan_bs_id', $bs->id)->count() === 1, 'Duplicate disbursement');
check(DB::table('bs_activity_logs')->where('pengajuan_bs_id', $bs->id)->where('activity_code', 'DISBURSED')->count() === 1, 'Duplicate disbursement audit');
$results[] = ['case' => 'duplicate_disbursement', 'passed' => true, 'responses' => $responses];
$bs = makeBs($owner, 'PENDING_ATASAN');
$responses = race('approve_vs_reject', [['user_id' => $manager->id, 'url' => '/approval-atasan/'.$bs->id.'/approve'], ['user_id' => $manager->id, 'url' => '/approval-atasan/'.$bs->id.'/reject', 'data' => ['rejection_reason' => 'Pengujian bersamaan']]]);
check(in_array($bs->fresh()->status, ['APPROVED_ATASAN', 'REJECTED_ATASAN']), 'Invalid approval status');
check(DB::table('bs_activity_logs')->where('pengajuan_bs_id', $bs->id)->count() === 1, 'Conflicting approval audit');
$results[] = ['case' => 'approve_vs_reject', 'passed' => true, 'responses' => $responses];

// Isolated fixture only: advance the period chain for a close/disbursement race.
Periode::where('periode', '<', '2026-10')->update(['status' => 'CLOSED']);
$period = Periode::updateOrCreate(['periode' => '2026-10'], ['status' => 'OPEN']);
$bs = makeBs($owner, 'APPROVED_ATASAN', period: '2026-10');
$responses = race('close_vs_disbursement', [['user_id' => $manager->id, 'url' => '/periode/'.$period->id.'/close', 'now' => '2026-11-16 10:00:00'],
    ['user_id' => $manager->id, 'url' => '/approval-finance/'.$bs->id.'/disburse', 'now' => '2026-10-16 10:00:00', 'data' => ['nominal_approved' => 1000000]]]);
$paid = DB::table('approval_bs_finance')->where('pengajuan_bs_id', $bs->id)->count();
check($period->fresh()->status === 'CLOSED', 'Close did not finish');
$snapshot = \App\Models\BsCashFlowSnapshot::where('user_id', $owner->id)->where('periode', '2026-10')->firstOrFail();
check($snapshot->total_masuk === ($paid ? '1000000.00' : '0.00'), 'Closed snapshot does not match committed disbursement');
$results[] = ['case' => 'close_vs_disbursement', 'passed' => true, 'responses' => $responses];
file_put_contents($directory.'/mysql-concurrency.json', json_encode(['database' => DB::connection()->getDatabaseName(), 'server' => DB::selectOne('select version() as v')->v, 'results' => $results], JSON_PRETTY_PRINT));
echo 'PASS: '.count($results).' concurrent scenarios on isolated MySQL/MariaDB.'.PHP_EOL;
