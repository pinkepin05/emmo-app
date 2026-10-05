<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail & Riwayat BS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg-body: #f8fafc; --border-color: #e2e8f0; --text-primary: #0f172a; --text-secondary: #64748b; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); color: var(--text-primary); }
        .card-custom { background: #fff; border: 1px solid var(--border-color); border-radius: 16px; padding: 2rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .btn-nav { background-color: white; border: 1px solid var(--border-color); color: var(--text-secondary); font-weight: 500; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; }

        /* Timeline Styles */
        .timeline { position: relative; padding: 1rem 0; }
        .timeline::before { content: ''; position: absolute; left: 20px; top: 0; bottom: 0; width: 2px; background: #e2e8f0; }
        .timeline-item { position: relative; padding-left: 60px; margin-bottom: 2rem; }
        .timeline-icon { position: absolute; left: 0; top: 0; width: 42px; height: 42px; border-radius: 50%; background: #fff; border: 2px solid #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; z-index: 1; }

        .icon-created { border-color: #3b82f6; color: #3b82f6; background: #eff6ff; }
        .icon-approved { border-color: #10b981; color: #10b981; background: #ecfdf5; }
        .icon-rejected { border-color: #ef4444; color: #ef4444; background: #fef2f2; }
        .icon-disbursed { border-color: #f59e0b; color: #f59e0b; background: #fffbeb; }
        .icon-settled { border-color: #6366f1; color: #6366f1; background: #eef2ff; }
        .icon-default { border-color: #64748b; color: #64748b; background: #f8fafc; }

        .timeline-content { background: #fff; border: 1px solid var(--border-color); border-radius: 12px; padding: 1rem 1.25rem; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .timeline-title { font-weight: 700; font-size: 1rem; margin-bottom: 0.25rem; }
        .timeline-meta { font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 0.5rem; }
        .timeline-desc { font-size: 0.9rem; color: var(--text-primary); }
        .timeline-nominal { font-weight: 700; color: #059669; font-size: 0.95rem; margin-top: 0.25rem; }

        /* Member Badge */
        .member-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            background: #eef2ff;
            color: #4f46e5;
            padding: 0.375rem 0.75rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid #c7d2fe;
        }
        .member-badge i { font-size: 0.85rem; }

        /* Tipe Badge */
        .badge-tipe { padding: 0.45em 0.85em; border-radius: 6px; font-weight: 600; font-size: 0.75rem; }
        .report-amount { font-size: 1.1rem; font-weight: 700; }
        .report-table th, .report-table td { vertical-align: top; white-space: nowrap; }
        .report-table .detail-cell { white-space: normal; min-width: 210px; }
        .report-table details { font-size: 0.85rem; }
        .report-table summary { color: #2563eb; cursor: pointer; }
        .report-table .detail-row { display: grid; grid-template-columns: 1fr auto; gap: 0.75rem; border-top: 1px solid var(--border-color); padding: 0.35rem 0; }
    </style>
</head>
<body class="py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 1100px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">Riwayat Pengajuan BS</h3>
            <a href="{{ route('pengajuan-bs.index') }}" class="btn-nav"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>

        {{-- Info Header BS --}}
        <div class="card-custom mb-4">
            <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1">{{ $bs->document_no }}</h5>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        <span class="badge bg-secondary">{{ $bs->periode }}</span>

                        {{-- 🎯 BARU: Badge Tipe Pengajuan --}}
                        @if(($bs->tipe_pengajuan ?? 'operasional_sales') === 'perjalanan_dinas')
                            <span class="badge-tipe" style="background: #ecfdf5; color: #047857;">
                                <i class="bi bi-airplane-fill me-1"></i>Perjalanan Dinas
                            </span>
                        @else
                            <span class="badge-tipe" style="background: #eff6ff; color: #1d4ed8;">
                                <i class="bi bi-briefcase-fill me-1"></i>Operasional Sales
                            </span>
                        @endif

                        <span class="badge
                            @if($bs->status == 'DISBURSED') bg-success
                            @elseif($bs->status == 'REJECTED_ATASAN') bg-danger
                            @elseif($bs->status == 'PENDING_ATASAN') bg-warning text-dark
                            @elseif($bs->status == 'SETTLED') bg-dark
                            @else bg-info @endif">
                            {{ str_replace('_', ' ', $bs->status) }}
                        </span>
                    </div>
                </div>
                <div class="text-end">
                    <div class="text-muted small">Diajukan oleh</div>
                    <strong>{{ $bs->user->nama ?? '-' }}</strong>
                    <div class="text-muted small">Diajukan {{ \App\Support\AppTime::format($bs->created_at) }}</div>
                </div>
            </div>
            <hr>
            <div class="row text-center">
                <div class="col-4">
                    <div class="text-muted small">Nominal Pengajuan</div>
                    <strong>Rp {{ number_format($bs->nominal_pengajuan, 0, ',', '.') }}</strong>
                </div>
                <div class="col-4">
                    <div class="text-muted small">Saldo BS Gabungan Saat Pengajuan</div>
                    <strong>Rp {{ number_format($bs->nominal_sisa_bs_snapshot, 0, ',', '.') }}</strong>
                </div>
                <div class="col-4">
                    <div class="text-muted small">Nominal Cair</div>
                    <strong class="text-success">Rp {{ number_format($bs->nominal_approved, 0, ',', '.') }}</strong>
                </div>
            </div>

            @if($bs->description)
                <div class="mt-3 p-3 bg-light rounded">
                    <strong class="small text-muted d-block mb-1">📝 Deskripsi / Keperluan:</strong>
                    {{ $bs->description }}
                </div>
            @endif

            {{-- 🎯 ANGGOTA GRUP DINAS (hanya relevan untuk Perjalanan Dinas) --}}
            @if($bs->members && $bs->members->count() > 0)
                <div class="mt-3 p-3 rounded" style="background: linear-gradient(135deg, #f0f4ff 0%, #e8efff 100%); border: 1px dashed #a5b4fc;">
                    <strong class="small d-block mb-2" style="color: #4338ca;">
                        <i class="bi bi-people-fill me-1"></i> Anggota Grup Dinas:
                    </strong>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($bs->members as $m)
                            <span class="member-badge">
                                <i class="bi bi-person-badge"></i>
                                {{ $m->nama ?? $m->name ?? 'User #' . $m->id }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 🎯 INFO SETTLEMENT --}}
            @if($bs->status === 'SETTLED')
                <div class="mt-3 p-3 rounded" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #86efac;">
                    <strong class="small d-block mb-1" style="color: #166534;">
                        <i class="bi bi-check-circle-fill me-1"></i> Status Penyelesaian:
                    </strong>
                    <div class="small" style="color: #15803d;">
                        @if($bs->nominal_pengembalian > 0)
                            ✅ User menyetor kembali <strong>Rp {{ number_format($bs->nominal_pengembalian, 0, ',', '.') }}</strong> ke kas perusahaan
                        @elseif($bs->nominal_reimburse > 0)
                            ✅ Kas me-reimburse <strong>Rp {{ number_format($bs->nominal_reimburse, 0, ',', '.') }}</strong> ke user
                        @else
                            ✅ Lunas tanpa selisih
                        @endif
                        <span class="text-muted ms-1">• Ditutup pada {{ \App\Support\AppTime::format($bs->settled_at) }} (Periode {{ $bs->settled_periode }})</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Timeline Riwayat --}}
        <h5 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Jejak Audit (Audit Trail)</h5>
        <div class="card-custom">
            <div class="timeline">
                @forelse($logs as $log)
                    @php
                        $iconClass = 'icon-default';
                        $icon = 'bi-circle';
                        if ($log->activity_code == 'CREATED') { $iconClass = 'icon-created'; $icon = 'bi-pencil-square'; }
                        elseif ($log->activity_code == 'APPROVED_ATASAN') { $iconClass = 'icon-approved'; $icon = 'bi-check-circle'; }
                        elseif ($log->activity_code == 'REJECTED_ATASAN') { $iconClass = 'icon-rejected'; $icon = 'bi-x-circle'; }
                        elseif ($log->activity_code == 'DISBURSED') { $iconClass = 'icon-disbursed'; $icon = 'bi-cash-coin'; }
                        elseif ($log->activity_code == 'SETTLED') { $iconClass = 'icon-settled'; $icon = 'bi-archive-fill'; }
                    @endphp

                    <div class="timeline-item">
                        <div class="timeline-icon {{ $iconClass }}">
                            <i class="bi {{ $icon }}"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-title">{{ $log->activity_name }}</div>
                            <div class="timeline-meta">
                                Oleh: <strong>{{ $log->actor->nama ?? 'System' }}</strong>
                                &bull; {{ \App\Support\AppTime::format($log->created_at) }}
                            </div>
                            @if($log->description)
                                <div class="timeline-desc">{{ $log->description }}</div>
                            @endif
                            @if($log->nominal)
                                <div class="timeline-nominal">
                                    <i class="bi bi-cash-stack me-1"></i> Rp {{ number_format($log->nominal, 0, ',', '.') }}
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center mb-0">Belum ada riwayat.</p>
                @endforelse
            </div>
        </div>
    </div>
</body>
</html>
