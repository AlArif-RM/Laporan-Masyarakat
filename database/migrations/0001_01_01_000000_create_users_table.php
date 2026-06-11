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
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('username', 50)->unique();
                $table->string('password');
                $table->string('name', 100);
                $table->string('role', 50)->default('ADMIN_KECAMATAN');
                $table->string('region_code', 13)->nullable();
                $table->dateTime('created_at')->useCurrent();

                $table->foreign('region_code')->references('code')->on('regions')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
