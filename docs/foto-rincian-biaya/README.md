# Peta file untuk foto per rincian biaya

Tanggal pemetaan: 8 Oktober 2026 (Asia/Bangkok).

Paket ini memuat 50 file sumber dan dokumentasi dari working tree saat pemetaan, termasuk perubahan lokal yang sudah ada. Ini adalah bahan implementasi/review, bukan fitur upload foto yang sudah selesai atau aplikasi mandiri.

Screenshot sesuai dengan `resources/views/transaksi_perjalanan_dinas/create.blade.php`: judul Rincian Biaya (Detail) dan placeholder Opsional (Misal: Tiket Kereta). Modul Operasional disertakan karena memiliki form rincian serupa dan berbagi komponen edit. Jika fitur hanya berlaku untuk Perjalanan Dinas, perubahan Operasional tidak otomatis diperlukan; komponen bersama harus tetap mendukung kedua modul.

ZIP: [file-kode-foto-rincian-biaya.zip](../file-kode-foto-rincian-biaya.zip). Di dalam ZIP, `source/` mempertahankan struktur path proyek dan `kode-untuk-chat/` berisi isi lengkap file yang digabung per kelompok. `manifest.json` mencatat semua file yang disertakan.

## Temuan dan titik integrasi

- Form create dan edit rincian belum memiliki input foto maupun `enctype="multipart/form-data"`.
- Penambahan baris pada form create masih berada di JavaScript inline masing-masing Blade. Edit menggunakan dua partial bersama dan `public/js/transaction-editor.js`.
- Saat create/tambah rincian, payload memakai `details[index]`. Saat edit, rincian lama memakai `details[id]`, rincian baru memakai `new_details[index]`, dan penghapusan memakai `delete_details[]`. Input file harus mengikuti struktur tersebut.
- Kedua controller mempunyai `store`, `update`, dan `storeDetail`. Endpoint tambah rincian masih terdaftar walaupun UI tambah rincian kini ada dalam mode edit. Semua jalur tersebut perlu diperiksa saat menambahkan upload.
- Model rincian belum mempunyai atribut foto di daftar `fillable`. Diperlukan migration baru untuk metadata foto pada tabel detail yang dipilih; untuk beberapa foto per item, pertimbangkan tabel lampiran terpisah.
- Pola lampiran yang tersedia adalah lampiran pengajuan BS, dengan storage privat dan penghapusan file pending melalui `AtomicBsRequest`. Pola itu merupakan referensi, bukan fitur foto per rincian yang sudah ada.
- Finance memetakan biaya memakai `source_detail_id` dan menyimpan snapshot. Jika foto perlu tersedia dalam riwayat, tentukan retensi/snapshot bukti ketika rincian diubah atau dihapus.

Jumlah foto per baris, wajib/opsional, format, batas ukuran, dan cakupan tampilan Finance belum ditentukan oleh permintaan ini. Nilai batas dari lampiran BS tidak otomatis menjadi persyaratan fitur baru.

## Perjalanan Dinas — form pada screenshot

| File | Peran |
| --- | --- |
| [resources/views/transaksi_perjalanan_dinas/create.blade.php](../../resources/views/transaksi_perjalanan_dinas/create.blade.php) | Form pembuatan, baris awal, template Tambah Baris, dan JavaScript inline; titik utama penambahan input foto. |
| [resources/views/transaksi_perjalanan_dinas/show.blade.php](../../resources/views/transaksi_perjalanan_dinas/show.blade.php) | Tabel rincian tersimpan dan form edit; tempat menampilkan foto dan menambahkan multipart pada form edit. |
| [app/Http/Controllers/TransaksiPerjalananDinasController.php](../../app/Http/Controllers/TransaksiPerjalananDinasController.php) | Validasi dan penyimpanan melalui store, update, storeDetail; penghapusan melalui destroy dan destroyDetail. |
| [app/Models/TransaksiPerjalananDinasDetail.php](../../app/Models/TransaksiPerjalananDinasDetail.php) | Model tabel transaksi_perjalanan_dinas_detail; penyimpanan metadata foto per item. |
| [app/Models/TransaksiPerjalananDinasHeader.php](../../app/Models/TransaksiPerjalananDinasHeader.php) | Relasi details dan identitas transaksi induk untuk pemeriksaan akses. |

## Operasional — modul dengan alur rincian serupa

| File | Peran |
| --- | --- |
| [resources/views/transaksi_operasional/create.blade.php](../../resources/views/transaksi_operasional/create.blade.php) | Form pembuatan, template baris dinamis dan reindexRows; indeks input foto harus mengikuti baris yang benar. |
| [resources/views/transaksi_operasional/show.blade.php](../../resources/views/transaksi_operasional/show.blade.php) | Tabel rincian tersimpan dan form edit Operasional. |
| [app/Http/Controllers/TransaksiOperasionalController.php](../../app/Http/Controllers/TransaksiOperasionalController.php) | Validasi, simpan, edit, tambah rincian, dan hapus transaksi Operasional. |
| [app/Models/TransaksiOperasionalDetail.php](../../app/Models/TransaksiOperasionalDetail.php) | Model tabel transaksi_operasional_area_sales_detail; penyimpanan metadata foto per item. |
| [app/Models/TransaksiOperasionalHeader.php](../../app/Models/TransaksiOperasionalHeader.php) | Relasi details dan identitas transaksi induk. |

## Komponen bersama, penyimpanan, dan akses

| File | Peran |
| --- | --- |
| [resources/views/partials/transaction-edit-details.blade.php](../../resources/views/partials/transaction-edit-details.blade.php) | Header tabel edit, rincian lama, rincian baru, dan template penambahan baris. |
| [resources/views/partials/transaction-edit-row.blade.php](../../resources/views/partials/transaction-edit-row.blade.php) | Isi setiap baris edit; tempat input foto, foto tersimpan, dan tindakan penggantian/penghapusan. |
| [public/js/transaction-editor.js](../../public/js/transaction-editor.js) | Tambah baris, indeks new_details, hapus/batalkan hapus dan input yang dinonaktifkan. |
| [routes/web.php](../../routes/web.php) | Route simpan/edit/tambah/hapus rincian dan lokasi penambahan endpoint lihat/unduh foto. |
| [config/filesystems.php](../../config/filesystems.php) | Konfigurasi disk penyimpanan; sudah ada contoh disk privat bs_attachments. |
| [app/Http/Middleware/AtomicBsRequest.php](../../app/Http/Middleware/AtomicBsRequest.php) | Transaksi database luar dan cleanup upload BS; relevan untuk rollback/retry penyimpanan foto rincian. |
| [app/Models/User.php](../../app/Models/User.php) | Aturan akses transaksi yang dapat digunakan saat mengizinkan akses foto. |
| [app/Models/Concerns/UsesBsBalance.php](../../app/Models/Concerns/UsesBsBalance.php) | Konteks status transaksi, pemilik BS, dan hubungan pemeriksaan Finance. |
| [resources/views/partials/mobile-assets.blade.php](../../resources/views/partials/mobile-assets.blade.php) | Pemuatan aset mobile bersama. |
| [public/css/mobile.css](../../public/css/mobile.css) | Layout tabel/form pada layar kecil; periksa saat menambah kolom foto. |
| [public/js/mobile.js](../../public/js/mobile.js) | Adaptasi tabel dan label sel pada tampilan mobile. |

## Pemeriksaan Finance, persetujuan, dan rekap — jika foto ditampilkan di alur ini

| File | Peran |
| --- | --- |
| [app/Services/BsUsageCheckService.php](../../app/Services/BsUsageCheckService.php) | snapshot memetakan setiap rincian ke source_detail_id dan fingerprint; perlu dipertimbangkan jika foto ikut menjadi bukti pemeriksaan. |
| [app/Http/Controllers/BsUsageCheckController.php](../../app/Http/Controllers/BsUsageCheckController.php) | Menyiapkan item pemeriksaan dan membaca riwayat hasil Finance. |
| [app/Models/BsUsageCheck.php](../../app/Models/BsUsageCheck.php) | Relasi pemeriksaan ke transaksi sumber dan detail pemeriksaan. |
| [app/Models/BsUsageCheckDetail.php](../../app/Models/BsUsageCheckDetail.php) | Model rincian pemeriksaan; terkait jika metadata foto disimpan sebagai snapshot historis. |
| [app/Models/BsUsageCheckDraft.php](../../app/Models/BsUsageCheckDraft.php) | Model draft pemeriksaan; terkait bila foto ikut menjadi bagian snapshot draft. |
| [resources/views/bs-usage-checks/create.blade.php](../../resources/views/bs-usage-checks/create.blade.php) | Tabel rincian saat Finance memeriksa nominal per item. |
| [resources/views/bs-usage-checks/show.blade.php](../../resources/views/bs-usage-checks/show.blade.php) | Tabel rincian hasil pemeriksaan dan riwayat Finance. |
| [app/Http/Controllers/TransactionRecapController.php](../../app/Http/Controllers/TransactionRecapController.php) | Pemuatan transaksi untuk halaman detail rekap. |
| [resources/views/transaction-recap/show.blade.php](../../resources/views/transaction-recap/show.blade.php) | Tabel rincian transaksi pada rekap. |
| [app/Http/Controllers/BsUsageApprovalController.php](../../app/Http/Controllers/BsUsageApprovalController.php) | Pemuatan rincian untuk persetujuan pemakaian BS oleh pemilik saldo. |
| [resources/views/bs-usage-approvals/show.blade.php](../../resources/views/bs-usage-approvals/show.blade.php) | Tabel rincian yang diperiksa pemilik saldo BS. |
| [database/migrations/2026_10_07_160000_track_finance_approval_per_source_detail.php](../../database/migrations/2026_10_07_160000_track_finance_approval_per_source_detail.php) | Referensi skema source_detail_id/source_remark; bukan migration foto. |

## Referensi upload yang sudah ada — lampiran pengajuan BS

| File | Peran |
| --- | --- |
| [app/Services/BsAttachmentService.php](../../app/Services/BsAttachmentService.php) | Contoh upload privat, penanganan kegagalan, afterCommit, dan pembersihan file ketika rollback. |
| [app/Http/Controllers/PengajuanBsAttachmentController.php](../../app/Http/Controllers/PengajuanBsAttachmentController.php) | Contoh otorisasi akses, validasi path, dan response lihat/unduh file privat. |
| [app/Http/Controllers/PengajuanBsController.php](../../app/Http/Controllers/PengajuanBsController.php) | Contoh validasi attachment dan penyimpanan path/nama pada pengajuan BS. |
| [app/Models/PengajuanBs.php](../../app/Models/PengajuanBs.php) | Contoh atribut attachment pada model pengajuan. |
| [resources/views/pengajuan-bs/create.blade.php](../../resources/views/pengajuan-bs/create.blade.php) | Contoh multipart/form-data, input file, pemilihan dan penghapusan file sebelum submit. |
| [resources/views/pengajuan-bs/partials/attachment.blade.php](../../resources/views/pengajuan-bs/partials/attachment.blade.php) | Contoh tampilan lampiran tersimpan. |
| [database/migrations/2026_10_07_030313_add_attachment_path_to_pengajuan_bs_table.php](../../database/migrations/2026_10_07_030313_add_attachment_path_to_pengajuan_bs_table.php) | Contoh migration metadata lampiran; targetnya pengajuan_bs, bukan rincian biaya. |

## Pengujian dan dokumentasi pendukung

| File | Peran |
| --- | --- |
| [tests/Feature/BsCashFlowTest.php](../../tests/Feature/BsCashFlowTest.php) | Skema database uji dan skenario create/edit/tambah/hapus rincian; tempat integrasi migration foto pada pengujian. |
| [tests/Feature/BsUsageCheckTest.php](../../tests/Feature/BsUsageCheckTest.php) | Pengujian pemeriksaan Finance yang perlu diperiksa jika foto masuk ke snapshot. |
| [tests/Concerns/BsAttachmentScenarios.php](../../tests/Concerns/BsAttachmentScenarios.php) | Contoh uji upload, akses file, file hilang, storage gagal, rollback dan retry. |
| [tests/Concerns/FinanceItemApprovalScenarios.php](../../tests/Concerns/FinanceItemApprovalScenarios.php) | Uji pemeriksaan per source_detail_id; terkait jika bukti foto ditampilkan/disnapshot. |
| [tests/Concerns/TransactionRecapScenarios.php](../../tests/Concerns/TransactionRecapScenarios.php) | Uji detail rekap dan akses pengguna. |
| [scripts/transaction-edit-browser-test.mjs](../../scripts/transaction-edit-browser-test.mjs) | Uji browser interaksi tambah/edit/hapus rincian dan tampilan mobile. |
| [scripts/bs-verification-bootstrap.php](../../scripts/bs-verification-bootstrap.php) | Pengamanan database terisolasi untuk skrip verifikasi pendukung. |
| [docs/transaction-detail-edit.md](../../docs/transaction-detail-edit.md) | Dokumentasi alur edit rincian yang sedang dipakai. |
| [docs/persetujuan-finance-per-rincian.md](../../docs/persetujuan-finance-per-rincian.md) | Dokumentasi hubungan rincian transaksi dengan hasil Finance. |
| [database/migrations/2026_08_27_085533_add_soft_deletes_to_detail_tables.php](../../database/migrations/2026_08_27_085533_add_soft_deletes_to_detail_tables.php) | Konteks kolom deleted_at pada tabel detail; cek perilaku model dan penghapusan aktual sebelum menentukan retensi foto. |

## File baru yang diperkirakan diperlukan

Nama berikut adalah usulan untuk implementasi berikutnya, belum ada dalam paket sumber:

- `database/migrations/<timestamp>_add_photo_fields_to_transaction_details.php`: metadata foto pada tabel detail; bentuk akhir mengikuti satu/beberapa foto per item.
- `app/Services/TransactionDetailPhotoService.php`: upload, penggantian, penghapusan, serta cleanup saat transaksi database gagal.
- `app/Http/Controllers/TransactionDetailPhotoController.php`: akses lihat/unduh foto dengan otorisasi transaksi terkait.
- `resources/views/partials/transaction-detail-photo.blade.php`: opsional, komponen tampilan/input foto agar konsisten.
- `tests/Concerns/TransactionDetailPhotoScenarios.php` atau file Feature khusus: uji keterkaitan foto dengan baris, create/edit/hapus, hak akses, dan rollback.

## Cara menggunakan paket

Mulai dari kelompok Perjalanan Dinas, kemudian komponen bersama, controller, model dan rancangan migration baru. Kelompok Operasional adalah perluasan untuk modul serupa. Kelompok Finance digunakan bila foto perlu dibaca saat pemeriksaan/persetujuan/rekap. Kelompok lampiran BS memberi contoh implementasi upload yang sudah tersedia.

Paket tidak mencakup seluruh dependensi aplikasi dan tidak dapat dijalankan sendiri. Pengujian yang disertakan dijalankan dari proyek lengkap. Tidak ada pengujian aplikasi atau migration yang dijalankan untuk pekerjaan pemetaan ini. Kode aplikasi tidak diubah; artefak yang dibuat hanya paket sumber dan peta file.

