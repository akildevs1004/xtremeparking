<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\Customers\TicketResponses;
use App\Models\Customers\TicketResponsesAttachments;
use App\Models\Customers\Tickets;
use Illuminate\Http\Request;

class TicketResponsesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $model = TicketResponses::where("company_id", $request->company_id)->where("ticket_id", $request->ticket_id);


        $model->orderBy("created_datetime", "desc");
        return $model->paginate();
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
        $request->validate(["description" => "required", "status" => "nullable"]);


        $data = $request->all();

        unset($data['login_user_id']);
        unset($data['login_user_type']);

        $columns = ['company_id', 'ticket_id',  'customer_id', 'technician_id', 'security_id', 'technician_id', 'description'];
        $selected = array_intersect_key($data, array_flip($columns));
        $selected["created_datetime"] = date("Y-m-d H:i:s");
        //$selected["is_read"] = false;

        $ticketRead["is_technician_read"] = true;
        $ticketRead["is_security_read"] = true;
        $ticketRead["is_customer_read"] = true;



        if ($request->filled("security_id")) {
            $ticketRead["is_technician_read"] = false;
        } else if ($request->filled("customer_id")) {
            $ticketRead["is_technician_read"] = false;
        } else if ($request->filled("technician_id")) {
            $ticketRead["is_security_read"] = false;
            $ticketRead["is_customer_read"] = false;
        }

        $selected["is_read"] = false;



        $model = TicketResponses::create($selected);
        //closed status
        if ($request->status == 0) {
            Tickets::where("id", $request->ticket_id)->update(["status" => 0, "updated_datetime" => date("Y-m-d H:i:s")]);
        }
        Tickets::where("id", $request->ticket_id)->update($ticketRead);


        $insertedId = $model->id;
        $attachments = [];
        if ($request->items)
            foreach ($request->items as $item) {

                $file = $item["file"];
                $title = $item["title"];
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $file->move(public_path('ticket_responses/attachments/' . $insertedId . "/"), $fileName);


                $attachments[] = [
                    "title" => $title,
                    "attachment" => $fileName,
                    "file_type" => $ext,
                    "ticket_response_id" => $insertedId,

                ];
            }

        TicketResponsesAttachments::insert($attachments);






        return $this->response("Ticket reply  is created successfully", $model, true);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customers\TicketResponses  $ticketResponses
     * @return \Illuminate\Http\Response
     */
    public function show(TicketResponses $ticketResponses)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Customers\TicketResponses  $ticketResponses
     * @return \Illuminate\Http\Response
     */
    public function edit(TicketResponses $ticketResponses)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customers\TicketResponses  $ticketResponses
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TicketResponses $ticketResponses)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customers\TicketResponses  $ticketResponses
     * @return \Illuminate\Http\Response
     */
    public function destroy(TicketResponses $ticketResponses)
    {
        //
    }

    public function downloadTicketAttachment(Request $request, $ticket_response_id, $file_name)
    {

        $filePath = public_path("ticket_responses/attachments/" . $ticket_response_id) .  '/' . $file_name;


        if (file_exists($filePath)) {

            return response()->download($filePath, $file_name);
        } else {

            abort(404);
        }
    }

    public function updateTicketReadStatus(Request $request)
    {




        // if ($request->filled("ticket_response_id")) {
        // }
        // if ($request->filled("ticket_id")) {
        //     TicketResponses::where("ticket_id", $request->ticket_id)->update(["is_read" => true]);
        //     Tickets::where("id", $request->ticket_id)->update(["is_read" => true]);
        // }


        TicketResponses::where("ticket_id", $request->ticket_id)->update(["is_read" => true]);
        if ($request->filled("user_type")) {
            $ticketRead = [];
            // $ticketRead["is_technician_read"] = true;
            // $ticketRead["is_security_read"] = true;
            // $ticketRead["is_customer_read"] = true;
            // Tickets::where("id", $request->ticket_id)->update($ticketRead);
            if ($request->user_type == "security") {
                Tickets::where("id", $request->ticket_id)->update(["is_security_read" => true]);
            }
            if ($request->user_type == "customer") {
                Tickets::where("id", $request->ticket_id)->update(["is_customer_read" => true]);
            }
            if ($request->user_type == "technician") {
                Tickets::where("id", $request->ticket_id)->update(["is_technician_read" => true]);
            }
        }





        return $this->response("Updated Ticket Details", null, true);
    }
}
