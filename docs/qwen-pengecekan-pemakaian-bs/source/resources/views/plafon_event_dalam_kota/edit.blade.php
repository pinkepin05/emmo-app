<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Plafon Event Dalam Kota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container-fluid px-5">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Master Plafon Event Dalam Kota</h4>
                <a href="{{ route('plafon-event-dalam-kota.create') }}" class="btn btn-sm btn-light fw-bold text-warning">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>
            <div class="card-body">
                
                @if($errors->any())
                    <div class="alert alert-danger">
                        <strong>Gagal Memperbarui:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('plafon-event-dalam-kota.update', $plafon->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Tanggal Berlaku <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_berlaku" class="form-control" 
                                   value="{{ old('tanggal_berlaku', $plafon->tanggal_berlaku) }}" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Level <span class="text-danger">*</span></label>
                            <select name="level_id" class="form-select" required>
                                <option value="">-- Pilih Level --</option>
                                @foreach($levels as $lvl)
                                    <option value="{{ $lvl->id }}" 
                                        {{ old('level_id', $plafon->level_id) == $lvl->id ? 'selected' : '' }}>
                                        {{ $lvl->description }} ({{ $lvl->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Kelompok Hari Kerja <span class="text-danger">*</span></label>
                            <select name="kelompok_hari_kerja_id" class="form-select" required>
                                <option value="">-- Pilih Kelompok --</option>
                                @foreach($weekdayGroups as $wg)
                                    <option value="{{ $wg->id }}" 
                                        {{ old('kelompok_hari_kerja_id', $plafon->kelompok_hari_kerja_id) == $wg->id ? 'selected' : '' }}>
                                        {{ $wg->description ?? $wg->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Hari Event (ENUM)</label>
                            <select name="hari_event" class="form-select">
                                <option value="">-- Pilih Hari --</option>
                                <option value="Reguler" {{ old('hari_event', $plafon->hari_event) == 'Reguler' ? 'selected' : '' }}>Reguler</option>
                                <option value="Sabtu" {{ old('hari_event', $plafon->hari_event) == 'Sabtu' ? 'selected' : '' }}>Sabtu</option>
                                <option value="Libur" {{ old('hari_event', $plafon->hari_event) == 'Libur' ? 'selected' : '' }}>Libur</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Jam Kerja Event <span class="text-danger">*</span></label>
                            <select name="jam_kerja_event_id" class="form-select" required>
                                <option value="">-- Pilih Jam Kerja --</option>
                                @foreach($eventHourGroups as $ehg)
                                    <option value="{{ $ehg->id }}" 
                                        {{ old('jam_kerja_event_id', $plafon->jam_kerja_event_id) == $ehg->id ? 'selected' : '' }}>
                                        {{ $ehg->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Nilai Plafon (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="default_value" class="form-control" 
                                   value="{{ old('default_value', $plafon->default_value) }}" 
                                   placeholder="Contoh: 150000" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Deskripsi <span class="text-danger">*</span></label>
                            <input type="text" name="description" class="form-control" 
                                   value="{{ old('description', $plafon->description) }}" 
                                   placeholder="Contoh: Plafon Marketing Dalam Kota (Reguler)" required>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning fw-bold flex-grow-1">
                            <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('plafon-event-dalam-kota.create') }}" class="btn btn-outline-secondary fw-bold">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
