<?php

namespace App\Models\Master;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Satuan extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];
}
