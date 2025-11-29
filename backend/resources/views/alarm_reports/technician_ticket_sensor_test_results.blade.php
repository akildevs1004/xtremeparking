<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alarm Customer Events Report</title>
    <style>
        .table-border table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-border tr {
            /* border-top: 1px solid black; */

            border-top: 1px solid #313131;
        }

        .table-border tr:first-child {
            border-top: none !important;
        }

        .table-border tr:first-child {
            border-bottom: 1px solid #313131;
        }

        .table-border th,
        .table-border td {
            padding: 8px;
            text-align: left;
        }

        .table-border-stats tr {

            border-top: 1px solid red !important;

        }

        .table-border-stats td {

            padding: 8px;

        }


        .table-border-header {
            border-collapse: collapse;
            width: 100%;
        }

        .table-border-header tr {
            border-bottom: 1px solid #313131;

        }

        .table-border-header th,
        .table-border-header td {
            padding: 5px;

        }
    </style>
</head>

<body>

    @php
    $title2= '#' . $ticket_id ;

    @endphp

    @php
    $title1='Sensor Test Report';

    @endphp
    <!-- Header -->
    <header>

        @include('alarm_reports.header', [

        'company' => $company,

        'title2'=>$title2
        ])



    </header>

    <!-- Footer -->
    <footer>

        @include('alarm_reports.footer', [

        'company' => $company
        ])

    </footer>
    <main>

        @php if(count($reports)>0) { @endphp
        <br />


        <table class="table-border" width="100%" cellspacing="0" cellpadding="5">



            <thead>
                <tr>
                    <th>#</th>
                    <th>Device Serial Number</th>
                    <th>Zone Number</th>
                    <th>Zone Name</th>
                    <th>Zone Type</th>
                    <th>Sensor Type</th>
                    <th>Area</th>
                    <th>Wired/Wireless</th>

                    <th>Test Datetime</th>
                    <th>Result</th>

                </tr>
            </thead>
            <tbody>

                @php
                $total=0;
                @endphp

                @foreach ($reports as $counter=>$item)
                @php
                $total=0;

                @endphp
                <tr>



                    <td>{{ ++$counter  }}</td>
                    <td>{{ $item->device['serial_number'] ??'---'  }} </td>
                    <td>{{ $item->zone_code ??'---'  }} </td>

                    <td>{{ $item->location ??'---'  }} </td>
                    <td>{{ $item->sensor_name ??'---' }} </td>
                    <td>{{ $item->sensor_type ??'---' }} </td>
                    <td>{{ $item->area_code ??'---' }} </td>
                    <td>{{ $item->wired ??'---' }} </td>

                    <td>{{ changeDateformatTime($item->test_datetime) }} </td>
                    <td>


                        @if ($item->test_result==0)

<span class="material-symbols-outlined green">
    <img src="{{ env('BASE_PUBLIC_URL').'/icons/tested_fail.jpg'}}" width="15">
</span>

@else
<span class="material-symbols-outlined red" >
    <img src="{{ env('BASE_PUBLIC_URL').'/icons/tested_ok.jpg'}}" width="15">
</span>
@endif




                        </td>


                </tr>
                @endforeach
            </tbody>
        </table>

        @php }
        else { @endphp

        <div style="text-align: center;padding-top:10%">
            No Data is Available

        </div>

        @php
        }

        @endphp
    </main>


    <script type="text/php">
        if (isset($pdf)) {
        $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $font = $fontMetrics->getFont("Verdana");
        $size = 6;
        $x = $pdf->get_width() - 50;
        $y = $pdf->get_height() - 30;
        $pdf->page_text($x, $y, $text, $font, $size);
    }
    </script>
    @php

    function changeDateformat($date)

    {
    if($date=='') return ['---','---'];
    $date = new DateTime($date);

    // Format the date to the desired format
    return [$date->format('M j, Y'),$date->format('H:i')];
    }
    function minutesToTime($totalMinutes)
    {
    if($totalMinutes==0) return '00:00';
    if($totalMinutes==null) return '---';
    // Calculate hours and minutes
    $hours = intdiv($totalMinutes, 60); // Integer division to get hours
    $minutes = $totalMinutes % 60; // Remainder to get minutes

    // Format hours and minutes to HH:MM
    return $formattedTime = sprintf('%02d:%02d', $hours, $minutes);

    }
    @endphp

</body>

</html>
