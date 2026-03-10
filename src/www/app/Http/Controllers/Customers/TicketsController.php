<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Customers\Reports\AlarmReportsController;
use App\Mail\EmailContentDefault;
use App\Models\AlarmEvents;
use App\Models\AlarmEventsTechnician;
use App\Models\Company;
use App\Models\Customers\CustomerContacts;
use App\Models\Customers\TicketAttachments;
use App\Models\Customers\TicketResponses;
use App\Models\Customers\TicketResponsesAttachments;
use App\Models\Customers\Tickets;
use App\Models\Device;
use App\Models\TicketCategories;
use App\Models\TicketSensorTest;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TicketsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $model = Tickets::with(["customer", "category", "technician"])->where("company_id", $request->company_id);

        $model->when($request->filled("technician_id"), function ($query) use ($request) {
            $query->where("technician_id", $request->technician_id);
        });
        $model->when($request->filled("category_id"), function ($query) use ($request) {
            $query->where("category_id", $request->category_id);
        });
        $model->when($request->filled("filter_customer_id"), function ($query) use ($request) {
            $query->where("customer_id", $request->filter_customer_id);
        });

        $model->when($request->filled("common_search"), function ($query) use ($request) {

            $query->where(function ($q) use ($request) {
                $q->where("id", "ILIKE", "%$request->common_search%")
                    ->orWhere("subject", "ILIKE", "%$request->common_search%")
                    ->orWhereHas("customer", function ($qqq) use ($request) {
                        $qqq->where("building_name", "ILIKE", "%$request->common_search%");
                    });
            });
        });

        $model->when($request->filled("filterRequestfrom"), function ($query) use ($request) {

            if ($request->filterRequestfrom == 'security') {
                $query->where("security_id", "!=", null);
            }
            if ($request->filterRequestfrom == 'customer') {
                $query->where("customer_id", "!=", null);
            }
        });

        $model->when($request->filled("date_from"), function ($q) use ($request) {
            $q->whereBetween("created_datetime", [$request->date_from . " 00:00:00", $request->date_to . " 23:59:00"]);
        });

        $model->when($request->filled("customer_id"), function ($q) use ($request) {

            $q->where("customer_id", $request->customer_id);
        });
        $model->when($request->filled("security_id"), function ($q) use ($request) {

            $q->where("security_id", $request->security_id);
        });
        $model->when($request->filled("status"), function ($q) use ($request) {

            $q->where("status", $request->status);
        });
        $model->when($request->filled("filterWord"), function ($q) use ($request) {
            if ($request->filterWord == 'activeStats') {
                $q->where("status", 1);
            }
            if ($request->filterWord == 'todayStats') {
                $q->whereBetween("created_datetime", [date("Y-m-d 00:00:00"), date("Y-m-d 23:59:59")]);
            }
            if ($request->filterWord == 'monthlyStats') {

                $monthYear = date("Y-m");
                $start_datetime = $monthYear . '-01 00:00:00';
                $end_datetime = (new DateTime($monthYear . '-01'))->modify('last day of this month')->format('Y-m-d 23:59:59');

                $q->whereBetween("created_datetime", [$start_datetime, $end_datetime]);
            }
            if ($request->filterWord == '10daysPending') {


                $q->where("created_datetime", "<", now()->subDays(10));
            }
            if ($request->filterWord == '30daysPending') {


                $q->where("created_datetime", "<", now()->subDays(30));
            }
        });




        $model->orderBy("created_datetime", "desc");
        return $model->paginate($request->per_page ?? 10);
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
        $request->validate(["subject" => "required", "description" => "required"]);


        $data = $request->all();
        unset($data['login_user_id']);
        unset($data['login_user_type']);

        $columns = ['company_id', 'customer_id', 'security_id', 'subject',  'description'];
        $selected = array_intersect_key($data, array_flip($columns));
        $selected["created_datetime"] = date("Y-m-d H:i:s");
        //$selected["is_read"] = false;
        $selected["is_technician_read"] = false;
        $selected["is_customer_read"] = false;
        $selected["is_security_read"] = false;

        if ($request->filled("category_id"))
            $selected["category_id"] = $request->category_id;


        $model = Tickets::create($selected);




        $insertedId = $model->id;
        $attachments = [];
        if ($request->items)
            foreach ($request->items as $item) {

                $file = $item["file"];
                $title = $item["title"];
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $file->move(public_path('tickets/attachments/' . $insertedId . "/"), $fileName);


                $attachments[] = [
                    "title" => $title,
                    "attachment" => $fileName,
                    "file_type" => $ext,
                    "ticket_id" => $insertedId,
                ];
            }

        TicketAttachments::insert($attachments);

        return $this->response("New Ticket is created successfully", $model, true);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tickets  $tickets
     * @return \Illuminate\Http\Response
     */
    public function show(Tickets $tickets)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tickets  $tickets
     * @return \Illuminate\Http\Response
     */
    public function edit(Tickets $tickets)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tickets  $tickets
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tickets $tickets)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tickets  $tickets
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tickets $tickets)
    {
        //
    }

    public function downloadTicketAttachment(Request $request, $ticket_id, $file_name)
    {

        $filePath = public_path("tickets/attachments/" . $ticket_id) .  '/' . $file_name;


        if (file_exists($filePath)) {

            return response()->download($filePath, $file_name);
        } else {

            abort(404);
        }
    }

    public function technicianDashboardTicketStats(Request $request)
    {

        $model = Tickets::where("company_id", $request->company_id)->where("status", 1);

        $model->when($request->filled("technician_id"), function ($query) use ($request) {
            $query->where("technician_id", $request->technician_id);
        });

        $total = $model->clone()->count();
        $security_count = $model->clone()->whereNotNull("security_id")->count();
        $customer_count = $model->clone()->whereNotNull("customer_id")->count();
        return ["total" => $total, "customer_count" => $customer_count, "security_count" => $security_count];
    }

    public function technicianDashboardMonthlyTicketStats(Request $request)
    {

        $monthYear = date("Y-m");
        $start_datetime = $monthYear . '-01 00:00:00';
        $end_datetime = (new DateTime($monthYear . '-01'))->modify('last day of this month')->format('Y-m-d 23:59:59');
        $model = Tickets::where("company_id", $request->company_id)->whereBetween("created_datetime", [$start_datetime, $end_datetime]);
        $model->when($request->filled("technician_id"), function ($query) use ($request) {
            $query->where("technician_id", $request->technician_id);
        });
        $pending_count = (clone $model)->where("status", 1)->count();
        $resolved_count = (clone $model)->where("status", 0)->count();
        return ["total" => $pending_count + $resolved_count, "pending_count" => $pending_count, "resolved_count" => $resolved_count];
    }
    public function technicianDashboardTodayTicketStats(Request $request)
    {

        $monthYear = date("Y-m-d");
        $start_datetime = $monthYear . ' 00:00:00';
        $end_datetime = $monthYear . ' 23:59:59';
        $model = Tickets::where("company_id", $request->company_id)->whereBetween("created_datetime", [$start_datetime, $end_datetime]);
        $model->when($request->filled("technician_id"), function ($query) use ($request) {
            $query->where("technician_id", $request->technician_id);
        });
        $pending_count = (clone $model)->where("status", 1)->count();
        $resolved_count = (clone $model)->where("status", 0)->count();
        return ["total" => $pending_count + $resolved_count, "pending_count" => $pending_count, "resolved_count" => $resolved_count];
    }
    public function technicianDashboardPendingMorethanDaysStats(Request $request)
    {

        $pendingDays = 10;
        if ($request->filled('days')) $pendingDays = $request->days;
        $model = Tickets::where("company_id", $request->company_id)
            ->where("status", 1)
            ->where("created_datetime", "<", now()->subDays($pendingDays));
        $model->when($request->filled("technician_id"), function ($query) use ($request) {
            $query->where("technician_id", $request->technician_id);
        });
        $total = $model->clone()->count();
        $security_count = $model->clone()->whereNotNull("security_id")->count();
        $customer_count = $model->clone()->whereNotNull("customer_id")->count();
        return ["total" => $total, "customer_count" => $customer_count, "security_count" => $security_count];
    }
    public function ticketsUnreadNotifications(Request $request)
    {
        $model = Tickets::where("company_id", $request->company_id);

        $model->when($request->filled("technician_id"), function ($query) use ($request) {
            $query->where("technician_id", $request->technician_id);
        });

        if ($request->filled("technician_id")) {

            $model->where("is_technician_read", false)->orderBy("created_datetime", "desc");

            return $model->get();
        }
        if ($request->filled("customer_id")) {

            $model->where("is_customer_read", false)->where("customer_id", $request->customer_id);
            $model->orderBy("created_datetime", "desc");

            return $model->get();
        }

        if ($request->filled("security_id")) {

            $model->where("is_security_read", false)->where("security_id", $request->security_id);
            $model->orderBy("created_datetime", "desc");

            return $model->get();
        }



        return [];
    }

    public function updateTicketTechnician(Request $request)
    {

        $request->validate([

            'company_id' => 'required|integer',
            'ticket_id' => 'required|integer',
            'technician_id' => 'required|integer',

        ]);


        Tickets::where("company_id", $request->company_id)->where("id", $request->ticket_id)->update(["technician_id" => $request->technician_id]);

        return $this->response("Technician Updated Successfully", null, true);
    }

    public function TechnicianStartJob(Request $request)
    {


        $request->validate([

            'company_id' => 'required|integer',
            'customer_id' => 'required|integer',
            'technician_id' => 'required|integer',
            'pin_verified_by_id' => 'required|integer',
            'tikcet_id' => 'required|integer',
            'pin_number' => 'required|integer',
            'close_ticket' => 'nullable|integer',




        ]);

        $subject = '';
        $subject1 = '';

        if ($request->filled("contact_type")) {



            $contactModel = CustomerContacts::where("id", $request->input('pin_verified_by_id'))
                ->where("alarm_stop_pin", (int)$request->input('pin_number'))
                ->get();

            if (count($contactModel) == 0) {
                return [
                    "status" => false,
                    "errors" => ['pin_number' => ['Customer PIN number is not matched']],
                ];
            } else {
                $subject = "#" . $request->tikcet_id . ":Technician Accepted Job with Customer Verification-" . $contactModel[0]["address_type"];
                $subject1 = "Technician Accepted Job with Customer Verification-" . $contactModel[0]["address_type"];

                $device = Device::where("customer_id", $request->customer_id)->first();
                $timeZone = $device?->utc_time_zone ?: 'Asia/Dubai';
                $dateObj  = new DateTime("now", new DateTimeZone($timeZone));
                $currentDateTime = $dateObj->format('Y-m-d H:i:s');




                $data2 = [];

                if ($request->filled('close_ticket')) {
                    $data2["job_end_verified_contact_id"] = $request->pin_verified_by_id;
                    $data2["job_end_datetime"] =  $currentDateTime;
                    $data2["status"] = 0;

                    $subject = "#" . $request->tikcet_id . ":Technican Closed Ticket with Customer Verification-" . $contactModel[0]["address_type"];
                    $subject1 = "Technican Closed Ticket with Customer Verification-" . $contactModel[0]["address_type"];


                    //upadate technicain id
                    Device::where("customer_id", $request->customer_id)->update(["ticket_id" => null]);
                } else {
                    $data2["job_start_verified_contact_id"] = $request->pin_verified_by_id;
                    $data2["job_start_datetime"] =  $currentDateTime;
                    $data2["technician_id"] =  $request->technician_id;



                    //upadate technicain id
                    Device::where("customer_id", $request->customer_id)->update(["ticket_id" => $request->tikcet_id]);
                }




                $ticket = Tickets::where("id", $request->tikcet_id);
                $ticket->update($data2);
            }
        }



        $data = [
            "company_id" => $request->company_id,
            "ticket_id" => $request->tikcet_id,
            "technician_id" => $request->technician_id,
            "description" => $subject1,
            "created_datetime" => date("Y-m-d H:i:s")
        ];

        $model = TicketResponses::create($data);


        //add test results attachement

        if ($request->filled('close_ticket')) {
            $sensorList = (new AlarmReportsController())->processTestResults($request->company_id, $request->customer_id,  $request->tikcet_id);




            $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first();

            $fileName = "#" . $request->ticket_id . " - Technician Ticket - Test Sensor Results.pdf";




            // Save PDF to a temporary path
            $pdf = PDF::loadView("alarm_reports/technician_ticket_sensor_test_results", ["request" => $request, "reports" => $sensorList, "ticket_id" => $request->ticket_id, "company" => $company])->setPaper("A4", "portrait");


            $fileName = time() . '.' . "pdf";

            $folderPath = public_path('ticket_responses/attachments/' . $model->id . "/");

            if (!is_dir($folderPath)) {
                mkdir($folderPath);
            }



            $pdfPath = public_path('ticket_responses/attachments/' . $model->id . "/" . $fileName);
            $pdf->save($pdfPath);
            $insertedId = $model->id;
            $attachments[] = [
                "title" => "Alarm Sensor test Results",
                "attachment" => $fileName,
                "file_type" => "pdf",
                "ticket_response_id" => $insertedId,

            ];
            TicketResponsesAttachments::insert($attachments);
        }



        if ($contactModel[0]["email"] != '') {
            $emailData = [
                'subject' => $subject,
                'body' =>  $subject,
            ];
            if ($request->filled('close_ticket')) {
                $body_content1 = new EmailContentDefault($emailData, $pdfPath, $fileName);
            } else
                $body_content1 = new EmailContentDefault($emailData);

            try {
                Mail::to($contactModel[0]["email"])
                    //->cc("venuakil2@gmail.com")
                    ->send($body_content1);
            } catch (\Exception $e) {
            }
        }


        return $this->response("Technician Job updated Successfully", null, true);
    }

    public function TicketCategories(Request $request)
    {

        return TicketCategories::query()->get();
    }

    public function TechnicianTestSensor(Request $request)
    {
        $test_datetime = date("Y-m-d H:i:s", strtotime("-1 minutes", strtotime($request->test_date_time)));

        $model = AlarmEventsTechnician::where("serial_number", $request->serial_number)
            ->where("zone", $request->zone);


        $area = "00";
        if ($request->filled("area"))
            $area = $request->area;
        $model->where("area", $area);



        $alarmCount = $model->where("alarm_start_datetime", ">=", $test_datetime)->get();
        if (count($alarmCount)) {

            return $this->response("Alarm Event Found", $alarmCount[0], true);
            //return $alarmCount->alarm_start_datetime;
        }

        return $this->response("Alarm Event Not Found", null, false);
    }

    public function TechnicianTestResultsUpdate(Request $request)
    {



        if ($request->filled("results")) {
            $data = $request->results;


            TicketSensorTest::where("ticket_id", $request->ticket_id)->delete();

            foreach ($data as $result) {

                $datetime = null;
                if (isset($result["test_date_time"]))
                    $datetime = date("Y-m-d") . ' ' . $result["test_date_time"];

                $resultsData = [
                    "ticket_id" => $result["ticket_id"],
                    "company_id" => $result["company_id"],
                    "customer_id" => $result["customer_id"],
                    "device_id" => $result["device_id"],
                    "serial_number" => $result["serial_number"],
                    "test_datetime" => $datetime, // date("Y-m-d") . ' ' . $result["test_date_time"],
                    "test_status" => $result["test_result"],
                    "zone_code" => $result["zone_code"],
                    "area_code" => $result["area_code"],




                ];

                TicketSensorTest::create($resultsData);
            }
        }

        return $this->response("Ticket Sensor results saved", null, true);
    }

    public function adminTicketsStatistics(Request $request)
    {




        return Tickets::where("company_id", $request->company_id)

            ->when($request->filled("technician_id"), function ($query) use ($request) {
                $query->where("technician_id", $request->technician_id);
            })
            ->selectRaw('
            COUNT(CASE WHEN DATE(created_datetime) = CURRENT_DATE THEN 1 END) AS today_tickets,
            COUNT(CASE WHEN job_start_datetime IS NOT NULL AND status = 1 THEN 1 END) AS assigned_tickets,
            COUNT(CASE WHEN status = 1 THEN 1 END) AS open_tickets,
            COUNT(CASE WHEN status = 0 THEN 1 END) AS closed_tickets
        ')
            ->first();
    }
}
