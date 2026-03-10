<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterDeviceSerialNumbers extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = ["assignedcompany", "liveDevice"];

    public function assignedcompany()
    {
        return  $this->belongsTo(Company::class, "company_id", "id");
    }

    public function liveDevice()
    {
        return $this->belongsTo(Device::class, "master_serial_number", "serial_number");
    }
    public function getPictureAttribute($value)
    {
        if (!$value) {
            return null;
        }
        return asset('master_devices/' . $value);
    }
}
