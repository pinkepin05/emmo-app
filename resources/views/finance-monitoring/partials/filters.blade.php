@php $hidden = array_filter($hidden ?? [], fn ($v) => $v !== null && $v !== ''); @endphp
<form method="GET" action="{{ $action }}" class="row g-2 align-items-end mb-4">
    @foreach ($hidden as $name => $value) <input type="hidden" name="{{ $name }}" value="{{ $value }}"> @endforeach
    <div class="col-12 col-md-4 col-lg-3">
        <label class="form-label">Periode</label>
        <input type="month" class="form-control" name="periode" value="{{ $periode }}">
    </div>
    <div class="col-12 col-md-auto"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Terapkan</button></div>
</form>