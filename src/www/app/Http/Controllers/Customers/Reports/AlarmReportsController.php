<?php

namespace App\Http\Controllers\Customers\Reports;

use App\Exports\ExportAlarmEventsCustomerGroup;
use App\Exports\AlarmEventsExport;
use App\Exports\DeviceArmedExport;
use App\Exports\DeviceArmedReportExcelMapping;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Customers\Alarm\AlarmNotificationController;
use App\Http\Controllers\Customers\Alarm\DeviceArmedLogsController;
use App\Http\Controllers\Customers\CustomerAlarmEventsController;
use App\Models\AlarmEvents;
use App\Models\AlarmLogs;
use App\Models\AttendanceLog;
use App\Models\Company;
use App\Models\Customers\CustomerAlarmEvents;
use App\Models\Customers\CustomerPayments;
use App\Models\Customers\Customers;
use App\Models\Deivices\DeviceZones;
use App\Models\SalesQuotations;
use App\Models\TicketSensorTest;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Facades\Excel;

class AlarmReportsController extends Controller
{
    public function alarmEventsExportCsv(Request $request)
    {
        $model =   (new CustomerAlarmEventsController())->filter($request);
        $model->orderBy("alarm_start_datetime", "asc");

        if ($request->date_from == '') {
            $model = $model->limit(50);
        }
        $file_name =  'Alarm Events from ' . $request->date_from . ' to ' . $request->date_to . ' .xlsx';
        $reports = $model->get();
        return Excel::download((new AlarmEventsExport($reports)), $file_name);
    }
    public function alarmEventsDownloadPdf(Request $request)
    {
        $file_name =  'Alarm Events from ' . $request->date_from . ' to ' . $request->date_to . ' .pdf';

        $model =   (new CustomerAlarmEventsController())->filter($request);
        $modelCount = $model->clone();


        $countResults = $modelCount->selectRaw("
                COUNT(CASE WHEN alarm_type = 'SOS'    THEN 1 END) as sosCount,
               COUNT(CASE WHEN alarm_category = 1 AND alarm_type != 'SOS'   THEN 1 END) as criticalCount,
                COUNT(CASE WHEN alarm_type = 'Offline'   THEN 1 END) as technicalCount,
                COUNT(CASE WHEN alarm_type IS NOT NULL  AND alarm_type != 'SOS'   AND alarm_category != 1 THEN 1 END) as eventsCount,
                COUNT(CASE WHEN alarm_type = 'Temperature' THEN 1 END) as temperatureCount,
                COUNT(CASE WHEN alarm_type = 'Water' THEN 1 END) as waterCount,
                COUNT(CASE WHEN alarm_type = 'Medical' THEN 1 END) as medicalCount,
                COUNT(CASE WHEN alarm_type = 'Fire' THEN 1 END) as fireCount
            ")->first();


        $model->orderBy("alarm_start_datetime", "asc");

        $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first();
        if ($request->date_from == '') {
            $model = $model->limit(50);
        }
        $reports = $model->get();
        $pdf = Pdf::loadView('alarm_reports/alarm_events_list',  ["counts" => $countResults, 'reports' => $reports, 'company' => $company, "request" => $request])->setPaper('A4', 'potrait');
        return $pdf->download($file_name);
    }
    public function alarmEventsPrintPdf(Request $request)
    {


        $model =   (new CustomerAlarmEventsController())->filter($request);

        $model->whereHas("device.customer");

        $modelCount = $model->clone();


        $countResults = $modelCount->selectRaw("
                COUNT(CASE WHEN alarm_type = 'SOS'    THEN 1 END) as sosCount,
               COUNT(CASE WHEN alarm_category = 1 AND alarm_type != 'SOS'  THEN 1 END) as criticalCount,
                COUNT(CASE WHEN alarm_type = 'Offline'   THEN 1 END) as technicalCount,
                COUNT(CASE WHEN alarm_type IS NOT NULL  AND alarm_type != 'SOS'   AND alarm_category != 1 THEN 1 END) as eventsCount,
                COUNT(CASE WHEN alarm_type = 'Temperature' THEN 1 END) as temperatureCount,
                COUNT(CASE WHEN alarm_type = 'Water' THEN 1 END) as waterCount,
                COUNT(CASE WHEN alarm_type = 'Medical' THEN 1 END) as medicalCount,
                COUNT(CASE WHEN alarm_type = 'Fire' THEN 1 END) as fireCount
            ")->first();






        $model->orderBy("alarm_start_datetime", "asc");


        if ($request->date_from == '') {
            $model = $model->limit(50);
        }


        $reports = $model->get();
        $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first();


        $pdf = Pdf::loadView('alarm_reports/alarm_events_list',  ['reports' => $reports, 'company' => $company, "request" => $request, "counts" => $countResults])->setPaper('A4', 'potrait');
        return $pdf->stream('report.pdf');
    }
    //----------------------------------------DEVICE ARMED REPORTS
    public function deviceArmedLogsPrintPdf(Request $request)
    {
        $model =   (new DeviceArmedLogsController())->filter($request);
        $model->orderBy("armed_datetime", "asc");
        $reports = $model->get();
        $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first();


        $pdf = Pdf::loadView('alarm_reports/device_armed_list',  ['reports' => $reports, 'company' => $company, "request" => $request])->setPaper('A4', 'potrait');
        return $pdf->stream('report.pdf');
    }
    public function deviceArmedLogsDownloadPdf(Request $request)
    {
        $model =   (new DeviceArmedLogsController())->filter($request);
        $model->orderBy("armed_datetime", "asc");
        $reports = $model->get();
        $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first();
        $file_name =  'Device Armed Reports from ' . $request->date_from . ' to ' . $request->date_to . ' .pdf';

        $pdf = Pdf::loadView('alarm_reports/device_armed_list',  ['reports' => $reports, 'company' => $company, "request" => $request])->setPaper('A4', 'potrait');
        return $pdf->download($file_name);
    }
    public function deviceArmedLogsExportExcel(Request $request)
    {
        $model =   (new DeviceArmedLogsController())->filter($request);
        $model->orderBy("armed_datetime", "asc");
        $reports = $model->get();

        $file_name =  'Device Armed Reports from ' . $request->date_from . ' to ' . $request->date_to . ' .xlsx';

        return Excel::download((new DeviceArmedExport($reports)), $file_name);
    }
    public function quotationPrintPdf(Request $request)
    {



        if (!$request->filled('quotation_id')) return [];
        $invoice =   SalesQuotations::with(["company",   "ProductService"])->where("id", $request->quotation_id)->first();


        $company =  $invoice->company;
        $customer =  $invoice;






        $pdf = Pdf::loadView('alarm_reports/sales_quotation', compact('invoice',  "company",  "customer"))->setPaper('A4', 'potrait');

        if ($request->type == 'print')
            return $pdf->stream($request->alarm_id . "_event_track_notes.pdf");
        else
            return $pdf->download($request->alarm_id . "_event_track_notes.pdf");
    }
    public function invoicePrintPdf(Request $request)
    {



        if (!$request->filled('invoice_id')) return [];
        $invoice =   CustomerPayments::with(["company", "customer.primary_contact", "customer_product_services.device_product_service"])->where("id", $request->invoice_id)->first();


        $company =  $invoice->company;
        $customer =  $invoice->customer;


        $pdf = Pdf::loadView('alarm_reports/customer_invoice', compact('invoice',  "company",  "customer"))->setPaper('A4', 'potrait');

        if ($request->type == 'print')
            return $pdf->stream($request->alarm_id . "_event_track_notes.pdf");
        else
            return $pdf->download($request->alarm_id . "_event_track_notes.pdf");
    }
    public function alarmEventsNotesDownload(Request $request)
    {

        if (!$request->filled('alarm_id')) return [];
        $alarm =   AlarmEvents::with([
            "device.customer.primary_contact",
            "device.customer.secondary_contact",
            "device.company.user",
            "notes.contact",
            "category",
            "device.customer.buildingtype",
            "zoneData",
            "security.user",
            "pinverifiedby"

        ])->where("id", $request->alarm_id)->first();

        $icons = (new AlarmNotificationController())->getGoogleMapIcons();


        $pdf = Pdf::loadView('alarm_reports/alarm_event_notes_track', compact('alarm', 'icons'))->setPaper('A4', 'potrait');
        return $pdf->download($request->alarm_id . "_event_track_notes.pdf");
    }
    public function alarmEventsNotesPrintPdf(Request $request)
    {



        if (!$request->filled('alarm_id')) return [];
        $alarm =   AlarmEvents::with([
            "device.customer.primary_contact",
            "device.customer.secondary_contact",
            "device.customer.contacts",
            "device.company.user",
            "notes.contact",
            "category",
            "device.customer.buildingtype",
            "zoneData",
            "security.user",
            "pinverifiedby"

        ])->where("id", $request->alarm_id)->first();

        $icons = (new AlarmNotificationController())->getAlarmNotificationIcons();
        $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first();


        $pdf = Pdf::loadView('alarm_reports/alarm_event_notes_track', compact('alarm', 'icons', "company"))->setPaper('A4', 'potrait');
        return $pdf->stream($request->alarm_id . "_event_track_notes.pdf");
    }
    public function alarmEventsNotesSummaryDownload(Request $request)
    {

        if (!$request->filled('alarm_id')) return [];
        $alarm =   AlarmEvents::with([
            "device.customer.primary_contact",
            "device.customer.secondary_contact",
            "device.company.user",
            "notes.contact",
            "category",
            "device.customer.buildingtype",
            "zoneData",
            "security.user",
            "pinverifiedby"

        ])->where("id", $request->alarm_id)->first();

        $icons = (new AlarmNotificationController())->getGoogleMapIcons();


        $pdf = Pdf::loadView('alarm_reports/alarm_event_notes_track_summary', compact('alarm', 'icons'))->setPaper('A4', 'potrait');
        return $pdf->download($request->alarm_id . "_event_track_notes.pdf");
    }
    public function alarmEventsNotesSummaryPrintPdf(Request $request)
    {

        if (!$request->filled('alarm_id')) return [];
        $alarm =   AlarmEvents::with([
            "device.customer.primary_contact",
            "device.customer.secondary_contact",
            "device.customer.contacts",
            "device.company.user",
            "notes.contact",
            "category",
            "device.customer.buildingtype",
            "zoneData",
            "security.user",
            "pinverifiedby"

        ])->where("id", $request->alarm_id)->first();

        $icons = (new AlarmNotificationController())->getAlarmNotificationIcons();
        $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first();


        $pdf = Pdf::loadView('alarm_reports/alarm_event_notes_track_summary', compact('alarm', 'icons', "company"))->setPaper('A4', 'potrait');
        return $pdf->stream($request->alarm_id . "_event_track_notes.pdf");
    }
    public function deviceArmedReportsDownloadExcel(Request $request)
    {

        $reports = $this->pdfArmedProcess($request);

        $file_name =  $request->date_from . ' to ' . $request->date_to . ' Armed Report.xlsx';

        return Excel::download((new DeviceArmedReportExcelMapping($reports)), $file_name);
    }
    public function deviceArmedReportsDownload(Request $request)
    {

        $reports = $this->pdfArmedProcess($request);

        $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first();

        $pdf = Pdf::loadView("alarm_reports/armed_reports", compact('company', 'reports',  'request'))->setPaper('A4', 'potrait');

        return $pdf->download($request->date_from . ' to ' . $request->date_to . ' Armed Report.pdf');
    }
    public function deviceArmedReportsPrintPdf(Request $request)
    {

        $reports = $this->pdfArmedProcess($request);

        $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first();

        $pdf = Pdf::loadView("alarm_reports/armed_reports", compact('company', 'reports',  'request'))->setPaper('A4', 'potrait');

        return $pdf->stream($request->date_from . ' to ' . $request->date_to . ' Armed Report.pdf');
    }
    public function alarmEventsCustomersGroupListIndividualDownloadPdf(Request $request)
    {

        $model =   (new CustomerAlarmEventsController())->filter($request);
        $model->whereHas("device.customer");
        $modelCount = $model->clone();
        $countResults = $modelCount->selectRaw("
                COUNT(CASE WHEN alarm_type = 'SOS'    THEN 1 END) as sosCount,
               COUNT(CASE WHEN alarm_category = 1 AND alarm_type != 'SOS'   THEN 1 END) as criticalCount,
                COUNT(CASE WHEN alarm_type = 'Offline'   THEN 1 END) as technicalCount,
                COUNT(CASE WHEN alarm_type IS NOT NULL  AND alarm_type != 'SOS'  AND alarm_category != 1 THEN 1 END) as eventsCount,
                COUNT(CASE WHEN alarm_type = 'Temperature' THEN 1 END) as temperatureCount,
                COUNT(CASE WHEN alarm_type = 'Water' THEN 1 END) as waterCount,
                COUNT(CASE WHEN alarm_type = 'Medical' THEN 1 END) as medicalCount,
                COUNT(CASE WHEN alarm_type = 'Fire' THEN 1 END) as fireCount
            ")->first();

        $model->orderBy("alarm_start_datetime", "asc");


        if ($request->date_from == '') {
            $model = $model->limit(50);
        }


        $reports = $model->get();
        $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first();

        $customer = Customers::whereId($request->customer_id)->first();

        $pdf = Pdf::loadView('alarm_reports/alarm_events_list_customer_individual',  ['reports' => $reports, 'company' => $company, 'customer' => $customer, "request" => $request, "counts" => $countResults])->setPaper('A4', 'potrait');
        return $pdf->download('report.pdf');
    }
    public function alarmEventsCustomersGroupListIndividualNotesPrintPdf(Request $request)
    {

        $model =   (new CustomerAlarmEventsController())->filter($request);
        $model->whereHas("device.customer");
        $modelCount = $model->clone();


        $countResults = $modelCount->selectRaw("
                COUNT(CASE WHEN alarm_type = 'SOS'    THEN 1 END) as sosCount,
               COUNT(CASE WHEN alarm_category = 1 AND alarm_type != 'SOS'   THEN 1 END) as criticalCount,
                COUNT(CASE WHEN alarm_type = 'Offline'   THEN 1 END) as technicalCount,
                COUNT(CASE WHEN alarm_type IS NOT NULL  AND alarm_type != 'SOS'   AND alarm_category != 1 THEN 1 END) as eventsCount,
                COUNT(CASE WHEN alarm_type = 'Temperature' THEN 1 END) as temperatureCount,
                COUNT(CASE WHEN alarm_type = 'Water' THEN 1 END) as waterCount,
                COUNT(CASE WHEN alarm_type = 'Medical' THEN 1 END) as medicalCount,
                COUNT(CASE WHEN alarm_type = 'Fire' THEN 1 END) as fireCount
            ")->first();

        $model->orderBy("alarm_start_datetime", "asc");


        if ($request->date_from == '') {
            $model = $model->limit(50);
        }


        $reports = $model->get();
        $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first();

        $customer = Customers::whereId($request->customer_id)->first();
        $icons = (new AlarmNotificationController())->getAlarmNotificationIcons();
        $pdf = Pdf::loadView('alarm_reports/alarm_events_list_customer_individual_notes',  ['reports' => $reports, 'company' => $company, 'customer' => $customer, "icons" => $icons, "request" => $request, "counts" => $countResults])->setPaper('A4', 'potrait');

        $fileName = "Alarm Customer Events with Notes.pdf";
        return $pdf->stream($fileName);
    }


    public function alarmEventsCustomersGroupListIndividualNotesDownloadPdf(Request $request)
    {

        $model =   (new CustomerAlarmEventsController())->filter($request);
        $model->whereHas("device.customer");
        $modelCount = $model->clone();


        $countResults = $modelCount->selectRaw("
                COUNT(CASE WHEN alarm_type = 'SOS'    THEN 1 END) as sosCount,
               COUNT(CASE WHEN alarm_category = 1 AND alarm_type != 'SOS'   THEN 1 END) as criticalCount,
                COUNT(CASE WHEN alarm_type = 'Offline'   THEN 1 END) as technicalCount,
                COUNT(CASE WHEN alarm_type IS NOT NULL  AND alarm_type != 'SOS'  AND alarm_category != 1 THEN 1 END) as eventsCount,
                COUNT(CASE WHEN alarm_type = 'Temperature' THEN 1 END) as temperatureCount,
                COUNT(CASE WHEN alarm_type = 'Water' THEN 1 END) as waterCount,
                COUNT(CASE WHEN alarm_type = 'Medical' THEN 1 END) as medicalCount,
                COUNT(CASE WHEN alarm_type = 'Fire' THEN 1 END) as fireCount
            ")->first();

        $model->orderBy("alarm_start_datetime", "asc");


        if ($request->date_from == '') {
            $model = $model->limit(50);
        }


        $reports = $model->get();
        $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first();

        $customer = Customers::whereId($request->customer_id)->first();
        $icons = (new AlarmNotificationController())->getAlarmNotificationIcons();
        $pdf = Pdf::loadView('alarm_reports/alarm_events_list_customer_individual_notes',  ['reports' => $reports, 'company' => $company, 'customer' => $customer, "icons" => $icons, "request" => $request, "counts" => $countResults])->setPaper('A4', 'potrait');

        $fileName = "Alarm Events Customer Notes.pdf";
        return $pdf->download($fileName);
    }
    public function alarmEventsCustomersGroupListIndividualPrintPdf(Request $request)
    {

        $model =   (new CustomerAlarmEventsController())->filter($request);

        $model->whereHas("device.customer");
        $modelCount = $model->clone();


        $countResults = $modelCount->selectRaw("
                COUNT(CASE WHEN alarm_type = 'SOS'    THEN 1 END) as sosCount,
               COUNT(CASE WHEN alarm_category = 1 AND alarm_type != 'SOS'   THEN 1 END) as criticalCount,
                COUNT(CASE WHEN alarm_type = 'Offline'  THEN 1 END) as technicalCount,
                COUNT(CASE WHEN alarm_type IS NOT NULL  AND alarm_type != 'SOS'   AND alarm_category != 1 THEN 1 END) as eventsCount,
                COUNT(CASE WHEN alarm_type = 'Temperature' THEN 1 END) as temperatureCount,
                COUNT(CASE WHEN alarm_type = 'Water' THEN 1 END) as waterCount,
                COUNT(CASE WHEN alarm_type = 'Medical' THEN 1 END) as medicalCount,
                COUNT(CASE WHEN alarm_type = 'Fire' THEN 1 END) as fireCount
            ")->first();

        $model->orderBy("alarm_start_datetime", "asc");


        // if ($request->date_from == '') {
        //     $model = $model->limit(50);
        // }


        $reports = $model->get();
        $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first();

        $customer = Customers::whereId($request->customer_id)->first();

        $pdf = Pdf::loadView('alarm_reports/alarm_events_list_customer_individual',  ['reports' => $reports, 'company' => $company, 'customer' => $customer, "request" => $request, "counts" => $countResults])->setPaper('A4', 'potrait');
        return $pdf->stream('report.pdf');
    }
    public function processTestResults($company_id, $customer_id, $ticket_id)
    {
        $model = DeviceZones::with("device")
            ->where("company_id", $company_id)
            ->whereHas("device", function ($query) use ($customer_id) {
                $query->where("customer_id", $customer_id);
            });

        $sensorList = $model->orderBy("zone_code", "ASC")->get();

        // Fetch all test results in one query to avoid multiple queries
        $reportData = TicketSensorTest::where("ticket_id", $ticket_id)
            ->get()
            ->groupBy(function ($item) {
                return $item->zone_code . '-' . $item->area_code . '-' . $item->device_id;
            });

        // Attach test results to sensors efficiently
        foreach ($sensorList as $sensor) {
            $key = $sensor->zone_code . '-' . $sensor->area_code . '-' . $sensor->device_id;
            $sensor->test_result = isset($reportData[$key]) ? $reportData[$key]->first()->test_status : 0;

            $sensor->test_datetime = isset($reportData[$key]) ? $reportData[$key]->first()->test_datetime : 0;
        }

        return $sensorList;
    }
    public function technicianTestResultsDownloadPdf(Request $request)
    {
        $sensorList = $this->processTestResults($request->company_id, $request->customer_id, $request->ticket_id);




        $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first();

        $fileName = "#" . $request->ticket_id . " - Technician Ticket - Test Sensor Results.pdf";


        return   Pdf::loadview("alarm_reports/technician_ticket_sensor_test_results", ["request" => $request, "reports" => $sensorList, "ticket_id" => $request->ticket_id, "company" => $company])->setpaper("A4", "potrait")->download($fileName);
    }
    public function technicianTestResultsPrintPdf(Request $request)
    {





        $sensorList = $this->processTestResults($request->company_id, $request->customer_id, $request->ticket_id);




        $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first();

        $fileName = "#" . $request->ticket_id . " - Technician Ticket - Test Sensor Results.pdf";


        return   Pdf::loadview("alarm_reports/technician_ticket_sensor_test_results", ["request" => $request, "reports" => $sensorList, "ticket_id" => $request->ticket_id, "company" => $company])->setpaper("A4", "potrait")->stream($fileName);
    }

    public function alarmEventsCustomersGroupPrintPdf(Request $request)
    {

        $report =  (new CustomerAlarmEventsController)->getGroupFilter($request);
        $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first();

        $fileName = "Alarm Events Count - Customers Group.pdf";


        return   Pdf::loadview("alarm_reports/alarm_events_customer_group_count", ["request" => $request, "reports" => $report["data"], "company" => $company])->setpaper("A4", "potrait")->stream($fileName);
    }
    public function alarmEventsCustomersGroupDownloadPdf(Request $request)
    {

        $report =  (new CustomerAlarmEventsController)->getGroupFilter($request);
        $company = Company::whereId($request->company_id)->with('contact:id,company_id,number')->first();

        $fileName = "Alarm Events Count - Customers Group.pdf";
        return   Pdf::loadview("alarm_reports/alarm_events_customer_group_count", ["request" => $request, "reports" => $report["data"], "company" => $company])->setpaper("A4", "potrait")->download($fileName);
    }
    public function alarmEventsCustomersGroupDownloadCSV(Request $request)
    {

        $reports =  (new CustomerAlarmEventsController)->getGroupFilter($request);

        $fileName = "Alarm Events Count - Customers Group.xlsx";

        return Excel::download((new ExportAlarmEventsCustomerGroup($reports["data"])), $fileName);
    }

    function pdfArmedProcess($request)
    {
        $reports1 = (new DeviceArmedLogsController())->reportProcess($request);


        $reports = [];

        foreach ($reports1 as $report) {

            if (empty($report['customers'])) {
                $reports[] = [
                    "date" => $report['date'],

                    "customer_id" => "---",
                    "customer" => "---",
                    "city" => "---",
                    "armed" => "---",
                    "sos" => "---",
                    "armedHours" => "---",
                    "disarmHours" => "---",
                    "events_count" => 0,

                ];
            } else {
                foreach ($report['customers'] as $customer) {

                    $armedTotalHrs = $this->getArmedTotalDuration($customer['armed']);
                    $disarmTotalHrs = $this->getDisarmTotalDuration($customer['armed'], $report['date']);
                    $reports[] = [
                        "date" => $report['date'],
                        "armedHours" => $armedTotalHrs,
                        "disarmHours" => $disarmTotalHrs,

                        ...$customer
                    ];
                }
            }
        }

        return  $reports;
    }
    function getDisarmTotalDuration($array, $date)
    {
        $startOfDay = new DateTime($date);
        $startOfDay->setTime(0, 0, 0); // Set to 12:00 AM
        $endOfDay = clone $startOfDay;
        $endOfDay->setTime(23, 59, 59); // Set to 11:59 PM

        $totalDurationInSeconds = 0;

        foreach ($array as $item) {
            if ($item && isset($item['armed_datetime'], $item['disarm_datetime'])) {
                $armedTime = new DateTime($item['armed_datetime']);
                $disarmedTime = new DateTime($item['disarm_datetime']);

                $durationInSeconds = $disarmedTime->getTimestamp() - $armedTime->getTimestamp();

                if ($durationInSeconds > 0) {
                    $totalDurationInSeconds += $durationInSeconds;
                }
            }
        }

        if (!is_numeric($totalDurationInSeconds)) {
            return "---";
        }

        $totalDurationInSeconds = 24 * 60 * 60 - $totalDurationInSeconds;

        $totalHours = floor($totalDurationInSeconds / 3600);
        $totalMinutes = floor(($totalDurationInSeconds % 3600) / 60);

        $totalHours = str_pad($totalHours, 2, "0", STR_PAD_LEFT);
        $totalMinutes = str_pad($totalMinutes, 2, "0", STR_PAD_LEFT);

        return "$totalHours:$totalMinutes";
    }

    function getArmedTotalDuration($array)
    {
        $totalDurationInSeconds = 0;

        foreach ($array as $item) {
            if ($item && isset($item['armed_datetime'], $item['disarm_datetime'])) {
                $armedTime = new DateTime($item['armed_datetime']);
                $disarmedTime = new DateTime($item['disarm_datetime']);

                $duration = $disarmedTime->getTimestamp() - $armedTime->getTimestamp();
                $totalDurationInSeconds += $duration;
            }
        }

        if (!is_numeric($totalDurationInSeconds)) {
            return "00:00";
        }

        $totalHours = floor($totalDurationInSeconds / 3600);
        $totalMinutes = floor(($totalDurationInSeconds % 3600) / 60);

        $totalHours = str_pad($totalHours, 2, "0", STR_PAD_LEFT);
        $totalMinutes = str_pad($totalMinutes, 2, "0", STR_PAD_LEFT);

        return "$totalHours:$totalMinutes";
    }

    //----------------------------------------------------------------

    public function sample_pdf_pagenumbers()
    {
        $reports =   AttendanceLog::where("UserID", "1001")->get();
        $pdf =  App::make('dompdf.wrapper');

        //$pdf->getDomPDF()->set_option("enable_php", true);//do not delete this line
        $pdf->loadView('alarm_reports/sample_with_page_numbers', compact('reports'))->setPaper('A4', 'potrait');
        return $pdf->stream('invoice.pdf');
    }
}
