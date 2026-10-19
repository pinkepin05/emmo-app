<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bs_usage_check_details', function (Blueprint $table) {
            // Logical reference: OPR/PD rows may be replaced or deleted; history must survive.
            $table->unsignedInteger('source_detail_id')->nullable();
            $table->text('source_remark')->nullable();
            // MySQL needs a replacement index for the parent foreign key before dropping the old unique key.
            $table->index('bs_usage_check_id', 'bs_check_parent_index');
            $table->unique(['bs_usage_check_id', 'source_detail_id'], 'bs_check_source_detail_unique');
        });
        Schema::table('bs_usage_check_details', fn (Blueprint $table) => $table->dropUnique('bs_check_category_unique'));
    }

    public function down(): void
    {
        if (DB::table('bs_usage_check_details')->whereNotNull('source_detail_id')->exists()
            || DB::table('bs_usage_check_drafts')->exists()) {
            throw new RuntimeException('Archive per-item Finance checks and drafts before reverting this migration.');
        }
        Schema::table('bs_usage_check_details', fn (Blueprint $table) =>
            $table->unique(['bs_usage_check_id', 'cost_category_id'], 'bs_check_category_unique'));
        Schema::table('bs_usage_check_details', function (Blueprint $table) {
            $table->dropUnique('bs_check_source_detail_unique');
            $table->dropIndex('bs_check_parent_index');
            $table->dropColumn(['source_detail_id', 'source_remark']);
        });
    }
};
