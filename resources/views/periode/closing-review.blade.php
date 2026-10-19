<section id="period-closing-review" class="card-x mb-4" aria-labelledby="closing-review-title">
    <div class="p-3 border-bottom">
        <h2 id="closing-review-title" class="h5 fw-bold mb-2">Pemeriksaan sebelum tutup buku</h2>
        <p class="small text-muted mb-0">Periode {{ $periode }} · Mencakup transaksi seluruh pengguna. Operasional Sales dan Perjalanan Dinas mengikuti periode pembukuan yang tersimpan, meskipun tanggal transaksinya berada pada bulan lain.</p>
    </div>
    @if($closingBlockers->isNotEmpty())
        <div class="p-3">
            <div class="alert alert-warning mb-0" role="status">
                <strong>{{ $closingBlockers->count() }} transaksi masih menghalangi tutup buku.</strong>
                <div class="small mt-1">
                    {{ $closingBlockers->where('stage', 'DRAFT')->count() }} draft ·
                    {{ $closingBlockers->where('stage', 'PENDING_OWNER')->count() }} menunggu pemilik saldo ·
                    {{ $closingBlockers->where('stage', 'FINANCE_PENDING')->count() }} belum final di Finance.
                    Buka detail untuk meninjau dan menyelesaikan masing-masing transaksi.
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table-x w-100 mb-0">
                <thead><tr><th scope="col">Transaksi / Modul</th><th scope="col">Tanggal</th><th scope="col">Pemilik transaksi</th><th scope="col" class="text-end">Nominal pengajuan</th><th scope="col">Penghambat</th><th scope="col">Aksi</th></tr></thead>
                <tbody>
                    @foreach($closingBlockers as $blocker)
                        <tr>
                            <td><strong>{{ $blocker['reference'] }}</strong><div class="small text-muted">{{ $blocker['module'] }}</div></td>
                            <td class="text-nowrap">{{ \Carbon\Carbon::parse($blocker['date'])->format('d/m/Y') }}</td>
                            <td>{{ $blocker['user_name'] }}</td>
                            <td class="text-end text-nowrap">Rp {{ number_format($blocker['amount'], 2, ',', '.') }}</td>
                            <td>{{ $blocker['reason'] }}</td>
                            <td>
                                @if(auth()->user()->can('akses.approval-finance') || auth()->user()->canSeeAllTransactions() || (int) auth()->id() === $blocker['user_id'])
                                    <a class="btn btn-sm btn-outline-primary text-nowrap" href="{{ route('transaction-recap.show', ['jenis' => $blocker['type'], 'source' => $blocker['id'], 'pemakaian_periode' => $periode, 'dari' => 'periode']) }}">Detail Transaksi</a>
                                @else
                                    <span class="small text-muted">Hubungi pemilik transaksi atau Finance.</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="p-3">
            <p class="mb-2">Tidak ada transaksi yang masih menunggu konfirmasi atau pemeriksaan Finance.</p>
            <p class="small text-muted mb-0">Urutan periode dan kesiapan saldo akan diperiksa kembali saat penutupan.</p>
        </div>
    @endif
    @if($canAttemptClose && $closingBlockers->isEmpty())
        <div class="p-3 border-top">
            <form action="{{ route('periode.close', $row->id) }}" method="POST" onsubmit="return confirm('Tutup buku periode {{ $periode }}? Transaksi periode ini akan dibekukan.');">
                @csrf
                <button class="btn btn-danger" type="submit"><i class="bi bi-lock-fill me-1" aria-hidden="true"></i>Tutup Buku {{ $periode }}</button>
            </form>
        </div>
    @elseif($canAttemptClose)
        <div class="p-3 border-top bg-light">
            <h3 class="h6 fw-bold">Tetap tutup periode</h3>
            <p class="small mb-3" id="closing-exclusion-help">{{ $closingBlockers->count() }} transaksi di atas akan tetap tersimpan dan dikunci pada periode {{ $periode }}. Nominalnya tidak dihitung sebagai pengeluaran penutupan dan transaksinya tidak dipindahkan ke bulan berikutnya. Hanya saldo akhir yang diteruskan. Pembukaan kembali tetap mengikuti aturan periode.</p>
            <form action="{{ route('periode.close', $row->id) }}" method="POST" id="close-with-exclusions" aria-describedby="closing-exclusion-help">
                @csrf
                <input type="hidden" name="exclude_pending" value="1">
                <input type="hidden" name="review_token" value="{{ $closingReviewToken }}">
                <label for="closing-reason" class="form-label fw-semibold">Alasan penutupan dengan pengecualian</label>
                <textarea id="closing-reason" name="reason" class="form-control mb-3" rows="2" minlength="5" maxlength="1000" required placeholder="Jelaskan alasan transaksi tertunda dikecualikan">{{ old('reason') }}</textarea>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="confirm_exclusions" value="1" id="confirm-exclusions" required>
                    <label class="form-check-label small" for="confirm-exclusions">Saya setuju mengecualikan dan mengunci transaksi di atas tanpa mengurangi saldo penutupan.</label>
                </div>
                <button class="btn btn-danger" type="submit"><i class="bi bi-lock-fill me-1" aria-hidden="true"></i>Tutup dengan Pengecualian</button>
            </form>
        </div>
    @elseif(!$canAttemptClose)
        <p class="small text-muted p-3 mb-0">Tutup buku tersedia setelah bulan periode berakhir.</p>
    @endif
</section>
