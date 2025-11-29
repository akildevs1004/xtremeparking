<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Report</title>
</head>

<body>
    <h1>Error Report - {{ env('APP_NAME') }} - {{ env('APP_ENV') }}</h1>
    <p><strong>Exception Message:</strong> {{ $errorDetails['exception_message'] }}</p>
    <p><strong>File:</strong> {{ $errorDetails['exception_file'] }}</p>
    <p><strong>Line:</strong> {{ $errorDetails['exception_line'] }}</p>
    <p><strong>Stack Trace:</strong></p>
    <pre>{{ $errorDetails['stack_trace'] }}</pre>
</body>

</html>
