<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Tambah Master Cost Category</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('partials.mobile-assets')
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 800px;">
        <div class="card shadow">
           <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Form Tambah Master Cost Category<h4>
    
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

                <form action="{{ route('cost_categories.store') }}" method="POST">
                    @csrf 

                    <div class="mb-3">
                        <label for="code" class="form-label">Cost Category Code</label>
                        <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="Contoh: HTL, OPRT, ADMN">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Nama Cost Category / Deskripsi</label>
                        <textarea name="description" id="description" rows="2" class="form-control @error('description') is-invalid @enderror" placeholder="Contoh: Hotel, Operation, Administration">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" checked>
                        <label class="form-check-label" for="is_active">Set Aktif (Is Active)</label>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Simpan Master Cost Category</button>
                </form>

                <hr class="my-5">

                <h5>Daftar Master Cost Category</h5>
                <div class="table-responsive">
                <table class="table table-bordered table-striped mt-3">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Nama Cost Category</th>
                            <th>Status</th>
                            <th>Aksi</th> 
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($costCategories as $costCategory)
                            <tr>
                                <td>{{ $costCategory->id }}</td>
                                <td>{{ $costCategory->nama }}</td>
                                <td>{{ $costCategory->description }}</td>
                                <td>
                                    <span class="badge {{ $costCategory->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $costCategory->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                                <td>
                                   <a href="{{ route('cost_categories.edit', $costCategory->id) }}" class="btn btn-sm btn-primary mb-1">Edit</a>

                            <form action="{{ route('cost_categories.destroy', $costCategory->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Apakah Anda yakin ingin menghapus position ini?')">
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
