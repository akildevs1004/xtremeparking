<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityDocuments extends Model
{
    protected $guarded = [];

    use HasFactory;

    public function getPathAttribute($value)
    {
        if (!$value) {
            return null;
        }
        return asset('security_documents/' . $value);
    }
    protected $casts = [
        "created_at" => "datetime:d-M-y"
    ];
}
