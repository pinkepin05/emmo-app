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
