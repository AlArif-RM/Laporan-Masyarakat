<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('status_logs')) {
            Schema::create('status_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->enum('old_status', ['BARU', 'DIPROSES', 'SELESAI', 'DITOLAK'])->nullable();
                $table->enum('new_status', ['BARU', 'DIPROSES', 'SELESAI', 'DITOLAK']);
                $table->string('note', 255)->nullable();
                $table->dateTime('changed_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('status_logs');
    }
};
