# Waktu aplikasi

Waktu aksi mengikuti jam server saat aksi berhasil diproses. Tampilan memakai
`APP_DISPLAY_TIMEZONE=Asia/Jakarta` (WIB) secara default, dengan presisi detik.
Sinkronkan jam sistem operasi server secara otomatis agar waktu aksi akurat.

`config('app.timezone')` tetap UTC, sesuai nilai timestamp yang sudah ditulis
aplikasi. `App\Support\AppTime::format()` mengubah timestamp tersebut ke zona
tampilan tanpa mengubah nilai tersimpan. Jangan mengubah zona penyimpanan atau
zona sesi database tanpa memeriksa dan memigrasikan data lama: instalasi saat ini
memakai zona sesi MySQL `SYSTEM` dan nilai yang ditulis Laravel dalam UTC.

Sumber waktu:

| Aksi | Timestamp |
| --- | --- |
| Pengajuan BS | `pengajuan_bs.created_at` |
| Approval atau penolakan atasan/Finance | `bs_activity_logs.created_at` untuk aksi terkait |
| Pencairan | `approval_bs_finance.created_at` |
| Konfirmasi operasional/perjalanan dinas | `validated_at` |
| Penyelesaian BS | `settled_at` |
| Tutup/buka periode | `closed_at` / `reopened_at` |

`tanggal` dan `tanggal_cair` adalah tanggal bisnis bertipe DATE. Kolom ini tidak
mengandung jam dan tidak digunakan sebagai sumber waktu aksi. Pengajuan dan
pencairan baru memakai tanggal kalender lokal, termasuk untuk nomor dokumen dan
periode. Tanggal transaksi yang dipilih pengguna tetap merupakan tanggal bisnis.

Data lama langsung ditampilkan dari timestamp yang tersedia. Timestamp kosong
ditampilkan sebagai `-`; aplikasi tidak menebak jam yang belum pernah tercatat.
Waktu penolakan diambil dari log aksi agar tidak berubah ketika dokumen diedit.

Setelah mengubah konfigurasi pada server, jalankan `php artisan config:clear`
dan `php artisan view:clear`; mulai ulang worker jangka panjang jika digunakan.
Pemeriksaan baca saja pada MySQL: `php scripts/inspect-application-time.php`.
Pengujian: `php artisan test` (database SQLite dalam memori sesuai phpunit.xml).
