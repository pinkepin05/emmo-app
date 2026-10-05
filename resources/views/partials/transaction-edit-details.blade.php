@php
    $deletedIds = array_map('strval', old('delete_details', []));
    $newDetails = old('new_details', []);
@endphp
<div class="card-modern">
    <div class="card-header-modern">
        <h5><i class="bi bi-list-ul me-2"></i>Edit Rincian Biaya</h5>
        <button type="button" class="btn btn-success" data-add-expense>Tambah Rincian Biaya</button>
    </div>
    <div class="card-body p-0">
        <p class="text-muted small p-3 mb-0">Ubah, tambah, atau tandai rincian untuk dihapus. Semua perubahan disimpan setelah Anda menekan Simpan Perubahan.</p>
        <div class="table-responsive">
            <table class="table-modern" data-mobile-entry>
                <thead><tr><th>Kategori Biaya</th><th>Keterangan</th><th>Nominal (Rp)</th><th>Aksi</th></tr></thead>
                <tbody data-expense-rows>
                    @foreach($transaksi->details as $detail)
                        @include('partials.transaction-edit-row', [
                            'prefix' => 'details['.$detail->id.']', 'existingId' => $detail->id,
                            'values' => old('details.'.$detail->id, $detail->toArray()),
                            'deleted' => in_array((string) $detail->id, $deletedIds, true),
                        ])
                    @endforeach
                    @foreach($newDetails as $key => $values)
                        @include('partials.transaction-edit-row', ['prefix' => 'new_details['.$key.']', 'existingId' => null, 'deleted' => false])
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3 text-end fw-bold">Total rincian setelah perubahan: <output data-expense-total aria-live="polite"></output></div>
    </div>
</div>
<template data-expense-template>
    @include('partials.transaction-edit-row', ['prefix' => 'new_details[__INDEX__]', 'existingId' => null, 'deleted' => false, 'values' => []])
</template>
<div class="d-flex flex-wrap gap-2 mb-4">
    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    <a href="{{ $cancelUrl }}" class="btn btn-outline-secondary">Batal</a>
</div>
