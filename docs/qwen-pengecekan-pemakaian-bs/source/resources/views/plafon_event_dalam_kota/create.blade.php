<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Plafon Event Dalam Kota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container-fluid px-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="bi bi-geo me-2"></i>Form Tambah Master Plafon Event Dalam Kota
                </h4>
                <a href="{{ route('dashboard.master_data') }}" class="btn btn-sm btn-light fw-bold text-primary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
                </a>
            </div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong>Gagal Menyimpan:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('plafon-event-dalam-kota.store') }}" method="POST">
                    @csrf 

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Tanggal Berlaku <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_berlaku" class="form-control" value="{{ old('tanggal_berlaku') }}" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Level <span class="text-danger">*</span></label>
                            <select name="level_id" class="form-select" required>
                                <option value="">-- Pilih Level --</option>
                                @foreach($levels as $lvl)
                                    <option value="{{ $lvl->id }}" {{ old('level_id') == $lvl->id ? 'selected' : '' }}>
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
                                    <option value="{{ $wg->id }}" {{ old('kelompok_hari_kerja_id') == $wg->id ? 'selected' : '' }}>
                                        {{ $wg->description ?? $wg->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Hari Event (ENUM)</label>
                            <select name="hari_event" class="form-select">
                                <option value="">-- Pilih Hari --</option>
                                <option value="Reguler" {{ old('hari_event') == 'Reguler' ? 'selected' : '' }}>Reguler</option>
                                <option value="Sabtu" {{ old('hari_event') == 'Sabtu' ? 'selected' : '' }}>Sabtu</option>
                                <option value="Libur" {{ old('hari_event') == 'Libur' ? 'selected' : '' }}>Libur</option>
                            </select>
                            <small class="text-muted">Kosongkan jika berlaku untuk semua hari</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Jam Kerja Event <span class="text-danger">*</span></label>
                            <select name="jam_kerja_event_id" class="form-select" required>
                                <option value="">-- Pilih Jam Kerja --</option>
                                @foreach($eventHourGroups as $ehg)
                                    <option value="{{ $ehg->id }}" {{ old('jam_kerja_event_id') == $ehg->id ? 'selected' : '' }}>
                                        {{ $ehg->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Nilai Plafon (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="default_value" class="form-control" 
                                   value="{{ old('default_value') }}" placeholder="Contoh: 150000" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Deskripsi <span class="text-danger">*</span></label>
                            <input type="text" name="description" class="form-control" 
                                   value="{{ old('description') }}" 
                                   placeholder="Contoh: Plafon Marketing Dalam Kota (Reguler)" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 fw-bold">
                        <i class="bi bi-check-circle me-1"></i> Simpan Plafon Event
                    </button>
                </form>

                <hr class="my-5">

                <h5 class="fw-bold mb-3">
                    <i class="bi bi-list-ul me-2"></i>Daftar Plafon Event Dalam Kota
                </h5>
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
                                <th class="text-end">Nilai Plafon</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plafons as $plafon)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($plafon->tanggal_berlaku)->format('d M Y') }}</td>
                                    <td>{{ $plafon->level->nama ?? '-' }}</td>
                                    <td>{{ $plafon->weekdayGroup->description ?? ($plafon->weekdayGroup->code ?? '-') }}</td>
                                    <td>
                                        @if($plafon->hari_event)
                                            <span class="badge 
                                                @if($plafon->hari_event == 'Reguler') bg-primary 
                                                @elseif($plafon->hari_event == 'Sabtu') bg-warning text-dark 
                                                @else bg-danger @endif">
                                                {{ $plafon->hari_event }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Semua Hari</span>
                                        @endif
                                    </td>
                                    <td>{{ $plafon->eventHourGroup->code ?? '-' }}</td>
                                    <td>{{ $plafon->description }}</td>
                                    <td class="text-end fw-bold text-success">
                                        Rp {{ number_format($plafon->default_value, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('plafon-event-dalam-kota.edit', $plafon->id) }}" 
                                           class="btn btn-sm btn-primary mb-1">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form action="{{ route('plafon-event-dalam-kota.destroy', $plafon->id) }}" 
                                              method="POST" style="display:inline;"
                                              onsubmit="return confirm('Yakin ingin menghapus plafon ini?\n\nLevel: {{ $plafon->level->description ?? '-' }}\nPlafon: Rp {{ number_format($plafon->default_value, 0, ',', '.') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger mb-1">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        Belum ada data plafon event dalam kota.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
