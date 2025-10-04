<?php

namespace App\Models\Transactions;

use App\Models\Master\Barang;
use App\Models\Master\Supplier;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penerimaan_r extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode');
    }
    public function suplier()
    {
        return $this->hasOne(Supplier::class, 'kode', 'kode_suplier');
    }
}
