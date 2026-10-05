<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Tambah Master Level</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('partials.mobile-assets')
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 800px;">
        <div class="card shadow">
           <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Form Tambah Master Level</h4>
    
    <a href="{{ route('dashboard.master_data') }}" class="btn btn-sm btn-light fw-bold text-primary">
        ← Kembali ke Dashboard
    </a>
</div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('levels.store') }}" method="POST">
                    @csrf 

                    <div class="mb-3">
                        <label for="code" class="form-label">Level Code</label>
                        <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="Contoh: NONSTAFF, STAFF, SPV, JM, MGR">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Perusahaan / Deskripsi</label>
                        <textarea name="nama" id="nama" rows="2" class="form-control @error('nama') is-invalid @enderror" placeholder="Contoh: Manager, Supervisor">{{ old('nama') }}</textarea>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" checked>
                        <label class="form-check-label" for="is_active">Set Aktif (Is Active)</label>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Simpan Master Level</button>
                </form>

                <hr class="my-5">

                <h5>Daftar Master Level</h5>
                <div class="table-responsive">
                <table class="table table-bordered table-striped mt-3">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Nama Level</th>
                            <th>Status</th>
                            <th>Aksi</th> 
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($levels as $level)
                            <tr>
                                <td>{{ $level->id }}</td>
                                <td>{{ $level->code }}</td>
                                <td>{{ $level->nama }}</td>
                                <td>
                                    <span class="badge {{ $level->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $level->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                                <td>
                                   <a href="{{ route('levels.edit', $level->id) }}" class="btn btn-sm btn-primary mb-1">Edit</a>

                            <form action="{{ route('levels.destroy', $level->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Apakah Anda yakin ingin menghapus perusahaan ini?')">
                                    Hapus
                                </button>
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
