<?php

namespace App\Models\Transactions;

use App\Models\Master\Supplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penerimaan_h extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function supllier()
    {
        return $this->belongsTo(Supplier::class, 'kode_supplier', 'kode');
    }
}
