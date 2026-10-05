<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Edit Master Wilayah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('partials.mobile-assets')
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 800px;">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Form Edit Master Wilayah</h4>
            </div>
            <div class="card-body">

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('wilayah.update', $wilayah->id) }}" method="POST">
                    @csrf 
                    @method('PUT') <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Wilayah Code</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $wilayah->code) }}" placeholder="Contoh: WIL-01">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Wilayah</label>
                            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $wilayah->nama) }}" required>
                            @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
    <label class="form-label">Parent Wilayah (Induk)</label>
    <select name="parent_id" id="parent-wilayah-select-edit" class="form-select">
        <option value="">-- Tidak Ada Induk (Berdiri Sendiri) --</option>
        @if($wilayah->parent_id)
            <option value="{{ $wilayah->parent_id }}" selected>{{ $wilayah->parent->nama }}</option>
        @endif
    </select>
</div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teritory</label>
                            <select name="teritory" class="form-select">
                                <option value="">-- Pilih Teritory --</option>
                                <option value="Jawa" {{ old('teritory', $wilayah->teritory) == 'Jawa' ? 'selected' : '' }}>Jawa</option>
                                <option value="Luar Jawa" {{ old('teritory', $wilayah->teritory) == 'Luar Jawa' ? 'selected' : '' }}>Luar Jawa</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ $wilayah->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Set Aktif (Is Active)</label>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('wilayah.create') }}" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Update Master Wilayah</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#parent-wilayah-select-edit').select2({
            placeholder: 'Ketik nama wilayah induk...',
            minimumInputLength: 2,
            ajax: {
                url: '{{ route("api.wilayah.search") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) { return { q: params.term }; },
                processResults: function (data) { return { results: data }; },
                cache: true
            }
        });
    });
</script>
</body>
</html>
