<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerProductServices extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function device_product_service()
    {
        return $this->belongsTo(DeviceProductServices::class, "device_product_service_id", "id");
    }
}
