# Persetujuan Finance per rincian biaya

Mulai 7 Oktober 2026, pemeriksaan Operasional Sales dan Perjalanan Dinas menyimpan satu hasil untuk setiap baris rincian aktif. Kategori yang sama tidak lagi digabung. BBM Rp150.000 dan Uang Makan Rp25.000 pada kategori yang sama memiliki input nominal disetujui, selisih, dan catatan internal masing-masing. Form, draft, hasil pemeriksaan, dan laporan menampilkan keterangan sumber untuk membedakan rincian.

Total persetujuan tetap merupakan penjumlahan nominal setiap rincian. Aturan saldo, konfirmasi Finance, penolakan, pemeriksaan ulang, dan batas periode mengikuti [sinkronisasi Stok Kas](sinkronisasi-stok-kas-finance.md). Event tetap memiliki satu item karena sumbernya menyimpan satu nominal.

## Identitas dan riwayat

- Snapshot hasil dan draft menyimpan `source_detail_id`, kategori, `source_remark`, nominal asal, nominal disetujui, dan catatan. Identitas serta keterangan diambil dari sumber di server, bukan dari isian pengguna.
- Permintaan harus memuat setiap ID rincian aktif tepat satu kali. Rincian tambahan, hilang, duplikat, milik transaksi lain, atau kategori yang tidak sesuai ditolak. Form lama tanpa ID rincian harus dimuat ulang.
- Persetujuan baru dicocokkan per ID rincian, kategori, nominal asal, keterangan, dan versi pengajuan. Perubahan dua nominal yang saling meniadakan tetap memerlukan pemeriksaan ulang. Rincian PD yang dihapus tidak ikut dihitung.
- Hasil lama yang digabung per kategori tidak dipecah atau dihitung ulang oleh migrasi. Nilai dan riwayat persetujuannya tetap berlaku menurut aturan pencocokan lama sampai ada perubahan sumber atau konfirmasi Finance berikutnya.
- Saat membuka kembali hasil/draft lama, nilai yang dapat dipetakan ke satu rincian tetap tersedia. Jika satu kategori berisi beberapa rincian, nominal persetujuan dikosongkan dan form meminta Finance mengisi setiap rincian. Nilai gabungan tidak dibagi rata atau disalin ke beberapa baris. Draft lama tetap utuh sampai Finance menyimpan lagi.
- Hasil yang sudah dikonfirmasi tetap dapat dibaca setelah rincian sumber diubah atau dihapus. Catatan internal tetap hanya tersedia bagi Finance.

## Penerapan

Jalankan migrasi `2026_10_07_160000_track_finance_approval_per_source_detail.php` bersama perubahan aplikasi. Migrasi menambahkan dua kolom snapshot yang nullable dan mengganti batas unik per kategori dengan batas unik per rincian dalam satu dokumen. Tidak ada pembaruan nominal historis atau transaksi sumber.

Pengujian terdapat pada `FinanceItemApprovalScenarios`, `BsUsageCheckTest`, `BsCashFlowTest`, dan `DashboardAccessTest`, menggunakan database SQLite dalam memori.
