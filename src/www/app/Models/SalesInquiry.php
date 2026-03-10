<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesInquiry extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function businessSource()
    {

        return $this->belongsTo(SalesBusinessSourceTypes::class, "business_source_id", "id");
    }

    public function buildingType()
    {
        return $this->belongsTo(CustomersBuildingTypes::class, "building_type_id", "id");
    }

    public function quotation()
    {
        return $this->belongsTo(SalesQuotations::class, "id", "inquiry_id");
    }
}
