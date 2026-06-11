<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('regions')) {
            Schema::create('regions', function (Blueprint $table) {
                $table->string('code', 13)->primary();
                $table->string('name', 100);
                $table->enum('level', ['PROVINCE', 'REGENCY', 'DISTRICT', 'VILLAGE']);
                $table->string('parent_code', 13)->nullable();

                $table->foreign('parent_code')->references('code')->on('regions')->nullOnDelete();
                $table->index(['level', 'name']);
                $table->index('parent_code');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
