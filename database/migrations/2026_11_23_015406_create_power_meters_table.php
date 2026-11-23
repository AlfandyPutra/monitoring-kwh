<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('power_meters', function (Blueprint $table) {
            $table->id();
            $table->string('panel_code')->unique();      // Contoh: INCOMING, MDP-01NJK, SDP-D1
            $table->string('panel_name');                // Nama lengkap panel
            $table->string('converter_name')->nullable();// Contoh: USR-DR302 #1 atau USR-DR302 #2
            $table->string('ip_address');                // IP Address dari Converter USR-DR302
            $table->integer('port')->default(502);       // Port standar Modbus TCP
            $table->integer('slave_id')->default(1);     // ID unik tiap kWh meter di jalur RS485
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('power_meters');
    }
};