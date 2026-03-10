<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceNotificationsManagers extends Model
{
    use HasFactory;

    protected $guarded = [];
    public function device()
    {
        return $this->belongsTo(Device::class, "serial_number", "serial_number");
    }
    public function company()
    {
        return $this->belongsTo(Company::class, "company_id", "id");
    }
}
