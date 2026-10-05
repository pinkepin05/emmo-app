# Spesifikasi Pengecekan Pemakaian BS (Finance)

## Tujuan

Mencatat hasil pengecekan/verifikasi Finance atas transaksi pemakaian BS pada Operasional Area Sales (OPR) dan Perjalanan Dinas (PD). Modul berfungsi untuk verifikasi dan laporan. Hasil pengecekan tidak memengaruhi perhitungan Sisa BS.

## Header yang diminta pengguna

| Field | Sumber/perilaku |
| --- | --- |
| Transaksi ID | Dibuat sistem; identitas dokumen pengecekan |
| Document No | Nomor dokumen pengecekan; nomor baru setiap penyimpanan konfirmasi |
| Tanggal Pengecekan | Tanggal verifikasi |
| Jenis Transaksi Pemakaian | OPR atau PD; otomatis dari referensi, view only |
| No Referensi Transaksi Pemakaian BS | Nomor transaksi sumber, otomatis, view only |
| Transaksi Pemakaian BS ID | Identitas sumber; relasi ke OPR atau PD sesuai jenis |
| User ID | User pemilik/penginput transaksi sumber; otomatis, view only |
| User Checker ID | Usulan pengguna: Finance yang melakukan pengecekan, untuk audit trail |
| Periode | Mengikuti bulan Tanggal Pengecekan |

## Detail yang diminta pengguna

| Field | Sumber/perilaku |
| --- | --- |
| ID | Dibuat sistem |
| Transaksi ID | FK ke header pengecekan |
| Cost Category ID (Item) | Dari detail transaksi sumber |
| Nominal Asal | Dari nominal detail sumber pada kategori terkait; otomatis, view only |
| Nominal Disetujui | Input Finance; usulan default = Nominal Asal |
| Catatan | Catatan Finance per item |

## Aturan yang diminta pengguna

1. Lookup hanya transaksi OPR dan PD yang sudah tersimpan. Jenis event/marketing, pengajuan BS, dan pencairan tidak masuk lookup.
2. Jenis, nomor referensi, user, dan nominal asal tidak dapat diedit. Polanya mengikuti nominal snapshot pada Approval BS Finance. Backend juga harus menjaga sumber nilai tersebut.
3. Satu transaksi pemakaian dapat dicek lebih dari satu kali. Setiap Simpan Konfirmasi membuat transaksi pengecekan dan nomor dokumen baru. Riwayat lama tetap utuh.
4. Laporan default menampilkan hasil pengecekan terakhir per transaksi sumber; tersedia opsi seluruh riwayat.
5. Nominal disetujui default sama dengan nominal asal, lalu Finance menyesuaikan per item. Ini merupakan usulan dalam permintaan dan disarankan diterapkan.
6. Hasil verifikasi tidak mengubah Sisa BS. Struktur bisa dikembangkan dengan flag/relasi di masa depan, tanpa menambahkan dampak finansial sekarang.
7. Status "Sudah Dicek" diturunkan dari keberadaan hasil pengecekan, bukan kolom status baru pada OPR/PD. Ini merupakan usulan dalam permintaan dan disarankan diterapkan.
8. Simpan user checker dari sesi login untuk audit trail. Ini merupakan usulan dalam permintaan dan disarankan diterapkan.

## Relasi

- Header pengecekan → `User` sebagai pemilik transaksi sumber.
- Header pengecekan → `User` sebagai Finance/checker.
- Header pengecekan → header OPR atau PD sesuai jenis dan ID sumber.
- Detail pengecekan → header pengecekan.
- Detail pengecekan → `CostCategory`.
- Nominal asal detail ditarik dari detail OPR/PD pada kategori yang sesuai ketika konfirmasi disimpan.

## Laporan

Filter wajib: rentang tanggal pengecekan, periode, jenis transaksi, nomor referensi, user, status selisih (sesuai/tidak sesuai), dan mode (cek terakhir/semua riwayat).

Kolom wajib, berurutan:

| No Transaksi Pengecekan | Tanggal Pengecekan | User | Jenis | No Referensi | Item | Nominal Asal | Nominal Disetujui | Selisih | Catatan |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |

Footer: Total Nominal Asal, Total Nominal Disetujui, Total Selisih. Rekomendasi: total seluruh hasil setelah filter, bukan hanya halaman yang sedang tampil; beri label jelas.

## Keputusan implementasi yang belum dinyatakan pengguna

Bagian ini adalah rekomendasi, bukan tambahan syarat bisnis yang sudah disetujui.

| Topik | Rekomendasi awal |
| --- | --- |
| Status sumber | Semua OPR/PD tersimpan dapat dipilih, termasuk draft/pending/rejected. Tampilkan status sumber. Jangan membatasi hanya VALIDATED tanpa keputusan baru. Tolak konfirmasi pengecekan jika sumber tidak mempunyai item aktif. |
| Transaksi historis tanpa BS | Jangan mensyaratkan `bs_id`, karena transaksi baru memakai `bs_owner_id`. Jika ingin membatasi data arsip non-BS, tentukan aturan eksplisit; awalnya ikuti semua OPR/PD tersimpan. |
| Kategori yang muncul beberapa kali | Satu baris pengecekan per kategori dengan SUM nominal detail aktif. Alternatif per baris sumber memerlukan source_detail_id; jangan memakai first() yang menghilangkan nominal duplikat. |
| Definisi selisih | Nominal Asal dikurangi Nominal Disetujui. Nilai positif = nominal asal lebih besar. Jelaskan formula di UI. |
| Status sesuai | Per dokumen: semua item berselisih nol. Tidak sesuai bila minimal satu item berbeda, walaupun total selisih saling meniadakan. Filter status dokumen tetap menampilkan semua item dokumen yang lolos. |
| Nominal disetujui | Wajib angka, minimal 0, maksimal 2 desimal, batas sesuai DECIMAL(15,2). Boleh melebihi nominal asal karena pengguna belum melarangnya; jangan otomatis membuat batas <= asal. |
| Tanggal dan periode | Tanggal default mengikuti AppTime::now(); periode YYYY-MM berasal dari tanggal tersebut. Modul laporan/verifikasi ini tidak perlu otomatis diblokir oleh periode pembukuan yang CLOSED. Kebijakan tanggal masa depan/backdate belum ditentukan pengguna. |
| Arti cek terakhir | Dokumen terakhir berhasil disimpan per pasangan jenis + ID sumber, deterministik melalui ID pengecekan yang meningkat. Tanggal bisnis tidak harus sama dengan urutan simpan. Pilih latest global sebelum filter tanggal/periode/status. |
| Nomor dokumen | Dibuat sistem memakai layanan nomor yang ada; contoh prefix baru CEKBS dan format CEKBS-YYYYMMDD-001. Prefix ini rekomendasi, bukan standar existing. |
| Penghapusan sumber | Riwayat pengecekan tetap tampil dari snapshot jika sumber draft kemudian dihapus. Untuk relasi logis, source dapat menjadi null saat dibuka; tampilkan referensi historis. Jika memilih FK fisik restrict, jelaskan perubahan perilaku penghapusan sumber. |
| Mutasi setelah dicek | Hasil lama tetap snapshot. Status Sudah Dicek berarti pernah dicek, bukan jaminan rincian terkini sama. Deteksi form kedaluwarsa sebelum save agar Finance memeriksa nominal terbaru. |
| Akses | Rekomendasi memakai permission Finance existing `akses.approval-finance` untuk awal. Permission terpisah dapat dibuat bila diperlukan; harus diintegrasikan dengan seeder dan role existing. |
| Ekspor/cetak | Pengguna meminta laporan. Ekspor Excel/cetak adalah peningkatan opsional setelah form/laporan wajib selesai; bila dibuat harus mengikuti filter yang sama. |

Jika Qwen memilih alternatif pada tabel ini, jelaskan dampaknya. Jangan mengubah aturan inti riwayat, field otomatis, batas jenis sumber, atau saldo BS.
