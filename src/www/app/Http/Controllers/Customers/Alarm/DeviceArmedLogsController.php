<?php

namespace App\Http\Controllers\Customers\Alarm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Customers\CustomersController;
use App\Models\AlarmEvents;
use App\Models\DeviceArmedLogs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceArmedLogsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $model = $this->filter($request);

        $model->withCount([
            'alarm_events'
        ]);
        $model->with([
            'alarm_events',
            'device.customer' => function ($query) {
                $query->without(['all_alarm_events', 'user', 'devices', 'contacts', 'profile_pictures',]);
            }
        ]);
        $model->whereHas('alarm_events', function ($query) {
            $query->whereColumn('alarm_start_datetime', '>=', 'device_armed_logs.armed_datetime')
                ->whereColumn('alarm_start_datetime', '<=', 'device_armed_logs.disarm_datetime');
        });

        return $model->paginate($request->perPage ?? 10);;
    }

    public function report(Request $request)
    {

        return  $this->reportProcess($request);
    }
    public function reportProcess($request)
    {
        // Parse start and end dates
        $startDate = Carbon::parse($request->input('date_from'))->startOfDay();
        $endDate = Carbon::parse($request->input('date_to'))->endOfDay();

        // Retrieve armed logs for the given company within the date range
        $armedLogs = DeviceArmedLogs::with([
            'device.customer' => function ($query) {
                $query->without(["primary_contact", "secondary_contact", "profile_pictures", 'all_alarm_events', 'user', 'devices', 'contacts', 'profilePictures']);
            }
        ])
            ->whereHas("device.customer")
            ->where("company_id", $request->company_id)
            ->when($request->filled("filter_customer_id"), function ($query) use ($request) {
                $query->whereHas("device.customer", function ($query) use ($request) {
                    $query->where('id', $request->filter_customer_id);
                });
            })
            ->whereBetween('armed_datetime', [$startDate, $endDate])
            ->orderBy('armed_datetime')
            ->get();

        // Calculate number of days in the date range
        $daysInRange = $startDate->diffInDays($endDate) + 1;

        // Retrieve event logs for the given company within the date range
        $eventLogs = AlarmEvents::with([

            'device.customer' => function ($query) {
                $query->without(["primary_contact", "secondary_contact", "profilePictures", 'all_alarm_events', 'user', 'devices', 'contacts', 'profilePictures']);
            }
        ])
            ->where("company_id", $request->company_id)
            ->when($request->filled("filter_customer_id"), function ($query) use ($request) {
                $query->whereHas("device.customer", function ($query) use ($request) {
                    $query->where('id', $request->filter_customer_id);
                });
            })
            ->whereBetween('alarm_start_datetime', [$startDate, $endDate])
            ->orderBy('alarm_start_datetime')
            ->get();

        // Group logs by date and customer, counting total and SOS events
        $armedLogsByDateAndCustomer = $armedLogs->groupBy(function ($log) {
            return Carbon::parse($log->armed_datetime)->format('Y-m-d') . '_' . optional($log->device->customer)->id;
        });

        $eventLogsByDateAndCustomer = $eventLogs->groupBy(function ($log) {

            $date = Carbon::parse($log->alarm_start_datetime)->format('Y-m-d');
            $customerId = optional(optional($log->device)->customer)->id ?? 'no_customer';
            return "{$date}_{$customerId}";
        })->map(function ($logs) {

            // return [
            //     'total_events' => $logs->where('alarm_type', '!=', 'SOS')->count(),
            //     'sos_count' => $logs->where('alarm_type', 'SOS')->count(),
            //     'customer' => $logs,
            // ];
            $countArray = [];
            $countArray['total_events'] =  $logs->count();
            $countArray['customer'] =    $logs;

            foreach ((new CustomersController())->alarmTypes()  as  $value) {
                $countArray[($value['id']) . '_count'] = $logs->where('alarm_type', $value['id'])->count();
            }

            return $countArray;
        });

        // Initialize the report array
        $report = [];


        $mergedArray = array_merge($armedLogsByDateAndCustomer->toArray(), $eventLogsByDateAndCustomer->toArray());

        // Generate report data
        foreach (range(0, $daysInRange - 1) as $i) {
            $currentDate = $startDate->copy()->addDays($i)->format('Y-m-d');
            $customers = [];

            $alarmEventCountTotal = 0;


            foreach ($mergedArray as $key => $logs) {



                list($date, $customerId) = explode('_', $key);

                if ($date === $currentDate) {
                    if (!isset($customers[$customerId])) {


                        $alarmEventCountTotal = $logs['total_events'] ?? 0;
                        //$sos_count = $logs['total_events'] ?? 0;

                        //$alarmEventCountTotal += $total_events + $sos_count;
                        if ($alarmEventCountTotal == 0 && $request->only_show_alarms == 'true') continue;



                        $customerData = [
                            'customer' => $logs[0]["device"]["customer"]["building_name"] ?? $logs["customer"][0]["customer"]["building_name"],
                            'city' => $logs[0]["device"]["customer"]["city"] ??   $logs["customer"][0]["customer"]["city"],
                            'customer_id' => $customerId,
                            'armed' => [],
                            'events_count' => $logs['total_events'] ?? 0,
                            // 'sos_count' => $logs['sos_count'] ?? 0,

                        ];

                        foreach ((new CustomersController())->alarmTypes()  as  $value) {
                            if (isset($logs[$value['id']  . '_count']))
                                $customerData[($value['id']) . '_count'] = $logs[$value['id']  . '_count'];
                            else
                                $customerData[($value['id']) . '_count'] = 0;
                        }

                        // foreach ((new CustomersController())->alarmTypes()  as  $value) {
                        //     $customerData[($value['name']) . '_count'] = $logs->where('alarm_type', $value['name'])->count();
                        // }


                        // Add armed logs if they exist
                        if (isset($armedLogsByDateAndCustomer[$key])) {
                            foreach ($armedLogsByDateAndCustomer[$key] as $log) {
                                $customerData['armed'][] = [
                                    'armed_datetime' => Carbon::parse($log->armed_datetime)->format('Y-m-d H:i:s'),
                                    'disarm_datetime' => $log->disarm_datetime
                                        ? Carbon::parse($log->disarm_datetime)->format('Y-m-d H:i:s')
                                        : null,

                                    'events_count' =>   0,
                                    'sos_count' =>  0,
                                ];
                            }
                        }

                        $customers[$customerId] = $customerData;
                    }
                }
            }

            // Add the current date and its customers to the report
            if ($request->only_show_alarms == 'true') {
                if ($alarmEventCountTotal > 0) {
                    $report[] = [
                        'date' => $currentDate,
                        'customers' => array_values($customers),
                    ];
                }
            } else {
                $report[] = [
                    'date' => $currentDate,
                    'customers' => array_values($customers),
                ];
            }
        }

        return $report;
    }
    public function filter($request)
    {
        $model = DeviceArmedLogs::with(["device"])->where("company_id", $request->company_id);


        $model->when($request->filled("customer_id"), function ($query) use ($request) {
            $query->whereHas("device", function ($q) use ($request) {
                $q->where("customer_id", $request->customer_id);
            });
        });


        $model->when($request->filled("common_search"), function ($q) use ($request) {
            $q->whereHas(
                "device",
                function ($qq) use ($request) {
                    $qq->where("name", "ILIKE", "%$request->common_search%");
                }
            );
        });
        if ($request->filled("date_from")) {
            // $model->whereBetween('armed_datetime', [$request->date_from . ' 00:00:00', $request->date_to . ' 23:59:59']);


            $model->where(function ($query) use ($request) {
                $query->whereBetween('armed_datetime', [$request->date_from . ' 00:00:00', $request->date_to . ' 23:59:59'])
                    ->orWhereBetween('disarm_datetime', [$request->date_from . ' 00:00:00', $request->date_to . ' 23:59:59']);
            });
        }
        return  $model->orderBy("armed_datetime", "DESC");
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
     * @param  \App\Models\DeviceArmedLogs  $deviceArmedLogs
     * @return \Illuminate\Http\Response
     */
    public function show(DeviceArmedLogs $deviceArmedLogs)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DeviceArmedLogs  $deviceArmedLogs
     * @return \Illuminate\Http\Response
     */
    public function edit(DeviceArmedLogs $deviceArmedLogs)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DeviceArmedLogs  $deviceArmedLogs
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DeviceArmedLogs $deviceArmedLogs)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DeviceArmedLogs  $deviceArmedLogs
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeviceArmedLogs $deviceArmedLogs)
    {
        //
    }
}
