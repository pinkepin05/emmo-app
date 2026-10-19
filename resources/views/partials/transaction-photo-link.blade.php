@if($photoUrl)
    <a href="{{ $photoUrl }}" target="_blank" rel="noopener" class="expense-photo-link" data-photo-open>
        <img src="{{ $photoUrl }}" alt="Foto bukti biaya" loading="lazy" width="72" height="54">
        <span>Lihat foto</span>
    </a>
@endif
