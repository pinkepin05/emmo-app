(() => {
    document.querySelectorAll('[data-transaction-editor]').forEach(form => {
        const rows = form.querySelector('[data-expense-rows]');
        const template = form.querySelector('[data-expense-template]');
        const total = form.querySelector('[data-expense-total]');
        let nextIndex = Math.max(-1, ...[...rows.querySelectorAll('[name^="new_details["]')].map(input => Number(input.name.match(/^new_details\[(\d+)\]/)?.[1] ?? -1))) + 1;
        const money = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 2 });
        const updateTotal = () => {
            const cents = [...rows.querySelectorAll('[data-expense-amount]:not(:disabled)')]
                .reduce((sum, input) => sum + Math.round((Number(input.value) || 0) * 100), 0);
            total.textContent = money.format(cents / 100);
        };
        const applyCategoryRules = () => {
            if (form.dataset.transactionEditor !== 'travel') return;
            const distance = Number(form.elements.jarak_km.value) || 0;
            const duration = Number(form.elements.durasi_jam.value) || 0;
            rows.querySelectorAll('.cat-select').forEach(select => {
                [...select.options].forEach(option => {
                    option.disabled = (option.dataset.type === 'lumpsum' && distance <= 200)
                        || (option.dataset.type === 'hotel' && duration <= 8);
                });
                const invalid = select.selectedOptions[0]?.disabled;
                select.setCustomValidity(invalid ? 'Kategori tidak sesuai jarak atau durasi perjalanan. Pilih kategori lain atau ubah informasi perjalanan.' : '');
            });
        };
        form.querySelector('[data-add-expense]').addEventListener('click', () => {
            const content = template.content.cloneNode(true);
            content.querySelectorAll('[name]').forEach(input => { input.name = input.name.replace('__INDEX__', String(nextIndex)); });
            nextIndex++;
            rows.append(content);
            applyCategoryRules();
            updateTotal();
            rows.lastElementChild.querySelector('select').focus();
        });
        rows.addEventListener('click', event => {
            const button = event.target.closest('[data-remove-expense]');
            if (!button) return;
            const row = button.closest('[data-expense-row]');
            const deleted = row.querySelector('[data-delete-expense]');
            if (deleted) {
                deleted.checked = !deleted.checked;
                row.classList.toggle('opacity-50', deleted.checked);
                row.querySelectorAll('input:not([data-delete-expense]), select').forEach(input => { input.disabled = deleted.checked; });
                button.textContent = deleted.checked ? 'Batalkan Hapus' : 'Hapus';
                button.classList.toggle('btn-outline-secondary', deleted.checked);
                button.classList.toggle('btn-outline-danger', !deleted.checked);
            } else {
                row.remove();
            }
            updateTotal();
        });
        form.addEventListener('input', () => { updateTotal(); applyCategoryRules(); });
        form.addEventListener('change', applyCategoryRules);
        updateTotal();
        applyCategoryRules();

        const territory = form.querySelector('#edit-teritory');
        const province = form.querySelector('#edit-provinsi');
        const city = form.querySelector('#edit-kota');
        if (!territory || !province || !city) return;
        let provinceRequest;
        let cityRequest;
        const reset = (select, message) => { select.replaceChildren(new Option(message, '')); };
        const loadOptions = async (select, url, controller, placeholder) => {
            reset(select, 'Memuat pilihan...');
            select.setCustomValidity('Tunggu sampai pilihan wilayah selesai dimuat.');
            try {
                const response = await fetch(url, { signal: controller.signal, headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('Gagal memuat wilayah');
                const data = await response.json();
                reset(select, placeholder);
                data.forEach(item => select.add(new Option(item.nama, item.id)));
                select.setCustomValidity('');
            } catch (error) {
                if (error.name === 'AbortError') return;
                reset(select, 'Gagal memuat. Pilih ulang wilayah di atas.');
                select.setCustomValidity('Pilihan wilayah gagal dimuat. Pilih ulang wilayah di atas.');
            }
        };
        territory.addEventListener('change', () => {
            provinceRequest?.abort(); cityRequest?.abort();
            reset(city, '-- Pilih Provinsi Terlebih Dahulu --');
            city.setCustomValidity('');
            provinceRequest = new AbortController();
            loadOptions(province, form.dataset.provincesUrl + '?teritory=' + encodeURIComponent(territory.value), provinceRequest, '-- Pilih Provinsi --');
        });
        province.addEventListener('change', () => {
            cityRequest?.abort();
            reset(city, '-- Pilih Kota/Kabupaten --');
            city.setCustomValidity('');
            if (!province.value) return;
            cityRequest = new AbortController();
            loadOptions(city, form.dataset.citiesUrl + '?provinsi_id=' + encodeURIComponent(province.value), cityRequest, '-- Pilih Kota/Kabupaten --');
        });
    });
})();
