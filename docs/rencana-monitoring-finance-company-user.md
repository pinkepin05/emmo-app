# Catatan kebutuhan: pemantauan Finance per Company dan User

Tanggal pencatatan: 14 Oktober 2026.

Status: disimpan sementara sebagai catatan kebutuhan. Belum ada instruksi untuk memulai implementasi. Pembuatan dokumen ini hanya untuk menyimpan hasil pembicaraan; kode aplikasi tidak diubah untuk kebutuhan ini.

## Tujuan yang sudah disepakati

Finance memiliki satu tempat khusus untuk melihat transaksi dan Stok Kas seluruh user, dengan user dikelompokkan berdasarkan Company masing-masing. Finance dapat memilih Company mana pun, bukan hanya Company tempat akun Finance terdaftar.

Alur utama:

**Finance → Daftar Company → Company yang dipilih → Daftar User → User yang dipilih → Transaksi, rincian, dan Stok Kas.**

## Tampilan pada setiap tingkat

### 1. Daftar Company

- Menampilkan Company yang dapat dipilih oleh Finance.
- Finance memilih Company yang ingin diperiksa.

### 2. Company yang dipilih

- Menampilkan semua user yang berada di Company tersebut.
- Menampilkan rekap/ringkasan keuangan per Company, yaitu gabungan data user di Company tersebut:
  - Total uang masuk.
  - Total uang keluar.
  - Sisa saldo Stok Kas.
- Finance dapat memilih salah satu user untuk membuka rincian user tersebut.

### 3. User yang dipilih

- Menampilkan transaksi milik user tersebut, mencakup:
  - Operasional, beserta rinciannya.
  - Perjalanan Dinas, beserta rinciannya.
  - Event Dalam Kota, beserta rinciannya.
  - Event Luar Kota, beserta rinciannya.
- Menampilkan Stok Kas user tersebut:
  - Total uang masuk.
  - Total uang keluar.
  - Sisa saldo.

## Contoh penggunaan

Finance memilih Company B. Halaman menampilkan ringkasan Company B dan semua user di Company B. Finance kemudian memilih Andi. Halaman Andi menampilkan empat jenis transaksi di atas beserta rinciannya, serta total uang masuk, total uang keluar, dan sisa Stok Kas Andi.

Ringkasan Company B mencakup user di Company B. Tampilan Andi hanya mencakup data Andi, bukan gabungan seluruh user di Company B.

## Batas pembicaraan saat ini

- Fokus kebutuhan yang dikonfirmasi adalah melihat, memantau, dan menelusuri data. Hak mengedit, menghapus, mencairkan, atau menyetujui melalui halaman baru ini belum ditentukan.
- Aturan filter periode dan status untuk halaman baru belum diputuskan. Penjelasan sebelumnya tentang filter adalah penjelasan perilaku aplikasi yang ada, bukan keputusan bahwa halaman baru harus mengecualikan status tertentu.
- Dasar perhitungan Stok Kas, termasuk saldo awal, penyesuaian, serta perlakuan transaksi draft dan hasil pemeriksaan Finance, perlu dicocokkan dengan aturan aplikasi ketika implementasi diminta. Dokumen ini tidak menetapkan rumus baru.
- Nama menu, desain tampilan, dan rincian teknis belum ditentukan.
- Belum ada permintaan implementasi atau perubahan kode, database, maupun permission.

## Ringkasan untuk melanjutkan percakapan

Berikut ringkasan kebutuhan, bukan transkrip kata demi kata:

> Saya ingin Finance memiliki satu tempat untuk melihat semua Company. Setelah memilih Company, Finance melihat ringkasan Company dan seluruh user yang berada di Company tersebut. Setelah memilih user, Finance melihat transaksi Operasional, Perjalanan Dinas, Event Dalam Kota, dan Event Luar Kota beserta rinciannya, serta total uang masuk, total uang keluar, dan sisa Stok Kas user tersebut. Di tingkat Company juga tersedia ringkasan gabungan user di Company itu. Kebutuhan ini baru disimpan untuk sementara. Jangan mulai mengubah aplikasi sampai saya memberikan instruksi untuk melanjutkan implementasi.

## Catatan lanjutan: 15 Oktober 2026

Setelah halaman monitoring tersedia dan ditinjau, pengguna meminta penyimpanan kebutuhan perubahan navigasi menuju daftar transaksi serta detail asli tiap modul untuk user yang dipilih, dengan akses melihat seluruh status. Rincian dan naskah untuk melanjutkan percakapan tersimpan di [Transkrip perubahan alur Monitoring Finance](transkrip-perubahan-alur-monitoring-finance.md). Perubahan alur baru tersebut masih berupa catatan kebutuhan.
