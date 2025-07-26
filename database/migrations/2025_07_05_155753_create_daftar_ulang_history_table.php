<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDaftarUlangHistoryTable extends Migration
{
    public function up()
    {
        Schema::create('daftar_ulang_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daftar_ulang_id')->constrained('daftar_ulang')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('detail_pembayaran_id')->constrained('detail_pembayarans')->onDelete('cascade');
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->onDelete('cascade');
            $table->integer('jumlah_bayar')->default(0);
            $table->enum('status', ['cicilan', 'lunas']);
            $table->string('keterangan')->nullable();
            $table->timestamp('tanggal_bayar')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('daftar_ulang_history');
    }
}
