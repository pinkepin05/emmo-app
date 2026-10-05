# Penghapusan fitur uang pribadi dan reimbursement

Mulai 24 September 2026, fitur uang pribadi dan reimbursement dihapus sesuai keputusan pengguna.

- Operasional Sales dan Perjalanan Dinas memakai [saldo BS gabungan](saldo-bs-gabungan.md), tanpa memilih referensi pengajuan. Jenis pengajuan tidak membatasi pemakaian.
- Formulir menampilkan saldo tersedia. Perjalanan Dinas menyediakan pilihan pemilik saldo untuk anggota grup yang berhak.
- Permintaan lama dengan sumber dana PERSONAL atau referensi `bs_id` ditolak. Konfirmasi harus mempunyai saldo gabungan yang cukup.
- Draft lama tanpa BS dapat dikonfirmasi memakai saldo gabungan penginput. Transaksi final historis tanpa BS tetap menjadi arsip dan tidak dibebankan ulang.
- Menu, halaman, endpoint persetujuan/pembayaran, dan notifikasi reimbursement tidak tersedia lagi.
- Stok Kas, dashboard, detail, dan Excel menghitung pencairan BS, pengeluaran BS, serta event dalam/luar kota. Uang pribadi dan pembayaran reimbursement tidak masuk perhitungan.

Rumus Stok Kas: **saldo awal gabungan + pencairan BS - pengeluaran BS - event dalam kota - event luar kota + penyesuaian BS**.

Sisa Saldo BS tetap dihitung melalui kas BS tersendiri. Penyesuaian settlement BS historis tetap mengikuti aturan BS yang sudah ada.

## Data yang sudah tersimpan

Dokumen transaksi, klaim, log, dan foto pembayaran lama dipertahankan sebagai arsip. Transaksi lama tanpa BS tetap dapat dibaca pada detail transaksi; dokumen final tetap tidak dapat diubah. Tidak ada penghapusan tabel, rollback migration, atau pengubahan otomatis transaksi lama menjadi transaksi BS.

Tabel/model klaim, disk foto privat, dan kolom integrasi Stok Kas tetap disimpan untuk kompatibilitas arsip. Perhitungan ulang periode OPEN mengosongkan kedua komponen lama dan menghitung ulang total dari BS dan event. Snapshot CLOSED tetap dibekukan. Pemeriksaan database lokal sebelum perubahan tidak menemukan komponen uang pribadi/reimbursement bernominal pada snapshot CLOSED; lingkungan lain dengan saldo CLOSED yang sudah mencakup komponen tersebut memerlukan rekonsiliasi historis sebelum menggunakan laporan tanpa komponen itu.

Tidak diperlukan migration baru. Periode OPEN diperbarui ketika Stok Kas/dashboard/ekspor dibuka.

## Verifikasi

Pengujian di tests/Feature/BsCashFlowTest.php memakai SQLite dalam memori, mencakup penggunaan saldo gabungan pada tambah/edit/konfirmasi, penolakan permintaan PERSONAL lama, endpoint yang dihapus, akses baca dokumen historis, perhitungan Stok Kas/Excel tanpa komponen lama, penutupan dan saldo awal bulan berikutnya, serta regresi BS dan event.
