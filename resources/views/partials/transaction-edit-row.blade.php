@php
    $rowCategories = $cost_categories;
    if ($existingId && isset($originalCategory) && ! $originalCategory->is_active) {
        $rowCategories = $rowCategories->concat([$originalCategory]);
    }
@endphp
<tr data-expense-row @if($existingId) data-existing-id="{{ $existingId }}" @endif class="{{ $deleted ? 'opacity-50' : '' }}">
    <td>
        <select name="{{ $prefix }}[cost_category_id]" class="form-select cat-select" required @disabled($deleted) aria-label="Kategori biaya">
            <option value="">-- Pilih Kategori Biaya --</option>
            @foreach($rowCategories as $category)
                @php
                    $categoryLabel = $category->name ?? $category->nama ?? $category->description ?? $category->code;
                    $categoryName = strtolower($categoryLabel ?? '');
                    $categoryType = str_contains($categoryName, 'lumpsum') || str_contains($categoryName, 'uang saku') ? 'lumpsum'
                        : (str_contains($categoryName, 'hotel') || str_contains($categoryName, 'akomodasi') ? 'hotel' : 'umum');
                @endphp
                <option value="{{ $category->id }}" data-type="{{ $categoryType }}" @selected((string) ($values['cost_category_id'] ?? '') === (string) $category->id)>{{ $categoryLabel }}{{ $category->is_active ? '' : ' (Nonaktif - tersimpan)' }}</option>
            @endforeach
        </select>
    </td>
    <td><input type="text" name="{{ $prefix }}[remark]" value="{{ $values['remark'] ?? '' }}" class="form-control" maxlength="255" @required($requireRemark) @disabled($deleted) aria-label="Keterangan biaya"></td>
    <td>@include('partials.transaction-photo-input', ['photoPrefix' => $prefix, 'photoDetail' => $photoDetail, 'photoDisabled' => $deleted])</td>
    <td><input type="number" name="{{ $prefix }}[nominal]" value="{{ $values['nominal'] ?? '' }}" class="form-control" inputmode="decimal" min="0" max="9999999999999.99" step="0.01" required @disabled($deleted) aria-label="Nominal biaya dalam rupiah" data-expense-amount></td>
    <td>
        @if($existingId)
            <input type="checkbox" name="delete_details[]" value="{{ $existingId }}" hidden @checked($deleted) data-delete-expense>
        @endif
        <button type="button" class="btn {{ $deleted ? 'btn-outline-secondary' : 'btn-outline-danger' }}" data-remove-expense>{{ $deleted ? 'Batalkan Hapus' : 'Hapus' }}</button>
    </td>
</tr>
