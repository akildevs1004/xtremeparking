<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingSlots extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'slot_number' => 'integer',
        'company_id' => 'integer',
    ];
}
