<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alarm Events Report</title>
    <style>
        .table-border1 table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-border1 tr {
            /* border-top: 1px solid black; */

            border-top: 1px solid #313131;
        }

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

        .table-border-top {
            border-collapse: collapse;
            width: 100%;
        }



        .table-border-top th,
        .table-border-top td {
            padding: 5px;

        }
    </style>



</head>

<body>
    <!-- Header -->
    @php
        $title1 = 'Alarm Event Notes Report';

    @endphp
    <header>

        @include('alarm_reports.header', [
            'title1' => $title1,
            'company' => $alarm['device']['company'],
            'title2' => '',
            'alarm_id' => $alarm['id'],
        ])



    </header>

    <!-- Footer -->
    <footer>

        @include('alarm_reports.footer', [
            'company' => $alarm['device']['company'],
        ])

    </footer>
    @php

        $customerLogo = getcwd() . '/no-business_profile.png';
        $companyLogo = getcwd() . '/no-business_profile.png';
        $securityLogo = getcwd() . '/no-profile-image.jpg';
        if (env('APP_ENV') !== 'local') {
            if (
                $alarm['device'] &&
                $alarm['device']['customer'] &&
                $alarm['device']['customer']['profile_picture'] != ''
            ) {
                $customerLogo = $alarm['device']['customer']['profile_picture'];
            }
            if ($alarm['device'] && $alarm['device']['company'] && $alarm['device']['company']['logo'] != '') {
                $companyLogo = $alarm['device']['company']['logo'];
            }
            if ($alarm['security'] && $alarm['security']['picture'] && $alarm['security']['picture'] != '') {
                $securityLogo = $alarm['security']['picture'];
            }
        }

    @endphp

    @php

        function changeDateformat($date)
        {
            if ($date == '') {
                return '---';
            }
            $date = new DateTime($date);

            // Format the date to the desired format
            return $date->format('M j, Y') . ' ' . $date->format('H:i');
        }
        function minutesToTime($totalMinutes)
        {
            if ($totalMinutes == 0) {
                return '00:00';
            }
            if ($totalMinutes == null) {
                return '---';
            }
            // Calculate hours and minutes
            $hours = intdiv($totalMinutes, 60); // Integer division to get hours
            $minutes = $totalMinutes % 60; // Remainder to get minutes

            // Format hours and minutes to HH:MM
            return $formattedTime = sprintf('%02d:%02d', $hours, $minutes);
        }
    @endphp
    <main>


        <table style="width:100%;font-size:12px">



            <tr>
                <td>
                    <table class="table-border-top" style="width:100%">
                        <tr>
                            <td colspan="5" style="background-color:#8f8f8f;height:20px;color:#FFF;font-size:16px">
                                Customer Details</td>
                        </tr>
                        <tr style="border: 1px solid #8f8f8f;border-top:0px">
                            <td rowspan="4" style="width:100px;text-align:center"> <img
                                    style="border-radius: 10%;height: 70px;min-height: 70px;  "
                                    src="{{ $customerLogo }}" /> </td>

                            <td style="font-weight:bold">Customer/Building Name</td>
                            <td>: {{ $alarm['device']['customer']['building_name'] }}</td>

                            <td style="font-weight:bold">Type</td>
                            <td>:{{ $alarm['device']['customer']['buildingtype']['name'] }}</td>


                        </tr>
                        <tr style="border: 1px solid #8f8f8f;">

                            <td style="font-weight:bold">Address</td>
                            <td colspan="3">: <span>{{ $alarm->device->customer->house_number ?? '---' }},
                                    {{ $alarm->device->customer->street_number ?? '---' }}</span>
                                <span>{{ $alarm->device->customer->area ?? '---' }},
                                    {{ $alarm->device->customer->city ?? '---' }} </span>
                                <span>{{ $alarm->device->customer->state ?? '---' }},
                                    {{ $alarm->device->customer->country ?? '---' }} </span>
                            </td>


                        </tr>

                        <tr style="border: 1px solid #8f8f8f;">

                            <td style="font-weight:bold">Contact</td>

                            <td colspan=3>: {{ $alarm->device->customer?->primary_contact?->first_name ?? '---' }}
                                {{ $alarm->device->customer?->primary_contact?->last_name ?? '---' }} </td>


                        </tr>
                        <tr style="border: 1px solid #8f8f8f;">

                            <td style="font-weight:bold">Phone Number</td>

                            <td>: {{ $alarm->device->customer?->primary_contact?->phone1 ?? '---' }}</td>
                            <td style="font-weight:bold">Email</td>

                            <td>: {{ $alarm->device->customer?->primary_contact?->email ?? '---' }}</td>

                        </tr>

                    </table>
                </td>
            </tr>

            <tr>
                <td>
                    <table class="table-border-top" style="width:100%">
                        <tr>
                            <td colspan="5" style="background-color:#8f8f8f;height:20px;color:#FFF;font-size:16px">
                                Event Details</td>
                        </tr>
                        <tr style="border: 1px solid #8f8f8f;border-top:0px">
                            <td rowspan="4" style="width:100px;text-align:center">

                                @if (isset($icons[$alarm->alarm_type]))
                                    <img style=" border-radius: 50%; width: 60px;  max-width: 60px;  "
                                        src="{{ env('BASE_PUBLIC_URL') }}/alarm_icons/{{ $icons[$alarm->alarm_type] }}" />
                                @else
                                    <img style=" border-radius: 50%; width: 60px;  max-width: 60px;  "
                                        src="{{ env('BASE_PUBLIC_URL') }}/alarm_icons//event.png" />
                                @endif
                            </td>

                            <td style="font-weight:bold">Event Id</td>
                            <td style="color:red">#{{ $alarm->id }}</td>

                            <td style="font-weight:bold">Status</td>

                            <td>: @if ($alarm->forwarded == true && $alarm->alarm_status == 1)
                                    Forwarded
                                @elseif($alarm->alarm_status == 1)
                                    Open
                                @else
                                    Closed
                                @endif
                            </td>


                        </tr>
                        <tr style="border: 1px solid #8f8f8f;">

                            <td style="font-weight:bold">Sensor Name</td>
                            <td>: {{ $alarm->zoneData->sensor_type ?? '---' }}</td>

                            <td style="font-weight:bold">Alarm Zone</td>
                            <td>: {{ $alarm->zoneData->sensor_name ?? '---' }}</td>


                        </tr>

                        <tr style="border: 1px solid #8f8f8f;">
                            <td style="font-weight:bold">Alarm Type</td>
                            <td>: {{ $alarm->alarm_type ?? '---' }}</td>

                            <td style="font-weight:bold">Alarm Location</td>
                            <td>: {{ $alarm->zoneData->location ?? '---' }}</td>


                        </tr>
                        <tr style="border: 1px solid #8f8f8f;">



                            <td style="font-weight:bold">Start</td>
                            <td>: {{ changeDateformatTime($alarm->alarm_start_datetime) }}</td>

                            <td style="font-weight:bold">End</td>
                            <td>:



                                {{ changeDateformatTime($alarm->alarm_end_datetime) }}
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>

            <!-- <tr>
                <td style="width:100%">
                    @include('alarm_reports.include_alarm_event_notes_track_customer1', [
                        'alarm' => $alarm,
                    ])

                </td>
            </tr> -->

            <!-- <tr>
                <td colspan="100%">
                    <hr style="color:#ddd;margin-top:0px " />
                </td>
            </tr> -->

            <tr>

                <td>

                    @if (count($alarm->notes) == 0)
                        <div style="width:100%;height:50px;margin:auto;font-size:12px;text-align:center">
                            <div style="margin:auto;padding-top:50px">
                                Operator Notes are not available
                            </div>

                        </div>
                    @endif
                </td>
            </tr>


            @foreach ($alarm->notes as $note)
                <tr>

                    <td style="padding:0px">
                        @if ($note->event_status == 'Forwarded')
                            @include('alarm_reports.include_alarm_event_notes_track_forward1', [
                                'note' => $note,
                            ])
                        @elseif($note->event_status != 'Forwarded')
                            @include('alarm_reports.include_alarm_event_notes_track_operator_notes', [
                                'note' => $note,
                            ])
                        @endif
                    </td>
                </tr>
            @endforeach
            <tr>

                <td colspan="100%" style="padding:0px">






                    @if ($alarm->alarm_end_datetime != '')
                        <!-- Closed Alarm  -->

                        @include('alarm_reports.include_alarm_event_notes_track_closed', [
                            'alarm' => $alarm,
                        ])
                    @endif

                </td>
            </tr>
        </table>
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
</body>

</html>
