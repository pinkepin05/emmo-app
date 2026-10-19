<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;

trait CostCategoryStatusScenarios
{
    public static function costCategoryModules(): array
    {
        return [
            'operasional' => ['transaksi_operasional', false],
            'perjalanan dinas' => ['transaksi_perjalanan_dinas', true],
        ];
    }

    private function seedInactiveCostCategory(): void
    {
        DB::table('cost_category')->where('id', 1)->update(['description' => 'Transport']);
        DB::table('cost_category')->insert([
            'id' => 7, 'nama' => 'OPRT SALES', 'description' => 'Operational Salesman', 'is_active' => false,
        ]);
    }

    private function costCategoryPayload(string $date = '2026-09-16'): array
    {
        return [
            'tanggal' => $date, 'description' => 'Kunjungan sales', 'teritory' => 'Jawa',
            'tujuan_dinas_id' => 1, 'jarak_km' => 100, 'durasi_jam' => 4,
        ];
    }

    #[DataProvider('costCategoryModules')]
    public function test_cost_category_create_options_exclude_inactive_categories(string $route, bool $travel): void
    {
        $this->seedInactiveCostCategory();
        $this->get(route($route.'.create'))->assertOk()->assertSee('Transport')
            ->assertDontSee('OPRT SALES')->assertDontSee('Operational Salesman');

        DB::table('cost_category')->where('id', 7)->update(['is_active' => true]);
        $this->get(route($route.'.create'))->assertOk()->assertSee($travel ? 'Operational Salesman' : 'OPRT SALES');
    }

    #[DataProvider('costCategoryModules')]
    public function test_cost_category_store_rejects_inactive_and_missing_categories(string $route, bool $travel): void
    {
        $this->seedInactiveCostCategory();
        $payload = $this->costCategoryPayload();
        foreach ([7, 99999] as $categoryId) {
            $payload['details'] = [['cost_category_id' => $categoryId, 'remark' => 'Transport', 'nominal' => 100]];
            $this->postJson(route($route.'.store'), $payload)
                ->assertUnprocessable()->assertJsonValidationErrors('details.0.cost_category_id');
        }

        DB::table('cost_category')->where('id', 7)->update(['is_active' => true]);
        $payload['details'][0]['cost_category_id'] = 7;
        $this->post(route($route.'.store'), $payload)->assertRedirect()->assertSessionHasNoErrors();
        $table = $travel ? 'transaksi_perjalanan_dinas_detail' : 'transaksi_operasional_area_sales_detail';
        $this->assertDatabaseCount($table, 1);
        $this->assertDatabaseHas($table, ['cost_category_id' => 7]);
    }

    #[DataProvider('costCategoryModules')]
    public function test_cost_category_add_detail_and_update_reject_new_inactive_usage(string $route, bool $travel): void
    {
        $this->seedInactiveCostCategory();
        $source = $this->expense($this->bs(), travel: $travel);
        $key = $travel ? $source->transaksi_id : $source->id;
        $detail = $source->details()->firstOrFail();
        $inactive = ['cost_category_id' => 7, 'remark' => 'Tidak boleh', 'nominal' => 100];

        $this->postJson(route($route.'.store_detail', $key), ['details' => [$inactive]])
            ->assertUnprocessable()->assertJsonValidationErrors('details.0.cost_category_id');
        $payload = $this->costCategoryPayload(\Carbon\Carbon::parse($source->tanggal)->format('Y-m-d'));
        $this->putJson(route($route.'.update', $key), $payload + ['new_details' => [$inactive]])
            ->assertUnprocessable()->assertJsonValidationErrors('new_details.0.cost_category_id');
        $this->putJson(route($route.'.update', $key), $payload + ['details' => [$detail->id => $inactive]])
            ->assertUnprocessable()->assertJsonValidationErrors('details.'.$detail->id.'.cost_category_id');
        $this->assertSame(1, $source->details()->count());
        $this->assertSame(1, (int) $detail->fresh()->cost_category_id);

        $this->post(route($route.'.store_detail', $key), ['details' => [array_replace($inactive, ['cost_category_id' => 1])]])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(2, $source->details()->count());
    }

    #[DataProvider('costCategoryModules')]
    public function test_cost_category_inactive_history_is_preserved_only_on_its_original_row(string $route, bool $travel): void
    {
        $this->seedInactiveCostCategory();
        $source = $this->expense($this->bs(), travel: $travel);
        $key = $travel ? $source->transaksi_id : $source->id;
        $saved = $source->details()->firstOrFail();
        $saved->update(['cost_category_id' => 7]);
        $other = $source->details()->create(['cost_category_id' => 1, 'remark' => 'Aktif', 'nominal' => 50]);

        $this->get(route($route.'.show', $key))->assertOk()->assertSee($travel ? 'Operational Salesman' : 'OPRT SALES');
        $response = $this->get(route($route.'.show', [$key, 'edit' => 1]))->assertOk()
            ->assertSee('OPRT SALES (Nonaktif - tersimpan)');
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $options = $xpath->query('//select/option[@value="7"]');
        $this->assertSame(1, $options->length, 'Inactive category must not appear in other rows or the new-row template.');
        $this->assertSame('details['.$saved->id.'][cost_category_id]', $options->item(0)->parentNode->getAttribute('name'));
        $this->assertTrue($options->item(0)->hasAttribute('selected'));

        $payload = $this->costCategoryPayload(\Carbon\Carbon::parse($source->tanggal)->format('Y-m-d'));
        $retained = ['cost_category_id' => 7, 'remark' => 'Keterangan diperbarui', 'nominal' => 125];
        $this->putJson(route($route.'.update', $key), $payload + ['details' => [$other->id => $retained]])
            ->assertUnprocessable()->assertJsonValidationErrors('details.'.$other->id.'.cost_category_id');
        $this->put(route($route.'.update', $key), $payload + ['details' => [$saved->id => $retained]])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(7, (int) $saved->fresh()->cost_category_id);
        $this->assertSame('Keterangan diperbarui', $saved->fresh()->remark);

        $retained['cost_category_id'] = 1;
        $this->put(route($route.'.update', $key), $payload + ['details' => [$saved->id => $retained]])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(1, (int) $saved->fresh()->cost_category_id);
    }
}
