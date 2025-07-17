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
        Schema::create('barang_masuks', function (Blueprint $table) {
            $table->id();
            // user id
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            // sub kategori id
            $table->foreignId('sub_kategori_id')
                ->constrained('sub_kategoris')
                ->onDelete('cascade');
            // asal barang
            $table->string('asal_barang');
            // nomor surat
            $table->string('nomor_surat')->unique();
            // lampiran
            $table->string('lampiran')->nullable();
            // tanggal masuk
            $table->date('tanggal_masuk');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_masuks');
    }
};
