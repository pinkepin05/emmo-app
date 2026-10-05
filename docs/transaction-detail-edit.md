# Detail dan edit transaksi OPR/PD

Halaman detail Operasional Sales dan Perjalanan Dinas menampilkan informasi transaksi serta rincian tersimpan. Form tambah rincian dan tombol hapus rincian langsung sudah dihapus dari mode lihat.

Tombol **Edit Transaksi** membuka mode edit pada halaman detail yang sama (`?edit=1`). Pengguna dapat mengubah tanggal, deskripsi, kategori/keterangan/nominal biaya, menambah rincian, atau menandai rincian untuk dihapus. Perjalanan Dinas juga mendukung perubahan territory, provinsi, kota tujuan, jarak, durasi, dan pemilik saldo BS yang diizinkan. Nomor dokumen dan identitas pencatat mengikuti data sistem.

**Simpan Perubahan** menyimpan informasi utama dan seluruh perubahan rincian dalam satu transaksi database. Penghapusan bisa dibatalkan sebelum menyimpan. **Batal** kembali ke detail tanpa menyimpan. Jika validasi gagal, mode edit beserta input, baris baru, dan tanda hapus tetap tersedia.

Edit tersedia untuk draft pada periode terbuka sesuai hak akses. Perubahan draft tidak membukukan penggunaan saldo BS; alur konfirmasi/persetujuan yang sudah ada tetap berlaku.

Form rincian digunakan bersama melalui `partials/transaction-edit-details.blade.php` dan `partials/transaction-edit-row.blade.php`. `public/js/transaction-editor.js` menangani baris dinamis, total, tandai/batalkan hapus, dan pilihan wilayah Perjalanan Dinas. Form menggunakan aturan mobile bersama.

Validasi:

- Empat tes baru (91 assertions) memeriksa mode lihat/edit, pembaruan header dan rincian sekaligus, pemulihan input gagal, serta status final/periode tertutup.
- Uji browser pada database terisolasi: 17 pemeriksaan interaksi berhasil, 12 tampilan pada lebar 320, 390, 844, dan 1366px tanpa overflow atau error JavaScript. Perubahan benar-benar disimpan pada dua draft khusus pengujian.
- Suite Laravel: 159 lulus, 1 kegagalan lama pada `test_rejection_time_stays_at_the_decision_even_after_document_updates`.

Artefak privat tersimpan di `storage/app/private/transaction-edit/`. Skrip browser `scripts/transaction-edit-browser-test.mjs` memerlukan fixture draft baru di direktori tersebut dan database terisolasi yang diverifikasi oleh `scripts/bs-verification-bootstrap.php`; jangan gunakan transaksi operasional untuk pengujian ini.
