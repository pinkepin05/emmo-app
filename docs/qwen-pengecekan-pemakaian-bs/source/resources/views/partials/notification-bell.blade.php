{{-- 🎯 LONCENG NOTIFIKASI GLOBAL (floating, auto-inject via middleware) --}}
<style>
    .notif-wrap{position:fixed;top:14px;right:18px;z-index:9999;}
    .notif-bell{background:#ffffff;border:1px solid #e2e8f0;font-size:1.25rem;color:#64748b;cursor:pointer;position:relative;padding:8px 12px;border-radius:12px;transition:all .2s;box-shadow:0 4px 10px -2px rgba(0,0,0,.08);}
    .notif-bell:hover{background:#f1f5f9;color:#0f172a;}
    .notif-badge{position:absolute;top:-6px;right:-6px;background:#ef4444;color:#fff;border-radius:999px;font-size:.65rem;font-weight:700;padding:3px 6px;line-height:1;border:2px solid #fff;}
    .notif-panel{position:absolute;right:0;top:calc(100% + 10px);width:350px;max-width:calc(100vw - 2rem);background:#fff;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 20px 40px -12px rgba(0,0,0,.18);display:none;overflow:hidden;}
    .notif-panel.show{display:block;}
    .notif-head{display:flex;justify-content:space-between;align-items:center;padding:.7rem 1rem;background:#f8fafc;border-bottom:1px solid #e2e8f0;font-size:.85rem;}
    .notif-item{display:block;padding:.75rem 1rem;border-bottom:1px solid #f1f5f9;text-decoration:none;color:#0f172a;transition:background .15s;}
    .notif-item:hover{background:#f8fafc;}
    .notif-title{font-weight:700;font-size:.85rem;margin-bottom:2px;}
    .notif-msg{font-size:.8rem;color:#64748b;line-height:1.45;}
    .notif-time{font-size:.7rem;color:#94a3b8;margin-top:3px;}
</style>

<div class="notif-wrap">
    <button type="button" class="notif-bell" id="notifBell" title="Notifikasi">
        <i class="bi bi-bell-fill"></i>
        <span class="notif-badge d-none" id="notifBadge">0</span>
    </button>
    <div class="notif-panel" id="notifPanel">
        <div class="notif-head">
            <span class="fw-bold">Notifikasi</span>
            <a href="#" id="notifReadAll" class="text-primary text-decoration-none small">Tandai semua dibaca</a>
        </div>
        <div id="notifList" style="max-height:340px;overflow-y:auto;">
            <div class="text-center text-muted small py-4">Memuat…</div>
        </div>
    </div>
</div>

<script>
(function(){
    const bell  = document.getElementById('notifBell');
    const panel = document.getElementById('notifPanel');
    const badge = document.getElementById('notifBadge');
    const list  = document.getElementById('notifList');
    const token = '{{ csrf_token() }}';

    bell.addEventListener('click', function(e){
        e.stopPropagation();
        panel.classList.toggle('show');
        if (panel.classList.contains('show')) load();
    });
    document.addEventListener('click', function(e){
        if (!panel.contains(e.target) && !bell.contains(e.target)) panel.classList.remove('show');
    });

    function load(){
        fetch('{{ route("notifications.unread") }}', {headers:{'Accept':'application/json'}})
            .then(r => r.json())
            .then(d => {
                badge.textContent = d.count;
                badge.classList.toggle('d-none', d.count === 0);

                if (!d.items.length){
                    list.innerHTML = '<div class="text-center text-muted small py-4">Tidak ada notifikasi baru ✅</div>';
                    return;
                }
                list.innerHTML = d.items.map(n => `
                    <a href="${n.url}" class="notif-item" data-id="${n.id}">
                        <div class="notif-title">${n.title}</div>
                        <div class="notif-msg">${n.message}</div>
                        <div class="notif-time">${n.time}</div>
                    </a>`).join('');

                list.querySelectorAll('.notif-item').forEach(a => {
                    a.addEventListener('click', () => {
                        fetch('/notifications/' + a.dataset.id + '/read', {
                            method:'POST',
                            headers:{'X-CSRF-TOKEN': token, 'Accept':'application/json'}
                        });
                    });
                });
            });
    }

    document.getElementById('notifReadAll').addEventListener('click', function(e){
        e.preventDefault();
        fetch('{{ route("notifications.read_all") }}', {
            method:'POST',
            headers:{'X-CSRF-TOKEN': token, 'Accept':'application/json'}
        }).then(() => { badge.classList.add('d-none'); load(); });
    });

    load();
    setInterval(load, 15000); // polling real-time
})();
</script>
