<?php

namespace App\Models;

use App\Models\Customers\AlarmCateogories;
use App\Models\Customers\CustomerAlarmNotes;
use App\Models\Customers\CustomerContacts;
use App\Models\Customers\Customers;
use App\Models\Customers\SecurityLogin;
use App\Models\Customers\TechnicianLogins;
use App\Models\Deivices\DeviceZones;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlarmEventsTechnician extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ["alarm_forwarded",];
    protected $with = ["category", "device"];


    public function customer()
    {
        return $this->belongsTo(Customers::class, "customer_id", "id");
    }
    public function category()
    {
        return $this->belongsTo(AlarmCateogories::class, "alarm_category", "id");
    }
    public function technician()
    {
        return $this->belongsTo(TechnicianLogins::class, "technician_id", "id");
    }


    public function device()
    {
        return $this->belongsTo(Device::class, "serial_number", "serial_number");
    }
    public function security()
    {
        return $this->belongsTo(SecurityLogin::class, "security_id", "id");
    }
    public function pinverifiedby()
    {
        return $this->belongsTo(CustomerContacts::class, "pin_verified_by_id", "id");
    }
    public function notes()
    {
        return $this->hasMany(CustomerAlarmNotes::class, "alarm_id", "id")->orderBy("created_datetime", "ASC");
    }
    // public function zoneData()
    // {
    //     return $this->belongsTo(Device::class, "serial_number", "serial_number");
    // }
    public function zoneData()
    {
        return  $this->belongsTo(DeviceZones::class, "sensor_zone_id", "id")

            // ->where("area_code", $this->area)

            //->where("area_code", $this->area_code)
        ;
    }
    // public function  forwarded()
    // {

    //     //return CustomerAlarmNotes::where("alarm_id", $this->id)->get();
    //     return $this->hasMany(CustomerAlarmNotes::class, "alarm_id", "id")->where("event_status", "Forwarded");
    // }
    public function getAlarmForwardedAttribute()
    {

        //return CustomerAlarmNotes::where("alarm_id", $this->id)->get();
        return $this->hasMany(CustomerAlarmNotes::class, "alarm_id", "id")->where("event_status", "Forwarded")->get();;
    }
}
