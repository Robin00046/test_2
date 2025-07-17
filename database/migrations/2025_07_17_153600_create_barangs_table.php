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
        Schema::create('barangs', function (Blueprint $table) {
            $table->id();
            // id barang masuk
            $table->foreignId('barang_masuk_id')
                ->constrained('barang_masuks')
                ->onDelete('cascade');
            // nama barang
            $table->string('nama_barang');
            // harga barang
            $table->decimal('harga_barang', 10, 2)->default(0.00);
            // jumlah barang
            $table->integer('jumlah_barang')->default(0);
            // satuan barang
            $table->string('satuan_barang')->default('pcs');
            // total harga
            $table->decimal('total_harga', 10, 2)->default(0.00);
            // tanggal expired
            $table->date('tanggal_expired')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
