@php
    $moduleActive = request()->routeIs($routePrefix . '.*');
    $creating = request()->routeIs($routePrefix . '.create');
    $listing = request()->routeIs($routePrefix . '.index');
@endphp

<details class="sidebar-module" @if($moduleActive) open @endif>
    <summary class="sidebar-link sidebar-module-toggle {{ $moduleActive ? 'is-current' : '' }}">
        <i class="bi {{ $icon }}" aria-hidden="true"></i>
        <span>{{ $label }}</span>
        <i class="bi bi-chevron-down sidebar-module-chevron" aria-hidden="true"></i>
    </summary>
    <div class="sidebar-submenu">
        <a href="{{ route($routePrefix . '.create') }}"
           class="sidebar-link {{ $creating ? 'active' : '' }}"
           @if($creating) aria-current="page" @endif>
            <i class="bi bi-plus-circle" aria-hidden="true"></i>
            <span>{{ $createLabel ?? 'Tambah Transaksi' }}<span class="visually-hidden"> — {{ $label }}</span></span>
        </a>
        <a href="{{ route($routePrefix . '.index') }}"
           class="sidebar-link {{ $moduleActive && !$creating ? 'active' : '' }}"
           @if($listing) aria-current="page" @endif>
            <i class="bi bi-list-ul" aria-hidden="true"></i>
            <span>{{ $listLabel ?? 'Daftar Transaksi' }}<span class="visually-hidden"> — {{ $label }}</span></span>
        </a>
    </div>
</details>
