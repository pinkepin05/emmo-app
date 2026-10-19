<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Edit Master Cost Category</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('partials.mobile-assets')
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 600px;">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Form Edit Master Cost Category</h4>
            </div>
            <div class="card-body">

                <form action="{{ route('cost_categories.update', $costCategories->id) }}" method="POST">
                    @csrf 
                    @method('PUT') 
                    
                    <div class="mb-3">
                        <label for="code" class="form-label">Cost Category Code</label>
                        <!-- Hapus 'readonly', tambahkan validasi error dan old() -->
                        <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $costCategories->nama) }}">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Nama Cost Category / Deskripsi</label>
                        <textarea name="description" id="description" rows="2" class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $costCategories->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ $costCategories->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Set Aktif (Is Active)</label>
                        <div class="form-text">Kategori nonaktif tidak dapat dipilih untuk rincian biaya baru. Riwayat transaksi tetap tersimpan.</div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('cost_categories.create') }}" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Update Master Cost Category</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</body>
</html>
