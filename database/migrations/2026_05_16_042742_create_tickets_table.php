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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->string('nama_pemesan');
            $table->string('email');
            $table->string('nomor_handphone');
            $table->date('tgl_lahir');
            $table->string('jenis_kelamin');
            $table->enum('kategori_tiket', ['vip', 'reg']);
            $table->string('nomor_kursi')->nullable();
            $table->string('kode_tiket');
            $table->string('qr_code');
            $table->enum('status', ['belum_hadir', 'checkin', 'checkout'])->default('belum_hadir');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
