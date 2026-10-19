# Saldo BS gabungan

Pembaruan 7 Oktober 2026: [sinkronisasi Stok Kas dan saldo BS](sinkronisasi-stok-kas-finance.md). Saldo kini berkurang setelah konfirmasi Finance, termasuk pengeluaran event; ketentuan waktu pembukuan di bawah merupakan dokumentasi sebelumnya.

Mulai 24 September 2026, pencairan BS Operasional Sales dan Perjalanan Dinas masuk ke satu saldo per pemilik. Jenis pengajuan tetap dicatat untuk informasi dan laporan. Jenis tersebut tidak membatasi kategori realisasi atau membentuk saldo terpisah.

## Pengajuan dan pencairan

Nomor BS mengidentifikasi pengajuan, persetujuan, dan pencairan. Pengajuan dan approval atasan belum menambah saldo; hanya pencairan Finance yang menambah saldo pada bulan pencairannya. Detail pengajuan tidak lagi mempunyai rincian pemakaian atau sisa dana per dokumen. Laporan pengajuan dan ekspornya mencatat pengajuan serta pencairan, termasuk pengajuan yang masih menunggu atau ditolak.

## Realisasi

- Form Operasional Sales memakai saldo penginput sendiri, tanpa referensi nomor BS.
- Form Perjalanan Dinas memakai saldo sendiri atau saldo pemohon yang memberikan akses grup. Pilih **Pemilik Saldo BS**, bukan nomor pengajuan.
- Anggota dari pengajuan Perjalanan Dinas yang sudah dicairkan dapat memakai **seluruh saldo gabungan pemohon**, termasuk dana dari pencairan jenis lain. Akses anggota hanya berlaku pada modul Perjalanan Dinas. Keanggotaan tidak membagi saldo menjadi jatah per anggota/per pengajuan. Hak akses diperiksa kembali saat pengajuan pemakaian dan saat persetujuan pemohon.
- Draft dan permintaan yang menunggu tidak mengurangi atau mencadangkan saldo. Konfirmasi saldo sendiri langsung memeriksa total positif, periode OPEN, hak penginput, dan saldo tersedia. Konfirmasi anggota mengirim permintaan kepada pemohon; persetujuan pemohon yang membukukan realisasinya. Transaksi final tetap tidak dapat diedit/dihapus.
- Saldo tersedia = saldo awal final + pencairan aktual bulan transaksi - realisasi VALIDATED + penyesuaian historis. Saldo bulan sebelumnya tersedia sesudah tutup buku. Dana dari bulan mendatang tidak membiayai bulan sebelumnya.

Contoh: pencairan Operasional Rp1 juta dan Perjalanan Dinas Rp2 juta memberi saldo Rp3 juta. Realisasi Operasional Rp1,5 juta diperbolehkan; realisasi Perjalanan Dinas Rp500 ribu sesudahnya menyisakan Rp1 juta.

## Persetujuan pemakaian anggota grup dinas

Pemohon membuka **Persetujuan Pemakaian BS** dari dashboard, Rincian Saldo BS, atau notifikasi aplikasi. Jumlah permintaan menunggu mencakup semua periode. Halaman khusus ini dapat diakses pemilik melalui izin Pengajuan BS, Perjalanan Dinas, atau Cash Flow, termasuk ketika pemilik tidak memiliki izin modul Perjalanan Dinas. Persetujuan tetap hanya boleh dilakukan pemilik saldo; peran admin tidak memberi hak menyetujui saldo orang lain.

| Tindakan | Status | Pengaruh pada saldo |
| --- | --- | --- |
| Simpan Draft | DRAFT | Tidak ada |
| Konfirmasi anggota / Simpan & Ajukan Pemakaian | PENDING_OWNER (Menunggu Persetujuan Pemohon) | Tidak ada; rincian terkunci |
| Pemohon Setujui & Konfirmasi | VALIDATED (Dikonfirmasi) | Mengurangi saldo pemohon; final |
| Pemohon Tolak Pemakaian, dengan alasan | REJECTED_OWNER (Ditolak Pemohon) | Tidak ada |
| Anggota Tarik Permintaan ke Draft | DRAFT | Tidak ada; boleh diperbaiki |
| Anggota Buka Draft untuk Perbaikan setelah ditolak | DRAFT | Tidak ada; perlu diajukan ulang |

Pemohon melihat anggota, tanggal, tujuan, keterangan, kategori biaya, seluruh rincian, total, saldo tersedia, serta riwayat keputusan. Setelah persetujuan atau penolakan, anggota mendapat notifikasi aplikasi. Saat anggota menarik permintaan, pemohon juga diberi notifikasi. Notifikasi dikirim setelah transaksi database berhasil disimpan. Tidak ada persetujuan otomatis atau pengurangan saldo hanya karena pemohon belum merespons.

Permintaan menunggu **tidak menyisihkan saldo**. Saldo dan akses grup diperiksa kembali ketika pemohon menyetujui. Jika dana sudah dipakai transaksi lain, persetujuan ditolak dan permintaan tetap menunggu. Pemohon dapat menolak dengan alasan atau anggota dapat menarik permintaan walaupun keanggotaan grup sudah dicabut. Transaksi menunggu memblokir tutup buku seperti draft; transaksi ditolak tidak dihitung sebagai realisasi. Semua perubahan hanya diperbolehkan pada periode OPEN.

Setiap pengajuan ulang menaikkan `approval_version`. Form persetujuan membawa versi yang diperiksa; persetujuan dari halaman lama gagal jika anggota sudah menarik, memperbaiki, lalu mengajukan ulang. Riwayat `bs_usage_approval_logs` menyimpan pengajuan, persetujuan, penolakan, penarikan, serta pembukaan draft, dengan versi, pelaku, waktu, nominal, dan alasan. Rincian bisa diubah hanya setelah kembali ke draft. Semua perubahan status, audit final, dan ringkasan kas pada persetujuan berada dalam satu transaksi database. Penguncian periode, pengguna, dan dokumen serta pemeriksaan status mencegah pencatatan berulang.

Migrasi `2026_09_24_160000_add_bs_owner_approval.php` menambah metadata persetujuan dan tabel riwayat. Transaksi historis yang sudah VALIDATED tetap final, tidak dipaksa mengulang persetujuan. Cadangan kode sebelum perubahan ini tersedia di `storage/app/private/bs-owner-approval-backup-20260924-151346/`. Setelah ada permintaan baru, rollback kode/schema memerlukan rekonsiliasi status dan arsip persetujuan terlebih dahulu.

## Rincian dan data lama

**Rincian Saldo BS** (`/saldo-bs`) tersedia dari kartu saldo di dashboard serta Stok Kas. Halaman ini menampilkan saldo awal, pencairan, pemakaian kedua modul, penyesuaian historis, dan saldo berjalan per periode. Pengeluaran anggota masuk pada rincian pemilik dan mencantumkan penginput. Rincian transaksi tetap mengikuti izin modul. Halaman Stok Kas beserta Excel tetap menggabungkan kas BS dan event; event tidak mengurangi batas saldo BS.

Transaksi baru menyimpan `bs_owner_id` tanpa `bs_id`. Referensi lama tetap disimpan dan dibaca untuk menentukan pemilik dana historis. Transaksi historis final tanpa BS tidak otomatis dibebankan ke saldo. Draft lama dapat dikonfirmasi memakai saldo gabungan sesuai akses dan kecukupan dana. Riwayat settlement yang sudah tercatat tetap dihitung. Endpoint settlement per pengajuan menolak permintaan karena tidak sesuai dengan saldo gabungan, termasuk bila flag lama diubah.

Konfirmasi dicatat di `bs_usage_logs`, meliputi nomor transaksi, pemilik saldo, penginput, aktor konfirmasi, periode, nominal, dan waktu. Pencatatan, perubahan status, dan ringkasan kas berada dalam satu transaksi database. Penguncian pengguna/pemilik dan periode mencegah konfirmasi memakai saldo lama saat ada permintaan lain.

## Migrasi dan verifikasi

Migrasi `2026_09_24_140000_add_pooled_bs_funding.php` menambah kolom pemilik saldo dan tabel audit. Tidak memindahkan atau mengubah nominal transaksi lama. Kolom lama dipertahankan untuk arsip. Rollback schema setelah sudah ada pemakaian baru memerlukan rekonsiliasi/restore data terlebih dahulu, karena transaksi baru tidak mempunyai referensi pengajuan untuk dihitung oleh kode lama.

Migrasi telah dijalankan pada MySQL lokal. Sidik data sebelum/sesudah membuktikan seluruh baris dan nilai lama pada sembilan tabel keuangan tidak berubah. Enam pemeriksaan route pada skema MySQL asli (kedua form, rincian saldo, Cash Flow, dan kedua endpoint saldo) berhasil; seluruh perubahan database dalam pemeriksaan dibatalkan. Cadangan kode sebelum perubahan tersedia di `storage/app/private/bs-pooled-balance-backup-20260924-134913/`.

Verifikasi saldo gabungan sebelum penambahan persetujuan: `php artisan test --compact` lulus **131 test, 1.727 assertion**, memakai database SQLite dalam memori. Pengujian mencakup saldo lintas jenis, batas saldo gabungan, draft/final, akses anggota khusus PD, penarikan akses, privasi endpoint, rollback saat pencatatan gagal, laporan/Excel, arsip tanpa BS, tanggal pencairan, dan rollover setelah close. Pemeriksaan sintaks `public/js/bs-balance.js` lulus. Pengujian konkurensi MySQL secara simultan belum dijalankan.


Verifikasi persetujuan pemohon: `php artisan test --compact` lulus **140 test, 1.902 assertion**. Kasus tambahan mencakup permintaan menunggu tanpa pengurangan/cadangan saldo, otorisasi pemilik (termasuk penolakan akses admin lain), pemilik tanpa izin modul PD, rincian terkunci, penarikan/perbaikan/pengajuan ulang, penolakan dengan alasan, versi halaman lama, akses grup dicabut, saldo berubah sebelum persetujuan, persetujuan berulang, rollback kegagalan ringkasan kas, serta tutup buku. Hasil JUnit ada di `storage/app/private/bs-owner-approval-results.xml`.

Migrasi persetujuan telah diterapkan pada database lokal setelah MySQL XAMPP dihidupkan kembali. Sidik data sebelum/sesudah membuktikan seluruh baris dan nilai sebelumnya pada **10 tabel keuangan tetap sama**. Delapan pemeriksaan GET pada skema lokal berhasil, termasuk daftar permintaan, dashboard, kedua form, daftar PD, rincian saldo, dan kedua endpoint saldo. Halaman pemeriksaan pemohon berhasil dirender dengan contoh dalam memori tanpa memasukkan transaksi contoh ke database. Perubahan ringkasan yang mungkin dibuat GET dibatalkan melalui rollback. JavaScript eksternal dan dua skrip inline formulir PD lolos pemeriksaan sintaks. Skrip verifikasi tersedia di `storage/app/private/bs-owner-approval-smoke.php` dan `storage/app/private/bs-owner-approval-migration-check.php` (mode `after`). Pengujian konkurensi MySQL simultan tetap belum dilakukan.
