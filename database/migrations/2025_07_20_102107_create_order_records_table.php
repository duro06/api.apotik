<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * flagging : 1 = draft, 2 = submitted, 3 = approved, 4 = rejected, 5 = completed
     */
    public function up(): void
    {
        Schema::create('order_records', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_order'); // Nomor Order, Contoh TRX000001, ambil dari order_headers
            $table->string('kode_barang');
            $table->string('kode_user');
            $table->string('satuan_k')->nullable();
            $table->string('satuan_b')->nullable();
            $table->string('isi')->nullable();
            $table->string('flag')->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_records');
    }
};
