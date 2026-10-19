@if(isset($pagedSharedUsage) && $pagedSharedUsage->total() > 0)
    <section class="cf-card" id="sec-shared-bs" aria-labelledby="shared-bs-title">
        <div class="cf-card-head">
            <h3 class="cf-head-title" id="shared-bs-title">
                <span class="cf-ico ico-blue"><i class="bi bi-people" aria-hidden="true"></i></span>
                Pemakaian BS Bersama
            </h3>
            <span class="cf-badge">{{ $pagedSharedUsage->total() }} transaksi</span>
        </div>
        <div class="cf-banner neutral">
            Rincian ini terlihat oleh pemakai dan pemilik BS. Saldo hanya berkurang pada pemilik BS sesuai hasil pemeriksaan Finance.
        </div>
        <div class="trx-list">
            @foreach($pagedSharedUsage as $usage)
                <article class="trx trx-item" data-shared-bs-reference="{{ $usage['reference'] }}">
                    <div class="trx-body">
                        <div class="trx-type">{{ $usage['type'] }}</div>
                        <a class="trx-desc shared-bs-link" href="{{ $usage['url'] }}">{{ $usage['reference'] }}</a>
                        <div class="shared-bs-person">Pemakai: <strong>{{ $usage['user_name'] }}</strong></div>
                        <div class="shared-bs-person">Pemilik BS: <strong>{{ $usage['owner_name'] }}</strong></div>
                        <div class="trx-date">{{ \Carbon\Carbon::parse($usage['date'])->locale('id')->translatedFormat('d M Y') }}</div>
                        <span class="cf-badge">{{ $usage['finance_status'] }}</span>
                    </div>
                    <div class="shared-bs-amounts">
                        <div><span>Nominal dilaporkan</span><strong class="amt">Rp {{ number_format($usage['reported'], 2, ',', '.') }}</strong></div>
                        <div><span>Disetujui Finance</span><strong class="amt">{{ $usage['approved'] === null ? '—' : 'Rp '.number_format($usage['approved'], 2, ',', '.') }}</strong></div>
                    </div>
                </article>
            @endforeach
        </div>
        @if($pagedSharedUsage->hasPages())
            <div class="p-3">{{ $pagedSharedUsage->links('pagination::bootstrap-5') }}</div>
        @endif
    </section>
@endif
