<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Master User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 900px;">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Form Edit Master User</h4>
            </div>
            <div class="card-body">

                <form action="{{ route('users.update', $item->id) }}" method="POST">
                    @csrf 
                    @method('PUT') 

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIK</label>
                            <input type="text" name="NIK" class="form-control @error('NIK') is-invalid @enderror" value="{{ old('NIK', $item->NIK) }}" maxlength="20">
                            @error('NIK')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama</label>
                            <!-- Diperbaiki: name="nam" diganti name="nama" -->
                            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $item->nama) }}" required>
                            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company</label>
                            <select name="company_id" class="form-select @error('company_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Company --</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id', $item->company_id) == $company->id ? 'selected' : '' }}>{{ $company->nama ?? $company->id }}</option>
                                @endforeach
                            </select>
                            @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Roles</label>
                            <select name="roles_id" class="form-select @error('roles_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Roles --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('roles_id', $item->roles_id) == $role->id ? 'selected' : '' }}>{{ $role->name ?? $role->code }}</option>
                                @endforeach
                            </select>
                            @error('roles_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Level</label>
                            <select name="level_id" class="form-select @error('level_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Level --</option>
                                @foreach($levels as $level)
                                    <option value="{{ $level->id }}" {{ old('level_id', $item->level_id) == $level->id ? 'selected' : '' }}>{{ $level->nama ?? $level->id }}</option>
                                @endforeach
                            </select>
                            @error('level_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Position</label>
                            <select name="position_id" class="form-select @error('position_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Position --</option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->id }}" {{ old('position_id', $item->position_id) == $position->id ? 'selected' : '' }}>{{ $position->nama ?? $position->id }}</option>
                                @endforeach
                            </select>
                            @error('position_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
    <label class="form-label">Homebase (Wilayah)</label>
    <select name="homebase_id" id="wilayah-select" class="form-select @error('homebase_id') is-invalid @enderror" required>
        @if(old('homebase_id'))
            @php
                $wilayah = \App\Models\Wilayah::find(old('homebase_id'));
            @endphp
            @if($wilayah)
                <option value="{{ $wilayah->id }}" selected>{{ $wilayah->nama }}</option>
            @endif
        @elseif(isset($item) && $item->homebase_id)
            @php
                $wilayah = \App\Models\Wilayah::find($item->homebase_id);
            @endphp
            @if($wilayah)
                <option value="{{ $wilayah->id }}" selected>{{ $wilayah->nama }}</option>
            @endif
        @else
            <option value="">-- Ketik minimal 2 huruf untuk mencari wilayah --</option>
        @endif
    </select>
    @error('homebase_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Atasan User</label>
                            <select name="atasan_user_id" class="form-select @error('atasan_user_id') is-invalid @enderror">
                                <option value="">-- Pilih Atasan (Opsional) --</option>
                                @foreach($atasans as $atasan)
                                    <!-- Diperbaiki: $atasan->nam diganti $atasan->nama -->
                                    <option value="{{ $atasan->id }}" {{ old('atasan_user_id', $item->atasan_user_id) == $atasan->id ? 'selected' : '' }}>{{ $atasan->nama }}</option>
                                @endforeach
                            </select>
                            @error('atasan_user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Hari Kerja</label>
                            <select name="hari_kerja_user" class="form-select @error('hari_kerja_user') is-invalid @enderror">
                                <option value="">-- Pilih Hari Kerja --</option>
                                <option value="5 HK" {{ old('hari_kerja_user', $item->hari_kerja_user) == '5 HK' ? 'selected' : '' }}>5 Hari Kerja</option>
                                <option value="6 HK" {{ old('hari_kerja_user', $item->hari_kerja_user) == '6 HK' ? 'selected' : '' }}>6 Hari Kerja</option>
                            </select>
                            @error('hari_kerja_user')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $item->username) }}" required>
                            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Kosongkan jika tidak diubah">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kost</label>
                            <input type="number" name="kost" class="form-control @error('kost') is-invalid @enderror" value="{{ old('kost', $item->kost) }}">
                            @error('kost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">BBM</label>
                            <input type="number" name="bbm" class="form-control @error('bbm') is-invalid @enderror" value="{{ old('bbm', $item->bbm) }}">
                            @error('bbm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Uang Makan</label>
                            <input type="number" name="uang_makan" class="form-control @error('uang_makan') is-invalid @enderror" value="{{ old('uang_makan', $item->uang_makan) }}">
                            @error('uang_makan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ $item->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Set Aktif</label>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('users.create') }}" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Update Data User</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
<!-- jQuery (wajib untuk Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Inisialisasi Select2 untuk dropdown wilayah
    $('#wilayah-select').select2({
        placeholder: 'Cari wilayah...',
        minimumInputLength: 2, // Minimal 2 huruf sebelum search
        ajax: {
            url: '/api/wilayah-search',
            dataType: 'json',
            delay: 250, // Delay 250ms sebelum kirim request
            data: function (params) {
                return {
                    q: params.term, // Search term
                    _token: '{{ csrf_token() }}' // CSRF token
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true
        },
        language: {
            inputTooShort: function() {
                return 'Ketik minimal 2 huruf untuk mencari...';
            },
            searching: function() {
                return 'Sedang mencari...';
            },
            noResults: function() {
                return 'Wilayah tidak ditemukan';
            }
        }
    });
});
</script>

</body>
</html>
