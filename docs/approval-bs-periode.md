# Approval atasan berdasarkan status periode

Pengajuan BS yang masih `PENDING_ATASAN` hanya dapat disetujui jika periode pengajuannya berstatus `OPEN`. Periode bulan lalu yang masih `OPEN` tetap dapat disetujui, termasuk jika bulan kalender saat ini sudah `CLOSED`. Kunci periode mengikuti `pengajuan_bs.periode`, dengan dukungan format `YYYY-MM` dan `YYYYMM`.

Jika periodenya tertutup, pengajuan yang masih menunggu atasan otomatis ditolak dengan alasan:

> Ditolak otomatis oleh sistem karena periode pengajuan YYYY-MM sudah ditutup.

Sesuai aturan `PeriodService`, periode lampau yang tidak terdaftar dianggap tertutup; periode saat ini atau mendatang yang belum terdaftar dianggap terbuka.

Penolakan otomatis dijalankan:

- Dalam transaksi penutupan periode, setelah status periode berubah menjadi `CLOSED`.
- Saat atasan membuka daftar atau detail approval yang menjadi kewenangannya.
- Saat permintaan approve/reject dikirim, termasuk dari halaman yang sudah lama terbuka.
- Setiap jam melalui command `bs:reject-closed-periods`, jika Laravel scheduler server berjalan.

Untuk memproses seluruh pengajuan lama tanpa menunggu kunjungan halaman, jalankan `php artisan bs:reject-closed-periods` di lingkungan tujuan. Command dapat diulang; pengajuan yang sudah diproses tidak akan ditolak atau diberi notifikasi lagi. Scheduler menggunakan konfigurasi standar Laravel (`php artisan schedule:run` setiap menit).

Status database tetap memakai `REJECTED_ATASAN` agar kompatibel dengan enum dan alur penolakan yang sudah ada. Pembeda tindakan otomatis adalah log `REJECTED_SYSTEM`, nama aktivitas `Ditolak Sistem`, dan `actor_user_id = null`. Daftar/detail approval dan riwayat pemohon menampilkan `Ditolak Sistem`; notifikasi pemohon juga menjelaskan tindakan sistem dan alasannya.

Pengajuan yang sudah disetujui, dicairkan, selesai, atau ditolak tidak diubah. Membuka kembali periode tidak menghidupkan kembali pengajuan yang sudah ditolak. Penolakan, log, dan penutupan periode menggunakan transaksi database; notifikasi dikirim setelah commit.

Verifikasi: `php -d memory_limit=512M vendor/bin/phpunit tests/Feature/BsCashFlowTest.php` (SQLite `:memory:`).

Hasil verifikasi: sembilan skenario approval periode lulus; 176 dari 177 pengujian BS lulus. Satu pengujian lama, `test_both_modules_use_pooled_funds_without_references_and_reject_obsolete_funding_requests`, gagal saat mengirim rincian berindeks `0` ke endpoint edit yang mengharapkan ID rincian tersimpan. Kegagalan tersebut juga terjadi saat diuji sendiri dan berasal dari validasi kepemilikan rincian pada `TransactionDetailPhotoService`, di luar perubahan approval periode.
