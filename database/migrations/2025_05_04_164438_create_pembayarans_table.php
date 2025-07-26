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
                $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
                $table->foreignId('detail_pembayaran_id')->constrained('detail_pembayarans')->onDelete('cascade');
                $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->onDelete('cascade');
                $table->tinyInteger('bulan'); 
                $table->integer('jumlah_bayar')->default(0);
                $table->integer('jumlah_tagihan');
                $table->enum('status', ['belum', 'cicilan', 'lunas', 'pending'])->default('belum');
                $table->timestamps();
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
