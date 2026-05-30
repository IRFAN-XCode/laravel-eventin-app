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
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->references('id')->on('organizer')->onDelete('cascade');
            $table->string('nama_event');
            $table->text('deskripsi');
            $table->date('tgl_mulai');
            $table->date('tgl_berakhir');
            $table->integer('harga_vip')->nullable();
            $table->integer('harga_reg')->nullable();
            $table->string('lokasi');
            $table->string('alamat');
            $table->boolean('seats');
            $table->string('thumbnail');
            $table->integer('kapasitas_vip')->nullable();
            $table->integer('kapasitas_reg')->nullable();
            $table->enum('tipe_event', ['paid', 'unpaid']);
            $table->enum('kategori', ['musik', 'pameran', 'seminar', 'workshop']);
            $table->enum('status', ['draft', 'open', 'close'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
