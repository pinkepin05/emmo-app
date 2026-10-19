(() => {
    const urls = new Map();
    let viewer;
    let opener;
    const createViewer = () => {
        const dialog = document.createElement('dialog');
        dialog.className = 'photo-lightbox';
        dialog.setAttribute('aria-labelledby', 'expense-photo-viewer-title');
        dialog.innerHTML = `<div class="photo-lightbox-header">
            <div><h2 id="expense-photo-viewer-title">Foto bukti biaya</h2><p class="photo-lightbox-name"></p></div>
            <button type="button" class="photo-lightbox-close" aria-label="Tutup pratinjau foto" autofocus>&times;</button>
            </div><div class="photo-lightbox-body"><p class="photo-lightbox-message" role="status">Memuat foto...</p><img alt="Foto bukti biaya diperbesar" hidden></div>
            <div class="photo-lightbox-footer"><a target="_blank" rel="noopener">Buka ukuran asli <span class="visually-hidden">(tab baru)</span></a></div>`;
        dialog.querySelector('button').addEventListener('click', () => dialog.close());
        dialog.addEventListener('click', event => {
            const rect = dialog.getBoundingClientRect();
            if (event.target === dialog && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) dialog.close();
        });
        dialog.addEventListener('close', () => {
            document.documentElement.classList.remove('photo-lightbox-open');
            dialog.querySelector('img').removeAttribute('src');
            if (opener?.isConnected) opener.focus({ preventScroll: true });
        });
        document.body.append(dialog);
        return dialog;
    };
    const clearPreview = input => {
        if (viewer?.open && viewer.querySelector('img').src === urls.get(input)) viewer.close();
        if (urls.has(input)) URL.revokeObjectURL(urls.get(input));
        urls.delete(input);
        const preview = input.closest('[data-photo-input]').querySelector('[data-photo-preview]');
        preview.hidden = true;
        preview.querySelector('img').removeAttribute('src');
        preview.querySelectorAll('[data-photo-open]').forEach(link => {
            link.removeAttribute('href');
            delete link.dataset.photoTitle;
        });
        preview.querySelector('[data-photo-filename]').textContent = '';
        preview.querySelector('[data-photo-filename]').removeAttribute('title');
    };
    document.addEventListener('change', event => {
        const input = event.target;
        if (input.matches('[data-remove-photo]')) {
            input.closest('[data-existing-photo]').classList.toggle('is-removing', input.checked);
            return;
        }
        if (!input.matches('[data-expense-photo]')) return;
        const container = input.closest('[data-photo-input]');
        const error = container.querySelector('[data-photo-error]');
        clearPreview(input);
        input.setCustomValidity('');
        error.textContent = '';
        error.hidden = true;
        input.classList.remove('is-invalid');
        const file = input.files[0];
        if (!file) return;
        const invalidType = !/\.(jpe?g|png|webp)$/i.test(file.name) || (file.type && !['image/jpeg', 'image/png', 'image/webp'].includes(file.type));
        const message = invalidType ? 'Pilih foto JPG, PNG, atau WebP.' : file.size > 2 * 1024 * 1024 ? 'Ukuran foto maksimal 2 MB.' : '';
        if (message) {
            input.value = '';
            error.textContent = message;
            error.hidden = false;
            input.classList.add('is-invalid');
            return;
        }
        const remove = container.querySelector('[data-remove-photo]');
        if (remove) {
            remove.checked = false;
            remove.dispatchEvent(new Event('change', { bubbles: true }));
        }
        const url = URL.createObjectURL(file);
        urls.set(input, url);
        const preview = container.querySelector('[data-photo-preview]');
        preview.querySelector('img').src = url;
        preview.querySelectorAll('[data-photo-open]').forEach(link => {
            link.href = url;
            link.dataset.photoTitle = file.name;
        });
        preview.querySelector('[data-photo-filename]').textContent = file.name;
        preview.querySelector('[data-photo-filename]').title = file.name;
        preview.hidden = false;
    });
    document.addEventListener('click', event => {
        const link = event.target.closest('[data-photo-open]');
        if (link?.href && !event.ctrlKey && !event.metaKey && !event.shiftKey && !event.altKey && typeof HTMLDialogElement !== 'undefined') {
            event.preventDefault();
            viewer ??= createViewer();
            opener = link;
            const img = viewer.querySelector('img');
            const message = viewer.querySelector('.photo-lightbox-message');
            viewer.querySelector('.photo-lightbox-name').textContent = link.dataset.photoTitle || '';
            viewer.querySelector('.photo-lightbox-footer a').href = link.href;
            img.hidden = true;
            message.hidden = false;
            message.textContent = 'Memuat foto...';
            img.onload = () => { img.hidden = false; message.hidden = true; };
            img.onerror = () => { img.hidden = true; message.hidden = false; message.textContent = 'Foto tidak dapat dimuat. Coba buka ukuran asli.'; };
            img.src = link.href;
            viewer.showModal();
            document.documentElement.classList.add('photo-lightbox-open');
            return;
        }
        const button = event.target.closest('[data-clear-photo]');
        if (!button) return;
        const input = button.closest('[data-photo-input]').querySelector('[data-expense-photo]');
        input.value = '';
        input.dispatchEvent(new Event('change', { bubbles: true }));
        input.focus({ preventScroll: true });
    });
    new MutationObserver(() => {
        for (const [input, url] of urls) {
            if (!input.isConnected) { URL.revokeObjectURL(url); urls.delete(input); }
        }
    }).observe(document.body, { childList: true, subtree: true });
    window.addEventListener('pagehide', () => {
        for (const url of urls.values()) URL.revokeObjectURL(url);
        urls.clear();
    });
})();
