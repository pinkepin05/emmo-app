# Kode existing: dokumentasi-existing, bagian 1

Snapshot referensi. Bukan implementasi fitur baru. Semua file di bawah disertakan utuh.

## FILE: docs/audit-kesesuaian-rev4.md

SHA-256: `50dac366d30283e0577f1e5bb49610f9241ed76d93e1920e407424207a320c22`

~~~~markdown
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

~~~~

## FILE: docs/evaluasi-stok-kas.md

SHA-256: `ac62ce33b04e5fa89bb448d00c4ca517b1005e1ee872ffe12bf6fa1e69a10c65`

~~~~markdown
**Evaluasi dan rancangan perbaikan stok kas EMMO**

Catatan status: dokumen ini merekam evaluasi **sebelum implementasi**. Setelah persetujuan implementasi, kode dan pengujian lokal telah dikerjakan. Status aktual, perilaku yang diterapkan, dan langkah aktivasi tercatat pada [implementasi-bs.md](implementasi-bs.md). Pernyataan "belum diubah" dan keputusan terbuka di bawah adalah konteks evaluasi awal, bukan status pekerjaan terbaru.

Tanggal evaluasi: 16 September 2026. Cakupan yang dipilih pengguna: evaluasi dan rancangan dahulu. Saldo yang dimaksud adalah saldo masing-masing user pemilik BS. Perlakuan anggota grup belum diputuskan; aturan grup di bawah adalah rekomendasi, bukan perubahan yang sudah diterapkan.

Pengguna membatasi cakupan pada lima modul berikut. Batas ini menggantikan rancangan sebelumnya yang mencakup modul lain; tahap pekerjaan tetap evaluasi dan rancangan, belum implementasi.

| Modul dalam cakupan | Fokus rancangan perbaikan |
|---|---|
| Pengajuan BS | Pemilik, jenis BS, periode pengajuan, nomor dokumen, dan status awal; pengajuan belum menambah kas |
| Approval atasan | Hak atasan terkait, transisi persetujuan/penolakan, audit, dan pencegahan pemrosesan ganda; persetujuan belum menambah kas |
| Approval Finance | Pencairan aktual, nominal dan periode pencairan, satu kali pencatatan uang masuk, serta pencegahan pencairan ganda |
| Operasional Area Sales | Draft, finalisasi/validasi, hubungan ke BS, saldo yang tersedia, dan satu kali pencatatan uang keluar |
| Perjalanan Dinas | Draft dan finalisasi yang konsisten dengan Operasional Area Sales, hubungan ke BS, serta kepemilikan pemakaian oleh anggota grup |

Stok kas, pemeriksaan saldo BS, periode, rollover, tampilan rincian, dan pengujian termasuk komponen pendukung hanya sejauh diperlukan untuk lima alur tersebut. Modul event dalam/luar kota, settlement, pengembalian, reimbursement, pemindahan dana antar-BS, dan perombakan laporan lain tidak masuk pekerjaan tahap ini. Perjalanan dinas tanpa BS tidak otomatis dijadikan pengeluaran kas BS.

Service, tabel periode, dan snapshot saat ini juga digunakan modul lain. Rancangan implementasi harus membatasi perubahan perilaku ke alur yang dipilih dan menguji kompatibilitas pemakai bersama. Pemisahan ringkasan BS tidak boleh menimpa ringkasan gabungan lama dengan arti yang berbeda atau menghilangkan penyesuaian historis dari modul di luar cakupan. Jika diperlukan ringkasan khusus BS, gunakan penanda cakupan atau penyimpanan terpisah yang jelas. Rancangan ini tidak menjanjikan perbaikan bug pada modul yang dikecualikan.

Kode aplikasi dan database operasional belum diubah. Evaluasi berdasarkan penelusuran controller, service, model, route, view, migration, serta simulasi menggunakan kode service/controller aktual dengan tabel minimal di SQLite dalam memori. Ini bukan audit nominal database operasional atau pengujian lengkap antarmuka. Constraint, trigger, dan struktur database legacy yang tidak tercakup migration repository belum diverifikasi.

**1. Kesimpulan dan bukti**

Fondasi fitur tersedia, tetapi angka saldo belum dapat dianggap konsisten dengan aturan pengguna. Prioritas utama dalam cakupan ini adalah memperbaiki pemilihan transaksi final, periode pencairan/realisasi, konsistensi saldo BS, dan integritas rollover. Pengubahan format periode saja tidak menyelesaikan kesalahan nominal.

| Temuan | Bukti dan dampak | Sumber |
|---|---|---|
| Draft dihitung sebagai uang keluar | Simulasi draft Rp600.000 menghasilkan total keluar Rp600.000, seharusnya nol. Draft dapat masuk ketika pemicu lain menghitung ulang saldo. | `app/Services/CashFlowService.php:62`; `app/Http/Controllers/CashFlowController.php:102` |
| Validasi mengurangi draft yang sedang diperiksa | Dana Rp1.000.000 dengan satu draft Rp600.000 ditolak saat validasi karena sisa dianggap Rp400.000. | `app/Services/BonSementaraService.php:75`; `app/Http/Controllers/TransaksiOperasionalController.php:278` |
| Periode pencairan mengikuti pengajuan | `tanggal_cair` memakai waktu sekarang, tetapi `periode` dan event memakai periode BS. Pencairan September atas BS Agustus masuk ke Agustus. | `app/Http/Controllers/ApprovalFinanceController.php:70` |
| Periode perjalanan dinas belum konsisten | Saat membuat transaksi digunakan periode aktif, sedangkan saat mengubah transaksi periode diturunkan dari tanggal. Dokumen bertanggal sama dapat masuk bulan berbeda berdasarkan jalur penyimpanan. | `app/Http/Controllers/TransaksiPerjalananDinasController.php:210` dan `:368` |
| Periode tertutup masih dapat berubah | Beberapa jalur pencairan dan perubahan/hapus perjalanan dinas tidak memeriksa periode. Service penghitungan ulang juga dapat menulis snapshot periode tertutup. Simulasi pemicu pencairan terlambat mengubah akhir Agustus menjadi Rp1.200.000, sementara awal September tetap Rp1.000.000. | `ApprovalFinanceController`; `TransaksiPerjalananDinasController`; `CashFlowService` |
| Penutupan bulan tidak harus berurutan | Pemanggilan controller close pada September lalu Agustus di simulasi membuat akhir September Rp1.000.000 tetapi awal Oktober tetap nol; September sudah berstatus CLOSED ketika angkanya ditulis ulang. | `app/Http/Controllers/PeriodeController.php:72` |
| Perhitungan rollover berbeda antarlayanan | Stok kas menunggu CLOSED; perhitungan sisa BS mengakumulasi bulan sebelumnya tanpa syarat itu. | `app/Services/CashFlowService.php:40`; `app/Services/BonSementaraService.php:51` dan `:75` |
| Aritmetika bulan bermasalah pada tanggal 31 | Dengan waktu uji 31 Januari, pencarian bulan sebelum Februari menggunakan kode saat ini menghasilkan Februari, seharusnya Januari. Hari saat ini ikut terbawa saat parsing tahun-bulan. | `app/Services/CashFlowService.php:42`; `app/Http/Controllers/PeriodeController.php:96` |
| Batas tiap BS belum ditegakkan | Simulasi BS A Rp100.000 dan BS B Rp900.000 milik user yang sama mengizinkan realisasi Rp500.000 pada BS A. Ini adalah perilaku saldo gabungan yang perlu diputuskan, bukan otomatis dianggap aturan bisnis yang benar. | `app/Services/BonSementaraService.php:98` |
| Ringkasan lama mencampur cakupan | Stok kas saat ini memasukkan transaksi event di luar lima modul yang dipilih. Perjalanan dinas tanpa BS tidak masuk; simulasi Rp200.000 menghasilkan total keluar nol. Definisi ringkasan BS perlu eksplisit tanpa mengubah diam-diam arti snapshot gabungan yang sudah ada. | `app/Services/CashFlowService.php:62` dan `:99` |
| Finalisasi belum seragam | Operasional memiliki DRAFT dan VALIDATED; perjalanan dinas langsung dicatat setelah simpan, tanpa tahap finalisasi bisnis yang sama. Validasi format input berbeda dari persetujuan transaksi final. | Controller dan view Operasional Area Sales dan Perjalanan Dinas |
| Laporan periode dapat berbeda dari stok kas | Laporan menjumlah detail dengan query langsung tanpa filter status final dan tanpa filter soft-delete detail perjalanan dinas. Sumber nominal masuk juga berbeda dari sumber pencairan stok kas. | `app/Http/Controllers/PeriodeController.php:34` dan `:50`; `app/Models/TransaksiPerjalananDinasDetail.php` |
| Risiko permintaan bersamaan | Status dan saldo diperiksa sebelum penguncian baris; tidak ditemukan `lockForUpdate` pada jalur keuangan yang ditinjau. Nomor dokumen memakai hitungan harian atau angka acak. Approval/reject bersamaan juga perlu transisi status atomik. Duplikasi aktual belum diuji dengan permintaan paralel pada MySQL. | Controller pengajuan, approval atasan/Finance, validasi, penutupan periode |
| Kegagalan pembaruan setelah commit | Beberapa transaksi commit lebih dahulu lalu menjalankan listener saldo. Jika listener gagal, dokumen sudah tersimpan, tetapi snapshot bisa tertinggal. Halaman cash flow hanya membuat snapshot yang belum ada. | `app/Http/Controllers/TransaksiOperasionalController.php:306`; `app/Listeners/UpdateCashFlowSnapshot.php`; `app/Http/Controllers/CashFlowController.php:34` |

Listener `CashFlowChanged -> UpdateCashFlowSnapshot@handle` terdaftar, dibuktikan melalui `php artisan event:list`. File `EventServiceProvider.php` yang tidak berbentuk kelas valid tidak membuktikan listener mati; aplikasi menemukan listener tersebut secara otomatis.

**2. Definisi saldo dan grup yang direkomendasikan**

Saldo dimiliki pemilik BS. Anggota grup adalah pihak yang diizinkan memakai BS, bukan penerima saldo tambahan dari pencairan yang sama. Simpan pemilik saldo, BS sumber dana, pembuat transaksi, dan pihak yang memfinalisasi sebagai identitas terpisah. Untuk kode saat ini, keanggotaan grup BS hanya ditambahkan pada pengajuan perjalanan dinas; perluasan ke jenis lain bukan bagian otomatis dari perbaikan.

| Aktivitas | Saldo pemilik A | Saldo anggota B | Catatan |
|---|---:|---:|---|
| Finance mencairkan BS A Rp1.000.000 | +Rp1.000.000 | Tidak berubah | Satu pencairan, satu pemilik saldo |
| B menyimpan draft Rp200.000 memakai BS A | Tidak berubah | Tidak berubah | Draft belum dibukukan |
| Transaksi B Rp200.000 difinalisasi memakai BS A | -Rp200.000 | Tidak berubah | Penginput tetap B, pemilik saldo A |
| A memfinalisasi pemakaian Rp300.000 pada BS yang sama | -Rp300.000 | Tidak berubah | Pengeluaran A dan B menggunakan sumber BS yang sama |

Pemilik perlu dapat melihat rincian semua pemakaian BS miliknya, termasuk oleh anggota. Izin anggota tetap dibatasi ke BS yang diikutinya dan tindakan yang diberikan; keanggotaan tidak otomatis memberikan hak validasi, hak Finance, atau akses ke seluruh transaksi pemilik.

Rekomendasi batas pemakaian: periksa sisa dana pada BS yang dipilih dan ketersediaan saldo pengguna yang telah sah dibukukan. Pemakaian dana BS lain tidak boleh terjadi diam-diam. Fitur pemindahan dana antar-BS tidak dibangun dalam cakupan ini. Batas per-BS tetap merupakan rekomendasi bisnis yang belum dikonfirmasi pengguna.

**3. Aturan masuk, keluar, dan finalisasi**

| Kejadian | Pengaruh pada stok kas BS |
|---|---|
| Pengajuan BS dibuat atau disetujui atasan | Tidak ada |
| Finance menyetujui administrasi tetapi uang belum dibayar | Tidak ada; bila bisnis memisahkannya, gunakan status sebelum DISBURSED |
| Finance mengonfirmasi pencairan yang benar-benar terjadi | Uang masuk sesuai nominal aktual, satu kali |
| Draft dibuat, diubah, atau dihapus | Tidak ada |
| Transaksi memakai BS difinalisasi dan lolos aturan | Uang keluar sesuai nominal final, satu kali |
| Transaksi final salah | Koreksi melalui pembalikan/koreksi tercatat, bukan penghapusan jejak transaksi |
| Perjalanan dinas tanpa BS | Tidak otomatis masuk uang keluar kas BS; alur non-BS yang sudah tersedia tidak diubah menjadi fitur klaim baru |

Gunakan alur finalisasi konsisten. Jika tidak ada tahap pemeriksa terpisah, tombol `Validasi & Submit` dapat menjadi satu tindakan atomik yang mengubah DRAFT menjadi VALIDATED dan membukukan mutasi. Jika harus ada pengirim dan pemeriksa berbeda, tambahkan SUBMITTED dan baru bukukan setelah VALIDATED. Pemilihan tahap pemeriksa merupakan keputusan bisnis, bukan alasan menambah approval secara otomatis.

Rumus pokok dalam cakupan ini adalah `saldo akhir = saldo awal final + pencairan BS aktual - realisasi BS final Operasional Area Sales - realisasi BS final Perjalanan Dinas`. Pengajuan dan approval atasan tidak masuk penjumlahan. Riwayat atau saldo lama yang sudah mencakup mutasi di luar rumus ini harus direkonsiliasi sebelum digunakan sebagai saldo awal; jangan sekadar menghapus komponennya dari query lama.

Approval atasan hanya dapat diproses oleh atasan yang berhak dan hanya dari status PENDING_ATASAN. Approval/reject berulang atau bersamaan harus menghasilkan satu transisi yang sah. Finance hanya mencairkan dari status APPROVED_ATASAN dan mencatat uang masuk setelah pencairan aktual dikonfirmasi. Penolakan pada kedua tahap tidak mengubah kas. Status BS yang sudah tertutup tetap diperiksa saat memilih dan memfinalisasi realisasi, tanpa mengubah mekanisme settlement di tahap ini.

**4. Periode, rollover, dan tutup buku**

Gunakan representasi periode enam digit `YYYYMM`, misalnya `202608`, pada alur BS yang dirancang. Karena tabel periode dan komponen lama dipakai modul di luar cakupan, rekomendasi tahap ini adalah konversi terpusat pada batas input/tampilan BS yang tetap kompatibel dengan penyimpanan `YYYY-MM` bersama. Migrasi format seluruh aplikasi bukan bagian tahap ini. Jika penyimpanan enam digit juga diperlukan, gunakan rancangan penyimpanan khusus yang kompatibel; jangan mengganti string secara global. Parser harus memvalidasi bulan 01-12, menetapkan hari pertama, dan menggunakan zona waktu operasional yang sama. Fungsi bulan sebelumnya/berikutnya harus melewati Desember-Januari dan Februari tanpa bergantung pada tanggal saat kode dijalankan.

Pisahkan tanggal pengajuan, tanggal transaksi, tanggal pencairan, tanggal finalisasi, dan periode pembukuan. Pencairan mengikuti tanggal pembayaran aktual. Realisasi memakai tanggal transaksi yang diperbolehkan pada periode OPEN. Dokumen yang terlambat setelah tutup buku perlu jalur koreksi pada periode terbuka dengan tanggal sumber tetap tersimpan; jangan memindahkan tanggal dokumen atau menulis kembali bulan tertutup secara diam-diam.

Selama periode sebelumnya belum CLOSED, saldo awal periode baru berstatus `menunggu tutup buku`, bukan saldo nol yang sudah final. Ringkasan periode baru boleh menampilkan mutasi yang sah pada bulan tersebut, tetapi saldo akhirnya harus diberi keterangan sementara. Sesuai syarat pengguna, dana dari periode lama belum boleh dianggap saldo awal yang telah tersedia. Rekomendasi: realisasi bulan baru hanya menggunakan dana yang sudah sah tersedia pada periode tersebut; penggunaan sisa bulan lama menunggu penutupan. Jika operasional menghendaki penggunaan lintas bulan sebelum close, itu perlu keputusan eksplisit dan pemisahan saldo operasional dari saldo pembukuan final.

Urutan close yang direkomendasikan:

1. Pastikan periode sebelumnya CLOSED, kecuali periode awal sistem yang ditetapkan saat migrasi.
2. Kunci periode agar pencatatan baru dan proses close tidak berjalan saling mendahului.
3. Periksa masalah yang belum selesai: draft tertinggal, selisih rekonsiliasi, atau pembayaran yang belum dikonfirmasi. Laporkan dan selesaikan; jangan mengubah status/tanggal otomatis.
4. Cocokkan jumlah mutasi final dengan ringkasan setiap pemilik.
5. Bekukan saldo akhir dan simpan pelaku serta waktu penutupan.
6. Tetapkan saldo awal bulan berikutnya dari saldo akhir tersebut tepat satu kali.
7. Selesaikan perubahan status, ringkasan, rollover, dan jejak audit dalam satu transaksi database.

Bulan tanpa aktivitas tetap harus meneruskan saldo. Cakupan pengguna meliputi pemilik dengan saldo bawaan, mutasi, atau ringkasan sebelumnya; pengguna nonaktif yang masih memiliki saldo tidak boleh dihilangkan. Saldo awal sistem harus ditetapkan secara eksplisit, baik nol maupun saldo hasil migrasi yang telah direkonsiliasi.

Reopen perlu memeriksa pencairan, realisasi BS, mutasi koreksi, saldo turunan, dan status CLOSED bulan setelahnya. Karena periode dipakai bersama, pemeriksaan dependensi modul lain yang sudah ada tidak boleh dihapus demi pembatasan cakupan. Rekomendasi default: koreksi melalui periode OPEN. Pengembangan ulang mekanisme reopen untuk seluruh aplikasi tidak termasuk tahap ini; pertahankan batas konservatif bila dampak ke periode atau modul lain belum dapat dipastikan.

**5. Rancangan sumber perhitungan dan penyimpanan**

Target rancangan: layar dan pemeriksaan saldo untuk lima alur yang dipilih membaca satu kumpulan mutasi BS final yang konsisten. Ringkasan periode BS menjadi hasil agregasi, bukan sumber angka yang terpisah dari rincian. Pemakaian `cash_flow_snapshots` bersama harus mempertahankan makna data lama; keputusan memperluas tabel dengan cakupan atau memakai ringkasan BS terpisah ditetapkan setelah pemeriksaan konsumennya.

| Komponen | Tanggung jawab |
|---|---|
| Layanan periode | Normalisasi YYYYMM dengan kompatibilitas format bersama, rentang tanggal, pemeriksaan OPEN, close dan rollover BS |
| Layanan pencatatan kas | Memeriksa izin, pemilik, BS, saldo, status, dan periode; mencatat satu mutasi sah secara atomik |
| Mutasi kas | Pemilik saldo, BS, jenis masuk/keluar, nominal presisi, tanggal sumber, periode pembukuan, dokumen asal, pelaku, waktu finalisasi, referensi pembalikan |
| Ringkasan periode | Saldo awal beserta statusnya, masuk, keluar, akhir, versi/waktu pembaruan, informasi close |
| Layanan rekonsiliasi | Membandingkan dokumen final, mutasi dan ringkasan; menyediakan laporan selisih tanpa langsung menimpa data |

Rekomendasi jangka panjang adalah tabel mutasi BS dengan referensi unik ke sumber pembukuannya, misalnya jenis sumber + ID sumber + jenis mutasi/versi. Catatan final tidak dihapus; koreksi membuat catatan pembalik yang terkait. Untuk implementasi bertahap, service perhitungan BS terpusat dapat menjadi perbaikan awal sebelum perpindahan penuh ke mutasi BS. Penambahan buku kas umum untuk modul lain tidak termasuk cakupan. Setelah perpindahan, jangan mempertahankan dua rumus aktif sebagai sumber saldo BS yang sama.

Nominal disimpan dan dihitung dengan representasi presisi: integer satuan uang terkecil atau decimal dengan aritmetika desimal. Hindari mengubah nominal uang menjadi float dalam rantai perhitungan. Pilih kebijakan pecahan rupiah secara konsisten dengan data lama dan kebutuhan pengguna.

Selaraskan konsumen yang relevan: ringkasan/rincian stok kas BS, daftar pengajuan, pilihan BS, validasi realisasi, serta rincian dan ekspor Operasional Area Sales/Perjalanan Dinas. Pada laporan periode bersama, batasi perubahan pada bagian BS dan kedua jenis realisasi itu serta pastikan bagian modul lain tetap kompatibel. Settlement dan perombakan laporan lintas modul tidak masuk backlog tahap ini. Baris yang dihapus secara logis atau belum final harus diperlakukan sama pada semua keluaran dalam cakupan.

**6. Ketahanan terhadap kegagalan dan permintaan ganda**

Pemeriksaan status, saldo, pencatatan dokumen/mutasi, pembaruan ringkasan, dan audit inti harus berada dalam transaksi database yang sama. Kunci data dengan urutan konsisten, misalnya periode, pemilik saldo, BS, lalu dokumen. Jalur penulisan dalam cakupan harus mengikuti protokol penguncian yang sama supaya close tidak berlomba dengan pencairan atau finalisasi transaksi BS. Pengujian tahap ini membuktikan jaminan pada alur BS tersebut; jangan mengklaim seluruh penulis dari modul lain telah diperbaiki.

Gunakan kunci unik pembukuan dan identitas permintaan agar klik ulang, timeout, atau pengiriman event berulang tidak menggandakan nominal. Periksa ulang status setelah memperoleh kunci. Apabila dua transaksi memakai sisa dana yang sama, hanya transaksi yang masih memenuhi saldo setelah penguncian boleh berhasil.

Untuk rancangan ini, pencatatan kas inti berjalan sinkron bersama transaksi bisnis; notifikasi dapat dikirim setelah commit. Keberhasilan transaksi tidak boleh bergantung pada notifikasi. Jika nantinya agregasi dipindahkan ke proses latar belakang, gunakan antrean perubahan yang tersimpan dengan transaksi asal, proses yang tahan pengulangan, dan indikator ringkasan belum diperbarui; ini bukan kebutuhan awal.

Nomor dokumen perlu constraint unik dan strategi pembentukan yang aman untuk permintaan bersamaan. Aturan satu pencairan per BS mengikuti perilaku saat ini. Bila pencairan bertahap kelak diizinkan, setiap pembayaran perlu identitas unik sendiri dan batas total pembayaran yang diperiksa secara atomik.

**7. Urutan implementasi yang direkomendasikan**

| Tahap | Hasil yang harus tersedia sebelum lanjut |
|---|---|
| 1. Tetapkan aturan dan batas | Lima modul, kepemilikan saldo, aturan grup, batas per-BS, finalisasi, tanggal pembukuan, serta penggunaan dana sebelum rollover terdokumentasi |
| 2. Buat pengujian regresi | Kasus kesalahan yang ditemukan dapat diulang otomatis pada database pengujian terpisah |
| 3. Perbaiki inti perhitungan BS | Draft dikecualikan; pencairan dan realisasi memakai tanggal/periode konsisten; ringkasan dan rincian BS memakai aturan yang sama |
| 4. Satukan alur pengajuan sampai realisasi | Approval/reject atomik; pencairan satu kali; finalisasi Operasional Area Sales/Perjalanan Dinas konsisten; perlindungan pengulangan dan penguncian tersedia |
| 5. Perbaiki dukungan periode BS | Urutan bulan, bulan kosong, pemilik nonaktif bersaldo, pembekuan saldo, dan rollover teruji dengan kompatibilitas periode bersama |
| 6. Sesuaikan data dan format dalam cakupan | Konversi input/tampilan periode, riwayat BS, dan ringkasan kandidat dibandingkan dengan data lama tanpa mengubah modul lain |
| 7. Uji alur terpilih | UI lima modul, permission pemilik/anggota/atasan/Finance, ekspor terkait, kegagalan transaksi, permintaan bersamaan, dan kompatibilitas komponen bersama lolos |

Pekerjaan tidak memerlukan penggantian seluruh aplikasi. Modul terkait bisa dipertahankan sambil memusatkan aturan dan mengganti sumber pembacaan saldo secara bertahap.

**8. Rencana pengujian penerimaan**

| Skenario | Hasil yang diharapkan |
|---|---|
| Pengajuan dibuat, disetujui atasan, atau ditolak | Tidak ada perubahan kas |
| Atasan tidak berhak memproses pengajuan | Ditolak tanpa perubahan status atau saldo |
| Approve dan reject terhadap BS yang sama dikirim bersamaan | Tepat satu transisi yang sah; audit sesuai hasil |
| Finance mencairkan pengajuan yang belum disetujui atasan | Ditolak tanpa uang masuk |
| Draft dibuat, diubah, dihapus | Saldo dan mutasi final tidak berubah |
| Dana Rp1.000.000, finalisasi Rp600.000 | Berhasil satu kali; sisa Rp400.000 |
| Finalisasi atau pencairan dikirim ulang | Tidak ada mutasi ganda |
| Dua transaksi Rp700.000 bersamaan, saldo Rp1.000.000 | Paling banyak satu berhasil jika saldo negatif tidak diizinkan |
| BS diajukan Agustus, dibayar September | Masuk September; riwayat pengajuan tetap Agustus |
| Realisasi diposting ke periode tertutup | Ditolak pada backend; bukan hanya tombol yang dinonaktifkan |
| Operasional Area Sales/Perjalanan Dinas bertanggal berbeda dari bulan input | Dokumen, mutasi, pemicu, rincian, dan ringkasan BS memakai aturan periode yang sama |
| Anggota B memakai BS A | Kas A berkurang; kas B tidak ikut berkurang; identitas B tercatat |
| User tidak berhak memilih BS | Ditolak tanpa perubahan saldo |
| BS A tidak cukup, BS B cukup | Mengikuti keputusan batas per-BS; rekomendasi menolak pemakaian silang diam-diam |
| Periode sebelumnya OPEN | Saldo awal belum final dan belum menerima rollover |
| Close berurutan | Saldo akhir bulan N sama dengan saldo awal bulan N+1 |
| Close bulan N+1 sebelum N | Ditolak, kecuali aturan periode awal sistem yang eksplisit |
| Bulan tanpa mutasi/pemilik nonaktif bersaldo | Saldo tetap terbawa |
| Februari, tahun kabisat, tanggal 31, Desember-Januari | Bulan sebelum/sesudah tetap benar |
| Perjalanan dinas tanpa BS | Tidak otomatis dibukukan sebagai uang keluar kas BS |
| Kegagalan di tengah pembukuan | Dokumen, mutasi, ringkasan, dan audit inti tidak tersimpan sebagian |
| Koreksi dan detail yang dihapus secara logis | Rincian, ringkasan, laporan periode, dan ekspor tetap cocok |
| Penghitungan ulang dijalankan berulang | Hasil sama; tidak menggandakan mutasi atau menimpa periode final tanpa prosedur rekonsiliasi |
| Service/tabel periode bersama tetap dipakai modul lain | Format dan perilaku di luar cakupan tidak rusak akibat perubahan BS; data lama tidak hilang |

Uji cepat dengan SQLite berguna untuk reproduksi fungsi. Perilaku konkurensi, penguncian, constraint, dan query khusus database harus diuji juga pada database MySQL pengujian yang terpisah dan sesuai lingkungan aplikasi. Pengujian yang tersedia di repository saat ini masih berupa contoh dasar, belum membuktikan skenario stok kas di atas.

**9. Perlakuan data lama**

Jangan langsung menjalankan penghitungan ulang menyeluruh dengan rumus lama. Inventarisasi format periode, status final, pencairan lintas bulan, pemilik BS, realisasi kedua modul, data yang dihapus, dan bulan yang sudah CLOSED. Tandai saldo atau penyesuaian historis dari modul di luar cakupan sebagai dependensi rekonsiliasi; jangan menghapusnya atau menganggap saldo khusus BS otomatis sama dengan saldo gabungan lama. Status default DRAFT pada migration lama bukan bukti bahwa seluruh transaksi historis memang belum pernah difinalisasi; klasifikasi harus memakai bukti proses yang tersedia.

Buat salinan data untuk rekonsiliasi dan laporan selisih per user/periode: angka tersimpan, angka usulan, sumber perubahan, serta pengaruh pada rollover. Data yang statusnya tidak dapat dipastikan harus masuk daftar pemeriksaan, bukan otomatis disahkan atau dihapus dari saldo.

Gunakan migration tambahan yang menjaga data. Migration pembuatan snapshot saat ini berisi `dropIfExists`; jangan memakai ulang migration tersebut sebagai cara memperbaiki tabel berisi saldo. Jangan memakai migrasi yang menghapus semua tabel pada database operasional.

Setelah koreksi disepakati, bangun riwayat dan saldo kandidat dari periode awal ke akhir, termasuk bulan kosong. Simpan laporan sebelum/sesudah dan jejak penerapan. Periode historis yang sudah final perlu prosedur koreksi atau pembukaan kembali yang eksplisit. Perintah rekalkulasi baru sebaiknya menyediakan mode laporan/dry-run, rentang periode, dan pilihan pengguna; akun nonaktif yang bersaldo tetap tercakup.

**10. Keputusan yang masih terbuka**

| Keputusan | Rekomendasi awal | Status |
|---|---|---|
| Tahap pekerjaan | Evaluasi dan rancangan dahulu | Sudah dikonfirmasi pengguna |
| Cakupan modul | Pengajuan BS, approval atasan, approval Finance, Operasional Area Sales, Perjalanan Dinas; dukungan saldo/periode seperlunya | Sudah ditetapkan pengguna |
| Pemilik stok kas | Masing-masing user pemilik BS | Sudah dikonfirmasi pengguna |
| Anggota grup | Pemakaian dibebankan ke pemilik BS, anggota tercatat sebagai pelaku | Belum dikonfirmasi |
| Batas belanja | Cek per-BS dan saldo pengguna; tanpa pemakaian silang otomatis | Belum dikonfirmasi |
| Perjalanan dinas tanpa BS | Jangan otomatis masukkan ke kas BS; jangan membangun alur klaim/reimbursement baru | Batas rancangan tahap ini |
| Format periode | YYYYMM pada alur BS dengan adapter yang kompatibel dengan format bersama | Rekomendasi implementasi untuk menjaga batas cakupan |
| Pemakaian sisa bulan lama sebelum close | Tunggu rollover sesuai syarat awal pengguna; tampilkan saldo awal belum final | Implikasi operasional perlu disepakati |
| Tahap pemeriksa terpisah | Gunakan satu tombol validasi dan submit bila tidak ada pemeriksa terpisah | Belum dikonfirmasi |
| Bulan awal dan saldo awal migrasi | Tetapkan eksplisit dari data yang direkonsiliasi | Memerlukan pemeriksaan data saat implementasi |

Dokumen ini merupakan rancangan untuk ditinjau. Belum ada perubahan alur aplikasi, status transaksi, nominal, atau periode operasional yang diterapkan.

~~~~

## FILE: docs/implementasi-bs.md

SHA-256: `d8f9d25a0192f944321a7081f07c2c5a5e856a03462ddf39d778fe72ae74c569`

~~~~markdown
# Implementasi alur BS dan stok kas

**Pembaruan 24 September 2026:** pemakaian sekarang memakai satu saldo BS gabungan per pemilik, tanpa referensi dan batas per pengajuan. Anggota grup dapat memakai saldo pemohon khusus untuk Perjalanan Dinas. Aturan terbaru ada di [Saldo BS gabungan](saldo-bs-gabungan.md) dan menggantikan pembatasan per dokumen/jenis pada catatan implementasi terdahulu di bawah ini.

Tanggal: 16 September 2026. Implementasi lokal mencakup pengajuan BS, approval atasan, pencairan Finance, Operasional Area Sales (OAS), dan Perjalanan Dinas (PD), berikut perhitungan kas dan tutup periode pendukungnya. Migrasi belum dijalankan pada database operasional. Pengujian dilakukan dengan database SQLite dalam memori, tanpa mengubah nominal operasional.

## Perilaku yang diterapkan

| Alur | Perilaku |
|---|---|
| Pengajuan | Status awal PENDING_ATASAN; pengajuan tidak menambah kas; periode mengikuti bulan pengajuan. Nomor BS/pencairan memakai tanggal dan urutan harian; lihat [perbaikan nomor dokumen](nomor-dokumen.md). |
| Approval atasan | Hanya atasan terkait; approval/reject memeriksa status terbaru dalam transaksi database. Approval belum menambah kas. Notifikasi diteruskan ke pengguna dengan izin Finance. |
| Finance | Hanya pemegang izin Finance; pencairan hanya dari APPROVED_ATASAN dan paling banyak sekali melalui alur aplikasi. Kas masuk mengikuti tanggal pencairan aktual, meskipun pengajuan berasal dari bulan sebelumnya. |
| OAS dan PD | Form baru menyediakan Simpan Draft dan Simpan & Konfirmasi. Draft belum mengurangi kas dan dapat dilengkapi sebelum Konfirmasi. Konfirmasi memeriksa akses, jenis BS, BS sudah cair, periode OPEN, total positif, batas BS, dan saldo pemilik pada periode transaksi. |
| Finalisasi | Status VALIDATED, audit realisasi, dan ringkasan kas disimpan dalam satu transaksi database. Kegagalan pembukuan membatalkan perubahan. Permintaan ulang ditolak. Dokumen final tidak dapat diedit/dihapus melalui route modul. |
| Kepemilikan | Kas mengikuti pemilik BS. Pemakaian anggota PD mengurangi kas pemilik, dengan identitas penginput tetap tercatat. Pemilik dapat membaca transaksi anggota; hak mengubah tetap mengikuti aturan penginput/admin yang sudah ada. |
| Batas kas | Batas belanja adalah nilai terendah antara sisa BS terpilih dan kas pemilik yang tersedia pada periode tersebut. Sisa BS bulan sebelumnya tersedia setelah saldo awal final. Tidak ada perpindahan otomatis antardokumen BS. |
| Periode | Stok kas menampilkan YYYYMM, misalnya 202609; adapter juga menerima YYYY-MM. Penyimpanan periode bersama tetap YYYY-MM agar modul lain tetap kompatibel. Bulan diturunkan dari tanggal transaksi. |
| Tutup buku | Hanya bulan yang telah berakhir; periode sebelumnya harus CLOSED dan urut tanpa bulan terlewat. Draft harus diselesaikan terlebih dahulu. Penutupan membekukan ringkasan BS dan meneruskan saldo ke bulan berikutnya, termasuk bulan kosong. |
| Reopen | Ditolak bila periode sesudahnya sudah CLOSED, memiliki pencairan, penyelesaian BS, atau transaksi menurut pemeriksaan periode bersama. Reopen menghapus ketersediaan saldo awal bulan berikutnya hingga close ulang. |
| Tampilan dan ekspor | Stok kas memperlihatkan saldo awal yang masih menunggu close. Rincian kas dan ekspor OAS/PD mengambil transaksi VALIDATED; detail PD yang dihapus secara logis tidak dihitung. |

Rumus kas BS saat close: `saldo akhir BS = saldo awal BS + pencairan - realisasi final BS + penyesuaian BS yang sudah tercatat`.

Mulai 24 September 2026, istilah tindakan Validate/Validasi & Submit pada OAS/PD menjadi **Konfirmasi**, dengan label status final **Dikonfirmasi**. Simpan & Konfirmasi menggunakan layanan finalisasi yang sama dalam transaksi database penyimpanan dokumen. Jika gagal, dokumen, rincian, audit, dan pembukuan dibatalkan; isian dikembalikan agar dapat diperbaiki atau disimpan sebagai draft. Status internal `VALIDATED`, kolom audit, serta route konfirmasi yang sudah ada tetap digunakan; perubahan ini tidak memerlukan migration. Permintaan simpan tanpa `save_action` tetap menghasilkan draft.

Mulai 18 September 2026, halaman Stok Kas dan bannernya memakai ringkasan gabungan BS dan event. Pengguna mengonfirmasi bahwa pemasukan memakai pencairan yang sudah ada; biaya event hanya menambah Total Keluar. Rumus awal gabungan: `saldo akhir Stok Kas = saldo awal gabungan + pencairan - realisasi BS VALIDATED - event dalam kota - event luar kota + penyesuaian BS`. Setiap event mengikuti pemilik `user_id` dan bulan `tanggal`, termasuk data lama yang kolom `periode`-nya tidak sesuai tanggal. Event tidak memerlukan referensi BS.

Mulai 24 September 2026, fitur uang pribadi dan reimbursement dihapus. Stok Kas kembali menghitung BS dan event. Transaksi OAS/PD baru wajib memiliki referensi BS; catatan lama dipertahankan sebagai arsip. Lihat [penghapusan fitur](reimbursement-pribadi.md) untuk cakupan dan perlakuan data historis.

Ringkasan gabungan disimpan di `stock_cash_flow_snapshots`, melalui migration `2026_09_18_110000_create_stock_cash_flow_snapshots_table.php`. Riwayat gabungan dibentuk dari snapshot BS yang sudah final dan nominal event; snapshot BS historis tetap dipertahankan. Saldo awal gabungan bulan berikutnya berasal dari saldo akhir gabungan bulan sebelumnya, sehingga biaya event dibawa tepat satu kali. Sisa Saldo BS dan validasi realisasi tetap memakai `BsCashFlowService`, tanpa pengurangan event. Rincian Total Keluar ditampilkan terpisah untuk BS, event dalam kota, dan event luar kota.

Mutasi event memakai `event.atomic`: periode tanggal lama dan baru dikunci serta wajib OPEN untuk tambah, ubah, pindah bulan, maupun hapus. Snapshot gabungan diperbarui dalam transaksi database yang sama; kegagalan membatalkan perubahan event. Foto lama baru dihapus setelah commit. Periode Event Luar Kota saat simpan sekarang mengikuti tanggal event.

Mulai 18 September 2026, Saldo Akhir hanya dihitung, disimpan, dan ditampilkan setelah periode berstatus CLOSED. Selama OPEN, kolom `saldo_akhir` bernilai `NULL`; pembukaan halaman, pencairan, dan validasi transaksi hanya memperbarui saldo awal serta mutasi. Saldo tersedia untuk validasi transaksi dan Sisa Saldo BS tetap dihitung terpisah. Close membentuk Saldo Akhir dan saldo awal bulan berikutnya dalam satu transaksi database. Reopen mengosongkan kembali Saldo Akhir dan mencabut saldo awal bulan berikutnya sampai close ulang. Nilai nol pada periode CLOSED tetap ditampilkan sebagai Rp 0.

Migration `2026_09_18_100000_defer_closing_balances_until_period_close.php` membuat `saldo_akhir` nullable pada kedua tabel ringkasan (`bs_cash_flow_snapshots` dan `cash_flow_snapshots`) serta mengosongkan nilai lama untuk periode yang belum CLOSED. Saldo Akhir historis CLOSED dan komponen mutasi dipertahankan. Terapkan migration ini bersama perubahan kode; pengujian close/reopen dan migration memakai SQLite dalam memori.

Penyesuaian membaca reimburse dikurangi pengembalian dari BS yang sudah SETTLED. Mulai 17 September 2026, pencatatan settlement baru dipause melalui `bs.settlement_enabled = false` di `config/bs.php`: endpoint menolak permintaan, termasuk dari Finance yang berizin, dan laporan menyembunyikan tombol settlement serta instruksi setor/reimburse pada BS terbuka. Riwayat settlement, ekspor, serta penyesuaian yang sudah tercatat tetap tersedia dan dihitung agar saldo historis tidak berubah. Tidak ada migration atau penghapusan data untuk pause ini.

Selama pause, transaksi baru mengikuti `saldo awal + pencairan aktual - realisasi OAS/PD VALIDATED`; sisa dana diteruskan ketika periode ditutup. Penutupan periode tidak mewajibkan BS menjadi SETTLED. Penyesuaian historis tetap ditambahkan bila ada. Sebelum mengaktifkan kembali settlement, validasi ulang workflow dan lakukan UAT; flag ini tidak memperbaiki alur settlement lama.

Pause settlement BS mengikuti aturan yang sama. Transaksi baru OAS/PD wajib menggunakan BS. Transaksi historis tanpa BS dan event tidak mengurangi kas BS. Perlakuan anggota dan batas per-BS tetap berlaku.

## Implementasi teknis

### Export Excel Stok Kas

Pada halaman detail Cash Flow, setiap baris Uang Masuk, Uang Keluar, dan Penyesuaian BS dapat diklik melalui tautan native (juga mendukung Tab/Enter). Pencairan dan penyesuaian membuka detail pengajuan BS; pengeluaran membuka detail OAS, PD, Event Dalam Kota, atau Event Luar Kota. Pengeluaran anggota BS tetap membuka dokumen anggota yang dapat dibaca pemilik BS. Hak akses halaman tujuan mengikuti izin modul dan kepemilikan yang sudah ada. Event Luar Kota memiliki halaman detail baca dengan data event, nominal, plafon, dan foto bukti.

Halaman Stok Kas menyediakan **Export Ringkasan Excel** untuk seluruh periode yang tampil. Tombol **Excel** pada kartu periode dan **Export Excel** pada halaman detail mengunduh satu periode dengan empat sheet: Ringkasan Stok Kas, Uang Masuk, Uang Keluar, dan Penyesuaian BS. File memakai format `.xlsx`, header dengan filter, dan nominal numerik dengan dua angka desimal agar dapat dijumlahkan di Excel.

Export mengikuti data pengguna yang login dan izin `akses.cash-flow`, termasuk realisasi anggota yang menggunakan BS pengguna tersebut. Ringkasan dan rincian memakai `StockCashFlowService` yang sama dengan halaman. Saldo akhir periode OPEN dikosongkan; saldo akhir CLOSED yang nol tetap berupa angka nol. Status saldo awal yang menunggu penutupan periode sebelumnya ikut dicantumkan. Penyesuaian historis mempertahankan tanda positif untuk reimburse dan negatif untuk pengembalian. Ekspor hanya menampilkan komponen BS dan event.

### Layanan dan penyimpanan

- `BsCashFlowService` adalah sumber perhitungan BS, disimpan pada tabel baru `bs_cash_flow_snapshots`. Tabel ringkasan gabungan lama tetap dipertahankan.
- Middleware `bs.atomic` membungkus perubahan lima modul dalam transaksi, dengan penguncian periode, pemilik, BS, dan dokumen. Respons kegagalan controller membatalkan transaksi luar. Pengujian konkurensi MySQL tetap diperlukan untuk memastikan perilaku penguncian pada lingkungan tujuan.
- `BsValidationService` menyatukan finalisasi OAS/PD; `BsPeriodClosingService` menyatukan close/reopen. Perhitungan bulan memakai awal bulan sehingga tanggal 31 tidak melompati bulan.
- Nilai kas dihitung dalam satuan sen integer lalu disimpan sebagai DECIMAL. Notifikasi dijalankan setelah commit; kegagalan notifikasi dicatat tanpa menggagalkan pembukuan yang telah berhasil.
- Tidak ditambahkan jurnal mutasi terpisah. Dokumen pencairan dan realisasi final adalah sumber ringkasan; audit finalisasi tersimpan pada `bs_activity_logs`.

## Pengujian dan batas verifikasi

Hasil verifikasi lokal: **27 test lulus, 150 assertion**, melalui `php artisan test --compact`. Suite BS memakai database terisolasi, skema legacy minimal, migration baru, dan route/controller aplikasi. Skenario mencakup alur pengajuan hingga realisasi, izin Finance/atasan, anggota, larangan pemakaian silang BS, kegagalan audit/snapshot, pengiriman ulang, proteksi transaksi final, close/reopen, bulan kosong, tanggal 31/tahun baru, migrasi PD lama, serta render halaman/form.

Belum dilakukan: uji permintaan paralel nyata pada MySQL, rekonsiliasi data nominal operasional, UAT oleh pengguna, dan pengujian browser HP secara langsung. Pengujian render HTML bukan bukti kompatibilitas penuh Safari/Chrome pada smartphone. Tampilan kas memakai kolom responsif dan pembungkusan teks, tetapi pengujian perangkat tetap diperlukan.

## Aktivasi pada lingkungan tujuan

1. Buat backup database dan salinan staging. Periksa saldo awal sistem, kelengkapan urutan bulan, pencairan lintas bulan, draft OAS lama, dan realisasi PD lama. Cocokkan kandidat saldo khusus BS dengan dokumen sumber; ringkasan lama mencakup sumber lain sehingga tidak selalu sama.
2. Terapkan kode dan migration tambahan secara terkoordinasi. Jangan melayani kode baru dengan skema lama. Migration khusus yang disiapkan:

   ```sh
   php artisan migrate --path=database/migrations/2026_09_16_100000_add_bs_cash_flow_and_travel_validation.php
   ```

   Migration membuat ringkasan BS terpisah, menambah status/audit validasi PD, dan menambah kolom audit validasi OAS. Tidak menghapus tabel atau nominal lama. PD yang sudah tersimpan dipertahankan sebagai VALIDATED sesuai perilaku lama yang langsung membukukan saat simpan; PD baru dari aplikasi berstatus DRAFT. `validated_at` dan `validated_by` historis dibiarkan kosong karena tidak tersedia bukti aktor/waktu validasi terpisah. Jika ada PD historis yang belum benar-benar final, selesaikan klasifikasinya sebelum aktivasi.

3. Jalankan pengujian konkurensi dan UAT di staging dengan data representatif. Verifikasi dua finalisasi bersamaan tidak melampaui saldo, approve versus reject bersamaan, pencairan berulang, serta transaksi versus close bersamaan.
4. Cocokkan per pemilik/periode: saldo awal, total masuk, total keluar, penyesuaian, saldo akhir, dan detail sumber. Ringkasan BS yang belum ada dibentuk saat perhitungan/halaman dibuka; ringkasan BS CLOSED yang telah terbentuk tidak ditimpa oleh penghitungan ulang biasa. Riwayat bulan tidak lengkap perlu rekonsiliasi sebelum close.
5. Uji pengajuan, approval, pencairan, tambah/edit draft, Validasi & Submit, detail kas, dan tutup periode melalui desktop dan HP. Setelah hasil diterima, aktivasi di operasional dengan backup serta prosedur pemulihan.

Jangan menjalankan `migrate:fresh`. Jangan menjalankan rollback migration setelah menerima transaksi PD baru tanpa rencana pemulihan: penghapusan kolom status akan menghilangkan pembeda draft/final.

Snapshot cadangan kode sebelum implementasi tersedia di `storage/app/bs-implementation-backup/`. Modul event dan workflow settlement/refund/reimburse tidak dibangun ulang pada pekerjaan ini. Ringkasan gabungan lama masih memakai aturan sumber lamanya, selain perbaikan perhitungan bulan; perubahan kas BS tidak berarti semua laporan lama telah diaudit ulang.

~~~~

## FILE: docs/nomor-dokumen.md

SHA-256: `1c89b5ae857d2e054a51598a62dc5637474e308b1c71d36464032a40bdf6cbcd`

~~~~markdown
# Perbaikan nomor dokumen — 17 September 2026

Generator ULID dan string acak pada alur BS diganti dengan tanggal dan nomor urut harian:

| Modul | Contoh |
|---|---|
| Pengajuan BS | `BS-20260917-001` |
| Pencairan Finance | `CAIR-20260917-001` |
| Operasional Area Sales | `OAS26091701` |
| Perjalanan Dinas | `PD26091701` |

BS/pencairan mengikuti tanggal pengajuan/pencairan. OAS/PD mengikuti tanggal transaksi yang dipilih. Urutan terpisah per jenis dan hari, melanjutkan nomor numerik lama tertinggi. Nomor yang sudah ada tetap diperhitungkan walaupun tanggal transaksinya pernah diedit. Nomor yang sudah diterbitkan tidak digunakan ulang setelah dokumen dihapus. Kegagalan penyimpanan membatalkan reservasi nomor dalam transaksi database yang sama.

`DocumentNumberService` mengunci counter harian dengan upsert dan `FOR UPDATE`. Kolom ID header/rincian OAS dan PD diperlebar menjadi 32 karakter agar urutan 100 dan seterusnya tetap tersimpan lengkap. Foreign key serta aturan cascade dipertahankan. Rollback migrasi mempertahankan lebar kolom untuk menghindari pemotongan nomor yang sudah diterbitkan.

## Penerapan

Setelah backup database, jalankan migrasi khusus:

```sh
php artisan migrate --path=database/migrations/2026_09_17_100000_restore_readable_document_numbers.php
php artisan bs:repair-document-numbers
php artisan bs:repair-document-numbers --apply
```

Perintah kedua hanya pratinjau dan membatalkan semua perubahan. `--apply` mengoreksi BS/pencairan berformat ULID dan OAS berformat acak, termasuk referensi rincian yang dihapus secara logis. Nomor numerik lama dipertahankan. Cadangan nomor dan data asal disimpan di `storage/app/private/document-number-backups/`; riwayat BS mendapat catatan nomor lama dan baru. Nominal, tanggal, status, dan snapshot kas tidak diubah. Pengulangan perintah tidak mengubah dokumen yang sudah diperbaiki.

`scripts/apply-document-number-fix.php --apply` adalah aktivasi khusus instalasi MySQL lokal Windows: backup SQL lengkap, migrasi, koreksi, lalu perbandingan seluruh kolom data terkait selain nomor. Skrip tersebut memakai executable mysqldump lokal `C:/xampp/mysql/bin/mysqldump.exe`.

## Verifikasi

```sh
php artisan test --compact
php scripts/verify-document-numbers.php
```

Suite otomatis mencakup format, pergantian tanggal/tahun, nomor lama yang berlubang, suffix 100/1000, rollback, koreksi yang dapat diulang, konsistensi saldo/rincian, dan tampilan BS/OAS/Finance. Skrip MySQL menggunakan database verifikasi terisolasi dari `bs-verification-bootstrap.php`; memeriksa migrasi, cascade, koreksi data, dan enam alokasi nomor bersamaan.

Hasil akhir: **31 test lulus, 197 assertion**; enam alokasi MySQL serentak menghasilkan nomor berbeda dan berurutan. Aktivasi pada database lokal `emmo_baru` selesai pada 17 September 2026: BS ID 25 menjadi `BS-20260917-001`, pencairan ID 16 menjadi `CAIR-20260917-001`, dan OAS ID 18 menjadi `OAS26091701`. Rekonsiliasi memastikan hanya nomor yang berubah; seluruh nominal, status, tanggal, relasi BS, snapshot, dan audit lama tetap utuh. Backup lengkap dan hasil rekonsiliasi berada di `storage/app/private/document-number-backups/activation-20260917_025314/`.

~~~~

## FILE: docs/reimbursement-pribadi.md

SHA-256: `03abb87d8b6d3055c8c2dfc7fc2c088949763e3b6cd90103c2fa44ca0e2cc676`

~~~~markdown
# Penghapusan fitur uang pribadi dan reimbursement

Mulai 24 September 2026, fitur uang pribadi dan reimbursement dihapus sesuai keputusan pengguna.

- Operasional Sales dan Perjalanan Dinas memakai [saldo BS gabungan](saldo-bs-gabungan.md), tanpa memilih referensi pengajuan. Jenis pengajuan tidak membatasi pemakaian.
- Formulir menampilkan saldo tersedia. Perjalanan Dinas menyediakan pilihan pemilik saldo untuk anggota grup yang berhak.
- Permintaan lama dengan sumber dana PERSONAL atau referensi `bs_id` ditolak. Konfirmasi harus mempunyai saldo gabungan yang cukup.
- Draft lama tanpa BS dapat dikonfirmasi memakai saldo gabungan penginput. Transaksi final historis tanpa BS tetap menjadi arsip dan tidak dibebankan ulang.
- Menu, halaman, endpoint persetujuan/pembayaran, dan notifikasi reimbursement tidak tersedia lagi.
- Stok Kas, dashboard, detail, dan Excel menghitung pencairan BS, pengeluaran BS, serta event dalam/luar kota. Uang pribadi dan pembayaran reimbursement tidak masuk perhitungan.

Rumus Stok Kas: **saldo awal gabungan + pencairan BS - pengeluaran BS - event dalam kota - event luar kota + penyesuaian BS**.

Sisa Saldo BS tetap dihitung melalui kas BS tersendiri. Penyesuaian settlement BS historis tetap mengikuti aturan BS yang sudah ada.

## Data yang sudah tersimpan

Dokumen transaksi, klaim, log, dan foto pembayaran lama dipertahankan sebagai arsip. Transaksi lama tanpa BS tetap dapat dibaca pada detail transaksi; dokumen final tetap tidak dapat diubah. Tidak ada penghapusan tabel, rollback migration, atau pengubahan otomatis transaksi lama menjadi transaksi BS.

Tabel/model klaim, disk foto privat, dan kolom integrasi Stok Kas tetap disimpan untuk kompatibilitas arsip. Perhitungan ulang periode OPEN mengosongkan kedua komponen lama dan menghitung ulang total dari BS dan event. Snapshot CLOSED tetap dibekukan. Pemeriksaan database lokal sebelum perubahan tidak menemukan komponen uang pribadi/reimbursement bernominal pada snapshot CLOSED; lingkungan lain dengan saldo CLOSED yang sudah mencakup komponen tersebut memerlukan rekonsiliasi historis sebelum menggunakan laporan tanpa komponen itu.

Tidak diperlukan migration baru. Periode OPEN diperbarui ketika Stok Kas/dashboard/ekspor dibuka.

## Verifikasi

Pengujian di tests/Feature/BsCashFlowTest.php memakai SQLite dalam memori, mencakup penggunaan saldo gabungan pada tambah/edit/konfirmasi, penolakan permintaan PERSONAL lama, endpoint yang dihapus, akses baca dokumen historis, perhitungan Stok Kas/Excel tanpa komponen lama, penutupan dan saldo awal bulan berikutnya, serta regresi BS dan event.

~~~~

## FILE: docs/saldo-bs-gabungan.md

SHA-256: `a46b34355fd6f7074a4299bb672c8feae1c60f4bdca4b6451018db5d30ffa27a`

~~~~markdown
# Saldo BS gabungan

Mulai 24 September 2026, pencairan BS Operasional Sales dan Perjalanan Dinas masuk ke satu saldo per pemilik. Jenis pengajuan tetap dicatat untuk informasi dan laporan. Jenis tersebut tidak membatasi kategori realisasi atau membentuk saldo terpisah.

## Pengajuan dan pencairan

Nomor BS mengidentifikasi pengajuan, persetujuan, dan pencairan. Pengajuan dan approval atasan belum menambah saldo; hanya pencairan Finance yang menambah saldo pada bulan pencairannya. Detail pengajuan tidak lagi mempunyai rincian pemakaian atau sisa dana per dokumen. Laporan pengajuan dan ekspornya mencatat pengajuan serta pencairan, termasuk pengajuan yang masih menunggu atau ditolak.

## Realisasi

- Form Operasional Sales memakai saldo penginput sendiri, tanpa referensi nomor BS.
- Form Perjalanan Dinas memakai saldo sendiri atau saldo pemohon yang memberikan akses grup. Pilih **Pemilik Saldo BS**, bukan nomor pengajuan.
- Anggota dari pengajuan Perjalanan Dinas yang sudah dicairkan dapat memakai **seluruh saldo gabungan pemohon**, termasuk dana dari pencairan jenis lain. Akses anggota hanya berlaku pada modul Perjalanan Dinas. Keanggotaan tidak membagi saldo menjadi jatah per anggota/per pengajuan. Hak akses diperiksa kembali saat pengajuan pemakaian dan saat persetujuan pemohon.
- Draft dan permintaan yang menunggu tidak mengurangi atau mencadangkan saldo. Konfirmasi saldo sendiri langsung memeriksa total positif, periode OPEN, hak penginput, dan saldo tersedia. Konfirmasi anggota mengirim permintaan kepada pemohon; persetujuan pemohon yang membukukan realisasinya. Transaksi final tetap tidak dapat diedit/dihapus.
- Saldo tersedia = saldo awal final + pencairan aktual bulan transaksi - realisasi VALIDATED + penyesuaian historis. Saldo bulan sebelumnya tersedia sesudah tutup buku. Dana dari bulan mendatang tidak membiayai bulan sebelumnya.

Contoh: pencairan Operasional Rp1 juta dan Perjalanan Dinas Rp2 juta memberi saldo Rp3 juta. Realisasi Operasional Rp1,5 juta diperbolehkan; realisasi Perjalanan Dinas Rp500 ribu sesudahnya menyisakan Rp1 juta.

## Persetujuan pemakaian anggota grup dinas

Pemohon membuka **Persetujuan Pemakaian BS** dari dashboard, Rincian Saldo BS, atau notifikasi aplikasi. Jumlah permintaan menunggu mencakup semua periode. Halaman khusus ini dapat diakses pemilik melalui izin Pengajuan BS, Perjalanan Dinas, atau Cash Flow, termasuk ketika pemilik tidak memiliki izin modul Perjalanan Dinas. Persetujuan tetap hanya boleh dilakukan pemilik saldo; peran admin tidak memberi hak menyetujui saldo orang lain.

| Tindakan | Status | Pengaruh pada saldo |
| --- | --- | --- |
| Simpan Draft | DRAFT | Tidak ada |
| Konfirmasi anggota / Simpan & Ajukan Pemakaian | PENDING_OWNER (Menunggu Persetujuan Pemohon) | Tidak ada; rincian terkunci |
| Pemohon Setujui & Konfirmasi | VALIDATED (Dikonfirmasi) | Mengurangi saldo pemohon; final |
| Pemohon Tolak Pemakaian, dengan alasan | REJECTED_OWNER (Ditolak Pemohon) | Tidak ada |
| Anggota Tarik Permintaan ke Draft | DRAFT | Tidak ada; boleh diperbaiki |
| Anggota Buka Draft untuk Perbaikan setelah ditolak | DRAFT | Tidak ada; perlu diajukan ulang |

Pemohon melihat anggota, tanggal, tujuan, keterangan, kategori biaya, seluruh rincian, total, saldo tersedia, serta riwayat keputusan. Setelah persetujuan atau penolakan, anggota mendapat notifikasi aplikasi. Saat anggota menarik permintaan, pemohon juga diberi notifikasi. Notifikasi dikirim setelah transaksi database berhasil disimpan. Tidak ada persetujuan otomatis atau pengurangan saldo hanya karena pemohon belum merespons.

Permintaan menunggu **tidak menyisihkan saldo**. Saldo dan akses grup diperiksa kembali ketika pemohon menyetujui. Jika dana sudah dipakai transaksi lain, persetujuan ditolak dan permintaan tetap menunggu. Pemohon dapat menolak dengan alasan atau anggota dapat menarik permintaan walaupun keanggotaan grup sudah dicabut. Transaksi menunggu memblokir tutup buku seperti draft; transaksi ditolak tidak dihitung sebagai realisasi. Semua perubahan hanya diperbolehkan pada periode OPEN.

Setiap pengajuan ulang menaikkan `approval_version`. Form persetujuan membawa versi yang diperiksa; persetujuan dari halaman lama gagal jika anggota sudah menarik, memperbaiki, lalu mengajukan ulang. Riwayat `bs_usage_approval_logs` menyimpan pengajuan, persetujuan, penolakan, penarikan, serta pembukaan draft, dengan versi, pelaku, waktu, nominal, dan alasan. Rincian bisa diubah hanya setelah kembali ke draft. Semua perubahan status, audit final, dan ringkasan kas pada persetujuan berada dalam satu transaksi database. Penguncian periode, pengguna, dan dokumen serta pemeriksaan status mencegah pencatatan berulang.

Migrasi `2026_09_24_160000_add_bs_owner_approval.php` menambah metadata persetujuan dan tabel riwayat. Transaksi historis yang sudah VALIDATED tetap final, tidak dipaksa mengulang persetujuan. Cadangan kode sebelum perubahan ini tersedia di `storage/app/private/bs-owner-approval-backup-20260924-151346/`. Setelah ada permintaan baru, rollback kode/schema memerlukan rekonsiliasi status dan arsip persetujuan terlebih dahulu.

## Rincian dan data lama

**Rincian Saldo BS** (`/saldo-bs`) tersedia dari kartu saldo di dashboard serta Stok Kas. Halaman ini menampilkan saldo awal, pencairan, pemakaian kedua modul, penyesuaian historis, dan saldo berjalan per periode. Pengeluaran anggota masuk pada rincian pemilik dan mencantumkan penginput. Rincian transaksi tetap mengikuti izin modul. Halaman Stok Kas beserta Excel tetap menggabungkan kas BS dan event; event tidak mengurangi batas saldo BS.

Transaksi baru menyimpan `bs_owner_id` tanpa `bs_id`. Referensi lama tetap disimpan dan dibaca untuk menentukan pemilik dana historis. Transaksi historis final tanpa BS tidak otomatis dibebankan ke saldo. Draft lama dapat dikonfirmasi memakai saldo gabungan sesuai akses dan kecukupan dana. Riwayat settlement yang sudah tercatat tetap dihitung. Endpoint settlement per pengajuan menolak permintaan karena tidak sesuai dengan saldo gabungan, termasuk bila flag lama diubah.

Konfirmasi dicatat di `bs_usage_logs`, meliputi nomor transaksi, pemilik saldo, penginput, aktor konfirmasi, periode, nominal, dan waktu. Pencatatan, perubahan status, dan ringkasan kas berada dalam satu transaksi database. Penguncian pengguna/pemilik dan periode mencegah konfirmasi memakai saldo lama saat ada permintaan lain.

## Migrasi dan verifikasi

Migrasi `2026_09_24_140000_add_pooled_bs_funding.php` menambah kolom pemilik saldo dan tabel audit. Tidak memindahkan atau mengubah nominal transaksi lama. Kolom lama dipertahankan untuk arsip. Rollback schema setelah sudah ada pemakaian baru memerlukan rekonsiliasi/restore data terlebih dahulu, karena transaksi baru tidak mempunyai referensi pengajuan untuk dihitung oleh kode lama.

Migrasi telah dijalankan pada MySQL lokal. Sidik data sebelum/sesudah membuktikan seluruh baris dan nilai lama pada sembilan tabel keuangan tidak berubah. Enam pemeriksaan route pada skema MySQL asli (kedua form, rincian saldo, Cash Flow, dan kedua endpoint saldo) berhasil; seluruh perubahan database dalam pemeriksaan dibatalkan. Cadangan kode sebelum perubahan tersedia di `storage/app/private/bs-pooled-balance-backup-20260924-134913/`.

Verifikasi saldo gabungan sebelum penambahan persetujuan: `php artisan test --compact` lulus **131 test, 1.727 assertion**, memakai database SQLite dalam memori. Pengujian mencakup saldo lintas jenis, batas saldo gabungan, draft/final, akses anggota khusus PD, penarikan akses, privasi endpoint, rollback saat pencatatan gagal, laporan/Excel, arsip tanpa BS, tanggal pencairan, dan rollover setelah close. Pemeriksaan sintaks `public/js/bs-balance.js` lulus. Pengujian konkurensi MySQL secara simultan belum dijalankan.


Verifikasi persetujuan pemohon: `php artisan test --compact` lulus **140 test, 1.902 assertion**. Kasus tambahan mencakup permintaan menunggu tanpa pengurangan/cadangan saldo, otorisasi pemilik (termasuk penolakan akses admin lain), pemilik tanpa izin modul PD, rincian terkunci, penarikan/perbaikan/pengajuan ulang, penolakan dengan alasan, versi halaman lama, akses grup dicabut, saldo berubah sebelum persetujuan, persetujuan berulang, rollback kegagalan ringkasan kas, serta tutup buku. Hasil JUnit ada di `storage/app/private/bs-owner-approval-results.xml`.

Migrasi persetujuan telah diterapkan pada database lokal setelah MySQL XAMPP dihidupkan kembali. Sidik data sebelum/sesudah membuktikan seluruh baris dan nilai sebelumnya pada **10 tabel keuangan tetap sama**. Delapan pemeriksaan GET pada skema lokal berhasil, termasuk daftar permintaan, dashboard, kedua form, daftar PD, rincian saldo, dan kedua endpoint saldo. Halaman pemeriksaan pemohon berhasil dirender dengan contoh dalam memori tanpa memasukkan transaksi contoh ke database. Perubahan ringkasan yang mungkin dibuat GET dibatalkan melalui rollback. JavaScript eksternal dan dua skrip inline formulir PD lolos pemeriksaan sintaks. Skrip verifikasi tersedia di `storage/app/private/bs-owner-approval-smoke.php` dan `storage/app/private/bs-owner-approval-migration-check.php` (mode `after`). Pengujian konkurensi MySQL simultan tetap belum dilakukan.

~~~~

## FILE: docs/waktu-aplikasi.md

SHA-256: `99a5573829ebaf9b6f925f8a6dfb64aea373b9d993c9478a036be67b832c3a16`

~~~~markdown
# Waktu aplikasi

Waktu aksi mengikuti jam server saat aksi berhasil diproses. Tampilan memakai
`APP_DISPLAY_TIMEZONE=Asia/Jakarta` (WIB) secara default, dengan presisi detik.
Sinkronkan jam sistem operasi server secara otomatis agar waktu aksi akurat.

`config('app.timezone')` tetap UTC, sesuai nilai timestamp yang sudah ditulis
aplikasi. `App\Support\AppTime::format()` mengubah timestamp tersebut ke zona
tampilan tanpa mengubah nilai tersimpan. Jangan mengubah zona penyimpanan atau
zona sesi database tanpa memeriksa dan memigrasikan data lama: instalasi saat ini
memakai zona sesi MySQL `SYSTEM` dan nilai yang ditulis Laravel dalam UTC.

Sumber waktu:

| Aksi | Timestamp |
| --- | --- |
| Pengajuan BS | `pengajuan_bs.created_at` |
| Approval atau penolakan atasan/Finance | `bs_activity_logs.created_at` untuk aksi terkait |
| Pencairan | `approval_bs_finance.created_at` |
| Konfirmasi operasional/perjalanan dinas | `validated_at` |
| Penyelesaian BS | `settled_at` |
| Tutup/buka periode | `closed_at` / `reopened_at` |

`tanggal` dan `tanggal_cair` adalah tanggal bisnis bertipe DATE. Kolom ini tidak
mengandung jam dan tidak digunakan sebagai sumber waktu aksi. Pengajuan dan
pencairan baru memakai tanggal kalender lokal, termasuk untuk nomor dokumen dan
periode. Tanggal transaksi yang dipilih pengguna tetap merupakan tanggal bisnis.

Data lama langsung ditampilkan dari timestamp yang tersedia. Timestamp kosong
ditampilkan sebagai `-`; aplikasi tidak menebak jam yang belum pernah tercatat.
Waktu penolakan diambil dari log aksi agar tidak berubah ketika dokumen diedit.

Setelah mengubah konfigurasi pada server, jalankan `php artisan config:clear`
dan `php artisan view:clear`; mulai ulang worker jangka panjang jika digunakan.
Pemeriksaan baca saja pada MySQL: `php scripts/inspect-application-time.php`.
Pengujian: `php artisan test` (database SQLite dalam memori sesuai phpunit.xml).

~~~~
