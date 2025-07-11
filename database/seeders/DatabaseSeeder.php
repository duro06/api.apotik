<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $sa = User::where('username', '=', 'sa')->first();
        if (!$sa) {
            User::create([
                'kode' => 'USR000000',
                'username' => 'sa',
                'nama' => 'Super Admin',
                'password' => bcrypt('sasa0102'),
                'email' => 'sa@app.com',

            ]);
        }
        // User::factory(5)->create();
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $conter = DB::table('counter')->first();
        if (!$conter) {
            DB::table('counter')->insert([
                'kode_barang' => 0,
                'kode_pelanggan' => 0,
                'kode_satuan' => 0,
                'kode_supplier' => 0,
                'kode_jabatan' => 0
            ]);
        }
    }
}
