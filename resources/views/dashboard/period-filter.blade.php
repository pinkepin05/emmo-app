<form method="GET"
      action="{{ route('dashboard.transaksi') }}#dashboard-overview"
      class="dashboard-period-filter"
      aria-labelledby="dashboard-period-title">
    <h2 id="dashboard-period-title">
        <i class="bi bi-funnel" aria-hidden="true"></i> Filter Periode
    </h2>

    <div class="dashboard-period-filter__controls">
        <div class="dashboard-period-filter__field">
            <label for="dashboard-period">Periode transaksi</label>
            <input class="form-control"
                   type="month"
                   id="dashboard-period"
                   name="pemakaian_periode"
                   value="{{ $selectedPeriod }}"
                   aria-describedby="dashboard-period-note"
                   required>
        </div>

        <button class="btn btn-primary" type="submit">
            <i class="bi bi-search" aria-hidden="true"></i> Tampilkan
        </button>
    </div>

    <p class="dashboard-period-filter__active">
        Bulan Berjalan: <strong>{{ $selectedPeriodLabel }}</strong>
    </p>
    <p id="dashboard-period-note">
        Berlaku untuk Saldo BS dan seluruh rekap di dashboard.
    </p>
</form>