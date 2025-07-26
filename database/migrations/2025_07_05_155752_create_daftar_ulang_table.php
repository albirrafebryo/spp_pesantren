<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDaftarUlangTable extends Migration
{
    public function up()
    {
        Schema::create('daftar_ulang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('detail_pembayaran_id')->constrained('detail_pembayarans')->onDelete('cascade');
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->onDelete('cascade');
            $table->integer('jumlah_tagihan')->default(0);
            $table->integer('jumlah_bayar')->default(0);
            $table->enum('status', ['belum', 'cicilan', 'lunas'])->default('belum');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('daftar_ulang');
    }
}

