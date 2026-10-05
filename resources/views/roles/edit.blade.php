<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Edit Master Role</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('partials.mobile-assets')
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <main class="container" style="max-width: 600px;">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Form Edit Master Role</h4>
                <a href="{{ route('roles.index') }}" class="btn btn-sm btn-light">Kembali</a>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('roles.update', $role->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="code" class="form-label">Role Code</label>
                        <input type="text" id="code" name="code" class="form-control" value="{{ $role->code }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama</label>
                        <textarea id="nama" name="nama" class="form-control" rows="3" required>{{ old('nama', $role->nama) }}</textarea>
                    </div>
                    <div class="mb-4 form-check">
                        <input type="checkbox" id="is_active" name="is_active" class="form-check-input" value="1" {{ $role->is_active ? 'checked' : '' }}>
                        <label for="is_active" class="form-check-label">Set Aktif (Is Active)</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update Master Role</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
