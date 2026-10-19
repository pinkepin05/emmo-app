# Stok Kas dan Sisa Saldo BS mengikuti konfirmasi Finance

Mulai perubahan 7 Oktober 2026, periode OPEN menggunakan sumber pengeluaran yang sama: nominal disetujui pada **hasil konfirmasi Finance terakhir**, untuk versi transaksi yang masih berlaku. Berlaku bagi Operasional Sales, Perjalanan Dinas, Event Dalam Kota, dan Event Luar Kota.

- Konfirmasi pengguna dan persetujuan pemilik BS mengirim pengajuan untuk diperiksa; belum mengurangi saldo atau menyisihkan dana.
- Draft pengecekan Finance belum mengubah saldo. Draft pemeriksaan ulang tetap mempertahankan hasil konfirmasi sebelumnya sampai Finance mengonfirmasi hasil baru.
- Konfirmasi APPROVED mengurangi saldo sebesar nominal disetujui. Hasil REJECTED, keputusan lama REVISION, dan pengajuan Pending (termasuk yang berubah setelah diperiksa) tidak dibukukan sebagai pengeluaran.
- Pemeriksaan ulang mengganti nominal sebelumnya, bukan menambahkannya. Dokumen pemeriksaan lama tetap tersimpan.
- Pengeluaran anggota perjalanan dinas mengurangi saldo pemilik BS. Event mengurangi saldo pengguna pemilik event.
- Nominal dibukukan ke periode transaksi asal (event mengikuti tanggal event), meskipun tanggal dokumen pengecekan berbeda bulan.
- Pemeriksaan kecukupan saldo pada pengajuan tetap berlaku. Finance memeriksa ulang kecukupan dana dengan memperhitungkan nominal sebelumnya ketika merevisi keputusan. Pengeluaran yang melampaui saldo ditolak atomik.

Rumus bersama: **saldo awal + pencairan − pengeluaran dikonfirmasi Finance + penyesuaian BS**. Rincian event di Stok Kas adalah bagian dari Total Keluar yang sama; tidak dikurangkan dua kali. Dashboard, rincian, dan Excel menggunakan sumber yang sama. Sisa Saldo BS ditampilkan saat periode berjalan; Saldo Akhir tetap diterbitkan setelah tutup buku.

Tutup buku menunggu pengajuan dan event selesai diperiksa Finance. Konfirmasi Finance pada periode transaksi CLOSED ditolak; periode harus dibuka kembali melalui alur yang tersedia bila perlu koreksi.

Migration `2026_10_07_120000_mark_finance_based_cash_snapshots.php` menambahkan penanda `finance_confirmed` pada kedua snapshot. Snapshot CLOSED lama beserta basis rincian lamanya dipertahankan. Snapshot OPEN dihitung ulang otomatis saat laporan dibuka atau Finance mengonfirmasi. Saldo awal Stok Kas dan BS periode berikutnya sama-sama meneruskan **Saldo Akhir Stok Kas yang sudah ditutup**, termasuk biaya event historis. Saldo BS lama hanya menjadi cadangan bila snapshot penutupan Stok Kas belum tersedia. Saldo nol dan negatif tetap diteruskan apa adanya; biaya historis tidak dikurangkan ulang sebagai pengeluaran bulan baru.

Contoh transisi: September ditutup dengan Stok Kas Rp16.780.000, sementara arsip BS lama Rp18.930.000. Saldo awal Oktober untuk kedua perhitungan adalah Rp16.780.000. Dengan pencairan Rp1.200.000 dan konfirmasi Finance Rp100.000, sisa Oktober menjadi Rp17.880.000. Arsip September tetap utuh.

Ketentuan ini menggantikan keterangan lama bahwa keputusan Finance dan biaya event tidak mengubah saldo BS.
