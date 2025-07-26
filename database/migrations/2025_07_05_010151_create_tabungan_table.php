<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('tabungan', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('siswa_id');
        $table->unsignedBigInteger('detail_pembayaran_id');
        $table->enum('jenis', ['setor', 'ambil']); 
        $table->integer('nominal');
        $table->date('tanggal');
        $table->string('keterangan')->nullable();
        $table->enum('status', ['valid', 'pending', 'ditolak'])->default('valid');
        $table->unsignedBigInteger('user_id')->nullable(); 
        $table->timestamps();

        $table->foreign('siswa_id')->references('id')->on('siswas')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tabungan');
    }
};
