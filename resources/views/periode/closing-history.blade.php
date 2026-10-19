@if($closingHistory->isNotEmpty())
    <section class="card-x mb-4" id="period-closing-history" aria-labelledby="closing-history-title">
        <div class="p-3 border-bottom"><h2 class="h5 fw-bold mb-0" id="closing-history-title">Riwayat Penutupan</h2></div>
        @foreach($closingHistory as $closing)
            <div class="p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                    <strong>{{ $closing->mode === 'EXCLUDE_PENDING' ? 'Tutup dengan Pengecualian' : 'Tutup Buku' }}</strong>
                    <span class="small text-muted">{{ \App\Support\AppTime::format($closing->created_at) }} · {{ $closing->actor_name ?? 'Sistem' }}</span>
                </div>
                @if($closing->reason)<p class="small mb-2"><strong>Alasan:</strong> {{ $closing->reason }}</p>@endif
                @if(count($closing->excluded_transactions))
                    <p class="small mb-2">{{ count($closing->excluded_transactions) }} transaksi dikecualikan dari pengeluaran saat penutupan ini. Data berikut merupakan catatan pada saat tutup buku.</p>
                    <details @if($loop->first && $status === 'CLOSED') open @endif>
                        <summary class="small fw-semibold mb-2">Transaksi yang dikecualikan</summary>
                        <div class="table-responsive">
                            <table class="table-x w-100">
                                <thead><tr><th scope="col">Transaksi / Modul</th><th scope="col">Pemilik</th><th scope="col">Status saat ditutup</th><th scope="col" class="text-end">Nominal pengajuan</th></tr></thead>
                                <tbody>
                                    @foreach($closing->excluded_transactions as $excluded)
                                        <tr>
                                            <td><strong>{{ $excluded['reference'] }}</strong><div class="small text-muted">{{ $excluded['module'] }}</div></td>
                                            <td>{{ $excluded['user_name'] }}</td>
                                            <td>{{ $excluded['reason'] }}</td>
                                            <td class="text-end text-nowrap">Rp {{ number_format($excluded['amount'], 2, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                @endif
            </div>
        @endforeach
    </section>
@endif
