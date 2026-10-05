<?php

namespace App\Services;

use App\Models\BsUsageCheck;
use App\Models\BsUsageCheckDraft;
use App\Models\TransaksiOperasionalHeader;
use App\Models\TransaksiPerjalananDinasHeader;
use App\Models\PerongkosanEventDalamKota;
use App\Models\PerongkosanEventLuarKota;
use App\Models\User;
use App\Support\Money;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BsUsageCheckService
{
    public const TYPES = [
        'OPR' => TransaksiOperasionalHeader::class,
        'PD' => TransaksiPerjalananDinasHeader::class,
        'EDK' => PerongkosanEventDalamKota::class,
        'ELK' => PerongkosanEventLuarKota::class,
    ];

    public const LABELS = ['OPR' => 'Operasional Sales', 'PD' => 'Perjalanan Dinas', 'EDK' => 'Event Dalam Kota', 'ELK' => 'Event Luar Kota'];
    public const DECISIONS = ['APPROVED' => 'Disetujui Finance', 'REJECTED' => 'Ditolak'];

    public static function decisionLabel(string $decision): string
    {
        return self::DECISIONS[$decision] ?? ($decision === 'REVISION' ? 'Perlu Revisi (keputusan lama)' : $decision);
    }

    public static function awaitingResubmission(Model $source): bool
    {
        return $source->status !== 'VALIDATED'
            && (self::isEvent($source) || $source->finance_correction_check_id !== null);
    }

    public static function isEvent(Model $source): bool
    {
        return $source instanceof PerongkosanEventDalamKota || $source instanceof PerongkosanEventLuarKota;
    }

    public function source(string $type, int $id, bool $lock = false): Model
    {
        abort_unless(isset(self::TYPES[$type]), 404);
        $query = self::TYPES[$type]::query();
        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->findOrFail($id);
    }

    public function snapshot(Model $source, bool $lock = false): array
    {
        if (self::isEvent($source)) {
            $type = $source instanceof PerongkosanEventDalamKota ? 'EDK' : 'ELK';
            $items = collect([[
                'cost_category_id' => null, 'cost_category_name' => self::LABELS[$type],
                'nominal_asal' => Money::decimal(Money::minor($source->nominal)),
            ]]);
            $fingerprint = hash('sha256', json_encode($source->getAttributes(), JSON_THROW_ON_ERROR));

            return compact('items', 'fingerprint');
        }
        $query = $source->details()->with('costCategory')->orderBy('id');
        if ($lock) {
            $query->lockForUpdate();
        }
        $details = $query->get();
        $items = $details->groupBy('cost_category_id')->map(function (Collection $rows, $categoryId) {
            $minor = $rows->sum(fn ($row) => Money::minor($row->nominal));
            if ($minor < 0 || $minor > 999999999999999) {
                throw ValidationException::withMessages(['source' => 'Total kategori sumber di luar batas nominal yang dapat diperiksa.']);
            }

            return [
                'cost_category_id' => (int) $categoryId,
                'cost_category_name' => $rows->first()->costCategory?->nama ?? 'Kategori #'.$categoryId,
                'nominal_asal' => Money::decimal($minor),
            ];
        })->sortKeys()->values();

        $fingerprint = hash('sha256', json_encode([
            'header' => [$source->getKey(), $source->transaksi_id, $source->user_id, $source->tanggal, $source->status, $source->description, (int) $source->submission_version],
            'details' => $details->map(fn ($row) => [$row->id, $row->cost_category_id, Money::decimal(Money::minor($row->nominal)), $row->remark])->all(),
        ], JSON_THROW_ON_ERROR));

        return compact('items', 'fingerprint');
    }

    public function token(string $type, int $id, string $fingerprint): string
    {
        return Crypt::encryptString(json_encode([$type, $id, $fingerprint], JSON_THROW_ON_ERROR));
    }

    public function reviewToken(string $type, int $id, ?BsUsageCheck $latest, ?BsUsageCheckDraft $draft): string
    {
        return Crypt::encryptString(json_encode([$type, $id, $latest?->id, $draft?->id, $draft?->version], JSON_THROW_ON_ERROR));
    }

    public function store(User $checker, string $type, int $id, array $data): BsUsageCheck|BsUsageCheckDraft
    {
        abort_unless($checker->can('akses.approval-finance'), 403);
        $data['save_action'] = $data['save_action'] ?? 'confirm';
        $explicitDecision = array_key_exists('keputusan', $data);
        if (!$explicitDecision) $data['keputusan'] = 'APPROVED';
        $isDraft = $data['save_action'] === 'draft';
        $data = validator($data, [
            'save_action' => 'required|in:draft,confirm',
            'tanggal_pengecekan' => 'required|date_format:Y-m-d',
            'source_token' => 'required|string|max:4096',
            'review_token' => 'nullable|required_if:save_action,draft|string|max:4096',
            'keputusan' => 'required|in:APPROVED,REJECTED',
            'catatan_keputusan' => 'nullable|'.($isDraft ? '' : 'required_if:keputusan,REJECTED|').'string|max:2000',
            'details' => 'required|array|min:1|max:1000',
            'details.*' => 'required|array:cost_category_id,nominal_disetujui,catatan',
            'details.*.cost_category_id' => 'present|nullable|integer|distinct',
            'details.*.nominal_disetujui' => [$isDraft ? 'present' : 'required', 'nullable', 'numeric', 'min:0', 'max:9999999999999.99', 'regex:/^\d{1,13}(?:\.\d{1,2})?$/D'],
            'details.*.catatan' => 'nullable|string|max:2000',
        ])->validate();
        try {
            $expected = json_decode(Crypt::decryptString($data['source_token']), true, flags: JSON_THROW_ON_ERROR);
        } catch (DecryptException|\JsonException $exception) {
            throw ValidationException::withMessages(['source_token' => 'Referensi pengecekan tidak valid. Buka kembali form dari daftar transaksi.']);
        }

        return DB::transaction(function () use ($checker, $type, $id, $data, $expected, $isDraft, $explicitDecision) {
            $source = $this->source($type, $id, true);
            $latest = BsUsageCheck::where('jenis_transaksi', $type)->where('transaksi_pemakaian_id', $id)->latest('id')->first();
            $draft = BsUsageCheckDraft::where('jenis_transaksi', $type)->where('transaksi_pemakaian_id', $id)->lockForUpdate()->first();
            if ($draft && !isset(self::DECISIONS[$draft->keputusan]) && !$explicitDecision) {
                throw ValidationException::withMessages(['keputusan' => 'Pilih Disetujui Finance atau Ditolak untuk melanjutkan draft lama.']);
            }
            // Lock the source first on every write; versioning also catches stale browser tabs.
            $context = null;
            if (!empty($data['review_token'])) {
                try {
                    $context = json_decode(Crypt::decryptString($data['review_token']), true, flags: JSON_THROW_ON_ERROR);
                } catch (DecryptException|\JsonException $exception) {
                    throw ValidationException::withMessages(['review_token' => 'Form pemeriksaan tidak valid. Buka kembali pemeriksaan dari daftar Finance.']);
                }
            }
            if ((!empty($data['review_token']) && $context !== [$type, $id, $latest?->id, $draft?->id, $draft?->version])
                || ($draft && empty($data['review_token']))) {
                throw ValidationException::withMessages(['review_token' => 'Pemeriksaan sudah diperbarui atau dikonfirmasi. Muat ulang untuk melanjutkan versi terbaru.']);
            }
            if (self::isEvent($source) && $source->status !== 'VALIDATED') {
                throw ValidationException::withMessages(['source' => 'Event harus dikonfirmasi oleh pemilik transaksi terlebih dahulu.']);
            }
            if (self::awaitingResubmission($source)) {
                throw ValidationException::withMessages(['source' => 'Perbaikan pengajuan harus dikonfirmasi kembali sebelum diperiksa Finance.']);
            }
            $snapshot = $this->snapshot($source, true);
            if ($expected !== [$type, $id, $snapshot['fingerprint']]) {
                throw ValidationException::withMessages(['source_token' => 'Transaksi sumber berubah. Muat ulang form dan periksa kembali rincian sebelum menyimpan.']);
            }
            $items = $snapshot['items'];
            $submitted = collect($data['details']);
            $expectedIds = $items->pluck('cost_category_id')->map(fn ($value) => (int) $value)->sort()->values()->all();
            $submittedIds = $submitted->pluck('cost_category_id')->map(fn ($value) => (int) $value)->sort()->values()->all();
            if ($items->isEmpty() || $expectedIds !== $submittedIds) {
                throw ValidationException::withMessages(['details' => 'Rincian harus memuat seluruh kategori sumber, tanpa kategori tambahan atau duplikat.']);
            }

            $source->load('user');
            $attributes = [
                'tanggal_pengecekan' => $data['tanggal_pengecekan'],
                'jenis_transaksi' => $type,
                'transaksi_pemakaian_id' => $source->getKey(),
                'no_referensi' => $source->transaksi_id ?: $type.' #'.$source->getKey(),
                'user_id' => $source->user_id,
                'user_checker_id' => $checker->id,
                'user_name' => $source->user->nama,
                'checker_name' => $checker->nama,
                'periode' => app(PeriodService::class)->ofDate($data['tanggal_pengecekan']),
                'source_fingerprint' => $snapshot['fingerprint'],
                'keputusan' => $data['keputusan'],
                'catatan_keputusan' => $data['catatan_keputusan'] ?? null,
                'revision_of_id' => $latest?->id,
            ];
            $submitted = $submitted->keyBy(fn ($input) => (int) $input['cost_category_id']);
            $details = $items->map(function ($item) use ($submitted, $data) {
                $input = $submitted->get((int) $item['cost_category_id']);
                return $item + [
                    'nominal_disetujui' => $data['keputusan'] === 'REJECTED' ? '0.00'
                        : ($input['nominal_disetujui'] === null ? null : Money::decimal(Money::minor($input['nominal_disetujui']))),
                    'catatan' => $input['catatan'] ?? null,
                ];
            });
            if ($isDraft) {
                $draft ??= new BsUsageCheckDraft();
                $draft->fill($attributes + ['details' => $details->all(), 'version' => ($draft->version ?? 0) + 1])->save();

                return $draft;
            }
            $check = BsUsageCheck::create($attributes + [
                'document_no' => app(DocumentNumberService::class)->next('CEKBS', $data['tanggal_pengecekan']),
                'source_revision' => (int) $source->submission_version,
            ]);
            $check->details()->createMany($details->all());
            // The draft is consumed only after the complete immutable document was saved.
            $draft?->delete();

            return $check;
        }, 3);
    }
}
