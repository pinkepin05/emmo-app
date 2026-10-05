<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Edit Master Weekday Group</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('partials.mobile-assets')
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 600px;">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Form Edit Master Weekday Group</h4>
            </div>
            <div class="card-body">

                <form action="{{ route('weekday_groups.update', $weekdayGroup->id) }}" method="POST">
                    @csrf 
                    @method('PUT') 

                    <div class="mb-3">
                        <label class="form-label">Code (Hari Kerja)</label>
                        <select name="code" class="form-select @error('code') is-invalid @enderror">
                            <option value="">-- Pilih Hari Kerja --</option>
                            <option value="5 HK" {{ old('code', $weekdayGroup->code) == '5 HK' ? 'selected' : '' }}>5 HK</option>
                            <option value="6 HK" {{ old('code', $weekdayGroup->code) == '6 HK' ? 'selected' : '' }}>6 HK</option>
                        </select>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2">{{ old('description', $weekdayGroup->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ $weekdayGroup->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Set Aktif</label>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('weekday_groups.create') }}" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Update Weekday Group</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</body>
</html>
