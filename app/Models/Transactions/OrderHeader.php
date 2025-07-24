<?php

namespace App\Models\Transactions;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderHeader extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];


    // Define any relationships or methods specific to OrderHeader here
    public function orderRecords()
    {
        return $this->hasMany(OrderRecord::class, 'nomor_order', 'nomor_order');
    }
}
