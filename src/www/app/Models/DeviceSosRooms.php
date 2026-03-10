<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceSosRooms extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    // public function latestAlarm()
    // {
    //     return $this->hasOne(DeviceSosRoomLogs::class, 'device_sos_room_table_id', 'id')
    //         ->where('company_id', $this->company_id ?? request('company_id'))
    //         ->latestOfMany('alarm_start_datetime');
    // }

    public function latestAlarm()
    {
        return $this->hasOne(DeviceSosRoomLogs::class, 'device_sos_room_table_id', 'id')
            // ->where('company_id', $this->company_id ?? request('company_id'))
            ->where('alarm_end_datetime', null);

        // ->latestOfMany('alarm_start_datetime');
    }
}
