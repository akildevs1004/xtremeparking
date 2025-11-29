<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceArmedLogs extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function device()
    {
        return $this->belongsTo(Device::class, "serial_number", "serial_number");
    }
    public function alarm_events()
    {
        return $this->hasMany(AlarmEvents::class, 'serial_number', 'serial_number');
    }
}
