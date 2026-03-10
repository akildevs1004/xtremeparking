<?php

namespace App\Http\Controllers\CarWashing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\UpdateRequest;
use App\Http\Requests\Customer\StoreRequest;
use App\Models\AlarmEvents;
use App\Models\Customers\CustomerContacts;
use App\Models\Customers\Customers;
use App\Models\CustomersBuildingTypes;
use App\Models\Deivices\DeviceZones;
use App\Models\Device;
use App\Models\ParkingCameraLogs;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use function Aws\filter;

class CarDashboardController extends Controller
{
    public function Dashboardstatistics(Request $request)
    {

        $companyId = $request->company_id;
        $today     = Carbon::today()->toDateString();

        // Total rooms
        $totalRooms = Device::where('company_id', $companyId)->count();

        // Base query for today's logs
        $todayLogsQuery = ParkingCameraLogs::where('company_id', $companyId);
        // ->whereDate('in_time', $today);

        // All logs for today
        $logs = $todayLogsQuery->get();

        // Occupied rooms = in today & out_time is NULL
        $occupiedRooms = (clone $todayLogsQuery)
            ->whereNull('out_time')
            ->count();

        $availableRooms = max($totalRooms - $occupiedRooms, 0);

        // ⏱️ Duration-based counts
        $moreThanOneHour = (clone $todayLogsQuery)
            ->where('duration_in_minutes', '>', 60)
            ->count();

        $lessThanOneHour = (clone $todayLogsQuery)
            ->where('duration_in_minutes', '<=', 60)
            ->count();

        $data = [
            'totalRooms'         => $totalRooms,
            'occupiedRooms'      => $occupiedRooms,
            'availableRooms'     => $availableRooms,
            'todayVehiclesCount' => $logs->count(),

            // New
            'moreThanOneHour'    => $moreThanOneHour,
            'lessThanOneHour'    => $lessThanOneHour,
        ];

        return response()->json($data);
    }
    public function roomsListStatus(Request $request)
    {

        $roomsList = Device::where('company_id', $request->company_id)->orderby("name", "asc")->get();




        foreach ($roomsList as $key => $room) {

            $lastEvent = ParkingCameraLogs::where('device_id_in', $room->id)
                ->where('company_id', $request->company_id)
                ->orderBy('in_time', 'desc')
                ->first();

            // Get device timezone or fallback to Asia/Dubai
            $tz = $room->utc_time_zone ?? 'Asia/Dubai';

            if ($lastEvent) {

                // "Now" in device timezone
                $deviceNow = now()->setTimezone($tz);

                // out_time in device timezone (if exists)
                $outTime = $lastEvent->out_time
                    ? Carbon::parse($lastEvent->out_time)->setTimezone($tz)
                    : null;

                // If out_time exists AND more than 1 hour ago in device time → mark as empty
                if ($outTime && $deviceNow->diffInHours($outTime) > 1) {

                    $roomsList[$key]->vehicle = null;
                    $roomsList[$key]->status  = 'empty';
                    $roomsList[$key]->last_event_time = null;
                } else {

                    // Valid event (no out_time or within 1 hour)
                    $lastEvent->public_image_url = env("BASE_URL") . '/api/parking_camera_logs/' . $lastEvent->company_id;

                    $roomsList[$key]->vehicle = $lastEvent;
                    $roomsList[$key]->status  = $lastEvent->event_type;
                    $roomsList[$key]->last_event_time = $lastEvent->created_at;
                }
            } else {

                // No event at all
                $roomsList[$key]->vehicle = null;
                $roomsList[$key]->status  = 'empty';
                $roomsList[$key]->last_event_time = null;
            }
        }



        return $roomsList;
    }
}
