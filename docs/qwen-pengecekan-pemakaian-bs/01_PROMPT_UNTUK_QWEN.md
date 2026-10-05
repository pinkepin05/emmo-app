# Instruksi untuk Qwen

Saya ingin Anda mengimplementasikan fitur **Pengecekan Pemakaian BS oleh Finance** pada aplikasi Laravel EMMO yang sudah ada. Paket terlampir berisi kode aplikasi saat ini, spesifikasi, metadata database aktual, dan rencana implementasi. Jangan membuat aplikasi baru.

## Baca konteks terlebih dahulu

Baca `02_SPESIFIKASI_FITUR.md`, `03_KONTEKS_APLIKASI.md`, `04_RENCANA_IMPLEMENTASI.md`, `05_KRITERIA_PENERIMAAN.md`, dan `SCHEMA_DATABASE.md`. Gunakan `SCHEMA_DATABASE.json` untuk detail tipe/indeks/FK. Kemudian baca kode sumber lengkap yang disebut dalam peta file. Kode ada dalam `source/` dengan path asli, atau `kode-untuk-chat/` bila Anda hanya menerima bagian Markdown.

Prioritas keputusan: instruksi pengguna ini dan spesifikasi kebutuhan; kode/skema aktual untuk fakta teknis; rencana implementasi sebagai rekomendasi. Jangan memperlakukan rekomendasi atau keputusan yang masih terbuka sebagai persetujuan bisnis yang sudah diberikan.

## Hasil yang harus dibuat

Implementasikan form lookup OPR/PD, form konfirmasi pengecekan dengan rincian per kategori, penyimpanan header/detail, riwayat lengkap, tampilan detail hasil, laporan dengan seluruh filter dan total, status "Sudah Dicek" yang dihitung dari data pengecekan, otorisasi Finance, dan navigasi yang sesuai aplikasi.

Setiap **Simpan Konfirmasi** membuat dokumen pengecekan baru. Dokumen lama tidak diubah. Nominal asal, nomor referensi, jenis, dan user diambil kembali dari transaksi sumber di server. Nominal disetujui default sama dengan nominal asal. User checker otomatis dari pengguna login, periode dari tanggal pengecekan. Tidak ada perubahan saldo BS, pembukuan, status konfirmasi sumber, atau persetujuan pemilik saldo.

Laporan default memilih pengecekan yang terakhir disimpan per pasangan jenis + ID transaksi sumber, lalu menerapkan filter. Sediakan mode semua riwayat. Bila memakai definisi terakhir yang berbeda, jelaskan sebelum implementasi karena tanggal pengecekan bisa diisi mundur. Rekomendasi definisi ini dirinci di spesifikasi dan rencana.

## Jebakan yang wajib diperhatikan

- `OPR` pada fitur baru dipetakan ke model `TransaksiOperasionalHeader` dengan nomor dokumen existing `OAS...`. Jangan mengganti nomor sumber menjadi OPR.
- Sumber punya `id` numerik dan `transaksi_id` string. Relasi detail existing memakai string tersebut. Rute detail OPR memakai ID numerik, rute detail PD memakai string `transaksi_id`.
- `user_id` pada hasil pengecekan = pemilik/penginput transaksi sumber. Pada PD grup, ini bisa berbeda dari `bs_owner_id` (pemilik uang).
- Tabel pengguna aktual bernama `user`, bukan `users`. Kolom ID user dan cost category adalah INT signed. Header OPR/PD memakai INT unsigned. Jangan otomatis memakai `foreignId()` untuk semua relasi ke tabel lama.
- Label cost category aktual ada pada `nama`. Skema lokal PD tidak memiliki kolom `document_no`, walaupun model lama mencantumkannya dalam fillable. Gunakan `transaksi_id` sebagai nomor referensi sumber.
- PD detail memakai soft delete, OPR detail aktual tidak punya `deleted_at`. Jangan memakai filter kolom PD secara buta pada OPR.
- BS baru memakai saldo gabungan; `bs_id` dapat kosong. Jangan membuat lookup yang mensyaratkan `bs_id` terisi atau menganggap satu transaksi selalu terkait satu pengajuan BS.
- Modul `BsUsageApprovalController` yang sudah ada adalah persetujuan pemilik uang; jangan menjadikannya penyimpanan pengecekan Finance.
- Middleware `bs.atomic` mengandung aturan status dan periode transaksi keuangan. Jangan memasangnya secara buta pada modul verifikasi ini.
- Seeder permission kanonis menghapus permission di luar daftar. Bila menambah permission, integrasikan dengan benar dan tambahkan akses bagi role existing secara non-destruktif.
- Satu kolom pasangan jenis + ID sumber tidak dapat mempunyai FK SQL kondisional ke dua tabel. Jelaskan apakah relasi sumber logis/polimorfik atau memakai dua FK fisik. Jangan mengklaim FK fisik jika tidak ada.

## Cara bekerja dan menyerahkan hasil

Jika Anda memiliki akses ke proyek: buat perubahan di proyek aktif, bukan hanya di folder snapshot. Jika Anda hanya berbasis chat: berikan isi lengkap semua file baru, patch yang tepat untuk file existing, urutan penerapan, serta perintah pengujian. Jangan memberikan placeholder, `...`, atau instruksi "tambahkan sendiri" pada implementasi inti.

Jaga scope pada fitur ini. Tampilkan asumsi untuk kategori berulang, tanggal/periode pengecekan, definisi selisih, definisi cek terakhir, serta penanganan perubahan/penghapusan transaksi sumber. Gunakan rekomendasi di paket bila belum ada jawaban; jangan diam-diam menambahkan batas VALIDATED-only atau nominal disetujui <= nominal asal.

Buat tes yang membuktikan penyimpanan ulang tetap membuat riwayat, field otomatis tidak dapat dimanipulasi, laporan/total benar, pengguna tanpa izin ditolak, dan saldo BS tidak berubah. Gunakan database pengujian terisolasi. Periksa kompatibilitas migrasi dengan MySQL aktual selain SQLite. Jangan menjalankan migrasi atau reset pada database operasional hanya untuk mencoba fitur.

Di akhir, laporkan daftar file dibuat/diubah, cara membuka form/laporan, skema dan permission baru, cara menjalankan migrasi pada lingkungan yang dituju, hasil tes yang benar-benar dijalankan, serta keterbatasan yang belum diuji. Bedakan hasil verifikasi aktual dari hasil historis yang tercantum di dokumentasi lama.
