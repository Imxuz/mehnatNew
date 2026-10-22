<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('click_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('click_logs', 'click_id')) {
                $table->unsignedBigInteger('click_id')->nullable()->after('admin_id')->index();
            }
        });

        // Mavjud 21,000+ yozuvlardagi new_values JSON dan click_id ni to'ldirish
        DB::statement("UPDATE click_logs SET click_id = JSON_UNQUOTE(JSON_EXTRACT(new_values, '$.id')) WHERE new_values IS NOT NULL AND click_id IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('click_logs', function (Blueprint $table) {
            if (Schema::hasColumn('click_logs', 'click_id')) {
                $table->dropIndex(['click_id']);
                $table->dropColumn('click_id');
            }
        });
    }
};
