<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityLogin extends Model
{

    use HasFactory;
    protected $guarded = [];

    protected $appends = ["full_name"];

    //protected $with = ["customersAssigned"];

    public function user()
    {
        return $this->belongsTo(User::class, "user_id", "id");
    }
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
    // public function customersAssigned()
    // {
    //     return $this->hasMany(SecurityCustomers::class, "security_id", "id");
    // }
    public function getPictureAttribute($value)
    {
        if (!$value) {
            return null;
        }
        return asset('security/' . $value);
    }

    public function sosRooms()
    {
        return $this->belongsToMany(
            DeviceSosRooms::class,
            'security_sos_rooms_lists',
            'security_user_id',
            'sos_room_table_id'
        )->withTimestamps();
    }
}
