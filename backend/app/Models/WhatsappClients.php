<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappClients extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'accounts' => 'array', // Automatically cast JSON to array
    ];
}
