# Tampilan smartphone

Penyesuaian 28 September 2026 berlaku pada layout sidebar dan seluruh halaman Blade mandiri. Aset bersama dimuat melalui `resources/views/partials/mobile-assets.blade.php`, dari `public/css/mobile.css` dan `public/js/mobile.js`. Tidak diperlukan build npm untuk perubahan ini.

## Perilaku

- Viewport mengikuti lebar perangkat dan menyediakan ruang untuk notch serta indikator home iPhone. Zoom pengguna tetap tersedia.
- Sidebar menjadi drawer di bawah 992px. Halaman di belakang terkunci saat menu terbuka; fokus dan posisi scroll kembali ketika ditutup. Peralihan ke desktop melepaskan keadaan dialog mobile.
- Header, filter, tombol, pagination, dan notifikasi menyesuaikan lebar layar. Panel notifikasi dibatasi tinggi viewport, termasuk landscape.
- Input mobile minimal 16px; tombol utama dan kontrol navigasi memiliki area sentuh minimal 44px.
- Tabel data tetap dapat digeser secara horizontal dalam pembungkusnya, termasuk dengan keyboard. Tabel master yang sebelumnya tidak memiliki pembungkus sudah diperbaiki.
- Form rincian OPR/PD menjadi baris vertikal berlabel di bawah 768px. Elemen input dan handler transaksi tetap digunakan, termasuk untuk baris baru yang ditambahkan pengguna.
- Tampilan desktop tetap memakai tabel dan sidebar seperti sebelumnya.

## Validasi

Chrome headless dengan emulasi sentuh memeriksa 37 halaman utama pada lebar 320, 360, 390, 430, 844, dan 1366px. Layar 844px diperiksa dengan tinggi 390px untuk landscape. Login diperiksa pada 320 × 568, 390 × 844, dan 844 × 390.

Pemeriksaan lanjutan mencakup 17 contoh halaman detail/edit pada 320, 390, 844, dan 1366px. Nomor dokumen panjang, ringkasan nominal BS, serta label/nilai Finance sudah disesuaikan untuk layar sempit. Halaman Edit Role yang sebelumnya hanya berupa fragmen formulir kini memakai dokumen HTML lengkap, viewport, dan tampilan master data yang konsisten.

Pemeriksaan interaksi mencakup buka/tutup drawer, Escape, sentuhan backdrop, fokus, pemulihan scroll, rotasi, transisi desktop, panel notifikasi, label baris biaya dinamis, format nominal dan penghapusan baris. Tidak ada formulir transaksi yang dikirim.

Laporan JSON dan screenshot tersimpan secara privat di `storage/app/private/mobile-preview/<timestamp>/`. Skrip `scripts/mobile-browser-test.mjs` menggunakan database uji terisolasi melalui `scripts/bs-uat-router.php`, dan fixture privat `storage/app/private/mobile-preview/fixture.json`. Fixture harus memiliki skema terkini serta akun uji dengan akses ke modul yang diperiksa. Jangan mengarahkan pengujian ke database operasional.

```powershell
node scripts/mobile-browser-test.mjs
# Opsional: periksa contoh detail/edit yang ditautkan dari halaman daftar.
$env:MOBILE_DETAILS = '1'
node scripts/mobile-browser-test.mjs
```

Variabel opsional: `PHP_BINARY`, `CHROME_BINARY`, `MOBILE_FIXTURE`, `MOBILE_WIDTHS` (daftar dipisahkan koma), `MOBILE_PAGES` (path dipisahkan koma), dan `MOBILE_SCREENSHOTS=all`. Aset CDN aplikasi membutuhkan koneksi internet. Script menghasilkan exit code gagal jika menemukan halaman melebar, input mobile terlalu kecil, error halaman, atau interaksi yang gagal.

Kompilasi Blade dan pemeriksaan sintaks JavaScript berhasil. Suite Laravel menghasilkan **155 lulus, 1 gagal** dari 156 tes. Kegagalan `BsCashFlowTest::test_rejection_time_stays_at_the_decision_even_after_document_updates` juga direproduksi menggunakan template Finance asli sebelum perubahan mobile; berkaitan dengan penampilan waktu penolakan Finance. Setelah penyesuaian, 43 tes khusus halaman master, profil, dashboard, dan waktu aplikasi lulus (597 assertions), serta 3 tes isi/hak akses dokumen lulus (60 assertions).

Server PHP pengujian memakai batas memori 512MB karena halaman edit wilayah memuat seluruh data wilayah sekaligus dan melebihi batas 128MB pada fixture yang tersedia. Pengaturan ini hanya berlaku untuk proses pengujian; konfigurasi server aplikasi tidak diubah.

Pengujian ini tidak mencakup Safari/WebKit atau perangkat fisik. Pemeriksaan pada Android Chrome dan iPhone Safari tetap diperlukan untuk memastikan perilaku keyboard sistem, pemilih tanggal native, serta safe area perangkat sebenarnya.
