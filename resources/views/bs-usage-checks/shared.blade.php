@push('styles')
<style>
    .check-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:1.25rem; margin-bottom:1.25rem; }
    .check-table { min-width:850px; }
    .check-table th { background:#f8fafc; font-size:.8rem; white-space:nowrap; }
    .check-table td { vertical-align:middle; }
    .check-money { text-align:right; white-space:nowrap; font-variant-numeric:tabular-nums; }
    .check-readonly { background:#f1f5f9 !important; }
    .check-note { max-width:300px; min-width:170px; white-space:pre-wrap; overflow-wrap:anywhere; }
    .check-summary { display:flex; flex-wrap:wrap; gap:1rem; }
    .check-summary > div { flex:1; min-width:180px; }
    .check-summary strong { display:block; font-size:1.25rem; margin-top:.25rem; }
</style>
@endpush
@if(session('success'))
    @if($compactFeedback ?? false)
        <div class="check-result-feedback" role="status"><i class="bi bi-check-circle" aria-hidden="true"></i><span>{{ session('success') }}</span></div>
    @else
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
@endif
@if($errors->any())
    <div class="alert alert-danger" role="alert"><strong>Data belum dapat diproses.</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
