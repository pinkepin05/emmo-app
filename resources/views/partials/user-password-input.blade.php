@php($editingPassword = $editingPassword ?? false)

@if($editingPassword)
    <div class="mb-3" data-stored-password>
        <label class="form-label" for="master-user-stored-password">Password Tersimpan</label>
        @if($item->password_encrypted)
            <div class="input-group">
                <input type="password" id="master-user-stored-password" class="form-control" readonly
                    placeholder="••••••••" autocomplete="off" aria-describedby="stored-password-feedback">
                <button type="button" class="btn btn-outline-primary" style="min-width: 44px;"
                    data-stored-password-toggle data-reveal-url="{{ route('users.password.reveal', $item->id) }}"
                    aria-controls="master-user-stored-password" aria-pressed="false"
                    aria-label="Lihat password tersimpan">Lihat</button>
            </div>
            <div id="stored-password-feedback" class="form-text" role="status" aria-live="polite">
                Tekan Lihat untuk menampilkan password yang tersimpan.
            </div>
        @else
            <div id="master-user-stored-password" class="form-text mt-0">
                Password lama belum tersedia untuk ditampilkan. Isi password baru di bawah lalu simpan sekali agar selanjutnya bisa dilihat di sini.
            </div>
        @endif
    </div>
@endif

<label class="form-label" for="master-user-password">{{ $editingPassword ? 'Password Baru' : 'Password' }}</label>
<div class="input-group has-validation">
    <input
        type="password"
        id="master-user-password"
        name="password"
        minlength="6"
        class="form-control @error('password') is-invalid @enderror"
        autocomplete="new-password"
        spellcheck="false"
        autocapitalize="none"
        @if($editingPassword)
            placeholder="Kosongkan jika tidak diubah"
            aria-describedby="master-user-password-help"
        @else
            required
        @endif
        @error('password') aria-invalid="true" @enderror
    >
    <button
        type="button"
        class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center"
        style="min-width: 44px;"
        data-password-toggle="master-user-password"
        aria-controls="master-user-password"
        aria-label="Tampilkan password"
        aria-pressed="false"
        title="Tampilkan password"
    >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" />
            <circle cx="12" cy="12" r="3" />
            <path data-password-slash d="M3 3 21 21" style="display: none;" />
        </svg>
    </button>
    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
@if($editingPassword)
    <div id="master-user-password-help" class="form-text">
        Minimal 6 karakter. Kosongkan jika password tidak diubah.
    </div>
@endif

@once
    <script src="{{ asset('js/user-password-toggle.js') }}?v={{ filemtime(public_path('js/user-password-toggle.js')) }}" defer></script>
@endonce
