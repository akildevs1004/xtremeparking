<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\ClientController;
use App\Mail\EmailContentDefault;
use App\Models\Company;
use App\Models\SalesQuotations;
use App\Models\TaxSlabs;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SalesQuotationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $model = SalesQuotations::with(["fallowups.user", "ProductService", "BuildingType", "invoice"])->where("company_id", $request->company_id);



        $model->when($request->filled("date_from"), function ($q) use ($request) {

            $q->where("created_datetime", ">=", $request->date_from . ' 00:00:00');
            $q->where("created_datetime", "<=", $request->date_to . ' 23:59:59');
        });


        $model->when($request->filled("common_search") && $request->common_search != '', function ($q) use ($request) {

            $q->where(function ($q) use ($request) {
                $q->Orwhere('first_name', 'ILIKE', '%' . $request->common_search . '%');
                $q->Orwhere('last_name', 'ILIKE', '%' . $request->common_search . '%');
                $q->Orwhere('contact_number', 'ILIKE', '%' . $request->common_search . '%');
                $q->Orwhere('whatsapp_number', 'ILIKE', '%' . $request->common_search . '%');
                $q->Orwhere('email', 'ILIKE', '%' . $request->common_search . '%');
                $q->Orwhere('address', 'ILIKE', '%' . $request->common_search . '%');
                $q->Orwhere('device_type', 'ILIKE', '%' . $request->common_search . '%');
                $q->Orwhere('quotation_id', 'ILIKE', '%' . $request->common_search . '%');
                $q->Orwhere('payment_type', 'ILIKE', '%' . $request->common_search . '%');
                $q->Orwhere('amount', 'ILIKE', '%' . $request->common_search . '%');
                $q->Orwhere('fallowup_status', 'ILIKE', '%' . $request->common_search . '%');

                $q->Orwhere('inquiry_id', 'ILIKE', '%' . $request->common_search . '%');
                $q->Orwhere('customer_id', 'ILIKE', '%' . $request->common_search . '%');


                $count = (int)$request->common_search;
                $q->Orwhere('sensor_count',   $count);
                $q->Orwhere('building_name', 'ILIKE', '%' . $request->common_search . '%');

                $q->orWhereHas('BuildingType', function ($query) use ($request) {
                    $query->where('name', 'ILIKE', '%' . $request->common_search . '%');
                });
                $q->orWhereHas('ProductService', function ($query) use ($request) {
                    $query->where('name', 'ILIKE', '%' . $request->common_search . '%');
                });
            });
        });

        $model->orderBy("created_datetime", "DESC");

        return $model->paginate($request->per_page);
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
        $validate = [
            "company_id" => "required",
            "first_name" => "required",
            "last_name" => "required",
            "contact_number" => "required",
            "whatsapp_number" => "nullable",
            "email" => "required",
            "address" => "required",
            "building_name" => "nullable",
            "building_type_id" => "nullable",
            "device_type" => "required",
            "sensor_count" => "required",
            "device_product_service_id" => "required",
            "payment_type" => "required",
            "discount" => "nullable",
            "amount" => "required",
            "total_years" => "required",
            "inquiry_id" => "nullable",


        ];
        $data =  $request->validate($validate);


        // Calculate the bill amount before discount
        $billAmountBeforeDiscount = $request->amount + $request->discount;


        $taxSlab = TaxSlabs::where('company_id', $request->company_id)
            ->where('start_price', '<=', $request->amount)
            ->where('end_price', '>=', $request->amount)
            ->orderByDesc('start_price')
            ->first();


        $taxPercentage = 0;
        $taxAmount = 0;

        if ($taxSlab) {
            $taxPercentage = $taxSlab->tax;
            $taxAmount = $request->amount * $taxPercentage / 100;
        }


        $data = [
            ...$data,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $taxAmount,
            'bill_amount_before_discount' => $billAmountBeforeDiscount,
        ];





        $quotation_id = null;
        $subject = "New Quotation Created for " . $request->first_name . ' ' . $request->last_name;

        if ($request->editId) {
            $subject = "Updated Quotation   for " . $request->first_name . ' ' . $request->last_name;
            $data["updated_datetime"] = date("Y-m-d H:i:s");
            SalesQuotations::where("id", $request->editId)->update($data);

            $quotation_id = $request->editId;
        } else {
            $maxId = SalesQuotations::where("company_id", $request->company_id)
                ->orderBy("quotation_count", "desc")
                ->value("quotation_count") ?? 0; // Default to 0 if no records exist

            $quotationFormat = sprintf("QTN%d%06d", $request->company_id, $maxId + 1);
            $data["created_datetime"] = date("Y-m-d H:i:s");

            $data["quotation_id"] = $quotationFormat;
            $data["quotation_count"] = $maxId + 1;
            $record = SalesQuotations::create($data);
            $quotation_id = $record->id;

            $subject = $quotationFormat . " - " . $subject;
        }

        //mail
        $this->sendMail($record, $quotation_id, $subject);


        //whatsapp

        $company = Company::where("id", $request->company_id)->first();
        $body_content = 'Hello *' . $request->first_name . ' ' . $request->last_name . '*\n\n
         ' . $subject . '\n\n

        Regards,
         ' . env("MAIL_FROM_NAME") . ' ';
        $response = (new WhatsappController())->sendWhatsappNotification($company, $body_content, $company->contact_number, []);


        if ($request->editId)
            return $this->response("Quotation Updated Successfully", null, true);

        else return $this->response("Quotation Created Successfully", null, true);
    }

    public function sendMail($quotation_id, $subject)
    {
        try {


            $invoice =   SalesQuotations::with(["company",   "ProductService"])->where("id",  $quotation_id)->first();
            $company =  $invoice->company;
            $customer =  $invoice;
            $quotation = $invoice;

            if ($subject == '')
                $subject =  "Created  Quotation   for " . $quotation->first_name . ' ' . $quotation->last_name;

            $body_content = ' <div class="email-body">
            <p>Hello <strong>' . $quotation->first_name . ' ' . $quotation->last_name . '</strong>,</p>
            <p>Attached Quotation for your reference</p>
<br/><br/>
            <p>Regards, <br/>
             ' . env("MAIL_FROM_NAME") . '</p>
        </div>';



            $pdf = Pdf::loadView('alarm_reports/sales_quotation', compact('invoice',  "company",  "customer"))->setPaper('A4', 'potrait');
            $fileName = $quotation_id . '.' . "pdf";
            $folderPath = public_path('temp/');
            if (!is_dir($folderPath)) {
                mkdir($folderPath);
            }

            $pdfPath = public_path('temp/' . $fileName);
            $pdf->save($pdfPath);


            $data = [
                'subject' => $subject,
                'body' => $body_content,
                'to' => $quotation->email,
                'file_path' => $pdfPath,
                'file_name' => $fileName,

            ];

            (new ClientController())->sendExternalMail($data);

            Mail::to($quotation->email)->queue(new EmailContentDefault($data, $pdfPath, $fileName));

            // //whatsapp

            // if ($quotation->whatsapp_number != '') {

            //     $body_content = "Hello *" . $quotation->first_name . ' ' . $quotation->last_name . "*\n\n" .
            //         $subject . "\n\n" .
            //         "Regards,\n" . env("MAIL_FROM_NAME");

            //     $response = (new WhatsappController())->sendWhatsappNotification(
            //         $company,
            //         $body_content,
            //         $quotation->whatsapp_number,
            //         []
            //     );
            // }

            //  unlink($pdfPath);
        } catch (Exception $e) {
        }
    }

    public function CustomerQuotationReminderMail(Request $request)
    {

        $this->sendMail($request->quotation_id, '');
        return $this->response("Mail sent successfully", null, true);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SalesQuotations  $salesQuotations
     * @return \Illuminate\Http\Response
     */
    public function show(SalesQuotations $salesQuotations)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SalesQuotations  $salesQuotations
     * @return \Illuminate\Http\Response
     */
    public function edit(SalesQuotations $salesQuotations)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SalesQuotations  $salesQuotations
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SalesQuotations $salesQuotations)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SalesQuotations  $salesQuotations
     * @return \Illuminate\Http\Response
     */
    public function destroy(SalesQuotations $salesQuotations)
    {
        //
    }
}
