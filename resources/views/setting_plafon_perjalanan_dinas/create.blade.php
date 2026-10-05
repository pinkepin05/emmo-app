<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Setting Plafon Perjalanan Dinas</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @include('partials.mobile-assets')
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 900px;">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Form Tambah Master Setting Plafon Perjalanan Dinas</h4>
                
                <a href="{{ route(auth()->user()->dashboardRouteName()) }}" class="btn btn-sm btn-light fw-bold text-primary">
                    ← Kembali ke Dashboard
                </a>
            </div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form action="{{ route('setting_plafon_perjalanan_dinas.store') }}" method="POST">
                    @csrf 

                    <!-- Ganti bagian dropdown Provinsi Awal & Tujuan dengan ini: -->
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Territory Awal <span class="text-danger">*</span></label>
        <select name="teritory_awal" id="teritory-awal-select" class="form-select @error('teritory_awal') is-invalid @enderror" required>
            <option value="">-- Pilih Territory --</option>
            <option value="Jawa" {{ old('teritory_awal') == 'Jawa' ? 'selected' : '' }}>Jawa</option>
            <option value="Luar Jawa" {{ old('teritory_awal') == 'Luar Jawa' ? 'selected' : '' }}>Luar Jawa</option>
        </select>
        @error('teritory_awal')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Provinsi Awal <span class="text-danger">*</span></label>
        <select name="provinsi_awal_id" id="provinsi-awal-select" class="form-select @error('provinsi_awal_id') is-invalid @enderror" required disabled>
            <option value="">-- Pilih Territory Terlebih Dahulu --</option>
        </select>
        @error('provinsi_awal_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Territory Tujuan <span class="text-danger">*</span></label>
        <select name="teritory_tujuan" id="teritory-tujuan-select" class="form-select @error('teritory_tujuan') is-invalid @enderror" required>
            <option value="">-- Pilih Territory --</option>
            <option value="Jawa" {{ old('teritory_tujuan') == 'Jawa' ? 'selected' : '' }}>Jawa</option>
            <option value="Luar Jawa" {{ old('teritory_tujuan') == 'Luar Jawa' ? 'selected' : '' }}>Luar Jawa</option>
        </select>
        @error('teritory_tujuan')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Provinsi Tujuan <span class="text-danger">*</span></label>
        <select name="provinsi_tujuan_id" id="provinsi-tujuan-select" class="form-select @error('provinsi_tujuan_id') is-invalid @enderror" required disabled>
            <option value="">-- Pilih Territory Terlebih Dahulu --</option>
        </select>
        @error('provinsi_tujuan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>



                    <div class="mb-3">
                        <label class="form-label">Level</label>
                        <select name="level_id" class="form-select @error('level_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Level --</option>
                            @foreach($levels as $level)
                                <option value="{{ $level->id }}" {{ old('level_id') == $level->id ? 'selected' : '' }}>
                                    {{ $level->name ?? $level->nama ?? $level->code }}
                                </option>
                            @endforeach
                        </select>
                        @error('level_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <input type="text" name="description" class="form-control @error('description') is-invalid @enderror" value="{{ old('description') }}" required>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Default Value (Nominal)</label>
                            <input type="number" name="default_value" class="form-control @error('default_value') is-invalid @enderror" value="{{ old('default_value', 1) }}">
                            @error('default_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Berlaku</label>
                            <input type="date" name="tanggal_berlaku" class="form-control @error('tanggal_berlaku') is-invalid @enderror" value="{{ old('tanggal_berlaku') }}">
                            @error('tanggal_berlaku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 fw-bold">Simpan Data Plafon</button>
                </form>

                <hr class="my-5">

                <h5>Daftar Setting Plafon Perjalanan Dinas</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mt-3">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Provinsi Awal</th>
                                <th>Provinsi Tujuan</th>
                                <th>Level</th>
                                <th>Deskripsi</th>
                                <th>Default Value</th>
                                <th>Tgl Berlaku</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->wilayahAwal->nama ?? '-' }}</td>
                                    <td>{{ $item->wilayahTujuan->nama ?? '-' }}</td>
                                    <td>{{ $item->level->name ?? $item->level->nama ?? $item->level->code ?? '-' }}</td>
                                    <td>{{ $item->description }}</td>
                                    <td>{{ number_format($item->default_value, 0, ',', '.') }}</td>
                                    <td>{{ $item->tanggal_berlaku ? \Carbon\Carbon::parse($item->tanggal_berlaku)->format('d M Y') : '-' }}</td>
                                    <td>
                                        <a href="{{ route('setting_plafon_perjalanan_dinas.edit', $item->id) }}" class="btn btn-sm btn-primary mb-1">Edit</a>
                                        <form action="{{ route('setting_plafon_perjalanan_dinas.destroy', $item->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4 d-flex justify-content-center">
                        {{ $data->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

   <!-- Ganti bagian <script> di paling bawah dengan ini: -->
<script>
    $(document).ready(function() {
        // Fungsi helper untuk setup cascade
        function setupCascade(teritoryId, provinsiId, apiUrl) {
            $(teritoryId).on('change', function() {
                var teritory = $(this).val();
                var provSelect = $(provinsiId);
                
                provSelect.prop('disabled', true).html('<option value="">-- Loading Provinsi... --</option>');
                
                if (teritory) {
                    $.ajax({
                        url: apiUrl,
                        type: 'GET',
                        data: { teritory: teritory },
                        dataType: 'json',
                        success: function(response) {
                            provSelect.prop('disabled', false).html('<option value="">-- Pilih Provinsi --</option>');
                            if (response.length > 0) {
                                $.each(response, function(key, value) {
                                    provSelect.append('<option value="' + value.id + '">' + value.nama + '</option>');
                                });
                            } else {
                                provSelect.append('<option value="">-- Tidak ada provinsi --</option>');
                            }
                        }
                    });
                } else {
                    provSelect.prop('disabled', true).html('<option value="">-- Pilih Territory Terlebih Dahulu --</option>');
                }
            });
        }

        // Inisialisasi untuk Awal dan Tujuan
        setupCascade('#teritory-awal-select', '#provinsi-awal-select', '{{ route("api.plafon.wilayah.provinsi") }}');
        setupCascade('#teritory-tujuan-select', '#provinsi-tujuan-select', '{{ route("api.plafon.wilayah.provinsi") }}');

        // 🎯 Restore old values jika validasi gagal
        @if(old('teritory_awal'))
            $('#teritory-awal-select').val('{{ old("teritory_awal") }}').trigger('change');
            setTimeout(function() { $('#provinsi-awal-select').val('{{ old("provinsi_awal_id") }}'); }, 500);
        @endif

        @if(old('teritory_tujuan'))
            $('#teritory-tujuan-select').val('{{ old("teritory_tujuan") }}').trigger('change');
            setTimeout(function() { $('#provinsi-tujuan-select').val('{{ old("provinsi_tujuan_id") }}'); }, 500);
        @endif
    });
</script>
</body>
</html>
