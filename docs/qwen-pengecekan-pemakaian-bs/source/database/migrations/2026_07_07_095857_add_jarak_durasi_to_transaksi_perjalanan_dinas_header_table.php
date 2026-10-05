// database/migrations/xxxx_xx_xx_add_jarak_durasi_to_transaksi_perjalanan_dinas_header_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_perjalanan_dinas_header', function (Blueprint $table) {
            $table->decimal('jarak_km', 10, 2)->default(0)->after('tujuan_dinas_id');
            $table->decimal('durasi_jam', 5, 2)->default(0)->after('jarak_km');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_perjalanan_dinas_header', function (Blueprint $table) {
            $table->dropColumn(['jarak_km', 'durasi_jam']);
        });
    }
};