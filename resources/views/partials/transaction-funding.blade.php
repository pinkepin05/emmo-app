@php
    $fundingDocument = $fundingDocument ?? null;
    $fundingCompact = $fundingCompact ?? false;
    $fundingInputter = (int) ($fundingDocument?->user_id ?? auth()->id());
    $fundingOwner = (int) old('bs_owner_id', $fundingDocument?->bsCashOwnerId() ?? $fundingInputter);
    $fundingDate = old('tanggal', $fundingDocument?->tanggal ?? \App\Support\AppTime::now()->toDateString());
    $fundingPeriod = is_string($fundingDate) && preg_match('/^\d{4}-(0[1-9]|1[0-2])-/', $fundingDate)
        ? substr($fundingDate, 0, 7) : app(\App\Services\PeriodService::class)->current();
    $fundingOwners = app(\App\Services\TransactionFundingService::class)->owners($fundingInputter, $fundingType === 'perjalanan_dinas');
    $fundingCash = app(\App\Services\BsCashFlowService::class);
    $fundingAllowed = $fundingOwners->contains('id', $fundingOwner);
    $fundingBalance = $fundingAllowed ? $fundingCash->calculate($fundingOwner, $fundingPeriod) : null;
    $fundingAvailable = $fundingBalance ? $fundingCash->availableBalance($fundingBalance) : null;
@endphp
<div id="funding-bs-fields" data-balance-url="{{ route('bs-balance.balances') }}" data-module="{{ $fundingType }}" data-transaction="{{ $fundingDocument?->id }}" data-inputter="{{ $fundingInputter }}" data-period="{{ $fundingPeriod }}">
    @if($fundingType === 'perjalanan_dinas')
        <label for="bs_owner_id_select" class="form-label fw-semibold">Pemilik Saldo BS</label>
        <select name="bs_owner_id" id="bs_owner_id_select" class="form-select" required>
            @foreach($fundingOwners as $owner)
                <option value="{{ $owner->id }}" @selected($fundingOwner === (int) $owner->id)>
                    {{ (int) $owner->id === $fundingInputter ? 'Saldo sendiri' : $owner->nama.' (Grup Dinas)' }}
                </option>
            @endforeach
        </select>
        @if(!$fundingCompact)
            <p class="small text-muted mt-2">Anggota grup dapat mengajukan pemakaian saldo pemohon untuk Perjalanan Dinas. Pemohon menyetujui rincian terlebih dahulu; saldo baru berkurang setelah konfirmasi Finance.</p>
        @endif
    @endif
    <div id="bs-saldo-info" class="mt-2" data-balance="{{ $fundingAvailable }}">
        Sisa Saldo BS: <strong id="bs-balance-display">{{ $fundingAvailable === null ? 'Tidak tersedia' : 'Rp '.number_format($fundingAvailable, 2, ',', '.') }}</strong>
    </div>
    <p id="bs-opening-info" class="small text-warning mt-2" @if($fundingBalance && $fundingBalance['opening_final']) hidden @endif>Saldo bulan sebelumnya tersedia setelah tutup buku.</p>
    @if(!$fundingCompact)
        <p class="small text-muted mt-2">Operasional Sales, Perjalanan Dinas, dan event memakai satu saldo BS. Saldo berkurang sesuai nominal yang dikonfirmasi Finance. {{ $fundingType === 'perjalanan_dinas' ? 'Pemakaian saldo pemohon memerlukan persetujuan pemohon sebelum pengecekan Finance.' : '' }}</p>
    @endif
</div>
<script src="{{ asset('js/bs-balance.js') }}"></script>
