<?php

namespace App\Models;

use App\Http\Controllers\ParkingCameraLogsController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingCameraLogs extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = ['parking_image_path'];


    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function getParkingImagePathAttribute()
    {
        // return url(env("PARKING_CAMERA_PUBLIC_FOLDER") . '/' . $this->attributes['company_id']);
        $ip = (new ParkingCameraLogsController())->getServerIp();

        return   "http://{$ip}:8000" .  '/api' . '/parking_camera_logs' . '/' . $this->attributes['company_id'] . '';
    }

    public function ParkingMembers()
    {
        return $this->belongsTo(ParkingMembers::class, "membership_id");
    }

    public function ParkingMembersGuest()
    {
        return $this->belongsTo(ParkingMembersVehiclesList::class, "member_guest_vehicle_id");
    }
}
