<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Master Company</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 600px;">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Form Edit Master Company</h4>
            </div>
            <div class="card-body">

                <form action="{{ route('companies.update', $company->id) }}" method="POST">
                    @csrf 
                    @method('PUT') <div class="mb-3">
    <label for="code" class="form-label">Company Code</label>
    <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $company->code) }}" required>
    @error('code')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Perusahaan / Deskripsi</label>
                        <textarea name="nama" id="nama" rows="2" class="form-control @error('nama') is-invalid @enderror" required>{{ $company->nama }}</textarea>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ $company->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Set Aktif (Is Active)</label>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('companies.create') }}" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Update Master Company</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</body>
</html>
