<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('satuan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->decimal('konversi', 8, 2);
            $table->timestamps();
        });

        Schema::create('kategori', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->timestamps();
        });

        Schema::create('barang', function (Blueprint $table) {
            $table->id();
            $table->string('barcode')->nullable();
            $table->string('nama');
            $table->foreignId('kategori_id')->constrained('kategori');
            $table->foreignId('satuan_id')->constrained('satuan');
            $table->decimal('stok', 8, 2)->default(0);
            $table->decimal('stok_minimal', 8, 2);
            $table->boolean('bisa_ecer')->default(false);
            $table->timestamps();
        });

        Schema::create('barang_masuk', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_nota');
            $table->date('tanggal');
            $table->decimal('harga_beli', 15, 2);
            $table->decimal('harga_jual', 15, 2);
            $table->decimal('harga_ecer', 15, 2)->nullable();
            $table->decimal('jumlah', 8, 2);
            $table->decimal('stok', 8, 2);
            $table->foreignId('barang_id')->constrained('barang');
            $table->timestamps();
        });

        Schema::create('barang_keluar', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('barang_keluar_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_keluar_id')->constrained('barang_keluar')->onDelete('cascade');
            $table->foreignId('barang_masuk_id')->constrained('barang_masuk');
            $table->decimal('jumlah', 8, 2);
            $table->decimal('harga', 15, 2);
            $table->enum('tipe', ['normal', 'ecer']);
            $table->timestamps();
        });
        Schema::create('pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pengeluaran');
            $table->decimal('jumlah', 15, 2);
            $table->date('tanggal');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('barang_keluar_detail');
        Schema::dropIfExists('barang_keluar');
        Schema::dropIfExists('barang_masuk');
        Schema::dropIfExists('barang');
        Schema::dropIfExists('kategori');
        Schema::dropIfExists('satuan');
    }
};