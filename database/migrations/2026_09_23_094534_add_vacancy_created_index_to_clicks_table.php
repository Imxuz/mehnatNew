<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $indexes = collect(DB::select("SHOW INDEXES FROM clicks"))->pluck('Key_name')->all();

        if (!in_array('idx_vacancy_created', $indexes)) {
            Schema::table('clicks', function (Blueprint $table) {
                $table->index(['vacancy_id', 'created_at'], 'idx_vacancy_created');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clicks', function (Blueprint $table) {
            $table->dropIndex('idx_vacancy_created');
        });
    }
};

