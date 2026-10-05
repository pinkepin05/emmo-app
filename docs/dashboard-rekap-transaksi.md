# Rekap pengajuan dan hasil Finance per modul

Implementasi 29 September 2026, melanjutkan pekerjaan 28 September. Migrasi fitur sudah diterapkan pada MySQL lokal. Pembaruan 30 September menggabungkan Pengajuan dan Hasil Finance menjadi Daftar Transaksi.

## Alur pengguna

Dashboard Transaksi menampilkan empat modul: Operasional Sales (OPR), Perjalanan Dinas (PD), Event Dalam Kota (EDK), dan Event Luar Kota (ELK). Hanya transaksi dengan status `VALIDATED` yang masuk rekap. Periode awal adalah bulan berjalan, berdasarkan periode transaksi. Pilih **Semua Periode** untuk seluruh periode.

- **Pengajuan:** jumlah dokumen yang sudah dikonfirmasi dan total nominal input user.
- **Disetujui Finance:** jumlah keputusan persetujuan terkini dan nominal yang disetujui Finance.
- **Tidak Disetujui:** jumlah penolakan eksplisit dan nominal pengajuan yang ditolak. Nilai persetujuan penolakan selalu nol, termasuk bila request mencoba mengirim nominal lain.
- **Revisi/Pending:** nominal pengajuan yang belum diperiksa, diminta revisi, atau perlu dicek ulang karena sumber berubah, **ditambah selisih persetujuan Finance**. Pengajuan Rp1.000.000 dengan persetujuan Rp800.000 menyumbang Rp200.000 ke kelompok ini. Penolakan eksplisit tidak dihitung lagi di sini.
- **Selisih persetujuan:** nominal pengajuan dikurangi nominal disetujui untuk keputusan persetujuan terkini. Selisih ini sudah termasuk Revisi/Pending; rincian pada kartu bukan tambahan total. Selisih tidak otomatis mengubah keputusan menjadi revisi atau penolakan. Persetujuan nol tetap merupakan persetujuan; keputusan penolakan harus eksplisit.

Hubungan total: **Pengajuan = Disetujui + Ditolak + Revisi/Pending**. Selisih tetap bertanda, termasuk bila Finance menyetujui lebih tinggi dari nominal asal; nilai negatif mengurangi total Revisi/Pending. Selisih antaritem yang saling meniadakan tetap terhitung sebagai ketidaksesuaian dan tetap muncul di filter Revisi/Pending meskipun nominalnya nol. Satu dokumen persetujuan dengan selisih masuk jumlah Disetujui dan jumlah Revisi/Pending; jumlah transaksi antarkelompok tidak dijumlahkan. Nominalnya terbagi sehingga tidak terhitung ganda.

Status proses dibedakan dari kelompok nominal:

- **Belum Dicek** (`PENDING`): Finance belum melakukan pemeriksaan; seluruh nominal pengajuan masuk Revisi/Pending.
- **Perlu Revisi** (`REVISION`): Finance meminta perbaikan; usulan pemeriksaan belum final sehingga seluruh nominal pengajuan masuk Revisi/Pending.
- **Perlu Cek Ulang** (`RECHECK`): sumber berubah setelah pemeriksaan; seluruh nominal terbaru masuk Revisi/Pending sampai diperiksa lagi.
- **Ada Selisih** (`DIFFERENCE`): keputusan terakhir disetujui dengan perbedaan nominal per item; selisih bertanda masuk Revisi/Pending.
- **Revisi / Pending** (`FOLLOW_UP`): filter gabungan keempat kondisi di atas. Kartu Revisi/Pending membuka filter ini. `REVISION` sekarang khusus permintaan revisi, sedangkan cek ulang menggunakan `RECHECK`.

Setiap baris dan footer daftar gabungan menampilkan nominal Revisi/Pending. Angka mengikuti pemeriksaan terakhir; setelah persetujuan terbaru sesuai dengan seluruh item pengajuan, transaksi keluar dari filter Revisi/Pending. Perhitungan dilakukan saat membaca laporan, tanpa mengubah keputusan/riwayat tersimpan atau memerlukan migrasi baru.

Verifikasi penyesuaian Revisi/Pending: **49 tes lulus, 877 assertion** pada SQLite dalam memori, mencakup empat modul, pengajuan/persetujuan sebagian, penolakan, keputusan terbaru, perubahan sumber, akses pemilik, filter dan pagination, selisih negatif/desimal, serta selisih antaritem yang saling meniadakan. **32 pemeriksaan tampilan browser lulus** setelah penambahan filter dan kolom laporan. Hasil tes: `storage/app/private/revisi-pending-tests.txt`. Pemeriksaan ini tidak mengakses database bisnis dan bukan verifikasi ulang pada MySQL. Cadangan perubahan: `storage/app/private/revisi-pending-backup-20260929-140150/`.

Filter periode, modul, nomor pengajuan, dan status berlaku pada rekap serta daftar. Total selalu menghitung seluruh hasil filter, bukan hanya halaman aktif. Tautan pada kartu membuka daftar modul/status yang dipilih.

## Daftar Transaksi gabungan

Dashboard Transaksi memiliki dua tab: **Ringkasan** dan **Daftar Transaksi**. Pergantian tab dilakukan langsung tanpa memuat ulang dokumen atau berpindah halaman.

- **Ringkasan:** total nominal dan status per modul.
- **Daftar Transaksi:** satu baris per pengajuan, berisi nomor/modul/tanggal, user dan konfirmasi, nominal pengajuan, nominal disetujui Finance, Revisi/Pending, status Finance, serta tombol Rincian. Pemeriksaan terakhir dipakai agar pengecekan ulang tidak menggandakan pengajuan.
- Persetujuan final yang belum tersedia ditampilkan sebagai tanda `—`, disertai status Belum Dicek, Perlu Revisi, atau Perlu Cek Ulang. Keputusan final bernilai nol tetap ditampilkan Rp0.
- Footer menampilkan total Pengajuan, Disetujui, Tidak Disetujui, dan Revisi/Pending untuk seluruh hasil filter.

Menu sidebar **Rekap Transaksi → Daftar Transaksi** membuka daftar gabungan yang sama pada halaman laporan. URL utama: `/laporan-transaksi?tampilan=daftar`. Tautan lama dengan `tampilan=pengajuan` atau `tampilan=finance` tetap diterima dan memakai tampilan gabungan. Dashboard mempertahankan tab, filter, halaman, dan konteks kembali dari rincian; semua periode dan semua modul tidak berubah saat kembali. Tanpa JavaScript, tab tetap bekerja melalui navigasi dashboard biasa.

**Rincian Transaksi** memperlihatkan pengajuan asli serta bagian **Hasil Finance terakhir**: nominal persetujuan, Revisi/Pending, selisih keputusan final, nomor dokumen pemeriksaan, checker, tanggal/waktu simpan, dan catatan keputusan untuk user. Jika sumber berubah setelah diperiksa, hasil lama ditandai sebagai dokumen sebelumnya, dan tidak dipakai sebagai persetujuan final.

User biasa hanya mengakses transaksi miliknya. Permission `akses.approval-finance` atau role yang memenuhi `canSeeAllTransactions()` dapat membaca rekap lintas user. Tombol Periksa/Cek Ulang, rincian pemeriksaan internal, serta riwayat hanya tersedia bagi Finance. Catatan pemeriksaan per item tidak dimuat dalam hasil publik; catatan keputusan untuk user ditampilkan dengan escaping Blade.

**Laporan Pengecekan BS** tetap terpisah untuk Finance, dengan pilihan Cek Terakhir atau Semua Riwayat. Penggabungan daftar tidak mengubah keputusan, perhitungan nominal, saldo, atau riwayat pemeriksaan.

## Konfirmasi Event

Sesuai pilihan user, **Event lama menunggu konfirmasi**. Migrasi tidak menganggap simpan lama sebagai konfirmasi. Pada daftar Event, buka rincian lalu tekan **Konfirmasi Pengajuan**. Tautan rincian kini juga ada pada daftar Event Luar Kota.

Hanya pemilik Event dengan izin modul tersebut yang dapat mengonfirmasi. Token snapshot menolak konfirmasi atas rincian yang berubah sejak dibuka. Klik ulang atas Event yang sudah dikonfirmasi tidak membuat versi baru atau mengubah waktu konfirmasi.

Edit Event mengembalikannya ke `DRAFT`, membersihkan informasi konfirmasi, dan menaikkan versi sumber. Setelah dikonfirmasi lagi, hasil Finance lama ditandai perlu cek ulang meskipun total nominal tidak berubah. Setiap keputusan Finance berikutnya membuat dokumen pemeriksaan baru; riwayat lama tetap tersedia.

Konfirmasi Event serta keputusan Finance tidak menulis jurnal, mengubah saldo BS, atau mengganti perhitungan Stok Kas. Perhitungan kas Event existing tetap mengikuti transaksi tersimpan sebagaimana sebelum fitur ini. Pengajuan dashboard mengikuti konfirmasi user.

## Pengecekan Finance

Menu **Pengecekan Pemakaian BS** kini menerima empat modul. Event harus terkonfirmasi sebelum dapat diperiksa. Lookup OPR/PD tetap mengikuti kemampuan sebelumnya untuk meninjau transaksi tersimpan; dashboard/laporan pengajuan hanya memasukkan sumber terkonfirmasi.

Finance memilih **Disetujui Finance**, **Tidak Disetujui**, atau **Perlu Revisi**. Catatan keputusan wajib untuk penolakan dan revisi. Pada revisi, nominal pemeriksaan merupakan usulan, belum masuk total persetujuan final. Penolakan menormalkan nominal persetujuan menjadi nol di server. Keputusan lama sebelum migrasi dipertahankan sebagai persetujuan berdasarkan nilai hasil pemeriksaan yang telah dikonfirmasi saat itu.

Pemeriksaan terbaru dipilih menurut ID simpan tertinggi per pasangan jenis dan ID sumber, sebelum menerapkan filter. Keputusan lama tidak muncul kembali hanya karena yang terbaru tidak cocok dengan filter. Pemeriksaan OPR/PD yang detailnya berubah juga membutuhkan cek ulang, dengan pembandingan per kategori termasuk kategori ditambahkan/dihapus. Detail PD yang soft-deleted dikecualikan.

Laporan riwayat Finance lama (`/pengecekan-pemakaian-bs/laporan`) tetap tersedia dengan nilai snapshot pemeriksaan, termasuk usulan revisi. Barisnya menampilkan keputusan; nilai tersebut berbeda fungsi dari total persetujuan final pada laporan rekap baru.

## Struktur dan penerapan

Migrasi: `database/migrations/2026_09_28_100000_extend_finance_checks_to_transaction_modules.php`.

- Header pemeriksaan: `keputusan`, `catatan_keputusan`, dan `source_revision`.
- Detail pemeriksaan: `cost_category_id` menjadi nullable; Event memiliki satu item nominal tanpa relasi kategori biaya buatan. Payload kategori Event memakai 0/null, disimpan null; kategori tambahan ditolak.
- Kedua tabel Event: `status` default `DRAFT`, `validated_at`, `validated_by`, dan `submission_version`.
- Relasi polimorfik pemeriksaan diperluas dengan EDK dan ELK. Nomor dokumen tetap memakai sequence CEKBS yang sama.

Pada lingkungan lain, terapkan migrasi sebelum melayani kode baru:

```powershell
php artisan migrate --path=database/migrations/2026_09_28_100000_extend_finance_checks_to_transaction_modules.php --force
```

Migrasi ini memerlukan tabel pengecekan dari migrasi 25 September. Tidak perlu seeder permission baru. Jangan menjalankan `migrate:fresh` pada database bisnis. Cache route/view mengikuti prosedur deployment lingkungan masing-masing.

Berkas utama: `BsUsageDashboardService`, `BsUsageCheckService`, `BsUsageCheckController`, `TransactionRecapController`, view `transaction-recap/*`, partial `event-confirmation`, form/hasil Finance, serta integrasi controller/model/view Event dan route web.

## Verifikasi

- Tes terfokus: **47 lulus, 787 assertion**, mencakup dashboard, izin akses, pemeriksaan Finance lama, serta delapan skenario alur empat modul.
- Seluruh aplikasi: **167 lulus, 1 gagal, 2.565 assertion**. Kegagalan existing adalah `test_rejection_time_stays_at_the_decision_even_after_document_updates`: view Approval Finance menampilkan waktu tanpa detik/WIB, sementara tes mengharapkan format lengkap. Halaman tersebut tidak diubah oleh fitur ini.
- MySQL: **41 permintaan berhasil**, empat modul, kedua konfirmasi Event, tiga keputusan per modul, riwayat dan rincian, serta kedua laporan. Nilai kolom bisnis lama identik sebelum/sesudah migrasi. Isi 19 tabel, termasuk hasil uji dan sequence, identik sebelum/sesudah rollback smoke test. Auto-increment MySQL dapat meninggalkan celah ID; nomor dokumen uji di-rollback.
- Browser Chrome headless: kedua laporan diperiksa pada lebar **1366, 768, 390, 320 px**; empat modul terbaca dan halaman tidak meluber horizontal. Tabel dapat digeser di wadahnya. Bootstrap CDN yang sama di-inline dari salinan lokal untuk pratinjau. HTML/screenshot merupakan artefak uji privat yang memuat keputusan sementara, bukan dokumen bisnis tersimpan.

Tes alur baru: `tests/Concerns/TransactionRecapScenarios.php`, dijalankan dalam fixture integrasi `BsCashFlowTest`. Hasil MySQL: `storage/app/private/transaction-recap-verification/result.json`; hasil seluruh tes: `storage/app/private/transaction-modules-all-tests.xml`. Skrip: `scripts/verify-transaction-recap.php` (before/after) dan `scripts/preview-transaction-recap.mjs`.

Cadangan kode sebelum pekerjaan: `storage/app/private/dashboard-modules-backup-20260928/`. Bundle Qwen lama tetap merupakan snapshot sebelumnya.

## Pembaruan UI/UX 29 September 2026

Dashboard dan laporan sekarang memiliki navigasi **Ringkasan**, **Pengajuan**, dan **Hasil Finance**. Ringkasan menampilkan kartu per modul; laporan langsung menampilkan total dan daftar transaksi agar pengguna lebih cepat menemukan pengajuan. Filter menyediakan pencarian nomor referensi, periode, modul, serta pilihan status dengan penjelasan tindakan berikutnya. Tombol kembali dari rincian mempertahankan filter, mode laporan, dan halaman daftar, termasuk pilihan semua periode.

Pada ponsel, baris laporan dan lookup Finance berubah menjadi kartu berlabel. Form pemeriksaan menampilkan langkah kerja, tautan rincian/bukti pengajuan, nominal dan selisih yang langsung diperbarui, serta petunjuk keputusan. Catatan untuk user dibedakan dari catatan pemeriksaan internal. Tombol simpan memberikan umpan balik saat pengiriman berlangsung. Konfirmasi Event menjelaskan rincian yang perlu diperiksa sebelum pengajuan dikirim.

Perubahan ini berada pada view `transaction-recap/*`, partial `event-confirmation`, view lookup/form `bs-usage-checks`, `public/css/transaction-recap.css`, dan `public/js/bs-usage-checks.js`. Pembaruan tampilan tidak memerlukan migrasi tambahan.

Verifikasi setelah pembaruan UI:

- **47 tes lulus, 787 assertion** untuk rekap, dashboard, hak akses, dan pemeriksaan Finance; hasil di `storage/app/private/transaction-ux-tests.txt`.
- **32 pemeriksaan tata letak lulus**: 8 halaman pada lebar 1366, 768, 390, dan 320 px. Halaman tidak meluber horizontal dan daftar memakai kartu pada ponsel.
- Interaksi keputusan disetujui/ditolak/revisi, pemulihan nominal setelah berpindah keputusan, catatan wajib, masukan nominal tidak valid, umpan balik simpan, serta konteks tautan kembali diperiksa di Chrome headless.
- MySQL lokal tidak tersedia pada pemeriksaan browser akhir. HTML akhir dirender menggunakan data sintetis dan SQLite dalam memori; database bisnis tidak diakses oleh skrip demo. Pemeriksaan ini bukan pengujian ulang penyimpanan pada MySQL.

Skrip pratinjau: `scripts/render-transaction-ux-demo.php` dan `scripts/preview-transaction-ux.mjs`. Artefak privat: `storage/app/private/transaction-ux-preview/`. Skrip browser membutuhkan Chrome lokal serta salinan Bootstrap CSS dari verifikasi sebelumnya. Cadangan kode sebelum pembaruan tampilan: `storage/app/private/transaction-ux-backup-20260929/`.

## Verifikasi tab di dalam dashboard

Implementasi memakai `public/js/transaction-recap-tabs.js`, atribut tampilan pada panel/tabel, dan parameter `tampilan` yang divalidasi service. Skrip bekerja hanya pada panel dashboard; komponen UI tambahan yang sudah ada tetap dipakai. Perhitungan nominal dan keputusan Finance tidak berubah. Tidak diperlukan migrasi.

- **50 tes terfokus lulus**; hasil di `storage/app/private/dashboard-tabs-tests.txt`. Setelah perbaikan kembali ke semua modul, skenario konteks tab diuji kembali: **1 tes lulus, 97 assertion**.
- **12 tampilan tab lulus** pada lebar 1366, 768, 390, dan 320 px, dengan skrip tab dan UX yang sebenarnya. Browser memastikan klik tab tidak mengirim permintaan dokumen baru, filter dan tautan rincian mengikuti tab, Back/Forward bekerja, refresh/form filter kembali ke tab yang sama, serta kondisi kosong dan fallback tanpa JavaScript berfungsi.
- Skrip: `scripts/test-dashboard-tabs.mjs`, dijalankan setelah `scripts/render-transaction-ux-demo.php`. HTML berasal dari Blade menggunakan SQLite dalam memori, kemudian disajikan oleh server fixture lokal. Database bisnis tidak diakses. Hasil akhir: `storage/app/private/transaction-ux-preview/tabs-browser-1790675578369/result.json`.
- Cadangan sebelum perubahan: `storage/app/private/dashboard-tabs-backup-20260929-163038/`.


## Verifikasi penggabungan 30 September 2026

Perubahan utama: `transaction-recap/panel`, `table`, `show`, partial `finance-result`, menu sidebar dan tautan terkait, serta `public/js/transaction-recap-tabs.js`. `BsUsageDashboardService::sourceResult()` menggunakan klasifikasi dan pembatasan akses yang sama dengan daftar untuk menampilkan hasil publik pada rincian. Tidak diperlukan migrasi.

- **51 tes lulus, 1.021 assertion**, termasuk kompatibilitas kedua parameter tampilan lama, pagination/filter, akses user dan Finance, hasil terakhir, perubahan sumber, dan kerahasiaan catatan internal. Hasil: `storage/app/private/unified-recap-tests.txt`.
- **8 tampilan tab browser lulus** (2 tab × 4 lebar layar), termasuk klik tanpa reload, Back/Forward, refresh, form filter, kembali dari rincian, tautan lama, kondisi kosong, dan fallback tanpa JavaScript.
- **32 pemeriksaan tata letak halaman lulus**, mencakup rincian Finance dan form pemeriksaan pada desktop/tablet/ponsel. Nama file pratinjau pengajuan/finance dipertahankan sebagai artefak verifikasi; keduanya merender daftar gabungan.
- Hasil browser: `storage/app/private/transaction-ux-preview/tabs-browser-1790750700586/result.json` dan `storage/app/private/transaction-ux-preview/browser-1790750703597/result.json`. Fixture sintetis memakai SQLite dalam memori; pemeriksaan browser tidak mengakses database bisnis.
- Cadangan: `storage/app/private/unified-recap-backup-20260930-133712/`.
- Query `sourceResult()` juga berhasil diperiksa secara baca-saja pada database lokal untuk dua modul yang memiliki transaksi terkonfirmasi. Tidak ada data bisnis yang diubah.

Catatan verifikasi 29 September di atas merupakan riwayat pengembangan; susunan navigasi terkini mengikuti bagian Daftar Transaksi gabungan.
