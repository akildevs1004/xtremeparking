<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailAlarmForwardMail  extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;
    protected $pdfPath;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    // public function __construct($data)
    // {
    //     $this->data = $data;
    // }
    public function __construct($data, $pdfPath = null)
    {
        $this->data = $data;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject($this->data['subject']);

        $this->attach($this->pdfPath, [
            "as" => "Alarm Event Forward Information" . $this->data['alarm']["id"] . ".pdf",
            "mime" => "application/pdf",
        ]);

        return $this->view('emails.AlarmForwardEmail')->with($this->data);
    }
}
