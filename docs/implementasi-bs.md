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
