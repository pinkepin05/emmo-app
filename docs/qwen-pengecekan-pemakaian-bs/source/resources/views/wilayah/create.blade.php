<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Wilayah - EMMO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css"> {{-- ✅ TAMBAHAN --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            height: calc(2.25rem + 2px);
            padding: 0.375rem 0.75rem;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.5;
            color: #212529;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(2.25rem + 2px);
        }
    </style>
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 1000px;">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Form Tambah Master Wilayah</h4>
                <a href="{{ route('dashboard.master_data') }}" class="btn btn-sm btn-light fw-bold text-primary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <strong>Gagal:</strong>
                        <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form action="{{ route('wilayah.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Wilayah Code (Opsional)</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" 
                                   value="{{ old('code') }}" placeholder="Contoh: 11.01, WIL-01">
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nama Wilayah <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" 
                                   value="{{ old('nama') }}" required placeholder="Contoh: Jawa Tengah">
                            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Parent Wilayah (Induk)</label>
                            <select name="parent_id" id="parent-wilayah-select" class="form-select @error('parent_id') is-invalid @enderror">
                                <option value="">-- Ketik untuk mencari wilayah induk --</option>
                                @if(old('parent_id'))
                                    @php $parent = \App\Models\Wilayah::find(old('parent_id')); @endphp
                                    @if($parent)
                                        <option value="{{ $parent->id }}" selected>{{ $parent->nama }}</option>
                                    @endif
                                @endif
                            </select>
                            @error('parent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Ketik minimal 2 huruf untuk mencari</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Teritory</label>
                            <select name="teritory" class="form-select @error('teritory') is-invalid @enderror">
                                <option value="">-- Pilih Teritory --</option>
                                <option value="Jawa" {{ old('teritory') == 'Jawa' ? 'selected' : '' }}>Jawa</option>
                                <option value="Luar Jawa" {{ old('teritory') == 'Luar Jawa' ? 'selected' : '' }}>Luar Jawa</option>
                            </select>
                            @error('teritory')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mb-4 form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" 
                               {{ old('is_active', 1) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Set Aktif</label>
                    </div>
                    <button type="submit" class="btn btn-success w-100 fw-bold">
                        <i class="bi bi-save me-2"></i>Simpan Master Wilayah
                    </button>
                </form>

                <hr class="my-5">

                {{-- 🎯 HEADER + SEARCH (SATU-SATUNYA HEADING) --}}
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h5 class="mb-0">
                        <i class="bi bi-table me-2"></i>Daftar Master Wilayah
                        @if($search)
                            <span class="badge bg-info text-dark ms-2" style="font-size: 0.8rem;">
                                🔍 Hasil: "{{ $search }}"
                            </span>
                        @endif
                    </h5>
                    
                    <form action="{{ route('wilayah.create') }}" method="GET" class="d-flex gap-2" style="min-width: 300px;">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0" 
                                   placeholder="Cari nama, code, atau teritory..." 
                                   value="{{ $search ?? '' }}" autocomplete="off">
                            @if($search)
                                <a href="{{ route('wilayah.create') }}" class="btn btn-outline-secondary" title="Reset">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            @endif
                        </div>
                        <button type="submit" class="btn btn-primary">Cari</button>
                    </form>
                </div>

                {{-- 🎯 INFO JUMLAH HASIL --}}
                @if($search)
                    <div class="alert alert-info py-2 small mb-3 d-flex align-items-center">
                        <i class="bi bi-info-circle me-2 fs-5"></i>
                        <div>
                            Ditemukan <strong>{{ $wilayahs->total() }}</strong> wilayah yang cocok.
                            <a href="{{ route('wilayah.create') }}" class="text-decoration-none ms-2">
                                <u>Tampilkan semua</u>
                            </a>
                        </div>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">ID</th>
                                <th width="15%">Code</th>
                                <th>Nama Wilayah</th>
                                <th>Induk (Parent)</th>
                                <th>Teritory</th>
                                <th width="10%">Status</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($wilayahs as $wil)
                                <tr>
                                    <td>{{ $wil->id }}</td>
                                    <td><code>{{ $wil->code ?? '-' }}</code></td>
                                    <td><strong>{{ $wil->nama }}</strong></td>
                                    <td>
                                        @if($wil->parent)
                                            <span class="badge bg-secondary">{{ $wil->parent->nama }}</span>
                                        @else
                                            <span class="text-muted small">— Tidak Ada —</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($wil->teritory == 'Jawa')
                                            <span class="badge bg-success">Jawa</span>
                                        @elseif($wil->teritory == 'Luar Jawa')
                                            <span class="badge bg-warning text-dark">Luar Jawa</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $wil->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $wil->is_active ? 'Aktif' : 'Non-Aktif' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('wilayah.edit', $wil->id) }}" class="btn btn-sm btn-primary mb-1">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form action="{{ route('wilayah.destroy', $wil->id) }}" method="POST" style="display:inline;">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger mb-1" 
                                                    onclick="return confirm('Hapus wilayah {{ $wil->nama }}?')">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        @if($search)
                                            Tidak ada wilayah yang cocok dengan "<strong>{{ $search }}</strong>"
                                        @else
                                            Belum ada data wilayah.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3 d-flex justify-content-center">
                    {{ $wilayahs->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#parent-wilayah-select').select2({
                placeholder: 'Ketik nama wilayah induk...',
                minimumInputLength: 2,
                allowClear: true,
                width: '100%',
                language: {
                    inputTooShort: function() { return 'Ketik minimal 2 huruf...'; },
                    noResults: function() { return 'Wilayah tidak ditemukan.'; },
                    searching: function() { return 'Mencari...'; }
                },
                ajax: {
                    url: '{{ route("api.wilayah.search") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { q: params.term };
                    },
                    processResults: function (data) {
                        return { results: data };
                    },
                    cache: true
                }
            });
        });
    </script>
</body>
</html>
