@php
    $photoKey = str_replace(['[', ']'], ['.', ''], $photoPrefix);
    $photoDisabled = $photoDisabled ?? false;
    $existingPhoto = $photoDetail?->photo_url ?? null;
    $hasError = $errors->has($photoKey . '.photo');
@endphp

<div class="expense-photo" data-photo-input>
    <label class="expense-photo-upload">
        <input type="file"
               name="{{ $photoPrefix }}[photo]"
               class="expense-photo-input @if($hasError) is-invalid @endif"
               accept="image/jpeg,image/png,image/webp"
               aria-label="{{ $existingPhoto ? 'Ganti foto bukti biaya' : 'Unggah foto bukti biaya' }}"
               data-expense-photo
               @disabled($photoDisabled)>
        <i class="bi bi-camera" aria-hidden="true"></i>
        <span>{{ $existingPhoto ? 'Ganti foto' : 'Pilih foto' }}</span>
        <i class="bi bi-upload expense-photo-upload-icon" aria-hidden="true"></i>
    </label>
    <small class="expense-photo-help">JPG, PNG, WebP &middot; Maks. 2 MB</small>

    @if($existingPhoto)
        <div class="expense-photo-saved expense-photo-card {{ old($photoKey.'.remove_photo') ? 'is-removing' : '' }}" data-existing-photo>
            <a href="{{ $existingPhoto }}" target="_blank" rel="noopener" class="expense-photo-link" data-photo-open data-photo-title="{{ $photoDetail->photo_name ?: 'Foto bukti biaya' }}">
                <img src="{{ $existingPhoto }}" alt="Perbesar foto bukti tersimpan" width="56" height="56" loading="lazy">
            </a>
            <div class="expense-photo-meta">
                <small class="expense-photo-state">Foto tersimpan</small>
                <span class="expense-photo-filename" title="{{ $photoDetail->photo_name ?: 'Foto bukti biaya' }}">{{ $photoDetail->photo_name ?: 'Foto bukti biaya' }}</span>
                <div class="expense-photo-card-actions">
                    <a href="{{ $existingPhoto }}" target="_blank" rel="noopener" class="expense-photo-action" data-photo-open data-photo-title="{{ $photoDetail->photo_name ?: 'Foto bukti biaya' }}">Perbesar</a>
                    <label class="expense-photo-remove" title="Hapus foto tersimpan saat menyimpan perubahan">
                        <input type="checkbox" name="{{ $photoPrefix }}[remove_photo]" value="1" data-remove-photo
                               @checked(old($photoKey.'.remove_photo')) @disabled($photoDisabled)>
                        <span>Hapus<span class="visually-hidden"> foto tersimpan</span></span>
                    </label>
                </div>
            </div>
        </div>
    @endif

    <div class="expense-photo-preview expense-photo-card" data-photo-preview hidden aria-live="polite">
        <a target="_blank" rel="noopener" class="expense-photo-link" data-photo-open>
            <img alt="Perbesar foto baru yang dipilih" width="56" height="56">
        </a>
        <div class="expense-photo-meta">
            <small class="expense-photo-state is-new">Foto baru dipilih</small>
            <span class="expense-photo-filename" data-photo-filename></span>
            <div class="expense-photo-card-actions">
                <a target="_blank" rel="noopener" class="expense-photo-action" data-photo-open>Perbesar</a>
                <button type="button" class="expense-photo-cancel" data-clear-photo aria-label="Batalkan pilihan foto" @disabled($photoDisabled)>Batal</button>
            </div>
        </div>
    </div>

    <div class="expense-photo-error" data-photo-error role="alert" @if(!$hasError) hidden @endif>{{ $hasError ? $errors->first($photoKey . '.photo') : '' }}</div>
</div>
