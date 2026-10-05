# Fakta aplikasi yang sudah diperiksa

Fakta berikut berasal dari kode sumber yang disertakan dan pembacaan metadata database lokal pada saat paket dibuat. Ini bukan desain fitur baru.

## Teknologi dan struktur

- PHP minimal 8.2, Laravel 12. Versi terkunci di `composer.lock`: `laravel/framework v12.61.1`, `spatie/laravel-permission 6.25.0`, `maatwebsite/excel 3.1.70`.
- UI memakai Blade. Layout bersama: `resources/views/layouts/app-with-sidebar.blade.php`. Banyak halaman transaksi lama masih HTML lengkap sendiri dengan Bootstrap dari CDN.
- Layout memakai Bootstrap 5.3, Bootstrap Icons, font Plus Jakarta Sans, sidebar, dan aset `public/js/sidebar.js`.
- Pagination Bootstrap diaktifkan di `app/Providers/AppServiceProvider.php`.
- Route web ada di `routes/web.php`; alias middleware ada di `bootstrap/app.php`.
- Metadata database aktual menggunakan driver MySQL. Tes memakai SQLite `:memory:` di `phpunit.xml`.
- Tidak ada folder `.git` yang dapat dipakai sebagai identitas commit pada workspace saat penyusunan paket. Identitas snapshot ada pada hash berkas di `MANIFEST.json`.

## Pemetaan transaksi dan ID

| Konsep baru | Model existing | Tabel header | Primary key | Nomor dokumen sumber |
| --- | --- | --- | --- | --- |
| OPR | `TransaksiOperasionalHeader` | `transaksi_operasional_area_sales_header` | `id`, INT unsigned | `transaksi_id`, VARCHAR(32), prefix OAS |
| PD | `TransaksiPerjalananDinasHeader` | `transaksi_perjalanan_dinas_header` | `id`, INT unsigned | `transaksi_id`, VARCHAR(32), prefix PD |

Model header tidak mengubah `$primaryKey`, sehingga `findOrFail($id)` berarti kolom `id`, bukan string `transaksi_id`. Nomor OPR existing seperti `OAS26091701`; nomor PD seperti `PD26091701`. Label OPR pada fitur baru tidak mengubah format itu.

| Relasi detail | Tabel detail | Foreign/local key |
| --- | --- | --- |
| OPR details() | `transaksi_operasional_area_sales_detail` | detail.transaksi_id → header.transaksi_id (string) |
| PD details() | `transaksi_perjalanan_dinas_detail` | detail.transaksi_id → header.transaksi_id (string) |

Hal ini berbeda dari ID numerik yang disarankan sebagai identitas sumber pada tabel pengecekan baru. Simpan ID numerik sumber dan snapshot nomor referensi sebagai dua hal berbeda.

Rute `transaksi_operasional.show` menerima ID numerik; controller memanggil `findOrFail`. Rute `transaksi_perjalanan_dinas.show` menerima nomor string; controller memanggil `where('transaksi_id', $id)`. Jangan menyalin link PD menjadi OPR tanpa mengganti parameternya.

## Perbedaan skema yang penting

| Field/table | Keadaan aktual | Implikasi |
| --- | --- | --- |
| User | Tabel `user`, ID INT signed, nama `nama`, tanpa timestamps model | Jangan pakai FK otomatis ke `users` atau BIGINT unsigned |
| Cost category | Tabel `cost_category`, ID INT signed; kolom `nama`, `description`, `is_active` | Label gunakan `nama`; tidak ada kolom `code`/`name` aktual |
| OPR/PD header.id | INT unsigned | Source ID bukan BIGINT standar Laravel |
| OPR detail.nominal | INT signed | Baca sebagai uang; jangan mengubah tipe legacy hanya untuk fitur ini |
| PD detail.nominal | INT unsigned | Tes mungkin memakai decimal; itu bukan bukti tipe database operasional |
| OPR detail.deleted_at | Tidak ada | Jangan menambahkan whereNull(deleted_at) pada query OPR |
| PD detail.deleted_at | Ada; model memakai SoftDeletes | Query relasi Eloquent mengecualikan rincian terhapus |
| PD header.document_no | Tidak ada di database lokal | Meskipun model punya fillable lama, ambil nomor referensi dari transaksi_id |
| periode.periode | VARCHAR(7), unique; format YYYY-MM | Bukan ID numerik periode |
| document_number_sequences | PK komposit type + date | Nomor baru perlu memakai mekanisme locking existing |

Lihat skema lengkap untuk signedness, ukuran, nullable, indeks, dan FK. Beberapa potongan lama controller/master data masih memakai nama kolom yang tidak cocok dengan skema aktual; jangan menyalin asumsi tersebut ke fitur baru. Misalnya `CostCategoryController` menggunakan `code` sedangkan model dan database aktual memakai `nama`. Paket ini tidak memperbaiki bug modul lain.

## BS gabungan dan identitas pengguna

Dokumen terbaru tentang alur dana adalah `docs/saldo-bs-gabungan.md`. Isinya menggantikan sebagian aturan lama dalam `docs/implementasi-bs.md` dan audit sebelumnya.

- Pencairan menambah satu saldo BS per pemilik.
- OPR baru memakai saldo sendiri. PD dapat memakai saldo sendiri atau saldo pemohon grup.
- `user_id` adalah penginput/pemilik transaksi penggunaan; `bs_owner_id` pemilik dana. Mereka bisa berbeda pada PD.
- `UsesBsBalance::bsCashOwnerId()` membaca `bs_owner_id`, lalu fallback ke pengajuan lama lewat `bs_id`.
- Transaksi baru boleh tidak memiliki `bs_id`. Jangan membangun lookup hanya dengan join ke pengajuan BS.
- Hanya realisasi final `VALIDATED` yang memengaruhi pengeluaran BS; draft/pending tidak mencadangkan saldo.
- OPR mempunyai status DRAFT/VALIDATED. PD mempunyai DRAFT/PENDING_OWNER/REJECTED_OWNER/VALIDATED.
- `BsUsageApprovalController`, tabel `bs_usage_approval_logs`, dan view `bs-usage-approvals` adalah persetujuan pemilik saldo untuk penggunaan anggota. Ini berbeda dari pemeriksaan Finance yang diminta.
- Konfirmasi finansial dicatat di `bs_usage_logs`. Hasil pengecekan Finance bukan record baru pada tabel tersebut.

Kode dana yang perlu dipahami untuk tes regresi: `BonSementaraService`, `BsCashFlowService`, `StockCashFlowService`, `BsValidationService`, `TransactionFundingService`, dan `BsPeriodClosingService`. Pengecekan baru tidak perlu memanggil layanan pembukuan ini ketika menyimpan.

## Nomor dokumen dan waktu

`DocumentNumberService::TYPES` saat ini hanya memuat BS, CAIR, OAS, PD. `next()` wajib dipanggil di dalam transaksi database. Ia mengunci/upsert sequence type+date, memeriksa nomor lama, dan menjaga pertumbuhan suffix. Jangan memakai count()+1 atau max ID di luar transaksi untuk nomor pengecekan.

Format panjang dengan separator saat ini berlaku untuk BS/CAIR. Bila menambah tipe CEKBS dan menginginkan format CEKBS-YYYYMMDD-001, Qwen juga perlu memperbarui pemilihan format di service; menambahkan TYPES saja akan menghasilkan format pendek.

`AppTime::now()` mengikuti `config('app.display_timezone')`, default Asia/Jakarta. Timestamp penyimpanan memakai UTC. Jangan memakai timezone mesin pengguna sebagai aturan bisnis aplikasi. `PeriodService::ofDate()` menurunkan YYYY-MM dari tanggal; `normalize()` menerima YYYYMM/ YYYY-MM. `lockOpen()` merupakan aturan pembukuan, bukan otomatis aturan pengecekan Finance.

## Otorisasi, menu, dan middleware

- Approval Finance existing memakai middleware `permission:akses.approval-finance` dan pemeriksaan permission di controller.
- Permission Spatie memakai tabel `spatie_*`; tabel `roles` adalah master role terpisah. Observer menyinkronkan master role dengan Spatie.
- `User::canSeeAllTransactions()` memeriksa kode ADMIN, DIR, FINANCE. `canAccessPortal()` memeriksa ADMIN, ADMINISTRATOR. Keduanya bukan pengganti permission endpoint Finance.
- Finance dapat memiliki izin Approval Finance tanpa izin membuka modul transaksi OPR/PD. Form pengecekan perlu menyediakan rincian referensi bagi Finance melalui endpoint yang dilindungi izin fitur ini.
- `PermissionSeeder` mempunyai daftar kanonis dan menghapus permission di luar daftar itu. Default matrix hanya diterapkan jika role masih kosong; menambah daftar default saja tidak memberi izin baru pada role yang sudah dipakai.
- `BsPermissionSeeder` memakai nama permission lama `bs.*`. Ikuti pola kanonis `akses.*` bila perlu permission baru.
- Navigasi Finance ada di `resources/views/components/sidebar.blade.php`; dashboard transaksi memakai layout sidebar.
- `config/bs.php` menyembunyikan menu laporan BS lama (`report_menu_enabled=false`) dan menonaktifkan settlement. Jangan mengikat menu laporan pengecekan baru ke flag laporan lama sehingga menu baru tidak terlihat.
- `AtomicBsRequest` melakukan lock periode/user/dokumen serta aturan status DRAFT atau status persetujuan. Memasang `bs.atomic` pada modul verifikasi dapat memblokir pengecekan historis karena aturan yang tidak diminta. Transaksi database internal tetap diperlukan di penyimpanan pengecekan.
- `InjectNotificationBell` menyisipkan lonceng untuk respons HTML. Untuk tes terisolasi, lihat cara existing test menonaktifkan middleware notifikasi UI bila perlu.

## Laporan dan tes existing

`LaporanBsController` dan `resources/views/laporan-bs/index.blade.php` merupakan laporan pengajuan/pencairan, bukan laporan pengecekan per item. Gunakan sebagai referensi ekspor/tampilan, jangan menimpa fungsi laporan tersebut.

`LaporanTableExport` adalah helper Excel umum dan saat ini format nominalnya `#,##0`. Jika hasil pengecekan mendukung dua desimal, ekspor perlu menampilkan dua desimal dengan benar. Jangan mengekspor string user sebagai formula aktif.

`tests/Feature/BsCashFlowTest.php` menyiapkan tabel bisnis secara manual, kemudian menjalankan migrasi tambahan tertentu. Migrasi pembuat tabel dasar bisnis tidak tersedia lengkap. Menambah test dengan RefreshDatabase lalu menjalankan semua migrasi pada database kosong tidak otomatis dapat bekerja. Perlu fixture yang mengikuti pola existing atau helper terisolasi.

`DocumentNumberTest` membuat tabel berdasarkan semua entri `DocumentNumberService::TYPES`. Jika tipe pengecekan ditambah, perhatikan efeknya pada fixture dan jangan membuat tabel yang sama dua kali.

Angka kelulusan tes di dokumentasi lama adalah riwayat pekerjaan sebelumnya. Penyusunan paket konteks ini tidak menjalankan ulang seluruh tes aplikasi dan tidak membuktikan fitur baru sudah berjalan.
