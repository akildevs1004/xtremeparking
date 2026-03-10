<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\WhatsappController;
use App\Mail\EmailContentDefault;
use App\Models\Company;
use App\Models\CustomerProductServices;
use App\Models\Customers\CustomerPayments;
use App\Models\Customers\Customers;
use App\Models\Deivices\DeviceZones;
use App\Models\Device;
use App\Models\SalesQuotations;
use App\Models\TaxSlabs;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CustomerPaymentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $model = CustomerPayments::with(["customer_product_services", "customer", "member"])->where('company_id', $request->company_id)
            ->when(
                $request->filled("customer_id"),
                fn($q) => $q->where("customer_id", $request->customer_id)
            )->when(
                $request->filled("filter_status"),
                fn($q) => $q->where("status", $request->filter_status)
            )->when(
                $request->filled("filter_mode_of_payment"),
                fn($q) => $q->where("mode_of_payment", $request->filter_mode_of_payment)
            )->when(
                $request->filled("filter_customer_id"),
                fn($q) => $q->where("customer_id", $request->filter_customer_id)
            )->when(
                $request->filled("common_search"),
                fn($q) => $q->where("invoice_number", "ILIKE", "%" . $request->common_search . "%")
            )->when(
                $request->filled("filter_member_id"),
                fn($q) => $q->where("member_id", $request->filter_member_id)
            );




        if ($request->filled("date_from"))
            $model->whereBetween(
                'invoice_date',
                [$request->date_from . ' 00:00:00', $request->date_to . ' 23:59:59']
            );


        $model->when($request->filled('common_search'), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->Where('invoice_number', 'ILIKE', "%$request->common_search%");
                $q->orWhere('amount', 'ILIKE', "%$request->common_search%");
                $q->orWhere('received_date', 'ILIKE', "%$request->common_search%");
                $q->orWhere('mode_of_payment', 'ILIKE', "%$request->common_search%");
                $q->orWhere('status', 'ILIKE', "%$request->common_search%");
            });
        });

        $model->orderBy("created_at", "DESC");
        return $model->orderByDesc('id')->paginate($request->perPage ?? 10);;
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

        $rules = [
            'company_id' => 'required|integer',
            'customer_id' => 'nullable',
            'invoice_number' => 'required',
            'amount' => 'required',
            'status' => 'required',
            'payment_id' => 'nullable', // Fixed typo from 'paymant_id'
            'received_date' => 'nullable',
            'mode_of_payment' => 'nullable',
            'invoice_date' => 'nullable',
            'cancel_notes' => 'nullable',





        ];

        // If received_date is present, make it required instead of nullable
        if ($request->status == 'Received') {
            $rules['received_date'] = 'required';
            $rules['mode_of_payment'] = 'required';
        }

        $request->validate($rules);
        try {


            $data = $request->all();

            unset($data['login_user_id']);
            unset($data['login_user_type']);


            unset($data['editId']);
            $data["updated_datetime"] = date("Y-m-d H:i:s");



            if ($request->filled("editId")) {

                unset($data['customer_id']);

                $record = CustomerPayments::where("id", $request->editId)->update($data);

                return $this->response('Payment Details are Updated.', $record, true);
            } else {
                $data["created_datetime"] = date("Y-m-d H:i:s");
                $record = CustomerPayments::create($data);
            }

            if ($record) {
                return $this->response('Payment Details are Created.', $record, true);
            } else {
                return $this->response('Payment Details Not Created', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customers\CustomerPayments  $customerPayments
     * @return \Illuminate\Http\Response
     */
    public function show(CustomerPayments $customerPayments)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Customers\CustomerPayments  $customerPayments
     * @return \Illuminate\Http\Response
     */
    public function edit(CustomerPayments $customerPayments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customers\CustomerPayments  $customerPayments
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CustomerPayments $customerPayments)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customers\CustomerPayments  $customerPayments
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $modelEvent = CustomerPayments::where("id", $request->id);

        if ($modelEvent)
            $modelEvent->delete();

        return $this->response('Payment Deleted successfully', null, true);
    }
    public function GetCustomerSensorsPaymentPackage(Request $request)
    {


        // return  $request->validate(
        //     [
        //         'company_id' => 'required|integer',
        //         'customer_id' => 'required|integer',
        //     ]
        // );

        $customerProductService = CustomerProductServices::with("device_product_service")
            ->where("customer_id", $request->customer_id)

            ->orderBy("created_datetime", "DESC")->first();

        if ($customerProductService)
            $customerProductService =   $customerProductService->toArray();
        else return null;


        $customerTotalSensors =   DeviceZones::whereHas('device', function ($query) use ($request) {
            $query->where('customer_id', $request->customer_id);
        })->count();;

        $customerProductService["customerTotalSensors"] = $customerTotalSensors;

        return  $customerProductService;
    }

    public function CustomerProductInvoiceSubmition(Request $request)
    {


        $request->validate(
            [
                'company_id' => 'required|integer',
                'customer_id' => 'required|integer',
                'device_service_id' => 'required|integer',
                'end_date' => 'required',
                'start_date' => 'required',
                'payment_type' => 'required',
                'product_discount_price' => 'required|numeric',
                'product_final_price' => 'required|numeric',
                'product_price' => 'required|numeric',


            ]
        );
        $totalInvoiceCount = $request->total_invoice_count;
        $data = [
            "company_id" => $request->company_id,
            "customer_id" => $request->customer_id,
            "device_product_service_id" => $request->device_service_id,
            "payment_type" => $request->payment_type,
            "discount" =>  $request->product_discount_price,
            "amount" =>  $request->product_final_price,
            "created_datetime" =>  date("Y-m-d H:i:s"),
            "start_date" =>  $request->start_date,
            "end_date" => $request->end_date,
            "invoices_count" => $totalInvoiceCount,

        ];
        // Calculate the bill amount before discount
        $billAmountBeforeDiscount = $request->product_final_price + $request->discount;
        $taxSlab = TaxSlabs::where('company_id', $request->company_id)
            ->where('start_price', '<=', $request->product_final_price)
            ->where('end_price', '>=', $request->product_final_price)
            ->orderByDesc('start_price')
            ->first();

        $taxPercentage = 0;
        $taxAmount = 0;

        if ($taxSlab) {
            $taxPercentage = $taxSlab->tax;
            $taxAmount = $request->product_final_price * $taxPercentage / 100;
        }


        $data = [
            ...$data,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $taxAmount,
            'bill_amount_before_discount' => $billAmountBeforeDiscount,
        ];



        $record = CustomerProductServices::create($data);


        $maxId = CustomerPayments::where("company_id", $request->company_id)
            ->orderBy("invoice_count", "desc")
            ->value("invoice_count") ?? 0; // Default to 0 if no records exist


        $daysToAdd = ($request->payment_type == 'Yearly') ? 364 : (($request->payment_type == 'Quarter') ? 89 : 29);

        $date = Carbon::parse($request->start_date);

        $invoices = [];
        $invoice_id = null;
        for ($i = 1; $i <= $totalInvoiceCount; $i++) {

            $invoiceFormat = sprintf("INV%d%06d", $request->company_id, $maxId + $i);
            $invoice_date = $date->copy()->addDays($daysToAdd * ($i - 1))->format('Y-m-d');
            $invoices  = [
                "company_id" => $request->company_id,
                "customer_id" => $request->customer_id,
                "invoice_number" => $invoiceFormat,
                "amount" => $request->product_final_price,
                'tax_percentage' => $taxPercentage,
                'tax_amount' => $taxAmount,


                "status" => "Pending",
                "invoice_date" => $invoice_date,
                "invoice_count" => $maxId + $i,
                "created_at" =>  date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s"),

                "created_datetime" =>  date("Y-m-d H:i:s"),
                "updated_datetime" => date("Y-m-d H:i:s"),
                "customer_product_service_id" => $record->id,

            ];

            $insertedId =  CustomerPayments::create($invoices);
            if ($i == 1)  $invoice_id = $insertedId->id;
        }


        //updae quotationtable invoiceId
        if ($request->quotation_id && $request->quotation_id != 'null' && $invoice_id)
            SalesQuotations::where("id", $request->quotation_id)->update(["invoice_id" => $invoice_id]);


        ///////CustomerPayments::insert($invoices);
        Customers::where("id", $request->customer_id)->where("start_date", null)->update(["start_date" => $request->start_date]);
        Customers::where("id", $request->customer_id)->update(["end_date" => $request->end_date, "customer_product_service_id" => $record->id]);

        $this->sendMail($invoice_id, $request->company_id);


        return $this->response("Invoices created successfully.", null, true);
    }

    public function sendMail($invoice_id, $company_id)
    {
        try {
            $invoice =   CustomerPayments::with(["company", "customer.primary_contact", "customer.user", "customer_product_services.device_product_service"])->where("id", $invoice_id)->first();
            $company =  $invoice->company;
            $customer =  $invoice->customer;


            $subject = "Customer Invoice Is generated For " . $customer->building_name . " -" . $invoice->invoice_number;

            $body_content = ' <div class="email-body">
            <p>Hello <strong>' .  $customer->building_name . '</strong>,</p>
            <p>Attached Invoice for your reference</p>
<br/><br/>
            <p>Regards, <br/>
             ' . env("MAIL_FROM_NAME") . '</p>
        </div>';



            $pdf = Pdf::loadView('alarm_reports/customer_invoice', compact('invoice',  "company",  "customer"))->setPaper('A4', 'potrait');
            $fileName = $invoice->invoice_number . "pdf";
            $folderPath = public_path('temp/');
            if (!is_dir($folderPath)) {
                mkdir($folderPath);
            }

            $pdfPath = public_path('temp/' . $fileName);
            $pdf->save($pdfPath);


            $data = [
                'subject' => $subject,
                'body' => $body_content,
                'to' => $customer->user->email,
                'file_path' => $pdfPath,
                'file_name' => $fileName,

            ];

            (new ClientController())->sendExternalMail($data);

            Mail::to($customer->user->email)->queue(new EmailContentDefault($data, $pdfPath, $fileName));


            CustomerPayments::where("id", $invoice_id)->update(["mail_sent_datetime" => date("Y-m-d H:i:s")]);

            //whatsapp


            $body_content = "Hello *" . $company->name . "*\n\n" .
                $subject . "\n\n" .
                "Regards,\n" . env("MAIL_FROM_NAME");

            $response = (new WhatsappController())->sendWhatsappNotification(
                $company,
                $body_content,
                $company->contact_number,
                []
            );


            //   unlink($pdfPath);
        } catch (Exception $e) {
        }
    }

    public function sendReminderMail($invoice)
    {



        $company =  $invoice->company;
        $customer =  $invoice->customer;


        $subject = "Reminder Customer Invoice For " . $customer->building_name . " -" . $invoice->invoice_number;



        $body_content = "<p>Dear  <strong>{$customer->building_name}</strong>,</p>

        I hope this email finds you well. This is a   reminder that invoice {$invoice->invoice_number}, dated {$invoice->invoice_date}, for {$invoice->amount}, is due on {$invoice->invoice_date}.<br/><br/>

    We appreciate your prompt attention to this matter. For your convenience, you can find the invoice details attached. If payment has already been made, please ignore this message.<br/><br/>

    If you have any questions or need any assistance, feel free to reach out to us .<br/><br/>

    Thank you for your continued business.<br/><br/><br/>
     Regards,<br/>" . $company->name;





        $pdf = Pdf::loadView('alarm_reports/customer_invoice', compact('invoice',  "company",  "customer"))->setPaper('A4', 'potrait');
        $fileName = $invoice->invoice_number  . "."  . "pdf";
        $folderPath = public_path('temp/');
        if (!is_dir($folderPath)) {
            mkdir($folderPath);
        }

        $pdfPath = public_path('temp\\' . $fileName);
        $pdf->save($pdfPath);


        $data = [
            'subject' => $subject,
            'body' => $body_content,
            'to' => $customer->user->email,
            'file_path' => $pdfPath,
            'file_name' => $fileName,

        ];

        (new ClientController())->sendExternalMail($data);

        Mail::to($customer->user->email)->queue(new EmailContentDefault($data, $pdfPath, $fileName));
        CustomerPayments::where("id", $invoice->id)->update(["mail_sent_datetime" => date("Y-m-d H:i:s")]);


        // //whatsapp

        // $body_content = "Hello *" . $company->name . "*\n\n" .
        //     $subject . "\n\n" .
        //     "Regards,\n" . env("MAIL_FROM_NAME");

        // $response = (new WhatsappController())->sendWhatsappNotification(
        //     $company,
        //     $body_content,

        //     $company->contact_number,
        //     []
        // );


        //unlink($pdfPath);


        return;
    }

    public function CustomerInvoiceReminderMail(Request $request)
    {

        $invoice = CustomerPayments::with(["customer", "company"])->where("id", $request->invoice_id)->first();
        $this->sendReminderMail($invoice);

        return $this->response("Mail sent successfully", null, true);
    }
}
