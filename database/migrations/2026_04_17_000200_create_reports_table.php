<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reports')) {
            Schema::create('reports', function (Blueprint $table) {
                $table->id();
                $table->string('ticket_code', 50)->unique();
                $table->string('reporter_name', 100)->nullable();
                $table->string('phone', 25)->nullable();
                $table->foreignId('category_id')->constrained('categories');
                $table->string('other_category', 100)->nullable();
                $table->string('title', 150);
                $table->text('description');
                $table->string('province_code', 13);
                $table->string('regency_code', 13);
                $table->string('district_code', 13);
                $table->string('village_code', 13)->nullable();
                $table->string('location_text', 255);
                $table->string('rt', 5)->nullable();
                $table->string('rw', 5)->nullable();
                $table->enum('status', ['BARU', 'DIPROSES', 'SELESAI', 'DITOLAK'])->default('BARU');
                $table->dateTime('created_at')->useCurrent();
                $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

                $table->foreign('province_code')->references('code')->on('regions')->restrictOnDelete();
                $table->foreign('regency_code')->references('code')->on('regions')->restrictOnDelete();
                $table->foreign('district_code')->references('code')->on('regions')->restrictOnDelete();
                $table->foreign('village_code')->references('code')->on('regions')->nullOnDelete();
                $table->index(['district_code', 'status', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
