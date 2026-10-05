@php
    $applicationNow = \App\Support\AppTime::now();
    $applicationTimeMode = $mode ?? 'bar';
@endphp

@if($applicationTimeMode === 'bar')
    <header class="application-time-bar" aria-label="Tanggal dan waktu aplikasi">
@endif

<time
    class="application-live-time application-live-time--{{ $applicationTimeMode }}"
    data-application-live-time
    data-server-epoch="{{ $applicationNow->getTimestamp() * 1000 }}"
    data-time-zone="{{ \App\Support\AppTime::timezone() }}"
    datetime="{{ $applicationNow->toIso8601String() }}"
    role="timer"
    aria-live="off"
    aria-label="{{ $applicationNow->format('F - j - Y, g:i:s a') }}"
>
    <span class="application-live-time__date">{{ $applicationNow->format('F - j - Y') }}</span>
    <span class="application-live-time__divider" aria-hidden="true"></span>
    <span class="application-live-time__clock">{{ $applicationNow->format('g:i:s a') }}</span>
</time>

@if($applicationTimeMode === 'bar')
    @auth
        @include('partials.notification-bell')
    @endauth
    </header>
@endif

@once
    <style>
        .application-time-bar {
            position: relative;
            z-index: 40;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.75rem;
            min-height: 56px;
            padding: 0.625rem 1.5rem;
            box-sizing: border-box;
        }

        .application-time-bar .notif-wrap {
            position: relative;
            top: auto;
            right: auto;
            flex: 0 0 auto;
        }

        .application-live-time {
            display: inline-flex;
            align-items: center;
            gap: 0.625rem;
            min-height: 38px;
            padding: 0.5rem 0.75rem;
            color: var(--text-secondary, #64748b);
            font-family: inherit;
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.01em;
            line-height: 1;
            white-space: nowrap;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 11px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 6px 18px rgba(15, 23, 42, 0.05);
            box-sizing: border-box;
        }

        .application-live-time__date {
            color: var(--text-secondary, #64748b);
            font-size: 0.775rem;
            font-weight: 600;
        }

        .application-live-time__divider {
            width: 1px;
            height: 18px;
            background: var(--border-color, #e2e8f0);
            border-radius: 999px;
            flex: 0 0 1px;
        }

        .application-live-time__clock {
            min-width: 76px;
            color: var(--text-primary, #0f172a);
            font-size: 0.825rem;
            font-weight: 700;
            text-align: center;
        }

        .application-live-time--inline {
            align-self: stretch;
            margin-right: 0.375rem;
            background: rgba(248, 250, 252, 0.92);
            box-shadow: none;
        }

        .application-live-time--plain {
            padding: 0;
            background: transparent;
            border: 0;
            border-radius: 0;
            box-shadow: none;
        }

        @media (max-width: 767.98px) {
            .topbar {
                height: auto;
                min-height: var(--topbar-height, 64px);
                padding-top: 0.5rem;
                padding-bottom: 0.5rem;
            }

            .topbar-actions {
                flex-wrap: wrap;
                justify-content: flex-end;
                max-width: min(260px, calc(100vw - 5rem));
            }

            .application-live-time--inline {
                order: 10;
                flex-basis: 100%;
                justify-content: flex-end;
                align-self: auto;
                margin: 0;
                min-height: 32px;
                padding: 0.375rem 0.5rem;
            }

            .application-time-bar {
                min-height: 48px;
                padding: 0.5rem 0.75rem;
                gap: 0.625rem;
            }

            .application-time-bar .application-live-time {
                min-width: 0;
                flex: 0 1 auto;
                flex-wrap: wrap;
                justify-content: center;
                row-gap: 0.375rem;
            }

            .application-time-bar .application-live-time__divider {
                display: none;
            }

            .application-live-time__date {
                font-size: 0.7rem;
            }

            .application-live-time__clock {
                min-width: 68px;
                font-size: 0.75rem;
            }
        }

        .login-header .application-live-time {
            justify-content: center;
            margin-top: 1rem;
        }

        .login-header .application-live-time__date,
        .login-header .application-live-time__clock {
            color: rgba(255, 255, 255, 0.92);
        }

        .login-header .application-live-time__divider {
            background: rgba(255, 255, 255, 0.35);
        }
    </style>
    <script src="{{ asset('js/application-time.js') }}" defer></script>
@endonce
