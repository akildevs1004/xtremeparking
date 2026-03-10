<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\ClientController;
use App\Mail\EmailContentDefault;
use App\Models\Company;
use App\Models\Customers\CustomerContacts;
use App\Models\Customers\Customers;
use App\Models\CustomersBuildingTypes;
use App\Models\SalesBusinessSourceTypes;
use App\Models\SalesInquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SalesInquiryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $model = SalesInquiry::with(["quotation.ProductService", "businessSource", "buildingType"])->where("company_id", $request->company_id);



        $model->when($request->filled("date_from"), function ($q) use ($request) {

            $q->where("created_datetime", ">=", $request->date_from . ' 00:00:00');
            $q->where("created_datetime", "<=", $request->date_to . ' 23:59:59');
        });


        $model->when($request->filled("common_search") && $request->common_search != '', function ($q) use ($request) {



            $q->where(function ($q) use ($request) {
                $q->Orwhere('business_source_info', 'ILIKE', '$request->common_search%');
                $q->Orwhere('first_name', 'ILIKE', '$request->common_search%');
                $q->Orwhere('last_name', 'ILIKE', '$request->common_search%');
                $q->Orwhere('contact_number', 'ILIKE', '$request->common_search%');
                $q->Orwhere('whatsapp_number', 'ILIKE', '$request->common_search%');
                $q->Orwhere('email', 'ILIKE', '$request->common_search%');
                $q->Orwhere('address', 'ILIKE', '$request->common_search%');
                $q->Orwhere('device_type', 'ILIKE', '$request->common_search%');

                $count = (int)$request->common_search;
                $q->Orwhere('sensor_count',   $count);
                $q->Orwhere('building_name', 'ILIKE', '$request->common_search%');
                $q->Orwhere('receiption_remarks', 'ILIKE', '$request->common_search%');
                $q->Orwhere('customer_request', 'ILIKE', '$request->common_search%');



                $q->orWhereHas('businessSource', function ($query) use ($request) {
                    $query->where('name', 'ILIKE', "%$request->common_search%");
                });

                $q->orWhereHas('buildingType', function ($query) use ($request) {
                    $query->where('name', 'ILIKE', "%$request->common_search%");
                });
                $q->orWhereHas('quotation', function ($query) use ($request) {
                    $query->where('quotation_id', 'ILIKE', "%$request->common_search%");
                });
            });
        });

        $model->orderBy("created_datetime", "DESC");

        return $model->paginate($request->per_page);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validate = [
            "company_id" => "required",
            "business_source_id" => "required",
            "business_source_info" => "required",
            "first_name" => "required",
            "last_name" => "required",
            "contact_number" => "required",
            "whatsapp_number" => "nullable",
            "email" => "required",
            "address" => "required",
            "building_name" => "nullable",
            "building_type_id" => "nullable",
            "device_type" => "required",
            "sensor_count" => "required",


            "receiption_remarks" => "nullable",
            "customer_request" => "nullable",
        ];
        $data =  $request->validate($validate);
        $subject = "New Inquiry received from " . $request->first_name . ' ' . $request->last_name;
        $business = "";
        if ($request->building_type_id > 0)
            $business = CustomersBuildingTypes::where("id", $request->building_type_id)->pluck("name")[0];
        $data["created_datetime"] = date("Y-m-d H:i:s");


        if ($request->editId) {
            $data["updated_datetime"] = date("Y-m-d H:i:s");
            SalesInquiry::where("id", $request->editId)->update($data);
            $subject = "Inquiry Updated by Team -  from " . $request->first_name . ' ' . $request->last_name;
        } else {
            SalesInquiry::create($data);
        }


        //mail
        $company = Company::with("user")->where("id", $request->company_id)
            ->with("user")
            ->first();
        if ($company) {


            $body_content = ' <div class="email-body">
            <p>Hello <strong>' . $company->name . '</strong>,</p>
            <p>You have received a new inquiry from <strong>' . $request->first_name . ' ' . $request->last_name . '</strong>.</p>

            <table class="email-table">

                <tr><td>Contact Number</td><td>: <strong>' . $request->contact_number . '</strong></td></tr>
                <tr><td>WhatsApp Number</td><td>: <strong>' . $request->whatsapp_number . '</strong></td></tr>
                <tr><td>Email</td><td>: <strong>' . $request->email . '</strong></td></tr>
                <tr><td>Building Name</td><td>: <strong>' . $request->building_name . '</strong></td></tr>
                <tr><td>Type Of Business</td><td>: <strong>' . $business . '</strong></td></tr>
                <tr><td>Alarm Type</td><td>:<strong> ' . $request->device_type . '</strong></td></tr>
                <tr><td>Sensors</td><td>: <strong>' . $request->sensor_count . '</strong></td></tr>
                <tr><td>Reception Notes</td><td>: <strong>' . $request->receiption_remarks . '</strong></td></tr>
                <tr><td>Customer Requests</td><td>: <strong>' . $request->customer_request . '</strong></td></tr>
            </table>
<br/><br/>
            <p>Regards, <br/>
             ' . env("MAIL_FROM_NAME") . '</p>
        </div>';
            $data = [
                'subject' => $subject,
                'body' => $body_content,
                'to' => $company->user->email,
            ];


            (new ClientController())->sendExternalMail($data);
            Mail::to($company->user->email)->queue(new EmailContentDefault($data));


            //whatsapp
            $body_content = 'Hello, Company Name:' . $company->name . '\n
            You have received a new Inquiry from *' . $request->first_name . ' ' . $request->last_name . '*\n\n
                Contact Number: *' . $request->contact_number . '*\n
                WhatsApp Number: *' . $request->whatsapp_number . '*\n
                Email: *' . $request->email . '*\n
                Building Name: *' . $request->building_name . '*\n
                Type Of Business: *' . $business . '*\n
                Alarm Type: *' . $request->device_type . '*\n
                Sensors: *' . $request->sensor_count . '*\n
                Reception Notes: *' . $request->receiption_remarks . '*\n
                Customer Requests: *' . $request->customer_request . '*\n\n\n

            Regards, \n
             ' . env("MAIL_FROM_NAME");
            $response = (new WhatsappController())->sendWhatsappNotification($company, $body_content, $company->contact_number, []);
        }


        if ($request->editId)
            return $this->response("Inquiry Updated Successfully", null, true);
        return $this->response("Inquiry Created Successfully", null, true);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SalesInquiry  $salesInquiry
     * @return \Illuminate\Http\Response
     */
    public function show(SalesInquiry $salesInquiry)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SalesInquiry  $salesInquiry
     * @return \Illuminate\Http\Response
     */
    public function edit(SalesInquiry $salesInquiry)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SalesInquiry  $salesInquiry
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SalesInquiry $salesInquiry)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SalesInquiry  $salesInquiry
     * @return \Illuminate\Http\Response
     */
    public function destroy(SalesInquiry $salesInquiry)
    {
        //
    }

    public function GetBusinessSourceTypes(Request $request)
    {

        return SalesBusinessSourceTypes::orderBy("name", "asc")->get();
    }
}
