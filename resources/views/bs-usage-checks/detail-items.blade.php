@php
    $showInternalNotes = ($showInternalNotes ?? true) && auth()->user()->can('akses.approval-finance');
    $originalMinor = $check->details->sum(fn ($item) => \App\Support\Money::minor($item->nominal_asal));
    $approvedMinor = $check->details->sum(fn ($item) => \App\Support\Money::minor($item->nominal_disetujui));
    $resultMoney = fn ($minor) => number_format(\App\Support\Money::decimal($minor), 2, ',', '.');
@endphp
@if(in_array($check->jenis_transaksi, ['OPR', 'PD']) && $check->details->every(fn ($item) => $item->source_detail_id === null))
    <p class="alert alert-info small">Dokumen lama ini menyimpan gabungan per kategori. Nominal historis tetap dipertahankan; pemeriksaan baru dilakukan per rincian biaya.</p>
@endif
<div class="table-responsive">
    <table class="table check-table">
        <thead><tr>
            <th scope="col">Item</th><th scope="col">Keterangan</th>
            <th scope="col" class="check-money">Nominal Asal (Rp)</th>
            <th scope="col" class="check-money">Nominal Disetujui (Rp)</th>
            <th scope="col" class="check-money">Selisih (Rp)</th>
            @if($showInternalNotes)<th scope="col">Catatan internal Finance</th>@endif
        </tr></thead>
        <tbody>
            @foreach($check->details as $item)
                <tr>
                    <td>{{ $item->cost_category_name }}</td>
                    <td class="text-break">{{ $item->source_remark ?: '—' }}
                        @if($showInternalNotes && $item->source_photo_path)
                            <div>@include('partials.transaction-photo-link', ['photoUrl' => route('transaction-detail-photo.history', $item->id)])</div>
                        @endif
                    </td>
                    <td class="check-money">{{ number_format($item->nominal_asal, 2, ',', '.') }}</td>
                    <td class="check-money">{{ number_format($item->nominal_disetujui, 2, ',', '.') }}</td>
                    <td class="check-money {{ $item->selisih !== '0.00' ? 'text-danger' : '' }}">{{ number_format($item->selisih, 2, ',', '.') }}</td>
                    @if($showInternalNotes)<td class="check-note">{{ $item->catatan ?: '—' }}</td>@endif
                </tr>
            @endforeach
        </tbody>
        <tfoot><tr class="fw-bold">
            <td colspan="2">Total</td>
            <td class="check-money">{{ $resultMoney($originalMinor) }}</td>
            <td class="check-money">{{ $resultMoney($approvedMinor) }}</td>
            <td class="check-money">{{ $resultMoney($originalMinor - $approvedMinor) }}</td>
            @if($showInternalNotes)<td></td>@endif
        </tr></tfoot>
    </table>
</div>
