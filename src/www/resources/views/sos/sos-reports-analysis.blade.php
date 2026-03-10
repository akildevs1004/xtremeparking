{{-- resources/views/sos/sos-reports-analysis.blade.php --}}
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>SOS Call Report Analysis</title>

    <style>
        /* @page {
            margin: 14mm 14mm 14mm 14mm;
        } */
    </style>
</head>

<body>

    @php
        // Icon paths (local)

        // Safe defaults
        $unitName = $unitName ?? 'Unit';
        $reportPeriod = $reportPeriod ?? '-';
        $generatedOn = $generatedOn ?? '-';
        $totalCalls = $totalCalls ?? 0;
        $avgResponse = $avgResponse ?? '00:00';
        $resolvedPct = $resolvedPct ?? '0%';
        $topLocation = $topLocation ?? '-';
        $topLocationCalls = $topLocationCalls ?? 0;

        // Charts defaults (if you still use any HTML bars)
        $bars = $hourlyBars ?? [15, 10, 5, 8, 25, 45, 65, 85, 55, 40, 30, 20];
        $maxBars = max($bars) ?: 1;

        $trend = $trend ?? ['Mon' => 72, 'Tue' => 68, 'Wed' => 64, 'Thu' => 66, 'Fri' => 60, 'Sat' => 56, 'Sun' => 52];

        $statusResolved = $statusResolved ?? '0%';
        $statusAck = $statusAck ?? '0%';
        $statusPending = $statusPending ?? '0%';

        $sources = $sourceBars ?? [
            ['label' => 'Patient Rooms', 'value' => 0, 'pct' => 0],
            ['label' => 'Disabled Toilets', 'value' => 0, 'pct' => 0],
            ['label' => 'Common Areas', 'value' => 0, 'pct' => 0],
        ];

        $periodFrom = $periodFrom ?? '';
        $periodTo = $periodTo ?? '';

        // ===== IMPORTANT: wkhtmltopdf local images must use file:// + public_path =====
        $chartHourlySos = 'file://' . public_path('storage/reports/charts/hourly_sos.png');
        $chartResponseHourly = 'file://' . public_path('storage/reports/charts/response_hourly_sos.png');
        $chartStatusDonut = 'file://' . public_path('storage/reports/charts/sos_status_donut.png');

        // 4th chart: change filename to your actual chart file
        // Example: room_type_donut.png / room_type_bars.png / top_locations.png etc.
        // $chartRoomType = 'file://' . public_path('storage/reports/charts/room_type_donut.png');

    @endphp

    {{-- PAGE 1 --}}

    @include('sos.sos-report-analysis-page1')

    {{-- PAGE 2 --}}

    @include('sos.sos-report-analysis-page2')

    {{-- PAGE 3 --}}


    @include('sos.sos-reports-logs-color-page3')


    {{-- <div class="note" style="margin-top:16px;">
            <strong>Note:</strong>
            This report includes all SOS calls triggered
            @if ($periodFrom && $periodTo)
                between {{ $periodFrom }} and {{ $periodTo }}.
            @else
                within the selected report period.
            @endif
            Average response time is calculated based on the interval between “Call Time” and “Ack. Time”.
        </div>

        <div class="footer">
            <table>
                <tr>
                    <td>© {{ date('Y') }} INTELLIGENT NURSE CALL SYSTEM </td>
                    <td style="text-align:right;"> </td>
                </tr>
            </table>
        </div> --}}

</body>

</html>
