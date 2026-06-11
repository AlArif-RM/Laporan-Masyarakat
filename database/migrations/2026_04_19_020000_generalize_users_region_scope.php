<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (Schema::hasColumn('users', 'district_code') && ! Schema::hasColumn('users', 'region_code')) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE users DROP FOREIGN KEY users_district_code_foreign');
                DB::statement('ALTER TABLE users DROP INDEX users_district_code_foreign');
                DB::statement('ALTER TABLE users CHANGE district_code region_code VARCHAR(13) NULL');
                DB::statement("ALTER TABLE users MODIFY role VARCHAR(50) NOT NULL DEFAULT 'ADMIN_KECAMATAN'");
                DB::statement('ALTER TABLE users ADD CONSTRAINT users_region_code_foreign FOREIGN KEY (region_code) REFERENCES regions(code) ON DELETE SET NULL');
            } else {
                Schema::table('users', function (Blueprint $table) {
                    $table->renameColumn('district_code', 'region_code');
                });
            }
        }

        if (Schema::hasColumn('users', 'region_code') && DB::getDriverName() !== 'mysql') {
            return;
        }
    }

    public function down(): void
    {
        // Intentionally left empty to avoid destructive changes in active databases.
    }
};
