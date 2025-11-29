<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailContentDefault  extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;
    protected $pdfPath;
    protected $fileName;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $pdfPath = null, $fileName = null)
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
        if ($this->pdfPath) {
            $this->attach($this->pdfPath, [
                "as" => $this->fileName,
                "mime" => "application/pdf",
            ]);
        }

        return $this->view('emails.email')->with(["body" => $this->data['body']]);
    }
}
