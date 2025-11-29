<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alarm Events Report</title>
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
        <table class="table-border-header" style="width:100%">
            <tr>
                <td style="color:#FF0000">SOS</td>
                <td>{{ $counts->soscount}}</td>

                <td style="color:#df3079">Critical</td>
                <td>


                    {{ $counts->criticalcount}}



                </td>
                <td style="color:#007BFF">Technical</td>
                <td> {{ $counts->technicalcount}}
                </td>
                <td style="color:black">Event</td>
                <td> {{ $counts->eventscount}}
                </td>
            </tr>
            <tr>



                <td style="color:#28A745">Medical</td>
                <td> {{ $counts->medicalcount}}
                </td>

                <td style="color:#FF8C00">Fire</td>
                <td> {{ $counts->firecount}}
                </td>
                <td style="color:#20C997">Water</td>
                <td> {{ $counts->watercount}}
                </td>
                <td style="color:#DC3545">Temperature</td>
                <td> {{ $counts->temperaturecount}}
                </td>
            </tr>


        </table>


        @php if(count($reports)>0) { @endphp
        <br />


        <table class="table-border" width="100%" cellspacing="0" cellpadding="5">



            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Property</th>
                    <th>Address</th>
                    <th>City</th>
                    <th>Type</th>
                    <th>Event Time</th>
                    <th>Closed Time</th>
                    <th>Priority</th>
                    <th>Duration <br /> (HH:MM)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reports as $item)

                @php
                $fontcolor='';
                if($item->alarm_end_datetime==null)
                {
                $fontcolor='color:red';
                @endphp
                <tr style="color:red">
                    @php
                    } else {
                    @endphp
                <tr>
                    @php
                    }
                    @endphp



                    <td>{{ $item->id }}</td>
                    <td>

                        {{$item->device->customer?->building_name??'---'}}
                        <div style="font-size:8px">{{ $item->device->customer?->primary_contact?->first_name ?? '---' }}
                            {{ $item->device->customer?->primary_contact?->last_name ?? '---' }}
                        </div>
                    </td>

                    <td>{{ $item->device->customer?->buildingtype?->name ??'---'  }} </td>
                    <td>{{ $item->device->customer?->area ?? '---' }} </td>
                    <td>{{ $item->device->customer?->city ?? '---' }} </td>
                    <td>{{ $item->alarm_type  }} </td>
                    <td>{{ changeDateformat($item->alarm_start_datetime)[1] }} <br /> {{ changeDateformat($item->alarm_start_datetime)[0] }} </td>
                    <td>{{ changeDateformat($item->alarm_end_datetime)[1] }} <br /> {{ changeDateformat($item->alarm_end_datetime)[0] }} </td>
                    <td>{{ $item->category->name }} </td>
                    <td style="text-align:left">
                        @php
                        if($item->alarm_end_datetime==null)
                        echo '---';
                        else echo minutesToTime($item->response_minutes)
                        @endphp

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