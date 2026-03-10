<?php

namespace App\Mail;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DbBackupMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()

    {
        try {
            $email = $this->view('emails.dbBackup')
                ->with([
                    'body' => $this->data['body'],
                    'date' => $this->data['date'],
                ]);

            if (!empty($this->data['file']) && isset($this->data['file'])) {
                $email->attach($this->data['file']);
            }
        } catch (Exception $e) {
        }


        return $email;
    }
}
