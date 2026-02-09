<?php

namespace App\Http\Controllers;

use App\Jobs\DeviceCommandsJob;
use App\Models\CustomerProductServices;
use App\Models\Customers\CustomerPayments;
use App\Models\Device;
use App\Models\ParkingCameraLogs;
use App\Models\ParkingMembers;
use App\Models\TaxSlabs;
use App\Services\MqttService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ParkingDeviceController extends Controller
{


    public function mqttOpenGateCommand($request)
    {
        if ($request->device_serial_number) {

            if ($request->function == 'in') {
                $postData = [
                    "action" => "UPDATE_CONFIG",
                    "serialNumber" => $request->device_serial_number,
                    "config" => ["relay0" => true],
                ];
            } else if ($request->function == 'out') {
                $postData = [
                    "action" => "UPDATE_CONFIG",
                    "serialNumber" => $request->device_serial_number,
                    "config" => ["relay1" => true],
                ];
            }


            // Publish  to MQTT
            $mqtt = new MqttService();
            $mqtt->publish(env('MQTT_DEVICE_CLIENTID') . "/{$request->device_serial_number}/config/request",  json_encode($postData), $request->device_serial_number);
        } else {
            return $this->response('Device Serial Number Not Found', null, false);
        }
    }
    // public function mqttCloseGateCommand($request)
    // {
    //     if ($request->device_serial_number) {
    //         $postData = [
    //             "action" => "UPDATE_CONFIG",
    //             "serialNumber" => $request->device_serial_number,
    //             "config" => ["relay0" => false],
    //         ];

    //         // Publish  to MQTT
    //         $mqtt = new MqttService();
    //         $mqtt->publish(env('MQTT_DEVICE_CLIENTID') . "/{$request->device_serial_number}/config/request",  json_encode($postData), $request->device_serial_number);
    //     } else {
    //         return $this->response('Device Serial Number Not Found', null, false);
    //     }
    // }
    public function OpenGateManually(Request $request)
    {
        $request->validate([
            'company_id' => 'required|integer',
            'event_id' => 'nullable|integer', //memberid
            'device_id' => 'nullable|integer', //memberid
            'trigger' => 'required|string', //memberid
            'device_serial_number' => 'nullable|string', //memberid
            "function" => 'required|string', //memberid
            "parking_gate_close_time" => 'nullable', //memberid


        ]);

        $request = new Request([
            'company_id' => $request->company_id,


            'trigger' => $request->trigger, //memberid
            'device_serial_number' => Device::where('company_id', $request->company_id)->first()->serial_number ?? null, //memberid
            "function" => $request->function, //memberid
            "parking_gate_close_time" => $request->parking_gate_close_time, //memberid

        ]);

        return $this->OpenGate($request);
    }
    public function OpenGate(Request $request)
    {
        $request->validate([
            'company_id' => 'required|integer',
            'event_id' => 'nullable|integer', //memberid
            'device_id' => 'nullable|integer', //memberid
            'trigger' => 'required|string', //memberid
            'device_serial_number' => 'required|string', //memberid
            "function" => 'required|string', //memberid
            "parking_gate_close_time" => 'nullable', //memberid


        ]);


        //try {
        $this->mqttOpenGateCommand($request);

        // $postData = [
        //     "action" => "UPDATE_CONFIG",
        //     "serialNumber" => $request->device_serial_number,
        //     "config" => ["relay0" => false],
        // ];

        if ($request->function == 'in') {
            $postData = [
                "action" => "UPDATE_CONFIG",
                "serialNumber" => $request->device_serial_number,
                "config" => ["relay0" => false],
            ];
        } else if ($request->function == 'out') {
            $postData = [
                "action" => "UPDATE_CONFIG",
                "serialNumber" => $request->device_serial_number,
                "config" => ["relay1" => false],
            ];
        }
        $delaySec = (int) ($request->input('parking_gate_close_time', 10));

        $deviceJobs = new DeviceCommandsJob($postData); //run Job and command after 5 seconds
        $this->dispatch($deviceJobs->delay(now()->addSeconds($delaySec)));



        // } catch (\Exception $e) {
        // }

        if ($request->event_id) {

            if ($request->trigger == 'manual') {
                ParkingCameraLogs::where("id", $request->event_id)
                    ->where("company_id", $request->company_id)

                    ->update([
                        "manual_gate_opened_at" => date("Y-m-d H:i:s")
                    ]);
            } else if ($request->trigger == 'automatic') {
                ParkingCameraLogs::where("id", $request->event_id)
                    ->where("company_id", $request->company_id)

                    ->update([
                        "automatic_gate_opened_at" => date("Y-m-d H:i:s")
                    ]);
            }
        }
        return $this->response('Gate Opened', null, true);
    }

    public function DeviceAcknowledged(Request $request)
    {
        $request->validate([

            'event_id' => 'required|integer', //memberid

        ]);
    }
}
