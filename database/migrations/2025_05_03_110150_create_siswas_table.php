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
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->string('nisn')->nullable()->unique(); // <- nullable
            $table->string('nis')->unique();
            $table->string('nama');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('wali_id')->nullable()->constrained('users')->onDelete('set null'); // nullable
            $table->text('alamat')->nullable(); // nullable
            $table->string('no_hp');
            $table->foreignId('tahun_masuk')->constrained('tahun_ajarans')->onDelete('cascade');
            $table->enum('status', ['aktif', 'lulus', 'keluar', 'mutasi'])->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
