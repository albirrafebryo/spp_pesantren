<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuktiPembayaransTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bukti_pembayarans', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('pembayaran_id')->index();

            // === PERUBAHAN MULAI DARI SINI ===
            $table->unsignedBigInteger('user_id')->nullable()->index(); // Kolom user_id yang mengupload bukti

            $table->string('bukti'); // Path file di storage/public
            $table->enum('status', ['pending', 'valid', 'invalid'])->default('pending'); // status: pending/valid/invalid

            // Kolom-kolom dari versi lama jika masih dipakai:
            // $table->string('file_bukti')->nullable(); // (BUKAN DIPAKAI, diganti ke 'bukti' saja)
            // $table->enum('status_verifikasi', ['pending', 'valid', 'invalid'])->nullable();
            $table->text('catatan_verifikasi')->nullable(); // alasan jika ditolak
            $table->integer('jumlah_bayar')->nullable(); // nominal yang dibayar (bisa cicilan)
            $table->unsignedBigInteger('diverifikasi_oleh')->nullable(); // id user (bendahara) yang verifikasi
            $table->timestamp('tanggal_verifikasi')->nullable();

            $table->timestamps();

            // Foreign Key
            $table->foreign('pembayaran_id')->references('id')->on('pembayarans')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('diverifikasi_oleh')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bukti_pembayarans');
    }
}
