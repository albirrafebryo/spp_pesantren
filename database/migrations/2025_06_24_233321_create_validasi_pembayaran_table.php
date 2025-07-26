<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateValidasiPembayaranTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('validasi_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bukti_pembayaran_id')
                  ->constrained('bukti_pembayarans')
                  ->onDelete('cascade');
            $table->foreignId('pembayaran_id')
                  ->constrained('pembayarans')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validasi_pembayaran');
    }
}
