<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clicks', function (Blueprint $table) {
            if (!Schema::hasColumn('clicks', 'is_manual')) {
                $table->boolean('is_manual')->default(false)->after('vacancy_id');
            }
            if (!Schema::hasColumn('clicks', 'created_by_admin_id')) {
                $table->foreignId('created_by_admin_id')->nullable()->after('is_manual')->constrained('admins')->nullOnDelete();
            }
            if (!Schema::hasColumn('clicks', 'basis_file')) {
                $table->string('basis_file')->nullable()->after('created_by_admin_id');
            }
            if (!Schema::hasColumn('clicks', 'basis_comment')) {
                $table->text('basis_comment')->nullable()->after('basis_file');
            }
        });

        \Illuminate\Support\Facades\DB::table('permissions')->updateOrInsert(
            ['name' => 'manual-click-create'],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $perm = \Illuminate\Support\Facades\DB::table('permissions')->where('name', 'manual-click-create')->first();
        $superAdmin = \Illuminate\Support\Facades\DB::table('roles')->where('name', 'super-admin')->first();
        if ($perm && $superAdmin) {
            \Illuminate\Support\Facades\DB::table('permission_role')->updateOrInsert(
                [
                    'permission_id' => $perm->id,
                    'role_id' => $superAdmin->id,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clicks', function (Blueprint $table) {
            if (Schema::hasColumn('clicks', 'created_by_admin_id')) {
                $table->dropForeign(['created_by_admin_id']);
                $table->dropColumn('created_by_admin_id');
            }
            if (Schema::hasColumn('clicks', 'is_manual')) {
                $table->dropColumn('is_manual');
            }
            if (Schema::hasColumn('clicks', 'basis_file')) {
                $table->dropColumn('basis_file');
            }
            if (Schema::hasColumn('clicks', 'basis_comment')) {
                $table->dropColumn('basis_comment');
            }
        });

        $perm = \Illuminate\Support\Facades\DB::table('permissions')->where('name', 'manual-click-create')->first();
        if ($perm) {
            \Illuminate\Support\Facades\DB::table('permission_role')->where('permission_id', $perm->id)->delete();
            \Illuminate\Support\Facades\DB::table('permissions')->where('id', $perm->id)->delete();
        }
    }
};
