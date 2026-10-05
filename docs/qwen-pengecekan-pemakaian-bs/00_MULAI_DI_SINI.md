# Paket konteks Qwen: Pengecekan Pemakaian BS Finance

Paket ini disiapkan dari kode EMMO yang ada pada 25 September 2026. Isinya adalah **kode aplikasi saat ini dan instruksi implementasi**, sesuai permintaan pemilik proyek. Fitur pengecekan Finance belum diimplementasikan oleh paket ini.

## Cara memberikan ke Qwen

1. Unggah `qwen-pengecekan-pemakaian-bs.zip`, atau buka folder hasil ekstraksinya menggunakan Qwen yang memiliki akses berkas.
2. Salin seluruh isi `01_PROMPT_UNTUK_QWEN.md` sebagai instruksi percakapan.
3. Minta Qwen membaca spesifikasi, konteks, skema, dan berkas kode yang dirujuk sebelum mengubah aplikasi.
4. Jika Qwen tidak dapat membuka ZIP, unggah dokumen `01` sampai `05`, `SCHEMA_DATABASE.md`, lalu berkas Markdown dari `kode-untuk-chat/` sesuai indeks `INDEKS_KODE.md`. Isi setiap bagian adalah kode lengkap, bukan ringkasan atau potongan yang dihilangkan.

Jangan menempel seluruh kode sekaligus jika melewati kapasitas konteks Qwen. Mulai dengan bagian inti di `INDEKS_KODE.md`, lalu tambahkan controller, UI, dan tes sesuai tahap pengerjaan. Folder `source/` tetap menjadi salinan kode lengkap dengan struktur path asli.

## Isi paket

| Berkas/folder | Isi |
| --- | --- |
| `01_PROMPT_UNTUK_QWEN.md` | Instruksi siap salin untuk Qwen |
| `02_SPESIFIKASI_FITUR.md` | Seluruh kebutuhan pengguna, dipisahkan dari asumsi implementasi |
| `03_KONTEKS_APLIKASI.md` | Fakta kode/database yang harus diperhatikan |
| `04_RENCANA_IMPLEMENTASI.md` | Peta berkas baru/perubahan, desain data dan alur yang disarankan |
| `05_KRITERIA_PENERIMAAN.md` | Kasus pengujian, regresi, dan batas verifikasi |
| `SCHEMA_DATABASE.json` | Metadata kolom, indeks, dan foreign key database lokal aktual |
| `SCHEMA_DATABASE.md` | Versi skema yang lebih mudah dibaca |
| `source/` | Salinan kode aplikasi, konfigurasi template, migrasi, tes, dan dokumentasi yang sudah ada |
| `kode-untuk-chat/` | Kode lengkap dalam bagian Markdown untuk Qwen berbasis chat |
| `INDEKS_KODE.md` | Daftar bagian Markdown dan berkas di dalamnya |
| `MANIFEST.json` | Path, ukuran, SHA-256, dan penempatan setiap berkas sumber |
| `VERIFIKASI_PAKET.json` | Hasil pemeriksaan integritas salinan/Markdown/ZIP |
| `tools/` | Alat ekspor metadata skema dan pembuatan ulang paket |

## Cakupan dan batas paket

Salinan sumber mencakup seluruh kode PHP aplikasi dalam `app/`, Blade/CSS/JS dalam `resources/`, route, konfigurasi, bootstrap utama, migrasi/seeder/factory, tes, dokumentasi proyek yang tersedia, aset JS publik, serta manifest dan lock dependensi. Ini memberi Qwen konteks modul pendukung, bukan hanya nama file yang perlu dicari sendiri.

Paket tidak membawa `.env` aktual, `vendor/`, `node_modules/`, isi database, unggahan pengguna, log, session, cache bootstrap, atau cadangan privat. `.env.example` yang berisi template ikut disertakan. Metadata skema dibaca tanpa mengambil baris transaksi atau akun pengguna.

Paket ini bukan salinan database atau aplikasi yang langsung bisa dijalankan pada database kosong. Banyak tabel bisnis berasal dari skema lama yang migrasi pembentukannya tidak tersedia di repositori. Gunakan proyek yang sudah berjalan, database pengujian terisolasi, dan metadata skema untuk implementasi. Jangan memakai `migrate:fresh` pada database aplikasi.

Kode dalam `source/` adalah snapshot referensi, bukan patch fitur baru. Qwen perlu membandingkan dengan proyek aktif jika ada perubahan setelah paket dibuat. Penyusunan paket tidak menjalankan migrasi fitur atau mengubah kode runtime aplikasi.
