<?php

namespace App\Models\Deivices;

use App\Models\Device;
use App\Models\DeviceSensorTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceZones extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $table = 'device_sensor_zones';

    public function device()
    {

        return $this->belongsTo(Device::class, "device_id", "id");
    }
    public function sensor_types()
    {

        return $this->belongsTo(DeviceSensorTypes::class, "sensor_type", "name");
    }
}
