<?php

// Development preview using the existing isolated integration fixture; no business database connection.
foreach (['APP_ENV' => 'testing', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'DB_URL' => '',
    'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'MAIL_MAILER' => 'array', 'QUEUE_CONNECTION' => 'sync',
    'BROADCAST_CONNECTION' => 'null'] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $_SERVER[$key] = $value;
}
require __DIR__.'/../vendor/autoload.php';

$test = new \Tests\Feature\BsCashFlowTest('test_recap_four_modules_reconcile_submission_approval_rejection_pending_and_difference');
$setup = new ReflectionMethod($test, 'setUp');
$setup->invoke($test);
try {
    if (\Illuminate\Support\Facades\DB::connection()->getDatabaseName() !== ':memory:') throw new RuntimeException('In-memory database required.');
    $test->test_recap_four_modules_reconcile_submission_approval_rejection_pending_and_difference();
    $directory = storage_path('app/private/transaction-ux-preview');
    if (!is_dir($directory)) mkdir($directory, 0700, true);
    $opr = \App\Models\TransaksiOperasionalHeader::firstOrFail();
    $pages = [
        'dashboard' => route('dashboard.transaksi', ['pemakaian_periode' => '']),
        'pengajuan' => route('transaction-recap.module', ['jenis' => 'OPR', 'pemakaian_periode' => '']),
        'finance' => route('transaction-recap.module', ['jenis' => 'PD', 'pemakaian_periode' => '']),
        'empty' => route('transaction-recap.module', ['jenis' => 'OPR', 'pemakaian_periode' => '2099-01']),
        'event-dalam' => route('transaction-recap.module', ['jenis' => 'EDK', 'pemakaian_periode' => '']),
        'event-luar' => route('transaction-recap.module', ['jenis' => 'ELK', 'pemakaian_periode' => '']),
        'dashboard-empty' => route('dashboard.transaksi', ['pemakaian_periode' => '2099-01']),
        'lookup' => route('bs-usage-checks.index'),
        'finance-report' => route('bs-usage-checks.index', ['tampilan' => 'rincian']),
        'finance-history' => route('bs-usage-checks.index', ['tampilan' => 'rincian', 'mode' => 'semua', 'jenis_source' => 'OPR', 'source_id' => $opr->id]),
        'finance-filter' => route('bs-usage-checks.index', ['tanggal_awal' => '2000-01-01', 'tanggal_akhir' => '2099-12-31']),
        'finance-empty' => route('bs-usage-checks.index', ['tampilan' => 'rincian', 'referensi' => 'TIDAK-ADA']),
        'check-form' => route('bs-usage-checks.create', ['OPR', $opr->id]),
        'detail' => route('transaction-recap.show', ['jenis' => 'OPR', 'source' => $opr->id, 'dari' => 'laporan',
            'pemakaian_periode' => '', 'pemakaian_jenis' => 'OPR', 'tampilan' => 'daftar', 'pemakaian_page' => 2]),
    ];
    foreach ($pages as $name => $url) {
        $response = $test->get($url)->assertOk();
        file_put_contents($directory.'/'.$name.'.html', $response->getContent());
    }
    $form = $test->get(route('bs-usage-checks.create', ['OPR', $opr->id]))->assertOk();
    $test->post(route('bs-usage-checks.store', ['OPR', $opr->id]), [
        'save_action' => 'draft', 'tanggal_pengecekan' => '2026-09-30',
        'source_token' => $form->viewData('sourceToken'), 'review_token' => $form->viewData('reviewToken'),
        'keputusan' => 'APPROVED', 'catatan_keputusan' => 'Catatan draft untuk pemeriksaan selanjutnya',
        'details' => $form->viewData('items')->map(fn ($item) => [
            'cost_category_id' => $item['cost_category_id'], 'nominal_disetujui' => '60.00',
            'catatan' => 'Catatan internal draft',
        ])->all(),
    ])->assertSessionHasNoErrors();
    foreach ([
        'draft-form' => route('bs-usage-checks.create', ['OPR', $opr->id]),
        'finance-drafts' => route('bs-usage-checks.index', ['tampilan' => 'draft']),
        'finance-with-draft' => route('bs-usage-checks.index'),
    ] as $name => $url) {
        $pages[$name] = $url;
        file_put_contents($directory.'/'.$name.'.html', $test->get($url)->assertOk()->getContent());
    }
    // Module navigation fixtures include real status filters and empty periods.
    foreach (['OPR', 'PD', 'EDK', 'ELK'] as $type) {
        foreach (['' => '', 'pending' => 'PENDING', 'approved' => 'APPROVED', 'difference' => 'DIFFERENCE', 'revision' => 'REVISION', 'recheck' => 'RECHECK', 'rejected' => 'REJECTED', 'empty' => ''] as $suffix => $status) {
            $response = $test->get(route('transaction-recap.module', ['jenis' => $type,
                'pemakaian_periode' => $suffix === 'empty' ? '2099-01' : '', 'pemakaian_status' => $status]))->assertOk();
            file_put_contents($directory.'/module-'.$type.($suffix ? '-'.$suffix : '').'.html', $response->getContent());
        }
    }
    $test->actingAs(\App\Models\User::findOrFail(1));
    $event = \App\Models\PerongkosanEventDalamKota::where('status', 'DRAFT')->firstOrFail();
    $response = $test->get(route('transaction-recap.show', ['EDK', $event->id]))->assertOk();
    file_put_contents($directory.'/event.html', $response->getContent());
    $rejected = \App\Models\TransaksiPerjalananDinasHeader::firstOrFail();
    $rejection = $rejected->financeChecks()->latest('id')->firstOrFail();
    $pages['rejected-detail'] = route('transaction-recap.show', ['PD', $rejected->id]);
    file_put_contents($directory.'/rejected-detail.html', $test->get($pages['rejected-detail'])->assertOk()->getContent());
    $test->post(route('transaction-recap.correct', ['PD', $rejected->id]), ['check_id' => $rejection->id])->assertSessionHasNoErrors();
    $pages['correcting-detail'] = $pages['rejected-detail'];
    file_put_contents($directory.'/correcting-detail.html', $test->get($pages['correcting-detail'])->assertOk()->getContent());
    file_put_contents($directory.'/pages.json', json_encode([...array_keys($pages), 'event'], JSON_THROW_ON_ERROR));
    echo "Rendered ".(count($pages) + 1)." page previews and module navigation fixtures with synthetic in-memory data; business database not accessed.\n";
} finally {
    // This is a standalone renderer, not a PHPUnit runner. Closing the in-memory
    // connection disposes of its data; process exit releases the fixture state.
    \Illuminate\Support\Facades\DB::disconnect();
}
