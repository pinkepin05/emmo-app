<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Master Plafon Event Luar Kota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('partials.mobile-assets')
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container-fluid px-5">
        <div class="card shadow">
           <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Form Tambah Master Plafon Event Luar Kota</h4>
    
    <a href="{{ route(auth()->user()->dashboardRouteName()) }}" class="btn btn-sm btn-light fw-bold text-primary">
        ← Kembali ke Dashboard
    </a>
</div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form action="{{ route('plafon-event-luar-kota.store') }}" method="POST">
                    @csrf 

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal Berlaku</label>
                            <input type="date" name="tanggal_berlaku" class="form-control" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Level</label>
                            <select name="level_id" class="form-select" required>
                                <option value="">-- Pilih Level --</option>
                                @foreach($levels as $lvl)
                                    <option value="{{ $lvl->id }}">{{ $lvl->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kelompok Hari Kerja</label>
                            <select name="kelompok_hari_kerja_id" class="form-select" required>
                                <option value="">-- Pilih Kelompok --</option>
                                @foreach($weekdayGroups as $wg)
                                    <option value="{{ $wg->id }}">{{ $wg->description ?? $wg->code }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                           <label class="form-label">Hari Event <span class="text-danger">*</span></label>
                           <select name="hari_event" class="form-select" required>
    <option value="">-- Pilih Hari --</option>
    <option value="Reguler" {{ old('hari_event') == 'Reguler' ? 'selected' : '' }}>Reguler</option>
    <option value="Sabtu" {{ old('hari_event') == 'Sabtu' ? 'selected' : '' }}>Sabtu</option>
    <option value="Libur" {{ old('hari_event') == 'Libur' ? 'selected' : '' }}>Libur</option>
</select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jam Kerja Event</label>
                            <select name="jam_kerja_event_id" class="form-select" required>
                                <option value="">-- Pilih Jam Kerja --</option>
                                @foreach($eventHourGroups as $ehg)
                                    <option value="{{ $ehg->id }}">{{ $ehg->code }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nilai Plafon (Rp)</label>
                            <input type="number" name="default_value" class="form-control" placeholder="Contoh: 300000" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Deskripsi</label>
                            <input type="text" name="description" class="form-control" placeholder="Contoh: Plafon Luar Kota (Reguler)" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning w-100">Simpan Plafon Event Luar Kota</button>
                </form>

                <hr class="my-5">

                <h5>Daftar Plafon Event Luar Kota</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mt-3 text-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th>Tgl Berlaku</th>
                                <th>Level</th>
                                <th>Kelompok Hari</th>
                                <th>Hari Event</th>
                                <th>Jam Event</th>
                                <th>Deskripsi</th>
                                <th>Nilai Plafon</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($plafons as $plafon)
                                <tr>
                                    <td>{{ $plafon->tanggal_berlaku }}</td>
                                    <td>{{ $plafon->level->nama ?? '-' }}</td>
                                    <td>{{ $plafon->weekdayGroup->description ?? ($plafon->weekdayGroup->code ?? '-') }}</td>
                                    <td>{{ $plafon->hari_event ?? '-' }}</td>
                                    <td>{{ $plafon->eventHourGroup->code ?? '-' }}</td>
                                    <td>{{ $plafon->description }}</td>
                                    <td>Rp {{ number_format($plafon->default_value, 0, ',', '.') }}</td>
                                    <td>
                                        <a href="{{ route('plafon-event-luar-kota.edit', $plafon->id) }}" class="btn btn-sm btn-primary mb-1">Edit</a>
                                        <form action="{{ route('plafon-event-luar-kota.destroy', $plafon->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Hapus plafon luar kota ini?')">Hapus</button>
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
</body>
</html>
