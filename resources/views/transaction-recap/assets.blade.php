@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/transaction-recap.css') }}?v={{ filemtime(public_path('css/transaction-recap.css')) }}">
        <link rel="stylesheet" href="{{ asset('css/transaction-recap-enhanced.css') }}?v=20260929">
        <link rel="stylesheet" href="{{ asset('css/transaction-recap-polish.css') }}?v=20261001">
    @endpush
    @push('scripts')
        <script src="{{ asset('js/transaction-recap-modules.js') }}?v={{ filemtime(public_path('js/transaction-recap-modules.js')) }}" defer></script>
        <script src="{{ asset('js/transaction-recap-ux.js') }}?v={{ filemtime(public_path('js/transaction-recap-ux.js')) }}" defer></script>
    @endpush
@endonce