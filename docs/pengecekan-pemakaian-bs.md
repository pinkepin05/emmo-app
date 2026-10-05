# Pengecekan Pemakaian BS oleh Finance

Perluasan 29 September 2026: dashboard kini mencakup empat modul, keputusan Finance eksplisit, serta konfirmasi pengajuan Event. Lihat [dokumentasi rekap transaksi terbaru](dashboard-rekap-transaksi.md). Aturan dan hasil uji di bawah mendokumentasikan implementasi awal 25 September.

Implementasi: 25 September 2026. Migrasi sudah diterapkan pada database MySQL lokal.

## Penggunaan

Pengguna dengan permission `akses.approval-finance` dapat membuka menu **Bon Sementara → Pengecekan Pemakaian BS**, atau URL `/pengecekan-pemakaian-bs`.

1. Cari transaksi OPR/PD berdasarkan jenis, nomor referensi, user, atau status pengecekan.
2. Pilih **Periksa** / **Cek Ulang**. Nomor OPR existing tetap menggunakan prefix OAS.
3. Isi tanggal pengecekan. Jenis, nomor referensi, user transaksi, checker, periode, dan nominal asal mengikuti data otomatis.
4. Nominal disetujui awalnya sama dengan nominal asal. Sesuaikan nominal dan catatan per kategori, lalu **Simpan Konfirmasi**.
5. Hasil tersimpan dengan nomor `CEKBS-YYYYMMDD-NNN`. Cek ulang selalu membuat dokumen baru; hasil lama tidak mempunyai aksi edit/hapus.

Laporan tersedia pada menu **Laporan → Laporan Pengecekan BS**, URL `/pengecekan-pemakaian-bs/laporan`. Menu ini memakai izin Finance existing, tanpa menambahkan permission atau mengaktifkan flag laporan BS lama.

## Aturan yang diterapkan

- Semua OPR/PD tersimpan dapat dipilih, termasuk draft/pending/rejected. Sumber harus mempunyai rincian aktif saat hasil pengecekan dikonfirmasi.
- Detail yang berkategori sama dijumlahkan menjadi satu item. Detail PD yang soft-deleted tidak dihitung. OPR tidak memerlukan kolom deleted_at.
- Nominal asal, nama kategori, nomor referensi, nama pemilik transaksi, dan nama checker disimpan sebagai snapshot. Riwayat tetap terbaca setelah transaksi draft sumber dihapus.
- User pemilik transaksi berasal dari source.user_id, bukan pemilik saldo BS. Pada PD grup, keduanya bisa berbeda.
- Nominal disetujui dapat nol atau melebihi nominal asal, maksimal dua desimal dan batas DECIMAL(15,2). Catatan maksimal 2.000 karakter.
- Periode mengikuti tanggal pengecekan (YYYY-MM). Tanggal default mengikuti AppTime dan timezone aplikasi. Verifikasi tidak membuka/menutup periode pembukuan dan tidak diblokir otomatis oleh status CLOSED.
- Form membawa token terenkripsi yang mengikat jenis, ID sumber dan fingerprint rincian. Jika sumber berubah sejak form dibuka, konfirmasi ditolak dan Finance perlu meninjau rincian terbaru.
- Selisih = nominal asal − nominal disetujui. Status dokumen "Sesuai" hanya bila semua item berselisih nol. Selisih item yang saling meniadakan tetap "Tidak Sesuai".
- "Cek Terakhir" memilih ID pengecekan terakhir per pasangan jenis + ID sumber sebelum menerapkan filter tanggal/periode/status. Pengecekan lama tidak muncul kembali hanya karena yang terbaru berada di luar rentang filter.
- Mode semua riwayat menampilkan seluruh hasil. Footer menghitung semua item dalam hasil terfilter, bukan hanya halaman aktif. Angka tampil dua desimal.
- Status "Sudah Dicek Finance" pada daftar/detail OPR/PD dihitung dengan EXISTS. Artinya transaksi pernah diperiksa; status ini tidak menggantikan status konfirmasi sumber.
- Penyimpanan pengecekan tidak mengubah nominal/status sumber, approval pemilik saldo, Sisa BS, Stok Kas, pencairan, settlement, atau reimbursement.

## Dashboard konfirmasi pemakaian BS

Panel **Konfirmasi Pemakaian BS** tersedia di `/dashboard/transaksi#pemakaian-bs`. Pengajuan yang ditampilkan adalah transaksi pemakaian OPR/PD yang sudah dikonfirmasi (`status = VALIDATED`). Draft, menunggu konfirmasi pemilik BS, dan penolakan pemilik BS tidak masuk panel ini.

- Kartu ringkasan menampilkan nominal dikonfirmasi user, nominal menunggu pengecekan, nominal disetujui Finance, dan selisih user dikurangi Finance.
- Tabel menampilkan nomor/jenis transaksi, user transaksi, tanggal/waktu dan pelaku konfirmasi, nominal user, dokumen/tanggal/waktu/checker Finance terakhir, nominal disetujui, selisih, serta status Finance.
- Hasil Finance diambil dari ID pengecekan terakhir per pasangan jenis dan ID sumber. Cek ulang tidak menggandakan jumlah transaksi atau total. Hasil pengecekan tetap diambil meskipun periode pengecekannya berbeda dari periode pemakaian yang dipilih.
- Filter menggunakan **periode pemakaian** dan jenis OPR/PD. Default bulan berjalan; **Semua Periode** membuka seluruh periode. Pagination berisi 10 transaksi per halaman; kartu tetap menghitung seluruh hasil filter.
- User biasa hanya melihat transaksi dengan `source.user_id` miliknya. Pemilik saldo BS tidak otomatis melihat transaksi input anggota lain pada panel ini. Finance dengan permission `akses.approval-finance` serta role yang sudah mempunyai `canSeeAllTransactions()` dapat melihat seluruh transaksi. Tautan rincian tetap mengikuti izin masing-masing modul; catatan internal Finance tidak ditampilkan.
- **Belum Dicek** berarti belum ada hasil Finance. Jika kategori atau nominal sumber berubah setelah diperiksa, status menjadi **Perlu Cek Ulang**. Perbandingan dilakukan per kategori, termasuk kategori ditambahkan/dihapus, bukan hanya total. PD yang soft-deleted tidak dihitung.
- Kedua status tersebut masuk nominal menunggu dan menampilkan selisih kosong (`—`), bukan menganggap persetujuan Finance nol. Nominal Finance lama tetap terlihat dengan penanda bahwa rincian sudah berubah, tetapi tidak masuk total Finance/selisih terkini.
- Persetujuan Finance sebesar nol tetap merupakan hasil sah dan menghasilkan selisih. Status **Sesuai** memerlukan seluruh item sesuai; selisih antaritem yang saling meniadakan tetap **Tidak Sesuai**.
- Panel hanya membaca data pemakaian dan pengecekan. Tidak ada migrasi tambahan untuk dashboard, dan perhitungan Sisa BS tetap memakai aturan existing.

Berkas dashboard: `app/Services/BsUsageDashboardService.php`, `resources/views/dashboard/partials/bs-usage-summary.blade.php`, integrasi di `routes/web.php` dan `resources/views/dashboard/transaksi.blade.php`. Tes dashboard berada di `tests/Feature/DashboardAccessTest.php` dan `tests/Feature/BsCashFlowTest.php`.

## Struktur dan titik integrasi

`bs_usage_checks` menyimpan header, user/checker, waktu, periode, referensi, dan fingerprint. `bs_usage_check_details` menyimpan item, nominal, dan catatan. Detail mempunyai FK ke header dan cost_category. Header mempunyai dua FK ke tabel `user`; tipe INT signed mengikuti skema aktual.

Relasi sumber merupakan relasi polimorfik logis: `jenis_transaksi` (OPR/PD) + `transaksi_pemakaian_id` (primary key numerik sumber). Satu FK SQL tidak dapat mengarah kondisional ke dua tabel; keberadaan serta konsistensi sumber diperiksa dan dikunci dalam transaksi oleh `BsUsageCheckService`. Snapshot tetap tersedia ketika sumber draft kemudian dihapus. Relasi sumber ini bukan FK fisik.

Nomor dokumen memakai `DocumentNumberService`, dengan sequence CEKBS tersendiri di transaksi database yang sama dengan header/detail. Lock header sumber menserialisasi pemeriksaan ulang sumber yang sama. Tidak ada unique constraint yang melarang satu sumber memiliki banyak hasil pengecekan.

Berkas utama:

- `app/Http/Controllers/BsUsageCheckController.php`
- `app/Services/BsUsageCheckService.php`
- `app/Models/BsUsageCheck.php`
- `app/Models/BsUsageCheckDetail.php`
- `database/migrations/2026_09_25_150000_create_bs_usage_checks.php`
- `resources/views/bs-usage-checks/`
- `resources/views/partials/bs-finance-check-status.blade.php`
- `public/js/bs-usage-checks.js`
- `tests/Feature/BsUsageCheckTest.php`

Model/controller/view daftar-detail OPR/PD, sidebar, route web, provider morphMap, dan layanan nomor dokumen juga diperbarui. Middleware bs.atomic tidak dipakai untuk modul verifikasi karena mengandung aturan pembukuan dan status sumber yang berbeda.

## Penerapan pada lingkungan lain

Terapkan kode dan migrasi secara berurutan sebelum melayani halaman OPR/PD, karena halaman tersebut sekarang memeriksa tabel riwayat baru:

```powershell
php artisan migrate --path=database/migrations/2026_09_25_150000_create_bs_usage_checks.php --force
```

Pada lingkungan yang memakai route/view cache, bangun kembali cache mengikuti prosedur deployment proyek. Tidak perlu menjalankan ulang PermissionSeeder. Jangan menjalankan migrate:fresh pada database bisnis lama.

## Hasil verifikasi

- PHP service/controller/migrasi dan JavaScript baru lolos pemeriksaan sintaks.
- Seluruh 10 tes BsUsageCheckTest lulus, termasuk snapshot, kategori berulang, PD soft delete, cek ulang, referensi OPR/PD dengan ID sama, field otomatis palsu, stale token, validasi nominal/item, rollback, authorization, filter latest/history, total lintas halaman, serta escaping catatan.
- Tambahan tes integrasi BsCashFlowTest lulus: pengecekan dan cek ulang OPR/PD tidak mengubah saldo atau tabel finansial, dan badge derived tampil pada rincian sumber.
- Pengujian seluruh aplikasi setelah penambahan dashboard: **155 lulus, 1 gagal; 2.276 assertion**. Kegagalan berada pada tes existing `test_rejection_time_stays_at_the_decision_even_after_document_updates`: view `approval-finance/index.blade.php` menampilkan waktu tanpa detik/WIB, sedangkan tes mengharapkan format lengkap. View ini tidak diubah oleh implementasi pengecekan/dashboard.
- Pengujian dashboard terfokus: **29 lulus; 323 assertion**, termasuk pembatasan ke transaksi terkonfirmasi, cakupan akses, hasil Finance terakhir, persetujuan nol, selisih antaritem, perubahan kategori dengan total tetap, filter, dan total lintas halaman.
- Verifikasi dashboard MySQL melalui `scripts/verify-bs-dashboard.php`: 9 permintaan halaman berhasil untuk Finance dan pemilik transaksi, hasil pengecekan ulang OPR/PD terbaru terbaca, persetujuan nol tetap mempunyai selisih. Isi 17 tabel (termasuk pengecekan dan sequence) identik setelah rollback. Hasil tersimpan pada `storage/app/private/bs-dashboard-verification/result.json`. Alias SQL pada SELECT menggunakan pengutipan query builder agar kompatibel dengan MySQL/MariaDB.
- Migrasi MySQL lokal berhasil. Smoke test melalui HTTP kernel mencakup 12 permintaan (lookup, laporan kosong, form OPR/PD, dua kali simpan untuk masing-masing jenis, detail hasil, dan laporan riwayat). Semua berhasil.
- Hash isi 14 tabel existing identik sebelum/sesudah migrasi dan smoke test. Header/detail pengecekan uji serta sequence di-rollback. Auto-increment ID MySQL dapat mempunyai celah akibat transaksi uji yang dibatalkan; nomor dokumen bisnis tidak dikonsumsi.
- Hasil MySQL tersimpan pada `storage/app/private/bs-finance-check-verification/after.json`. Skrip verifikasi: `scripts/verify-bs-finance-check.php` (mode before/after). Skrip tidak mengekspor isi transaksi.
- Uji konkurensi MySQL simultan dan inspeksi visual browser belum dilakukan. Render halaman diuji melalui HTTP kernel/Blade; JavaScript diperiksa sintaksnya.

Cadangan berkas existing sebelum perubahan: `storage/app/private/bs-finance-check-backup-20260925-143816/`. Jika sudah ada hasil pengecekan bisnis, pertahankan arsip sebelum mempertimbangkan rollback tabel baru.

Cadangan sebelum integrasi dashboard: `storage/app/private/bs-dashboard-backup-20260925-160407/`.
