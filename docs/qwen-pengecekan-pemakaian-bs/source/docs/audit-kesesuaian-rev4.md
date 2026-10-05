# Audit kesesuaian emmo-app terhadap Struktur Database EMMO_rev4

Tanggal pemeriksaan: 17 September 2026.

**Kesimpulan: belum sepenuhnya sesuai.** Semua 21 tabel utama yang dipetakan dari dokumen tersedia pada database aktif, termasuk tabel header/detail transaksi. Banyak alur BS sudah sesuai, tetapi ada bug yang terkonfirmasi, perbedaan struktur, dan beberapa keputusan bisnis yang belum dijelaskan oleh rev4. Kesamaan nama menu atau keberadaan tabel belum membuktikan kesesuaian seluruh aturan.

Acuan: `C:\Users\kevin\Downloads\Struktur Database EMMO_rev4.docx`. Dokumen diperlakukan sebagai spesifikasi pembanding. Pemeriksaan meliputi isi dokumen, kode dan form lokal, metadata MySQL aktif, migrasi yang tercatat, suite pengujian yang tersedia, serta pemeriksaan tambahan di SQLite dalam memori. Tidak dilakukan perubahan kode aplikasi, migrasi, atau data operasional.

## Temuan yang perlu diperbaiki

### 1. Cost Category: form dan controller tidak cocok dengan database — prioritas tinggi

Rev4 menyebut `Code`, `Description`, dan `Active`. Database aktif mempunyai `nama`, `description`, `is_active`; tidak ada kolom `code`. Daftar kategori menampilkan `nama` sebagai Code, tetapi form tambah/edit mengirim `code`, dan controller memvalidasi `unique:cost_category,code`.

Validasi tersebut diuji langsung sebagai query baca pada MySQL aktif dan menghasilkan **`Unknown column 'code' in 'where clause'`**. Selain itu, model hanya mengizinkan pengisian `nama`, bukan `code`, sehingga mengganti validasi saja belum cukup. Input, validasi, model, dan skema harus memakai pemetaan yang konsisten.

Bukti: [controller](../app/Http/Controllers/CostCategoryController.php), terutama baris 36–45 dan 75–84; [model](../app/Models/CostCategory.php), baris 15–19; [form](../resources/views/cost_categories/create.blade.php), baris 32–42.

### 2. Aturan lumpsum dan hotel dapat dilewati saat edit/finalisasi — prioritas tinggi

Rev4 mensyaratkan lumpsum untuk perjalanan >200 km dan hotel untuk durasi >8 jam. Pemeriksaan ada saat pembuatan transaksi, tetapi belum konsisten sepanjang alur:

- Saat edit, kategori dibaca dari `name` atau `description`, tanpa `nama`. Saat pembuatan, `nama` ikut diperiksa.
- Jika permintaan edit hanya mengubah header, detail biaya yang sudah tersimpan tidak diperiksa ulang terhadap jarak/durasi baru.
- Finalisasi memeriksa status, nominal, akses, dan saldo; tidak mengulang pemeriksaan kelayakan hotel/lumpsum.

Reproduksi terisolasi melalui route aplikasi: buat draft dengan biaya kategori yang dikenali sebagai lumpsum/hotel pada jarak 300 km dan durasi 10 jam, ubah hanya header menjadi 100 km dan 2 jam, lalu validasi. Hasil aktual: status **VALIDATED**, biaya tetap tercatat. Reproduksi kedua membuktikan kategori `nama=LUMPSUM` dengan deskripsi umum juga lolos edit menjadi 100 km walaupun detail dikirim ulang. Percobaan memakai PD tanpa BS yang memang diizinkan aplikasi; tidak menyentuh database operasional.

Perbaikannya perlu memeriksa seluruh detail aktif menggunakan identitas kategori yang konsisten setelah perubahan header dan sebelum finalisasi.

Bukti: [controller PD](../app/Http/Controllers/TransaksiPerjalananDinasController.php), baris 160–181 dan 327–402; [service finalisasi](../app/Services/BsValidationService.php), baris 17–42.

### 3. Hari kerja pengguna belum berupa relasi Weekday Group

Bagian User pada rev4 menyebut `Hari Kerja User (Weekday Group ID)`. Database dan controller saat ini memakai enum/string `5 HK` atau `6 HK`, tanpa foreign key ke `weekday_group`. Service plafon menerjemahkan kode tersebut menjadi ID saat dibutuhkan.

Fungsi dasar dapat berjalan, tetapi struktur belum mengikuti relasi yang ditulis pada bagian User. Diagram lain di dokumen juga menuliskan nilai 5 HK/6 HK; sebaiknya tetapkan bentuk yang menjadi acuan final sebelum migrasi.

Bukti: [UserController](../app/Http/Controllers/UserController.php), baris 51 dan 99; [PlafonService](../app/Services/PlafonService.php), method `resolveKelompokHariKerjaId`; metadata tabel `user` dan `weekday_group`.

### 4. Beberapa relasi belum ditegakkan oleh foreign key

Kolom dan relasi model tersedia, tetapi metadata database aktif belum mempunyai foreign key untuk:

| Kolom | Tujuan relasi |
|---|---|
| `user.homebase_id` | `wilayah.id` |
| `setting_plafon_perjalanan_dinas.provinsi_awal_id` | `wilayah.id` |
| `setting_plafon_perjalanan_dinas.provinsi_tujuan_id` | `wilayah.id` |
| `transaksi_perjalanan_dinas_header.tujuan_dinas_id` | `wilayah.id` |
| `perongkosan_event_luar_kota.lokasi_event_id` | `wilayah.id` |

Ini merupakan kekurangan penegakan integritas pada tingkat database, bukan bukti bahwa data saat ini sudah rusak. Validasi `exists` pada aplikasi membantu ketika menyimpan, tetapi tidak menggantikan constraint database.

Khusus OAS, rev4 secara eksplisit mewajibkan header berelasi dengan BS. Form/controller mewajibkan BS, tetapi kolom `bs_id` masih nullable dan foreign key memakai `ON DELETE SET NULL`; hubungan wajib belum dijamin sepenuhnya oleh database.

### 5. Catatan tambahan: periode Event Luar Kota tidak konsisten

Pembuatan Event Luar Kota mengambil periode OPEN terbaru melalui `getActivePeriode()`, sedangkan proses edit menurunkannya dari tanggal transaksi. Event Dalam Kota sudah memakai tanggal transaksi saat pembuatan.

Akibatnya, event bertanggal Agustus yang dibuat ketika periode terbaru September dapat tersimpan pada periode September. Ini perlu diselaraskan untuk konsistensi laporan. Rev4 mencantumkan tanggal dan periode tetapi tidak merinci kebijakan backdate, sehingga temuan ini dicatat sebagai inkonsistensi implementasi, bukan pelanggaran aturan backdate yang eksplisit.

Bukti: [controller Event Luar Kota](../app/Http/Controllers/PerongkosanEventLuarKotaController.php), baris 108–109 dan 229–232; [controller Event Dalam Kota](../app/Http/Controllers/PerongkosanEventDalamKotaController.php), baris 88–91.

## Bagian yang sudah tercakup

| Bagian rev4 | Hasil pemeriksaan |
|---|---|
| Company, Level, Position, Roles | Tabel, kode, nama/deskripsi, dan status aktif tersedia. Beberapa memakai `nama` untuk label Description. |
| Wilayah | Kode, nama, parent, territory Jawa/Luar Jawa, dan status aktif tersedia; parent merujuk ID wilayah. Kelengkapan seluruh kota/kabupaten belum diaudit. |
| Weekday Group dan Event Hour Group | Tabel serta pilihan 5 HK/6 HK dan 8/12/16 Jam tersedia. |
| User | Identitas, organisasi, homebase, tunjangan, atasan, username/password, dan aktif tersedia; ada perbedaan hari kerja di atas. |
| Tiga pengaturan plafon | Dimensi provinsi/level/tanggal dan level/hari kerja/hari event/jam event tersedia. |
| Periode | Tersedia; implementasi menambahkan OPEN/CLOSED dan jejak close/reopen. |
| Pengajuan BS | Nomor, tanggal, pemohon, atasan, nominal, deskripsi, periode, serta status tersedia. Tidak ada pembatasan satu pengajuan per pengguna per bulan. |
| Approval atasan | Persetujuan/penolakan dibatasi kepada atasan terkait; Finance hanya memproses status APPROVED_ATASAN. |
| Snapshot saldo BS | Saldo saat pengajuan disimpan di `nominal_sisa_bs_snapshot`; Finance menyalin angka itu, bukan menghitung ulang saldo realtime. Nilai pengajuan/snapshot tidak diambil dari input nominal Finance. |
| BS grup | Anggota disimpan melalui `pengajuan_bs_members`; pemilik dan anggota dapat memakai BS perjalanan sesuai akses. |
| Saldo BS | Pencairan Finance menambah kas; realisasi OAS/PD VALIDATED mengurangi kas pemilik BS. Rollover tersedia setelah close. |
| OAS | Header/detail, kategori, remark, nominal, dan BS tersedia; controller menerapkan satu transaksi per tanggal per pengguna. |
| PD | Header/detail, tujuan, kategori, remark, nominal, dan referensi BS tersedia; validasi kelayakan belum konsisten seperti temuan di atas. |
| Event dalam/luar kota | Hari libur, kelompok jam, pengguna, nominal, deskripsi, periode, dan tiga slot foto tersedia. Form luar kota menyimpan lokasi provinsi. |

Bukti utama untuk alur BS: [PengajuanBsController](../app/Http/Controllers/PengajuanBsController.php), [ApprovalAtasanController](../app/Http/Controllers/ApprovalAtasanController.php), [ApprovalFinanceController](../app/Http/Controllers/ApprovalFinanceController.php), [PengajuanBs](../app/Models/PengajuanBs.php), [BsCashFlowService](../app/Services/BsCashFlowService.php), dan [BsPeriodClosingService](../app/Services/BsPeriodClosingService.php).

## Perbedaan desain yang perlu dipetakan atau diperjelas

- **Nomor dokumen:** OAS, PD, dan event memakai `transaksi_id` sebagai nomor yang ditampilkan, dengan `id` sebagai kunci internal. Tidak ada kolom terpisah `document_no` pada tabel-tabel tersebut. Nomor referensi BS diturunkan melalui `bs_id` dan relasi. Secara fungsi dapat setara dengan rev4; tidak otomatis harus menambah kolom duplikat.
- **Approved Y/N:** digantikan status yang lebih rinci seperti PENDING_ATASAN, APPROVED_ATASAN, DISBURSED, dan REJECTED. Ini memperluas representasi alur yang diminta.
- **Plafon HOTEL/LUMPSUM:** rev4 menandai kedua kategori sebagai kategori berplafon, tetapi tabel plafon dalam dokumen sendiri tidak mencantumkan kategori biaya. Implementasi membandingkan total perjalanan dengan satu plafon rute/level/tanggal, dan pelampauan menghasilkan penanda/notifikasi. Perlu menetapkan apakah batas harus per kategori atau total serta apakah harus menolak atau cukup memperingatkan; dokumen belum cukup tegas untuk menyimpulkan bug dari hal ini saja.
- **Jarak pulang-pergi dan cakupan PD:** dokumen menyebut luar homebase >200 km PP. Input aplikasi berlabel jarak perjalanan tanpa penjelasan PP; jarak di bawah ambang tetap diizinkan untuk kategori selain lumpsum. Definisi jarak dan apakah ambang berlaku untuk seluruh dokumen PD perlu diperjelas.
- **PD tanpa BS:** tersedia pilihan reimbursement pribadi dan tidak dihitung sebagai pengeluaran kas BS. Rev4 menampilkan relasi BS pada PD tetapi tidak merinci pengecualian ini.
- **Deskripsi Finance:** kolom `approval_bs_finance.description` ada, tetapi proses pencairan tidak mengisi kolom tersebut dan form tidak menyediakan catatan Finance tersendiri. Deskripsi pengajuan tetap ditampilkan.
- **Saldo dan periode:** aplikasi menambahkan status DRAFT/VALIDATED, menunggu close sebelum rollover, pembatasan saldo per BS, serta jenis BS operasional/perjalanan. Ini aturan tambahan yang perlu masuk spesifikasi final. Riwayat settlement lama tetap dihitung; settlement baru sedang dinonaktifkan melalui konfigurasi.

## Bukti pengujian dan batas pemeriksaan

- Suite tersedia: `php vendor/bin/phpunit --do-not-cache-result` → **44 tes lulus, 318 assertion** pada SQLite dalam memori.
- Pemeriksaan tambahan sementara: **3 tes, 18 assertion**, mengonfirmasi dua jalur lolosnya aturan perjalanan dan ketidakcocokan pengisian model kategori. Keberhasilan tes pemeriksaan ini berarti perilaku bermasalah berhasil direproduksi, bukan berarti sudah diperbaiki.
- Pemeriksaan kategori pada MySQL aktif hanya menjalankan query baca dan menghasilkan error kolom `code` di atas.
- Metadata 22 tabel diperiksa: 21 tabel utama dari rev4 ditambah pivot anggota BS. Seluruh file migrasi yang tersedia tercatat pada database aktif, termasuk migrasi BS 16 September dan nomor dokumen 17 September 2026.
- Suite BS membangun skema sintetis; fixture kategori mempunyai kolom `name` dan `code` yang tidak ada pada database aktif. Karena itu, tes yang lulus belum membuktikan kesesuaian skema nyata.
- File migrasi di proyek belum memuat pembuatan seluruh tabel bisnis dasar; database aktif bergantung pada skema legacy yang sudah tersedia. Instalasi dari database kosong belum dapat dinyatakan lengkap berdasarkan migrasi yang ada.
- Tidak dilakukan uji browser menyeluruh, UAT, audit seluruh isi master wilayah, rekonsiliasi nominal transaksi operasional, atau uji konkurensi baru pada MySQL. Skrip pemeriksaan sementara telah dihapus setelah pemeriksaan.

Urutan tindak lanjut: perbaiki Cost Category dan validasi perjalanan terlebih dahulu; kemudian selaraskan relasi/hari kerja serta periode event; terakhir putuskan perbedaan desain dan perbarui spesifikasi sebelum menyatakan kepatuhan penuh terhadap rev4.
