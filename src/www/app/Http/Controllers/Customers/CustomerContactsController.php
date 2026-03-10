<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\WhatsappController;
use App\Mail\EmailContentDefault;
use App\Models\Company;
use App\Models\Customers\CustomerContacts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class CustomerContactsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customers\CustomerContacts  $customerContacts
     * @return \Illuminate\Http\Response
     */
    public function show(CustomerContacts $customerContacts)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Customers\CustomerContacts  $customerContacts
     * @return \Illuminate\Http\Response
     */
    public function edit(CustomerContacts $customerContacts)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customers\CustomerContacts  $customerContacts
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CustomerContacts $customerContacts)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customers\CustomerContacts  $customerContacts
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $contact = CustomerContacts::find($id);

        if ($contact->delete()) {

            return $this->response('Contact Details are Deleted', null, true);
        } else
            return $this->response('Contact Details are not Deleted', null, false);
        //
    }


    public function ResendSecretCodeMail(Request $request)
    {


        if ($request->filled("contact_id")) {

            $contact = CustomerContacts::where("id", $request->contact_id)->first();

            $emailData = [
                'subject' => "Secret Code for Verification - " . $contact["first_name"] . ' ' . $contact["last_name"],
                'body' =>  "Secret Code for Verification - " . $contact["first_name"] . ' ' . $contact["last_name"] . " is " . $contact["alarm_stop_pin"],
            ];

            $body_content1 = new EmailContentDefault($emailData);


            //tanjore mail settings
            $emailData["to"] = $contact["email"];


            (new ClientController())->sendExternalMail($emailData);

            Mail::to($contact["email"])
                ->cc("venuakil2@gmail.com")
                ->queue($body_content1);

            //whatsapp

            $company = Company::where("id", $request->company_id)->first();
            $body_content = $emailData["body"] . '\n' .
                '\n\n

        Regards,
         ' . env("MAIL_FROM_NAME") . ' ';
            $response = (new WhatsappController())->sendWhatsappNotification($company, $body_content, $contact["whatsapp"], []);





            return $this->response("Verification Code shared to email" . $contact["email"], null, true);
        }
    }
}
