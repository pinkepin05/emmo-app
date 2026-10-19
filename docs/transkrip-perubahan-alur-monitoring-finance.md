# Transkrip kebutuhan: perubahan alur Monitoring Finance

Tanggal pencatatan: 15 Oktober 2026.

Status: disimpan sementara untuk melanjutkan pekerjaan nanti. Perubahan alur ini belum diminta untuk diimplementasikan. Pencatatan ini hanya mengubah dokumentasi.

Dokumen ini melanjutkan [rencana monitoring Finance berdasarkan Company dan User](rencana-monitoring-finance-company-user.md). Isinya merupakan rangkuman percakapan, bukan salinan kata demi kata.

## Latar belakang

Setelah melihat halaman Monitoring User, pengguna ingin Finance dapat membuka halaman daftar transaksi masing-masing modul untuk user yang dipilih, kemudian membuka halaman detail transaksi asli modul tersebut.

Contoh yang diberikan adalah halaman **Operasional Area Sales** dan **Detail Nota Operasional**. Alur yang sama diinginkan untuk empat jenis transaksi: Operasional, Perjalanan Dinas, Event Dalam Kota, dan Event Luar Kota.

Pada contoh Monitoring User, transaksi Draft sudah terlihat. Kebutuhan baru mencakup perubahan tujuan navigasi ke halaman daftar dan detail asli, sekaligus memastikan akses melihat transaksi tersedia untuk seluruh status.

## Alur yang diinginkan

**Finance → pilih Company → pilih User → pilih jenis transaksi → Daftar Transaksi User pada modul tersebut → Detail Transaksi.**

1. Finance dapat memilih Company mana pun dalam cakupan monitoring seluruh Company.
2. Halaman Company menampilkan rekap Company dan daftar user di dalamnya.
3. Finance memilih salah satu user dan melihat ringkasan Stok Kas user tersebut.
4. Finance memilih Operasional, Perjalanan Dinas, Event Dalam Kota, atau Event Luar Kota untuk membuka halaman daftar transaksi modul yang bersangkutan.
5. Daftar tersebut menampilkan transaksi milik user yang dipilih dalam konteks Company tersebut.
6. Finance dapat membuka detail asli setiap transaksi, termasuk rincian biaya, keterangan, foto bukti, serta lampiran yang tersedia.

Contoh: Finance memilih PT. Marifood Putra Kencana, kemudian BUDI SANTOSO, lalu Operasional. Halaman Operasional Area Sales menampilkan transaksi BUDI SANTOSO. Memilih salah satu transaksi membuka Detail Nota Operasional transaksi tersebut.

## Cakupan transaksi dan navigasi

- Akses melihat daftar dan detail mencakup seluruh status pengajuan, termasuk Draft, menunggu persetujuan, dan sudah dikonfirmasi user.
- Transaksi dapat dilihat saat belum diperiksa Finance, sudah disetujui, ditolak, maupun memerlukan pemeriksaan ulang.
- Konfirmasi atau persetujuan Finance bukan syarat untuk melihat detail transaksi.
- Status pengajuan dan keputusan Finance tetap dibedakan dalam tampilan.
- Company, user, dan filter periode yang dipilih harus tetap terbawa ketika berpindah halaman.
- Tombol kembali dari detail mengarah ke daftar transaksi dengan konteks yang sama; navigasi berikutnya tetap dapat kembali ke Monitoring User dan Company terkait.

## Ringkasan keuangan yang dipertahankan

- Stok Kas setiap user tetap menampilkan total uang masuk, total uang keluar, dan sisa saldo.
- Rekap Company tetap mencakup gabungan data user pada Company tersebut sesuai periode yang dipilih.
- Perhitungan mengikuti aturan aplikasi yang sudah ada, termasuk saldo awal, penyesuaian, dan keputusan Finance yang masih valid.
- Menampilkan transaksi Draft atau belum diperiksa tidak otomatis menjadikannya pengeluaran dalam Stok Kas.

## Batas perubahan yang dicatat

Permintaan ini berfokus pada akses melihat daftar dan detail transaksi dari Monitoring Finance. Hak membuat, mengubah, menghapus, mengonfirmasi pengajuan, atau memberikan keputusan Finance tidak diperluas otomatis hanya karena Finance membuka halaman modul tersebut.

Bentuk tombol atau pilihan modul, penyesuaian permission, dan rincian teknis perlu dicocokkan dengan kode yang tersedia ketika implementasi diminta. Jangan mulai mengubah kode aplikasi untuk perubahan alur ini sebelum pengguna memberikan instruksi melanjutkan.

## Naskah untuk melanjutkan percakapan

> Saya ingin mengubah alur Monitoring Finance. Setelah Finance memilih Company dan User, Finance dapat memilih salah satu dari empat jenis transaksi: Operasional, Perjalanan Dinas, Event Dalam Kota, atau Event Luar Kota. Pilihan tersebut membuka halaman daftar transaksi asli modul untuk user yang dipilih. Dari daftar itu, Finance dapat membuka halaman detail transaksi asli, seperti Operasional Area Sales menuju Detail Nota Operasional.
>
> Finance harus dapat melihat seluruh status transaksi, termasuk Draft dan transaksi yang belum diperiksa Finance. Pertahankan konteks Company, User, dan periode pada daftar, detail, serta tombol kembali. Stok Kas User dan rekap Company tetap tersedia dengan aturan perhitungan yang sudah ada. Akses melihat transaksi tidak otomatis menambahkan hak mengedit atau menghapus.
>
> Simpan kebutuhan ini terlebih dahulu. Jangan mulai mengimplementasikan perubahan alur sampai saya memberikan instruksi untuk melanjutkan.
