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

        // Total rooms for this company
        $totalRooms = Device::where('company_id', $companyId)->count();

        // Base query for today's logs
        $todayLogsQuery = ParkingCameraLogs::where('company_id', $companyId)
            ->whereDate('in_time', $today);

        // All logs for today
        $logs = $todayLogsQuery->get();

        // Rooms currently occupied = in today & out_time is NULL
        $occupiedRooms = (clone $todayLogsQuery)
            ->whereNull('out_time')   // <--- make sure this matches your DB column
            ->count();

        $availableRooms = $totalRooms - $occupiedRooms;

        $data = [
            'totalRooms'         => $totalRooms,
            'occupiedRooms'      => $occupiedRooms,
            'availableRooms'     => max($availableRooms, 0),
            'todayVehiclesCount' => $logs->count(),
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



            if ($lastEvent) {

                $lastEvent["public_image_url"] =  env("BASE_URL") . '/api/parking_camera_logs' . '/' .  $lastEvent->company_id;


                $roomsList[$key]->vehicle =  $lastEvent;
                $roomsList[$key]->status = $lastEvent->event_type;
                $roomsList[$key]->last_event_time = $lastEvent->created_at;
            } else {
                $roomsList[$key]->vehicle =  null;
                $roomsList[$key]->status = 'empty';
                $roomsList[$key]->last_event_time = null;
            }
        }



        return $roomsList;
    }
}
