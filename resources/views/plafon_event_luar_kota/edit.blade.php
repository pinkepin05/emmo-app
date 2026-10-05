<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Edit Plafon Event Luar Kota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('partials.mobile-assets')
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container-fluid px-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Form Edit Setting Plafon Event (Luar Kota)</h4>
            </div>
            <div class="card-body">
                
                <form action="{{ route('plafon-event-luar-kota.update', $plafon->id) }}" method="POST">
                    @csrf 
                    @method('PUT') 

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal Berlaku</label>
                            <input type="date" name="tanggal_berlaku" class="form-control" value="{{ old('tanggal_berlaku', $plafon->tanggal_berlaku) }}" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Level</label>
                            <select name="level_id" class="form-select" required>
                                <option value="">-- Pilih Level --</option>
                                @foreach($levels as $lvl)
                                    <option value="{{ $lvl->id }}" {{ old('level_id', $plafon->level_id) == $lvl->id ? 'selected' : '' }}>
                                        {{ $lvl->description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kelompok Hari Kerja</label>
                            <select name="kelompok_hari_kerja_id" class="form-select" required>
                                <option value="">-- Pilih Kelompok --</option>
                                @foreach($weekdayGroups as $wg)
                                    <option value="{{ $wg->id }}" {{ old('kelompok_hari_kerja_id', $plafon->kelompok_hari_kerja_id) == $wg->id ? 'selected' : '' }}>
                                        {{ $wg->description ?? $wg->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Hari Event (ENUM)</label>
                            <select name="hari_event" class="form-select" required>
    <option value="">-- Pilih Hari --</option>
    <option value="Reguler" {{ old('hari_event', $plafon->hari_event) == 'Reguler' ? 'selected' : '' }}>Reguler</option>
    <option value="Sabtu" {{ old('hari_event', $plafon->hari_event) == 'Sabtu' ? 'selected' : '' }}>Sabtu</option>
    <option value="Libur" {{ old('hari_event', $plafon->hari_event) == 'Libur' ? 'selected' : '' }}>Libur</option>
</select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jam Kerja Event</label>
                            <select name="jam_kerja_event_id" class="form-select" required>
                                <option value="">-- Pilih Jam Kerja --</option>
                                @foreach($eventHourGroups as $ehg)
                                    <option value="{{ $ehg->id }}" {{ old('jam_kerja_event_id', $plafon->jam_kerja_event_id) == $ehg->id ? 'selected' : '' }}>
                                        {{ $ehg->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nilai Plafon (Rp)</label>
                            <input type="number" name="default_value" class="form-control" value="{{ old('default_value', $plafon->default_value) }}" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Deskripsi</label>
                            <input type="text" name="description" class="form-control" value="{{ old('description', $plafon->description) }}" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <a href="{{ route('plafon-event-luar-kota.create') }}" class="btn btn-secondary">Batal / Kembali</a>
                        <button type="submit" class="btn btn-primary">Update Plafon Event Luar Kota</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</body>
</html>
