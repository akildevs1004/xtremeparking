<?php

namespace App\Models;

use App\Models\Customers\Customers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportNotificationLogs extends Model
{

    protected $guarded = [];
    use HasFactory;
    // protected $casts = [
    //     'created_at' => 'datetime:d-M-y H:i',
    // ];

    public function customer()
    {
        return $this->belongsTo(Customers::class, "customer_id", "id");
    }
}
