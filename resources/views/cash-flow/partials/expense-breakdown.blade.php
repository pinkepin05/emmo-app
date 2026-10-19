{{-- Rincian arus kas — dipakai di index & show --}}

<div class="bd-grid" role="list" aria-label="Rincian arus kas periode ini">
    <div class="bd-cell bd-green" role="listitem">
        <span class="bd-label">Pencairan BS</span>
        <span class="bd-value amt">Rp {{ number_format($snapshot->total_masuk ?? 0, 0, ',', '.') }}</span>
    </div>

    {{-- ✨ DIPISAH MENJADI DUA ✨ --}}
    <div class="bd-cell bd-red" role="listitem">
        <span class="bd-label">Operasional Sales</span>
        <span class="bd-value amt">{{ $snapshot->total_keluar_operasional === null ? 'Belum tersedia' : 'Rp '.number_format($snapshot->total_keluar_operasional, 0, ',', '.') }}</span>
    </div>

    <div class="bd-cell bd-amber" role="listitem">
        <span class="bd-label">Perjalanan Dinas</span>
        <span class="bd-value amt">{{ $snapshot->total_keluar_perjalanan_dinas === null ? 'Belum tersedia' : 'Rp '.number_format($snapshot->total_keluar_perjalanan_dinas, 0, ',', '.') }}</span>
    </div>
    {{-- ========================== --}}

    <div class="bd-cell bd-slate" role="listitem">
        <span class="bd-label">Event Dalam Kota</span>
        <span class="bd-value amt">Rp {{ number_format($snapshot->total_event_dalam ?? 0, 0, ',', '.') }}</span>
    </div>

    <div class="bd-cell bd-slate" role="listitem">
        <span class="bd-label">Event Luar Kota</span>
        <span class="bd-value amt">Rp {{ number_format($snapshot->total_event_luar ?? 0, 0, ',', '.') }}</span>
    </div>
</div>
