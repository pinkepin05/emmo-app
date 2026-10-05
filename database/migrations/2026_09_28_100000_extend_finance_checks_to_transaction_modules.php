<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bs_usage_checks', function (Blueprint $table) {
            // Existing confirmations retain their accepted amounts and immutable history.
            $table->string('keputusan', 16)->default('APPROVED');
            $table->text('catatan_keputusan')->nullable();
            $table->unsignedInteger('source_revision')->nullable();
        });
        Schema::table('bs_usage_check_details', function (Blueprint $table) {
            // An Event has one total and no cost-category relation.
            $table->integer('cost_category_id')->nullable()->change();
        });
        foreach (['perongkosan_event_dalam_kota', 'perongkosan_event_luar_kota'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->string('status', 16)->default('DRAFT');
                $table->timestamp('validated_at')->nullable();
                $table->integer('validated_by')->nullable();
                $table->unsignedInteger('submission_version')->default(0);
            });
        }
    }

    public function down(): void
    {
        if (DB::table('bs_usage_checks')->whereIn('jenis_transaksi', ['EDK', 'ELK'])->exists()
            || DB::table('bs_usage_checks')->where('keputusan', '<>', 'APPROVED')->exists()) {
            throw new RuntimeException('Archive the new Finance decisions before reverting this migration.');
        }
        foreach (['perongkosan_event_dalam_kota', 'perongkosan_event_luar_kota'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn(['status', 'validated_at', 'validated_by', 'submission_version']));
        }
        Schema::table('bs_usage_check_details', fn (Blueprint $table) => $table->integer('cost_category_id')->nullable(false)->change());
        Schema::table('bs_usage_checks', fn (Blueprint $table) => $table->dropColumn(['keputusan', 'catatan_keputusan', 'source_revision']));
    }
};
