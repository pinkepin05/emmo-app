# Rekap pengajuan dan hasil Finance per modul

Rincian biaya gabungan (16 Oktober 2026): rincian pengajuan dan hasil pemeriksaan tampil dalam satu tabel dengan kolom **Item, Keterangan, Foto, Nominal Asal, Nominal Disetujui, Selisih** serta satu baris total untuk ketiga nominal. Foto terhubung ke rincian sumber yang sama; Finance memakai bukti tersimpan pada pemeriksaan, sedangkan pengguna biasa tetap memakai akses foto transaksi yang diizinkan. Catatan internal tetap hanya terlihat oleh Finance. Sebelum diperiksa atau saat rincian berubah, persetujuan/selisih ditampilkan sebagai tanda pisah; hasil lama tetap tersedia melalui pilihan pemeriksaan dan Jejak Audit. Dokumen lama yang menggabungkan kategori tetap memakai nominal historis tanpa membagikannya secara asumsi ke beberapa item. Pada ponsel, enam kolom disusun vertikal per item. Verifikasi: 6 tes lulus (301 assertion), mencakup empat modul, foto, nominal nol, perubahan sumber dan riwayat; 56 pemeriksaan browser pada lebar 320, 390, 844, dan 1366 piksel lulus tanpa kesalahan JavaScript. Pratinjau: `storage/app/private/unified-bs-recap-preview/rincian-gabungan.png`.

Penghapusan ringkasan hasil Finance (16 Oktober 2026): kotak **Hasil Finance terakhir** beserta keadaan kosongnya dihapus dari halaman detail. Teks yang sebelumnya merujuk ke kotak tersebut disesuaikan; rincian hasil pemeriksaan dan jejak audit tetap tersedia. Label **Ada Selisih** berasal dari adanya rincian yang nominal asalnya berbeda dari persetujuan Finance (`different_items`), sehingga bukan semata perbandingan total. Contoh total laporan Rp 200.000 dan persetujuan Rp 170.000 menghasilkan selisih Rp 30.000 yang tidak disetujui. Verifikasi: 2 tes lulus (175 assertion), termasuk catatan publik/internal dan empat modul, serta 48 pemeriksaan browser pada empat ukuran layar lulus.

Penghapusan sidebar detail (16 Oktober 2026): kotak **Total Pengajuan** dan **Alur pengajuan** dihapus dari halaman detail laporan. Informasi pengajuan, rincian biaya, serta hasil Finance memakai lebar penuh. Tombol Finance **Periksa Pengajuan/Cek Ulang** dipindahkan ke header dengan syarat akses dan kelayakan pemeriksaan yang sama. Verifikasi: 48 pemeriksaan browser pada empat ukuran layar lulus, serta tes alur empat modul lulus (141 assertion).

Pengelompokan Informasi pengajuan (16 Oktober 2026): informasi disusun dalam kelompok Identitas transaksi, Tanggal & konfirmasi, Tujuan & perjalanan/Lokasi & kegiatan, serta Pengeluaran & plafon. Tanggal dan periode berada satu baris, diikuti status **Dikonfirmasi User** bersama waktu konfirmasi. Tujuan dan Territory berdekatan, begitu juga jarak dan durasi. Deskripsi mendapat bidang tersendiri; ukuran teks, penekanan nominal, jarak, dan pemenggalan waktu diperbaiki. Kelompok menyesuaikan lebar kartu dan menjadi vertikal pada layar kecil. Verifikasi: tes alur empat modul lulus (141 assertion) dan 48 pemeriksaan browser pada empat ukuran layar lulus tanpa kesalahan JavaScript. Pratinjau: `storage/app/private/unified-bs-recap-preview/informasi-pengajuan-terkelompok.png`.

Informasi header pada laporan pengecekan (16 Oktober 2026): bagian **Informasi pengajuan** sekarang memuat ID, karyawan, tanggal, periode, status, waktu konfirmasi, total pengeluaran asli, dan deskripsi. Operasional/Perjalanan Dinas menampilkan pemilik saldo BS dengan dukungan relasi BS lama maupun saldo gabungan; Perjalanan Dinas juga menampilkan tujuan beserta provinsi/territory, jarak, durasi, nilai dan status plafon. Event mempertahankan hari libur/jam kerja serta melengkapi lokasi, jarak, durasi dan status plafon sesuai jenisnya. Data diambil dari transaksi sumber, dengan susunan dua kolom di desktop dan satu kolom di ponsel. Verifikasi: empat tes workspace lulus, serta 48 pemeriksaan browser pada lebar 320, 390, 844, dan 1366 piksel tanpa kesalahan JavaScript. Fixture membedakan pembuat transaksi dengan pemilik BS dan total asli Rp 200.000 dengan persetujuan Rp 170.000. Satu tes tambahan yang sudah ada (`test_transaction_workspace_preserves_filter_context_and_rejects_stale_review_tabs`) gagal pada perbandingan urutan parameter URL yang nilainya sama; alur filter tidak diubah dalam perubahan ini. Pratinjau: `storage/app/private/unified-bs-recap-preview/informasi-pengajuan-lengkap.png`.

Garis kolom menyambung (16 Oktober 2026): pada tampilan empat kolom, tiga garis pembatas sekarang memanjang dari tepi atas sampai tepi bawah panel, termasuk sela antarbaris. Garis berada di tengah pemisah kolom dan tidak menerima klik, sehingga tautan kategori dan kotak Finance tetap berfungsi. Tampilan tablet/ponsel mengikuti susunan responsif sebelumnya. Verifikasi: 250 pemeriksaan browser lulus pada lima ukuran layar dan pratinjau desktop diperiksa. Skrip browser juga menunggu dokumen baru saat berpindah ke URL dengan path yang sama, agar tidak memeriksa halaman sebelumnya. Pratinjau: `storage/app/private/unified-bs-recap-preview/rekap-garis-menyambung.png`.

Perapian grid rekap pemakaian (16 Oktober 2026): ringkasan dan rincian memakai pembagian empat kolom serta inset yang sama, sehingga nominal dan garis pemisah Finance sejajar. Padding, jarak antarbaris, tinggi minimum kartu, dan ukuran nominal diseragamkan; ringkasan dipisahkan dari rincian dengan garis horizontal. Pada layar kecil kolom menyesuaikan dan status Finance tersusun vertikal. Label **Total Pemakaian** serta dua tujuan tautan per jenis transaksi tetap berlaku. Verifikasi: 250 pemeriksaan browser lulus pada lebar 320, 390, 844, 1366, dan 1600 piksel tanpa kesalahan JavaScript; pratinjau desktop dan ponsel diperiksa. Pratinjau: `storage/app/private/unified-bs-recap-preview/rekap-grid-rapi.png`.

Daftar pribadi dan BS bersama (16 Oktober 2026): daftar serta ekspor Operasional/Perjalanan Dinas untuk pengguna biasa sekarang dibatasi pada pembuat transaksi (`user_id`); hubungan sebagai pemilik saldo BS tidak lagi menambahkan transaksi anggota ke daftar pribadi. Akses Admin/Finance dan akses detail pemilik BS tetap berlaku. Detail Cash Flow menambahkan **Pemakaian BS Bersama** untuk pemakai dan pemilik BS, dengan nomor transaksi, nama kedua pihak, nominal dilaporkan, dan hasil Finance terkini. Bagian ini mengikuti bulan **tanggal transaksi**, termasuk data lama yang kolom periodenya berbeda, dan memiliki pagination serta lembar ekspor tersendiri. Rincian bersama bersifat informasi: saldo dan total pengeluaran tetap hanya dibebankan pada pemilik BS melalui perhitungan Finance yang sudah ada. Verifikasi: 7 tes lulus (288 assertion), 250 pemeriksaan browser pada lima ukuran layar, serta pemeriksaan baca saja transaksi `PD26082659` yang muncul pada rincian Agustus Budi dan Rina. Pratinjau: `storage/app/private/unified-bs-recap-preview/pemakaian-bs-bersama.png`.

Daftar dari Total Dilaporkan (16 Oktober 2026): tautan jenis transaksi sekarang membawa `dilaporkan=1`, sehingga daftar Operasional, Perjalanan Dinas, dan kedua Event hanya menampilkan transaksi berstatus `VALIDATED` pada periode pilihan. Penanda **Sudah dikonfirmasi user** menjelaskan filter yang aktif; pergantian periode, pagination, dan ekspor mempertahankan filter tersebut. Daftar biasa dari menu tetap menampilkan Draft. Verifikasi: 4 tes lulus (266 assertion), termasuk kasus Rp 200.000 terkonfirmasi dan Rp 20.000 Draft, empat modul, nominal, ekspor, pagination, periode, dan lingkup pengguna. Sebanyak 230 pemeriksaan browser lulus pada lima ukuran layar tanpa kesalahan JavaScript.

Riwayat Cash Flow sampai bulan berjalan (16 Oktober 2026): mode semua periode dari Dashboard tetap mencakup seluruh bulan lampau dan bulan berjalan, tetapi menyembunyikan bulan mendatang seperti 202611 saat masih Oktober. Pembatasan diterapkan sebelum perhitungan snapshot, pada Riwayat Periode, dan ekspor semua periode. Data periode mendatang tetap tersimpan dan otomatis tersedia ketika bulannya tiba sesuai zona waktu aplikasi. Verifikasi: 10 tes lulus (187 assertion), termasuk pergantian bulan, mode biasa/semua periode, ekspor, navigasi Dashboard, dan lingkup data pengguna.

Navigasi Cash Flow (16 Oktober 2026): **Lihat Detail** pada Saldo BS Dashboard selalu membuka daftar Cash Flow semua periode (`cash-flow.index?semua_periode=1`), tanpa membawa pilihan periode Dashboard. Dari daftar ini pengguna dapat membuka rincian bulanan; tombol Kembali pada rincian mempertahankan daftar semua periode. Filter Dashboard tetap menghitung saldo dan rekap untuk bulan yang dipilih. Verifikasi: 3 tes lulus (126 assertion), mencakup pilihan bulan berbeda, seluruh periode milik pengguna, tautan ekspor, navigasi rincian/kembali, dan akses tanpa izin Cash Flow. Ekspektasi periode kosong pada tes navigasi disesuaikan dengan perilaku Dashboard yang sudah memakai bulan berjalan.

Total nominal daftar transaksi (16 Oktober 2026): daftar Operasional Sales dan Perjalanan Dinas menampilkan kolom **Total Nominal** sebelum Status, berupa jumlah rincian biaya aktif per transaksi dengan format Rupiah dua desimal. Draft tetap menampilkan jumlah rincian saat ini dan transaksi tanpa rincian menampilkan Rp 0,00. Nilai yang ditampilkan adalah nominal transaksi, termasuk saat persetujuan Finance berbeda. Daftar monitoring memakai perhitungan yang sama; daftar Event Dalam/Luar Kota memakai nominal Event dengan label dan format yang konsisten. Verifikasi: 3 tes lulus (124 assertion), mencakup rincian ganda, rincian yang dihapus, Draft, transaksi kosong, monitoring, filter periode, pagination, dan akses Detail; 210 pemeriksaan browser lulus pada lima ukuran layar tanpa kesalahan JavaScript.

Tautan nomor transaksi (16 Oktober 2026): nomor pada kolom Pengajuan di Daftar Pengecekan Finance sekarang membuka halaman detail asli masing-masing modul. Operasional dan kedua Event menggunakan ID sumber, sedangkan Perjalanan Dinas menggunakan nomor transaksi sesuai rutenya. Verifikasi: 3 tes lulus (278 assertion), termasuk membuka detail asli dari 12 transaksi pada empat modul dan mempertahankan konteks filter/pagination pada alur laporan Finance.

Penyederhanaan Daftar Pengecekan Finance (16 Oktober 2026): kartu Total Pengajuan serta kartu Dikonfirmasi Finance, Pending, dan Ditolak di atas tabel dihapus. Form hanya menampilkan pencarian nomor pengajuan/transaksi, periode, tombol Tampilkan, serta filter **Sudah Dicek** dan **Belum Dicek**. Pintasan Bulan ini/Semua periode dihapus, termasuk pada keadaan daftar kosong. Sudah Dicek mencakup keputusan terbaru yang masih sesuai versi transaksi (disetujui, ditolak, atau revisi lama); transaksi baru atau berubah setelah pemeriksaan masuk Belum Dicek. Jumlah pada kedua tab mengikuti jenis, periode, pencarian, dan hak akses tanpa menyusut akibat tab yang dipilih. Saat masuk dari dashboard daftar tetap menampilkan semua status; klik ulang tab aktif melepas filter status. Rincian dan total di bagian bawah tabel tetap tersedia.

Verifikasi penyederhanaan: 7 tes lulus (393 assertion), termasuk kedua filter pada empat modul, keputusan terakhir, revisi lama, perubahan transaksi, pencarian/periode, pagination, kembali dari detail, dan lingkup akses pengguna. Sebanyak 190 pemeriksaan browser lulus pada lima ukuran layar tanpa kesalahan JavaScript. Pratinjau: `storage/app/private/unified-bs-recap-preview/daftar-pengecekan-finance-ringkas.png`.

Pembaruan 16 Oktober 2026: rincian pemakaian sekarang disusun per jenis transaksi. Setiap baris memiliki dua tautan: **Total Dilaporkan / Jenis Transaksi** membuka daftar transaksi asli, sedangkan **satu kotak Finance** menampilkan nominal dan jumlah transaksi untuk Dikonfirmasi Finance, Pending / Belum Dicek, dan Total Ditolak. Seluruh kotak Finance membuka **Daftar Pengecekan Finance** jenis tersebut dengan semua status, mempertahankan periode tanpa membawa filter status/referensi lama. Nilai nol tetap terlihat; keputusan revisi lama tetap ditampilkan jika ada. Pada ponsel, bagian dalam kotak menyesuaikan menjadi vertikal.

Verifikasi penggabungan: 6 tes lulus (287 assertion) dan 175 pemeriksaan browser lulus pada lebar 320, 390, 844, 1366, dan 1600 piksel. Mencakup nominal dan jumlah transaksi, klik bagian status yang tetap membuka semua status, tautan daftar asli, filter periode, privasi, dan pagination. Fixture browser memakai CSS Bootstrap lokal dan memblokir CDN eksternal agar pengujian tidak tergantung koneksi internet; aset aplikasi tidak diubah. Pratinjau: `storage/app/private/unified-bs-recap-preview/rekap-finance-gabungan.png`.

Pembaruan aksi daftar transaksi (15 Oktober 2026): daftar Operasional, Perjalanan Dinas, Event Dalam Kota, dan Event Luar Kota memakai tombol **Detail** langsung tanpa dropdown. Tombol **Hapus** hanya muncul untuk peran `ADMIN`, `ADMINISTRATOR`, dan `FINANCE`, dan keempat endpoint penghapusan memeriksa peran tersebut. Operasional/Perjalanan Dinas tetap hanya dapat dihapus saat Draft, seluruh penghapusan tetap mengikuti periode terbuka, dan mode monitoring tetap hanya baca. Izin melihat semua transaksi pada peran `DIR` tidak memberikan izin menghapus.

Verifikasi aksi: 13 tes lulus (377 assertion), termasuk tautan Detail, izin penghapusan, periode/status terkunci, penghapusan rincian/foto, dan pagination; 145 pemeriksaan browser lulus pada lebar 320, 390, 844, 1366, dan 1600 piksel tanpa kesalahan JavaScript. Pengujian memakai SQLite dalam memori.

## Rekap pemakaian dan konfirmasi digabung — 15 Oktober 2026

Dashboard sekarang menampilkan dua panel: **Rekap Pengajuan & Pencairan BS** dan **Rekap Pemakaian & Konfirmasi BS**. Panel kedua memiliki empat kolom: total dilaporkan, dikonfirmasi Finance, pending/belum dicek, dan total ditolak. Selisih yang tidak disetujui Finance digabung ke Total Ditolak. Pada tablet dan ponsel, kolom menyesuaikan lebar layar.

Rincian **Operasional Sales, Perjalanan Dinas, Event Dalam Kota, dan Event Luar Kota** langsung terlihat di bawah setiap kolom, beserta nominal dan jumlah transaksinya. Tombol **Lihat Riwayat** dan mekanisme buka-tutup dihapus dari panel pemakaian. Pada **Total Dilaporkan**, kartu membuka halaman **Daftar Transaksi** asli masing-masing modul (`/transaksi-operasional`, `/transaksi-perjalanan-dinas`, `/event-dalam-kota`, atau `/event-luar-kota`), dengan parameter `periode` dari pilihan dashboard. Daftar asli tetap menampilkan status transaksi sebagaimana alur modulnya; rekap Total Dilaporkan tetap hanya menghitung transaksi terkonfirmasi. Kolom hasil Finance membuka daftar rekap dengan status dan periode yang sesuai. Semua tautan tetap bekerja tanpa JavaScript. Keputusan lama Perlu Revisi tetap ditampilkan apabila ada. Pembatasan akses tetap berlaku; tidak memerlukan migrasi.

Total Ditolak dihitung per transaksi berdasarkan keputusan Finance terakhir yang masih berlaku: penolakan penuh memakai seluruh nominal laporan, sedangkan persetujuan sebagian memakai selisih positif antara total laporan dan total persetujuan. Persetujuan melebihi laporan tidak mengurangi penolakan transaksi lain atau menghasilkan penolakan negatif. Pending, sumber yang berubah, draft, dan keputusan lama Perlu Revisi tidak masuk kelompok ini. Satu transaksi dihitung sekali.

Filter riwayat `NOT_APPROVED` mencakup penolakan penuh dan sebagian, dengan kolom **Nominal Ditolak** serta total seluruh hasil filter yang sama dengan ringkasan. Kartu dan footer daftar modul juga memakai total gabungan. Filter lama `REJECTED` (penolakan penuh) dan `DIFFERENCE` (semua selisih, termasuk negatif) tetap tersedia. Keputusan tersimpan, saldo, dan jurnal tidak diubah. Contoh: laporan Rp200.000 dan persetujuan Rp170.000 menghasilkan Total Ditolak Rp30.000.

Verifikasi memakai fixture SQLite dalam memori: delapan tes terfokus lulus dan 90 pemeriksaan browser lulus pada lebar 320, 390, 844, 1366, dan 1600 piksel. Mencakup penolakan penuh/sebagian, keputusan terakhir, nominal desimal, perubahan sumber, pagination, periode, dan hak akses. Satu tes diulang setelah benturan cache view Windows ketika PHPUnit dan renderer browser berjalan bersamaan; pengujian ulang lulus. Skrip pratinjau: `scripts/bs-recap-browser-test.mjs`. Screenshot terbaru: `storage/app/private/unified-bs-recap-preview/rekap-pemakaian-konfirmasi-bs-4-kolom.png`.

Pemeriksaan tambahan menemukan satu tes periode lama masih gagal: `test_global_dashboard_period_changes_cash_funding_usage_and_finance_together` mengharapkan periode kosong berarti semua periode, sedangkan route dashboard saat ini menormalkannya ke bulan berjalan. Perilaku route tersebut sudah ada sebelum penggabungan panel dan tidak diubah dalam pekerjaan ini. Bagian di bawah merupakan riwayat susunan sebelumnya.

Pembaruan rincian langsung tampil: 90 pemeriksaan browser lulus pada lima ukuran layar yang sama, termasuk seluruh 16 tautan kategori terlihat tanpa membuka dropdown dan navigasi tetap membawa filter yang sesuai. Screenshot tampilan terbaru: `storage/app/private/unified-bs-recap-preview/rekap-pemakaian-rincian-langsung.png`. Pembaruan ini hanya mengubah tampilan; perhitungan nominal memakai hasil penggabungan di atas.

Pembaruan tautan Total Dilaporkan: daftar Perjalanan Dinas dan kedua Event kini menerima filter `periode`, mempertahankannya pada pagination, serta menyediakan pilihan periode untuk **Tampilkan** dan **Export**. Daftar Operasional memakai filter yang sudah tersedia. Tiga tes terfokus lulus (196 assertion) dan 105 pemeriksaan browser lulus pada lima ukuran layar, termasuk klik ke keempat daftar asli, periode terpilih, semua periode, pagination, serta pembatasan data pengguna.

## Filter periode seluruh dashboard — 13 Oktober 2026

Filter periode berada di kiri Saldo BS, tepat di bawah banner Dashboard Transaksi. Pada layar kecil, filter berada di atas Saldo BS. Filter lama di dalam rekap dashboard dihapus; filter nomor dan periode pada daftar/histori tetap tersedia.

- Tanpa parameter `pemakaian_periode`, dashboard memakai bulan kalender saat permintaan dibuka, melalui `PeriodService::current()` dan zona waktu aplikasi. Tombol **Bulan ini** kembali ke URL tanpa parameter sehingga tidak terikat pada bulan yang pernah disimpan di tautan.
- `pemakaian_periode=YYYY-MM` memilih satu bulan untuk Saldo BS, pengajuan/pencairan, pemakaian per modul, dan Konfirmasi Finance. Nilai kosong (`pemakaian_periode=`) berarti **Semua Periode**, termasuk periode transaksi yang belum memiliki snapshot.
- `StockCashFlowService::dashboardSummary()` memakai snapshot bulanan untuk satu periode. Untuk semua periode, saldo awal diambil sekali dari awal riwayat keuangan pengguna; total masuk, keluar, dan penyesuaian dijumlahkan. Saldo bawaan antarbulan tidak dijumlahkan kembali. Snapshot periode CLOSED yang sudah diterbitkan tetap dipertahankan.
- Riwayat pengajuan/pencairan mengikuti **periode pengajuan BS**. Penerimaan Stok Kas mengikuti **tanggal pencairan**. Satu filter tidak mengubah dasar pembukuan tersebut. Saldo BS tetap milik pengguna yang masuk; lingkup Finance pada rekap tetap mengikuti izin yang sudah ada.
- **Lihat Detail** membuka bulan terpilih. Pada Semua Periode, tautan membuka `/cash-flow?semua_periode=1` dan ekspor mengikuti cakupan tersebut; halaman Stok Kas biasa tetap membatasi daftar sampai bulan berjalan. Tautan histori dan tautan kembali mempertahankan pilihan periode.

Verifikasi perubahan filter: **14 tes lulus, 234 assertion**, dan **90 pemeriksaan browser lulus** pada lebar 320, 390, 844, 1366, dan 1600 piksel. Mencakup pergantian bulan kalender, seluruh periode, saldo awal tanpa hitung ganda, snapshot CLOSED, pencairan lintas bulan, izin akses, periode kosong, navigasi, serta tata letak. Data pengujian menggunakan SQLite dalam memori. Skenario: `tests/Concerns/DashboardPeriodScenarios.php`, `tests/Feature/CashFlowPeriodTest.php`, dan `scripts/bs-recap-browser-test.mjs`. Tidak memerlukan migrasi.

## Pembaruan rekap BS 13 Oktober 2026

Urutan dashboard adalah **Dashboard Transaksi**, **Filter Periode di sebelah Saldo BS**, lalu seluruh rekap berikut:

1. **Rekap Pengajuan & Pencairan BS** menampilkan total diajukan, dicairkan, pending, dan ditolak dalam satu panel. Setiap bagian membuka histori yang dapat dipaginasi di `/rekap-bs`. Semua bagian mengikuti periode pengajuan BS. Total Ditolak mencakup penolakan penuh dan selisih pencairan sebagian; Total Pending mencakup pengajuan yang menunggu Atasan atau Finance.
2. **Rekap Pemakaian BS** menampilkan nominal yang dilaporkan dan dikonfirmasi user untuk Operasional Sales, Perjalanan Dinas, Event Dalam Kota, dan Event Luar Kota. Tombol **Lihat Histori** membuka daftar transaksi modul tersebut dengan filter yang sama.
3. **Konfirmasi Finance** mempertahankan kartu hasil pemeriksaan per modul, termasuk Disetujui Finance, Ditolak, Pending / Belum Dicek, dan Selisih Persetujuan Finance.

User biasa hanya melihat pengajuan dan pencairan miliknya. Finance atau pengguna yang memiliki akses lintas transaksi dapat melihat seluruh user. Total histori mencakup semua hasil filter, bukan hanya halaman aktif. Rekap kosong menampilkan nol.

Pencarian nomor tersedia pada halaman daftar/histori pengajuan, pencairan, dan pemakaian. Dashboard hanya memfilter periode. Tautan kembali dari histori membawa periode tanpa nomor pencarian atau status; parameter pencarian/status dari URL dashboard lama diabaikan agar ringkasan tetap mencakup seluruh transaksi pada periode tersebut. Tautan dashboard lama yang menyebut modul tetap diarahkan ke daftar modul beserta filter pencariannya.

Verifikasi pemisahan filter: **46 tes terfokus lulus, 964 assertion**, dan **28 pemeriksaan browser lulus** pada lebar 320, 390, 844, dan 1366 piksel. Tautan dashboard lama tidak membatasi total berdasarkan nomor/status; pencarian pada histori tetap memfilter dokumen dan tautan kembali mempertahankan periode.

Error `Undefined array key "pengajuan"` diperbaiki dengan menyelaraskan data controller dan Blade. Template sebelumnya mengakses struktur `pengajuan` yang tidak tersedia dalam hasil `BsUsageDashboardService::data()`. Rekap pendanaan kini disediakan oleh `BsFundingRecapService` melalui key `funding`; angka contoh dan route histori yang belum terdaftar telah dihapus. Perubahan ini tidak memerlukan migrasi.

Verifikasi: **51 tes terfokus lulus, 1.168 assertion**, serta **24 pemeriksaan browser lulus** pada lebar 320, 390, 844, dan 1366 piksel. Pengujian mencakup nominal, filter periode termasuk pencairan lintas bulan, pembatasan data per user, kondisi kosong, pagination, navigasi histori, dan tata letak. Fixture memakai SQLite dalam memori; pengujian ini tidak mengubah database bisnis. Skenario baru berada pada `tests/Concerns/BsFundingRecapScenarios.php` dan `scripts/bs-recap-browser-test.mjs`.

Bagian bertanggal lebih lama di bawah merupakan riwayat pengembangan; susunan dashboard terkini mengikuti pembaruan ini.

Riwayat pemeriksaan ditampilkan sebagai **Jejak Audit (Audit Trail)** di setiap Detail Transaksi dan hasil pengecekan. Satu kejadian mewakili satu konfirmasi pemeriksaan, berurutan sesuai penyimpanan, dengan nama pemeriksa, waktu, keputusan, nominal, catatan untuk pemohon, dan rincian item yang dapat dibuka. Catatan internal item hanya tampil untuk Finance. Cek ulang menambah kejadian; hasil sebelumnya tetap tersimpan. Tab riwayat pada daftar utama dihapus; tautan laporan lama tetap tersedia untuk arsip. Draft belum menjadi kejadian audit karena belum dikonfirmasi.

Pembaruan 12 Oktober 2026: kolom tindakan memakai satu tombol **Detail Transaksi** pada daftar modul dan Pengecekan Pemakaian BS. Route `transaction-recap.show` memuat bagian **Rincian & Hasil** (pengajuan, foto bukti, hasil per item, dan riwayat). Form pemeriksaan (`?pemeriksaan=1`) dibuka melalui tombol **Periksa Pengajuan** untuk pemeriksaan pertama atau **Cek Ulang** untuk pengajuan yang sudah diperiksa; tidak ada tab Pemeriksaan Finance. Simpan Draft kembali ke pemeriksaan; Simpan Konfirmasi kembali ke hasil pada halaman yang sama. Filter daftar tetap dibawa. Pemeriksaan hanya tersedia bagi Finance untuk pengajuan yang telah dikonfirmasi, tidak menunggu pengajuan ulang, dan periodenya masih terbuka. Catatan internal dan draft hanya terlihat oleh Finance. Pemilihan hasil lama (`?hasil=ID`) dibatasi pada transaksi yang sama. Route pemeriksaan lama tetap tersedia untuk kompatibilitas dan arsip yang sumbernya sudah tidak tersedia. Tidak ada migrasi tambahan.

Pembaruan 8 Oktober 2026: status pemeriksaan ulang digabung ke **Pending / Belum Dicek** (`PENDING`). Pengajuan baru dan pengajuan yang berubah setelah diperiksa dihitung bersama pada nominal serta jumlah Pending. Kartu, filter, dan footer khusus status lama dihapus. Tautan filter lama diarahkan ke Pending. Deteksi perubahan sumber dan riwayat Finance tetap dipakai agar keputusan lama tidak dianggap berlaku untuk pengajuan terbaru; tidak diperlukan migrasi database.

Pembaruan 7 Oktober 2026: [persetujuan Finance per rincian biaya](persetujuan-finance-per-rincian.md). Item berkategori sama diperiksa terpisah; total laporan menjumlahkan persetujuan setiap rincian.

Pembaruan 7 Oktober 2026: [sinkronisasi Stok Kas dan saldo BS](sinkronisasi-stok-kas-finance.md) menggantikan aturan lama bahwa hasil Finance tidak mengubah saldo.

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

- **Belum Dicek** (`PENDING`): belum ada keputusan Finance untuk versi pengajuan terbaru, termasuk pengajuan yang berubah setelah diperiksa; seluruh nominal terbaru dan jumlah transaksinya masuk Pending.
- **Perlu Revisi** (`REVISION`): Finance meminta perbaikan; usulan pemeriksaan belum final sehingga seluruh nominal pengajuan masuk Revisi/Pending.
- **Ada Selisih** (`DIFFERENCE`): keputusan terakhir disetujui dengan perbedaan nominal per item; selisih bertanda masuk Revisi/Pending.
- **Perlu Tindak Lanjut** (`FOLLOW_UP`): filter gabungan Pending, keputusan lama Perlu Revisi, dan persetujuan dengan selisih. Kartu nominal menampilkan masing-masing kelompok secara terpisah.

Setiap baris dan footer daftar gabungan menampilkan nominal Revisi/Pending. Angka mengikuti pemeriksaan terakhir; setelah persetujuan terbaru sesuai dengan seluruh item pengajuan, transaksi keluar dari filter Revisi/Pending. Perhitungan dilakukan saat membaca laporan, tanpa mengubah keputusan/riwayat tersimpan atau memerlukan migrasi baru.

Verifikasi penyesuaian Revisi/Pending: **49 tes lulus, 877 assertion** pada SQLite dalam memori, mencakup empat modul, pengajuan/persetujuan sebagian, penolakan, keputusan terbaru, perubahan sumber, akses pemilik, filter dan pagination, selisih negatif/desimal, serta selisih antaritem yang saling meniadakan. **32 pemeriksaan tampilan browser lulus** setelah penambahan filter dan kolom laporan. Hasil tes: `storage/app/private/revisi-pending-tests.txt`. Pemeriksaan ini tidak mengakses database bisnis dan bukan verifikasi ulang pada MySQL. Cadangan perubahan: `storage/app/private/revisi-pending-backup-20260929-140150/`.

Filter periode, modul, nomor pengajuan, dan status berlaku pada rekap serta daftar. Total selalu menghitung seluruh hasil filter, bukan hanya halaman aktif. Tautan pada kartu membuka daftar modul/status yang dipilih.

## Daftar Transaksi gabungan

Dashboard Transaksi memiliki dua tab: **Ringkasan** dan **Daftar Transaksi**. Pergantian tab dilakukan langsung tanpa memuat ulang dokumen atau berpindah halaman.

- **Ringkasan:** total nominal dan status per modul.
- **Daftar Transaksi:** satu baris per pengajuan, berisi nomor/modul/tanggal, user dan konfirmasi, nominal pengajuan, nominal disetujui Finance, Revisi/Pending, status Finance, serta tombol Rincian. Pemeriksaan terakhir dipakai agar pengecekan ulang tidak menggandakan pengajuan.
- Persetujuan final yang belum tersedia ditampilkan sebagai tanda `—`, disertai status Belum Dicek atau keputusan lama Perlu Revisi. Keputusan final bernilai nol tetap ditampilkan Rp0.
- Footer menampilkan total Pengajuan, Disetujui, Tidak Disetujui, dan Revisi/Pending untuk seluruh hasil filter.

Menu sidebar **Rekap Transaksi → Daftar Transaksi** membuka daftar gabungan yang sama pada halaman laporan. URL utama: `/laporan-transaksi?tampilan=daftar`. Tautan lama dengan `tampilan=pengajuan` atau `tampilan=finance` tetap diterima dan memakai tampilan gabungan. Dashboard mempertahankan tab, filter, halaman, dan konteks kembali dari rincian; semua periode dan semua modul tidak berubah saat kembali. Tanpa JavaScript, tab tetap bekerja melalui navigasi dashboard biasa.

**Rincian Transaksi** memperlihatkan pengajuan asli serta bagian **Hasil Finance terakhir**: nominal persetujuan, Revisi/Pending, selisih keputusan final, nomor dokumen pemeriksaan, checker, tanggal/waktu simpan, dan catatan keputusan untuk user. Jika sumber berubah setelah diperiksa, hasil lama ditandai sebagai dokumen sebelumnya, dan tidak dipakai sebagai persetujuan final.

User biasa hanya mengakses transaksi miliknya. Permission `akses.approval-finance` atau role yang memenuhi `canSeeAllTransactions()` dapat membaca rekap lintas user. Tombol Periksa/Cek Ulang, rincian pemeriksaan internal, serta riwayat hanya tersedia bagi Finance. Catatan pemeriksaan per item tidak dimuat dalam hasil publik; catatan keputusan untuk user ditampilkan dengan escaping Blade.

**Laporan Pengecekan BS** tetap terpisah untuk Finance, dengan pilihan Cek Terakhir atau Semua Riwayat. Penggabungan daftar tidak mengubah keputusan, perhitungan nominal, saldo, atau riwayat pemeriksaan.

## Konfirmasi Event

Sesuai pilihan user, **Event lama menunggu konfirmasi**. Migrasi tidak menganggap simpan lama sebagai konfirmasi. Pada daftar Event, buka rincian lalu tekan **Konfirmasi Pengajuan**. Tautan rincian kini juga ada pada daftar Event Luar Kota.

Hanya pemilik Event dengan izin modul tersebut yang dapat mengonfirmasi. Token snapshot menolak konfirmasi atas rincian yang berubah sejak dibuka. Klik ulang atas Event yang sudah dikonfirmasi tidak membuat versi baru atau mengubah waktu konfirmasi.

Edit Event mengembalikannya ke `DRAFT`, membersihkan informasi konfirmasi, dan menaikkan versi sumber. Setelah dikonfirmasi lagi, pengajuan masuk Pending meskipun total nominal tidak berubah. Setiap keputusan Finance berikutnya membuat dokumen pemeriksaan baru; riwayat lama tetap tersedia.

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
