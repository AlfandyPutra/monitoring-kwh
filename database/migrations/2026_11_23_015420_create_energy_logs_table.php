<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('energy_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('power_meter_id')->constrained('power_meters')->onDelete('cascade');
            $table->decimal('kwh_total', 15, 2);           // Angka kumulatif meteran
            $table->decimal('kwh_usage', 15, 2)->default(0); // Selisih pemakaian (anti-minus)
            $table->decimal('voltage_avg', 8, 2)->nullable();// Rata-rata Voltase (V)
            $table->decimal('current_avg', 8, 2)->nullable();// Rata-rata Arus (Ampere)
            $table->decimal('power_factor', 5, 2)->nullable();// Cos Phi / Power Factor
            $table->timestamp('recorded_at');              // Waktu pembacaan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_logs');
    }
};