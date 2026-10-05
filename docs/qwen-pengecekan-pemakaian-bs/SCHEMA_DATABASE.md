# Skema database lokal aktual

Waktu pembacaan (UTC): 2026-09-25T04:21:04+00:00. Driver: `mysql`.

Hanya metadata, tanpa isi transaksi/akun. Bukan migrasi fitur baru atau dump yang dapat langsung diimpor. Kolom/FK/index berasal dari database, bukan dugaan dari model.

Nama schema/database tujuan FK dikeluarkan; nama tabel dan kolom tetap disertakan.

## user

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| id | int(11) | Tidak | Ya | None |
| NIK | varchar(20) | Ya | Tidak | NULL |
| nama | varchar(255) | Tidak | Tidak | None |
| company_id | int(11) | Tidak | Tidak | None |
| roles_id | int(11) | Tidak | Tidak | None |
| level_id | int(11) | Tidak | Tidak | None |
| position_id | int(11) | Tidak | Tidak | None |
| homebase_id | int(11) | Tidak | Tidak | None |
| kost | int(11) | Ya | Tidak | NULL |
| bbm | int(11) | Ya | Tidak | NULL |
| uang_makan | int(11) | Ya | Tidak | NULL |
| hari_kerja_user | enum('5 HK','6 HK') | Ya | Tidak | NULL |
| atasan_user_id | int(11) | Ya | Tidak | NULL |
| username | varchar(20) | Tidak | Tidak | None |
| password | varchar(100) | Ya | Tidak | NULL |
| is_active | tinyint(1) | Ya | Tidak | 1 |

Indeks:

- `fk_user_company`: INDEX (company_id).
- `primary`: PRIMARY (id).
- `user_atasan_user_id(user_id)_foreign`: INDEX (atasan_user_id).
- `user_homebase_id(wilayah_id)_foreign`: INDEX (homebase_id).
- `user_level_id_foreign`: INDEX (level_id).
- `user_position_id_foreign`: INDEX (position_id).
- `user_roles_id_foreign`: INDEX (roles_id).

Foreign key:

- `fk_user_atasan`: (atasan_user_id) → `user` (id); update cascade, delete set null.
- `fk_user_company`: (company_id) → `company` (id); update cascade, delete restrict.
- `user_level_id_foreign`: (level_id) → `level` (id); update restrict, delete restrict.
- `user_position_id_foreign`: (position_id) → `position` (id); update restrict, delete restrict.
- `user_roles_id_foreign`: (roles_id) → `roles` (id); update restrict, delete restrict.

## roles

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| id | int(11) | Tidak | Ya | None |
| code | varchar(50) | Tidak | Tidak | None |
| nama | varchar(255) | Ya | Tidak | NULL |
| is_active | tinyint(1) | Ya | Tidak | 1 |

Indeks:

- `primary`: PRIMARY (id).
- `roles_code_unique`: UNIQUE (code).

Foreign key:

- Tidak ada FK fisik yang tercatat.

## company

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| id | int(11) | Tidak | Ya | None |
| code | varchar(50) | Tidak | Tidak | None |
| nama | varchar(255) | Ya | Tidak | NULL |
| is_active | tinyint(1) | Ya | Tidak | 1 |

Indeks:

- `company_code_unique`: UNIQUE (code).
- `primary`: PRIMARY (id).

Foreign key:

- Tidak ada FK fisik yang tercatat.

## level

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| id | int(11) | Tidak | Ya | None |
| code | varchar(50) | Tidak | Tidak | None |
| nama | varchar(255) | Ya | Tidak | NULL |
| is_active | tinyint(1) | Ya | Tidak | 1 |

Indeks:

- `level_code_unique`: UNIQUE (code).
- `primary`: PRIMARY (id).

Foreign key:

- Tidak ada FK fisik yang tercatat.

## position

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| id | int(11) | Tidak | Ya | None |
| code | varchar(50) | Ya | Tidak | NULL |
| nama | varchar(255) | Ya | Tidak | NULL |
| is_active | tinyint(1) | Ya | Tidak | 1 |

Indeks:

- `position_code_unique`: UNIQUE (code).
- `primary`: PRIMARY (id).

Foreign key:

- Tidak ada FK fisik yang tercatat.

## wilayah

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| id | int(11) | Tidak | Ya | None |
| code | varchar(50) | Ya | Tidak | NULL |
| nama | varchar(150) | Tidak | Tidak | None |
| parent_id | int(11) | Ya | Tidak | NULL |
| teritory | enum('Jawa','Luar Jawa') | Ya | Tidak | NULL |
| is_active | tinyint(1) | Ya | Tidak | 1 |

Indeks:

- `idx_code`: INDEX (code).
- `idx_nama`: INDEX (nama).
- `idx_parent_code`: INDEX (parent_id, code).
- `idx_parent_id`: INDEX (parent_id).
- `idx_teritory`: INDEX (teritory).
- `primary`: PRIMARY (id).
- `wilayah_code_unique`: UNIQUE (code).
- `wilayah_parent_id_foreign`: INDEX (parent_id).

Foreign key:

- `wilayah_parent_id_foreign`: (parent_id) → `wilayah` (id); update restrict, delete restrict.

## cost_category

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| id | int(11) | Tidak | Ya | None |
| nama | varchar(50) | Tidak | Tidak | None |
| description | varchar(255) | Ya | Tidak | NULL |
| is_active | tinyint(1) | Ya | Tidak | 1 |

Indeks:

- `cost_category_code_unique`: UNIQUE (nama).
- `primary`: PRIMARY (id).

Foreign key:

- Tidak ada FK fisik yang tercatat.

## periode

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| id | int(10) unsigned | Tidak | Ya | None |
| periode | varchar(7) | Tidak | Tidak | None |
| status | enum('OPEN','CLOSED') | Tidak | Tidak | 'OPEN' |
| closed_at | timestamp | Ya | Tidak | NULL |
| closed_by | int(10) unsigned | Ya | Tidak | NULL |
| reopened_at | timestamp | Ya | Tidak | NULL |
| reopen_reason | varchar(255) | Ya | Tidak | NULL |
| created_at | timestamp | Ya | Tidak | NULL |
| updated_at | timestamp | Ya | Tidak | NULL |

Indeks:

- `primary`: PRIMARY (id).
- `unique_periode`: UNIQUE (periode).

Foreign key:

- Tidak ada FK fisik yang tercatat.

## transaksi_operasional_area_sales_header

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| id | int(10) unsigned | Tidak | Ya | None |
| transaksi_id | varchar(32) | Tidak | Tidak | None |
| tanggal | date | Tidak | Tidak | None |
| user_id | int(11) | Tidak | Tidak | None |
| bs_id | int(10) unsigned | Ya | Tidak | NULL |
| periode | varchar(7) | Ya | Tidak | NULL |
| description | varchar(255) | Ya | Tidak | NULL |
| status | enum('DRAFT','VALIDATED') | Tidak | Tidak | 'DRAFT' |
| validated_at | timestamp | Ya | Tidak | NULL |
| validated_by | int(10) unsigned | Ya | Tidak | NULL |
| bs_owner_id | int(10) unsigned | Ya | Tidak | NULL |

Indeks:

- `fk_oas_bs`: INDEX (bs_id).
- `idx_oas_periode`: INDEX (periode).
- `oas_bs_owner_period_index`: INDEX (bs_owner_id, periode, status).
- `primary`: PRIMARY (id).
- `transaksi_operasional_area_sales_header_user_id_foreign`: INDEX (user_id).
- `unique_operasional_transaksi_id`: UNIQUE (transaksi_id).

Foreign key:

- `fk_transaksi_operasional_area_sales_header_bs_id`: (bs_id) → `pengajuan_bs` (id); update restrict, delete set null.
- `transaksi_operasional_area_sales_header_user_id_foreign`: (user_id) → `user` (id); update restrict, delete restrict.

## transaksi_operasional_area_sales_detail

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| id | int(10) unsigned | Tidak | Ya | None |
| transaksi_id | varchar(32) | Tidak | Tidak | None |
| cost_category_id | int(11) | Tidak | Tidak | None |
| remark | varchar(255) | Tidak | Tidak | None |
| nominal | int(11) | Tidak | Tidak | None |

Indeks:

- `fk_detail_operasional_header`: INDEX (transaksi_id).
- `primary`: PRIMARY (id).
- `transaksi_operasional_area_sales_detail_cost_category_id_foreign`: INDEX (cost_category_id).

Foreign key:

- `fk_detail_operasional_header`: (transaksi_id) → `transaksi_operasional_area_sales_header` (transaksi_id); update cascade, delete cascade.
- `transaksi_operasional_area_sales_detail_cost_category_id_foreign`: (cost_category_id) → `cost_category` (id); update restrict, delete restrict.

## transaksi_perjalanan_dinas_header

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| id | int(10) unsigned | Tidak | Ya | None |
| transaksi_id | varchar(32) | Tidak | Tidak | None |
| tanggal | date | Tidak | Tidak | None |
| user_id | int(11) | Tidak | Tidak | None |
| bs_id | int(10) unsigned | Ya | Tidak | NULL |
| periode | varchar(7) | Ya | Tidak | NULL |
| tujuan_dinas_id | int(11) | Tidak | Tidak | None |
| jarak_km | decimal(10,2) | Tidak | Tidak | 0.00 |
| durasi_jam | decimal(5,2) | Tidak | Tidak | 0.00 |
| description | varchar(255) | Tidak | Tidak | None |
| is_over_plafon | tinyint(1) | Tidak | Tidak | 0 |
| plafon_value | decimal(15,2) | Ya | Tidak | NULL |
| selisih_plafon | decimal(15,2) | Ya | Tidak | NULL |
| status | varchar(16) | Tidak | Tidak | 'VALIDATED' |
| validated_at | timestamp | Ya | Tidak | NULL |
| validated_by | int(10) unsigned | Ya | Tidak | NULL |
| bs_owner_id | int(10) unsigned | Ya | Tidak | NULL |
| approval_version | int(10) unsigned | Tidak | Tidak | 0 |
| submitted_at | timestamp | Ya | Tidak | NULL |
| submitted_by | int(10) unsigned | Ya | Tidak | NULL |
| owner_reviewed_at | timestamp | Ya | Tidak | NULL |
| owner_reviewed_by | int(10) unsigned | Ya | Tidak | NULL |
| owner_rejection_reason | text | Ya | Tidak | NULL |

Indeks:

- `fk_header_dinas_tujuan`: INDEX (tujuan_dinas_id).
- `fk_tpd_bs`: INDEX (bs_id).
- `idx_pd_periode`: INDEX (periode).
- `pd_bs_owner_period_index`: INDEX (bs_owner_id, periode, status).
- `primary`: PRIMARY (id).
- `transaksi_perjalanan_dinas_header_user_id_foreign`: INDEX (user_id).
- `unique_dinas_transaksi_id`: UNIQUE (transaksi_id).

Foreign key:

- `fk_tpd_bs`: (bs_id) → `pengajuan_bs` (id); update restrict, delete set null.
- `transaksi_perjalanan_dinas_header_user_id_foreign`: (user_id) → `user` (id); update restrict, delete restrict.

## transaksi_perjalanan_dinas_detail

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| id | int(10) unsigned | Tidak | Ya | None |
| transaksi_id | varchar(32) | Tidak | Tidak | None |
| cost_category_id | int(11) | Tidak | Tidak | None |
| remark | varchar(1000) | Ya | Tidak | NULL |
| nominal | int(10) unsigned | Tidak | Tidak | None |
| deleted_at | timestamp | Ya | Tidak | NULL |

Indeks:

- `fk_detail_dinas_cost`: INDEX (cost_category_id).
- `fk_detail_dinas_header`: INDEX (transaksi_id).
- `fk_nominal_ke_setting_id`: INDEX (nominal).
- `primary`: PRIMARY (id).

Foreign key:

- `fk_detail_dinas_cost`: (cost_category_id) → `cost_category` (id); update cascade, delete restrict.
- `fk_detail_dinas_header`: (transaksi_id) → `transaksi_perjalanan_dinas_header` (transaksi_id); update cascade, delete cascade.

## pengajuan_bs

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| id | int(10) unsigned | Tidak | Ya | None |
| document_no | varchar(50) | Tidak | Tidak | None |
| tanggal | date | Tidak | Tidak | None |
| user_id | int(11) | Tidak | Tidak | None |
| atasan_user_id | int(11) | Ya | Tidak | NULL |
| periode | varchar(7) | Tidak | Tidak | None |
| tipe_pengajuan | varchar(30) | Tidak | Tidak | 'operasional_sales' |
| nominal_sisa_bs_snapshot | decimal(15,2) | Tidak | Tidak | 0.00 |
| nominal_pengajuan | decimal(15,2) | Tidak | Tidak | None |
| nominal_approved | decimal(15,2) | Tidak | Tidak | 0.00 |
| nominal_pengembalian | decimal(15,2) | Tidak | Tidak | 0.00 |
| nominal_reimburse | decimal(15,2) | Tidak | Tidak | 0.00 |
| settled_at | timestamp | Ya | Tidak | NULL |
| settled_periode | varchar(7) | Ya | Tidak | NULL |
| settled_by | int(11) | Ya | Tidak | NULL |
| description | text | Ya | Tidak | NULL |
| rejection_reason | text | Ya | Tidak | NULL |
| approval_message | text | Ya | Tidak | NULL |
| status | enum('PENDING_ATASAN','APPROVED_ATASAN','REJECTED_ATASAN','REJECTED_FINANCE','DISBURSED','SETTLED') | Ya | Tidak | 'PENDING_ATASAN' |
| created_at | timestamp | Ya | Tidak | NULL |
| updated_at | timestamp | Ya | Tidak | NULL |

Indeks:

- `fk_bs_atasan`: INDEX (atasan_user_id).
- `fk_bs_user`: INDEX (user_id).
- `primary`: PRIMARY (id).
- `unique_bs_document_no`: UNIQUE (document_no).

Foreign key:

- `fk_bs_atasan`: (atasan_user_id) → `user` (id); update restrict, delete set null.
- `fk_bs_user`: (user_id) → `user` (id); update restrict, delete cascade.

## pengajuan_bs_members

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| id | int(10) unsigned | Tidak | Ya | None |
| pengajuan_bs_id | int(10) unsigned | Tidak | Tidak | None |
| user_id | int(11) | Tidak | Tidak | None |

Indeks:

- `fk_bs_member_header`: INDEX (pengajuan_bs_id).
- `fk_bs_member_user`: INDEX (user_id).
- `primary`: PRIMARY (id).

Foreign key:

- `fk_bs_member_header`: (pengajuan_bs_id) → `pengajuan_bs` (id); update restrict, delete cascade.
- `fk_bs_member_user`: (user_id) → `user` (id); update restrict, delete cascade.

## approval_bs_finance

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| id | int(10) unsigned | Tidak | Ya | None |
| document_no | varchar(50) | Tidak | Tidak | None |
| pengajuan_bs_id | int(10) unsigned | Tidak | Tidak | None |
| tanggal_cair | date | Tidak | Tidak | None |
| finance_user_id | int(11) | Tidak | Tidak | None |
| nominal_pengajuan_bs | decimal(15,2) | Tidak | Tidak | None |
| nominal_sisa_bs | decimal(15,2) | Tidak | Tidak | None |
| nominal_approved | decimal(15,2) | Tidak | Tidak | None |
| periode | varchar(7) | Tidak | Tidak | None |
| description | text | Ya | Tidak | NULL |
| created_at | timestamp | Ya | Tidak | NULL |
| updated_at | timestamp | Ya | Tidak | NULL |

Indeks:

- `fk_finance_bs`: INDEX (pengajuan_bs_id).
- `fk_finance_user`: INDEX (finance_user_id).
- `primary`: PRIMARY (id).
- `unique_finance_document_no`: UNIQUE (document_no).

Foreign key:

- `fk_finance_bs`: (pengajuan_bs_id) → `pengajuan_bs` (id); update restrict, delete cascade.
- `fk_finance_user`: (finance_user_id) → `user` (id); update restrict, delete cascade.

## document_number_sequences

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| type | varchar(8) | Tidak | Tidak | None |
| date | date | Tidak | Tidak | None |
| last_number | bigint(20) unsigned | Tidak | Tidak | 0 |

Indeks:

- `primary`: PRIMARY (type, date).

Foreign key:

- Tidak ada FK fisik yang tercatat.

## bs_usage_logs

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| id | bigint(20) unsigned | Tidak | Ya | None |
| transaction_type | varchar(32) | Tidak | Tidak | None |
| transaction_id | int(10) unsigned | Tidak | Tidak | None |
| document_no | varchar(64) | Tidak | Tidak | None |
| bs_owner_id | int(10) unsigned | Tidak | Tidak | None |
| inputter_id | int(10) unsigned | Tidak | Tidak | None |
| actor_id | int(10) unsigned | Tidak | Tidak | None |
| periode | varchar(7) | Tidak | Tidak | None |
| nominal | decimal(15,2) | Tidak | Tidak | None |
| created_at | timestamp | Tidak | Tidak | current_timestamp() |

Indeks:

- `bs_usage_logs_bs_owner_id_index`: INDEX (bs_owner_id).
- `bs_usage_logs_transaction_type_transaction_id_unique`: UNIQUE (transaction_type, transaction_id).
- `primary`: PRIMARY (id).

Foreign key:

- Tidak ada FK fisik yang tercatat.

## bs_usage_approval_logs

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| id | bigint(20) unsigned | Tidak | Ya | None |
| transaction_id | int(10) unsigned | Tidak | Tidak | None |
| document_no | varchar(64) | Tidak | Tidak | None |
| approval_version | int(10) unsigned | Tidak | Tidak | None |
| bs_owner_id | int(10) unsigned | Tidak | Tidak | None |
| inputter_id | int(10) unsigned | Tidak | Tidak | None |
| actor_id | int(10) unsigned | Tidak | Tidak | None |
| action | varchar(16) | Tidak | Tidak | None |
| nominal | decimal(15,2) | Tidak | Tidak | None |
| reason | text | Ya | Tidak | NULL |
| created_at | timestamp | Tidak | Tidak | current_timestamp() |

Indeks:

- `bs_usage_approval_logs_transaction_id_index`: INDEX (transaction_id).
- `bs_usage_review_action_unique`: UNIQUE (transaction_id, approval_version, action).
- `primary`: PRIMARY (id).

Foreign key:

- Tidak ada FK fisik yang tercatat.

## bs_activity_logs

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| id | int(10) unsigned | Tidak | Ya | None |
| pengajuan_bs_id | int(10) unsigned | Tidak | Tidak | None |
| activity_code | varchar(50) | Tidak | Tidak | None |
| activity_name | varchar(100) | Tidak | Tidak | None |
| actor_user_id | int(11) | Ya | Tidak | NULL |
| description | text | Ya | Tidak | NULL |
| nominal | decimal(15,2) | Ya | Tidak | NULL |
| created_at | timestamp | Ya | Tidak | current_timestamp() |
| updated_at | timestamp | Ya | Tidak | current_timestamp() |

Indeks:

- `fk_log_bs`: INDEX (pengajuan_bs_id).
- `idx_actor`: INDEX (actor_user_id).
- `primary`: PRIMARY (id).

Foreign key:

- `fk_log_bs`: (pengajuan_bs_id) → `pengajuan_bs` (id); update restrict, delete cascade.

## bs_cash_flow_snapshots

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| id | bigint(20) unsigned | Tidak | Ya | None |
| user_id | int(10) unsigned | Tidak | Tidak | None |
| periode | varchar(7) | Tidak | Tidak | None |
| saldo_awal | decimal(15,2) | Tidak | Tidak | 0.00 |
| total_masuk | decimal(15,2) | Tidak | Tidak | 0.00 |
| total_keluar | decimal(15,2) | Tidak | Tidak | 0.00 |
| penyesuaian | decimal(15,2) | Tidak | Tidak | 0.00 |
| saldo_akhir | decimal(15,2) | Ya | Tidak | NULL |
| opening_final | tinyint(1) | Tidak | Tidak | 0 |
| last_updated | timestamp | Ya | Tidak | NULL |

Indeks:

- `bs_cash_flow_snapshots_user_id_periode_unique`: UNIQUE (user_id, periode).
- `primary`: PRIMARY (id).

Foreign key:

- Tidak ada FK fisik yang tercatat.

## stock_cash_flow_snapshots

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| id | bigint(20) unsigned | Tidak | Ya | None |
| user_id | int(10) unsigned | Tidak | Tidak | None |
| periode | varchar(7) | Tidak | Tidak | None |
| saldo_awal | decimal(15,2) | Tidak | Tidak | 0.00 |
| total_masuk | decimal(15,2) | Tidak | Tidak | 0.00 |
| total_keluar_bs | decimal(15,2) | Tidak | Tidak | 0.00 |
| total_event_dalam | decimal(15,2) | Tidak | Tidak | 0.00 |
| total_event_luar | decimal(15,2) | Tidak | Tidak | 0.00 |
| total_keluar | decimal(15,2) | Tidak | Tidak | 0.00 |
| penyesuaian | decimal(15,2) | Tidak | Tidak | 0.00 |
| saldo_akhir | decimal(15,2) | Ya | Tidak | NULL |
| opening_final | tinyint(1) | Tidak | Tidak | 0 |
| last_updated | timestamp | Ya | Tidak | NULL |
| total_keluar_pribadi | decimal(15,2) | Tidak | Tidak | 0.00 |
| total_masuk_reimbursement | decimal(15,2) | Tidak | Tidak | 0.00 |
| includes_personal_funds | tinyint(1) | Tidak | Tidak | 0 |

Indeks:

- `primary`: PRIMARY (id).
- `stock_cash_flow_snapshots_user_id_periode_unique`: UNIQUE (user_id, periode).

Foreign key:

- Tidak ada FK fisik yang tercatat.

## cash_flow_snapshots

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| id | bigint(20) unsigned | Tidak | Ya | None |
| user_id | int(10) unsigned | Tidak | Tidak | None |
| periode | varchar(7) | Tidak | Tidak | None |
| saldo_awal | decimal(15,2) | Tidak | Tidak | 0.00 |
| total_masuk | decimal(15,2) | Tidak | Tidak | 0.00 |
| total_keluar | decimal(15,2) | Tidak | Tidak | 0.00 |
| saldo_akhir | decimal(15,2) | Ya | Tidak | NULL |
| last_updated | timestamp | Tidak | Tidak | current_timestamp() |

Indeks:

- `cash_flow_snapshots_user_id_periode_unique`: UNIQUE (user_id, periode).
- `primary`: PRIMARY (id).

Foreign key:

- Tidak ada FK fisik yang tercatat.

## reimbursement_claims

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| id | bigint(20) unsigned | Tidak | Ya | None |
| transaction_type | varchar(32) | Tidak | Tidak | None |
| transaction_id | int(10) unsigned | Tidak | Tidak | None |
| document_no | varchar(64) | Tidak | Tidak | None |
| user_id | int(10) unsigned | Tidak | Tidak | None |
| expense_date | date | Tidak | Tidak | None |
| description | varchar(255) | Ya | Tidak | NULL |
| amount | decimal(15,2) | Tidak | Tidak | None |
| items | longtext | Tidak | Tidak | None |
| status | varchar(24) | Tidak | Tidak | 'PENDING_FINANCE' |
| submitted_by | int(10) unsigned | Tidak | Tidak | None |
| submitted_at | timestamp | Tidak | Tidak | current_timestamp() |
| approved_by | int(10) unsigned | Ya | Tidak | NULL |
| approved_at | timestamp | Ya | Tidak | NULL |
| rejected_by | int(10) unsigned | Ya | Tidak | NULL |
| rejected_at | timestamp | Ya | Tidak | NULL |
| rejection_reason | text | Ya | Tidak | NULL |
| paid_by | int(10) unsigned | Ya | Tidak | NULL |
| paid_at | timestamp | Ya | Tidak | NULL |
| payment_date | date | Ya | Tidak | NULL |
| payment_reference | varchar(150) | Ya | Tidak | NULL |
| created_at | timestamp | Ya | Tidak | NULL |
| updated_at | timestamp | Ya | Tidak | NULL |
| payment_proofs | longtext | Ya | Tidak | NULL |

Indeks:

- `primary`: PRIMARY (id).
- `reimbursement_claims_status_index`: INDEX (status).
- `reimbursement_claims_transaction_type_transaction_id_unique`: UNIQUE (transaction_type, transaction_id).
- `reimbursement_claims_user_id_index`: INDEX (user_id).

Foreign key:

- Tidak ada FK fisik yang tercatat.

## spatie_permissions

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| id | bigint(20) unsigned | Tidak | Ya | None |
| name | varchar(255) | Tidak | Tidak | None |
| guard_name | varchar(255) | Tidak | Tidak | None |
| created_at | timestamp | Ya | Tidak | NULL |
| updated_at | timestamp | Ya | Tidak | NULL |

Indeks:

- `primary`: PRIMARY (id).
- `spatie_permissions_name_guard_name_unique`: UNIQUE (name, guard_name).

Foreign key:

- Tidak ada FK fisik yang tercatat.

## spatie_roles

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| id | bigint(20) unsigned | Tidak | Ya | None |
| name | varchar(255) | Tidak | Tidak | None |
| guard_name | varchar(255) | Tidak | Tidak | None |
| created_at | timestamp | Ya | Tidak | NULL |
| updated_at | timestamp | Ya | Tidak | NULL |

Indeks:

- `primary`: PRIMARY (id).
- `spatie_roles_name_guard_name_unique`: UNIQUE (name, guard_name).

Foreign key:

- Tidak ada FK fisik yang tercatat.

## spatie_role_has_permissions

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| permission_id | bigint(20) unsigned | Tidak | Tidak | None |
| role_id | bigint(20) unsigned | Tidak | Tidak | None |

Indeks:

- `primary`: PRIMARY (permission_id, role_id).
- `spatie_role_has_permissions_role_id_foreign`: INDEX (role_id).

Foreign key:

- `spatie_role_has_permissions_permission_id_foreign`: (permission_id) → `spatie_permissions` (id); update restrict, delete cascade.
- `spatie_role_has_permissions_role_id_foreign`: (role_id) → `spatie_roles` (id); update restrict, delete cascade.

## spatie_model_has_permissions

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| permission_id | bigint(20) unsigned | Tidak | Tidak | None |
| model_type | varchar(255) | Tidak | Tidak | None |
| model_id | bigint(20) unsigned | Tidak | Tidak | None |

Indeks:

- `model_has_permissions_model_id_model_type_index`: INDEX (model_id, model_type).
- `primary`: PRIMARY (permission_id, model_id, model_type).

Foreign key:

- `spatie_model_has_permissions_permission_id_foreign`: (permission_id) → `spatie_permissions` (id); update restrict, delete cascade.

## spatie_model_has_roles

| Kolom | Tipe aktual | Nullable | Auto increment | Default |
| --- | --- | --- | --- | --- |
| role_id | bigint(20) unsigned | Tidak | Tidak | None |
| model_type | varchar(255) | Tidak | Tidak | None |
| model_id | bigint(20) unsigned | Tidak | Tidak | None |

Indeks:

- `model_has_roles_model_id_model_type_index`: INDEX (model_id, model_type).
- `primary`: PRIMARY (role_id, model_id, model_type).

Foreign key:

- `spatie_model_has_roles_role_id_foreign`: (role_id) → `spatie_roles` (id); update restrict, delete cascade.
