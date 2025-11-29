<?php

namespace App\Models\Customers;

use App\Models\Company;
use App\Models\CustomerProductServices;
use App\Models\ParkingMembers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPayments extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function company()
    {
        return $this->belongsTo(Company::class, "company_id", "id");
    }
    public function member()
    {
        return $this->belongsTo(ParkingMembers::class, "member_id", "id");
    }
    public function customer()
    {
        return $this->belongsTo(Customers::class, "customer_id", "id");
    }
    public function customer_product_services()
    {
        return $this->belongsTo(CustomerProductServices::class, "customer_product_service_id", "id");
    }
}
