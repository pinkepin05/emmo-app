<?php

namespace Tests\Concerns;

use App\Models\Company;
use App\Models\Periode;
use App\Models\TransaksiOperasionalHeader;
use App\Models\User;
use App\Services\BsUsageCheckService;

trait FinanceMonitoringScenarios
{
    private function prepareMonitoringCompanies(): array
    {
        $this->travelTo(now()->setDate(2026, 9, 16)->setTime(10, 0));
        Periode::query()->updateOrCreate(['periode' => '2026-09'], ['status' => 'OPEN']);

        $alpha = Company::query()->updateOrCreate(['code' => 'MON-A'], ['nama' => 'Monitoring Alpha', 'is_active' => 1]);
        $beta = Company::query()->updateOrCreate(['code' => 'MON-B'], ['nama' => 'Monitoring Beta', 'is_active' => 1]);

        User::findOrFail(1)->update(['company_id' => $alpha->id, 'is_active' => 1]);
        User::findOrFail(2)->update(['company_id' => $alpha->id, 'is_active' => 1]);
        User::findOrFail(3)->update(['company_id' => $beta->id, 'is_active' => 1]);

        User::findOrFail(2)->givePermissionTo('akses.approval-finance');
        User::findOrFail(3)->syncPermissions([]);

        return [$alpha, $beta];
    }

    private function createApprovedOperationalExpense(int $userId, string $amount = '100000.00'): TransaksiOperasionalHeader
    {
        $bs = $this->bs(['user_id' => $userId, 'nominal_approved' => '1000000.00']);
        $source = $this->expense($bs, 'VALIDATED', $amount, false, ['user_id' => $userId]);
        $detail = $source->details()->firstOrFail();
        $this->dashboardFinanceCheck($source, 'OPR', [(int) $detail->cost_category_id => $amount]);
        return $source->fresh();
    }

    private function escaped(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }

    private function monitoringWorkspaceContext(Company $company, int $userId, string $periode = '2026-09', int $page = 1, ?string $jenisFilter = 'OPR'): array
    {
        $context = ['pemakaian_periode' => $periode, 'dari' => 'monitoring', 'company' => $company->id, 'user' => $userId, 'page' => $page];
        if ($jenisFilter !== null) $context['jenis_filter'] = $jenisFilter;
        return $context;
    }

    private function monitoringCheckPayload($source, string $type, array $context, bool $draft = false): array
    {
        $service = app(BsUsageCheckService::class);
        $snapshot = $service->snapshot($source);
        $detail = $source->details()->firstOrFail();

        $payload = [
            'tanggal_pengecekan' => '2026-09-16',
            'source_token' => $service->token($type, $source->id, $snapshot['fingerprint']),
            'details' => [['source_detail_id' => $detail->id, 'cost_category_id' => $detail->cost_category_id, 'nominal_disetujui' => $detail->nominal, 'catatan' => 'Diperiksa dari monitoring']],
            '_workspace' => true,
            'workspace_context' => $context,
        ];

        if ($draft) {
            $form = $this->get(route('bs-usage-checks.create', [$type, $source->id]))->assertOk();
            $payload['save_action'] = 'draft';
            $payload['review_token'] = $form->viewData('reviewToken');
        }
        return $payload;
    }

    public function test_finance_monitoring_requires_approval_finance_permission(): void
    {
        [$alpha] = $this->prepareMonitoringCompanies();
        $this->actingAs(User::findOrFail(2));
        $this->get(route('monitoring-finance.index'))->assertOk()->assertSee('Monitoring Alpha');
        $this->actingAs(User::findOrFail(3));
        $this->get(route('monitoring-finance.index'))->assertForbidden();
    }

    public function test_finance_monitoring_shows_all_companies_including_inactive(): void
    {
        [$alpha, $beta] = $this->prepareMonitoringCompanies();
        $beta->update(['is_active' => 0]);
        $this->actingAs(User::findOrFail(2));
        $this->get(route('monitoring-finance.index'))->assertOk()->assertSee('Monitoring Alpha')->assertSee('Monitoring Beta')->assertSee('Nonaktif');
    }

    public function test_finance_monitoring_company_page_shows_inactive_users_and_aggregates_all_users(): void
    {
        [$alpha] = $this->prepareMonitoringCompanies();
        User::findOrFail(3)->update(['company_id' => $alpha->id, 'is_active' => 0]);
        $this->createApprovedOperationalExpense(1, '100000.00');
        $this->createApprovedOperationalExpense(3, '200000.00');
        $this->actingAs(User::findOrFail(2));
        $response = $this->get(route('monitoring-finance.company', ['company' => $alpha->id, 'periode' => '2026-09']));
        $response->assertOk()->assertSee('Monitoring Alpha')->assertSee(User::findOrFail(1)->nama)->assertSee(User::findOrFail(3)->nama)->assertSee('Nonaktif');
    }

    public function test_finance_monitoring_rejects_cross_company_user_url(): void
    {
        [$alpha, $beta] = $this->prepareMonitoringCompanies();
        $this->actingAs(User::findOrFail(2));
        $this->get(route('monitoring-finance.user', ['company' => $alpha->id, 'user' => 3, 'periode' => '2026-09']))->assertForbidden();
        $this->get(route('monitoring-finance.user', ['company' => $beta->id, 'user' => 1, 'periode' => '2026-09']))->assertForbidden();
    }

    public function test_finance_monitoring_user_page_paginates_transactions_from_query(): void
    {
        [$alpha] = $this->prepareMonitoringCompanies();
        $bs = $this->bs(['user_id' => 1, 'nominal_approved' => '1000000.00']);
        for ($i = 0; $i < 25; $i++) $this->expense($bs, 'VALIDATED', '1000.00', false, ['user_id' => 1]);
        $this->actingAs(User::findOrFail(2));
        $responsePage1 = $this->get(route('monitoring-finance.user', ['company' => $alpha->id, 'user' => 1, 'periode' => '2026-09', 'page' => 1]));
        $paginatorPage1 = $responsePage1->viewData('transactions');
        $this->assertSame(25, $paginatorPage1->total());
        $this->assertSame(20, $paginatorPage1->count());
    }

    public function test_finance_monitoring_validates_page_and_period_input(): void
    {
        [$alpha] = $this->prepareMonitoringCompanies();
        $this->actingAs(User::findOrFail(2));
        $this->getJson(route('monitoring-finance.user', ['company' => $alpha->id, 'user' => 1, 'periode' => '2026-09', 'page' => 'abc']))->assertStatus(422)->assertJsonValidationErrors('page');
        $this->getJson(route('monitoring-finance.user', ['company' => $alpha->id, 'user' => 1, 'periode' => ['invalid']]))->assertStatus(422)->assertJsonValidationErrors('periode');
    }

    public function test_finance_monitoring_shows_current_finance_decision_not_old_decision_after_revision(): void
    {
        [$alpha] = $this->prepareMonitoringCompanies();
        $source = $this->createApprovedOperationalExpense(1, '100000.00');
        $this->actingAs(User::findOrFail(2));
        $this->get(route('monitoring-finance.user', ['company' => $alpha->id, 'user' => 1, 'periode' => '2026-09']))->assertOk()->assertSee('Disetujui');
        $source->increment('submission_version');
        $this->get(route('monitoring-finance.user', ['company' => $alpha->id, 'user' => 1, 'periode' => '2026-09']))->assertOk()->assertSee('Perlu Dicek Ulang')->assertDontSee('Disetujui');
    }

    public function test_finance_monitoring_detail_transaction_preserves_monitoring_context(): void
    {
        [$alpha] = $this->prepareMonitoringCompanies();
        $source = $this->createApprovedOperationalExpense(1, '100000.00');
        $this->actingAs(User::findOrFail(2));
        $detailUrl = route('transaction-recap.show', ['jenis' => 'OPR', 'source' => $source->id, 'pemakaian_periode' => '2026-09', 'dari' => 'monitoring', 'company' => $alpha->id, 'user' => 1, 'page' => 1]);
        $response = $this->get($detailUrl);
        $expectedBackUrl = route('monitoring-finance.user', ['company' => $alpha->id, 'user' => 1, 'periode' => '2026-09', 'page' => 1]);
        $response->assertOk()->assertSee('href="' . $this->escaped($expectedBackUrl) . '#monitoring-transactions"', false);
    }

    public function test_finance_monitoring_detail_transaction_rejects_invalid_context(): void
    {
        [$alpha, $beta] = $this->prepareMonitoringCompanies();
        $source = $this->createApprovedOperationalExpense(1, '100000.00');
        $this->actingAs(User::findOrFail(2));
        $this->get(route('transaction-recap.show', ['jenis' => 'OPR', 'source' => $source->id, 'pemakaian_periode' => '2026-09', 'dari' => 'monitoring', 'company' => $beta->id, 'user' => 1, 'page' => 1]))->assertForbidden();
        $this->get(route('transaction-recap.show', ['jenis' => 'OPR', 'source' => $source->id, 'pemakaian_periode' => '2026-09', 'dari' => 'monitoring', 'company' => $alpha->id, 'user' => 3, 'page' => 1]))->assertForbidden();
    }

    public function test_finance_monitoring_can_store_check_from_monitoring_workspace(): void
    {
        [$alpha] = $this->prepareMonitoringCompanies();
        $bs = $this->bs(['user_id' => 1, 'nominal_approved' => '1000000.00']);
        $source = $this->expense($bs, 'VALIDATED', '100000.00', false, ['user_id' => 1]);
        $this->actingAs(User::findOrFail(2));
        $response = $this->post(route('bs-usage-checks.store', ['jenis' => 'OPR', 'source' => $source->id]), $this->monitoringCheckPayload($source, 'OPR', $this->monitoringWorkspaceContext($alpha, 1), false));
        $expected = route('transaction-recap.show', ['jenis' => 'OPR', 'source' => $source->id, 'pemakaian_periode' => '2026-09', 'dari' => 'monitoring', 'company' => $alpha->id, 'user' => 1, 'page' => 1, 'jenis_filter' => 'OPR']);
        $response->assertSessionHasNoErrors()->assertRedirect($expected . '#hasil-finance');
        $this->assertSame(1, \App\Models\BsUsageCheck::query()->where('jenis_transaksi', 'OPR')->where('transaksi_pemakaian_id', $source->id)->count());
    }

    public function test_finance_monitoring_store_rejects_mismatched_context_before_persisting(): void
    {
        [$alpha, $beta] = $this->prepareMonitoringCompanies();
        $bs = $this->bs(['user_id' => 1, 'nominal_approved' => '1000000.00']);
        $source = $this->expense($bs, 'VALIDATED', '100000.00', false, ['user_id' => 1]);
        $this->actingAs(User::findOrFail(2));
        $validContext = $this->monitoringWorkspaceContext($alpha, 1);
        $wrongCompany = $validContext; $wrongCompany['company'] = $beta->id;
        $wrongUser = $validContext; $wrongUser['user'] = 3;

        foreach ([false, true] as $draft) {
            foreach ([$wrongCompany, $wrongUser] as $context) {
                $this->post(route('bs-usage-checks.store', ['jenis' => 'OPR', 'source' => $source->id]), $this->monitoringCheckPayload($source, 'OPR', $context, $draft))->assertForbidden();
                $this->assertSame(0, \App\Models\BsUsageCheck::query()->count(), 'Keputusan Finance tidak boleh tersimpan saat konteks salah.');
                $this->assertSame(0, \App\Models\BsUsageCheckDraft::query()->count(), 'Draft tidak boleh tersimpan saat konteks salah.');
                $this->assertSame('1000000.00', app(\App\Services\BonSementaraService::class)->getRealtimeSisaSaldo(1, '2026-09'), 'Saldo BS tidak boleh berubah saat konteks salah.');
            }
        }
    }

    public function test_finance_monitoring_can_save_draft_from_monitoring_workspace(): void
    {
        [$alpha] = $this->prepareMonitoringCompanies();
        $bs = $this->bs(['user_id' => 1, 'nominal_approved' => '1000000.00']);
        $source = $this->expense($bs, 'VALIDATED', '100000.00', false, ['user_id' => 1]);
        $this->actingAs(User::findOrFail(2));
        $response = $this->post(route('bs-usage-checks.store', ['jenis' => 'OPR', 'source' => $source->id]), $this->monitoringCheckPayload($source, 'OPR', $this->monitoringWorkspaceContext($alpha, 1), true));
        $expected = route('transaction-recap.show', ['jenis' => 'OPR', 'source' => $source->id, 'pemakaian_periode' => '2026-09', 'dari' => 'monitoring', 'company' => $alpha->id, 'user' => 1, 'page' => 1, 'jenis_filter' => 'OPR', 'pemeriksaan' => 1]);
        $response->assertSessionHasNoErrors()->assertRedirect($expected);
        $this->assertSame(1, \App\Models\BsUsageCheckDraft::query()->where('jenis_transaksi', 'OPR')->where('transaksi_pemakaian_id', $source->id)->count());
        $this->assertSame(0, \App\Models\BsUsageCheck::query()->count());
        $this->assertSame('1000000.00', app(\App\Services\BonSementaraService::class)->getRealtimeSisaSaldo(1, '2026-09'));
    }
}