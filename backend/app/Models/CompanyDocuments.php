<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyDocuments extends Model
{
    protected $guarded = [];

    use HasFactory;

    public function getPathAttribute($value)
    {
        if (!$value) {
            return null;
        }
        return asset('company_documents/' . $value);
    }
    protected $casts = [
        "created_at" => "datetime:d-M-y"
    ];
}
