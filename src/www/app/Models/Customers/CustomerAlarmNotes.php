<?php

namespace App\Models\Customers;

use App\Models\AlarmEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\Document\Security;

class CustomerAlarmNotes extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $appends = ['picture_raw'];
    public function getPictureAttribute($value)
    {
        if (!$value) {
            return null;
        }
        return asset('customers/notes/' . $value);
    }
    public function getPictureRawAttribute($value)
    {
        $arr = explode('customers/notes/', $this->picture);
        return    $arr[1] ?? '';
    }

    public function security()
    {
        return $this->belongsTo(SecurityLogin::class, 'security_id', "id");
    }
    public function contact()
    {
        return $this->belongsTo(CustomerContacts::class, 'contact_id', "id");
    }

    public function alarm()
    {
        return $this->belongsTo(AlarmEvents::class, 'alarm_id', "id");
    }
}
