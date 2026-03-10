<?php

namespace App\Http\Controllers\Alarm\Api;

use App\Console\Commands\SendWhatsappNotification;
use App\Http\Controllers\Controller;
use App\Http\Controllers\WhatsappController;
use App\Mail\DbBackupMail;
use App\Mail\EmailContentDefault;
use App\Mail\ReportNotificationMail;
use App\Models\Alarm\DeviceSensorLogs;
use Illuminate\Http\Request;
use App\Models\Community\AttendanceLog;
use App\Models\Company;
use App\Models\Device;
use App\Models\NurseCallingLogs;
use App\Models\ReportNotification;
use App\Models\ReportNotificationLogs;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ApiNurseCallingControlController extends Controller
{
    public function ApiLogDeviceStatus(Request $request)
    {



        // try {
        Storage::append("logs/nurse-calling-system/api-requests-device-" . date('Y-m-d') . ".txt", date("Y-m-d H:i:s") .  " : "    . json_encode($request->all()));

        $switch1 = -1;
        $switch2 = -1;

        $log_time = date('Y-m-d H:i:s');





        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://alarmbackend.xtremeguard.org/api/alarm_device_status',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
            "serialNumber": "' . $request->serialNumber . '",    
            "alarm_status":1, 
                }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);




        $device_serial_number = $request->serialNumber;
        $switch1_status = $request->switch1_status;
        $switch2_status = $request->switch2_status;


        if ($device_serial_number != '') {

            $logs = [];
            $logs["serial_number"] = $device_serial_number;

            if ($request->filled("switch1_status")) {

                $logs["switch1_status"] = $request->switch1_status;
            }

            if ($request->filled("switch2_status")) {
                $logs["switch2_status"] = $request->switch2_status;
            }


            $logs["log_time"] = $log_time;



            NurseCallingLogs::create($logs);


            return $this->response('Successfully Updated', null, true);
        }
        // } catch (\Exception $e) {
        //     Storage::append("logs/nurse-calling-system-error/api-requests-device-" . date('Y-m-d') . ".txt", date("Y-m-d H:i:s") .  " : "    . json_encode($request->all()) . ' \n' . $e->getMessage());

        //     return  $e->getMessage();
        // }




        return $this->response('Data error', null, false);
    }
}
