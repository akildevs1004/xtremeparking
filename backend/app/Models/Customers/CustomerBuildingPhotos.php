<?php

namespace App\Models\Customers;

use App\Models\Plotting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerBuildingPhotos extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $appends = ['picture_raw'];
    protected $with = ["photoPlottings"];

    public function getPictureAttribute($value)
    {
        if (!$value) {
            return null;
        }
        return asset('customers/building_photos/' . $value);
    }
    public function getPictureRawAttribute($value)
    {
        $arr = explode('customers/building_photos/', $this->picture);
        return    $arr[1] ?? '';
    }
    public function photoPlottings()
    {
        return $this->hasMany(Plotting::class, "customer_building_picture_id", "id");
    }
}
