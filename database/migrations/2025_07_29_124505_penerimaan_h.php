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
        Schema::create('penerimaan_h', function (Blueprint $table) {
            $table->id();
            $table->string('nopenerimaan');
            $table->string('noorder');
            $table->date('tgl_penerimaan')->default(now());
            $table->string('nofaktur');
            $table->date('tgl_faktur');
            $table->string('kode_suplier');
            $table->string('jenispajak');
            $table->string('pajak');
            $table->string('kode_user');
            $table->string('flag');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimaan_h');
    }
};
