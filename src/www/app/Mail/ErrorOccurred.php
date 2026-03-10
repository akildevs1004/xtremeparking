<?php

namespace App\Mail;

use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\WhatsappController;
use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ErrorOccurred extends Mailable
{
    use Queueable, SerializesModels;

    public $errorDetails;

    /**
     * Create a new message instance.
     *
     * @param array $errorDetails
     * @return void
     */
    public function __construct($errorDetails)
    {
        $this->errorDetails = $errorDetails;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {


        $emailData = [
            'subject' =>  env('APP_ENV') . ' - ' . env('APP_NAME') .   ' -   Application Error Report1 - ' . date("Y-m-d H:i:s"),
            'body' =>  $this->errorDetails['exception_message'] . '<br/>' . json_encode($this->errorDetails),
        ];

        //tanjore mail settings
        $emailData["to"] = "venuakil2@gmail.com";
        (new ClientController())->sendExternalMail($emailData);



        //whatsapp
        $company = Company::where("id", 2)->first();
        $body_content =  $emailData["subject"];
        $body_content .=  $emailData["body"];
        $body_content .=
            "Regards,\n" . env("MAIL_FROM_NAME");

        $response = (new WhatsappController())->sendWhatsappNotification(
            $company,
            $body_content,
            "971552205149",
            []
        );


        return $this->subject(env('APP_ENV') . '-' .  env('APP_NAME') .  ' -   Application Error Report2 - ' . date("Y-m-d H:i:s"))
            ->view('emails.error_report'); // Create this view
    }
}
