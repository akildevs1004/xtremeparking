<?php

namespace App\Http\Controllers\Dashboards;

use App\Exports\SOSExcelReports;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\Device;
use App\Models\DeviceSosAlarms;
use App\Models\DeviceSosRoomLogs;
use App\Models\DeviceSosRooms;
use App\Models\Employee;
use App\Models\HostCompany;
use App\Models\Leave;
use App\Models\SecuritySosRoomsList;
use App\Models\Visitor;
use App\Models\VisitorAttendance;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class SOSRoomsControllers extends Controller
{

    public function updateResponseDatetime(Request $request)
    {
        $logDir = base_path('../../logs/sos-nurse-calling-system/mqtt-sosalarm-logs');

        if (!File::exists($logDir)) {
            File::makeDirectory($logDir, 0755, true);
        }
        $logPath = $logDir . '/' . date("Y-m-d") . '.log';


        $companyId =  (int) $request->company_id;
        $alarmId =  (int) $request->alarmId;

        $alarmLog = DeviceSosRoomLogs::with(["device", "room"])->where("id", $alarmId)->where("company_id", $companyId)->where("sos_status", true)->first();


        if ($alarmLog && $alarmLog["device"]) {

            $now = now()->setTimezone($alarmLog->device->utc_time_zone)->format('Y-m-d H:i:s');


            DeviceSosRoomLogs::where("id", $alarmLog->id)->update([                          // FIX: update the instance

                "responded_datetime"  => $now,


            ]);

            //Close Alarm if SOS button has only one press button Like Fire Alarm
            {

                $device_sos_button_off_code = $alarmLog->room->off_code;
                if ($device_sos_button_off_code == '' || $device_sos_button_off_code == null) { {
                        # code...


                        DeviceSosRooms::where("id", $alarmLog->device_sos_room_table_id)->where("company_id", $companyId)->update([
                            'alarm_status'     => false,
                            'updated_at' => now(),
                        ]);;


                        $response_in_minutes = 0;

                        if ($alarmLog) {
                            $response_in_minutes = Carbon::parse($alarmLog->alarm_start_datetime)
                                ->diffInMinutes($now);

                            DeviceSosRoomLogs::where("id", $alarmLog->id)->update([                          // FIX: update the instance
                                "sos_status"          => false,
                                "off_code"            => null,
                                "alarm_end_datetime"  => $now,
                                "responded_datetime"  => $now,
                                "response_in_minutes"  => $response_in_minutes,

                            ]);
                            echo "\n -------------------------  Alarm Closed";

                            File::prepend($logPath, "[" . now() . "] Info: SOS Alarm Is Updated  $alarmLog->id  \n");
                        }
                    }


                    echo "\n SINGLE SOS BUTTON OFF " . $alarmLog->id;
                }
            }



            return $this->response('Acknowledgement Updated Successfully', null, true);
        } else {
            echo "\n Alarm Is not Open " . $alarmLog->id;
        }
        return $this->response('Not Updated.Try Again ', null, false);
    }

    public function dashboardStats(Request $request)
    {
        $companyId = (int) $request->company_id;
        $slaMinutes = 1;


        $filterRoomIds = collect();

        if ($request->filled('securityId')) {
            $securityId =   (int) $request->securityId;

            // IMPORTANT: pick the correct column:
            // - If this table stores DeviceSosRooms IDs, pluck('device_sos_room_id') (example)
            // - If it stores its own row IDs, pluck('id') will be wrong for filtering rooms.
            $filterRoomIds = SecuritySosRoomsList::where('security_user_id', $securityId)
                ->pluck('sos_room_table_id'); // <-- adjust this column name to your schema
        }

        // total responded calls
        $totalSOSActive = DeviceSosRoomLogs::with('room')
            ->where('company_id', $companyId)
            // ->where("alarm_end_datetime", null)
            ->when(!$request->filled('date_to'), function ($q) use ($request) {
                $q->whereDate('alarm_end_datetime', '>=', date("Y-m-d"));
            })
            ->when($filterRoomIds->isNotEmpty(), function ($q) use ($filterRoomIds) {
                $q->whereIn('device_sos_room_table_id', $filterRoomIds);
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                $q->whereDate('alarm_end_datetime', '<=', $request->date_to);
            })
            ->when($request->filled('roomType'), function ($q) use ($request) {
                $q->whereHas('room', function ($qr) use ($request) {
                    $qr->where('room_type', $request->roomType);
                });
            })
            ->count();

        $activeDisabledSOS = DeviceSosRoomLogs::with('room')
            ->where('company_id', $companyId)

            ->where('alarm_end_datetime', null)


            ->when($filterRoomIds->isNotEmpty(), function ($q) use ($filterRoomIds) {
                $q->whereIn('device_sos_room_table_id', $filterRoomIds);
            })

            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('alarm_start_datetime', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                $q->whereDate('alarm_end_datetime', '<=', $request->date_to);
            })
            ->when($request->filled('roomType'), function ($q) use ($request) {
                $q->whereHas('room', function ($qr) use ($request) {
                    $qr->where('room_type', $request->roomType);
                });
            })
            ->whereHas('room', function ($qr) use ($request) {
                $qr->where('room_type', $request->roomType);
            })
            ->count();
        $repeatedCalls = DeviceSosRoomLogs::where('company_id', $companyId)->whereDate('alarm_start_datetime', date("Y-m-d"))

            ->when($filterRoomIds->isNotEmpty(), function ($q) use ($filterRoomIds) {
                $q->whereIn('device_sos_room_table_id', $filterRoomIds);
            })
            ->whereNotNull('alarm_start_datetime')
            ->select('room_id')
            ->groupBy('room_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();
        $ackCount = DeviceSosRoomLogs::where('company_id', $companyId)->whereDate('alarm_start_datetime', date("Y-m-d"))
            ->when($filterRoomIds->isNotEmpty(), function ($q) use ($filterRoomIds) {
                $q->whereIn('device_sos_room_table_id', $filterRoomIds);
            })
            ->whereNotNull('responded_datetime')
            ->select('id')
            ->groupBy('id')
            ->havingRaw('COUNT(*) > 0')
            ->count();

        $averageMinutes = DeviceSosRoomLogs::where('company_id', $companyId)
            ->when($filterRoomIds->isNotEmpty(), function ($q) use ($filterRoomIds) {
                $q->whereIn('device_sos_room_table_id', $filterRoomIds);
            })
            ->whereNotNull('response_in_minutes')

            ->avg('response_in_minutes');



        // responded within SLA
        $withinSla = DeviceSosRoomLogs::where('company_id', $companyId)
            ->when($filterRoomIds->isNotEmpty(), function ($q) use ($filterRoomIds) {
                $q->whereIn('device_sos_room_table_id', $filterRoomIds);
            })
            ->whereNotNull('response_in_minutes')
            ->where('response_in_minutes', '<=', $slaMinutes)
            ->count();

        $percentage = $totalSOSActive > 0
            ? round(($withinSla / $totalSOSActive) * 100, 2)
            : 0;

        return response()->json([
            'sla_percentage' => $percentage,   // e.g. 82.45
            'sla_minutes'    => $slaMinutes,
            'averageMinutes'    => $averageMinutes,



            'repeated'    => $repeatedCalls,
            'ackCount'    => $ackCount,

            'totalSOSCount' => $totalSOSActive,

            "activeDisabledSos" => $activeDisabledSOS


        ]);
    }

    public function getServerIp()
    {

        // return "165.22.222.17";
        $ips = gethostbynamel(gethostname());
        foreach ($ips as $ip) {
            if ($ip !== '127.0.0.1') {
                return $ip;
            }
        }
        return '127.0.0.1';
    }

    public function dashboardRooms(Request $request)
    {
        $companyId = (int) $request->company_id;

        $filterRoomIds = collect();

        if ($request->filled('securityId')) {
            $securityId =   (int) $request->securityId;

            echo $securityId;

            if ($securityId > 0) {

                // IMPORTANT: pick the correct column:
                // - If this table stores DeviceSosRooms IDs, pluck('device_sos_room_id') (example)
                // - If it stores its own row IDs, pluck('id') will be wrong for filtering rooms.
                $filterRoomIds = SecuritySosRoomsList::where('security_user_id', $securityId)
                    ->pluck('sos_room_table_id'); // <-- adjust this column name to your schema

            }
        }

        $query = DeviceSosRooms::query()
            ->with([
                'device',
                'latestAlarm' => function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                }
            ])
            ->where('company_id', $companyId)
            ->orderBy('room_id', 'asc');

        if ($filterRoomIds->isNotEmpty()) {
            $query->whereIn('id', $filterRoomIds);
        }

        $deviceRooms = $query->get()
            ->map(function ($room) {
                $room->setAttribute('alarm', $room->latestAlarm);
                unset($room->latestAlarm);
                return $room;
            });

        return response()->json($deviceRooms);

        // $companyId = $request->company_id;


        // $deviceRooms = DeviceSosRooms::with(["device"])->where("company_id", $companyId)->get();

        // foreach ($deviceRooms as $key => $room) {
        //     $alarm = DeviceSosRoomLogs::where("company_id", $companyId)->where("device_sos_room_table_id", $room->id)->orderby("alarm_start_datetime", "desc")->first();

        //     if ($alarm)
        //         $room["alarm"] = $alarm;

        //     else
        //         $room["alarm"] = null;
        // }

        // return $deviceRooms;
    }

    public function  getRecords($request, $perpage = null)
    {

        $companyId = (int) $request->company_id;

        $model = DeviceSosRoomLogs::query()
            ->with(['room', 'device'])
            ->where('company_id', $companyId);

        // Common search
        $model->when($request->filled('common_search'), function ($q) use ($request) {
            $s = trim($request->common_search);

            $q->where(function ($qq) use ($s) {
                $qq->where('room_id', 'ILIKE', "%{$s}%")
                    ->orWhere('serial_number', 'ILIKE', "%{$s}%")
                    ->orWhere('room_name', 'ILIKE', "%{$s}%")
                    // IMPORTANT: use correct relation name (likely "room")
                    ->orWhereHas('room', function ($rq) use ($s) {
                        $rq->where('room_type', 'ILIKE', "%{$s}%");
                    })
                    // Optional: device location search
                    ->orWhereHas('device', function ($dq) use ($s) {
                        $dq->where('location', 'ILIKE', "%{$s}%");
                    });
            });
        });

        // Date range (safe)
        $model->when($request->filled('date_from'), function ($q) use ($request) {
            $q->whereDate('alarm_start_datetime', '>=', $request->date_from);

            if ($request->filled('date_to')) {
                $q->whereDate('alarm_start_datetime', '<=', $request->date_to);
            }
        });

        // Alarm status filter (keeping your semantics)
        if ($request->filled('alarm_status')) {
            if ($request->alarm_status === "true") {
                $model->where('sos_status', true);
            } elseif ($request->alarm_status === "false") {
                $model->where('sos_status', false);
            } elseif ($request->alarm_status === "acknowledged") {
                // Your "none" means acknowledged (active + responded)
                $model->whereNotNull('responded_datetime');
            }
        }


        $model->orderBy('alarm_start_datetime', 'desc');


        // $model->orderBy("updated_at", "DESC");

        if (request()->has('perPage') || request()->has('page') || $perpage) {
            return   $model->paginate($request->per_page ?? 20);
        } else {
            return $model->get();
        }
    }
    public function SosLogsReports(Request $request)
    {



        return   $this->getRecords($request, 20);
    }


    public function SosLogsPrintPdf(Request $request)
    {



        $report =  $this->getRecords($request);
        $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first();

        $fileName = "SOS Reports List.pdf";


        return   Pdf::loadview("sos/sos-reports", ["request" => $request, "reports" => $report, "company" => $company])->setpaper("A4", "potrait")->stream($fileName);
    }
    public function SosLogsDownloadPdf(Request $request)
    {

        $report =  $this->getRecords($request);
        $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first();

        $fileName = "SOS Reports List.pdf";


        return   Pdf::loadview("sos/sos-reports", ["request" => $request, "reports" => $report, "company" => $company])->setpaper("A4", "potrait")->download($fileName);
    }
    public function SosLogsDownloadCSV(Request $request)
    {

        $reports =   $this->getRecords($request);
        $fileName = "SOS Reports List.xlsx";

        return Excel::download((new SOSExcelReports($reports)), $fileName);
    }

    //-[[----------------------------------------------------------]]
    public function SOSMonitorStatistics(Request $request)
    {

        $date_from = $request->date_from;
        $date_to = $request->date_to;


        $companyId = (int) $request->company_id;
        $slaMinutes = 5;

        // total   calls
        $totalSOSCount = DeviceSosRoomLogs::where('company_id', $companyId)
            ->whereDate('alarm_start_datetime', ">=", $date_from . " 00:00:00")
            ->where('alarm_start_datetime', "<=", $date_to . " 24:00:00")



            ->when($request->filled('sosStatus'), function ($q) use ($request) {
                if ($request->sosStatus == "ON")
                    $q->where("sos_status", true);
                if ($request->sosStatus == "OFF")
                    $q->where("sos_status", false);
                if ($request->sosStatus == "PENDING")
                    $q->where("sos_status", true)->where("responded_datetime", "!=", null);
            })

            ->when($request->filled('roomType'), function ($q) use ($request) {
                $q->whereHas('room', function ($qr) use ($request) {
                    $qr->where('room_type', $request->roomType);
                });
            })




            ->count();


        $totalResolved = DeviceSosRoomLogs::where('company_id', $companyId)
            ->where('alarm_end_datetime', ">=", $date_from . " 00:00:00")
            ->where('alarm_end_datetime', "<=", $date_to . " 24:00:00")
            ->where('alarm_end_datetime', "!=", null)
            ->when($request->filled('sosStatus'), function ($q) use ($request) {
                if ($request->sosStatus == "ON")
                    $q->where("sos_status", true);
                if ($request->sosStatus == "OFF")
                    $q->where("sos_status", false);
                if ($request->sosStatus == "PENDING")
                    $q->where("sos_status", true)->where("responded_datetime", "!=", null);
            })
            ->when($request->filled('roomType'), function ($q) use ($request) {
                $q->whereHas('room', function ($qr) use ($request) {
                    $qr->where('room_type', $request->roomType);
                });
            })
            ->count();


        $totalSOSActive = DeviceSosRoomLogs::where('company_id', $companyId)

            ->where('alarm_end_datetime',   null)
            ->when($request->filled('sosStatus'), function ($q) use ($request) {
                if ($request->sosStatus == "ON")
                    $q->where("sos_status", true);
                if ($request->sosStatus == "OFF")
                    $q->where("sos_status", false);
                if ($request->sosStatus == "PENDING")
                    $q->where("sos_status", true)->where("responded_datetime", "!=", null);
            })
            ->when($request->filled('roomType'), function ($q) use ($request) {
                $q->whereHas('room', function ($qr) use ($request) {
                    $qr->where('room_type', $request->roomType);
                });
            })
            ->count();



        $averageMinutes = DeviceSosRoomLogs::where('company_id', $companyId)
            ->whereNotNull('response_in_minutes')
            ->when($request->filled('sosStatus'), function ($q) use ($request) {
                if ($request->sosStatus == "ON")
                    $q->where("sos_status", true);
                if ($request->sosStatus == "OFF")
                    $q->where("sos_status", false);
                if ($request->sosStatus == "PENDING")
                    $q->where("sos_status", true)->where("responded_datetime", "!=", null);
            })
            ->where('alarm_start_datetime', ">=", $date_from . " 00:00:00")
            ->where('alarm_end_datetime', "<=", $date_to . " 24:00:00")
            ->when($request->filled('roomType'), function ($q) use ($request) {
                $q->whereHas('room', function ($qr) use ($request) {
                    $qr->where('room_type', $request->roomType);
                });
            })
            ->avg('response_in_minutes');


        $totalDeviceOnline = Device::where('company_id', $companyId)
            ->where('status_id', 1)
            ->count();
        $totalDeviceOffline = Device::where('company_id', $companyId)
            ->where('status_id', 2)
            ->count();




        return response()->json([
            'totalSOSCount' => $totalSOSCount,   // e.g. 82.45
            'totalResolved'    => $totalResolved,
            'totalSOSActive'    => $totalSOSActive,
            'totalDeviceOnline'    => $totalDeviceOnline,
            'totalDeviceOffline'    => $totalDeviceOffline,




            'averageMinutes'    => round($averageMinutes, 2),




        ]);
    }
    private function roomTypeLabels(): array
    {
        return [
            "room"      => "Room",
            "toilet"    => "Toilet",
            "toilet-ph" => "Toilet For Disabled",
        ];
    }
    public function sosHourlyReport(Request $request)
    {



        $companyId = (int) $request->company_id;

        if ($request->date_from) {
            // Date range
            $from = Carbon::parse($request->date_from)->startOfDay();
            $to   = Carbon::parse($request->date_to)->endOfDay();
        }
        // Labels
        $labels = $this->roomTypeLabels();

        // Base query

        if ($request->date_from) {
            $query = DB::table('device_sos_room_logs as l')
                ->join('device_sos_rooms as r', 'r.id', '=', 'l.device_sos_room_table_id')
                ->whereBetween('l.alarm_start_datetime', [$from, $to])
                ->whereNotNull('r.room_type');
        } else {
            $query = DB::table('device_sos_room_logs as l')
                ->join('device_sos_rooms as r', 'r.id', '=', 'l.device_sos_room_table_id')

                ->whereNotNull('r.room_type');
        }

        if ($companyId) {
            $query->where('l.company_id', $companyId);
        }

        // SOS status filter
        $query->when($request->filled('sosStatus'), function ($q) use ($request) {
            if ($request->sosStatus === "ON") {
                $q->where('l.sos_status', true);
            } elseif ($request->sosStatus === "OFF") {
                $q->where('l.sos_status', false);
            } elseif ($request->sosStatus === "PENDING") {
                // ON but not responded
                $q->where('l.sos_status', true)
                    ->whereNull('l.responded_datetime');
            } elseif ($request->sosStatus === "RESPONDED") {
                $q->whereNotNull('l.responded_datetime');
            }
        });



        $query->when($request->filled('alarm_status'), function ($q) use ($request) {
            if ($request->alarm_status === 'true') {
                $q->where('l.sos_status', true);
            } elseif ($request->alarm_status === 'false') {
                $q->where('l.sos_status', false);
            } elseif ($request->alarm_status === 'acknowledged') {
                $q->whereNotNull('l.responded_datetime');
            }
        });

        // Room type filter
        $query->when($request->filled('roomType'), function ($q) use ($request) {
            $q->where('r.room_type', $request->roomType);
        });

        // DB-specific hour extraction
        $driver = DB::getDriverName();
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            $hourExpr = "EXTRACT(HOUR FROM l.alarm_start_datetime)";
        } elseif ($driver === 'sqlite') {
            // SQLite: hour as 00-23 string, cast to int
            $hourExpr = "CAST(strftime('%H', l.alarm_start_datetime) AS INTEGER)";
        } else {
            // MySQL/MariaDB
            $hourExpr = "HOUR(l.alarm_start_datetime)";
        }

        // Aggregate
        $rows = $query
            ->selectRaw("
                r.room_type AS room_type,
                $hourExpr   AS hour,
                COUNT(*)    AS total
            ")
            ->groupBy('r.room_type', DB::raw($hourExpr))
            ->orderBy('r.room_type')
            ->orderBy(DB::raw($hourExpr))
            ->get();

        // X-axis categories (0–23)
        $categories = array_map('strval', range(0, 23));

        // Use all known room types so zero-value series still appear
        $roomTypes = array_keys($labels);

        // Initialize series map
        $seriesMap = [];
        foreach ($roomTypes as $rt) {
            $seriesMap[$rt] = array_fill(0, 24, 0);
        }

        // Fill data
        foreach ($rows as $row) {
            $rt = (string) $row->room_type;
            $h  = (int) $row->hour;

            if ($h >= 0 && $h <= 23) {
                // Include unexpected room types safely
                if (!isset($seriesMap[$rt])) {
                    $seriesMap[$rt] = array_fill(0, 24, 0);
                }
                $seriesMap[$rt][$h] = (int) $row->total;
            }
        }

        // Build ApexCharts series
        $series = [];
        foreach ($seriesMap as $roomType => $data) {
            $series[] = [
                'name' => $labels[$roomType] ?? ucfirst(str_replace('-', ' ', $roomType)),
                'data' => $data,
            ];
        }

        return response()->json([
            'categories' => $categories,
            'series'     => $series,
            'meta'       => [
                'company_id' => $companyId,
                'date_from'  => (isset($from)) ? $from->toDateTimeString() : '--',
                'date_to'    => (isset($to)) ? $to->toDateTimeString() : '--',
                'db_driver'  => $driver,
            ],
        ]);
    }
    public function sosHourlyMixedRoomsReport(Request $request)
    {

        $companyId = (int) $request->company_id; {
            $from = Carbon::parse($request->date_from)->startOfDay();
            $to   = Carbon::parse($request->date_to)->endOfDay();
        }
        // Base query (NO room join)
        $query = DB::table('device_sos_room_logs as l');

        if ($request->filled("date_from"))
            $query->whereBetween('l.alarm_start_datetime', [$from, $to]);

        //if ($companyId) {

        $query->where('l.company_id', $companyId);
        //}

        $query->when($request->filled('common_search'), function ($q) use ($request) {
            $s = trim($request->common_search);

            $q->where(function ($qq) use ($s) {
                $qq->where('l.room_id', 'ILIKE', "%{$s}%")
                    ->orWhere('l.serial_number', 'ILIKE', "%{$s}%")
                    ->orWhere('l.room_name', 'ILIKE', "%{$s}%")
                    // IMPORTANT: use correct relation name (likely "room")
                    // ->orWhereHas('room', function ($rq) use ($s) {
                    //     $rq->where('room_type', 'ILIKE', "%{$s}%");
                    // })
                    // Optional: device location search
                    // ->orWhereHas('device', function ($dq) use ($s) {
                    //     $dq->where('location', 'ILIKE', "%{$s}%");
                    // })
                ;
            });
        });

        // SOS status filter
        $query->when($request->filled('sosStatus'), function ($q) use ($request) {
            if ($request->sosStatus === "ON") {
                $q->where('l.sos_status', true);
            } elseif ($request->sosStatus === "OFF") {
                $q->where('l.sos_status', false);
            } elseif ($request->sosStatus === "PENDING") {
                $q->where('l.sos_status', true)
                    ->whereNull('l.responded_datetime');
            } elseif ($request->sosStatus === "RESPONDED") {
                $q->where('l.sos_status', true)
                    ->whereNotNull('l.responded_datetime');
            }
        });

        $query->when($request->filled('alarm_status'), function ($q) use ($request) {
            if ($request->alarm_status === 'true') {
                $q->where('l.sos_status', true);
            } elseif ($request->alarm_status === 'false') {
                $q->where('l.sos_status', false);
            } elseif ($request->alarm_status === 'acknowledged') {
                $q->whereNotNull('l.responded_datetime');
            }
        });

        // DB-specific hour extraction
        // $driver = DB::getDriverName();
        // $hourExpr = ($driver === 'pgsql')
        //     ? "CAST(EXTRACT(HOUR FROM l.alarm_start_datetime) AS INT)"
        //     : "HOUR(l.alarm_start_datetime)";


        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            $hourExpr = "EXTRACT(HOUR FROM l.alarm_start_datetime)";
        } elseif ($driver === 'sqlite') {
            // SQLite: hour as 00-23 string, cast to int
            $hourExpr = "CAST(strftime('%H', l.alarm_start_datetime) AS INTEGER)";
        } else {
            // MySQL/MariaDB
            $hourExpr = "HOUR(l.alarm_start_datetime)";
        }

        // Aggregate per hour
        $rows = $query
            ->selectRaw("
        $hourExpr AS hour,
        COUNT(*)  AS total
    ")
            ->groupBy(DB::raw($hourExpr))
            ->orderBy(DB::raw($hourExpr))
            ->get();

        // X-axis categories: 00–23
        $categories = array_map(
            fn($h) => str_pad((string) $h, 2, '0', STR_PAD_LEFT),
            range(0, 23)
        );

        // Initialize hourly totals
        $data = array_fill(0, 24, 0);

        // Fill results
        foreach ($rows as $row) {
            $h = (int) $row->hour;
            if ($h >= 0 && $h <= 23) {
                $data[$h] = (int) $row->total;
            }
        }

        // Single-series response
        return response()->json([
            'categories' => $categories,
            'series' => [
                [
                    'name' => 'Total SOS',
                    'data' => $data,
                ],
            ],
            'meta' => [
                'company_id' => $companyId,
                'date_from'  => (isset($from)) ? $from->toDateTimeString() : '--',
                'date_to'    => (isset($to)) ? $to->toDateTimeString() : '--',
                'db_driver'  => $driver,
            ],
        ]);
    }
    public function roomTypesPercentages(Request $request)
    {
        $companyId = (int) $request->company_id;
        if ($request->date_from) {
            $from = Carbon::parse($request->date_from)->startOfDay();
            $to   = Carbon::parse($request->date_to)->endOfDay();
        }
        $labels = $this->roomTypeLabels();

        /*
         * Base query
         */

        if ($request->date_from) {
            $base = DB::table('device_sos_room_logs as l')
                ->join('device_sos_rooms as r', 'r.id', '=', 'l.device_sos_room_table_id')
                ->whereBetween('l.alarm_start_datetime', [$from, $to])
                ->whereNotNull('r.room_type');
        } else {
            $base = DB::table('device_sos_room_logs as l')
                ->join('device_sos_rooms as r', 'r.id', '=', 'l.device_sos_room_table_id')

                ->whereNotNull('r.room_type');
        }

        if ($companyId) {
            $base->where('l.company_id', $companyId);
        }



        $base->when($request->filled('common_search'), function ($q) use ($request) {
            $s = trim($request->common_search);

            $q->where(function ($qq) use ($s) {
                $qq->where('l.room_id', 'ILIKE', "%{$s}%")
                    ->orWhere('l.serial_number', 'ILIKE', "%{$s}%")
                    ->orWhere('l.room_name', 'ILIKE', "%{$s}%")
                    // IMPORTANT: use correct relation name (likely "room")
                    // ->orWhereHas('room', function ($rq) use ($s) {
                    //     $rq->where('room_type', 'ILIKE', "%{$s}%");
                    // })
                    // Optional: device location search
                    // ->orWhereHas('device', function ($dq) use ($s) {
                    //     $dq->where('location', 'ILIKE', "%{$s}%");
                    // })
                ;
            });
        });
        /*
         * SOS status filter
         */
        $base->when($request->filled('sosStatus'), function ($q) use ($request) {
            if ($request->sosStatus === 'ON') {
                $q->where('l.sos_status', true);
            } elseif ($request->sosStatus === 'OFF') {
                $q->where('l.sos_status', false);
            } elseif ($request->sosStatus === 'PENDING') {
                // ON but not responded yet
                $q->where('l.sos_status', true)
                    ->whereNull('l.responded_datetime');
            } elseif ($request->sosStatus === 'RESPONDED') {
                $q->whereNotNull('l.responded_datetime');
            }
        });

        $base->when($request->filled('alarm_status'), function ($q) use ($request) {
            if ($request->alarm_status === 'true') {
                $q->where('l.sos_status', true);
            } elseif ($request->alarm_status === 'false') {
                $q->where('l.sos_status', false);
            } elseif ($request->alarm_status === 'acknowledged') {
                $q->whereNotNull('l.responded_datetime');
            }
        });

        /*
         * Total SOS count (denominator)
         */
        $totalSOS = (clone $base)->count();

        /*
         * Count per room type
         */
        $rows = (clone $base)
            ->selectRaw('r.room_type AS room_type, COUNT(*) AS total')
            ->groupBy('r.room_type')
            ->orderBy('r.room_type')
            ->get();

        /*
         * Build result with percentage
         */
        $items = [];
        foreach ($rows as $row) {
            $count = (int) $row->total;
            $percentage = $totalSOS > 0
                ? round(($count / $totalSOS) * 100, 2)
                : 0;

            $items[] = [
                'room_type' => (string) $row->room_type,
                'label'     => $labels[$row->room_type]
                    ?? ucfirst(str_replace('-', ' ', $row->room_type)),
                'count'     => $count,
                'percentage' => $percentage,
            ];
        }

        /*
         * OPTIONAL: include room types with zero values
         */
        if ($request->boolean('includeZeroTypes', true)) {
            $indexed = collect($items)->keyBy('room_type')->all();
            $items = [];

            foreach ($labels as $key => $label) {
                $count = $indexed[$key]['count'] ?? 0;
                $percentage = $totalSOS > 0
                    ? round(($count / $totalSOS) * 100, 2)
                    : 0;

                $items[] = [
                    'room_type' => $key,
                    'label'     => $label,
                    'count'     => $count,
                    'percentage' => $percentage,
                ];
            }
        }

        return response()->json([
            'total_sos' => (int) $totalSOS,
            'items'     => $items,
            'colors' => ["#3b82f6", "#fb8c00", "#ef4444", "#3b82f6", "#fb8c00", "#ef4444"],
            'meta'      => [
                'company_id' => $companyId,
                'date_from'  => (isset($from)) ? $from->toDateTimeString() : '--',
                'date_to'    => (isset($to)) ? $to->toDateTimeString() : '--',
            ],
        ]);
    }
    public function roomTypes(Request $request)
    {

        return $this->roomTypeLabels();;
    }


    public function SosLogsAnalyticsPdf(Request $request)
    {
        // if (count($request->all()) == 0) {
        //     $request = new Request([
        //         "company_id" => 8,
        //         "date_from" => "2025-12-01",
        //         "date_to" => "2025-12-31",
        //     ]);
        // }


        $report  = $this->getRecords($request);

        $company = Company::query()
            ->whereKey($request->company_id)
            ->with('contact:id,company_id,number')
            ->first();

        $fileName = 'SOS Reports Analysis.pdf';
        $avgResponseMinutes = collect($report)
            ->whereNotNull('response_in_minutes')
            ->avg('response_in_minutes') ?? 0;

        // Optional: round to 2 decimals
        $avgResponseMinutes = round($avgResponseMinutes);
        $hours = floor($avgResponseMinutes / 60);
        $mins  = $avgResponseMinutes % 60;

        $time = sprintf('%02dh:%02dm', $hours, $mins);

        $items = collect($report);
        //-----------------
        // Total SOS in the report
        $total = $items->count();

        $closed = $items->filter(fn($r) => !empty($r->alarm_end_datetime))->count();

        $closedPct = $total > 0 ? round(($closed / $total) * 100, 1) : 0;
        ///----------------
        $roomCounts = [];

        // Count SOS per room
        foreach ($report as $r) {

            // Only valid SOS
            if (empty($r->alarm_start_datetime) || empty($r->room_id)) {
                continue;
            }

            $roomId   = $r->room_id;
            $roomName = $r->room_name ?? '-';

            // Initialize if not exists
            if (!isset($roomCounts[$roomId])) {
                $roomCounts[$roomId] = [
                    'room_id'   => $roomId,
                    'room_name' => $roomName,
                    'count'     => 0,
                ];
            }

            // Increment count
            $roomCounts[$roomId]['count']++;
        }
        $topRoom = null;

        foreach ($roomCounts as $room) {
            if ($topRoom === null || $room['count'] > $topRoom['count']) {
                $topRoom = $room;
            }
        }
        $topRoomName  = $topRoom['room_name'] ?? '-';
        $topRoomId    = $topRoom['room_id'] ?? null;
        $topRoomCount = $topRoom['count'] ?? 0;
        $reportPeriod = "All Time";

        if ($request->filled('date_from'))

            $reportPeriod = $request->date_from . ' To ' . $request->date_to;

        $data = [
            'unitName'          => 'All Rooms',
            'reportPeriod'      =>  $reportPeriod,
            'generatedOn'       => date("Y-m-d H:i:s"),

            'totalCalls'        => count($report),
            'avgResponse'       => $time,
            'resolvedPct'       => $closedPct . '%',
            'topLocation'       => $topRoomName . "(" . $topRoomId . ")",
            'topLocationCalls'  => $topRoomCount . ' calls this period',

            // Pass raw records; map in blade OR map here (recommended below)
            'callLogs'          => $report,
            'company'           => $company,


        ];



        // 4) Room types distribution (bars list)
        $rt = $this->roomTypesPercentages($request)->getData(true);
        $roomTypeItems = $rt  ?? []; // each: label,count,percentage

        $data['roomTypeItems'] = $roomTypeItems;



        $data['chartImage'] =  public_path('storage/' . "reports/charts/hourly_sos.png");
        $data['response_hourly_sosImage'] =  public_path('storage/' . "reports/charts/response_hourly_sos.png");


        //logs
        $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first();
        $data['reports'] = $report;
        $data['request'] = $request;
        $data['company'] = $company;




        //page 1
        $freq = $this->sosHourlyMixedRoomsReport($request)->getData(true);

        $hourValues = $freq["series"][0]["data"] ?? [];
        $maxHour = null;
        $maxValue = null;
        // Guard: empty data
        if (empty($hourValues)) {
            $maxHour = null;
            $maxValue = null;
        } else {
            $maxValue = max($hourValues);
            $maxHour  = array_search($maxValue, $hourValues);
        }
        if ($maxHour) {

            $from = date('g A', strtotime("$maxHour:00"));
            $to   = date('g A', strtotime((($maxHour + 1) % 24) . ":00"));

            $data['maxHour'] = $maxHour ? $from . " - " . $to : null;
        } else {
            $data['maxHour'] =   null;
        }



        $pdf = PDF::loadView('sos.sos-reports-analysis', $data)
            ->setOption('enable-local-file-access', true)

            // ->setOption('encoding', 'utf-8')
            // ->setOption('page-size', 'A4')
            // ->setOption('orientation', 'Portrait')
            ->setOption('margin-top', 14)
            ->setOption('margin-right', 14)
            ->setOption('margin-bottom', 14)
            ->setOption('margin-left', 14)
            // ->setOption('print-media-type', true)
            ->setOption('enable-local-file-access', true)
            ->setpaper("A4", "potrait");







        // return view('sos.sos-reports-analysis', $data);




        return $pdf->stream($fileName);
    }

    public function sosResponseMinutesHourly(Request $request)
    {
        $companyId = (int) $request->company_id;



        $query = DB::table('device_sos_room_logs as l');



        if ($request->filled('date_from')) {
            $from = Carbon::parse($request->date_from)->startOfDay();
            $to   = Carbon::parse($request->date_to)->endOfDay();


            $query->whereBetween('l.alarm_start_datetime', [$from, $to]);
        }


        $query->whereNotNull('l.responded_datetime'); // responded SOS only

        $query->when($request->filled('common_search'), function ($q) use ($request) {
            $s = trim($request->common_search);

            $q->where(function ($qq) use ($s) {
                $qq->where('l.room_id', 'ILIKE', "%{$s}%")
                    ->orWhere('l.serial_number', 'ILIKE', "%{$s}%")
                    ->orWhere('l.room_name', 'ILIKE', "%{$s}%")
                    // IMPORTANT: use correct relation name (likely "room")
                    // ->orWhereHas('room', function ($rq) use ($s) {
                    //     $rq->where('room_type', 'ILIKE', "%{$s}%");
                    // })
                    // Optional: device location search
                    // ->orWhereHas('device', function ($dq) use ($s) {
                    //     $dq->where('location', 'ILIKE', "%{$s}%");
                    // })
                ;
            });
        });
        $query->when($request->filled('alarm_status'), function ($q) use ($request) {
            if ($request->alarm_status === 'true') {
                $q->where('l.sos_status', true);
            } elseif ($request->alarm_status === 'false') {
                $q->where('l.sos_status', false);
            } elseif ($request->alarm_status === 'acknowledged') {
                $q->whereNotNull('l.responded_datetime');
            }
        });

        if ($companyId) {
            $query->where('l.company_id', $companyId);
        }

        // DB-specific expressions
        // $driver = DB::getDriverName();

        // $hourExpr = ($driver === 'pgsql')
        //     ? "CAST(EXTRACT(HOUR FROM l.alarm_start_datetime) AS INT)"
        //     : "HOUR(l.alarm_start_datetime)";

        $driver = DB::getDriverName();

        $driver = DB::getDriverName();

        $hourExpr = ($driver === 'pgsql')
            ? "EXTRACT(HOUR FROM l.alarm_start_datetime)"
            : (($driver === 'sqlite')
                ? "CAST(strftime('%H', l.alarm_start_datetime) AS INTEGER)"
                : "HOUR(l.alarm_start_datetime)");

        $responseMinutesExpr = ($driver === 'pgsql')
            ? "EXTRACT(EPOCH FROM (l.responded_datetime - l.alarm_start_datetime)) / 60"
            : (($driver === 'sqlite')
                ? "(strftime('%s', l.responded_datetime) - strftime('%s', l.alarm_start_datetime)) / 60.0"
                : "TIMESTAMPDIFF(MINUTE, l.alarm_start_datetime, l.responded_datetime)");
        // Aggregate
        $rows = $query
            ->selectRaw("
        $hourExpr AS hour,
        AVG($responseMinutesExpr) AS avg_response_minutes
    ")
            ->groupBy(DB::raw($hourExpr))
            ->orderBy(DB::raw($hourExpr))
            ->get();

        // X-axis categories (00–23)
        $categories = array_map(
            fn($h) => str_pad((string)$h, 2, '0', STR_PAD_LEFT),
            range(0, 23)
        );

        // Zero-filled data
        $data = array_fill(0, 24, 0);

        // Fill results
        foreach ($rows as $row) {
            $h = (int) $row->hour;
            if ($h >= 0 && $h <= 23) {
                // round to 1 decimal for chart readability
                $data[$h] = round((float) $row->avg_response_minutes, 1);
            }
        }

        return response()->json([
            'categories' => $categories,
            'series' => [
                [
                    'name' => 'Avg Response Time (Minutes)',
                    'data' => $data,
                ],
            ],
            'meta' => [
                'company_id' => $companyId,
                'date_from'  => (isset($from)) ? $from->toDateTimeString() : '--',
                'date_to'    => (isset($to)) ? $to->toDateTimeString() : '--',
                'db_driver'  => $driver,
            ],
        ]);
    }
    public function sosDonutStatusCounts(Request $request)
    {
        $companyId = (int) $request->company_id;

        // $request->validate([
        //     'company_id' => 'nullable|integer',
        //     'date_from'  => 'required|date',
        //     'date_to'    => 'required|date|after_or_equal:date_from',
        // ]);
        if ($request->date_from) {
            $from = Carbon::parse($request->date_from)->startOfDay();
            $to   = Carbon::parse($request->date_to)->endOfDay();
        }

        if ($request->date_from) {
            $q = DB::table('device_sos_room_logs as l')
                ->whereBetween('l.alarm_start_datetime', [$from, $to]);
        } else {
            $q = DB::table('device_sos_room_logs as l');
        }

        if ($companyId) {
            $q->where('l.company_id', $companyId);
        }

        if ($request->filled('alarm_status')) {
            if ($request->alarm_status === "true") {
                $q->where('sos_status', true);
            } elseif ($request->alarm_status === "false") {
                $q->where('sos_status', false);
            } elseif ($request->alarm_status === "acknowledged") {
                // Your "none" means acknowledged (active + responded)
                $q->whereNotNull('responded_datetime');
            }
        }

        $row = $q->selectRaw("
        SUM(CASE WHEN l.sos_status = false THEN 1 ELSE 0 END) AS resolved_count,
        SUM(CASE WHEN l.sos_status = TRUE AND l.responded_datetime IS NULL THEN 1 ELSE 0 END) AS pending_count,
        SUM(CASE WHEN     l.responded_datetime IS NOT NULL THEN 1 ELSE 0 END) AS responded_count
    ")->first();

        $resolved  = (int) ($row->resolved_count ?? 0);
        $pending   = (int) ($row->pending_count ?? 0);
        $responded = (int) ($row->responded_count ?? 0);

        return response()->json([
            'labels' => ['Resolved', 'Responded', 'Pending'],
            'series' => [$resolved, $responded, $pending],
            'meta'   => [
                'company_id' => $companyId,
                'date_from'  => (isset($from)) ? $from->toDateTimeString() : '--',
                'date_to'    => (isset($to)) ? $to->toDateTimeString() : '--',
            ],
        ]);
    }
    public function chartRender(Request $request)
    {

        // if (count($request->all()) == 0) {
        //     $request = new Request([
        //         "company_id" => 8,
        //         "date_from" => "2025-12-01",
        //         "date_to" => "2025-12-31",
        //     ]);
        // }

        // 1) Call Frequency (24h)
        $freq = $this->sosHourlyMixedRoomsReport($request)->getData(true);
        $hourLabels = $freq["categories"] ?? [];
        $hourValues = $freq["series"][0]["data"] ?? [];

        // 2) Response Minutes (hourly or daily — you already have hourly)
        $resp = $this->sosResponseMinutesHourly($request)->getData(true);
        $respLabels = $resp["categories"] ?? [];
        $respValues = $resp["series"][0]["data"] ?? [];

        // 3) Status donut
        $donut = $this->sosDonutStatusCounts($request)->getData(true);
        $donutLabels = ['Resolved', 'Responded', 'Pending'];
        $donutSeries = $donut['series'] ?? [0, 0, 0];

        // 4) Room types distribution (bars list)
        $rt = $this->roomTypesPercentages($request)->getData(true);
        $roomTypeItems = $rt  ?? []; // each: label,count,percentage

        return view('sos.sos-chart-render4', [
            'company_id' => (int) $request->company_id,
            'date_from'  => $request->date_from,
            'date_to'    => $request->date_to,

            'hourLabels' => $hourLabels,
            'hourValues' => $hourValues,

            'respLabels' => $respLabels,
            'respValues' => $respValues,

            'donutLabels' => $donutLabels,
            'donutSeries' => $donutSeries,

            'roomTypeItems' => $roomTypeItems,
        ]);
    }
    // public function chartRender4(Request $request)
    // {

    //     if (count($request->all()) == 0) {
    //         $date_from = "2025-12-01";
    //         $date_to = "2025-12-31";

    //         $request = new Request(["company_id" => 8, "date_from" => $date_from, "date_to" => $date_to]);
    //     }

    //     $data = $this->roomTypesPercentages($request);
    //     $data =   $data->getData(true);



    //     $colors = ["#3b82f6", "#22c55e", "#ef4444", "#3b82f6", "#22c55e", "#ef4444"];
    //     $items = [];
    //     foreach ($data["items"] as $key => $value) {
    //         $items[] = ['label' => $value["label"], 'count' => $value["count"], 'percentage' => $value["percentage"], 'color' => $colors[$key]];
    //     }


    //     return view('sos.sos-chart-render-sos-rooms-type', [
    //         'title' => 'SOS Rooms / Sources',
    //         'items' => $items,
    //         'company_id' => $request->company_id,

    //         'nextUrl'    => route('sos.analytics.pdf', $request->query()), // redirect after uploa
    //     ]);
    // }
    // public function chartRender3(Request $request)
    // {

    //     if (count($request->all()) == 0) {
    //         $date_from = "2025-12-01";
    //         $date_to = "2025-12-31";

    //         $request = new Request(["company_id" => 8, "date_from" => $date_from, "date_to" => $date_to]);
    //     }

    //     $data = $this->sosDonutStatusCounts($request);
    //     $data =   $data->getData(true);





    //     return view('sos.sos-chart-render-sos-donut-stats', [
    //         'labels' => ['Resolved', 'Responded', 'Pending'],
    //         'series' => $data['series'] ?? [0, 0, 0],
    //         'company_id' => $request->company_id,

    //         'nextUrl'    => route('sos.analytics.pdf', $request->query()), // redirect after uploa
    //     ]);
    // }
    // public function chartRender2(Request $request)
    // {

    //     if (count($request->all()) == 0) {
    //         $date_from = "2025-12-01";
    //         $date_to = "2025-12-31";

    //         $request = new Request(["company_id" => 8, "date_from" => $date_from, "date_to" => $date_to]);
    //     }

    //     $data = $this->sosResponseMinutesHourly($request);
    //     $data =   $data->getData(true);





    //     return view('sos.sos-chart-render-sos-response-minutes', [
    //         'hourLabels' => $data["categories"],
    //         'hourValues' => $data["series"][0]["data"],
    //         'company_id' => $request->company_id,

    //         'nextUrl'    => route('sos.analytics.pdf', $request->query()), // redirect after uploa
    //     ]);
    // }

    // public function chartRender1(Request $request)
    // {

    //     if (count($request->all()) == 0) {
    //         $date_from = "2025-12-01";
    //         $date_to = "2025-12-31";

    //         $request = new Request(["company_id" => 8, "date_from" => $date_from, "date_to" => $date_to]);
    //     }

    //     $data = $this->sosHourlyMixedRoomsReport($request);
    //     $data =   $data->getData(true);





    //     return view('sos.sos-chart-render', [
    //         'hourLabels' => $data["categories"],
    //         'hourValues' => $data["series"][0]["data"],
    //         'company_id' => $request->company_id,

    //         'nextUrl'    => route('sos.analytics.pdf', $request->query()), // redirect after uploa
    //     ]);
    // }

    public function storeChart(Request $request)
    {

        //return $request->file('chart');
        if (!$request->hasFile('chart')) {
            return response()->json(['message' => 'No chart received'], 422);
        }

        $file = $request->file('chart');

        if (!$file->isValid()) {
            return response()->json(['message' => 'Invalid file'], 422);
        }

        // Store into public disk
        $path = $file->storeAs('reports/charts', $request->filename, 'public');

        return response()->json([
            'ok' => true,
            //'pdf_url' => route('geenratecharts', ['chart' => $path]),
        ]);
    }
}
