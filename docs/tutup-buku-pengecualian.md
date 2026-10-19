# Tutup buku dengan pengecualian

Halaman Manajemen Periode membuka pemeriksaan sebelum tutup buku. Daftar ini mencakup seluruh pengguna dan mengikuti periode pembukuan OPR/PD serta tanggal event. Karena itu transaksi bertanggal Agustus yang disimpan pada periode September tetap muncul di September.

Jika masih ada draft, permintaan persetujuan pemilik saldo, atau transaksi/event yang belum memiliki keputusan Finance final yang sesuai versi pengajuan, pengguna berizin `akses.master-data` dapat memilih **Tutup dengan Pengecualian**. Pengguna wajib mengisi alasan dan menyetujui daftar pengecualian yang ditampilkan. Server memeriksa kembali daftar tersebut setelah mengunci periode; perubahan daftar atau nominal mengharuskan halaman dimuat ulang.

Penutupan dengan pengecualian:

- Mengubah periode menjadi `CLOSED`, mencatat pelaku dan waktu, menghitung saldo akhir, lalu meneruskan saldo ke bulan berikutnya.
- Mempertahankan transaksi tertunda beserta status, rincian, lampiran, dan pemeriksaan terdahulunya. Transaksi tidak dihapus, tidak dianggap disetujui, dan tidak dipindahkan ke bulan lain.
- Mengecualikan transaksi tertunda dari pengeluaran. Saldo BS dan Stok Kas tetap mengikuti keputusan Finance final yang masih berlaku. Draft cek ulang tidak membatalkan persetujuan final sebelumnya.
- Mengunci perubahan, penghapusan, konfirmasi pemohon, persetujuan pemilik saldo, serta penyimpanan draft/konfirmasi Finance melalui pemeriksaan periode pada backend.
- Menyimpan alasan dan salinan daftar pengecualian di `period_closing_logs` dalam transaksi database yang sama dengan saldo. Kegagalan penutupan membatalkan status, saldo, dan log.

Aturan bulan lampau, urutan periode, saldo awal final, dan syarat membuka kembali tetap berlaku. Pembukaan kembali yang memenuhi syarat mengaktifkan kembali proses transaksi; catatan pengecualian lama tetap menjadi riwayat. Penutupan berikutnya mencatat daftar baru. Transaksi yang sudah final di Finance tidak masuk daftar pengecualian, termasuk keputusan ditolak yang bernilai pengeluaran nol.

Ringkasan `cash_flow_snapshots` lama tetap memakai aturan nominal asalnya, tetapi ikut menyaring transaksi yang secara eksplisit dikecualikan. Saldo dan pengeluaran pengguna pada Stok Kas menggunakan `stock_cash_flow_snapshots` dan `bs_cash_flow_snapshots` berbasis persetujuan Finance.

Skema tambahan: `database/migrations/2026_10_12_100000_create_period_closing_logs_table.php`. Migrasi hanya menambah tabel riwayat; tidak menutup periode atau mengubah transaksi yang sudah ada.

Pengujian: `tests/Concerns/PeriodClosingExclusionScenarios.php` dan `PeriodClosingReviewScenarios.php`, dijalankan melalui `BsCashFlowTest` menggunakan SQLite `:memory:`.
