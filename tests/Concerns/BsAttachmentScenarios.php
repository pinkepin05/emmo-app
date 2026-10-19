<?php

namespace Tests\Concerns;

use App\Http\Controllers\PengajuanBsController;
use App\Models\BsActivityLog;
use App\Models\PengajuanBs;
use App\Models\User;
use App\Services\BonSementaraService;
use App\Services\BsAttachmentService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;

trait BsAttachmentScenarios
{
    private function attachmentPayload(?UploadedFile $file = null): array
    {
        return ['tipe_pengajuan' => 'perjalanan_dinas', 'nominal_pengajuan' => '100000',
            'description' => 'Perjalanan uji', 'attachment' => $file];
    }

    private function travelPdf(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('Izin perjalanan.pdf', "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF");
    }

    private function submitAttachment(): PengajuanBs
    {
        Storage::fake(BsAttachmentService::DISK);
        Storage::fake('public');
        $this->post(route('pengajuan-bs.store'), $this->attachmentPayload($this->travelPdf()))
            ->assertRedirect(route('pengajuan-bs.index'))->assertSessionHasNoErrors();

        return PengajuanBs::latest('id')->firstOrFail();
    }

    public function test_attachment_pdf_is_private_and_survives_the_outer_commit(): void
    {
        $bs = $this->submitAttachment();
        $this->assertSame('Izin perjalanan.pdf', $bs->attachment_name);
        $this->assertSame('PENDING_ATASAN', $bs->status);
        Storage::disk(BsAttachmentService::DISK)->assertExists($bs->attachment_path);
        $this->assertCount(1, Storage::disk(BsAttachmentService::DISK)->allFiles());
        $this->assertCount(0, Storage::disk('public')->allFiles());
        $this->get(route('pengajuan-bs.attachment', $bs))->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringStartsWith('%PDF-1.4', $this->get(route('pengajuan-bs.attachment', $bs))->streamedContent());
        $this->get(route('pengajuan-bs.attachment', ['bs' => $bs, 'download' => 1]))
            ->assertDownload('Izin perjalanan.pdf');
        $this->assertStringContainsString('no-store', $this->get(route('pengajuan-bs.attachment', $bs))->headers->get('Cache-Control'));
    }

    public function test_attachment_jpeg_can_be_uploaded(): void
    {
        Storage::fake(BsAttachmentService::DISK);
        $this->post(route('pengajuan-bs.store'), $this->attachmentPayload(
            UploadedFile::fake()->image('izin.jpg', 2, 2)
        ))->assertRedirect(route('pengajuan-bs.index'))->assertSessionHasNoErrors();
        $bs = PengajuanBs::firstOrFail();
        $this->get(route('pengajuan-bs.attachment', $bs))->assertOk()->assertHeader('Content-Type', 'image/jpeg');
    }

    public static function invalidAttachments(): array
    {
        return ['missing' => ['missing'], 'spoofed PDF' => ['spoofed'], 'PNG' => ['png'],
            'too large' => ['large'], 'wrong extension' => ['extension']];
    }

    #[DataProvider('invalidAttachments')]
    public function test_attachment_validation_rejects_invalid_uploads(string $case): void
    {
        Storage::fake(BsAttachmentService::DISK);
        $spoofed = UploadedFile::fake()->createWithContent('izin.pdf', 'This is plain text, not a PDF.');
        $file = match ($case) {
            'missing' => null,
            // Real UploadedFile detects the content MIME; the client's PDF label is untrusted.
            'spoofed' => new UploadedFile($spoofed->getRealPath(), 'izin.pdf', 'application/pdf', null, true),
            'png' => UploadedFile::fake()->image('izin.png', 2, 2),
            'large' => $this->travelPdf()->size(2049),
            'extension' => UploadedFile::fake()->createWithContent('izin.txt', "%PDF-1.4\n%%EOF"),
        };
        $this->post(route('pengajuan-bs.store'), $this->attachmentPayload($file))->assertSessionHasErrors('attachment');
        $this->assertDatabaseCount('pengajuan_bs', 0);
        $this->assertCount(0, Storage::disk(BsAttachmentService::DISK)->allFiles());
    }

    public function test_attachment_is_ignored_for_operational_submission(): void
    {
        Storage::fake(BsAttachmentService::DISK);
        $data = $this->attachmentPayload(UploadedFile::fake()->createWithContent('invalid.txt', 'test'));
        $data['tipe_pengajuan'] = 'operasional_sales';
        $this->post(route('pengajuan-bs.store'), $data)->assertSessionHasNoErrors();
        $this->assertNull(PengajuanBs::firstOrFail()->attachment_path);
        $this->assertCount(0, Storage::disk(BsAttachmentService::DISK)->allFiles());
    }

    public function test_attachment_authorizes_each_reader_and_denies_guests_and_unrelated_users(): void
    {
        $bs = $this->submitAttachment();
        $url = route('pengajuan-bs.attachment', $bs);
        $this->actingAs(User::findOrFail(3))->get($url)->assertForbidden();
        $bs->members()->attach(3);
        $this->get($url)->assertOk();

        $supervisor = User::findOrFail(2);
        $supervisor->syncPermissions([]);
        $this->actingAs($supervisor)->get($url)->assertOk();
        DB::table('user')->insert(['id' => 4, 'nama' => 'Finance uji', 'is_active' => 1]);
        $finance = User::findOrFail(4);
        $finance->givePermissionTo('akses.approval-finance');
        $this->actingAs($finance)->get($url)->assertOk();
        $finance->syncPermissions([]);
        $this->get($url)->assertForbidden();

        $bs->update(['atasan_user_id' => null]);
        $this->actingAs($supervisor)->get($url)->assertForbidden();
        auth()->logout();
        $this->get($url)->assertRedirect(route('login'));
    }

    public function test_attachment_is_visible_to_applicant_supervisor_and_finance(): void
    {
        $bs = $this->submitAttachment();
        $url = route('pengajuan-bs.attachment', $bs);
        $download = route('pengajuan-bs.attachment', ['bs' => $bs, 'download' => 1]);
        $assertPanel = function ($response) use ($url, $download) {
            $response->assertOk()->assertSee('Izin perjalanan.pdf')
                ->assertSee('Buka PDF')->assertSee('Unduh')->assertSee('Pratinjau PDF')
                ->assertSee($download)->assertSee('<iframe src="'.$url.'"', false)
                ->assertSee('title="Pratinjau PDF: Izin perjalanan.pdf"', false);
        };
        $assertPanel($this->get(route('pengajuan-bs.show', $bs)));
        $this->actingAs(User::findOrFail(2));
        $assertPanel($this->get(route('approval-atasan.detail', $bs)));
        $assertPanel($this->get(route('approval-finance.detail', $bs)));
    }

    public function test_attachment_jpeg_has_open_and_download_links_without_pdf_preview(): void
    {
        Storage::fake(BsAttachmentService::DISK);
        $this->post(route('pengajuan-bs.store'), $this->attachmentPayload(
            UploadedFile::fake()->image('Izin perjalanan.jpg')
        ))->assertSessionHasNoErrors();
        $bs = PengajuanBs::latest('id')->firstOrFail();

        $this->get(route('pengajuan-bs.show', $bs))->assertOk()
            ->assertSee('Buka Lampiran')->assertSee('Unduh')
            ->assertSee(route('pengajuan-bs.attachment', $bs))
            ->assertDontSee('Pratinjau PDF')->assertDontSee('<iframe', false);
    }

    public function test_attachment_missing_files_and_invalid_paths_return_not_found(): void
    {
        $bs = $this->submitAttachment();
        Storage::disk(BsAttachmentService::DISK)->delete($bs->attachment_path);
        $this->get(route('pengajuan-bs.attachment', $bs))->assertNotFound();
        $bs->update(['attachment_path' => '../secret.pdf']);
        $this->get(route('pengajuan-bs.attachment', $bs))->assertNotFound();
        $bs->update(['attachment_path' => null]);
        $this->get(route('pengajuan-bs.attachment', $bs))->assertNotFound();
    }

    public function test_attachment_is_removed_when_the_submission_audit_fails(): void
    {
        Storage::fake(BsAttachmentService::DISK);
        BsActivityLog::creating(fn () => throw new \RuntimeException('Attachment test audit failure'));
        try {
            $this->post(route('pengajuan-bs.store'), $this->attachmentPayload($this->travelPdf()))->assertStatus(500);
        } finally {
            BsActivityLog::flushEventListeners();
        }
        $this->assertDatabaseCount('pengajuan_bs', 0);
        $this->assertCount(0, Storage::disk(BsAttachmentService::DISK)->allFiles());
    }

    public function test_attachment_is_removed_when_the_outer_transaction_fails_after_controller_success(): void
    {
        Storage::fake(BsAttachmentService::DISK);
        Route::post('/attachment-outer-failure', function (Request $request) {
            app(PengajuanBsController::class)->store($request, app(BonSementaraService::class));
            return response('Simulated failure after saving', 500);
        })->middleware(['web', 'auth', 'bs.atomic'])->name('pengajuan-bs.attachment-test');
        $this->post('/attachment-outer-failure', $this->attachmentPayload($this->travelPdf()))->assertStatus(500);
        $this->assertDatabaseCount('pengajuan_bs', 0);
        $this->assertCount(0, Storage::disk(BsAttachmentService::DISK)->allFiles());
    }

    public function test_attachment_is_removed_after_inner_rollback_even_when_validation_redirect_commits_outer_transaction(): void
    {
        Storage::fake(BsAttachmentService::DISK);
        BsActivityLog::creating(fn () => throw \Illuminate\Validation\ValidationException::withMessages(['error' => 'Simulated validation failure']));
        try {
            $this->post(route('pengajuan-bs.store'), $this->attachmentPayload($this->travelPdf()))->assertSessionHasErrors('error');
        } finally {
            BsActivityLog::flushEventListeners();
        }
        $this->assertDatabaseCount('pengajuan_bs', 0);
        $this->assertCount(0, Storage::disk(BsAttachmentService::DISK)->allFiles());
    }

    public function test_attachment_survives_a_retried_outer_transaction_without_extra_files(): void
    {
        Storage::fake(BsAttachmentService::DISK);
        $attempts = 0;
        Route::post('/attachment-retry', function (Request $request) use (&$attempts) {
            $response = app(PengajuanBsController::class)->store($request, app(BonSementaraService::class));
            if (++$attempts === 1) {
                throw new \Illuminate\Database\DeadlockException('Deadlock found when trying to get lock');
            }
            return $response;
        })->middleware(['web', 'auth', 'bs.atomic'])->name('pengajuan-bs.attachment-retry-test');
        // Let the retryable exception reach the transaction instead of rendering an HTTP 500.
        $this->withoutExceptionHandling();
        $this->post('/attachment-retry', $this->attachmentPayload($this->travelPdf()))->assertRedirect();
        $this->assertSame(2, $attempts);
        $this->assertDatabaseCount('pengajuan_bs', 1);
        Storage::disk(BsAttachmentService::DISK)->assertExists(PengajuanBs::firstOrFail()->attachment_path);
        $this->assertCount(1, Storage::disk(BsAttachmentService::DISK)->allFiles());
    }

    public static function attachmentStorageFailures(): array
    {
        return ['false result' => [false], 'write exception' => [true]];
    }

    #[DataProvider('attachmentStorageFailures')]
    public function test_attachment_storage_failure_prevents_submission(bool $throws): void
    {
        $disk = \Mockery::mock(\Illuminate\Filesystem\FilesystemAdapter::class);
        $write = $disk->shouldReceive('putFileAs')->once();
        if ($throws) {
            $write->andThrow(\League\Flysystem\UnableToWriteFile::atLocation('test.pdf', 'Simulated write failure'));
        } else {
            $write->andReturn(false);
        }
        Storage::set(BsAttachmentService::DISK, $disk);
        $this->post(route('pengajuan-bs.store'), $this->attachmentPayload($this->travelPdf()))->assertSessionHasErrors('attachment');
        $this->assertDatabaseCount('pengajuan_bs', 0);
    }
}
