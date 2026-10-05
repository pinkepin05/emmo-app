# Indeks kode lengkap untuk Qwen

253 file sumber, 1,954,400 byte, dibagi menjadi 15 bagian Markdown.

Mulai dengan dokumen 01–05, SCHEMA_DATABASE.md dan kelompok 01 (inti). Lanjutkan kelompok 02–06 untuk implementasi, 07 untuk tes, 08 untuk sejarah aturan bisnis. Kelompok 09 menyediakan dependensi kode pendukung agar konteks tidak terputus.

Setiap file disertakan utuh dan hanya muncul sekali dalam bagian kode. File besar dapat membuat satu bagian melebihi target 180 KB; jangan memotong isinya tanpa memberi Qwen file lengkap. Upload bertahap bila kapasitas konteks terbatas.

## [01-inti-model-route-penomoran-izin-01.md](kode-untuk-chat/01-inti-model-route-penomoran-izin-01.md)

Ukuran UTF-8: 58,226 byte. Isi:

- [`app/Models/ApprovalBsFinance.php`](source/app/Models/ApprovalBsFinance.php)
- [`app/Models/Concerns/UsesBsBalance.php`](source/app/Models/Concerns/UsesBsBalance.php)
- [`app/Models/CostCategory.php`](source/app/Models/CostCategory.php)
- [`app/Models/PengajuanBs.php`](source/app/Models/PengajuanBs.php)
- [`app/Models/Periode.php`](source/app/Models/Periode.php)
- [`app/Models/Role.php`](source/app/Models/Role.php)
- [`app/Models/TransaksiOperasionalDetail.php`](source/app/Models/TransaksiOperasionalDetail.php)
- [`app/Models/TransaksiOperasionalHeader.php`](source/app/Models/TransaksiOperasionalHeader.php)
- [`app/Models/TransaksiPerjalananDinasDetail.php`](source/app/Models/TransaksiPerjalananDinasDetail.php)
- [`app/Models/TransaksiPerjalananDinasHeader.php`](source/app/Models/TransaksiPerjalananDinasHeader.php)
- [`app/Models/User.php`](source/app/Models/User.php)
- [`app/Providers/AppServiceProvider.php`](source/app/Providers/AppServiceProvider.php)
- [`app/Services/DocumentNumberService.php`](source/app/Services/DocumentNumberService.php)
- [`app/Services/PeriodService.php`](source/app/Services/PeriodService.php)
- [`app/Support/AppTime.php`](source/app/Support/AppTime.php)
- [`app/Support/Money.php`](source/app/Support/Money.php)
- [`bootstrap/app.php`](source/bootstrap/app.php)
- [`bootstrap/providers.php`](source/bootstrap/providers.php)
- [`composer.json`](source/composer.json)
- [`config/bs.php`](source/config/bs.php)
- [`config/permission.php`](source/config/permission.php)
- [`database/seeders/PermissionSeeder.php`](source/database/seeders/PermissionSeeder.php)
- [`phpunit.xml`](source/phpunit.xml)
- [`routes/web.php`](source/routes/web.php)

## [02-controller-transaksi-finance-laporan-01.md](kode-untuk-chat/02-controller-transaksi-finance-laporan-01.md)

Ukuran UTF-8: 64,493 byte. Isi:

- [`app/Http/Controllers/AccessManagementController.php`](source/app/Http/Controllers/AccessManagementController.php)
- [`app/Http/Controllers/ApprovalFinanceController.php`](source/app/Http/Controllers/ApprovalFinanceController.php)
- [`app/Http/Controllers/BsBalanceController.php`](source/app/Http/Controllers/BsBalanceController.php)
- [`app/Http/Controllers/BsUsageApprovalController.php`](source/app/Http/Controllers/BsUsageApprovalController.php)
- [`app/Http/Controllers/LaporanBsController.php`](source/app/Http/Controllers/LaporanBsController.php)
- [`app/Http/Controllers/TransaksiOperasionalController.php`](source/app/Http/Controllers/TransaksiOperasionalController.php)
- [`app/Http/Controllers/TransaksiPerjalananDinasController.php`](source/app/Http/Controllers/TransaksiPerjalananDinasController.php)

## [03-layanan-saldo-middleware-01.md](kode-untuk-chat/03-layanan-saldo-middleware-01.md)

Ukuran UTF-8: 58,275 byte. Isi:

- [`app/Events/CashFlowChanged.php`](source/app/Events/CashFlowChanged.php)
- [`app/Http/Middleware/AtomicBsRequest.php`](source/app/Http/Middleware/AtomicBsRequest.php)
- [`app/Http/Middleware/AtomicEventRequest.php`](source/app/Http/Middleware/AtomicEventRequest.php)
- [`app/Http/Middleware/CheckActiveAtasan.php`](source/app/Http/Middleware/CheckActiveAtasan.php)
- [`app/Http/Middleware/CheckActiveCompany.php`](source/app/Http/Middleware/CheckActiveCompany.php)
- [`app/Http/Middleware/InjectNotificationBell.php`](source/app/Http/Middleware/InjectNotificationBell.php)
- [`app/Listeners/UpdateCashFlowSnapshot.php`](source/app/Listeners/UpdateCashFlowSnapshot.php)
- [`app/Observers/RoleObserver.php`](source/app/Observers/RoleObserver.php)
- [`app/Observers/UserObserver.php`](source/app/Observers/UserObserver.php)
- [`app/Services/BonSementaraService.php`](source/app/Services/BonSementaraService.php)
- [`app/Services/BsCashFlowService.php`](source/app/Services/BsCashFlowService.php)
- [`app/Services/BsNotifications.php`](source/app/Services/BsNotifications.php)
- [`app/Services/BsPeriodClosingService.php`](source/app/Services/BsPeriodClosingService.php)
- [`app/Services/BsValidationService.php`](source/app/Services/BsValidationService.php)
- [`app/Services/CashFlowService.php`](source/app/Services/CashFlowService.php)
- [`app/Services/PlafonService.php`](source/app/Services/PlafonService.php)
- [`app/Services/StockCashFlowService.php`](source/app/Services/StockCashFlowService.php)
- [`app/Services/TransactionFundingService.php`](source/app/Services/TransactionFundingService.php)

## [04-tampilan-transaksi-dan-finance-01.md](kode-untuk-chat/04-tampilan-transaksi-dan-finance-01.md)

Ukuran UTF-8: 157,079 byte. Isi:

- [`resources/views/approval-finance/detail.blade.php`](source/resources/views/approval-finance/detail.blade.php)
- [`resources/views/approval-finance/index.blade.php`](source/resources/views/approval-finance/index.blade.php)
- [`resources/views/bs-usage-approvals/actions.blade.php`](source/resources/views/bs-usage-approvals/actions.blade.php)
- [`resources/views/bs-usage-approvals/history.blade.php`](source/resources/views/bs-usage-approvals/history.blade.php)
- [`resources/views/bs-usage-approvals/index.blade.php`](source/resources/views/bs-usage-approvals/index.blade.php)
- [`resources/views/bs-usage-approvals/show.blade.php`](source/resources/views/bs-usage-approvals/show.blade.php)
- [`resources/views/transaksi_operasional/create.blade.php`](source/resources/views/transaksi_operasional/create.blade.php)
- [`resources/views/transaksi_operasional/index.blade.php`](source/resources/views/transaksi_operasional/index.blade.php)
- [`resources/views/transaksi_operasional/show.blade.php`](source/resources/views/transaksi_operasional/show.blade.php)

## [04-tampilan-transaksi-dan-finance-02.md](kode-untuk-chat/04-tampilan-transaksi-dan-finance-02.md)

Ukuran UTF-8: 95,046 byte. Isi:

- [`resources/views/transaksi_perjalanan_dinas/create.blade.php`](source/resources/views/transaksi_perjalanan_dinas/create.blade.php)
- [`resources/views/transaksi_perjalanan_dinas/index.blade.php`](source/resources/views/transaksi_perjalanan_dinas/index.blade.php)
- [`resources/views/transaksi_perjalanan_dinas/show.blade.php`](source/resources/views/transaksi_perjalanan_dinas/show.blade.php)

## [05-layout-menu-laporan-aset-01.md](kode-untuk-chat/05-layout-menu-laporan-aset-01.md)

Ukuran UTF-8: 135,354 byte. Isi:

- [`public/js/application-time.js`](source/public/js/application-time.js)
- [`public/js/bs-balance.js`](source/public/js/bs-balance.js)
- [`public/js/sidebar.js`](source/public/js/sidebar.js)
- [`resources/views/admin/access-management.blade.php`](source/resources/views/admin/access-management.blade.php)
- [`resources/views/bs-balance/show.blade.php`](source/resources/views/bs-balance/show.blade.php)
- [`resources/views/components/sidebar-module.blade.php`](source/resources/views/components/sidebar-module.blade.php)
- [`resources/views/components/sidebar.blade.php`](source/resources/views/components/sidebar.blade.php)
- [`resources/views/dashboard/master_data.blade.php`](source/resources/views/dashboard/master_data.blade.php)
- [`resources/views/dashboard/portal.blade.php`](source/resources/views/dashboard/portal.blade.php)
- [`resources/views/dashboard/transaksi.blade.php`](source/resources/views/dashboard/transaksi.blade.php)
- [`resources/views/laporan-bs/index.blade.php`](source/resources/views/laporan-bs/index.blade.php)
- [`resources/views/layouts/app-with-sidebar.blade.php`](source/resources/views/layouts/app-with-sidebar.blade.php)
- [`resources/views/partials/application-time.blade.php`](source/resources/views/partials/application-time.blade.php)
- [`resources/views/partials/bs-approval-link.blade.php`](source/resources/views/partials/bs-approval-link.blade.php)
- [`resources/views/partials/notification-bell.blade.php`](source/resources/views/partials/notification-bell.blade.php)
- [`resources/views/partials/transaction-funding.blade.php`](source/resources/views/partials/transaction-funding.blade.php)
- [`resources/views/partials/travel-confirmation.blade.php`](source/resources/views/partials/travel-confirmation.blade.php)

## [06-migrasi-seeder-factory-01.md](kode-untuk-chat/06-migrasi-seeder-factory-01.md)

Ukuran UTF-8: 45,487 byte. Isi:

- [`database/factories/UserFactory.php`](source/database/factories/UserFactory.php)
- [`database/migrations/0001_01_01_000000_create_users_table.php`](source/database/migrations/0001_01_01_000000_create_users_table.php)
- [`database/migrations/0001_01_01_000001_create_cache_table.php`](source/database/migrations/0001_01_01_000001_create_cache_table.php)
- [`database/migrations/0001_01_01_000002_create_jobs_table.php`](source/database/migrations/0001_01_01_000002_create_jobs_table.php)
- [`database/migrations/2026_06_17_050908_create_personal_access_tokens_table.php`](source/database/migrations/2026_06_17_050908_create_personal_access_tokens_table.php)
- [`database/migrations/2026_07_07_065552_add_jarak_durasi_to_perongkosan_event_luar_kota_table.php`](source/database/migrations/2026_07_07_065552_add_jarak_durasi_to_perongkosan_event_luar_kota_table.php)
- [`database/migrations/2026_07_07_095857_add_jarak_durasi_to_transaksi_perjalanan_dinas_header_table.php`](source/database/migrations/2026_07_07_095857_add_jarak_durasi_to_transaksi_perjalanan_dinas_header_table.php)
- [`database/migrations/2026_07_08_014711_create_wilayah_distances_table.php`](source/database/migrations/2026_07_08_014711_create_wilayah_distances_table.php)
- [`database/migrations/2026_07_16_075422_create_permission_tables.php`](source/database/migrations/2026_07_16_075422_create_permission_tables.php)
- [`database/migrations/2026_08_26_031245_add_audit_columns_to_periode_table.php`](source/database/migrations/2026_08_26_031245_add_audit_columns_to_periode_table.php)
- [`database/migrations/2026_08_27_010727_fix_bs_id_nullable_in_operasional_header.php`](source/database/migrations/2026_08_27_010727_fix_bs_id_nullable_in_operasional_header.php)
- [`database/migrations/2026_08_27_085533_add_soft_deletes_to_detail_tables.php`](source/database/migrations/2026_08_27_085533_add_soft_deletes_to_detail_tables.php)
- [`database/migrations/2026_08_27_085556_create_audit_logs_table.php`](source/database/migrations/2026_08_27_085556_create_audit_logs_table.php)
- [`database/migrations/2026_09_04_065700_create_cash_flow_snapshots_table.php`](source/database/migrations/2026_09_04_065700_create_cash_flow_snapshots_table.php)
- [`database/migrations/2026_09_08_015951_add_tipe_pengajuan_to_pengajuan_bs_table.php`](source/database/migrations/2026_09_08_015951_add_tipe_pengajuan_to_pengajuan_bs_table.php)
- [`database/migrations/2026_09_08_051501_add_status_to_transaksi_operasional_header.php`](source/database/migrations/2026_09_08_051501_add_status_to_transaksi_operasional_header.php)
- [`database/migrations/2026_09_10_044520_add_rejected_finance_to_pengajuan_bs_status_enum.php`](source/database/migrations/2026_09_10_044520_add_rejected_finance_to_pengajuan_bs_status_enum.php)
- [`database/migrations/2026_09_16_100000_add_bs_cash_flow_and_travel_validation.php`](source/database/migrations/2026_09_16_100000_add_bs_cash_flow_and_travel_validation.php)
- [`database/migrations/2026_09_17_100000_restore_readable_document_numbers.php`](source/database/migrations/2026_09_17_100000_restore_readable_document_numbers.php)
- [`database/migrations/2026_09_18_100000_defer_closing_balances_until_period_close.php`](source/database/migrations/2026_09_18_100000_defer_closing_balances_until_period_close.php)
- [`database/migrations/2026_09_18_110000_create_stock_cash_flow_snapshots_table.php`](source/database/migrations/2026_09_18_110000_create_stock_cash_flow_snapshots_table.php)
- [`database/migrations/2026_09_21_100000_create_reimbursement_claims.php`](source/database/migrations/2026_09_21_100000_create_reimbursement_claims.php)
- [`database/migrations/2026_09_23_100000_add_payment_proofs_to_reimbursement_claims.php`](source/database/migrations/2026_09_23_100000_add_payment_proofs_to_reimbursement_claims.php)
- [`database/migrations/2026_09_23_110000_add_personal_funds_to_stock_cash_flow.php`](source/database/migrations/2026_09_23_110000_add_personal_funds_to_stock_cash_flow.php)
- [`database/migrations/2026_09_24_140000_add_pooled_bs_funding.php`](source/database/migrations/2026_09_24_140000_add_pooled_bs_funding.php)
- [`database/migrations/2026_09_24_160000_add_bs_owner_approval.php`](source/database/migrations/2026_09_24_160000_add_bs_owner_approval.php)
- [`database/seeders/BsPermissionSeeder.php`](source/database/seeders/BsPermissionSeeder.php)
- [`database/seeders/DatabaseSeeder.php`](source/database/seeders/DatabaseSeeder.php)
- [`database/seeders/WilayahSeeder.php`](source/database/seeders/WilayahSeeder.php)

## [07-pengujian-existing-01.md](kode-untuk-chat/07-pengujian-existing-01.md)

Ukuran UTF-8: 162,841 byte. Isi:

- [`tests/Feature/ApplicationTimeTest.php`](source/tests/Feature/ApplicationTimeTest.php)
- [`tests/Feature/BsCashFlowTest.php`](source/tests/Feature/BsCashFlowTest.php)
- [`tests/Feature/DashboardAccessTest.php`](source/tests/Feature/DashboardAccessTest.php)
- [`tests/Feature/DocumentNumberTest.php`](source/tests/Feature/DocumentNumberTest.php)
- [`tests/Feature/ExampleTest.php`](source/tests/Feature/ExampleTest.php)
- [`tests/Feature/MasterDataPagesTest.php`](source/tests/Feature/MasterDataPagesTest.php)
- [`tests/Feature/ProfilePageTest.php`](source/tests/Feature/ProfilePageTest.php)
- [`tests/TestCase.php`](source/tests/TestCase.php)
- [`tests/Unit/ExampleTest.php`](source/tests/Unit/ExampleTest.php)

## [08-dokumentasi-existing-01.md](kode-untuk-chat/08-dokumentasi-existing-01.md)

Ukuran UTF-8: 70,911 byte. Isi:

- [`docs/audit-kesesuaian-rev4.md`](source/docs/audit-kesesuaian-rev4.md)
- [`docs/evaluasi-stok-kas.md`](source/docs/evaluasi-stok-kas.md)
- [`docs/implementasi-bs.md`](source/docs/implementasi-bs.md)
- [`docs/nomor-dokumen.md`](source/docs/nomor-dokumen.md)
- [`docs/reimbursement-pribadi.md`](source/docs/reimbursement-pribadi.md)
- [`docs/saldo-bs-gabungan.md`](source/docs/saldo-bs-gabungan.md)
- [`docs/waktu-aplikasi.md`](source/docs/waktu-aplikasi.md)

## [09-kode-pendukung-lain-01.md](kode-untuk-chat/09-kode-pendukung-lain-01.md)

Ukuran UTF-8: 139,133 byte. Isi:

- [`.editorconfig`](source/.editorconfig)
- [`.env.example`](source/.env.example)
- [`README.md`](source/README.md)
- [`app/Console/Commands/RecalculateCashFlow.php`](source/app/Console/Commands/RecalculateCashFlow.php)
- [`app/Console/Commands/RepairDocumentNumbers.php`](source/app/Console/Commands/RepairDocumentNumbers.php)
- [`app/Exports/CashFlowExport.php`](source/app/Exports/CashFlowExport.php)
- [`app/Exports/CashFlowSheetExport.php`](source/app/Exports/CashFlowSheetExport.php)
- [`app/Exports/LaporanTableExport.php`](source/app/Exports/LaporanTableExport.php)
- [`app/Http/Controllers/ApprovalAtasanController.php`](source/app/Http/Controllers/ApprovalAtasanController.php)
- [`app/Http/Controllers/AuthController.php`](source/app/Http/Controllers/AuthController.php)
- [`app/Http/Controllers/CashFlowController.php`](source/app/Http/Controllers/CashFlowController.php)
- [`app/Http/Controllers/CompanyController.php`](source/app/Http/Controllers/CompanyController.php)
- [`app/Http/Controllers/Controller.php`](source/app/Http/Controllers/Controller.php)
- [`app/Http/Controllers/CostCategoryController.php`](source/app/Http/Controllers/CostCategoryController.php)
- [`app/Http/Controllers/DashboardMasterController.php`](source/app/Http/Controllers/DashboardMasterController.php)
- [`app/Http/Controllers/EventHourGroupController.php`](source/app/Http/Controllers/EventHourGroupController.php)
- [`app/Http/Controllers/LevelController.php`](source/app/Http/Controllers/LevelController.php)
- [`app/Http/Controllers/NotificationController.php`](source/app/Http/Controllers/NotificationController.php)
- [`app/Http/Controllers/PengajuanBsController.php`](source/app/Http/Controllers/PengajuanBsController.php)
- [`app/Http/Controllers/PeriodeController.php`](source/app/Http/Controllers/PeriodeController.php)
- [`app/Http/Controllers/PerongkosanEventDalamKotaController.php`](source/app/Http/Controllers/PerongkosanEventDalamKotaController.php)
- [`app/Http/Controllers/PerongkosanEventLuarKotaController.php`](source/app/Http/Controllers/PerongkosanEventLuarKotaController.php)
- [`app/Http/Controllers/PlafonEventDalamKotaController.php`](source/app/Http/Controllers/PlafonEventDalamKotaController.php)
- [`app/Http/Controllers/PlafonEventLuarKotaController.php`](source/app/Http/Controllers/PlafonEventLuarKotaController.php)
- [`app/Http/Controllers/PositionController.php`](source/app/Http/Controllers/PositionController.php)
- [`app/Http/Controllers/ProfileController.php`](source/app/Http/Controllers/ProfileController.php)
- [`app/Http/Controllers/RoleController.php`](source/app/Http/Controllers/RoleController.php)
- [`app/Http/Controllers/SettingPlafonPerjalananDinasController.php`](source/app/Http/Controllers/SettingPlafonPerjalananDinasController.php)
- [`app/Http/Controllers/TransaksiController.php`](source/app/Http/Controllers/TransaksiController.php)
- [`app/Http/Controllers/UserController.php`](source/app/Http/Controllers/UserController.php)
- [`app/Http/Controllers/WeekdayGroupController.php`](source/app/Http/Controllers/WeekdayGroupController.php)
- [`app/Http/Controllers/WilayahController.php`](source/app/Http/Controllers/WilayahController.php)
- [`app/Models/AuditLog.php`](source/app/Models/AuditLog.php)
- [`app/Models/BsActivityLog.php`](source/app/Models/BsActivityLog.php)
- [`app/Models/BsCashFlowSnapshot.php`](source/app/Models/BsCashFlowSnapshot.php)
- [`app/Models/BsUsageApprovalLog.php`](source/app/Models/BsUsageApprovalLog.php)
- [`app/Models/CashFlowSnapshot.php`](source/app/Models/CashFlowSnapshot.php)
- [`app/Models/Company.php`](source/app/Models/Company.php)
- [`app/Models/EventHourGroup.php`](source/app/Models/EventHourGroup.php)
- [`app/Models/Level.php`](source/app/Models/Level.php)
- [`app/Models/PerongkosanEventDalamKota.php`](source/app/Models/PerongkosanEventDalamKota.php)
- [`app/Models/PerongkosanEventLuarKota.php`](source/app/Models/PerongkosanEventLuarKota.php)
- [`app/Models/PlafonEventDalamKota.php`](source/app/Models/PlafonEventDalamKota.php)
- [`app/Models/PlafonEventLuarKota.php`](source/app/Models/PlafonEventLuarKota.php)
- [`app/Models/Position.php`](source/app/Models/Position.php)
- [`app/Models/ReimbursementClaim.php`](source/app/Models/ReimbursementClaim.php)
- [`app/Models/ReimbursementLog.php`](source/app/Models/ReimbursementLog.php)
- [`app/Models/SettingPlafonPerjalananDinas.php`](source/app/Models/SettingPlafonPerjalananDinas.php)
- [`app/Models/StockCashFlowSnapshot.php`](source/app/Models/StockCashFlowSnapshot.php)
- [`app/Models/WeekdayGroup.php`](source/app/Models/WeekdayGroup.php)
- [`app/Models/Wilayah.php`](source/app/Models/Wilayah.php)
- [`app/Models/WilayahDistance.php`](source/app/Models/WilayahDistance.php)
- [`app/Notifications/BsApproved.php`](source/app/Notifications/BsApproved.php)
- [`app/Notifications/BsCreated.php`](source/app/Notifications/BsCreated.php)
- [`app/Notifications/BsDisbursed.php`](source/app/Notifications/BsDisbursed.php)
- [`app/Notifications/BsRejected.php`](source/app/Notifications/BsRejected.php)
- [`app/Notifications/BsSettled.php`](source/app/Notifications/BsSettled.php)
- [`app/Notifications/BsUsageApprovalUpdated.php`](source/app/Notifications/BsUsageApprovalUpdated.php)
- [`app/Notifications/TransaksiOverPlafon.php`](source/app/Notifications/TransaksiOverPlafon.php)
- [`app/Providers/EventServiceProvider.php`](source/app/Providers/EventServiceProvider.php)
- [`app/Traits/FiltersByPeriod.php`](source/app/Traits/FiltersByPeriod.php)
- [`artisan`](source/artisan)

## [09-kode-pendukung-lain-02.md](kode-untuk-chat/09-kode-pendukung-lain-02.md)

Ukuran UTF-8: 338,216 byte. Isi:

- [`composer.lock`](source/composer.lock)

## [09-kode-pendukung-lain-03.md](kode-untuk-chat/09-kode-pendukung-lain-03.md)

Ukuran UTF-8: 182,137 byte. Isi:

- [`config/app.php`](source/config/app.php)
- [`config/auth.php`](source/config/auth.php)
- [`config/cache.php`](source/config/cache.php)
- [`config/database.php`](source/config/database.php)
- [`config/filesystems.php`](source/config/filesystems.php)
- [`config/logging.php`](source/config/logging.php)
- [`config/mail.php`](source/config/mail.php)
- [`config/queue.php`](source/config/queue.php)
- [`config/sanctum.php`](source/config/sanctum.php)
- [`config/services.php`](source/config/services.php)
- [`config/session.php`](source/config/session.php)
- [`package.json`](source/package.json)
- [`public/index.php`](source/public/index.php)
- [`resources/css/app.css`](source/resources/css/app.css)
- [`resources/js/app.js`](source/resources/js/app.js)
- [`resources/js/bootstrap.js`](source/resources/js/bootstrap.js)
- [`resources/views/Pagination/bootstrap-5-htmx.blade.php`](source/resources/views/Pagination/bootstrap-5-htmx.blade.php)
- [`resources/views/Positions/create.blade.php`](source/resources/views/Positions/create.blade.php)
- [`resources/views/Positions/edit.blade.php`](source/resources/views/Positions/edit.blade.php)
- [`resources/views/approval-atasan/detail.blade.php`](source/resources/views/approval-atasan/detail.blade.php)
- [`resources/views/approval-atasan/index.blade.php`](source/resources/views/approval-atasan/index.blade.php)
- [`resources/views/auth/login.blade.php`](source/resources/views/auth/login.blade.php)
- [`resources/views/cash-flow/index.blade.php`](source/resources/views/cash-flow/index.blade.php)
- [`resources/views/cash-flow/partials/expense-breakdown.blade.php`](source/resources/views/cash-flow/partials/expense-breakdown.blade.php)
- [`resources/views/cash-flow/partials/styles.blade.php`](source/resources/views/cash-flow/partials/styles.blade.php)
- [`resources/views/cash-flow/partials/transaction-section.blade.php`](source/resources/views/cash-flow/partials/transaction-section.blade.php)
- [`resources/views/cash-flow/show.blade.php`](source/resources/views/cash-flow/show.blade.php)
- [`resources/views/companies/create.blade.php`](source/resources/views/companies/create.blade.php)
- [`resources/views/companies/edit.blade.php`](source/resources/views/companies/edit.blade.php)
- [`resources/views/cost_categories/create.blade.php`](source/resources/views/cost_categories/create.blade.php)
- [`resources/views/cost_categories/edit.blade.php`](source/resources/views/cost_categories/edit.blade.php)
- [`resources/views/event_hour_groups/create.blade.php`](source/resources/views/event_hour_groups/create.blade.php)
- [`resources/views/event_hour_groups/edit.blade.php`](source/resources/views/event_hour_groups/edit.blade.php)
- [`resources/views/levels/create.blade.php`](source/resources/views/levels/create.blade.php)
- [`resources/views/levels/edit.blade.php`](source/resources/views/levels/edit.blade.php)

## [09-kode-pendukung-lain-04.md](kode-untuk-chat/09-kode-pendukung-lain-04.md)

Ukuran UTF-8: 144,132 byte. Isi:

- [`resources/views/pengajuan-bs/create.blade.php`](source/resources/views/pengajuan-bs/create.blade.php)
- [`resources/views/pengajuan-bs/index.blade.php`](source/resources/views/pengajuan-bs/index.blade.php)
- [`resources/views/pengajuan-bs/show.blade.php`](source/resources/views/pengajuan-bs/show.blade.php)
- [`resources/views/periode/index.blade.php`](source/resources/views/periode/index.blade.php)
- [`resources/views/periode/show.blade.php`](source/resources/views/periode/show.blade.php)
- [`resources/views/perongkosan_event_dalam_kota/create.blade.php`](source/resources/views/perongkosan_event_dalam_kota/create.blade.php)
- [`resources/views/perongkosan_event_dalam_kota/edit.blade.php`](source/resources/views/perongkosan_event_dalam_kota/edit.blade.php)
- [`resources/views/perongkosan_event_dalam_kota/index.blade.php`](source/resources/views/perongkosan_event_dalam_kota/index.blade.php)
- [`resources/views/perongkosan_event_dalam_kota/show.blade.php`](source/resources/views/perongkosan_event_dalam_kota/show.blade.php)

## [09-kode-pendukung-lain-05.md](kode-untuk-chat/09-kode-pendukung-lain-05.md)

Ukuran UTF-8: 180,886 byte. Isi:

- [`resources/views/perongkosan_event_luar_kota/create.blade.php`](source/resources/views/perongkosan_event_luar_kota/create.blade.php)
- [`resources/views/perongkosan_event_luar_kota/edit.blade.php`](source/resources/views/perongkosan_event_luar_kota/edit.blade.php)
- [`resources/views/perongkosan_event_luar_kota/index.blade.php`](source/resources/views/perongkosan_event_luar_kota/index.blade.php)
- [`resources/views/perongkosan_event_luar_kota/show.blade.php`](source/resources/views/perongkosan_event_luar_kota/show.blade.php)
- [`resources/views/plafon_event_dalam_kota/create.blade.php`](source/resources/views/plafon_event_dalam_kota/create.blade.php)
- [`resources/views/plafon_event_dalam_kota/edit.blade.php`](source/resources/views/plafon_event_dalam_kota/edit.blade.php)
- [`resources/views/plafon_event_luar_kota/create.blade.php`](source/resources/views/plafon_event_luar_kota/create.blade.php)
- [`resources/views/plafon_event_luar_kota/edit.blade.php`](source/resources/views/plafon_event_luar_kota/edit.blade.php)
- [`resources/views/profile/index.blade.php`](source/resources/views/profile/index.blade.php)
- [`resources/views/roles/create.blade.php`](source/resources/views/roles/create.blade.php)
- [`resources/views/roles/edit.blade.php`](source/resources/views/roles/edit.blade.php)

## [09-kode-pendukung-lain-06.md](kode-untuk-chat/09-kode-pendukung-lain-06.md)

Ukuran UTF-8: 160,873 byte. Isi:

- [`resources/views/setting_plafon_perjalanan_dinas/create.blade.php`](source/resources/views/setting_plafon_perjalanan_dinas/create.blade.php)
- [`resources/views/setting_plafon_perjalanan_dinas/edit.blade.php`](source/resources/views/setting_plafon_perjalanan_dinas/edit.blade.php)
- [`resources/views/users/create.blade.php`](source/resources/views/users/create.blade.php)
- [`resources/views/users/edit.blade.php`](source/resources/views/users/edit.blade.php)
- [`resources/views/weekday_groups/create.blade.php`](source/resources/views/weekday_groups/create.blade.php)
- [`resources/views/weekday_groups/edit.blade.php`](source/resources/views/weekday_groups/edit.blade.php)
- [`resources/views/welcome.blade.php`](source/resources/views/welcome.blade.php)
- [`resources/views/wilayah/create.blade.php`](source/resources/views/wilayah/create.blade.php)
- [`resources/views/wilayah/edit.blade.php`](source/resources/views/wilayah/edit.blade.php)
- [`routes/api.php`](source/routes/api.php)
- [`routes/console.php`](source/routes/console.php)
- [`vite.config.js`](source/vite.config.js)
