@php
    use Carbon\Carbon;

    $companyName = $receipt->company->name ?? '—';
    $companyLoc = $receipt->company->location ?? '—';
    $receiptNo = $receipt->id ?? '—';
    $vehicleNumber = $receipt->log_vehicle_number ?? '—';

    $inTime = $receipt->in_time ? Carbon::parse($receipt->in_time)->format('g:i A, M jS Y') : '---';
    $outTime = $receipt->out_time ? Carbon::parse($receipt->out_time)->format('g:i A, M jS Y') : '---';
    $paymentReceivedTime = $receipt->payment_datetime
        ? Carbon::parse($receipt->payment_datetime)->format('g:i A, M jS Y')
        : '—';

    $mins = (int) ($receipt->duration_in_minutes ?? 0);
    $hh = floor($mins / 60);
    $mm = $mins % 60;
    $durationTxt = sprintf('%dh:%02dm', $hh, $mm);

    $hours = is_numeric($receipt->duration_in_hours) ? (float) $receipt->duration_in_hours : null;
    $rate = is_numeric($receipt->duration_per_hour_amount) ? (float) $receipt->duration_per_hour_amount : null;
    $total = is_numeric($receipt->total_amount) ? (float) $receipt->total_amount : 0.0;

    $breakdown = $hours !== null && $rate !== null ? "{$hours}h × " . number_format($rate, 2) . ' AED' : '---';

    $paymentAt = Carbon::parse($receipt->payment_datetime);

    if ($receipt->payment_datetime) {
        // 1) Parse payment time
        $paymentAt = \Carbon\Carbon::parse($receipt->payment_datetime);

        // 2) Buffer minutes (fallback to 20)
        $bufferMin = is_numeric($receipt->parking_exit_buffertime) ? (int) $receipt->parking_exit_buffertime : 20;

        // 3) Compute expiry (Carbon instance)
        $expiryAt = $paymentAt->copy()->addMinutes($bufferMin);

        // 4) Nicely formatted string for display
        $expiryAtFormatted = $expiryAt->format('g:i A, M jS Y');
    } else {
        $expiryAt = null;
        $expiryAtFormatted = null;
    }
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Parking Receipt</title>
    <style>
        /* DomPDF-friendly CSS (no flexbox, no box-shadow) */
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            background: #fff;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .wrap {
            max-width: 350px;
            /* keep it compact; change to 400px if you like */
            margin: 20px auto;
            padding: 12px;
            border: 1px solid #000;
            border-radius: 8px;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
        }

        .title {
            font-size: 14px;
            color: #555;
        }

        .company {
            font-weight: bold;
            font-size: 16px;
            margin: 6px 0;
        }

        .date {
            font-size: 12px;
            color: #333;
        }

        .row {
            width: 100%;
            background: #f2f2f2;
            border-radius: 6px;
            padding: 0;
            /* table will handle inner spacing */
            margin: 0 0 8px 0;
            border-collapse: collapse;
            /* safer in dompdf */
        }

        .row td {
            padding: 8px 10px;
            vertical-align: middle;
        }

        .label {
            font-size: 13px;
        }

        .value {
            font-weight: bold;
            text-align: right;
        }

        .status {
            text-align: center;
            background: #000;
            /* black for B/W printers; dompdf doesn’t do green well */
            color: #fff;
            font-weight: bold;
            border-radius: 6px;
            padding: 8px;
            margin-top: 12px;
        }

        /* Optional: make emoji smaller if you keep them */
        .icon {
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="header">
            <div class="title">Parking Receipt #{{ $receiptNo }}</div>
            <div class="company">{{ strtoupper($companyName) }}</div>


            <div class="date">{{ $paymentReceivedTime }}</div>
            @if (!empty($expiryAtFormatted))
                <div class="date">Valid till: {{ $expiryAtFormatted }}</div>
            @endif
        </div>

        <table class="row">
            <tr>
                <td class="label"> Vehicle number</td>
                <td class="value">{{ $vehicleNumber }}</td>
            </tr>
        </table>

        <table class="row">
            <tr>
                <td class="label"> Location</td>
                <td class="value">{{ $companyLoc }}</td>
            </tr>
        </table>

        <table class="row">
            <tr>
                <td class="label"> Entry time</td>
                <td class="value">{{ $inTime }}</td>
            </tr>
        </table>

        <table class="row">
            <tr>
                <td class="label"> Exit time</td>
                <td class="value">{{ $outTime }}</td>
            </tr>
        </table>

        <table class="row">
            <tr>
                <td class="label"> Total duration</td>
                <td class="value">{{ $durationTxt }}</td>
            </tr>
        </table>

        <table class="row">
            <tr>
                <td class="label"> Charge breakdown</td>
                <td class="value">{{ $breakdown }}</td>
            </tr>
        </table>

        <table class="row">
            <tr>
                <td class="label"> Amount</td>
                <td class="value">{{ number_format($total, 2) }} AED</td>
            </tr>
        </table>

        <div class="status">Online Payment - Completed</div>
    </div>
</body>

</html>
