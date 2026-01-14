<?php

namespace App\Http\Controllers;

use App\Exports\ParkingReports;
use App\Models\Company;
use App\Models\Device;
use App\Models\ParkingCameraLogs;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;

class ParkingCameraLogsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        /*$model = ParkingCameraLogs::with(["ParkingMembers", "ParkingMembersGuest"])->where('company_id', $request->company_id);;

        $model->when($request->filled('member_id'), function ($q) use ($request) {
            $q->where('membership_id', $request->member_id);
        });

        $model->when($request->filled('filter_duration'), function ($q) use ($request) {

            if ($request->filter_duration == '1')
                $q->where('duration_in_minutes', '>', 60);

            else if ($request->filter_duration == '2')
                $q->where('duration_in_minutes', '<=', 60);
        });
        $model->when($request->filled('date_from'), function ($q) use ($request) {
            $q->whereDate('in_time', '>=', $request->date_from);
            $q->whereDate('in_time', '<=', $request->date_to);
        });
        // $model->when($request->filled('filter_payment'), function ($q) use ($request) {
        //     if ($request->filter_payment == 'Cash')
        //         $q->where('payment_mode', "cash");

        //     else if ($request->filter_payment == 'Online')
        //         $q->where('payment_mode', "online");
        //     else if ($request->filter_payment == 'Pending')
        //         $q->where('payment_mode', null);
        // });
        $model->when($request->filled('common_search'), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('total_amount', 'ILIKE', "%$request->common_search%")
                    ->orWhere('log_vehicle_number', 'ILIKE', "%$request->common_search%")

                    ->orWhere('raw_plate_no', 'ILIKE', "%$request->common_search%")
                    ->orWhere('raw_vehicle_color', 'ILIKE', "%$request->common_search%")
                    ->orWhere('raw_vehicle_type', 'ILIKE', "%$request->common_search%")
                    ->orWhere('raw_vehicle_brand', 'ILIKE', "%$request->common_search%")
                    ->orWhere('raw_country_region', 'ILIKE', "%$request->common_search%")
                    ->orWhere('raw_plate_color', 'ILIKE', "%$request->common_search%")
                    ->orWhere('raw_plate_size', 'ILIKE', "%$request->common_search%")
                    ->orWhere('raw_plate_type', 'ILIKE', "%$request->common_search%")
                    ->orWhere('raw_province', 'ILIKE', "%$request->common_search%")
                    ->orWhere('raw_camera_no', 'ILIKE', "%$request->common_search%")
                    ->orWhere('raw_event_category', 'ILIKE', "%$request->common_search%");
            });
        });

        // $model->when($request->filled('branch_id'), function ($q) use ($request) {
        //     $q->where('branch_id', $request->branch_id);
        // });





        $model->orderBy("updated_at", "DESC");
        return $model->paginate($request->per_page ?? 10);*/

        return $this->getRecords($request);
    }
    public function getLiveVehicleLogs(Request $request)
    {
        $companyId = $request->company_id;

        // Base IN logs
        $inLogs = ParkingCameraLogs::select(
            'id',
            'log_vehicle_number',
            'raw_country_region',
            DB::raw("'IN' as direction"),
            'company_id',
            'membership_id',
            // full info for detail panel
            'in_time as log_time_in',
            'out_time as log_time_out',
            'in_time as log_time', // for list display
            'duration_in_minutes',
            'duration_in_hours',
            'duration_per_hour_amount',
            'total_amount',
            'payment_mode',
        )
            ->where('company_id', $companyId)
            ->whereDate('in_time', date('Y-m-d'))
            //->limit(20)
            ->whereNotNull('in_time');

        // Base OUT logs
        $outLogs = ParkingCameraLogs::select(
            'id',
            'log_vehicle_number',
            'raw_country_region',
            DB::raw("'OUT' as direction"),
            'company_id',
            'membership_id',
            'in_time as log_time_in',
            'out_time as log_time_out',
            'out_time as log_time', // for list display
            'duration_in_minutes',
            'duration_in_hours',
            'duration_per_hour_amount',
            'total_amount',
            'payment_mode',
        )
            ->where('company_id', $companyId)
            ->whereNotNull('out_time');

        $union = $inLogs->unionAll($outLogs);

        $logs = DB::query()
            ->fromSub($union, 'logs')
            ->orderBy('log_time', 'desc')
            ->limit(20)
            ->get();

        return ['data' => $logs];
    }
    public function getRecords(Request $request, $perpage = null)
    {
        $model = ParkingCameraLogs::with(["ParkingMembers", "ParkingMembersGuest"])->where('company_id', $request->company_id);;

        $model->when($request->filled('member_id'), function ($q) use ($request) {
            $q->where('membership_id', $request->member_id);
        });

        $model->when($request->filled('filter_duration'), function ($q) use ($request) {

            if ($request->filter_duration == '1')
                $q->where('duration_in_minutes', '>', 60);

            else if ($request->filter_duration == '2')
                $q->where('duration_in_minutes', '<=', 60);
        });
        $model->when($request->filled('date_from'), function ($q) use ($request) {
            $q->whereDate('in_time', '>=', $request->date_from);
            $q->whereDate('in_time', '<=', $request->date_to);
        });
        // $model->when($request->filled('filter_payment'), function ($q) use ($request) {
        //     if ($request->filter_payment == 'Cash')
        //         $q->where('payment_mode', "cash");

        //     else if ($request->filter_payment == 'Online')
        //         $q->where('payment_mode', "online");
        //     else if ($request->filter_payment == 'Pending')
        //         $q->where('payment_mode', null);
        // });
        $model->when($request->filled('common_search'), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('total_amount', 'ILIKE', "%$request->common_search%")
                    ->orWhere('log_vehicle_number', 'ILIKE', "%$request->common_search%")

                    ->orWhere('raw_plate_no', 'ILIKE', "%$request->common_search%")
                    ->orWhere('raw_vehicle_color', 'ILIKE', "%$request->common_search%")
                    ->orWhere('raw_vehicle_type', 'ILIKE', "%$request->common_search%")
                    ->orWhere('raw_vehicle_brand', 'ILIKE', "%$request->common_search%")
                    ->orWhere('raw_country_region', 'ILIKE', "%$request->common_search%")
                    ->orWhere('raw_plate_color', 'ILIKE', "%$request->common_search%")
                    ->orWhere('raw_plate_size', 'ILIKE', "%$request->common_search%")
                    ->orWhere('raw_plate_type', 'ILIKE', "%$request->common_search%")
                    ->orWhere('raw_province', 'ILIKE', "%$request->common_search%")
                    ->orWhere('raw_camera_no', 'ILIKE', "%$request->common_search%")
                    ->orWhere('raw_event_category', 'ILIKE', "%$request->common_search%");
            });
        });

        // $model->when($request->filled('branch_id'), function ($q) use ($request) {
        //     $q->where('branch_id', $request->branch_id);
        // });





        $model->orderBy("updated_at", "DESC");

        if (request()->has('perPage') || request()->has('page') || $perpage) {
            return   $model->paginate($request->per_page ?? 10);
        } else {
            return $model->get();
        }
    }

    public function parkingRecordInfo(Request $request)
    {
        return ParkingCameraLogs::where('company_id', $request->company_id)->where('id', $request->id)->first();
    }

    public function parkingPaymentUpdate(Request $request)
    {

        try {
            $model = ParkingCameraLogs::where('company_id', $request->company_id)->where('id', $request->id);;


            $data = [
                'payment_mode' => $request->payment_mode,

            ];

            $model->update($data);

            return $this->response("success", null, true);
        } catch (\Throwable $e) {
            return $this->response("error", $e->getMessage(), false);
        }
    }

    public function parkingDashboardStatistics(Request $request)
    {

        $totalParked   = ParkingCameraLogs::where('company_id', $request->company_id)->whereNull('out_time')->count();

        $vehicle_count_today  =   ParkingCameraLogs::where('company_id', $request->company_id)

            ->whereDate('in_time', date('Y-m-d'))

            ->count();


        $totalPayments  =   ParkingCameraLogs::where('company_id', $request->company_id)
            ->whereNotNull('out_time')
            ->whereDate('out_time',  date('Y-m-d'))

            ->sum('total_amount');


        $Total = Company::where('id', $request->company_id)->first()->parking_count ?? 0;

        $TotalDevices = Device::where('company_id', $request->company_id)->count();
        $TotdevicesOnlineCount = Device::where('company_id', $request->company_id)->where("status_id", 1)->count();



        return  [
            'vehicle_count_today' => $vehicle_count_today,

            'total_parked' => $totalParked,
            'total_available' => $Total - $totalParked,
            'total_parking_count' => $Total,
            'total_payments' => $totalPayments,
            'devices_offline_count' => $TotalDevices - $TotdevicesOnlineCount,
            'devices_online_count' => $TotdevicesOnlineCount,




        ];
    }
    public function ParkingCameraLogsPrintPdf(Request $request)
    {

        $report =  (new ParkingCameraLogsController)->getRecords($request);
        $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first();

        $fileName = "Parking List.pdf";


        return   Pdf::loadview("parking/parking-reports", ["request" => $request, "reports" => $report, "company" => $company])->setpaper("A4", "potrait")->stream($fileName);
    }
    public function ParkingCameraLogsDownloadPdf(Request $request)
    {

        $report =  (new ParkingCameraLogsController)->getRecords($request);
        $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first();

        $fileName = "Parking List.pdf";
        return   Pdf::loadview("parking/parking-reports", ["request" => $request, "reports" => $report, "company" => $company])->setpaper("A4", "potrait")->download($fileName);
    }
    public function ParkingCameraLogsDownloadCSV(Request $request)
    {

        $reports =  (new ParkingCameraLogsController)->getRecords($request);

        $fileName = "Parking Reports.xlsx";

        return Excel::download((new ParkingReports($reports)), $fileName);
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
     * @param  \App\Models\ParkingCameraLogs  $parkingCameraLogs
     * @return \Illuminate\Http\Response
     */
    public function show(ParkingCameraLogs $parkingCameraLogs)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ParkingCameraLogs  $parkingCameraLogs
     * @return \Illuminate\Http\Response
     */
    public function edit(ParkingCameraLogs $parkingCameraLogs)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ParkingCameraLogs  $parkingCameraLogs
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ParkingCameraLogs $parkingCameraLogs)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ParkingCameraLogs  $parkingCameraLogs
     * @return \Illuminate\Http\Response
     */
    public function destroy(ParkingCameraLogs $parkingCameraLogs)
    {
        //
    }
    public function print($id)
    {
        $receipt = ParkingCameraLogs::with('company')->where("id", $id)->first();

        if ($receipt) {

            $pdf = Pdf::loadView('parking.parking-print', compact('receipt'))
                ->setPaper('A5', 'portrait'); // or 'a4'

            return $pdf->download("parking-receipt-{$id}.pdf");
        } else {
            echo "Receipt not found";
        }


        // return view('parking.parking-print', compact('receipt'));
    }

    public function ParkingPaymentResponseUpdate(Request $request)
    {


        if ($request->filled("parking_id") && $request->filled("payment_response") && $request->filled("fee")) {
            $parkingLog = ParkingCameraLogs::with("company")->where("id", $request->parking_id)->first();

            $payment_response = $request->payment_response;
            $fee = $request->fee;





            $data = [
                'payment_mode' => "Online",
                'payment_response_code' => $payment_response["id"],
                //'out_time' => $fee["out_time"],
                'payment_datetime' => $fee["out_time"],
                'duration_in_minutes' => $fee["duration_in_minutes"],
                'duration_in_hours' => $fee["duration_in_hours"],
                'duration_per_hour_amount' => (int) $fee["duration_per_hour_amount"],
                'total_amount' =>   $fee["total_amount"],
                'parking_exit_buffertime' =>   $parkingLog->company["parking_exit_buffertime"],


            ];

            $parkingLog->update($data);

            // ✅ Reload the updated row from DB

            $parkingLog = ParkingCameraLogs::with("company")->where("id", $request->parking_id)->first();


            return $this->response("succeess", $parkingLog, true);
        }

        return $this->response("error", "Information is missing", false);
    }

    public function qrParkingExtraPayment(Request $request)
    {

        if ($request->filled("log_id")) {


            $open = ParkingCameraLogs::where("id", $request->log_id)->first();;
            $in_time =  Carbon::parse($open->in_time);
            $alreadyPaidForHours =  $open->duration_in_hours;




            // Calculate expiry = payment time + buffer minutes
            $out_time = $in_time->copy()->addHours($alreadyPaidForHours);

            $open->out_time = $out_time;
            $open->save();


            $newLogAfterBufferTime = [

                "prev_parking_log_id" => $open->id,
                "company_id" => $open->company_id,
                "log_timestamp" => $open->log_timestamp,
                "log_vehicle_number" => $open->log_vehicle_number,
                "in_background_file_name" => $open->in_background_file_name,

                "in_time" => $out_time,
                "raw_device_no" => $open->raw_device_no,
                "raw_capture_time" => $open->raw_capture_time,
                "raw_plate_no" => $open->raw_plate_no,
                "raw_vehicle_color" => $open->raw_vehicle_color,
                "raw_vehicle_type" => $open->raw_vehicle_type,
                "raw_vehicle_brand" => $open->raw_vehicle_brand,
                "raw_moving_direction" => $open->raw_moving_direction,
                "raw_validity" => $open->raw_validity,
                "raw_country_region" => $open->raw_country_region,
                "raw_plate_color" => $open->raw_plate_color,
                "raw_plate_size" => $open->raw_plate_size,
                "raw_plate_type" => $open->raw_plate_type,
                "raw_province" => $open->raw_province,
                "raw_camera_no" => $open->raw_camera_no,
                "raw_info" => $open->raw_info,
                "raw_event_type" => $open->raw_event_type,
                "raw_camera_code" => $open->raw_camera_code,
                "raw_direction" => $open->raw_direction,
                "raw_lane" => $open->raw_lane,
                "device_id_in" => $open->device_id_in,

            ];

            $new = ParkingCameraLogs::create($newLogAfterBufferTime);



            return $this->response("success", $new, true);
        } else {
            return $this->response("Information is not available", null, false);
        }
    }

    public function ParkingCameraImage($company, $filename)
    {


        // // Base folder where your camera images are stored
        // $basePath = env('PARKING_CAMERA_STORAGE_PATH');

        // // Full path to image
        // $filePath = $basePath . DIRECTORY_SEPARATOR . $company . DIRECTORY_SEPARATOR . $filename;



        $basePath = rtrim(env('PARKING_CAMERA_STORAGE_PATH'), DIRECTORY_SEPARATOR);
        $todayPrefix = now()->format('Ymd');
        $filePrefix = null;

        // Try to extract 8-digit date prefix (e.g., 20251013)
        if (preg_match('/^(\d{8})/', $filename, $matches)) {
            $filePrefix = $matches[1];
        }

        // Default path (for today's files or files without prefix)
        $filePath = $basePath . DIRECTORY_SEPARATOR . $company . DIRECTORY_SEPARATOR . $filename;

        // If prefix is found and not today's date, look inside that subfolder
        if ($filePrefix && $filePrefix !== $todayPrefix) {
            $filePath = $basePath . DIRECTORY_SEPARATOR . $company . DIRECTORY_SEPARATOR . $filePrefix . DIRECTORY_SEPARATOR . $filename;
        }










        // If file not found, return 404
        if (!File::exists($filePath)) {



            $fallbackPath = public_path('image_not_found.png');

            // If even fallback missing, return 404 JSON
            if (!File::exists($fallbackPath)) {
                return response()->json(['error' => 'Image not found'], 404);
            }

            $filePath = $fallbackPath;
        }

        // Detect MIME type (e.g. image/jpeg)
        $mimeType = File::mimeType($filePath);

        // Return actual image content
        return Response::file($filePath, ['Content-Type' => $mimeType]);
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
}
