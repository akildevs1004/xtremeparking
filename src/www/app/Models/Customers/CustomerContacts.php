<?php

namespace App\Models\Customers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerContacts extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $appends = ['picture_raw', "full_name"];
    public function getProfilePictureAttribute($value)
    {
        if (!$value) {
            return null;
        }
        return asset('customers/contacts/' . $value);
    }

    public function getPictureRawAttribute($value)
    {
        $arr = explode('customers/contacts/', $this->profile_picture);
        return    $arr[1] ?? '';
    }
    public function getFullNameAttribute($value)
    {
        return $this->first_name . " " . $this->last_name;
    }
}
