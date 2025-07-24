<?php

namespace App\Models\Transactions;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderRecord extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];

    // protect pemanggilan data pasti dengan order_header
    public function orderHeader()
    {
        return $this->belongsTo(OrderHeader::class, 'nomor_order', 'nomor_order');
    }
}
