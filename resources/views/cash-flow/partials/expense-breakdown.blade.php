{{-- Rincian arus kas — dipakai di index & show --}}

<div class="bd-grid" role="list" aria-label="Rincian arus kas periode ini">
    <div class="bd-cell bd-green" role="listitem">
        <span class="bd-label">Pencairan BS</span>
        <span class="bd-value amt">Rp {{ number_format($snapshot->total_masuk ?? 0, 0, ',', '.') }}</span>
    </div>

    <div class="bd-cell bd-red" role="listitem">
        <span class="bd-label">Pengeluaran BS</span>
        <span class="bd-value amt">Rp {{ number_format($snapshot->total_keluar_bs ?? 0, 0, ',', '.') }}</span>
    </div>

    <div class="bd-cell bd-slate" role="listitem">
        <span class="bd-label">Event Dalam Kota</span>
        <span class="bd-value amt">Rp {{ number_format($snapshot->total_event_dalam ?? 0, 0, ',', '.') }}</span>
    </div>

    <div class="bd-cell bd-slate" role="listitem">
        <span class="bd-label">Event Luar Kota</span>
        <span class="bd-value amt">Rp {{ number_format($snapshot->total_event_luar ?? 0, 0, ',', '.') }}</span>
    </div>
</div>
