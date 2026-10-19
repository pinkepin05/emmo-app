<?php

namespace Tests\Concerns;

use App\Models\Periode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;

trait TransactionActionScenarios
{
    private function transactionActionUser(string $code = 'FINANCE'): User
    {
        $role = DB::table('roles')->where('code', $code)->value('id')
            ?? DB::table('roles')->insertGetId(['code' => $code]);
        User::findOrFail(2)->update(['roles_id' => $role]);

        return User::findOrFail(2);
    }

    private function transactionActionSources(): array
    {
        $bs = $this->bs();

        return [
            'transaksi_operasional' => $this->expense($bs),
            'transaksi_perjalanan_dinas' => $this->expense($bs, travel: true),
            'perongkosan_event_dalam_kota' => $this->recapEvent('EDK'),
            'perongkosan_event_luar_kota' => $this->recapEvent('ELK'),
        ];
    }

    private function transactionActionKey(string $route, $source)
    {
        return $route === 'transaksi_perjalanan_dinas' ? $source->transaksi_id : $source->id;
    }

    public function test_transaction_lists_total_current_details_including_drafts_and_empty_transactions(): void
    {
        [$company] = $this->prepareMonitoringCompanies();
        $bs = $this->bs();

        foreach (['OPR' => 'transaksi_operasional', 'PD' => 'transaksi_perjalanan_dinas'] as $type => $route) {
            $source = $this->expense($bs, 'VALIDATED', '125000.25', $type === 'PD');
            $source->details()->create(['cost_category_id' => 1, 'remark' => 'Biaya kedua', 'nominal' => '74999.75']);
            $removed = $source->details()->create(['cost_category_id' => 1, 'remark' => 'Dihapus', 'nominal' => '99999.99']);
            $removed->delete();
            $this->recapDecision($source, $type, 'APPROVED', '170000');

            $draft = $this->expense($bs, amount: '1234.56', travel: $type === 'PD');
            $empty = $this->expense($bs, amount: '100', travel: $type === 'PD');
            $empty->details()->get()->each->delete();

            $this->actingAs(User::findOrFail(1));
            $responses = [$this->get(route($route.'.index', ['periode' => '2026-09']))->assertOk()];
            $this->actingAs(User::findOrFail(2));
            $responses[] = $this->get(route('monitoring-finance.module.index', [
                'company' => $company->id, 'user' => 1, 'jenis' => $type, 'periode' => '2026-09',
            ]))->assertOk();

            foreach ($responses as $response) {
                $response->assertSee('Total Nominal');
                $document = new \DOMDocument();
                @$document->loadHTML($response->getContent());
                $xpath = new \DOMXPath($document);
                foreach ([$source->transaksi_id => 'Rp 200.000,00', $draft->transaksi_id => 'Rp 1.234,56', $empty->transaksi_id => 'Rp 0,00'] as $reference => $amount) {
                    $cell = $xpath->query('//tr[td/span[normalize-space()="'.$reference.'"]]//*[contains(concat(" ", normalize-space(@class), " "), " transaction-total ")]');
                    $this->assertSame(1, $cell->length);
                    $this->assertSame($amount, trim($cell->item(0)->textContent));
                }
            }
        }
    }

    public function test_user_transaction_actions_open_detail_and_forbid_deleting_owned_transactions(): void
    {
        foreach ($this->transactionActionSources() as $route => $source) {
            $key = $this->transactionActionKey($route, $source);
            $before = $source->fresh()->getAttributes();
            $this->get(route($route.'.index'))->assertOk()
                ->assertSee('Total Nominal')
                ->assertSee(in_array($route, ['transaksi_operasional', 'transaksi_perjalanan_dinas'], true) ? 'Rp 600.000,00' : 'Rp 300,00')
                ->assertSee('href="'.route($route.'.show', $key).'"', false)
                ->assertSee('transaction-action-detail')->assertDontSee('transaction-action-delete')
                ->assertDontSee('data-action-toggle')->assertDontSee('data-action-menu');
            $this->get(route($route.'.show', $key))->assertOk();
            $this->deleteJson(route($route.'.destroy', $key))->assertForbidden();
            $this->assertSame($before, $source->fresh()->getAttributes());
        }
        $this->assertDatabaseCount('transaksi_operasional_area_sales_detail', 1);
        $this->assertDatabaseCount('transaksi_perjalanan_dinas_detail', 1);
    }

    public static function transactionDeletionRoles(): array
    {
        return ['Admin' => ['ADMIN'], 'Administrator' => ['ADMINISTRATOR'], 'Finance' => ['FINANCE']];
    }

    #[DataProvider('transactionDeletionRoles')]
    public function test_admin_and_finance_transaction_actions_delete_eligible_transactions(string $role): void
    {
        $sources = $this->transactionActionSources();
        $this->actingAs($this->transactionActionUser($role));
        foreach ($sources as $route => $source) {
            $key = $this->transactionActionKey($route, $source);
            $this->get(route($route.'.index'))->assertOk()
                ->assertSee('href="'.route($route.'.show', $key).'"', false)
                ->assertSee('action="'.route($route.'.destroy', $key).'"', false)
                ->assertSee('transaction-action-detail')->assertSee('transaction-action-delete')
                ->assertDontSee('data-action-toggle');
            $this->get(route($route.'.show', $key))->assertOk();
            $this->delete(route($route.'.destroy', $key))->assertRedirect(route($route.'.index'))->assertSessionHasNoErrors();
            $this->assertNull($source->fresh());
        }
        $this->assertDatabaseCount('transaksi_operasional_area_sales_detail', 0);
        $this->assertSame(0, \App\Models\TransaksiPerjalananDinasDetail::count());
        $this->assertSoftDeleted('transaksi_perjalanan_dinas_detail', [
            'transaksi_id' => $sources['transaksi_perjalanan_dinas']->transaksi_id,
        ]);
    }

    public function test_director_and_finance_permission_alone_do_not_grant_transaction_deletion(): void
    {
        $sources = $this->transactionActionSources();
        $this->actingAs($this->transactionActionUser('DIR'));
        foreach ($sources as $route => $source) {
            $this->get(route($route.'.index'))->assertOk()
                ->assertSee('transaction-action-detail')->assertDontSee('transaction-action-delete');
            $this->deleteJson(route($route.'.destroy', $this->transactionActionKey($route, $source)))->assertForbidden();
        }
        $owner = User::findOrFail(1);
        $owner->givePermissionTo('akses.approval-finance');
        $this->actingAs($owner);
        foreach ($sources as $route => $source) {
            $this->deleteJson(route($route.'.destroy', $this->transactionActionKey($route, $source)))->assertForbidden();
            $this->assertNotNull($source->fresh());
        }
    }

    public function test_privileged_transaction_deletion_keeps_final_status_and_period_locks(): void
    {
        $sources = $this->transactionActionSources();
        $this->actingAs($this->transactionActionUser());
        foreach (array_slice($sources, 0, 2, true) as $route => $source) {
            $source->update(['status' => 'VALIDATED']);
            $this->get(route($route.'.index'))->assertOk()->assertDontSee('transaction-action-delete');
            $this->deleteJson(route($route.'.destroy', $this->transactionActionKey($route, $source)))
                ->assertUnprocessable()->assertJsonValidationErrors('error');
        }
        Periode::where('periode', '2026-09')->update(['status' => 'CLOSED']);
        foreach ($sources as $route => $source) {
            $this->get(route($route.'.index'))->assertOk()->assertDontSee('transaction-action-delete');
            $this->deleteJson(route($route.'.destroy', $this->transactionActionKey($route, $source)))
                ->assertUnprocessable()->assertJsonValidationErrors('tanggal');
            $this->assertNotNull($source->fresh());
        }
    }
}
