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
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->string('nisn');
            $table->string('tahun_ajaran');
            $table->string('bulan');
            $table->integer('jumlah_bayar')->default(0); // berapa yang dibayar
            $table->integer('jumlah_tagihan');           // total tagihan dari SPP
            $table->enum('status', ['belum', 'cicilan', 'lunas'])->default('belum');
            $table->timestamps();

            // ✅ Fix foreign key
            $table->foreign('nisn')->references('nisn')->on('siswas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
