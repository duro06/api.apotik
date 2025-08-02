<?php

namespace App\Models\Transactions;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanH extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];

    public function rinci()
    {
        return $this->hasMany(PenjualanR::class, 'nopenjualan', 'nopenjualan');
    }
}
