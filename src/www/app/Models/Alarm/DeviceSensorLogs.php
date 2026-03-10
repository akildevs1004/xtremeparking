<?php

namespace App\Models\Alarm;

use App\Models\Company;
use App\Models\CompanyBranch;
use App\Models\Device;
use App\Models\DeviceStatus;
use App\Models\DeviceTemperatureSensors;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceSensorLogs extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function device()
    {
        return $this->belongsTo(Device::class, "serial_number", "device_id")->withDefault(["name" => "Manual", "device_id" => "Manual"]);
    }

    public function deviceTemperatureSensor()
    {
        return $this->belongsTo(DeviceTemperatureSensors::class, 'temperature_serial_address', 'temperature_serial_address')
            ->whereColumn('device_id', 'device_id'); // assuming serial_number in logs is same as device_id
    }
    // public function deviceTemperatureSensor()
    // {
    //     return $this->hasOne(DeviceTemperatureSensors::class, 'temperature_serial_address', 'temperature_serial_address');
    // }
}
