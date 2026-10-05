{{-- =====================================================
     Design System bersama untuk modul Cash Flow (index & show)
     ===================================================== --}}
<style>
    :root{
        --bg:#f4f5f7;
        --surface:#ffffff;
        --surface-2:#f8fafc;
        --ink:#0f172a;
        --ink-2:#64748b;
        --ink-3:#94a3b8;
        --line:#e2e8f0;
        --line-2:#eef2f6;

        --blue:#2563eb;  --blue-soft:#eff6ff;
        --green:#059669; --green-soft:#ecfdf5;
        --red:#dc2626;   --red-soft:#fef2f2;
        --amber:#b45309; --amber-soft:#fffbeb;

        --r-lg:16px; --r-md:12px; --r-sm:10px;
        --sh-sm:0 1px 2px rgba(16,24,40,.05);
        --sh-md:0 10px 24px -8px rgba(16,24,40,.14);
    }

    body.cf-body{
        font-family:'Plus Jakarta Sans',system-ui,sans-serif;
        background:var(--bg);
        color:var(--ink);
        min-height:100vh;
        padding-bottom:4rem;
        -webkit-font-smoothing:antialiased;
    }

    .cf-wrap{max-width:1080px;margin-inline:auto;padding:1.5rem 1rem 0;}
    .cf-wrap.narrow{max-width:920px;}

    /* Angka monospace agar kolom Rupiah rapi */
    .amt{font-variant-numeric:tabular-nums lining-nums;letter-spacing:-.01em;white-space:nowrap;}

    /* ---------- Page header ---------- */
    .cf-header{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;margin-bottom:1.75rem;}
    .cf-title{font-size:1.65rem;font-weight:800;letter-spacing:-.03em;margin:0 0 .3rem;}
    .cf-subtitle{font-size:.9rem;color:var(--ink-2);margin:0;max-width:62ch;line-height:1.55;}
    .cf-actions{display:flex;gap:.6rem;flex-wrap:wrap;}

    .btn-cf{display:inline-flex;align-items:center;gap:.5rem;border-radius:10px;font-weight:600;font-size:.85rem;
            padding:.55rem 1rem;text-decoration:none;transition:.18s ease;border:1px solid transparent;}
    .btn-cf i{font-size:.95rem;}
    .btn-cf.primary{background:var(--green);color:#fff;box-shadow:var(--sh-sm);}
    .btn-cf.primary:hover{background:#047857;color:#fff;transform:translateY(-1px);}
    .btn-cf.ghost{background:var(--surface);border-color:var(--line);color:var(--ink-2);}
    .btn-cf.ghost:hover{background:var(--surface-2);color:var(--ink);border-color:#cbd5e1;}

    /* ---------- Card shell ---------- */
    .cf-card{background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);
             box-shadow:var(--sh-sm);overflow:hidden;margin-bottom:1.25rem;}
    .cf-card.accent{border-left:4px solid var(--blue);}

    .cf-card-head{display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;
                  padding:1.15rem 1.5rem;background:linear-gradient(90deg,var(--surface-2),#fff);border-bottom:1px solid var(--line);}
    .cf-head-title{display:flex;align-items:center;gap:.7rem;margin:0;font-size:1.02rem;font-weight:700;}
    .cf-head-title .sub{font-size:.78rem;font-weight:500;color:var(--ink-2);}
    .cf-badge{background:#e8edf3;color:#475569;font-size:.7rem;font-weight:700;padding:.22rem .6rem;border-radius:999px;white-space:nowrap;}

    .cf-ico{width:34px;height:34px;border-radius:9px;display:inline-flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0;}
    .ico-blue{background:var(--blue-soft);color:var(--blue);}
    .ico-green{background:var(--green-soft);color:var(--green);}
    .ico-red{background:var(--red-soft);color:var(--red);}
    .ico-amber{background:var(--amber-soft);color:var(--amber);}
    .ico-slate{background:#f1f5f9;color:var(--ink-2);}

    /* ---------- Status pill ---------- */
    .pill{display:inline-flex;align-items:center;gap:.4rem;border-radius:999px;font-size:.7rem;font-weight:800;
          letter-spacing:.06em;padding:.34rem .75rem;text-transform:uppercase;}
    .pill.open{background:var(--green-soft);color:var(--green);}
    .pill.closed{background:#f1f5f9;color:var(--ink-2);}
    .pill .dot{width:6px;height:6px;border-radius:50%;background:currentColor;animation:cf-pulse 2s infinite;}
    @keyframes cf-pulse{0%{box-shadow:0 0 0 0 rgba(5,150,105,.45)}70%{box-shadow:0 0 0 6px rgba(5,150,105,0)}100%{box-shadow:0 0 0 0 rgba(5,150,105,0)}}

    /* ---------- Banners ---------- */
    .cf-banner{display:flex;align-items:center;gap:.55rem;flex-wrap:wrap;font-size:.83rem;padding:.7rem 1.5rem;border-top:1px solid var(--line);}
    .cf-banner.warn{background:var(--amber-soft);border-top-color:#fde68a;color:#92400e;}
    .cf-banner.neutral{background:var(--surface-2);color:var(--ink-2);}

    /* ---------- Summary stats ---------- */
    .eyebrow{font-size:.68rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-3);margin-bottom:.15rem;}
    .period-lg{font-size:1.45rem;font-weight:800;letter-spacing:-.02em;margin:0;}
    .period-lg span{font-size:.9rem;font-weight:500;color:var(--ink-2);}

    .cf-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:1.1rem;padding:1.35rem 1.5rem;}
    .cf-stat{display:flex;flex-direction:column;gap:.3rem;min-width:0;}
    .cf-stat .cf-ico{width:30px;height:30px;border-radius:8px;margin-bottom:.4rem;}
    .cf-stat .lbl{font-size:.74rem;font-weight:700;color:var(--ink-2);text-transform:uppercase;letter-spacing:.05em;}
    .cf-stat .val{font-size:1.22rem;font-weight:800;letter-spacing:-.02em;overflow-wrap:anywhere;}
    .cf-stat .val.pos{color:var(--green);} .cf-stat .val.neg{color:var(--red);} .cf-stat .val.neu{color:var(--ink);}
    @media(min-width:768px){ .cf-stat + .cf-stat{border-left:1px solid var(--line-2);padding-left:1.1rem;} }

    /* ---------- Breakdown grid (partial expense-breakdown) ---------- */
    .bd-wrap{padding:0 1.5rem 1.4rem;}
    .bd-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:.6rem;}
    .bd-cell{background:var(--surface-2);border:1px solid var(--line-2);border-left:3px solid var(--ink-3);
             border-radius:var(--r-sm);padding:.6rem .8rem;display:flex;flex-direction:column;gap:.15rem;min-width:0;}
    .bd-cell .bd-label{font-size:.66rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-3);}
    .bd-cell .bd-value{font-size:.92rem;font-weight:800;color:var(--ink);}
    .bd-green{border-left-color:var(--green);} .bd-green .bd-value{color:var(--green);}
    .bd-blue{border-left-color:var(--blue);}   .bd-blue  .bd-value{color:var(--blue);}
    .bd-red{border-left-color:var(--red);}     .bd-red   .bd-value{color:var(--red);}
    .bd-amber{border-left-color:#f59e0b;}
    .bd-slate{border-left-color:#94a3b8;}
    .bd-note{display:flex;align-items:center;gap:.45rem;font-size:.78rem;color:var(--ink-3);margin:.7rem 0 0;}

    /* ---------- History list (index) ---------- */
    .cf-section-head{display:flex;align-items:center;gap:.6rem;margin:2rem 0 1rem;}
    .cf-section-head h3{margin:0;font-size:1.05rem;font-weight:800;letter-spacing:-.01em;display:flex;align-items:center;gap:.55rem;}
    .cf-section-head h3 i{color:var(--ink-3);}

    .h-card{background:var(--surface);border:1px solid var(--line);border-radius:var(--r-md);padding:1.1rem 1.25rem;margin-bottom:.9rem;transition:.18s ease;}
    .h-card:hover{border-color:#cbd5e1;box-shadow:var(--sh-md);transform:translateY(-2px);}
    .h-top{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;margin-bottom:.9rem;}
    .h-id{display:flex;gap:.9rem;align-items:center;min-width:0;}
    .h-id .cf-ico{width:40px;height:40px;border-radius:11px;font-size:1.05rem;}
    .h-title{font-size:1rem;font-weight:800;margin:0;letter-spacing:-.01em;}
    .h-title span{font-weight:500;color:var(--ink-2);font-size:.82rem;}
    .h-meta{display:flex;align-items:center;gap:.4rem;font-size:.74rem;color:var(--ink-3);margin-top:.2rem;}
    .h-actions{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}
    .btn-detail{display:inline-flex;align-items:center;gap:.4rem;background:var(--blue);color:#fff;border-radius:8px;
                font-size:.8rem;font-weight:700;padding:.42rem .85rem;text-decoration:none;transition:.15s;}
    .btn-detail:hover{background:#1d4ed8;color:#fff;}
    .btn-icon{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border:1px solid var(--line);
              border-radius:8px;color:var(--ink-2);background:transparent;text-decoration:none;transition:.15s;}
    .btn-icon:hover{color:var(--green);border-color:#bbf7d0;background:var(--green-soft);}

    .h-alert{display:flex;gap:.5rem;align-items:center;background:var(--amber-soft);border:1px solid #fde68a;color:#92400e;
             border-radius:8px;font-size:.78rem;padding:.45rem .7rem;margin-bottom:.8rem;}

    .h-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:.9rem;background:var(--surface-2);
             border:1px solid var(--line-2);border-radius:var(--r-sm);padding:.7rem .95rem;}
    @media(min-width:768px){ .h-stats.with-end{grid-template-columns:repeat(4,1fr);} }
    .h-stat{display:flex;flex-direction:column;gap:.1rem;min-width:0;}
    .h-stat .lbl{font-size:.64rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-3);}
    .h-stat .val{font-size:.88rem;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .h-stat .val.pos{color:var(--green);} .h-stat .val.neg{color:var(--red);}
    .h-stat.end{border-left:1px solid var(--line);padding-left:.9rem;}
    .h-break{margin-top:.95rem;padding-top:.95rem;border-top:1px dashed var(--line);}

    /* ---------- Transaction list (show) ---------- */
    .trx-list{padding:.85rem 1rem;}
    .trx{display:flex;align-items:center;gap:.85rem;padding:.7rem .8rem;border:1px solid var(--line-2);
         border-radius:var(--r-sm);background:#fff;margin-bottom:.5rem;transition:.15s;}
    .trx:last-child{margin-bottom:0;}
    .trx:hover{border-color:var(--line);background:var(--surface-2);}
    .trx-link{color:inherit;text-decoration:none;}
    .trx-link:hover{color:inherit;border-color:var(--blue);background:var(--blue-soft);}
    .trx-link:focus-visible{outline:2px solid var(--blue);outline-offset:-3px;background:var(--blue-soft);}
    .trx-open{display:inline-flex;align-items:center;gap:.35rem;margin-top:.3rem;font-size:.72rem;font-weight:700;color:var(--blue);}
    .trx .cf-ico{width:36px;height:36px;}
    .trx-body{flex:1;min-width:0;}
    .trx-type{font-size:.66rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-3);}
    .trx-desc{font-size:.88rem;font-weight:600;overflow-wrap:anywhere;}
    .trx-date{display:flex;align-items:center;gap:.35rem;font-size:.72rem;color:var(--ink-3);margin-top:.15rem;}
    .trx-amt{font-weight:800;font-size:.92rem;}
    .trx-amt.pos{color:var(--green);} .trx-amt.neg{color:var(--red);} .trx-amt.neu{color:var(--ink);}
    .trx-foot{display:flex;justify-content:flex-end;align-items:baseline;gap:.6rem;padding:.8rem 1rem;
              border-top:1px solid var(--line-2);background:var(--surface-2);font-size:.85rem;}
    .trx-foot .lbl{color:var(--ink-2);font-weight:600;}
    .trx-foot .val{font-size:1rem;font-weight:800;}
    .trx-foot .val.pos{color:var(--green);} .trx-foot .val.neg{color:var(--red);}

    /* ---------- Empty state ---------- */
    .cf-empty{text-align:center;padding:3.5rem 1.5rem;color:var(--ink-2);}
    .cf-empty i{font-size:2.6rem;color:#cbd5e1;display:block;margin-bottom:.8rem;}
    .cf-empty h4{font-weight:800;color:var(--ink);margin-bottom:.4rem;}
    .cf-empty.sm{padding:2.2rem 1rem;font-size:.88rem;}
    .cf-empty.sm i{font-size:2rem;margin-bottom:.5rem;}

    /* ---------- Responsive ---------- */
    @media(max-width:576px){
        .cf-stats{padding:1.1rem;gap:.9rem;}
        .cf-stat + .cf-stat{border-left:0;padding-left:0;}
        .bd-wrap{padding:0 1.1rem 1.2rem;}
        .cf-card-head{padding:1rem 1.1rem;}
        .cf-banner{padding-left:1.1rem;padding-right:1.1rem;}
        .trx{flex-wrap:wrap;}
        .trx-amt{width:100%;text-align:right;margin-left:44px;}
        .h-top{flex-direction:column;}
        .h-actions{width:100%;justify-content:flex-end;}
    }
</style>
