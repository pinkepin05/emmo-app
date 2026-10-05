# Rekomendasi implementasi untuk Qwen

Dokumen ini memetakan pekerjaan. Nama tabel/file baru adalah usulan, belum ada implementasinya dalam `source/`. Kebutuhan pengguna tetap mengacu ke `02_SPESIFIKASI_FITUR.md`.

## Peta file baru

| Path usulan | Tanggung jawab |
| --- | --- |
| `database/migrations/YYYY_MM_DD_HHMMSS_create_bs_usage_checks.php` | Header/detail, FK yang benar, indeks latest/filter, unique nomor dokumen |
| `app/Models/BsUsageCheck.php` | Header, relasi pemilik/checker/details/source, query latest |
| `app/Models/BsUsageCheckDetail.php` | Detail, relasi header/cost category, decimal cast |
| `app/Services/BsUsageCheckService.php` | Resolve sumber OPR/PD, snapshot kategori, validasi sumber, simpan atomik |
| `app/Http/Controllers/BsUsageCheckController.php` | Lookup/form/store/show/report; otorisasi dan filter |
| `app/Http/Requests/StoreBsUsageCheckRequest.php` | Opsional; validasi form dapat juga mengikuti controller existing |
| `resources/views/bs-usage-checks/index.blade.php` | Lookup sumber tersimpan + status pernah dicek |
| `resources/views/bs-usage-checks/create.blade.php` | Form header/detail otomatis dan input Finance |
| `resources/views/bs-usage-checks/show.blade.php` | Detail immutable dan tautan cek ulang/riwayat |
| `resources/views/bs-usage-checks/report.blade.php` | Filter, tabel per item, pagination, total |
| `public/js/bs-usage-checks.js` | Opsional untuk lookup/total dinamis; backend tetap otoritatif |
| `tests/Feature/BsUsageCheckTest.php` | Skenario wajib pada dokumen 05 |
| `docs/pengecekan-pemakaian-bs.md` | Dokumentasi final perilaku, migrasi, dan tes |

Riwayat dapat memakai laporan mode semua dengan filter sumber, sehingga tidak wajib mempunyai halaman controller terpisah. Excel export opsional dapat memakai export class khusus agar format uang dan teks aman.

## File existing yang mungkin perlu diubah

| Path | Perubahan terarah |
| --- | --- |
| `routes/web.php` | Tambah route auth + permission untuk pengecekan, lookup, laporan |
| `app/Services/DocumentNumberService.php` | Tipe dan format nomor dokumen pengecekan |
| `app/Models/TransaksiOperasionalHeader.php` | Relasi hasil pengecekan/derived status |
| `app/Models/TransaksiPerjalananDinasHeader.php` | Relasi hasil pengecekan/derived status |
| `app/Http/Controllers/TransaksiOperasionalController.php` | withExists/eager load status pada daftar/detail bila badge ditambahkan |
| `app/Http/Controllers/TransaksiPerjalananDinasController.php` | withExists/eager load status pada daftar/detail bila badge ditambahkan |
| `resources/views/transaksi_operasional/index.blade.php` dan `show.blade.php` | Badge Sudah Dicek; link Finance menurut izin |
| `resources/views/transaksi_perjalanan_dinas/index.blade.php` dan `show.blade.php` | Badge Sudah Dicek; link Finance menurut izin |
| `resources/views/components/sidebar.blade.php` | Menu form dan laporan; kondisi section harus mencakup permission fitur |
| `app/Providers/AppServiceProvider.php` | Hanya bila memakai mapping morph OPR/PD; jangan mengubah semua morph type existing |
| `database/seeders/PermissionSeeder.php` | Hanya bila menambah permission tersendiri; daftar kanonis + default matrix |
| Migrasi/seeder permission khusus baru | Hanya bila permission baru; grant additive untuk role existing tanpa sync destruktif |
| `tests/Feature/DocumentNumberTest.php` | Format/urutan nomor baru dan rollback |
| Fixture tes existing | Tambah migrasi tabel pengecekan jika halaman existing mulai menjalankan query status baru |

Layanan saldo, approval pemilik uang, dan jumlah nominal sumber tidak perlu berubah.

## Desain data awal

Header `bs_usage_checks`:

| Kolom usulan | Jenis / keterangan |
| --- | --- |
| id | PK baru BIGINT unsigned (Transaksi ID sistem) |
| document_no | VARCHAR(64), unique |
| tanggal_pengecekan | DATE, index |
| jenis_transaksi | VARCHAR(3), domain OPR/PD |
| transaksi_pemakaian_id | INT unsigned; source.id sesuai jenis |
| no_referensi | VARCHAR(32); snapshot source.transaksi_id |
| user_id | INT signed; FK ke user.id |
| user_checker_id | INT signed; FK ke user.id; wajib pada penyimpanan baru |
| periode | VARCHAR(7), YYYY-MM, index |
| created_at / updated_at | Timestamp Laravel; created_at untuk audit |

Detail `bs_usage_check_details`:

| Kolom usulan | Jenis / keterangan |
| --- | --- |
| id | PK BIGINT unsigned |
| bs_usage_check_id | BIGINT unsigned; FK header |
| cost_category_id | INT signed; FK cost_category.id |
| nominal_asal | DECIMAL(15,2); snapshot hasil baca sumber |
| nominal_disetujui | DECIMAL(15,2); input Finance |
| catatan | Nullable text, dengan batas panjang validasi |

Jika menggunakan agregasi per kategori, beri unique `(bs_usage_check_id, cost_category_id)`. **Jangan** unique `(jenis_transaksi, transaksi_pemakaian_id)` pada header: itu akan melarang cek ulang. Index komposit `(jenis_transaksi, transaksi_pemakaian_id, id)` membantu latest/history; tambahkan filter tanggal/periode/user seperlunya.

Selisih dapat dihitung dari dua nominal, tidak perlu disimpan. Jumlah header juga bisa dihitung dari details. Label/nama user atau kategori historis bisa ikut disnapshot bila diperlukan untuk audit saat master berganti nama; kebutuhan ini belum diwajibkan pengguna.

### Relasi sumber lintas dua tabel

Pilihan A (paling dekat dengan pasangan jenis + ID yang diminta): relasi logis/polimorfik `(jenis_transaksi, transaksi_pemakaian_id)` dengan map hanya OPR/PD. Relasi Eloquent dapat memakai morphTo dan morphMap OPR/PD, atau resolver service yang eksplisit. ID selalu source.id, bukan nomor dokumen. **Tidak ada FK database tunggal yang dapat menunjuk dua tabel.** Integritas sumber wajib diperiksa dalam service ketika simpan; snapshot mempertahankan hasil jika sumber kemudian hilang.

Pilihan B (jika FK SQL fisik diwajibkan): dua kolom nullable `operasional_header_id` dan `perjalanan_dinas_header_id`, masing-masing FK ke tabelnya; tepat satu diisi dan cocok dengan jenis. Ini mengubah representasi fisik spesifikasi. Tetapkan constraint/validasi dan perilaku delete secara jelas. Hindari tiga kolom identitas redundan tanpa penjagaan konsistensi.

Untuk kebutuhan saat ini, rekomendasi A mempertahankan satu ID sumber. Jelaskan bahwa itu relasi logis. Jangan memasang dua FK pada satu kolom atau mengarahkan semua ID ke salah satu tabel.

FK ke user/category sebaiknya menjaga arsip dari cascade delete. Jangan membuat cascade penghapusan transaksi sumber menghapus riwayat pengecekan. Detail → header boleh memakai FK sesuai kebijakan retensi, tetapi UI/route fitur tidak menyediakan edit/delete hasil final.

## Alur lookup dan form

1. Finance masuk dari sidebar menuju daftar sumber OPR/PD tersimpan. Sediakan pencarian nomor/user dan pagination. Hindari memuat seluruh riwayat transaksi ke satu select tanpa batas.
2. Pemilihan satu baris membawa identitas sumber. Jenis adalah bagian dari pilihan sumber, bukan input bebas di form konfirmasi. ID numerik OPR dan PD dapat sama; pasangan jenis + ID wajib dipakai.
3. Backend resolve lewat whitelist model. Jangan menerima nama model/tabel dari request. Query detail aktif; PD otomatis mengecualikan soft-deleted detail.
4. Bentuk baris per kategori sesuai kebijakan: rekomendasi SUM semua nominal aktif. Gunakan `Money::minor()`/`Money::decimal()` untuk aritmetika PHP. Pastikan hasil agregasi tidak melampaui batas DECIMAL tujuan.
5. Tampilkan jenis, referensi, user, nominal asal secara read only. User checker dari sesi login. Tanggal default AppTime::now(); tampilkan periode hasil tanggal.
6. Nominal disetujui default sama dengan asal setiap kali membuat pengecekan baru. Setelah error validasi, pertahankan input nominal/catatan Finance dengan `old()`; data otomatis tetap dari sumber terpercaya.
7. Tampilkan total dan selisih bila membantu input. UI disable tombol selama submit untuk mengurangi klik tak sengaja, tetapi tidak menghilangkan kemampuan cek ulang.

## Alur penyimpanan

1. Terapkan auth dan permission pada seluruh endpoint, termasuk lookup JSON, show, report dan export opsional. Readonly HTML bukan kontrol keamanan.
2. Validasi tanggal, identitas sumber, rincian, format uang, panjang catatan. Reject sumber tidak ada/jenis tidak dikenal/array rincian kosong.
3. Gunakan transaksi database; lock header sumber lalu detail aktif untuk pembacaan konsisten dengan perubahan sumber. Pertimbangkan urutan lock existing agar tidak membuat deadlock baru.
4. Baca ulang jenis, nomor, user dan nominal asal dari sumber; jangan memakai nilai kiriman browser sebagai snapshot. Checker = request.user.id; periode = tanggal pengecekan YYYY-MM.
5. Validasi kesetaraan himpunan kategori: jangan menerima item asing, item ganda, atau item sumber yang sengaja dihilangkan.
6. Karena draft bisa berubah, bawa fingerprint/snapshot token server dari form dan bandingkan dengan sumber terbaru saat submit. Token harus terikat pada sumber dan rincian (ID detail, kategori, nominal). Jika berubah/hilang, minta reload dan tinjau ulang; jangan memasangkan persetujuan lama dengan nominal baru secara diam-diam. Signed token atau penyimpanan server lebih kuat daripada percaya hidden nominal.
7. Buat nomor lewat DocumentNumberService di transaksi yang sama, insert header baru, lalu insert seluruh detail snapshot. Setiap konfirmasi yang disengaja membuat dokumen baru; jangan `updateOrCreate` berdasarkan ID sumber.
8. Jika penyimpanan satu detail gagal, rollback header, detail, dan sequence. Lock per sumber membuat urutan cek ulang dapat ditentukan konsisten.
9. Redirect ke detail hasil. Halaman tersebut menampilkan snapshot tersimpan, tidak menghitung ulang nominal asal dari transaksi sumber.
10. Jangan memanggil konfirmasi transaksi, update source.status, recalculation BS/stock, settlement, reimbursement, atau insert audit penggunaan kas sebagai efek pengecekan.

## Latest/history dan laporan

- Latest: pilih MAX(id) per `(jenis_transaksi, transaksi_pemakaian_id)` dari seluruh header pengecekan. Jika memakai urutan timestamp, tambah ID sebagai tie-breaker dan gunakan definisi yang konsisten.
- Pilih latest sebelum menerapkan rentang tanggal/periode/status. Contoh: cek A tanggal 1 dan cek B tanggal 10; filter tanggal 1–5 pada mode latest tidak menghidupkan kembali A. Mode semua riwayat menampilkan A.
- Cek yang disimpan belakangan dengan tanggal bisnis lebih awal tetap terbaru menurut rekomendasi urutan simpan.
- Filter user mengacu ke pemilik transaksi, bukan checker/pemilik saldo. Filter referensi mengacu snapshot nomor sumber.
- Status "sesuai" adalah tidak ada detail dengan nominal_asal != nominal_disetujui. "Tidak sesuai" adalah exists detail berbeda. Jangan menilai hanya total selisih karena +100 dan -100 dapat net menjadi nol.
- Setelah filter dokumen, tampilkan semua detail dokumen tersebut. Total mengikuti baris yang ditampilkan oleh hasil terfilter, mencakup seluruh halaman.
- Rentang tanggal validasi Y-m-d dan tanggal akhir >= awal. Periode validasi/normalisasi YYYY-MM. Filter jenis, status, mode memakai whitelist.
- `withQueryString()` mempertahankan filter saat pagination. Hindari N+1 dengan eager loading user/checker/category.
- Gunakan satu pembangun query untuk layar dan ekspor bila ekspor dibuat. Format seluruh nominal dua desimal; escape catatan dalam Blade dan tulis teks Excel sebagai teks saat berpotensi formula.

## Derived status pada sumber

Tambahkan relasi checks() yang selalu membatasi jenis. Gunakan withExists/exists untuk status Sudah Dicek; jangan membuat kolom boolean atau mengganti status DRAFT/VALIDATED/PENDING_OWNER existing. Riwayat dan tombol Cek Ulang memakai pasangan jenis + ID yang sama. Akses melihat badge tidak otomatis memberi akses membaca catatan Finance; link ke detail tetap menurut permission.

## Urutan pengerjaan

1. Pastikan pilihan desain/asumsi tercatat, baca kode/skema aktual.
2. Buat migrasi, model, service snapshot, dan tes penyimpanan/riwayat terlebih dahulu.
3. Integrasikan penomoran dan permission, lalu route/controller/form.
4. Buat laporan, query latest/history/filter/total, derived status dan navigasi.
5. Jalankan tes terisolasi dan uji migrasi pada database MySQL pengujian yang cocok skema aktual. SQLite saja tidak menguji signedness FK atau locking MySQL.
6. Serahkan file/patch lengkap, perintah deployment terarah, hasil tes aktual, dan panduan penggunaan. Jangan mengubah database operasional sebagai bagian dari pembuatan contoh.
