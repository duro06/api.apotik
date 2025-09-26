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
        Schema::table('penjualan_r_s', function (Blueprint $table) {
            $table->decimal('diskon', 20)->default(0)->after('hpp');
        });
        Schema::table('retur_penjualan_rs', function (Blueprint $table) {
            $table->decimal('diskon', 20)->default(0)->after('hpp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('penjualan_r_s', 'diskon')) {
            Schema::table('penjualan_r_s', function (Blueprint $table) {
                $table->dropColumn('diskon');
            });
        }
        if (Schema::hasColumn('retur_penjualan_rs', 'diskon')) {
            Schema::table('retur_penjualan_rs', function (Blueprint $table) {
                $table->dropColumn('diskon');
            });
        }
    }
};
