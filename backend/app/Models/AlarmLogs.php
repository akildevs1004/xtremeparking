<?php

namespace App\Models;

use App\Models\Deivices\DeviceZones;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AlarmLogs extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:d-M-y',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class, "company_id", "id");
    }
    public function device()
    {
        return $this->belongsTo(Device::class, "serial_number", "serial_number");
    }
    public function devicesensorzones()
    {


        return $this->belongsTo(DeviceZones::class,  "zone", "zone_code")
            ->whereColumn("serial_number", "=", "device_sensor_zones.serial_number")
        ;


        // return $this->belongsTo(DeviceZones::class, "area", "area_code")
        //     // ->whereNotNull("area_code") // Ignore records where area_code is NULL
        //     ->whereColumn("serial_number", "=", "devices.serial_number")
        //     ->whereColumn("zone_code", "=", "devices.zone_code");

        // return $this->hasOne(DeviceZones::class, 'area_code', 'area')
        //     ->whereColumn('serial_number', 'device_sensor_zones.serial_number')
        //     ->whereColumn('zone_code', 'device_sensor_zones.zone_code')
        //     ->where(function ($query) {
        //         $query->whereNull('area_code')->orWhere('area_code', '000');
        //     });

        return $this->belongsTo(DeviceZones::class,  "area", "area_code")
            ->whereColumn("serial_number", "=", "device_sensor_zones.serial_number")
            ->whereColumn("zone_code", "=", "device_sensor_zones.zone_code");
    }
    // public function sensorzones()
    // {
    //     return $this->belongsTo(DeviceZones::class, "id", "zone_id");
    // }

    protected static function boot()
    {
        parent::boot();

        // Order by name ASC
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('id', 'desc');
        });
    }
}
