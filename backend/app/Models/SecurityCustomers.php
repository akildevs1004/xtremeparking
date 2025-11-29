<?php

namespace App\Models;

use App\Models\Customers\SecurityLogin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\Document\Security;

class SecurityCustomers extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = ["securityInfo"];

    public function securityInfo()
    {
        return $this->belongsTo(SecurityLogin::class, "security_id", "id");
    }
}
