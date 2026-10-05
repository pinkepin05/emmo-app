<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Access Management - EMMO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --accent-primary: #3b82f6;
            --accent-success: #10b981;
            --accent-purple: #8b5cf6;
            --accent-warning: #f59e0b;
            --accent-pink: #ec4899;
            --accent-indigo: #6366f1;
            --accent-slate: #64748b;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            min-height: 100vh;
            background-image: 
                radial-gradient(at 0% 0%, rgba(139, 92, 246, 0.04) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.04) 0px, transparent 50%);
        }

        /* Navbar */
        .navbar-custom {
            background-color: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            padding: 0.75rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--text-primary) !important;
            letter-spacing: -0.025em;
            font-size: 1.25rem;
        }

        .navbar-brand i { color: var(--accent-primary); }

        .btn-nav {
            font-weight: 500;
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-nav-outline {
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            text-decoration: none;
        }

        .btn-nav-outline:hover {
            background-color: #f1f5f9;
            color: var(--text-primary);
            border-color: #cbd5e1;
        }

        /* Hero Header */
        .hero-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            border-radius: 24px;
            padding: 2.5rem;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .hero-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .hero-header .subtitle {
            font-size: 0.875rem;
            opacity: 0.85;
            margin-bottom: 0.25rem;
            position: relative;
            z-index: 1;
        }

        .hero-header h2 {
            font-weight: 800;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .hero-header p {
            opacity: 0.9;
            margin-bottom: 0;
            position: relative;
            z-index: 1;
            max-width: 700px;
            font-size: 0.9375rem;
        }

        /* Stats */
        .stats-row {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            position: relative;
            z-index: 1;
            flex-wrap: wrap;
        }

        .stat-chip {
            background: rgba(255,255,255,0.18);
            backdrop-filter: blur(10px);
            padding: 0.625rem 1rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.2);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stat-chip .stat-value {
            background: rgba(255,255,255,0.95);
            color: var(--text-primary);
            padding: 0.125rem 0.5rem;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.875rem;
        }

        /* Info Bar */
        .info-bar {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 0.875rem 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.875rem;
        }

        .info-bar i {
            color: var(--accent-primary);
            font-size: 1.125rem;
        }

        /* Role Card */
        .role-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 18px;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        .role-card:hover {
            box-shadow: 0 12px 32px -12px rgba(0,0,0,0.08);
            transform: translateY(-2px);
        }

        .role-card-header {
            padding: 1.25rem 1.5rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .role-card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .role-card-title {
            font-weight: 700;
            font-size: 1.125rem;
            margin: 0 0 0.125rem 0;
            position: relative;
            z-index: 1;
        }

        .role-card-code {
            font-size: 0.75rem;
            opacity: 0.85;
            font-weight: 500;
            font-family: 'SF Mono', Monaco, monospace;
            position: relative;
            z-index: 1;
        }

        .role-meta {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.75rem;
            position: relative;
            z-index: 1;
        }

        .role-meta-item {
            background: rgba(255,255,255,0.2);
            padding: 0.25rem 0.625rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .status-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(239, 68, 68, 0.25);
            color: white;
            padding: 0.25rem 0.625rem;
            border-radius: 8px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            z-index: 2;
        }

        .status-badge.active {
            background: rgba(74, 222, 128, 0.25);
        }

        .role-card-body {
            padding: 1.25rem 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Select All Row */
        .select-all-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.625rem 0.875rem;
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .select-all-row label {
            margin: 0;
            font-weight: 600;
            font-size: 0.8125rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .select-all-row .counter {
            font-size: 0.75rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        /* Permission Group */
        .perm-group {
            margin-bottom: 1rem;
        }

        .perm-group-title {
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            padding-left: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .perm-group-title i {
            color: var(--group-accent, var(--accent-primary));
            font-size: 0.75rem;
        }

        .perm-item {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.5rem 0.625rem;
            border-radius: 8px;
            margin-bottom: 0.25rem;
            transition: background 0.15s ease;
            cursor: pointer;
        }

        .perm-item:hover {
            background: #f8fafc;
        }

        .perm-item input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            flex-shrink: 0;
            accent-color: var(--group-accent, var(--accent-primary));
        }

        .perm-item label {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-primary);
            cursor: pointer;
            flex: 1;
            font-weight: 500;
        }

        .perm-item.critical label::after {
            content: 'CRITICAL';
            font-size: 0.625rem;
            font-weight: 700;
            color: var(--accent-pink);
            margin-left: 0.5rem;
            padding: 0.125rem 0.375rem;
            background: #fdf2f8;
            border-radius: 4px;
            letter-spacing: 0.05em;
        }

        /* Group Accent Colors */
        .group-transaksi { --group-accent: var(--accent-primary); }
        .group-approval { --group-accent: var(--accent-purple); }
        .group-laporan { --group-accent: var(--accent-pink); }
        .group-admin { --group-accent: var(--accent-slate); }

        /* Save Button */
        .role-card-footer {
            padding: 1rem 1.5rem 1.5rem;
            border-top: 1px solid var(--border-color);
            background: #fafbfc;
        }

        .btn-save {
            width: 100%;
            background: var(--text-primary);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-weight: 600;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }

        .btn-save:hover {
            background: #334155;
            transform: translateY(-1px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--bg-card);
            border: 1px dashed var(--border-color);
            border-radius: 18px;
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--border-color);
            margin-bottom: 1rem;
        }

        /* Toast */
        .toast-container {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 9999;
        }

        .custom-toast {
            background: var(--text-primary);
            color: white;
            padding: 0.875rem 1.25rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.3);
            font-size: 0.875rem;
            font-weight: 500;
            min-width: 280px;
            animation: slideIn 0.3s ease;
        }

        .custom-toast i {
            color: var(--accent-success);
            font-size: 1.25rem;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Form Check Custom */
        .form-check-input {
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: var(--group-accent, var(--accent-primary));
            border-color: var(--group-accent, var(--accent-primary));
        }

        @media (max-width: 575.98px) {
            .hero-header { padding: 1.75rem; }
            .hero-header h2 { font-size: 1.375rem; }
            .stats-row { gap: 0.5rem; }
            .stat-chip { padding: 0.5rem 0.75rem; font-size: 0.8125rem; }
        }
    </style>
    @include('partials.mobile-assets')
</head>
<body>
    @include('partials.application-time')

    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center" href="{{ url('/dashboard') }}">
                <i class="bi bi-hexagon-fill me-2"></i> EMMO APP
            </a>
            <div class="ms-auto d-flex align-items-center gap-2">
                <a href="{{ url('/dashboard') }}" class="btn-nav btn-nav-outline">
                    <i class="bi bi-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 py-4">

        {{-- Hero Header --}}
        <div class="hero-header">
            <div class="subtitle"><i class="bi bi-shield-lock"></i> ADMINISTRATOR PANEL</div>
            <h2>Manajemen Hak Akses Fitur</h2>
            <p>
                Atur hak akses menu per <strong>User Right Group (Roles)</strong> sesuai struktur database EMMO.
                Setiap perubahan akan langsung berlaku untuk seluruh user yang tergabung dalam role tersebut.
            </p>
            <div class="stats-row">
                <div class="stat-chip">
                    <i class="bi bi-people-fill"></i>
                    Total Role
                    <span class="stat-value">{{ $roles->count() }}</span>
                </div>
                <div class="stat-chip">
                    <i class="bi bi-key-fill"></i>
                    Total Permission
                    <span class="stat-value">{{ $permissions->count() }}</span>
                </div>
                <div class="stat-chip">
                    <i class="bi bi-person-check-fill"></i>
                    Role Aktif
                    <span class="stat-value">{{ $roles->filter(fn($r) => ($masterActive[$r->name] ?? 1))->count() }}</span>
                </div>
            </div>
        </div>

        {{-- Info Bar --}}
        <div class="info-bar">
            <i class="bi bi-info-circle-fill"></i>
            <div>
                <strong>Tips:</strong> Perubahan hak akses akan langsung berlaku untuk user. Permission bertanda <span style="color: var(--accent-pink); font-weight: 700;">CRITICAL</span> sebaiknya hanya diberikan ke role Administrator.
            </div>
        </div>

        {{-- Success Toast --}}
        @if(session('success'))
        <div class="toast-container">
            <div class="custom-toast">
                <i class="bi bi-check-circle-fill"></i>
                <div>{!! session('success') !!}</div>
            </div>
        </div>
        @endif

        {{-- Grid Role Cards --}}
        @if($roles->isEmpty())
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h5>Belum ada role</h5>
                <p class="text-muted mb-0">Tambahkan role terlebih dahulu di Master Data → Roles.</p>
            </div>
        @else
            <div class="row g-4">
                @foreach($roles as $role)
                    @php
                        $isActive = $masterActive[$role->name] ?? 1;
                        $userCount = $userCounts[$role->name] ?? 0;
                        $permCount = $role->permissions->count();
                        $totalPerm = $permissions->count();
                    @endphp

                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="role-card">
                            {{-- Header --}}
                            <div class="role-card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                @if($isActive)
                                    <span class="status-badge active">Aktif</span>
                                @else
                                    <span class="status-badge">Nonaktif</span>
                                @endif

                                <h3 class="role-card-title">
                                    {{ $masterNames[$role->name] ?? $role->name }}
                                </h3>
                                <div class="role-card-code">{{ $role->name }}</div>

                                <div class="role-meta">
                                    <div class="role-meta-item">
                                        <i class="bi bi-people-fill"></i>
                                        {{ $userCount }} user
                                    </div>
                                    <div class="role-meta-item">
                                        <i class="bi bi-key-fill"></i>
                                        {{ $permCount }}/{{ $totalPerm }}
                                    </div>
                                </div>
                            </div>

                            {{-- Body --}}
                            <form action="{{ route('access-management.update', $role->id) }}" method="POST" class="role-card-form">
                                @csrf
                                @method('PUT')

                                <div class="role-card-body">

                                    {{-- Select All Row --}}
                                    <div class="select-all-row">
                                        <label for="selectAll_{{ $role->id }}">
                                            <input class="form-check-input" type="checkbox"
                                                   id="selectAll_{{ $role->id }}"
                                                   onchange="toggleAll(this, '{{ $role->id }}')">
                                            Pilih Semua
                                        </label>
                                        <span class="counter" id="counter_{{ $role->id }}">
                                            {{ $permCount }}/{{ $totalPerm }}
                                        </span>
                                    </div>

                                    {{-- GROUP: Transaksi --}}
                                    @php
                                        $groups = [
                                            'transaksi' => [
                                                'title' => 'Transaksi',
                                                'icon' => 'bi-graph-up-arrow',
                                                'class' => 'group-transaksi',
                                                'keywords' => ['pengajuan', 'transaksi', 'event'],
                                            ],
                                            'approval' => [
                                                'title' => 'Approval',
                                                'icon' => 'bi-person-check',
                                                'class' => 'group-approval',
                                                'keywords' => ['approval'],
                                            ],
                                            'laporan' => [
                                                'title' => 'Laporan & Cash Flow',
                                                'icon' => 'bi-file-earmark-text',
                                                'class' => 'group-laporan',
                                                'keywords' => ['laporan', 'cash'],
                                            ],
                                            'admin' => [
                                                'title' => 'Administrasi',
                                                'icon' => 'bi-gear',
                                                'class' => 'group-admin',
                                                'keywords' => ['master', 'access'],
                                            ],
                                        ];
                                    @endphp

                                    @foreach($groups as $groupKey => $group)
                                        @php
                                            $groupPerms = $permissions->filter(function($p) use ($group) {
                                                foreach ($group['keywords'] as $keyword) {
                                                    if (str_contains($p->name, $keyword)) return true;
                                                }
                                                return false;
                                            });
                                        @endphp

                                        @if($groupPerms->isNotEmpty())
                                        <div class="perm-group {{ $group['class'] }}">
                                            <div class="perm-group-title">
                                                <i class="bi {{ $group['icon'] }}"></i>
                                                {{ $group['title'] }}
                                            </div>

                                            @foreach($groupPerms as $permission)
                                                @php
                                                    $label = str_replace(['akses.', '-', '_', '.'], ['', ' ', ' ', ' '], $permission->name);
                                                    $label = ucwords($label);
                                                    $isCritical = str_contains($permission->name, 'access-management') || str_contains($permission->name, 'master-data');
                                                @endphp
                                                <div class="perm-item {{ $isCritical ? 'critical' : '' }}">
                                                    <input class="form-check-input permission-checkbox"
                                                           type="checkbox"
                                                           name="permissions[]"
                                                           value="{{ $permission->name }}"
                                                           id="perm_{{ $role->id }}_{{ $permission->id }}"
                                                           data-role="{{ $role->id }}"
                                                           {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                                    <label for="perm_{{ $role->id }}_{{ $permission->id }}">
                                                        {{ $label }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        @endif
                                    @endforeach

                                </div>

                                <div class="role-card-footer">
                                    <button type="submit" class="btn-save">
                                        <i class="bi bi-check2-circle"></i>
                                        Simpan Hak Akses
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleAll(checkbox, roleId) {
            document.querySelectorAll('.permission-checkbox[data-role="' + roleId + '"]').forEach(function(cb) {
                cb.checked = checkbox.checked;
            });
            updateCounter(roleId);
        }

        function updateCounter(roleId) {
            var all = document.querySelectorAll('.permission-checkbox[data-role="' + roleId + '"]');
            var checked = document.querySelectorAll('.permission-checkbox[data-role="' + roleId + '"]:checked');
            var counter = document.getElementById('counter_' + roleId);
            var selectAll = document.getElementById('selectAll_' + roleId);

            if (counter) counter.textContent = checked.length + '/' + all.length;
            if (selectAll) {
                selectAll.checked = checked.length === all.length;
                selectAll.indeterminate = checked.length > 0 && checked.length < all.length;
            }
        }

        document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                updateCounter(this.dataset.role);
            });
        });

        // Init counters on page load
        document.querySelectorAll('.permission-checkbox').forEach(function(cb) {
            var roleId = cb.dataset.role;
            if (!document.querySelector('[data-init="' + roleId + '"]')) {
                updateCounter(roleId);
                var marker = document.createElement('span');
                marker.dataset.init = roleId;
                document.body.appendChild(marker);
            }
        });

        // Auto-hide toast after 4s
        setTimeout(function() {
            var toast = document.querySelector('.custom-toast');
            if (toast) {
                toast.style.transition = 'opacity 0.3s ease';
                toast.style.opacity = '0';
                setTimeout(function() { toast.remove(); }, 300);
            }
        }, 4000);
    </script>
</body>
</html>
