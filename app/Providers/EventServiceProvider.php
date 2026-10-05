protected $listen = [
    \App\Events\CashFlowChanged::class => [
        \App\Listeners\UpdateCashFlowSnapshot::class,
    ],
    // ... listener lain yang sudah ada biarkan saja
];