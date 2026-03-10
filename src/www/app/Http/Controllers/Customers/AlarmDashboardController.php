<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\UpdateRequest;
use App\Http\Requests\Customer\StoreRequest;
use App\Models\AlarmEvents;
use App\Models\Customers\CustomerContacts;
use App\Models\Customers\Customers;
use App\Models\CustomersBuildingTypes;
use App\Models\Deivices\DeviceZones;
use App\Models\Device;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use function Aws\filter;

class AlarmDashboardController extends Controller
{

    public function dashboardStatisctsCustomers(Request $request)
    {

        $oneDevice = Device::with("company")->where("company_id", $request->company_id)->first();


        $totalCustomers = Customers::where("company_id", $request->company_id)->where("deleted", 0)

            ->when($request->filled("filter_customers_list"), function ($q) use ($request) {
                $q->whereIn("id", $request->filter_customers_list);
            })
            ->when($request->filled("customer_id"), function ($q) use ($request) {
                $q->where("id", $request->customer_id);
            })
            ->count();
        $deviceCounts = Device::where("company_id", $request->company_id)

            // ->whereHas('customer', function ($query) {
            //     $query->where('deleted', 0);
            // })
            ->where("device_type", "!=", "Mobile")

            ->where("device_type", "!=", "Manual")
            ->where("device_id", "!=", "Manual")
            ->where("serial_number", "!=", null)

            ->when($request->filled("filter_customers_list"), function ($q) use ($request) {
                $q->whereIn("customer_id", $request->filter_customers_list);
            })
            ->when($request->filled("customer_id"), function ($q) use ($request) {
                $q->where("customer_id", $request->customer_id);
            })



            ->selectRaw("
        COUNT(CASE WHEN armed_status = 1 THEN 1 END) as armedCount,
        COUNT(CASE WHEN armed_status = 0 THEN 1 END) as disarmCount,
        COUNT(CASE WHEN status_id = 1 THEN 1 END) as onlineCount,
        COUNT(CASE WHEN status_id = 2 THEN 1 END) as offlineCount
    ")->first();

        $alarmCounts = 0;
        if ($oneDevice) {
            $alarmCounts = AlarmEvents::where("company_id", $request->company_id)
                ->where('created_at', '>=', Carbon::now()->subDays($oneDevice->company->dashboard_alarm_open_count_days))
                ->selectRaw("
    COUNT(CASE WHEN alarm_status = 1 THEN 1 END) as openCount,
    COUNT(CASE WHEN alarm_status = 0 THEN 1 END) as closedCount,
    COUNT(CASE WHEN forwarded = TRUE AND alarm_status = 1 THEN 1 END) as forwardCount
")

                //->whereDate("alarm_start_datetime", $request->date)

                ->when($request->filled("filter_customers_list"), function ($q) use ($request) {
                    $q->whereIn("customer_id", $request->filter_customers_list);
                })



                ->first();
        }


        $recentAlarm = AlarmEvents::where("company_id", $request->company_id)->orderby("alarm_start_datetime", "desc")->first();





        return  [
            "customersCount" => $totalCustomers,
            "armedCount" => $deviceCounts->armedcount,
            "disarmCount" => $deviceCounts->disarmcount,
            "onlineCount" => $deviceCounts->onlinecount,
            "offlineCount" => $deviceCounts->offlinecount,


            "openCount" => $alarmCounts->opencount - $alarmCounts->forwardcount,
            "closedCount" => $alarmCounts->closedcount,
            "forwardCount" => $alarmCounts->forwardcount ??  0,
            "oneDevice" => $oneDevice,
            "recentAlarm" => $recentAlarm,


        ];
    }
    public function dashboardStatisctsHourlyRange(Request $request)
    {
        //         return json_decode('[
        //   {
        //     "hour": 0,
        //     "sosCount": 1,
        //     "highCount": 1,
        //     "mediumCount": 1,
        //     "lowCount": 1
        //   },
        //   {
        //     "hour": 1,
        //     "sosCount": 0,
        //     "highCount": 0,
        //     "mediumCount": 0,
        //     "lowCount": 0
        //   },
        //   {
        //     "hour": 2,
        //     "sosCount": 3,
        //     "highCount":4,
        //     "mediumCount":5,
        //     "lowCount": 6
        //   },
        //   {
        //     "hour": 3,
        //     "sosCount": 7,
        //     "highCount":7,
        //     "mediumCount":8,
        //     "lowCount": 10
        //   },
        //   {
        //     "hour": 4,
        //     "sosCount": 1,
        //     "highCount": 2,
        //     "mediumCount": 3,
        //     "lowCount": 4
        //   },
        //   {
        //     "hour": 5,
        //     "sosCount": 5,
        //     "highCount":6,
        //     "mediumCount": 7,
        //     "lowCount": 8
        //   },
        //   {
        //     "hour": 6,
        //     "sosCount":  1,
        //     "highCount":  2,
        //     "mediumCount":  1,
        //     "lowCount":  1
        //   },
        //   {
        //     "hour": 7,
        //     "sosCount": 5,
        //     "highCount": 0,
        //     "mediumCount": 0,
        //     "lowCount": 0
        //   },
        //   {
        //     "hour": 8,
        //     "sosCount": 5,
        //     "highCount": 0,
        //     "mediumCount": 0,
        //     "lowCount": 0
        //   },
        //   {
        //     "hour": 9,
        //     "sosCount": 5,
        //     "highCount": 0,
        //     "mediumCount": 0,
        //     "lowCount": 0
        //   },
        //   {
        //     "hour": 10,
        //     "sosCount": 5,
        //     "highCount": 0,
        //     "mediumCount": 0,
        //     "lowCount": 0
        //   },
        //   {
        //     "hour": 11,
        //     "sosCount": 5,
        //     "highCount": 0,
        //     "mediumCount": 0,
        //     "lowCount": 0
        //   },
        //   {
        //     "hour": 12,
        //     "sosCount": 5,
        //     "highCount": 0,
        //     "mediumCount": 0,
        //     "lowCount": 0
        //   },
        //   {
        //     "hour": 13,
        //     "sosCount": 5,
        //     "highCount": 0,
        //     "mediumCount": 0,
        //     "lowCount": 0
        //   },
        //   {
        //     "hour": 14,
        //     "sosCount": 5,
        //     "highCount": 0,
        //     "mediumCount": 0,
        //     "lowCount": 0
        //   },
        //   {
        //     "hour": 15,
        //     "sosCount": 5,
        //     "highCount": 0,
        //     "mediumCount": 0,
        //     "lowCount": 0
        //   },
        //   {
        //     "hour": 16,
        //     "sosCount": 0,
        //     "highCount": 0,
        //     "mediumCount": 0,
        //     "lowCount": 0
        //   },
        //   {
        //     "hour": 17,
        //     "sosCount": 0,
        //     "highCount": 0,
        //     "mediumCount": 0,
        //     "lowCount": 0
        //   },
        //   {
        //     "hour": 18,
        //     "sosCount": 0,
        //     "highCount": 0,
        //     "mediumCount": 0,
        //     "lowCount": 0
        //   },
        //   {
        //     "hour": 19,
        //     "sosCount": 0,
        //     "highCount": 0,
        //     "mediumCount": 0,
        //     "lowCount": 0
        //   },
        //   {
        //     "hour": 20,
        //     "sosCount": 0,
        //     "highCount": 0,
        //     "mediumCount": 0,
        //     "lowCount": 0
        //   },
        //   {
        //     "hour": 21,
        //     "sosCount": 0,
        //     "highCount": 0,
        //     "mediumCount": 0,
        //     "lowCount": 0
        //   },
        //   {
        //     "hour": 22,
        //     "sosCount": 0,
        //     "highCount": 0,
        //     "mediumCount": 0,
        //     "lowCount": 0
        //   },
        //   {
        //     "hour": 23,
        //     "sosCount": 0,
        //     "highCount": 0,
        //     "mediumCount": 0,
        //     "lowCount": 0
        //   }
        // ]');


        $date_from = $request->date_from;
        $date_to = $request->date_to;

        for ($i = 0; $i < 24; $i++) {

            $j = $i;

            $j = $i <= 9 ? "0" . $i : $i;


            $counts = AlarmEvents::where("company_id", $request->company_id)->selectRaw("
                COUNT(CASE WHEN alarm_type = 'SOS' THEN 1   END) as sosCount,
                COUNT(CASE WHEN alarm_category = 1 AND alarm_type != 'SOS' THEN 1  END) as crititalCount,
                COUNT(CASE WHEN alarm_category = 2 AND alarm_type != 'SOS' THEN 1   END) as mediumCount,
                COUNT(CASE WHEN alarm_category = 3 AND alarm_type != 'SOS' THEN 1   END) as lowCount
            ")->where('alarm_start_datetime', '>=', $date_from . ' ' . $j . ':00:00')
                ->where('alarm_start_datetime', '<=', $date_to  . ' ' . $j . ':59:59')
                ->when($request->filled("filter_customers_list"), function ($model) use ($request) {
                    $model->whereIn('customer_id', $request->filter_customers_list);
                })
                ->first();
            $finalarray[] = [
                "hour" => $i,
                "sosCount" => $counts->soscount,
                "highCount" => $counts->crititalcount,
                "mediumCount" => $counts->mediumcount,
                "lowCount" => $counts->lowcount,
            ];
        }

        return $finalarray;
    }
    public function dashboardAlarmStatistics(Request $request)
    {
        $statistics = DB::table('alarm_events')
            ->where('company_id', $request->company_id)
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                $query->where('customer_id', $request->customer_id);
            })
            ->selectRaw('
            COUNT(CASE WHEN alarm_status = 1 AND forwarded = false THEN 1 END) AS open ,
            COUNT(CASE WHEN alarm_status = 1 AND forwarded = true THEN 1 END) AS forwarded,
            COUNT(CASE WHEN alarm_status = 0 THEN 1 END) AS closed,
            COUNT(CASE WHEN alarm_category = 1 THEN 1 END) AS high,
            COUNT(CASE WHEN alarm_category = 2 THEN 1 END) AS medium,
            COUNT(CASE WHEN alarm_category = 3 THEN 1 END) AS low
        ')
            ->first();

        return $statistics;
    }
    public function dashboardOpenAlarmList(Request $request)
    {
        return AlarmEvents::with(["customer", "notificationicon"])->where("company_id", $request->company_id)
            //->where("alarm_status", 1)
            ->when($request->filled("customer_id"), function ($q) use ($request) {
                $q->where("customer_id", $request->customer_id);
            })
            ->when($request->filled("customer_id"), function ($q) use ($request) {
                $q->where("customer_id", $request->customer_id);
            })
            ->when($request->filled("filter_customers_list"), function ($model) use ($request) {
                $model->whereIn('customer_id', $request->filter_customers_list);
            })
            ->orderBy("alarm_start_datetime", "desc")->limit(10)
            ->get();
    }
    public function dashboardEventsStatisctsDateRangeHistory(Request $request)
    {
        $finalarray = [];
        $dateStrings = [];
        if ($request->has("date_from") && $request->has("date_to")) {
            // Usage example:
            $startDate = new DateTime($request->date_from . ' 00:00:00');
            $endDate = new DateTime($request->date_to . ' 23:59:59');

            $dateStrings = $this->createDateRangeArray($startDate, $endDate);
        } else {
            for ($i = 6; $i >= 0; $i--) {
                $dateStrings[] = date('Y-m-d', strtotime(date('Y-m-d') . '-' . $i . ' days'));
            }
        };

        foreach ($dateStrings as $key => $date) {

            $counts = AlarmEvents::where("company_id", $request->company_id)


                ->when($request->filled("customer_id"), function ($q) use ($request) {
                    $q->where("customer_id", $request->customer_id);
                })



                ->whereDate("alarm_start_datetime", $date)

                ->when($request->filled("filter_customers_list"), function ($model) use ($request) {
                    $model->whereIn('customer_id', $request->filter_customers_list);
                })


                ->count();
            $finalarray[] = [
                "date" => $date,
                "count" => $counts ?? 0,




            ];
        }


        return  $finalarray;
    }
    public function dashboardStatisctsDateRangeHistory(Request $request)
    {
        $finalarray = [];
        $dateStrings = [];
        if ($request->has("date_from") && $request->has("date_to")) {
            // Usage example:
            $startDate = new DateTime($request->date_from . ' 00:00:00');
            $endDate = new DateTime($request->date_to . ' 23:59:59');

            $dateStrings = $this->createDateRangeArray($startDate, $endDate);
        } else {
            for ($i = 6; $i >= 0; $i--) {
                $dateStrings[] = date('Y-m-d', strtotime(date('Y-m-d') . '-' . $i . ' days'));
            }
        }


        foreach ($dateStrings as $key => $date) {

            $counts = AlarmEvents::where("company_id", $request->company_id)


                ->when($request->filled("customer_id"), function ($q) use ($request) {
                    $q->where("customer_id", $request->customer_id);
                })


                ->selectRaw("
                COUNT(CASE WHEN alarm_type = 'SOS'    THEN 1 END) as sosCount,
               COUNT(CASE WHEN alarm_category = 1 AND alarm_type != 'SOS'   THEN 1 END) as criticalCount,

                COUNT(CASE WHEN alarm_type = 'Offline' THEN 1 END) as technicalCount,
                COUNT(CASE WHEN alarm_type IS NOT NULL  AND alarm_type != 'SOS'  AND alarm_category != 1 THEN 1 END) as eventsCount,
                COUNT(CASE WHEN alarm_category = 2   THEN 1 END) as mediumCount,
                COUNT(CASE WHEN alarm_category = 3   THEN 1 END) as lowCount,

  COUNT(CASE WHEN alarm_type = 'Temperature'   THEN 1 END) as temperatureCount,
                COUNT(CASE WHEN alarm_type = 'Water'   THEN 1 END) as waterCount,
                COUNT(CASE WHEN alarm_type = 'Medical'   THEN 1 END) as medicalCount,
                COUNT(CASE WHEN alarm_type = 'Fire'   THEN 1 END) as fireCount



            ")
                ->whereDate("alarm_start_datetime", $date)

                ->when($request->filled("filter_customers_list"), function ($model) use ($request) {
                    $model->whereIn('customer_id', $request->filter_customers_list);
                })


                ->first();
            $finalarray[] = [
                "date" => $date,
                "sosCount" => $counts->soscount ?? 0,
                "highCount" => $counts->criticalcount ?? 0,
                "criticalCount" => $counts->criticalcount ?? 0,
                "technicalCount" => $counts->technicalcount ?? 0,
                "eventsCount" => $counts->eventscount ?? 0,
                "mediumCount" => $counts->mediumcount ?? 0,
                "temperatureCount" => $counts->temperaturecount ?? 0,
                "waterCount" => $counts->watercount ?? 0,
                "medicalCount" => $counts->medicalcount ?? 0,
                "fireCount" => $counts->firecount ?? 0,
                "lowCount" => $counts->lowcount ?? 0,



            ];
        }


        return  $finalarray;
    }
    public function dashboardStatisctsDateRange(Request $request)
    {
        $finalarray = [];
        $dateStrings = [];
        if ($request->has("date_from") && $request->has("date_to")) {
            // Usage example:
            $startDate = new DateTime($request->date_from . ' 00:00:00');
            $endDate = new DateTime($request->date_to . ' 23:59:59');

            $dateStrings = $this->createDateRangeArray($startDate, $endDate);
        } else {
            for ($i = 6; $i >= 0; $i--) {
                $dateStrings[] = date('Y-m-d', strtotime(date('Y-m-d') . '-' . $i . ' days'));
            }
        }


        foreach ($dateStrings as $key => $date) {

            $counts = AlarmEvents::where("company_id", $request->company_id)


                ->when($request->filled("customer_id"), function ($q) use ($request) {
                    $q->where("customer_id", $request->customer_id);
                })


                ->selectRaw("
                COUNT(CASE WHEN alarm_type = 'SOS' AND alarm_status =1  THEN 1 END) as sosCount,
               COUNT(CASE WHEN alarm_category = 1 AND alarm_type != 'SOS' AND alarm_status =1 THEN 1 END) as criticalCount,

                COUNT(CASE WHEN alarm_type = 'Offline' AND alarm_status =1 THEN 1 END) as technicalCount,
                COUNT(CASE WHEN alarm_type IS NOT NULL  AND alarm_type != 'SOS' AND alarm_status =1 AND alarm_category != 1 THEN 1 END) as eventsCount,
                COUNT(CASE WHEN alarm_category = 2 AND alarm_status =1 THEN 1 END) as mediumCount,
                COUNT(CASE WHEN alarm_category = 3 AND alarm_status =1 THEN 1 END) as lowCount,

  COUNT(CASE WHEN alarm_type = 'Temperature' AND alarm_status =1 THEN 1 END) as temperatureCount,
                COUNT(CASE WHEN alarm_type = 'Water' AND alarm_status =1 THEN 1 END) as waterCount,
                COUNT(CASE WHEN alarm_type = 'Medical' AND alarm_status =1 THEN 1 END) as medicalCount,
                COUNT(CASE WHEN alarm_type = 'Fire' AND alarm_status =1 THEN 1 END) as fireCount



            ")
                ->whereDate("alarm_start_datetime", $date)

                ->when($request->filled("filter_customers_list"), function ($model) use ($request) {
                    $model->whereIn('customer_id', $request->filter_customers_list);
                })


                ->first();
            $finalarray[] = [
                "date" => $date,
                "sosCount" => $counts->soscount ?? 0,
                "highCount" => $counts->criticalcount ?? 0,
                "criticalCount" => $counts->criticalcount ?? 0,
                "technicalCount" => $counts->technicalcount ?? 0,
                "eventsCount" => $counts->eventscount ?? 0,
                "mediumCount" => $counts->mediumcount ?? 0,
                "temperatureCount" => $counts->temperaturecount ?? 0,
                "waterCount" => $counts->watercount ?? 0,
                "medicalCount" => $counts->medicalcount ?? 0,
                "fireCount" => $counts->firecount ?? 0,
                "lowCount" => $counts->lowcount ?? 0,



            ];
        }


        return  $finalarray;
    }
    public function dashboardStatisctsAllCounts(Request $request)
    {
        $finalarray = [];
        $dateStrings = [];
        // if ($request->has("date_from") && $request->has("date_to")) {
        //     // Usage example:
        //     $startDate = new DateTime($request->date_from . ' 00:00:00');
        //     $endDate = new DateTime($request->date_to . ' 23:59:59');

        //     $dateStrings = $this->createDateRangeArray($startDate, $endDate);
        // } else {
        //     for ($i = 6; $i >= 0; $i--) {
        //         $dateStrings[] = date('Y-m-d', strtotime(date('Y-m-d') . '-' . $i . ' days'));
        //     }
        // }


        // foreach ($dateStrings as $key => $date) {

        $counts = AlarmEvents::where("company_id", $request->company_id)


            ->when($request->filled("customer_id"), function ($q) use ($request) {
                $q->where("customer_id", $request->customer_id);
            })
            ->when($request->filled("filter_customers_list"), function ($model) use ($request) {
                $model->whereIn('customer_id', $request->filter_customers_list);
            })

            ->selectRaw("
                COUNT(CASE WHEN alarm_type = 'SOS' AND alarm_status =1  THEN 1 END) as sosCount,
               COUNT(CASE WHEN alarm_category = 1 AND alarm_type != 'SOS' AND alarm_status =1 THEN 1 END) as criticalCount,

                COUNT(CASE WHEN alarm_type = 'Offline' AND alarm_status =1 THEN 1 END) as technicalCount,
               COUNT(CASE WHEN alarm_type IS NOT NULL  AND alarm_type != 'SOS' AND alarm_status =1 AND alarm_category != 1 THEN 1 END) as eventsCount,
                COUNT(CASE WHEN alarm_category = 2 AND alarm_status =1 THEN 1 END) as mediumCount,
                COUNT(CASE WHEN alarm_category = 3 AND alarm_status =1 THEN 1 END) as lowCount,

  COUNT(CASE WHEN alarm_type = 'Temperature' AND alarm_status =1 THEN 1 END) as temperatureCount,
                COUNT(CASE WHEN alarm_type = 'Water' AND alarm_status =1 THEN 1 END) as waterCount,
                COUNT(CASE WHEN alarm_type = 'Medical' AND alarm_status =1 THEN 1 END) as medicalCount,
                COUNT(CASE WHEN alarm_type = 'Fire' AND alarm_status =1 THEN 1 END) as fireCount,

                COUNT(CASE WHEN alarm_type = 'Gas' AND alarm_status =1 THEN 1 END) as gasCount,

                COUNT(CASE WHEN alarm_type = 'Power' AND alarm_status =1 THEN 1 END) as powerCount,


 COUNT(CASE WHEN alarm_status = 1 THEN 1 END) as openCount,
    COUNT(CASE WHEN alarm_status = 0 THEN 1 END) as closedCount,
    COUNT(CASE WHEN forwarded = TRUE AND alarm_status = 1 THEN 1 END) as forwardCount

            ")
            //->whereDate("alarm_start_datetime", $date)




            ->first();
        $finalarray[] = [
            //"date" => $date,
            "sosCount" => $counts->soscount ?? 0,
            "highCount" => $counts->criticalcount ?? 0,
            "criticalCount" => $counts->criticalcount ?? 0,
            "technicalCount" => $counts->technicalcount ?? 0,

            "mediumCount" => $counts->mediumcount ?? 0,
            "temperatureCount" => $counts->temperaturecount ?? 0,
            "waterCount" => $counts->watercount ?? 0,
            "medicalCount" => $counts->medicalcount ?? 0,
            "fireCount" => $counts->firecount ?? 0,
            "lowCount" => $counts->lowcount ?? 0,
            "openCount" => $counts->opencount ?? 0,
            "closedCount" => $counts->closedcount ?? 0,
            "forwardCount" => $counts->forwardcount ?? 0,

            "gasCount" => $counts->gascount ?? 0,

            "powerCount" => $counts->powercount ?? 0,

            "eventsCount" =>  $counts->opencount - $counts->soscount - $counts->criticalcount - $counts->technicalcount - $counts->temperaturecount - $counts->watercount - $counts->firecount - $counts->medicalcount,



        ];
        // }


        return  $finalarray;
    }
    function createDateRangeArray($startDate, $endDate)
    {
        $dateStrings = [];
        $currentDate = $startDate;

        while ($currentDate <= $endDate) {
            $dateStrings[] = $currentDate->format('Y-m-d'); // Change the format as needed
            $currentDate->modify('+1 day');
        }

        return $dateStrings;
    }
    public function getDeviceArmedStatistics(Request $request)
    {

        $model = Device::where("company_id", $request->company_id)->where("device_id", "!=", "Manual")

            ->where("device_type", "!=", "Mobile")

            ->where("device_type", "!=", "Manual")
            ->where("device_id", "!=", "Manual")
            ->where("serial_number", "!=", null);


        $model->when($request->filled("filter_customers_list"), function ($model) use ($request) {
            $model->whereIn('customer_id', $request->filter_customers_list);
        });

        $model->when($request->filled("customer_id"), function ($model) use ($request) {
            $model->where('customer_id', $request->customer_id);
        });

        $totalCount = $model->clone()->count();
        $armedCount =  $model->clone()->where("armed_status", 1)->count();
        $disarmCount = $model->clone()->where("armed_status", 0)->count();
        $otherCount =   $totalCount - $armedCount - $disarmCount;




        return ["total" => $totalCount, "armed" => $armedCount, "disarm" => $disarmCount, "other" => $otherCount];
    }
    public function getDeviceLiveStatisticsGroupBy(Request $request)
    {
        $statuses = [
            'Water' => ['online' => 0, 'offline' => 0],
            'Burglary' => ['online' => 0, 'offline' => 0],
            'Fire' => ['online' => 0, 'offline' => 0],
            'Medical' => ['online' => 0, 'offline' => 0],
            'Temperature' => ['online' => 0, 'offline' => 0],

        ];

        foreach ($statuses as $type => $status) {
            $model = Device::with(['sensorzones'])
                ->where('company_id', $request->company_id)->where("device_id", "!=", "Manual")
                ->where(function ($query) use ($type) {
                    $query->where('device_type', 'ILIKE', $type)
                        ->orWhereHas('sensorzones', function ($query) use ($type) {
                            $query->where('sensor_name', 'ILIKE', $type);
                        });
                });


            $model->when($request->filled("filter_customers_list"), function ($model) use ($request) {
                $model->whereIn('customer_id', $request->filter_customers_list);
            });
            $statuses[$type]['online'] = $model->clone()->where('status_id', 1)->count();
            $statuses[$type]['offline'] = $model->clone()->where('status_id', 2)->count();
            $statuses[$type]['percentage'] = ($statuses[$type]['online'] + $statuses[$type]['offline'] > 0) ?  round($statuses[$type]['online'] * 100 / ($statuses[$type]['online'] + $statuses[$type]['offline'])) : 0;
        }

        return [
            'Burglary' => ["online" => $statuses['Burglary']['online'], "offline" => $statuses['Burglary']['offline'],  "percentage" => $statuses['Burglary']['percentage']],
            'Water' => ["online" => $statuses['Water']['online'],  "offline" => $statuses['Water']['offline'],  "percentage" => $statuses['Water']['percentage']],
            'Fire' => ["online" => $statuses['Fire']['online'],  "offline" => $statuses['Fire']['offline'],  "offline" => $statuses['Fire']['offline'],  "percentage" => $statuses['Fire']['percentage']],
            'Medical' => ["online" => $statuses['Medical']['online'],  "offline" => $statuses['Medical']['offline'],  "percentage" => $statuses['Medical']['percentage']],
            'Temperature' => ["online" => $statuses['Temperature']['online'],  "offline" => $statuses['Temperature']['offline'],  "percentage" => $statuses['Temperature']['percentage']],

        ];
    }
    public function getDeviceLiveStatistics(Request $request)
    {

        $model = Device::where("company_id", $request->company_id)->where("device_id", "!=", "Manual");
        $totalCount = $model->count();
        $online = $model->where("status_id", 1)->count();

        return ["total" => $totalCount, "online" => $online];
    }
    public function getCustomerContractStatistics(Request $request)
    {

        $today = now();
        $thirtyDaysFromNow = now()->addDays(30);


        $customers = Customers::where("company_id", $request->company_id);

        $total = $customers->clone()->count();
        $expired = $customers->clone()->where("end_date", "<", $today)
            ->count();
        $expiringIn30Days = $customers->clone()->whereBetween("end_date", [$today, $thirtyDaysFromNow])->count();
        return ["total" => $total, "expired" => $expired, "expiringin30days" => $expiringIn30Days];
    }
    public function alarmResponseStatistics(Request $request)
    {
        $statistics = DB::table('alarm_events')
            ->where('company_id', $request->company_id)
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                $query->where('customer_id', $request->customer_id);
            })
            ->whereBetween('alarm_start_datetime', [$request->date_from . ' 00:00:00', $request->date_to . ' 23:59:59'])
            ->selectRaw('
        COALESCE(SUM(CASE WHEN response_minutes < 1 THEN 1   END), 0) AS less_than_1_minute,
        COALESCE(SUM(CASE WHEN response_minutes >= 1 AND response_minutes < 5 THEN 1   END), 0) AS between_1_and_5_minutes,
        COALESCE(SUM(CASE WHEN response_minutes >= 5 AND response_minutes < 10 THEN 1   END), 0) AS between_5_and_10_minutes,
        COALESCE(SUM(CASE WHEN response_minutes >= 10 THEN 1   END), 0) AS more_than_10_minutes
    ')
            ->first();

        return  $statistics;
    }
    public function alarmCustomerStatistics(Request $request)
    {
        $statistics = AlarmEvents::with('customer')
            ->select('customer_id', DB::raw('count(*) as total_alarms')) // Add aggregate function
            ->where('company_id', $request->company_id)
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                $query->where('customer_id', $request->customer_id);
            })
            ->whereBetween('alarm_start_datetime', [
                $request->date_from . ' 00:00:00',
                $request->date_to . ' 23:59:59'
            ])
            ->groupBy('customer_id')
            ->get();
        return  $statistics;
    }


    public function alarmStatistics(Request $request)
    {
        $statistics = DB::table('alarm_events')
            ->where('company_id', $request['company_id'])
            ->where('alarm_status', 1)
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                $query->where('customer_id', $request->customer_id);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereBetween('alarm_start_datetime', [
                    $request['date_from'] . ' 00:00:00',
                    $request['date_from'] . ' 23:59:59'
                ]);
            })
            ->when($request->filled("filter_customers_list"), function ($q) use ($request) {
                $q->whereIn("customer_id", $request->filter_customers_list);
            })



            ->selectRaw('
            COALESCE(SUM(CASE WHEN alarm_type = \'Burglary\' and alarm_status=1 THEN 1  END), 0) AS burglary,
            COALESCE(SUM(CASE WHEN alarm_type = \'Medical\' and alarm_status=1 THEN 1   END), 0) AS medical,
            COALESCE(SUM(CASE WHEN alarm_type = \'Temperature\' and alarm_status=1 THEN 1   END), 0) AS temperature,
            COALESCE(SUM(CASE WHEN alarm_type = \'Water\' and alarm_status=1 THEN 1 END), 0) AS water,
            COALESCE(SUM(CASE WHEN alarm_type = \'AC_off\' and alarm_status=1 THEN 1 END), 0) AS power,
            COALESCE(SUM(CASE WHEN alarm_type = \'Gas\' and alarm_status=1 THEN 1 END), 0) AS gas,


            COALESCE(SUM(CASE WHEN alarm_type = \'Fire\' and alarm_status=1 THEN 1 END), 0) AS fire,
            COALESCE(SUM(CASE WHEN alarm_type = \'SOS\' and alarm_status=1 THEN 1   END), 0) AS sos,
            COALESCE(SUM(CASE WHEN alarm_category = \'1\' and alarm_status=1 THEN 1   END), 0) AS critical,
 COALESCE(SUM(CASE WHEN alarm_type = \'Offline\' and alarm_status=1  THEN 1 END), 0) AS techinical,



             COALESCE(SUM(CASE WHEN   alarm_status=1 THEN 1   END), 0) AS all_alarms



        ')
            ->first();
        $statistics = (array) $statistics;;

        $statistics["events"] = $statistics["all_alarms"] - (
            $statistics["burglary"]
            + $statistics["medical"]
            + $statistics["temperature"]
            + $statistics["water"]
            + $statistics["power"]
            + $statistics["gas"]
            + $statistics["fire"]
            + $statistics["sos"]
            + $statistics["critical"]
            + $statistics["techinical"]);


        return response()->json($statistics);
    }
    public function alarmEventOperatorStatistics(Request $request)
    {
        $statistics = DB::table('alarm_events')
            ->where('company_id', $request['company_id'])
            ->when($request->filled("filter_customers_list") && $request->filter_customers_list != '', function ($query) use ($request) {
                $query->whereIn('customer_id', $request->filter_customers_list);
            })
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                $query->where('customer_id', $request->customer_id);
            })



            ->selectRaw('
            COALESCE(SUM(CASE WHEN alarm_type = \'Intruder\' AND alarm_status = 1 AND customer_id IN (' . implode(',', $request->filter_customers_list) . ') THEN 1   END), 0) AS intruder,

            COALESCE(SUM(CASE WHEN alarm_type = \'SOS\' AND alarm_status = 1 AND customer_id IN (' . implode(',', $request->filter_customers_list) . ') THEN 1  END), 0) AS sos,
            COALESCE(SUM(CASE WHEN alarm_type = \'Medical\' AND alarm_status = 1 AND customer_id IN (' . implode(',', $request->filter_customers_list) . ') THEN 1  END), 0) AS medical,
            COALESCE(SUM(CASE WHEN alarm_type = \'ac_off\' AND alarm_status = 1 AND customer_id IN (' . implode(',', $request->filter_customers_list) . ') THEN 1  END), 0) AS ac_off,
            COALESCE(SUM(CASE WHEN alarm_category = \'1\' AND alarm_status = 1 AND customer_id IN (' . implode(',', $request->filter_customers_list) . ') THEN 1   END), 0) AS critical,

              (SELECT COUNT(*) FROM devices WHERE status_id = 2 AND customer_id IN (' . implode(',', $request->filter_customers_list) . ')) AS offline,


             (SELECT COUNT(*) FROM devices WHERE armed_status = 1   AND customer_id IN (' . implode(',', $request->filter_customers_list) . ')) AS armed,
              (SELECT COUNT(*) FROM devices WHERE armed_status = 0  AND customer_id IN (' . implode(',', $request->filter_customers_list) . ')) AS disarm
        ')

            ->first();

        return response()->json($statistics);
    }
    public function alarmEventTemperatureStatistics(Request $request)
    {
        $events = AlarmEvents::with(["category"])->where('company_id', $request['company_id'])
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                $query->where('customer_id', $request->customer_id);
            })
            ->where('alarm_type', "Temperature");
        $events->when($request->filled("filter_customers_list"), function ($model) use ($request) {
            $model->whereIn('customer_id', $request->filter_customers_list);
        });
        return ["active" => $events->clone()->where("alarm_status", 1)->count(), "closed" => $events->clone()->where("alarm_status", 0)->count()];
    }
    public function alarmEventWaterStatistics(Request $request)
    {
        $events = AlarmEvents::with(["category"])->where('company_id', $request['company_id'])
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                $query->where('customer_id', $request->customer_id);
            })
            ->where('alarm_type', "Water");
        $events->when($request->filled("filter_customers_list"), function ($model) use ($request) {
            $model->whereIn('customer_id', $request->filter_customers_list);
        });

        return ["active" => $events->clone()->where("alarm_status", 1)->count(), "closed" => $events->clone()->where("alarm_status", 0)->count()];
    }
    public function alarmEventFireStatistics(Request $request)
    {
        $events = AlarmEvents::with(["category"])->where('company_id', $request['company_id'])
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                $query->where('customer_id', $request->customer_id);
            })
            ->where('alarm_type', "Fire");
        $events->when($request->filled("filter_customers_list"), function ($query) use ($request) {
            $query->whereIn('customer_id', $request->filter_customers_list);
        });
        return ["active" => $events->clone()->where("alarm_status", 1)->count(), "closed" => $events->clone()->where("alarm_status", 0)->count()];
    }
    public function buildingtypeCustomerStats(Request $request)
    {

        $BuildingTypes = CustomersBuildingTypes::all();

        $groupCount = Customers::where('company_id', $request->company_id)
            ->select('building_type_id', DB::raw('COUNT(id) as total_count'))
            ->groupBy('building_type_id')
            ->pluck('total_count', 'building_type_id'); // Pluck total_count with building_type_id as the key

        $groupResults = [];

        foreach ($BuildingTypes as $buildingType) {
            $groupResults[] = ["name" => $buildingType->name, "count" => $groupCount->get($buildingType->id, 0)];
        }

        return $groupResults;
    }

    public function customersAccountsExpireStats(Request $request)
    {

        $businessTypes = CustomersBuildingTypes::all();

        $groupCount = Customers::where('company_id', $request->company_id)
            ->select('building_type_id', DB::raw('COUNT(id) as total_count'))
            ->groupBy('building_type_id')
            ->pluck('total_count', 'building_type_id'); // Pluck total_count with building_type_id as the key

        $groupResults = [];

        foreach ($businessTypes as $buildingType) {
            $groupResults[] = ["name" => $buildingType->name, "count" => $groupCount->get($buildingType->id, 0)];
        }

        return $groupResults;
    }

    public function alarmEventMedicalStatistics(Request $request)
    {
        $events = AlarmEvents::with(["category"])->where('company_id', $request['company_id'])
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                $query->where('customer_id', $request->customer_id);
            })
            ->where('alarm_type', "Medical");

        $events->when($request->filled("filter_customers_list"), function ($model) use ($request) {
            $model->whereIn('customer_id', $request->filter_customers_list);
        });

        return ["active" => $events->clone()->where("alarm_status", 1)->count(), "closed" => $events->clone()->where("alarm_status", 0)->count()];
    }

    public function alarmEventBurglaryStatistics(Request $request)
    {
        $events = AlarmEvents::with(["category"])->where('company_id', $request['company_id'])
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                $query->where('customer_id', $request->customer_id);
            })
            ->where('alarm_status', 1)
            ->where('alarm_type', "Intruder")
            ->when($request->filled("filter_customers_list"), function ($query) use ($request) {
                $query->whereIn('customer_id', $request->filter_customers_list);
            })->when($request->filled("filter_customers_list"), function ($query) use ($request) {
                $query->whereIn('customer_id', $request->filter_customers_list);
            })
            ->when($request->filled("filter_customers_list"), function ($query) use ($request) {
                $query->whereIn('customer_id', $request->filter_customers_list);
            })


            ->get()->groupBy("alarm_category");

        return $events;
    }
    public function alarmEventStatistics(Request $request)
    {
        $events = AlarmEvents::where('company_id', $request['company_id'])
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                $query->where('customer_id', $request->customer_id);
            })
            ->whereBetween('alarm_start_datetime', [
                $request['date_from'] . ' 00:00:00',
                $request['date_to'] . ' 23:59:59'
            ])->get();

        return $events;
    }
    public function getDeviceSensorStatistics(Request $request)
    {


        $deviceTypes = Device::where('company_id', $request->company_id)

            ->distinct()
            ->pluck('device_type');


        $sensorNames = DeviceZones::whereHas('device', function ($query) use ($request) {
            $query->where('company_id', $request->company_id);
        })->pluck('sensor_name');


        $mergedCollection = $deviceTypes->merge($sensorNames);


        $distinctValues = $mergedCollection->toArray();
        $item_counts = array();


        foreach ($distinctValues as $item) {
            if (array_key_exists($item, $item_counts)) {
                $item_counts[$item]++;
            } else {
                $item_counts[$item] = 1;
            }
        }

        return $item_counts;
    }
}
