<?php

namespace App\Models;

use App\Models\Customers\CustomerBuildingPictures;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plotting extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        "plottings" => "array"
    ];

    public function photos()
    {
        return $this->belongsTo(CustomerBuildingPictures::class, "customer_building_picture_id", "id");
    }
}
