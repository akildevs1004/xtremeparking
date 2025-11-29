<?php

namespace App\Http\Controllers\Customers;

use App\Exports\AlarmEventNotesExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Customers\Alarm\AlarmNotificationController;
use App\Http\Controllers\Customers\Api\ApiAlarmDeviceTemperatureLogsController;
use App\Models\AlarmEvents;
use App\Models\AlarmEventsTechnician;
use App\Models\AlarmLogs;
use App\Models\Company;
use App\Models\Customers\CustomerAlarmEvents;
use App\Models\Customers\CustomerAlarmNotes;
use App\Models\Customers\CustomerContacts;
use App\Models\Customers\Customers;
use App\Models\Device;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class CustomerAlarmEventsController extends Controller
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
     * @param  \App\Models\Customers\CustomerAlarmEvents  $customerAlarmEvents
     * @return \Illuminate\Http\Response
     */
    public function show(CustomerAlarmEvents $customerAlarmEvents)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Customers\CustomerAlarmEvents  $customerAlarmEvents
     * @return \Illuminate\Http\Response
     */
    public function edit(CustomerAlarmEvents $customerAlarmEvents)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customers\CustomerAlarmEvents  $customerAlarmEvents
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CustomerAlarmEvents $customerAlarmEvents)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customers\CustomerAlarmEvents  $customerAlarmEvents
     * @return \Illuminate\Http\Response
     */
    public function destroyEvent(Request $request)
    {
        $modelEvent = AlarmEvents::where("id", $request->id);

        foreach ($modelEvent->get() as $event) {
            $model = CustomerAlarmNotes::where('id', $event->id);
            try {
                if (file_exists(public_path('/customers/notes') . '/' . $model->first()->picture_raw))
                    unlink(public_path('/customers/notes') . '/' . $model->first()->picture_raw);
            } catch (\Exception $e) {
            }
            $model->delete();
        }
        $modelEvent->delete();

        return $this->response('Event Deleted successfully', null, true);
    }
    public function destroyNotes(Request $request)
    {
        $model = CustomerAlarmNotes::where('id', $request->id);


        try {
            if (file_exists(public_path('/customers/notes') . '/' . $model->first()->picture_raw))
                unlink(public_path('/customers/notes') . '/' . $model->first()->picture_raw);
        } catch (\Exception $e) {
        }
        $model->delete();

        return $this->response('Notes Deleted successfully', null, true);
    }
    public function stopEvent(Request $request)
    {


        $data = $request->validate([
            'primary_pin_number' => 'nullable',
            'seconday_pin_number' => 'nullable',
            'company_id' => 'required',
            'customer_id' => 'required',
            'event_id' => 'required',
            'notes' => 'required',
            'title' => 'required',



        ]);

        if (empty($request->input('primary_pin_number')) && empty($request->input('seconday_pin_number'))) {
            return [
                "status" => false,
                "errors" => ['primary_pin_number' => ['Either Primary or Secondary Pin is  required.']],
            ];
        }
        if ($request->event_id > 0) {
            $alarmModel = AlarmEvents::where("id", $request->event_id);
            $alarm_start_datetime = $alarmModel->first()->alarm_start_datetime;
            $device_serial_number = $alarmModel->first()->serial_number;
            $data = [];
            $primaryCount = CustomerContacts::where("customer_id", $request->input('customer_id'))
                ->where("alarm_stop_pin", $request->input('primary_pin_number'))
                ->count();

            $secondaryCount = CustomerContacts::where("customer_id", $request->input('customer_id'))->where("alarm_stop_pin", $request->input('seconday_pin_number'))->count();



            if ($request->filled('primary_pin_number') && $primaryCount == 0) {
                return [
                    "status" => false,
                    "errors" => ['primary_pin_number' => ['PIN number is not matched']],
                ];
            }
            if ($request->filled('seconday_pin_number') && $secondaryCount == 0) {
                return [
                    "status" => false,
                    "errors" => ['primary_pin_number' => ['PIN number is not matched']],
                ];
            }

            // if ($primaryCount == 0 && $secondaryCount == 0) {
            //     return [
            //         "status" => false,
            //         "errors" => ['primary_pin_number' => ['PIN number is not matched']],
            //     ];
            // }

            if ($primaryCount) {
                $data["pin_verified_by"] = "primary";
            } else if ($secondaryCount) {
                $data["pin_verified_by"] = "secondary";
            }

            $data["alarm_status"] = 0;
            $data["alarm_end_manually"] = 1;
            $data["alarm_end_datetime"] = date("Y-m-d H:i:s");

            $datetime1 = new DateTime($alarm_start_datetime);
            $datetime2 = new DateTime(date("Y-m-d H:i:s"));

            $interval = $datetime1->diff($datetime2);

            $data["response_minutes"] = $interval->i + ($interval->h * 60) + ($interval->days * 1440);


            $alarmModel->update($data);
            //-----------------------------------------------------------------------------------------
            $data = [];

            // i represents the minutes part of the interval

            $data["event_status"] = "Closed";
            $data['company_id'] =  $request->company_id;
            $data['customer_id'] = $request->customer_id;
            $data['alarm_id'] = $request->event_id;
            $data['title'] = $request->title;
            $data['notes'] = $request->notes;
            $data['created_datetime'] = date("Y-m-d H:i:s");
            $record = CustomerAlarmNotes::create($data);

            AlarmEvents::where("id", $request->event_id)->update(["forwarded" => false]);



            //udapte json file
            (new ApiAlarmDeviceTemperatureLogsController)->createAlarmEventsJsonFile($request->company_id);

            //turnoff device alarm status
            if ($device_serial_number != '') {
                $alarm_event_active_count = AlarmEvents::where("serial_number", $device_serial_number)->where("alarm_status", 1)->count();
                if ($alarm_event_active_count == 0) {
                    $device_Data = [];
                    $device_Data["alarm_status"] = 0;
                    $device_Data["alarm_end_datetime"] = date('Y-m-d H:i:s');

                    Device::where("serial_number", $device_serial_number)->update($device_Data);
                }
            }

            return $this->response('Alarm stopped Successfully', null, true);
        } else {
            return $this->response('Alarm Details are not available', null, false);
        }
    }

    // public function forwardAlarmEventNotificationByContact($alarm_id,$contactId)
    // {
    //     if ($email != '') {
    //         try {


    //             $response = $response . $this->sendMail($name, $alarm, $email, $alarm_id, $external_cc_email);
    //         } catch (\Exception $e) {
    //             $response = $response . $email . ' - Email  Not sent ' . $e;
    //         }
    //     }

    //     if ($whatsapp_number != '') {
    //         try {

    //             $this->sendWhatsappMessage($name, $alarm, $whatsapp_number, $alarm_id);
    //         } catch (\Exception $e) {
    //             $response = $response . $whatsapp_number . ' - Whatsapp  Not sent  ';
    //         }
    //     }
    // }
    public function createEventNotes(Request $request)
    {


        $request->validate([

            'company_id' => 'required|integer',
            'customer_id' => 'required|integer',
            'alarm_id' => 'required|integer',
            'title' => 'nullable',
            'notes' => 'required',
            'title' => 'nullable',
            'contact_id' => 'nullable',

            'call_status' => 'required',
            'response' => 'nullable',
            'event_status' => 'required',
            'security_id' => 'nullable',


        ]);
        // if ((int)$request->customer_id <= 0 || (int)$request->company_id <= 0) {
        //     return $this->response('Customer Id and Company Ids are not exist', null, false);
        // }
        try {


            $data = []; // $request->all();


            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;

                $request->file('attachment')->move(public_path('/customers/notes'), $fileName);
                $data['picture'] = $fileName;
                unset($data['attachment']);
            }


            $data['company_id'] =  $request->company_id;
            $data['customer_id'] = $request->customer_id;
            $data['alarm_id'] = $request->alarm_id;
            $data['title'] = $request->title;
            $data['notes'] = $request->notes;
            $data['call_status'] = $request->call_status;
            $data['response'] = $request->response;
            $data['event_status'] = $request->event_status;
            $data['security_id'] = $request->security_id;
            $data['contact_id'] = $request->contact_id;
            $data['created_datetime'] = date("Y-m-d H:i:s");


            if ($request->filled("selected_forward_contact_ids")) {
                try {
                    if (count($request->selected_forward_contact_ids)) {
                        AlarmEvents::where("id", $request->alarm_id)->update(["forwarded" => true]);
                        $contacts = CustomerContacts::whereIn('id', $request->selected_forward_contact_ids)->get();
                        (new AlarmNotificationController())->forwardAlarmEventToContactsList($request->alarm_id,  $contacts);
                    } else {
                        AlarmEvents::where("id", $request->alarm_id)->update(["forwarded" => false]);
                    }
                } catch (\Exception) {
                }
            } else {

                AlarmEvents::where("id", $request->alarm_id)->update(["forwarded" => false]);
            }


            if ($request->filled("notes_id")) {

                $record = CustomerAlarmNotes::where("id", $request->notes_id)->update($data);
            } else {

                if ($request->event_status != "Closed") {
                    $record = CustomerAlarmNotes::create($data);


                    $alarmModel = AlarmEvents::where("id", $request->alarm_id);
                    $data2 = [];
                    $data2["alarm_status"] = 1;
                    $alarmModel->update($data2);
                } else if ($request->event_status == "Closed") {
                    if (empty($request->input('pin_number'))) {
                        return [
                            "status" => false,
                            "errors" => ['pin_number' => ['  Primary  Pin is  required.']],
                        ];
                    }
                    if ($request->alarm_id > 0) {
                        $alarmModel = AlarmEvents::where("id", $request->alarm_id);
                        $alarm_start_datetime = $alarmModel->first()->alarm_start_datetime;
                        $device_serial_number = $alarmModel->first()->serial_number;
                        $data2 = [];
                        if ($request->filled("contact_type")) {

                            if ($request->contact_type == 'primary') {

                                $primaryCount = CustomerContacts::where("id", $request->input('contact_id'))
                                    ->where("alarm_stop_pin", (int)$request->input('pin_number'))
                                    ->count();

                                if ($primaryCount == 0) {
                                    return [
                                        "status" => false,
                                        "errors" => ['pin_number' => ['PIN number is not matched']],
                                    ];
                                }

                                if ($primaryCount) {
                                    $data2["pin_verified_by"] = "primary";
                                    $data2["pin_verified_by_id"] = $request->input('contact_id');
                                }
                            }

                            if ($request->contact_type == 'secondary') {

                                $primaryCount = CustomerContacts::where("id", $request->input('contact_id'))
                                    ->where("alarm_stop_pin", (int)$request->input('pin_number'))
                                    ->count();

                                if ($primaryCount == 0) {
                                    return [
                                        "status" => false,
                                        "errors" => ['pin_number' => ['PIN number is not matched']],
                                    ];
                                }

                                if ($primaryCount) {
                                    $data2["pin_verified_by"] = "secondary";
                                    $data2["pin_verified_by_id"] = $request->input('contact_id');
                                }
                            }
                        }

                        ///$data2["event_status"] = "Closed";
                        $data2["alarm_status"] = 0;

                        $data2["alarm_end_manually"] = 1;
                        $data2["alarm_end_datetime"] = date("Y-m-d H:i:s");

                        $datetime1 = new DateTime($alarm_start_datetime);
                        $datetime2 = new DateTime(date("Y-m-d H:i:s"));

                        $interval = $datetime1->diff($datetime2);

                        $data2["response_minutes"] = $interval->i + ($interval->h * 60) + ($interval->days * 1440);


                        $alarmModel->update($data2);
                        //-----------------------------------------------------------------------------------------


                        $record = CustomerAlarmNotes::create($data);



                        //udapte json file
                        (new ApiAlarmDeviceTemperatureLogsController)->createAlarmEventsJsonFile($request->company_id);

                        //turnoff device alarm status
                        if ($device_serial_number != '') {
                            $alarm_event_active_count = AlarmEvents::where("serial_number", $device_serial_number)->where("alarm_status", 1)->count();
                            if ($alarm_event_active_count == 0) {
                                $device_Data = [];
                                $device_Data["alarm_status"] = 0;
                                $device_Data["alarm_end_datetime"] = date('Y-m-d H:i:s');

                                Device::where("serial_number", $device_serial_number)->update($device_Data);
                            }
                        }
                        return $this->response('Alarm stopped Successfully #' . $request->alarm_id, $data2, true);
                    } else {
                        return $this->response('Alarm Details are not available', null, false);
                    }
                }
            }

            if ($record) {
                return $this->response('  Notes   Created.', $record, true);
            } else {
                return $this->response('Notes   Not Created', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function securityAlarmNotesExportExcel(Request $request)
    {
        $model = $this->filterNotes($request);
        $model->orderBy("created_datetime", "asc");
        $reports = $model->get();

        $file_name =  "Alarm Event Id " . $request->alarm_id . ' notes.xlsx';

        return Excel::download((new AlarmEventNotesExport($reports)), $file_name);
    }
    public function securityAlarmNotesDownloadPdf(Request $request)
    {

        $model = $this->filterNotes($request);
        $model->orderBy("created_datetime", "asc");
        $report = $model->get();
        $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first();

        $fileName = "Alarm Event Id " . $request->alarm_id . ' notes.pdf';
        return   Pdf::loadview("alarm_reports/alarm_security_notes", ["reports" => $report, "company" => $company])->setpaper("A4", "potrait")->download($fileName);
    }
    public function securityAlarmNotesPrintPdf(Request $request)
    {

        $model = $this->filterNotes($request);
        $model->orderBy("created_datetime", "asc");
        $report = $model->get();
        $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first();

        return   Pdf::loadview("alarm_reports/alarm_security_notes", ["reports" => $report, "company" => $company])->setpaper("A4", "potrait")->stream("report.pdf");
    }
    public function getAlarmEventsNotes(Request $request)
    {
        $model = $this->filterNotes($request);
        $model->orderBy("created_datetime", "DESC");
        return $model->paginate($request->perPage ?? 10);;
    }
    public function filterNotes($request)
    {
        $model = CustomerAlarmNotes::with(["security", "contact", "alarm.customer"])->where("company_id", $request->company_id)
            ->where("alarm_id", $request->alarm_id);
        $model->when($request->filled("contact_id"), function ($query) use ($request) {
            $query->where("contact_id", $request->contact_id);
        });

        $model->when($request->filled("date_from"), function ($query) use ($request) {
            $query->WhereBetween("created_datetime", [$request->date_from . " 00:00:00", $request->date_to . " 23:59:59"]);
        });

        $model->when($request->filled("common_search"), function ($query) use ($request) {


            $query->where(function ($q) use ($request) {
                $q->where("notes", "ILIKE", "%$request->common_search%")

                    ->orWhere("call_status", "ILIKE", "%$request->common_search%")
                    ->orWhere("response", "ILIKE", "%$request->common_search%")
                    ->orWhere("event_status", "ILIKE", "%$request->common_search%")
                    ->orWherehas("contact", function ($query) use ($request) {

                        $query->where("first_name", "ILIKE",  "%$request->common_search%")
                            ->orWhere("last_name", "ILIKE",  "%$request->common_search%")
                            ->orWhere("phone1", "ILIKE",  "%$request->common_search%");
                    })
                    ->orWherehas("security", function ($query) use ($request) {

                        $query->where("first_name", "ILIKE",  "%$request->common_search%")
                            ->orWhere("last_name", "ILIKE",  "%$request->common_search%");
                    });
            });
        });

        return $model;
    }

    public function getAlarmNotificationsListTechnician(Request $request)
    {
        $model = AlarmEventsTechnician::with([
            "device.customer.primary_contact",
            "device.customer.secondary_contact",
            "notes"
        ])->where('company_id', $request->company_id)->where('alarm_status', 1);



        $model->orderBy("alarm_start_datetime", "DESC");
        return  $events = $model->get();
    }
    public function getAlarmNotificationsList(Request $request)
    {
        //




        try {
            $jsonFilePath = 'alarm-sensors/' . $request->company_id . '_live_events.json';
            $fileContent = Storage::read($jsonFilePath);

            if ($request->filled("filter_customers_list")) {
                $data = json_decode($fileContent, true);

                if ($data) {
                    $filteredData = array_filter($data, function ($item) use ($request) {
                        return in_array($item['customer_id'], $request->filter_customers_list);
                    });
                    $return = [];
                    foreach ($filteredData as $key => $value) {
                        $return[] = $value;
                    }

                    return $return;
                }
            }



            return json_decode($fileContent, true);
        } catch (\Exception $e) {

            return (new ApiAlarmDeviceTemperatureLogsController())->createAlarmEventsJsonFile($request->company_id);
        }



        $model = AlarmEvents::with([
            "device.customer.primary_contact",
            "device.customer.secondary_contact",
            "notes"
        ])->where('company_id', $request->company_id)->where('alarm_status', 1);



        $model->orderBy("alarm_start_datetime", "DESC");
        return  $events = $model->get();
    }
    public function getAlarmEventsMapOperator(Request $request)
    {
        $model = AlarmEvents::with([
            "device.customer.primary_contact",
            "device.customer.secondary_contact",
            "device.customer.latest_alarm_event",
            "device.customer.photos",
            "device.customer.buildingtype",
            "device.customer.cameras",
            "device.sensorzones",
            "zoneData"

        ])

            ->when($request->filled("company_id"), function ($q) use ($request) {


                $q->where("company_id", $request->company_id);
            })
            ->when($request->filled("filterAlarmType"), function ($q) use ($request) {


                $q->where("alarm_type", $request->filterAlarmType);
            })
            ->when($request->filled("alarm_status"), function ($q) use ($request) {
                if ($request->alarm_status == 3) {
                    $q->where("forwarded", true);
                } else if ($request->alarm_status == 0 || $request->alarm_status == 1) {
                    $q->where("alarm_status", $request->alarm_status);
                    //////////$q->where("forwarded", false);
                }
            })->when($request->filled("filterBuildingType"), function ($q) use ($request) {
                $q->whereHas("device.customer", function ($q) use ($request) {
                    $q->where("building_type_id", $request->filterBuildingType);
                });
            })
            ->when($request->filled("filterAlarmStatus"), function ($q) use ($request) {
                if ($request->filterAlarmStatus == 3) {
                    $q->where("forwarded", true);
                } else if ($request->filterAlarmStatus == 0 || $request->filterAlarmStatus == 1) {
                    $q->where("alarm_status", $request->filterAlarmStatus);
                    ///////$q->where("forwarded", false);
                }
            })

            ->when($request->filled("customer_id"), fn($q) => $q->where("customer_id", $request->customer_id));

        $model->when($request->filled("filter_customers_list") && $request->filter_customers_list != '', function ($query) use ($request) {
            $query->whereIn('customer_id', $request->filter_customers_list);
        });

        $model->when($request->filled("eventID"), function ($query) use ($request) {
            $query->where('id', $request->eventID);
        });





        $model->orderBy("alarm_start_datetime",  "desc");




        //$model->orderBy(request('sortBy') ?? "alarm_start_datetime", request('sortDesc') ? "desc" : "asc");

        return   $model->paginate($request->perPage ?? 10);;
    }

    public function getAlarmEventById(Request $request)
    {
        return AlarmEvents::whereId($request->alarm_id)->first();
    }


    public function getAlarmEventsCustomersGroup(Request $request)
    {

        return   $this->getGroupFilter($request);
    }
    public function getGroupFilter($request)
    {
        $customers = Customers::where("company_id", $request->company_id)

            ->when($request->filled("customer_id") && $request->customer_id != '', function ($query) use ($request) {
                $query->where('id', $request->customer_id);
            })
            ->orderBy("building_name", "asc")
            ->get();

        $customerIds = $customers->pluck('id'); // Collect all customer ids in a single array.

        $model = AlarmEvents::whereIn('customer_id', $customerIds);

        if ($request->filled("date_from") && $request->date_from != '') {
            $model->whereBetween('alarm_start_datetime', [$request->date_from . ' 00:00:00', $request->date_to . ' 23:59:59']);
        }



        $model = $model->selectRaw("
       customer_id,
         COALESCE(COUNT(CASE WHEN alarm_events.alarm_type = 'SOS'   THEN 1 END), 0) as sosCount,
        COALESCE(COUNT(CASE WHEN alarm_events.alarm_category = 1 AND alarm_events.alarm_type != 'SOS'   THEN 1 END), 0) as criticalCount,
        COALESCE(COUNT(CASE WHEN alarm_events.alarm_type = 'Offline'  THEN 1 END), 0) as technicalCount,
        COALESCE(COUNT(CASE WHEN alarm_events.alarm_type IS NOT NULL AND alarm_events.alarm_type != 'SOS'   AND alarm_events.alarm_category != 1 THEN 1 END), 0) as eventsCount,
        COALESCE(COUNT(CASE WHEN alarm_events.alarm_category = 2  THEN 1 END), 0) as mediumCount,
        COALESCE(COUNT(CASE WHEN alarm_events.alarm_category = 3  THEN 1 END), 0) as lowCount,
        COALESCE(COUNT(CASE WHEN alarm_events.alarm_type = 'Temperature' THEN 1 END), 0) as temperatureCount,
        COALESCE(COUNT(CASE WHEN alarm_events.alarm_type = 'Water' THEN 1 END), 0) as waterCount,
        COALESCE(COUNT(CASE WHEN alarm_events.alarm_type = 'Medical' THEN 1 END), 0) as medicalCount,
        COALESCE(COUNT(CASE WHEN alarm_events.alarm_type = 'Fire' THEN 1 END), 0) as fireCount
    ")
            ->groupBy("customer_id");


        $counts = $model->get();



        // Map the counts back to customers
        $customers->map(function ($customer) use ($counts) {
            $customer->counts = $counts->where('customer_id', $customer->id)->first();


            if (!$customer->counts) {
                $customer->counts =  [
                    "soscount" => 0,
                    "criticalcount" => 0,
                    "technicalcount" => 0,
                    "eventscount" => 0,
                    "mediumcount" => 0,
                    "lowcount" => 0,
                    "temperaturecount" => 0,
                    "watercount" => 0,
                    "medicalcount" => 0,
                    "firecount" => 0,
                ];
            }



            return $customer;
        });

        return ["data" => $customers];
    }
    public function getAlarmEventsTechnician(Request $request)
    {
        $model = AlarmEventsTechnician::with([
            "device.customer.primary_contact",
            "device.customer.secondary_contact",
            "device.company.user",
            "notes.contact",
            "category",
            "device.customer.buildingtype",
            "zoneData",
            "security",
            "pinverifiedby",
            "technician",


        ])->where('alarm_events_technicians.company_id', $request->company_id)


            ->when($request->filled("alarm_status"), function ($q) use ($request) {




                if ($request->alarm_status == 3) {

                    $q->where("forwarded", true);

                    // $q->wherehas('notes', function ($qq) use ($request) {


                    //     $qq->where("event_status", 'Forwarded');
                    // });
                } else if ($request->alarm_status == 0 || $request->alarm_status == 1) {
                    $q->where("alarm_status", $request->alarm_status);
                    ////////$q->where("forwarded", false);

                    // $q->wherehas('notes', function ($qq) use ($request) {


                    //     $qq->where("event_status", 'Forwarded');
                    // });
                }
            })
            // ->when($request->filled("filterSupervisor"), function ($q) use ($request) {
            //     $q->where("forwarded", true);
            // })

            ->when($request->filled("filterResponseInMinutes"), function ($query) use ($request) {
                if ((int) $request->filterResponseInMinutes == 0)
                    $query->where("response_minutes", '>', 10);
                else
                if ((int) $request->filterResponseInMinutes == 1)
                    $query->where("response_minutes", '<=', 1);
                else
                if ((int) $request->filterResponseInMinutes == 5)

                    $query->where("response_minutes", '>=', 1)->where("response_minutes", '<=', 5);

                else
                    if ((int) $request->filterResponseInMinutes == 10)

                    $query->where("response_minutes", '>=', 5)->where("response_minutes", '<=', 10);
            })
            ->when($request->filled("customer_id"), fn($q) => $q->where("customer_id", $request->customer_id));
        if ($request->filled("date_from") && $request->date_from != '') {
            $model->whereBetween('alarm_start_datetime', [$request->date_from . ' 00:00:00', $request->date_to . ' 23:59:59']);
        }
        $model->when($request->filled("filter_customers_list") && $request->filter_customers_list != '', function ($query) use ($request) {
            $query->whereIn('customer_id', $request->filter_customers_list);
        });

        $model->when($request->filled("customer_id") && $request->customer_id != '', function ($query) use ($request) {
            $query->where('customer_id', $request->customer_id);
        });

        $model->when($request->filled("filter_date") && $request->filter_date != '', function ($query) use ($request) {
            $query->whereDate('alarm_start_datetime', $request->filter_date);
        });


        $model->when($request->filled("filter_alarm_type") && $request->filter_alarm_type != '', function ($query) use ($request) {


            $query->where('alarm_type',   $request->filter_alarm_type);
            // if ($request->filter_alarm_type == 'non-sos')
            //     $query->where('alarm_type', "!=", "SOS");

            // if ($request->filter_alarm_type == 'sos')
            //     $query->where('alarm_type',   "SOS");
        });


        $model->when($request->filled('filterSensorname') && $request->filterSensorname != '', function ($q) use ($request) {

            $q->Where('alarm_type', 'ILIKE', "%$request->filterSensorname%");
        });
        $model->when($request->filled('filterDeviceType') && $request->filterDeviceType != '', function ($q) use ($request) {

            $q->WhereHas('device',  function ($q) use ($request) {
                $q->where('device_type', 'ILIKE', "%$request->filterDeviceType%");
            });
        });

        $model->when($request->filled('common_search') && $request->common_search != '', function ($q) use ($request) {
            $q->where(function ($q) use ($request) {

                //$q->Where('serial_number', 'ILIKE', "$request->common_search%");
                $q->Where('id',  'ILIKE', "%$request->common_search");
                $q->orWhere('alarm_type', 'ILIKE', "%$request->common_search%");
                //$q->orWhere('alarm_category', 'ILIKE', "%$request->common_search%");
                $q->orWhere('zone', 'ILIKE', "%$request->common_search%");
                $q->orWhere('area', 'ILIKE', "%$request->common_search%");

                $q->orWhereHas('device.customer.primary_contact', fn(Builder $query) => $query->where('first_name', 'ILIKE', "$request->common_search%")
                    ->orWhere('last_name', 'ILIKE', "$request->common_search%"));

                $q->orWhereHas(
                    'device.customer',
                    fn(Builder $query) => $query->where('city', 'ILIKE', "$request->common_search%")
                );
                $q->orWhereHas(
                    'device.customer.buildingtype',
                    fn(Builder $query) => $query->where('name', 'ILIKE', "$request->common_search%")
                );
                $q->orWhereHas(
                    'category',
                    fn(Builder $query) => $query->where('name', 'ILIKE', "$request->common_search%")
                );
                $q->orWhereHas('device', fn(Builder $query) => $query->where('name', 'ILIKE', "$request->common_search%")->where('company_id', $request->company_id));
                $q->orWhereHas('device', fn(Builder $query) => $query->where('device_type', 'ILIKE', "$request->common_search%")->where('company_id', $request->company_id));
                $q->orWhereHas('device', fn(Builder $query) => $query->where('location', 'ILIKE', "$request->common_search%")->where('company_id', $request->company_id));

                $q->when(
                    !$request->filled("customer_id"),
                    function ($quqery) use ($request) {
                        $quqery->orWhereHas('device.customer', fn(Builder $query) => $query->where('building_name', 'ILIKE', "$request->common_search%")
                            ->orWhere('area', 'ILIKE', "$request->common_search%")
                            ->orWhere('city', 'ILIKE', "$request->common_search%")

                            ->where('company_id', $request->company_id));
                    }

                );
            });
        });


        $model->orderBy(request('sortBy') ?? "alarm_start_datetime", request('sortDesc') ? "desc" : "desc");

        return $model->paginate($request->perPage ?? 10);;
    }

    public function getAlarmEvents(Request $request)
    {
        $model = $this->filter($request);


        $model->orderBy(request('sortBy') ?? "alarm_start_datetime", request('sortDesc') ? "desc" : "desc");

        return $model->paginate($request->perPage ?? 10);;
    }
    public function filter($request)
    {
        $model = AlarmEvents::with([
            "device.customer.primary_contact",
            "device.customer.secondary_contact",
            "device.company.user",
            "notes.contact",
            "category",
            "device.customer.buildingtype",
            "zoneData",
            "security",
            "pinverifiedby",


        ])->where('alarm_events.company_id', $request->company_id)


            ->when($request->filled("alarm_status"), function ($q) use ($request) {




                if ($request->alarm_status == 3) {

                    $q->where("forwarded", true);

                    // $q->wherehas('notes', function ($qq) use ($request) {


                    //     $qq->where("event_status", 'Forwarded');
                    // });
                } else if ($request->alarm_status == 0 || $request->alarm_status == 1) {
                    $q->where("alarm_status", $request->alarm_status);
                    ////////$q->where("forwarded", false);

                    // $q->wherehas('notes', function ($qq) use ($request) {


                    //     $qq->where("event_status", 'Forwarded');
                    // });
                }
            })
            // ->when($request->filled("filterSupervisor"), function ($q) use ($request) {
            //     $q->where("forwarded", true);
            // })

            ->when($request->filled("filterResponseInMinutes"), function ($query) use ($request) {
                if ((int) $request->filterResponseInMinutes == 0)
                    $query->where("response_minutes", '>', 10);
                else
                if ((int) $request->filterResponseInMinutes == 1)
                    $query->where("response_minutes", '<=', 1);
                else
                if ((int) $request->filterResponseInMinutes == 5)

                    $query->where("response_minutes", '>=', 1)->where("response_minutes", '<=', 5);

                else
                    if ((int) $request->filterResponseInMinutes == 10)

                    $query->where("response_minutes", '>=', 5)->where("response_minutes", '<=', 10);
            })
            ->when($request->filled("customer_id"), fn($q) => $q->where("customer_id", $request->customer_id));
        if ($request->filled("date_from") && $request->date_from != '') {
            $model->whereBetween('alarm_start_datetime', [$request->date_from . ' 00:00:00', $request->date_to . ' 23:59:59']);
        }
        $model->when($request->filled("filter_customers_list") && $request->filter_customers_list != '', function ($query) use ($request) {
            $query->whereIn('customer_id', $request->filter_customers_list);
        });

        $model->when($request->filled("customer_id") && $request->customer_id != '', function ($query) use ($request) {
            $query->where('customer_id', $request->customer_id);
        });

        $model->when($request->filled("filter_date") && $request->filter_date != '', function ($query) use ($request) {
            $query->whereDate('alarm_start_datetime', $request->filter_date);
        });


        $model->when($request->filled("filter_alarm_type") && $request->filter_alarm_type != '', function ($query) use ($request) {


            $query->where('alarm_type',   $request->filter_alarm_type);
            // if ($request->filter_alarm_type == 'non-sos')
            //     $query->where('alarm_type', "!=", "SOS");

            // if ($request->filter_alarm_type == 'sos')
            //     $query->where('alarm_type',   "SOS");
        });


        $model->when($request->filled('filterSensorname') && $request->filterSensorname != '', function ($q) use ($request) {

            $q->Where('alarm_type', 'ILIKE', "%$request->filterSensorname%");
        });
        $model->when($request->filled('filterDeviceType') && $request->filterDeviceType != '', function ($q) use ($request) {

            $q->WhereHas('device',  function ($q) use ($request) {
                $q->where('device_type', 'ILIKE', "%$request->filterDeviceType%");
            });
        });

        $model->when($request->filled('common_search') && $request->common_search != '', function ($q) use ($request) {
            $q->where(function ($q) use ($request) {

                //$q->Where('serial_number', 'ILIKE', "$request->common_search%");
                $q->Where('id',  'ILIKE', "%$request->common_search");
                $q->orWhere('alarm_type', 'ILIKE', "%$request->common_search%");
                //$q->orWhere('alarm_category', 'ILIKE', "%$request->common_search%");
                $q->orWhere('zone', 'ILIKE', "%$request->common_search%");
                $q->orWhere('area', 'ILIKE', "%$request->common_search%");

                $q->orWhereHas('device.customer.primary_contact', fn(Builder $query) => $query->where('first_name', 'ILIKE', "$request->common_search%")
                    ->orWhere('last_name', 'ILIKE', "$request->common_search%"));

                $q->orWhereHas(
                    'device.customer',
                    fn(Builder $query) => $query->where('city', 'ILIKE', "$request->common_search%")
                );
                $q->orWhereHas(
                    'device.customer.buildingtype',
                    fn(Builder $query) => $query->where('name', 'ILIKE', "$request->common_search%")
                );
                $q->orWhereHas(
                    'category',
                    fn(Builder $query) => $query->where('name', 'ILIKE', "$request->common_search%")
                );
                $q->orWhereHas('device', fn(Builder $query) => $query->where('name', 'ILIKE', "$request->common_search%")->where('company_id', $request->company_id));
                $q->orWhereHas('device', fn(Builder $query) => $query->where('device_type', 'ILIKE', "$request->common_search%")->where('company_id', $request->company_id));
                $q->orWhereHas('device', fn(Builder $query) => $query->where('location', 'ILIKE', "$request->common_search%")->where('company_id', $request->company_id));

                $q->when(
                    !$request->filled("customer_id"),
                    function ($quqery) use ($request) {
                        $quqery->orWhereHas('device.customer', fn(Builder $query) => $query->where('building_name', 'ILIKE', "$request->common_search%")
                            ->orWhere('area', 'ILIKE', "$request->common_search%")
                            ->orWhere('city', 'ILIKE', "$request->common_search%")

                            ->where('company_id', $request->company_id));
                    }

                );
            });
        });

        return $model;
    }
    public function getAlarmLogs(Request $request)
    {
        // return  $request->validate([
        //     'date_from' => 'required|date|before_or_equal:date_to',
        //     'date_to' => 'required|date|after_or_equal:date_from',
        //     'company_id' => 'required|integer',
        //     'customer_id' => 'required|integer'
        // ]);
        $model = AlarmLogs::with(["device.customer"])->where('company_id', $request->company_id)
            ->where('customer_id', $request->customer_id)
            // ->when($request->filled("serial_number"), fn ($q) => $q->when("serial_number", $request->serial_number))
            ->whereBetween('log_time', [$request->date_from . ' 00:00:00', $request->date_to . ' 23:59:59'])
            ->where('alarm_status', 1)
            ->orderBy("log_time", "asc");
        return $model->orderByDesc('id')->paginate($request->perPage);;
    }
}
