<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 1000px;">
        <div class="card shadow">
           <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Form Tambah Master User</h4>
                <a href="{{ route('dashboard.master_data') }}" class="btn btn-sm btn-light fw-bold text-primary">← Kembali ke Dashboard</a>
            </div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h5 class="fw-bold">Gagal Menyimpan Data User:</h5>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('users.store') }}" method="POST">
                    @csrf 

                    <div class="row">
                       <div class="col-md-6 mb-3">
                            <label class="form-label">NIK</label>
                            <input type="text" 
                                   name="NIK" 
                                   class="form-control @error('NIK') is-invalid @enderror" 
                                   value="{{ old('NIK') }}" 
                                   placeholder="Masukkan NIK (Maks 20 Karakter)"
                                   maxlength="20">
                            @error('NIK')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" required>
                            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row">
                       <div class="col-md-6 mb-3">
                            <label class="form-label">Company</label>
                            <select name="company_id" class="form-select @error('company_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Company --</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ $company->nama ?? $company->code }} </option>
                                @endforeach
                            </select>
                            @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Roles</label>
                            <select name="roles_id" class="form-select @error('roles_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Roles --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('roles_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->nama ?? $role->code }} </option>
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
                                    <option value="{{ $level->id }}" {{ old('level_id') == $level->id ? 'selected' : '' }}>
                                        {{ $level->nama ?? $level->code }} </option>
                                @endforeach
                            </select>
                            @error('level_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Position</label>
                            <select name="position_id" class="form-select @error('position_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Position --</option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->id }}" {{ old('position_id') == $position->id ? 'selected' : '' }}>
                                        {{ $position->nama ?? $position->code }} </option>
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
                                @else
                                    <option value="">-- Ketik min 2 huruf untuk mencari wilayah --</option>
                                @endif
                            </select>
                            @error('homebase_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Atasan User</label>
                            <select name="atasan_user_id" class="form-select @error('atasan_user_id') is-invalid @enderror">
                                <option value="">-- Pilih Atasan (Opsional) --</option>
                                @foreach($atasans as $atasan)
                                    <option value="{{ $atasan->id }}" {{ old('atasan_user_id') == $atasan->id ? 'selected' : '' }}>{{ $atasan->nama }}</option>
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
                                <option value="5 HK" {{ old('hari_kerja_user') == '5 HK' ? 'selected' : '' }}>5 Hari Kerja</option>
                                <option value="6 HK" {{ old('hari_kerja_user') == '6 HK' ? 'selected' : '' }}>6 Hari Kerja</option>
                            </select>
                            @error('hari_kerja_user')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" required>
                            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kost</label>
                            <input type="number" name="kost" class="form-control @error('kost') is-invalid @enderror" value="{{ old('kost') }}">
                            @error('kost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">BBM</label>
                            <input type="number" name="bbm" class="form-control @error('bbm') is-invalid @enderror" value="{{ old('bbm') }}">
                            @error('bbm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Uang Makan</label>
                            <input type="number" name="uang_makan" class="form-control @error('uang_makan') is-invalid @enderror" value="{{ old('uang_makan') }}">
                            @error('uang_makan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
                        <label class="form-check-label" for="is_active">Set Aktif</label>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Simpan Data User</button>
                </form>

                <hr class="my-5">

                <h5>Daftar Master User</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mt-3">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>NIK</th>
                                <th>Nama</th>
                                <th>Company</th>
                                <th>Level</th>
                                <th>Position</th>
                                <th>Username</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->NIK ?? '-' }}</td>
                                    <td>{{ $item->nama }}</td>
                                    <td>{{ $item->company->code ?? '-' }}</td>
                                    <td>{{ $item->level->code ?? '-' }}</td>
                                    <td>{{ $item->position->code ?? '-' }}</td>
                                    <td>{{ $item->username }}</td>
                                    <td><span class="badge {{ $item->is_active ? 'bg-success' : 'bg-danger' }}">{{ $item->is_active ? 'Aktif' : 'Non-Aktif' }}</span></td>
                                    <td>
                                        <a href="{{ route('users.edit', $item->id) }}" class="btn btn-sm btn-primary mb-1">Edit</a>
                                        <form action="{{ route('users.destroy', $item->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Inisialisasi Select2 untuk dropdown wilayah
    $('#wilayah-select').select2({
        placeholder: 'Cari wilayah...',
        minimumInputLength: 2,
        allowClear: true,
        width: '100%',
        ajax: {
            url: '/api/wilayah-search',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term
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
                return 'Ketik minimal 2 huruf...';
            },
            searching: function() {
                return 'Sedang mencari...';
            },
            noResults: function() {
                return 'Wilayah tidak ditemukan';
            },
            errorLoading: function() {
                return 'Gagal memuat data';
            }
        }
    });
});
</script>
</body>
</html>
