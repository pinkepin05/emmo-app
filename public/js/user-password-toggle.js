(() => {
    document.querySelectorAll('[data-password-toggle]').forEach(button => {
        const input = document.getElementById(button.dataset.passwordToggle);
        if (!input) return;

        const setVisible = visible => {
            input.type = visible ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(visible));
            const label = visible ? 'Sembunyikan password' : 'Tampilkan password';
            button.setAttribute('aria-label', label);
            button.title = label;
            button.querySelector('[data-password-slash]').style.display = visible ? '' : 'none';
        };

        button.addEventListener('click', () => setVisible(input.type === 'password'));
        input.form?.addEventListener('submit', () => setVisible(false));
        input.form?.addEventListener('reset', () => setVisible(false));
        window.addEventListener('pageshow', () => setVisible(false));
    });

    document.querySelectorAll('[data-stored-password]').forEach(container => {
        const button = container.querySelector('[data-stored-password-toggle]');
        if (!button) return;
        const input = container.querySelector('input');
        const feedback = container.querySelector('[role="status"]');
        const form = input.form;
        let pending;

        const hide = () => {
            pending?.abort();
            pending = null;
            input.value = '';
            input.type = 'password';
            button.disabled = false;
            button.textContent = 'Lihat';
            button.setAttribute('aria-label', 'Lihat password tersimpan');
            button.setAttribute('aria-pressed', 'false');
            feedback.textContent = 'Tekan Lihat untuk menampilkan password yang tersimpan.';
            feedback.classList.remove('text-danger');
        };

        button.addEventListener('click', async () => {
            if (input.type === 'text') {
                hide();
                return;
            }
            const request = new AbortController();
            pending = request;
            button.disabled = true;
            feedback.classList.remove('text-danger');
            feedback.textContent = 'Memuat password…';
            try {
                const response = await fetch(button.dataset.revealUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    cache: 'no-store',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value,
                    },
                    signal: request.signal,
                });
                const data = await response.json().catch(() => ({}));
                if (request.signal.aborted) return;
                if (!response.ok || typeof data.password !== 'string') {
                    throw new Error(response.status === 409
                        ? data.message
                        : 'Password tidak dapat ditampilkan. Muat ulang halaman lalu coba lagi.');
                }
                input.value = data.password;
                input.type = 'text';
                button.textContent = 'Tutup';
                button.setAttribute('aria-label', 'Sembunyikan password tersimpan');
                button.setAttribute('aria-pressed', 'true');
                feedback.textContent = 'Password yang sedang digunakan. Tekan Tutup untuk menyembunyikannya.';
            } catch (error) {
                if (!request.signal.aborted) {
                    feedback.textContent = error.message;
                    feedback.classList.add('text-danger');
                }
            } finally {
                if (pending === request) {
                    pending = null;
                    button.disabled = false;
                }
            }
        });

        form.addEventListener('submit', hide);
        form.addEventListener('reset', hide);
        window.addEventListener('pagehide', hide);
        window.addEventListener('pageshow', hide);
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) hide();
        });
    });
})();
