<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Setting Plafon Perjalanan Dinas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 900px;">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <h4 class="mb-0 fw-bold">Form Edit Plafon Perjalanan Dinas</h4>
                
                <a href="{{ route('setting_plafon_perjalanan_dinas.create') }}" class="btn btn-sm btn-dark fw-bold">
                    ← Batal & Kembali
                </a>
            </div>
            <div class="card-body">

                <form action="{{ route('setting_plafon_perjalanan_dinas.update', $item->id) }}" method="POST">
                    @csrf 
                    @method('PUT')

                   @php
    $teritoryAwal = $item->wilayahAwal ? $item->wilayahAwal->teritory : '';
    $teritoryTujuan = $item->wilayahTujuan ? $item->wilayahTujuan->teritory : '';
@endphp

<!-- Ganti bagian dropdown Provinsi Awal & Tujuan dengan ini: -->
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Territory Awal <span class="text-danger">*</span></label>
        <select name="teritory_awal" id="teritory-awal-select" class="form-select @error('teritory_awal') is-invalid @enderror" required>
            <option value="">-- Pilih Territory --</option>
            <option value="Jawa" {{ $teritoryAwal == 'Jawa' ? 'selected' : '' }}>Jawa</option>
            <option value="Luar Jawa" {{ $teritoryAwal == 'Luar Jawa' ? 'selected' : '' }}>Luar Jawa</option>
        </select>
        @error('teritory_awal')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Provinsi Awal <span class="text-danger">*</span></label>
        <select name="provinsi_awal_id" id="provinsi-awal-select" class="form-select @error('provinsi_awal_id') is-invalid @enderror" required disabled>
            <option value="">-- Loading... --</option>
        </select>
        @error('provinsi_awal_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Territory Tujuan <span class="text-danger">*</span></label>
        <select name="teritory_tujuan" id="teritory-tujuan-select" class="form-select @error('teritory_tujuan') is-invalid @enderror" required>
            <option value="">-- Pilih Territory --</option>
            <option value="Jawa" {{ $teritoryTujuan == 'Jawa' ? 'selected' : '' }}>Jawa</option>
            <option value="Luar Jawa" {{ $teritoryTujuan == 'Luar Jawa' ? 'selected' : '' }}>Luar Jawa</option>
        </select>
        @error('teritory_tujuan')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Provinsi Tujuan <span class="text-danger">*</span></label>
        <select name="provinsi_tujuan_id" id="provinsi-tujuan-select" class="form-select @error('provinsi_tujuan_id') is-invalid @enderror" required disabled>
            <option value="">-- Loading... --</option>
        </select>
        @error('provinsi_tujuan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

                    <div class="mb-3">
                        <label class="form-label">Level</label>
                        <select name="level_id" class="form-select @error('level_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Level --</option>
                            @foreach($levels as $level)
                                <option value="{{ $level->id }}" {{ old('level_id', $item->level_id) == $level->id ? 'selected' : '' }}>
                                    {{ $level->name ?? $level->nama ?? $level->code }}
                                </option>
                            @endforeach
                        </select>
                        @error('level_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <input type="text" name="description" class="form-control @error('description') is-invalid @enderror" value="{{ old('description', $item->description) }}" required>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Default Value (Nominal)</label>
                            <input type="number" name="default_value" class="form-control @error('default_value') is-invalid @enderror" value="{{ old('default_value', $item->default_value) }}">
                            @error('default_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Berlaku</label>
                            <input type="date" name="tanggal_berlaku" class="form-control @error('tanggal_berlaku') is-invalid @enderror" value="{{ old('tanggal_berlaku', $item->tanggal_berlaku ? \Carbon\Carbon::parse($item->tanggal_berlaku)->format('Y-m-d') : '') }}">
                            @error('tanggal_berlaku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning w-100 fw-bold">Update Data Plafon</button>
                </form>

            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        function setupCascade(teritoryId, provinsiId, apiUrl, initialTeritory, initialProvinsi) {
            // Set initial values jika sedang edit
            if (initialTeritory && initialProvinsi) {
                $(teritoryId).val(initialTeritory);
            }

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
                                    var selected = (initialProvinsi && value.id == initialProvinsi) ? 'selected' : '';
                                    provSelect.append('<option value="' + value.id + '" ' + selected + '>' + value.nama + '</option>');
                                });
                            }
                        }
                    });
                } else {
                    provSelect.prop('disabled', true).html('<option value="">-- Pilih Territory Terlebih Dahulu --</option>');
                }
            });

            // Trigger change saat load agar provinsi terisi otomatis jika ada data awal
            if (initialTeritory) {
                $(teritoryId).trigger('change');
            }
        }

        // Inisialisasi untuk Awal
        setupCascade(
            '#teritory-awal-select', 
            '#provinsi-awal-select', 
            '{{ route("api.plafon.wilayah.provinsi") }}',
            '{{ $teritoryAwal }}',
            '{{ $item->provinsi_awal_id }}'
        );

        // Inisialisasi untuk Tujuan
        setupCascade(
            '#teritory-tujuan-select', 
            '#provinsi-tujuan-select', 
            '{{ route("api.plafon.wilayah.provinsi") }}',
            '{{ $teritoryTujuan }}',
            '{{ $item->provinsi_tujuan_id }}'
        );
    });
</script>
</body>
</html>
