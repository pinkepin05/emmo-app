@php
    Carbon\Carbon::setLocale('id');
    $rpSigned = fn($v) => ($v < 0 ? '-' : '') . 'Rp ' . number_format(abs($v), 0, ',', '.');

    $iconMap = [
        'masuk' => ['icon' => 'bi-arrow-down-circle', 'ico-class' => 'ico-green', 'item-icon' => 'bi-box-arrow-in-down', 'sign' => '+', 'amt-class' => 'pos'],
        'keluar' => ['icon' => 'bi-arrow-up-circle', 'ico-class' => 'ico-red', 'item-icon' => 'bi-box-arrow-up', 'sign' => '-', 'amt-class' => 'neg'],
    ];
    $meta = $iconMap[$section];
    $title = $section === 'masuk' ? 'Uang Masuk' : 'Uang Keluar';
@endphp

<div class="cf-card-head">
    <h3 class="cf-head-title">
        <span class="cf-ico {{ $meta['ico-class'] }}"><i class="bi {{ $meta['icon'] }}"></i></span>
        {{ $title }}
    </h3>
    <span class="cf-badge">{{ $paginator->total() }} transaksi</span>
</div>

@if($paginator->total() > 0)
    <div class="trx-list">
        @foreach($paginator->items() as $trx)
            @if(!empty($trx['url']))
                <a href="{{ $trx['url'] }}" class="trx trx-item trx-link">
            @else
                <div class="trx trx-item">
            @endif
                <span class="cf-ico {{ $meta['ico-class'] }}"><i class="bi {{ $meta['item-icon'] }}"></i></span>
                <div class="trx-body">
                    <div class="trx-type">{{ $trx['type'] }}</div>
                    <div class="trx-desc">{{ $trx['description'] }}</div>
                    <div class="trx-date">
                        <i class="bi bi-calendar3"></i>
                        {{ Carbon\Carbon::parse($trx['date'])->translatedFormat('d M Y') }}
                    </div>
                    @if(!empty($trx['url']))
                        <span class="trx-open">Lihat detail <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                    @endif
                </div>
                <div class="trx-amt {{ $meta['amt-class'] }} amt">
                    {{ $meta['sign'] }} Rp {{ number_format($trx['nominal'], 0, ',', '.') }}
                </div>
            @if(!empty($trx['url']))</a>@else</div>@endif
        @endforeach
    </div>

    @if($paginator->count() > 0)
        <div class="trx-range">
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}
            dari {{ $paginator->total() }} transaksi
        </div>
    @endif

    @if($paginator->hasPages())
        <div class="trx-paginate">
            {!! $paginator->appends([$otherSection . '_page' => $otherPage])
                         ->fragment($sectionId)
                         ->links('pagination.bootstrap-5-htmx') !!}
        </div>
    @endif

    <div class="trx-foot">
        <span class="lbl">Total {{ $title }}:</span>
        <span class="val {{ $meta['amt-class'] }} amt">
            {{ $meta['sign'] }} Rp {{ number_format($total, 0, ',', '.') }}
        </span>
    </div>
@else
    <div class="cf-empty sm">
        <i class="bi bi-inbox"></i>
        Tidak ada {{ strtolower($title) }} di periode ini
    </div>
@endif