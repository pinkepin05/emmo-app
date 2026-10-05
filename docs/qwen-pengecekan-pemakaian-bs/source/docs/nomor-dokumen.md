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
