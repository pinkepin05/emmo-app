<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Tambah Master Weekday Group</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('partials.mobile-assets')
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 800px;">
        <div class="card shadow">
           <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Form Tambah Master WeekDayGroup</h4>
    
    <a href="{{ route('dashboard.master_data') }}" class="btn btn-sm btn-light fw-bold text-primary">
        ← Kembali ke Dashboard
    </a>
</div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form action="{{ route('weekday_groups.store') }}" method="POST">
                    @csrf 

                    <div class="mb-3">
                        <label class="form-label">Code (Hari Kerja)</label>
                        <select name="code" class="form-select @error('code') is-invalid @enderror">
                            <option value="">-- Pilih Hari Kerja --</option>
                            <option value="5 HK" {{ old('code') == '5 HK' ? 'selected' : '' }}>5 HK</option>
                            <option value="6 HK" {{ old('code') == '6 HK' ? 'selected' : '' }}>6 HK</option>
                        </select>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2" placeholder="Contoh: Jadwal Kerja Standar">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
                        <label class="form-check-label" for="is_active">Set Aktif</label>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Simpan Weekday Group</button>
                </form>

                <hr class="my-5">

                <h5>Daftar Master Weekday Group</h5>
                <div class="table-responsive">
                <table class="table table-bordered table-striped mt-3">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($weekdayGroups as $wg)
                            <tr>
                                <td>{{ $wg->id }}</td>
                                <td>{{ $wg->code ?? '-' }}</td>
                                <td>{{ $wg->description ?? '-' }}</td>
                                <td><span class="badge {{ $wg->is_active ? 'bg-success' : 'bg-danger' }}">{{ $wg->is_active ? 'Aktif' : 'Non-Aktif' }}</span></td>
                                <td>
                                    <a href="{{ route('weekday_groups.edit', $wg->id) }}" class="btn btn-sm btn-primary mb-1">Edit</a>
                                    <form action="{{ route('weekday_groups.destroy', $wg->id) }}" method="POST" style="display:inline;">
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
</body>
</html>
