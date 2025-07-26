<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembayaran_id')->constrained('pembayarans')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('detail_pembayaran_id')->constrained('detail_pembayarans')->onDelete('cascade');
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->onDelete('cascade');
            $table->tinyInteger('bulan');
            $table->integer('jumlah_bayar');
            $table->enum('status', ['pending', 'cicilan', 'lunas', 'invalid']);
            $table->integer('cicilan_ke')->nullable();
            $table->dateTime('tanggal_bayar')->nullable();
            $table->string('keterangan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_histories');
    }
};
