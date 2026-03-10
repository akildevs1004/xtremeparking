<?php

namespace App\Models\Customers;

use App\Models\Customers\Customers;
use App\Models\Customers\SecurityLogin;
use App\Models\Customers\TicketResponses;
use App\Models\TicketCategories;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tickets extends Model
{
    use HasFactory;

    protected $guarded = [];


    protected $appends = ['last_active_datetime'];
    protected $with = ["customer", "security", "responses", "attachments"];
    public function responses()
    {
        return $this->hasMany(TicketResponses::class, "ticket_id", "id")->orderBy("created_datetime", 'DESC');
    }
    public function attachments()
    {
        return $this->hasMany(TicketAttachments::class, "ticket_id", "id");
    }
    public function customer()
    {
        return $this->belongsTo(Customers::class, "customer_id", "id");
    }
    public function technician()
    {
        return $this->belongsTo(TechnicianLogins::class, "technician_id", "id");
    }


    public function category()
    {
        return $this->belongsTo(TicketCategories::class, "category_id", "id");
    }
    public function security()
    {
        return $this->belongsTo(SecurityLogin::class, "security_id", "id");
    }
    public function  getlastActiveDatetimeAttribute()
    {

        $model = TicketResponses::where("ticket_id", $this->id)->orderBy("created_datetime", "desc")->first();

        if ($model) {
            return $model->created_datetime;
        } else return null;
    }
}
