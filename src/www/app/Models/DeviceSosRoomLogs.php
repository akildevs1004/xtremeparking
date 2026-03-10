<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceSosRoomLogs extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function device()
    {
        return $this->belongsTo(Device::class);
    }
    public function room()
    {
        return $this->belongsTo(DeviceSosRooms::class, "device_sos_room_table_id");
    }
}
