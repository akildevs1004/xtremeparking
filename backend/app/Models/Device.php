<?php

namespace App\Models;

use App\Models\Customers\Customers;
use App\Models\Deivices\DeviceZones;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Device extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $with = ["sensorzones"];
    public function getPictureAttribute($value)
    {
        if (!$value) {
            return null;
        }
        return asset('master_devices/' . $value);
    }
    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customers::class);
    }

    public function sensorzones()
    {
        return $this->hasMany(DeviceZones::class,  'device_id', 'id');
    }
    public function status()
    {
        return $this->belongsTo(DeviceStatus::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function activealarmlogs()
    {
        return $this->hasMany(AlarmLogs::class, "serial_number", "serial_number")->where("alarm_status", 1);
    }



    public function companyBranch()
    {
        return $this->belongsTo(CompanyBranch::class, "branch_id");
    }
    public function branch()
    {
        return $this->belongsTo(CompanyBranch::class, "branch_id");
    }
    protected $casts = [
        'created_at' => 'datetime:d-M-y',
    ];

    public function scopeExcludeMobile($query)
    {
        return $query->where('name', 'not like', '%Mobile%')->where('name', 'not like', '%Manual%');
    }
}
