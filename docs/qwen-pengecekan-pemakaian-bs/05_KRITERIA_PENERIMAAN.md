# Kriteria penerimaan dan pengujian

Dokumen ini adalah target verifikasi untuk implementasi Qwen, bukan klaim bahwa fitur sudah dibuat atau dites.

## Penyimpanan dan audit

- Finance membuka OPR dan PD tersimpan; jenis selain dua itu ditolak lookup dan endpoint simpan.
- Sumber DRAFT/PENDING_OWNER/REJECTED_OWNER tidak otomatis disembunyikan karena spesifikasi hanya mensyaratkan tersimpan. Tampilkan status agar pemeriksa memahami sumber.
- Nomor sumber OPR tetap OAS; nomor PD tetap PD. Source ID numerik dan nomor referensi tidak tertukar.
- User sumber, checker, periode, nomor referensi, jenis dan nominal asal benar. Permintaan yang mengirim nilai otomatis palsu tidak memengaruhi data tersimpan.
- PD anggota memakai user anggota sebagai pemilik transaksi hasil pengecekan, walaupun uang milik pemohon lain.
- Nilai persetujuan default = asal; nominal 0 sah. Negatif, format bukan angka, >2 desimal, nilai melebihi DECIMAL(15,2), dan catatan terlalu panjang ditolak.
- Kategori asing, kategori ganda, kategori hilang, detail kosong, sumber hilang dan jenis tidak sah ditolak tanpa header/detail parsial.
- Kategori yang berulang di sumber mengikuti kebijakan agregasi yang dipilih. Untuk SUM, tidak ada nominal sumber yang hilang dan detail PD terhapus tidak dihitung.
- Jika nominal/kategori/rincian sumber berubah setelah form dibuka, penyimpanan form lama gagal dengan pesan muat ulang. Uji juga sumber dihapus selama form terbuka.
- Dua kali Simpan Konfirmasi pada satu sumber menghasilkan dua header dan nomor unik; detail lama dan catatan lama tetap sama.
- Gagal menyimpan detail tidak meninggalkan header/sequence parsial.
- Dua konfirmasi paralel tidak menerbitkan nomor dokumen yang sama. Pengujian race sebenarnya memerlukan MySQL; jangan mengklaim SQLite in-memory membuktikannya.
- Hasil yang sudah tersimpan tidak mempunyai endpoint edit/delete publik; cek ulang membuat record baru.
- Snapshot hasil tetap sama saat nominal/nama/nomor sumber berubah menurut cakupan snapshot yang dipilih. Jika source dihapus, riwayat nominal dan referensi tetap dapat dibaca.

## Laporan dan total

Fixture minimum yang disarankan (data sintetis):

| Sumber | Cek | Urutan simpan | Tanggal cek | Nominal asal item | Nominal disetujui item |
| --- | --- | --- | --- | --- | --- |
| OPR ID 10 | A | 1 | 2026-09-01 | 100,00 dan 50,00 | 90,00 dan 50,00 |
| OPR ID 10 | B | 2 | 2026-09-10 | 100,00 dan 50,00 | 100,00 dan 50,00 |
| PD ID 10 | C | 3 | 2026-09-05 | 200,00 | 180,00 |

- Mode latest: B dan C saja; ID OPR 10 dan PD 10 tidak bertabrakan. Total asal 350,00; disetujui 330,00; selisih 20,00.
- Mode semua: A, B, C; total asal 500,00; disetujui 470,00; selisih 30,00.
- Filter tanggal 1–5 pada latest: C saja; A tidak muncul kembali karena B latest berada di luar rentang. Mode semua menampilkan A dan C.
- Filter sesuai pada latest: B; filter tidak sesuai: C. Mode semua tidak sesuai: A dan C.
- Uji dokumen dua item dengan selisih +10 dan -10: tetap tidak sesuai, meskipun total selisih 0.
- Uji pengecekan D untuk OPR 10 yang disimpan setelah B tetapi diberi tanggal lebih awal: D tetap latest bila definisi urutan simpan dipakai.
- Uji filter periode, jenis, nomor referensi, user, kombinasi filter, pagination, hasil kosong dan rentang tanggal terbalik.
- Footer menghitung seluruh hasil terfilter walaupun ada beberapa halaman; bukan seluruh database dan bukan hanya halaman pertama.
- Catatan `<script>` tampil sebagai teks. Jika ekspor dibuat, teks seperti `=1+1` tidak dieksekusi sebagai formula.
- Jika ekspor dibuat, dataset dan total sama dengan layar untuk semua mode/filter; presisi desimal tidak hilang.

## Akses dan navigasi

- Guest diarahkan ke login; pengguna terautentikasi tanpa permission Finance ditolak pada index/create/lookup/store/show/report/export.
- Finance yang tidak punya permission modul OPR/PD tetap dapat memeriksa sumber melalui endpoint fitur yang memang diizinkan.
- Menyembunyikan tombol saja tidak cukup; POST dan lookup langsung juga dilindungi.
- Sidebar form dan laporan terlihat untuk izin yang tepat, termasuk ketika flag laporan BS lama false.
- Derived status tampil benar sebelum/sesudah pemeriksaan dan setelah cek ulang; tidak ada kolom boolean baru pada sumber.
- Bila permission baru dibuat: role Finance existing memperoleh akses melalui jalur provisioning yang jelas; permission tidak hilang setelah PermissionSeeder dijalankan; izin lain tidak terhapus akibat sync tambahan.

## Tidak mengubah saldo BS

Ambil saldo dan status sumber sebelum konfirmasi pengecekan, simpan hasil dengan nominal disetujui berbeda, lalu bandingkan sesudahnya:

- Sisa saldo BS pemilik dan anggota tidak berubah.
- Total keluar dan saldo stok kas tidak berubah.
- Source nominal, status, validated_at, validated_by, approval_version dan keputusan pemilik saldo tidak berubah.
- Tidak ada baris baru di bs_usage_logs/bs_usage_approval_logs/pencairan/reimbursement akibat verifikasi.
- Pengecekan ulang juga tidak mengubah nilai-nilai tersebut.
- Periode pengecekan mengikuti tanggal pengecekan, bukan tanggal transaksi sumber. Pemeriksaan sumber periode lampau tidak membuka ulang atau menutup periode pembukuan.

## Menjalankan tes pada proyek

Gunakan `phpunit.xml` yang mengarahkan ke SQLite `:memory:`. Existing test menegaskan databaseName = :memory: sebelum menyiapkan schema. Jangan memakai .env operasional sebagai database fixture tes.

Contoh perintah setelah Qwen membuat implementasi dan fixture:

```powershell
php artisan test --filter=BsUsageCheckTest
php artisan test --filter=DocumentNumberTest
php artisan test --filter=BsCashFlowTest
php artisan test --compact
```

Perintah tersebut belum dijalankan sebagai pengujian fitur baru dalam pembuatan paket ini. Jika nomor dokumen/derived status mengubah query halaman existing, sesuaikan fixture yang menyiapkan tabel secara manual. Jangan mengakali kegagalan tes dengan melewati authorization atau pengecekan saldo.

Lint file PHP baru/perubahan dan periksa Blade dengan request render pada tes. Uji migrasi MySQL pada database pengujian tersendiri dengan tipe user.id/category.id signed INT. Catat secara eksplisit jika uji MySQL atau concurrency belum dijalankan.

## Serah terima akhir dari Qwen

Hasil akhir harus memuat file baru lengkap, patch existing yang dapat diterapkan, nama migrasi dan urutan penerapan, izin/menu, route form dan laporan, keputusan asumsi yang dipilih, hasil pengujian aktual, serta cara menjalankan fitur. Klaim "selesai" perlu mencakup form, penyimpanan, riwayat, laporan, total, akses dan tes regresi saldo.
