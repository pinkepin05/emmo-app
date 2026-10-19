<?php

namespace Tests\Concerns;

use App\Models\TransaksiOperasionalHeader;
use App\Models\TransaksiPerjalananDinasHeader;
use App\Models\User;
use App\Services\BsUsageCheckService;
use App\Services\TransactionDetailPhotoService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;

trait TransactionDetailPhotoScenarios
{
    public static function photoModules(): array
    {
        return ['Operasional' => [false], 'Perjalanan Dinas' => [true]];
    }

    private function photoModule(bool $travel): string
    {
        return $travel ? 'transaksi_perjalanan_dinas' : 'transaksi_operasional';
    }

    private function photoKey($source)
    {
        return $source instanceof TransaksiPerjalananDinasHeader ? $source->transaksi_id : $source->id;
    }

    private function photoSource(bool $travel)
    {
        Storage::fake(TransactionDetailPhotoService::DISK);
        $payload = $this->transactionPayload($travel);
        $payload['details'][0]['photo'] = UploadedFile::fake()->image('struk.jpg', 4, 4);
        $this->post(route($this->photoModule($travel).'.store'), $payload)->assertSessionHasNoErrors();
        $model = $travel ? TransaksiPerjalananDinasHeader::class : TransaksiOperasionalHeader::class;

        return $model::latest('id')->firstOrFail();
    }

    #[DataProvider('photoModules')]
    public function test_detail_photo_create_keeps_sparse_rows_and_private_access(bool $travel): void
    {
        Storage::fake(TransactionDetailPhotoService::DISK);
        Storage::fake('public');
        $payload = $this->transactionPayload($travel);
        $line = $payload['details'][0];
        $payload['details'] = [
            2 => array_replace($line, ['remark' => 'Tiket', 'photo' => UploadedFile::fake()->image('tiket.jpg', 4, 4)]),
            8 => array_replace($line, ['remark' => 'Makan', 'photo' => UploadedFile::fake()->image('makan.png', 5, 5)]),
            11 => array_replace($line, ['remark' => 'Tanpa foto', 'photo_path' => 'receipts/forged.jpg']),
        ];
        $this->post(route($this->photoModule($travel).'.store'), $payload)->assertSessionHasNoErrors();
        $model = $travel ? TransaksiPerjalananDinasHeader::class : TransaksiOperasionalHeader::class;
        $source = $model::firstOrFail();
        $rows = $source->details()->orderBy('id')->get();
        $this->assertCount(3, $rows);
        $this->assertSame('tiket.jpg', $rows[0]->photo_name);
        $this->assertSame('makan.png', $rows[1]->photo_name);
        $this->assertNull($rows[2]->photo_path);
        $this->assertCount(2, Storage::disk(TransactionDetailPhotoService::DISK)->allFiles());
        $this->assertCount(0, Storage::disk('public')->allFiles());
        $this->get($rows[0]->photo_url)->assertOk()->assertHeader('Content-Type', 'image/jpeg')->assertHeader('X-Content-Type-Options', 'nosniff');
        $response = $this->get($rows[1]->photo_url)->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->assertSame(Storage::disk(TransactionDetailPhotoService::DISK)->get($rows[1]->photo_path), $response->streamedContent());
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->get($rows[0]->photo_url.'?download=1')->assertDownload('tiket.jpg');
        $this->get(route($this->photoModule($travel).'.show', $this->photoKey($source)))->assertOk()->assertSee($rows[0]->photo_url);
        $this->actingAs(User::findOrFail(3))->get($rows[0]->photo_url)->assertForbidden();
        $this->actingAs(User::findOrFail(2))->get($rows[0]->photo_url)->assertOk();
        auth()->logout();
        $this->get($rows[0]->photo_url)->assertRedirect(route('login'));
    }

    #[DataProvider('photoModules')]
    public function test_detail_photo_edit_preserves_replaces_adds_and_removes(bool $travel): void
    {
        $source = $this->photoSource($travel);
        $row = $source->details()->firstOrFail();
        $oldPath = $row->photo_path;
        $payload = $this->transactionPayload($travel);
        $line = $payload['details'][0];
        $payload['details'] = [$row->id => $line];
        $url = route($this->photoModule($travel).'.update', $this->photoKey($source));
        $this->put($url, $payload)->assertSessionHasNoErrors();
        $this->assertSame($oldPath, $row->fresh()->photo_path);
        $payload['details'][$row->id]['photo'] = UploadedFile::fake()->image('pengganti.png', 5, 5);
        $payload['new_details'][9] = $line + ['photo' => UploadedFile::fake()->image('parkir.jpg', 6, 6)];
        $this->put($url, $payload)->assertSessionHasNoErrors();
        $row->refresh();
        $this->assertSame('pengganti.png', $row->photo_name);
        Storage::disk(TransactionDetailPhotoService::DISK)->assertMissing($oldPath);
        $this->assertSame('parkir.jpg', $source->details()->latest('id')->first()->photo_name);
        $this->get(route($this->photoModule($travel).'.show', $this->photoKey($source)).'?edit=1')->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)->assertSee($row->photo_url)
            ->assertSee('name="details['.$row->id.'][remove_photo]"', false);
        unset($payload['new_details'], $payload['details'][$row->id]['photo']);
        $payload['details'][$row->id]['remove_photo'] = 1;
        $path = $row->photo_path;
        $this->put($url, $payload)->assertSessionHasNoErrors();
        $this->assertNull($row->fresh()->photo_path);
        Storage::disk(TransactionDetailPhotoService::DISK)->assertMissing($path);
    }

    #[DataProvider('photoModules')]
    public function test_detail_photo_add_endpoint_and_deletion_cleanup(bool $travel): void
    {
        $source = $this->photoSource($travel);
        $row = $source->details()->firstOrFail();
        $prefix = $this->photoModule($travel);
        $key = $this->photoKey($source);
        $line = $this->transactionPayload($travel)['details'][0];
        $this->post(route($prefix.'.store_detail', $key), ['details' => [7 => $line + ['photo' => UploadedFile::fake()->image('tambahan.jpg', 3, 3)]]])->assertSessionHasNoErrors();
        $new = $source->details()->latest('id')->firstOrFail();
        $this->assertSame('tambahan.jpg', $new->photo_name);
        $payload = $this->transactionPayload($travel);
        unset($payload['details']);
        $payload['delete_details'] = [$row->id];
        $this->put(route($prefix.'.update', $key), $payload)->assertSessionHasNoErrors();
        Storage::disk(TransactionDetailPhotoService::DISK)->assertMissing($row->photo_path);
        $this->get($row->photo_url)->assertNotFound();
        $this->actingAs($this->transactionActionUser())->delete(route($prefix.'.destroy', $key))->assertRedirect()->assertSessionHasNoErrors();
        Storage::disk(TransactionDetailPhotoService::DISK)->assertMissing($new->photo_path);
    }

    #[DataProvider('photoModules')]
    public function test_detail_photo_rejects_invalid_uploads_without_saving(bool $travel): void
    {
        Storage::fake(TransactionDetailPhotoService::DISK);
        $text = UploadedFile::fake()->createWithContent('fake.jpg', 'not an image');
        $files = [
            new UploadedFile($text->getRealPath(), 'fake.jpg', 'image/jpeg', null, true),
            UploadedFile::fake()->image('oversize.jpg')->size(2049),
            UploadedFile::fake()->image('wrong.gif'),
            UploadedFile::fake()->createWithContent('vector.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>'),
        ];
        foreach ($files as $file) {
            $payload = $this->transactionPayload($travel);
            $payload['details'][0]['photo'] = $file;
            $this->post(route($this->photoModule($travel).'.store'), $payload)->assertSessionHasErrors('details.0.photo');
        }
        $this->assertCount(0, Storage::disk(TransactionDetailPhotoService::DISK)->allFiles());
        $this->assertDatabaseCount($travel ? 'transaksi_perjalanan_dinas_header' : 'transaksi_operasional_area_sales_header', 0);
    }

    #[DataProvider('photoModules')]
    public function test_detail_photo_cannot_change_another_transactions_item_or_finalized_source(bool $travel): void
    {
        $source = $this->photoSource($travel);
        $other = $this->expense($this->bs(), travel: $travel, attributes: ['user_id' => 3]);
        $row = $other->details()->firstOrFail();
        $payload = $this->transactionPayload($travel);
        $payload['details'] = [$row->id => $payload['details'][0] + ['photo' => UploadedFile::fake()->image('foreign.jpg')]];
        $this->put(route($this->photoModule($travel).'.update', $this->photoKey($source)), $payload)->assertSessionHasErrors('details');
        $this->assertNull($row->fresh()->photo_path);
        $this->assertCount(1, Storage::disk(TransactionDetailPhotoService::DISK)->allFiles());
        $row = $source->details()->first();
        $source->update(['status' => 'VALIDATED']);
        $payload['details'] = [$row->id => $this->transactionPayload($travel)['details'][0] + ['photo' => UploadedFile::fake()->image('final.jpg')]];
        $this->putJson(route($this->photoModule($travel).'.update', $this->photoKey($source)), $payload)->assertUnprocessable();
        $this->assertSame($row->photo_path, $row->fresh()->photo_path);
        $this->assertCount(1, Storage::disk(TransactionDetailPhotoService::DISK)->allFiles());
    }

    #[DataProvider('photoModules')]
    public function test_detail_photo_failed_confirmation_cleans_up_upload(bool $travel): void
    {
        Storage::fake(TransactionDetailPhotoService::DISK);
        $payload = $this->transactionPayload($travel);
        $payload['save_action'] = 'confirm';
        $payload['details'][0]['photo'] = UploadedFile::fake()->image('gagal.jpg');
        // No BS balance exists, so confirmation rolls back after the file was stored.
        $this->post(route($this->photoModule($travel).'.store'), $payload)->assertSessionHasErrors('bs_balance');
        $this->assertCount(0, Storage::disk(TransactionDetailPhotoService::DISK)->allFiles());
        $this->assertDatabaseCount($travel ? 'transaksi_perjalanan_dinas_header' : 'transaksi_operasional_area_sales_header', 0);
    }

    public function test_detail_photo_outer_rollback_preserves_old_file_and_discards_replacement(): void
    {
        $source = $this->photoSource(false);
        $row = $source->details()->first();
        Route::post('/test-photo-rollback', function (Request $request) use ($row) {
            $row->update(app(TransactionDetailPhotoService::class)->attributes($request, 'details.0'));
            return response('Failure after upload', 500);
        })->middleware(['web', 'auth', 'bs.atomic'])->name('test-photo.rollback');
        $this->post('/test-photo-rollback', ['tanggal' => '2026-09-16', 'details' => [['photo' => UploadedFile::fake()->image('replacement.jpg')]]])->assertStatus(500);
        $this->assertSame('struk.jpg', $row->fresh()->photo_name);
        Storage::disk(TransactionDetailPhotoService::DISK)->assertExists($row->fresh()->photo_path);
        $this->assertCount(1, Storage::disk(TransactionDetailPhotoService::DISK)->allFiles());
    }

    #[DataProvider('photoModules')]
    public function test_detail_photo_finance_history_keeps_original_evidence_after_correction(bool $travel): void
    {
        $this->bs();
        $source = $this->photoSource($travel);
        $row = $source->details()->firstOrFail();
        $oldPath = $row->photo_path;
        $type = $travel ? 'PD' : 'OPR';
        $prefix = $this->photoModule($travel);
        $this->post(route($prefix.'.validate', $this->photoKey($source)))->assertSessionHasNoErrors();
        $source->refresh();
        $check = $this->recapDecision($source, $type, 'REJECTED', '0', 'Perbaiki foto struk');
        $saved = $check->details()->firstOrFail();
        $this->assertSame($oldPath, $saved->source_photo_path);
        $historyUrl = route('transaction-detail-photo.history', $saved->id);
        $this->actingAs(User::findOrFail(2))->get($historyUrl)->assertOk();
        $this->get(route('bs-usage-checks.show', $check))->assertOk()->assertSee($historyUrl);
        $this->actingAs(User::findOrFail(1));
        $this->get($historyUrl)->assertForbidden();
        $this->post(route('transaction-recap.correct', [$type, $source->id]), ['check_id' => $check->id])->assertSessionHasNoErrors();
        $before = app(BsUsageCheckService::class)->snapshot($source->fresh())['fingerprint'];
        $payload = $this->transactionPayload($travel);
        $payload['details'] = [$row->id => $payload['details'][0] + ['photo' => UploadedFile::fake()->image('koreksi.png')]];
        $this->put(route($prefix.'.update', $this->photoKey($source)), $payload)->assertSessionHasNoErrors();
        $this->assertNotSame($before, app(BsUsageCheckService::class)->snapshot($source->fresh())['fingerprint']);
        $this->assertSame($oldPath, $saved->fresh()->source_photo_path);
        Storage::disk(TransactionDetailPhotoService::DISK)->assertExists($oldPath);
        $this->actingAs(User::findOrFail(2))->get($historyUrl)->assertOk();
    }

    public function test_detail_photo_missing_file_and_invalid_path_return_not_found(): void
    {
        $source = $this->photoSource(false);
        $row = $source->details()->firstOrFail();
        $url = $row->photo_url;
        Storage::disk(TransactionDetailPhotoService::DISK)->delete($row->photo_path);
        $this->get($url)->assertNotFound();
        DB::table($row->getTable())->where('id', $row->id)->update(['photo_path' => '../secret.jpg']);
        $this->get($url)->assertNotFound();
    }
}
