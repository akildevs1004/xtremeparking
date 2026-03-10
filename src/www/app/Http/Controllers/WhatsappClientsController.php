<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\WhatsappClient;
use App\Models\WhatsappClients;
use Illuminate\Http\Request;

class WhatsappClientsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($company_id)
    {
        // WhatsappClient::truncate();



        $clients = WhatsappClients::where('company_id', $company_id)->first();

        if ($clients)
            return response()->json($clients);

        else return [];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {



        // $validated = $request->validate([
        //     'company_id' => 'required|exists:companies,id',
        //     'accounts' => 'required|array',
        // ]);

        // Check if a record exists for the given company_id
        $whatsappClient = WhatsappClients::where('company_id', $request->company_id)->first();

        if ($whatsappClient) {
            // Update existing record

            $whatsappClient->update(['accounts' => $request->accounts]);
            (new ActivityController())->recordNewActivity($request, "update", "Company->Settings->Whatsapp is Updated", $request->company_id,  "company", null, null);
        } else {
            // Create new record

            $whatsappClient = WhatsappClients::create(['company_id' => $request->company_id, 'accounts' => $request->accounts]);
            (new ActivityController())->recordNewActivity($request, "create", "Company->Settings->Whatsapp is Created", $request->company_id,  "company", null, null);
        }

        return response()->json($whatsappClient, 200);
    }

    // public function delete(Request $request)
    // {
    //     if ($request->filled("client_id")) {
    //         $clientId = $request->client_id;
    //     }
    // }

    public function testMessage(Request $request)
    {

        $company = Company::with("contact")->where("id", $request->company_id)->first();

        return (new WhatsappController())->sendWhatsappNotification($company, "Test Message", $company["contact"]["whatsapp"], []);
    }
}
