<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Master Event Hour Group</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 800px;">
        <div class="card shadow">
           <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Form Tambah Master Event Hour Group</h4>
    
    <a href="{{ route('dashboard.master_data') }}" class="btn btn-sm btn-light fw-bold text-primary">
        ← Kembali ke Dashboard
    </a>
</div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form action="{{ route('event_hour_groups.store') }}" method="POST">
                    @csrf 

                    <div class="mb-3">
                        <label class="form-label">Code (Jam)</label>
                        <select name="code" class="form-select @error('code') is-invalid @enderror">
                            <option value="">-- Pilih Jam --</option>
                            <option value="8 Jam" {{ old('code') == '8 Jam' ? 'selected' : '' }}>8 Jam</option>
                            <option value="12 Jam" {{ old('code') == '12 Jam' ? 'selected' : '' }}>12 Jam</option>
                            <option value="16 Jam" {{ old('code') == '16 Jam' ? 'selected' : '' }}>16 Jam</option>
                        </select>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2" placeholder="Contoh: Shift Standar">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
                        <label class="form-check-label" for="is_active">Set Aktif</label>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Simpan Event Hour Group</button>
                </form>

                <hr class="my-5">

                <h5>Daftar Master Event Hour Group</h5>
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
                        @foreach($eventHourGroups as $ehg)
                            <tr>
                                <td>{{ $ehg->id }}</td>
                                <td>{{ $ehg->code ?? '-' }}</td>
                                <td>{{ $ehg->description ?? '-' }}</td>
                                <td><span class="badge {{ $ehg->is_active ? 'bg-success' : 'bg-danger' }}">{{ $ehg->is_active ? 'Aktif' : 'Non-Aktif' }}</span></td>
                                <td>
                                    <a href="{{ route('event_hour_groups.edit', $ehg->id) }}" class="btn btn-sm btn-primary mb-1">Edit</a>
                                    <form action="{{ route('event_hour_groups.destroy', $ehg->id) }}" method="POST" style="display:inline;">
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
</body>
</html>
