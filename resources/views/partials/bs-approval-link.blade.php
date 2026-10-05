@canany(['akses.pengajuan-bs', 'akses.transaksi-perjalanan-dinas', 'akses.cash-flow'])
    @php($pendingBsUsage = \App\Models\TransaksiPerjalananDinasHeader::forBsOwner((int) auth()->id())->where('user_id', '<>', auth()->id())->where('status', 'PENDING_OWNER')->count())
    <div class="my-2"><a href="{{ route('bs-usage-approvals.index') }}" class="btn btn-sm btn-outline-primary">Persetujuan Pemakaian BS <span class="badge bg-primary">{{ $pendingBsUsage }} menunggu</span></a></div>
@endcanany
