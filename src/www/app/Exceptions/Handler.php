<?php

namespace App\Exceptions;

use App\Mail\ErrorOccurred;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $levels = [];

    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function report(Throwable $exception)
    {
        Log::error($exception->getMessage(), ['exception' => $exception]);

        // Prevent duplicate email sending
        if (!$this->shouldntReport($exception)) {
            $this->sendErrorEmail($exception);
        }

        parent::report($exception);
    }

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            Log::error('An error occurred: ' . $e->getMessage());
        });

        // if (config('app.debug')) {
        //     ini_set('display_errors', 1);  // Enable PHP error display
        //     error_reporting(E_ALL);        // Report all types of errors
        // }
    }

    protected function sendErrorEmail(Throwable $exception)
    {
        try {
            $errorDetails = [
                'exception_message' => $exception->getMessage(),
                'exception_file' => $exception->getFile(),
                'exception_line' => $exception->getLine(),
                'stack_trace' => $exception->getTraceAsString(),
            ];

            $recipients = ['xtremegurad@gmail.com', 'venuakil2@gmail.com'];

            foreach ($recipients as $email) {
                Mail::to($email)->send(new ErrorOccurred($errorDetails));
            }
        } catch (Throwable $mailException) {
            Log::error("Error sending error email: " . $mailException->getMessage());
        }
    }
}
