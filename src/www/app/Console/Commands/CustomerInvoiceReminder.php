<?php

namespace App\Console\Commands;

use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\Customers\CustomerPaymentsController;
use App\Http\Controllers\WhatsappController;
use App\Mail\EmailContentDefault;
use App\Models\Customers\CustomerPayments;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CustomerInvoiceReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:customer-invoice-reminder-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {


        //monthly invoices remind before 5 days
        $date = date("Y-m-d", strtotime("+5 day", strtotime(date("Y-m-d"))));

        $invoices1 = CustomerPayments::with(["customer.user", "company", "customer.primary_contact", "customer_product_services"])
            ->where("invoice_date", $date)
            ->where("status", "Pending")
            ->whereHas("customer_product_services", function ($q) use ($date) {
                $q->where("payment_type", "Monthly");
            })

            ->get();


        $this->callMails($invoices1);

        //quaterly invoice 10 days before
        $date = date("Y-m-d", strtotime("+10 day", strtotime(date("Y-m-d"))));
        $invoices2 = CustomerPayments::with(["customer.user", "company", "customer.primary_contact", "customer_product_services"])
            ->where("invoice_date", $date)
            ->where("status", "Pending")
            ->whereHas("customer_product_services", function ($q) use ($date) {
                $q->where("payment_type", "Quarter");
            })->get();
        $this->callMails($invoices2);

        //yearly invoice 30 days before
        $date = date("Y-m-d", strtotime("+30 day", strtotime(date("Y-m-d"))));
        $invoices3 = CustomerPayments::with(["customer.user", "company", "customer.primary_contact", "customer_product_services"])
            ->where("invoice_date", $date)
            ->where("status", "Pending")
            ->whereHas("customer_product_services", function ($q) use ($date) {
                $q->where("payment_type", "Yearly");
            })->get();



        $this->callMails($invoices3);
    }


    public function callMails($invoices)
    {



        foreach ($invoices as   $invoice) {

            return (new CustomerPaymentsController())->sendReminderMail($invoice);
        }
    }
}
