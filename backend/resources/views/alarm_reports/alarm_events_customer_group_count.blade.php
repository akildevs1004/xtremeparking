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
    $title2='';
    // Check if the request has 'date_from' and 'date_to' parameters
    if($request->date_from && $request->date_to) {
    // Assuming changeDateformat returns an array, so accessing the first element
    $fromDate = changeDateformat($request->date_from)[0];
    $toDate = changeDateformat($request->date_to)[0];
    $title2= "From $fromDate to $toDate";
    }
    @endphp

    @php
    $title1='Alarm Events List Report';

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
        <hr style="color:#ddd;margin-top:10px " />



        @php if(count($reports)>0) { @endphp
        <br />


        <table class="table-border" width="100%" cellspacing="0" cellpadding="5">



            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Type</th>
                    <th>SOS</th>
                    <th>Critical</th>
                    <th>Technical</th>
                    <th>Events</th>
                    <th>Medical</th>
                    <th>Fire</th>
                    <th>Water</th>
                    <th>Temperature</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>

                @php
                $total=0;
                @endphp

                @foreach ($reports as $counter=>$item)
                @php
                $total=0;
                if($item->counts)
                {
                $total +=
                ($item->counts->soscount ?? 0) +
                ($item->counts->criticalcount ?? 0) +
                ($item->counts->technicalcount ?? 0) +
                ($item->counts->eventscount ?? 0) +
                ($item->counts->medicalcount ?? 0) +
                ($item->counts->firecount ?? 0) +
                ($item->counts->watercount ?? 0) +
                ($item->counts->temperaturecount ?? 0);



                }
                @endphp
                <tr>



                    <td>{{ ++$counter  }}</td>
                    <td>{{ $item->building_name ??'---'  }} </td>

                    <td>{{ $item->buildingtype?->name ??'---'  }} </td>
                    <td>{{ $item->counts?->soscount ?? '0' }} </td>
                    <td>{{ $item->counts?->criticalcount ?? '0' }} </td>
                    <td>{{ $item->counts?->technicalcount ?? '0' }} </td>
                    <td>{{ $item->counts?->eventscount ?? '0' }} </td>
                    <td>{{ $item->counts?->medicalcount ?? '0' }} </td>
                    <td>{{ $item->counts?->firecount ?? '0' }} </td>
                    <td>{{ $item->counts?->watercount ?? '0' }} </td>
                    <td>{{ $item->counts?->temperaturecount ?? '0' }} </td>
                    <td style="font-weight:bold">{{ $total }} </td>

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