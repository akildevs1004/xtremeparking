<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingMembers extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['picture_raw'];

    public function getPictureAttribute($value)
    {
        if (!$value) {
            return null;
        }
        return asset('parking_members/' . $value);
    }
    public function getPictureRawAttribute($value)
    {
        try {
            if ($this->attributes['picture']) {
                return  $this->attributes['picture'];
            }
        } catch (\Exception $e) {
            return null;
        }
    }
   public function ParkingFamilyMembers()
    {
        return $this->hasMany(ParkingMembersVehiclesList::class, "member_id");
    }
}
