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

        .table-border-all {
            border-collapse: collapse;
            width: 100%;
        }

        .table-border-all th,
        .table-border-all td {
            border: 1px solid #8f8f8f;

        }
    </style>



</head>

<body>
    <!-- Header -->
    @php
        $title1 = 'Alert !';

    @endphp
    <header>

        @include('alarm_reports.header', [
            'title1' => $title1,
            'company' => $alarm['device']['company'],
            'title2' => 'Alarm Urgest',
            'qrcode' => 'qrcode',
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
        <br />

        <table style="width:100%;font-size:12px">

            <tr>
                <td>
                    <table class="table-border-top" style="width:100%">
                        <tr>
                            <td colspan="4" style="background-color:#8f8f8f;height:20px;color:#FFF;font-size:16px">
                                Event Details</td>
                        </tr>
                        <tr style="border: 1px solid #8f8f8f;border-top:0px">


                            <td style="font-weight:bold;width:100px">Event Id</td>
                            <td style="color:red;border-left: 1px solid #8f8f8f;">#{{ $alarm->id }}</td>

                            <td style="font-weight:bold; border-left: 1px solid #8f8f8f;">Status</td>

                            <td style="border-left: 1px solid #8f8f8f;">
                                @if ($alarm->forwarded == true && $alarm->alarm_status == 1)
                                    Forwarded
                                @elseif($alarm->alarm_status == 1)
                                    Open
                                @else
                                    Closed
                                @endif
                            </td>

                            {{-- <td style="font-weight:bold">Sensor Name</td>
                            <td>: {{ $alarm->zoneData->sensor_type ?? '---' }}</td> --}}
                        </tr>
                        <tr style="border: 1px solid #8f8f8f;">

                            <td style="font-weight:bold">Priority</td>
                            <td style="border-left: 1px solid #8f8f8f;">
                                @if ($alarm->alarm_category == 1)
                                    Critical
                                @elseif ($alarm->alarm_category == 2)
                                    Medium
                                @else
                                    Low
                                @endif
                            </td>



                            <td style="font-weight:bold;border-left: 1px solid #8f8f8f;">Alarm Zone</td>
                            <td style="border-left: 1px solid #8f8f8f;"> {{ $alarm->zoneData->sensor_name ?? '---' }}
                            </td>


                        </tr>

                        <tr style="border: 1px solid #8f8f8f;">
                            <td style="font-weight:bold">Event Type</td>
                            <td style="border-left: 1px solid #8f8f8f;"> {{ $alarm->alarm_type ?? '---' }}</td>

                            <td style="font-weight:bold;border-left: 1px solid #8f8f8f;">Alarm Location</td>
                            <td style="border-left: 1px solid #8f8f8f;"> {{ $alarm->zoneData->location ?? '---' }}</td>


                        </tr>
                        <tr style="border: 1px solid #8f8f8f;">



                            <td style="font-weight:bold">Event Time</td>
                            <td style="border-left: 1px solid #8f8f8f;">
                                {{ changeDateformatTime($alarm->alarm_start_datetime) }}</td>

                            <td style="font-weight:bold;border-left: 1px solid #8f8f8f;">Event End Time</td>
                            <td style="border-left: 1px solid #8f8f8f;">



                                {{ changeDateformatTime($alarm->alarm_end_datetime) }}
                            </td>



                        </tr>

                    </table>
                </td>
            </tr>

            <tr>
                <td style="padding-top:10px;">
                    <table class="table-border-top" style="width:100%">
                        <tr>
                            <td colspan="3" style="background-color:#8f8f8f;height:20px;color:#FFF;font-size:16px">
                                Event Location</td>
                        </tr>



                        <tr style="border: 1px solid #8f8f8f;border-top:0px">


                            <td style="font-weight:bold;width:100px">Customer Name</td>
                            <td style="border-left: 1px solid #8f8f8f;">
                                {{ $alarm['device']['customer']['building_name'] }}</td>


                            <td rowspan="5" style="border:1px solid #8f8f8f; width:100px;text-align:center">
                                <img style="border-radius:5px;height: 100px;min-height: 100px;  "
                                    src="{{ $customerLogo }}" />
                            </td>
                        </tr>
                        <tr style="border: 1px solid #8f8f8f;border-top:0px">


                            <td style="font-weight:bold">Business Type
                            </td>
                            <td style="border-left: 1px solid #8f8f8f;">
                                {{ $alarm['device']['customer']['buildingtype']['name'] }}</td>


                        </tr>
                        <tr style="border: 1px solid #8f8f8f;">

                            <td style="font-weight:bold">Address</td>
                            <td style="border-left: 1px solid #8f8f8f;">
                                <span>{{ $alarm->device->customer->house_number ?? '---' }},
                                    {{ $alarm->device->customer->street_number ?? '---' }}</span>
                                <span>{{ $alarm->device->customer->area ?? '---' }},
                                    {{ $alarm->device->customer->city ?? '---' }} </span>
                                <span>{{ $alarm->device->customer->state ?? '---' }},
                                    {{ $alarm->device->customer->country ?? '---' }} </span>
                            </td>


                        </tr>
                        <tr style="border: 1px solid #8f8f8f;">

                            <td style="font-weight:bold">Landmark</td>
                            <td style="border-left: 1px solid #8f8f8f;">
                                <span>{{ $alarm->device->customer->landmark ?? '---' }} </span>
                            </td>


                        </tr>

                        <tr style="border: 1px solid #8f8f8f;">

                            <td style="font-weight:bold">Google Map </td>
                            <td style="border-left: 1px solid #8f8f8f;">
                                <span>https://www.google.com/maps?q={{ $alarm->device->customer->latitude }},{{ $alarm->device->customer->longitude }}</span>
                            </td>


                        </tr>


                    </table>
                </td>
            </tr>

            <tr>
                <td style="padding-top:10px;">

                    <table class="table-border table-border-all " style="width:100%;border:0px solid red">


                        <tr>
                            <td colspan="5" style="background-color:#8f8f8f;height:20px;color:#FFF;font-size:16px">
                                Contact Details</td>
                        </tr>
                        <tr>
                            <th>
                                Category
                            </th>
                            <th>
                                Name
                            </th>
                            <th>
                                Phone
                            </th>
                            <th>
                                Email
                            </th>
                            <th>
                                Notes
                            </th>

                        </tr>
                        @foreach ($alarm->device->customer->contacts as $contact)
                            @if (strtolower($contact->address_type) != 'police' &&
                                    strtolower($contact->address_type) != 'medical' &&
                                    strtolower($contact->address_type) != 'fire')
                                <tr>
                                    <td>
                                        {{ ucfirst($contact->address_type) }}
                                    </td>
                                    <td>
                                        {{ ucfirst($contact->first_name) }}
                                        {{ ucfirst($contact->last_name) }}
                                    </td>
                                    <td>
                                        {{ $contact->phone1 }}

                                    </td>
                                    <td>
                                        {{ $contact->email }}
                                    </td>
                                    <td style="font-size:10px">
                                        {{ $contact->notes }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach

                    </table>
                </td>
            </tr>

            <tr>
                <td style="padding-top:10px;padding-bottom:20px;">


                    <table class="table-border-top" style="width:100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="background-color:#8f8f8f;height:20px;color:#FFF;font-size:16px">
                                Operator Notes</td>
                        </tr>

                        <tr>
                            <td
                                style="border : 1px solid #8f8f8f;;width:100%;height:130px;padding:0px;vertical-align:top">

                                @if (count($alarm->notes) == 0)
                                    <div style="width:100%;height:50px;margin:auto;font-size:12px;text-align:center">
                                        <div style="margin:auto;padding-top:50px">
                                            Operator Notes are not available
                                        </div>

                                    </div>
                                @else
                                    <table style="width:100%;vertical-align:top">
                                        <tr>
                                            <td>

                                                <span>Date Time:
                                                    {{ changeDateformat($alarm->notes[count($alarm->notes) - 1]->created_datetime) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>

                                                Operator:
                                                {{ $alarm->notes[count($alarm->notes) - 1]->security ? ucfirst($alarm->notes[count($alarm->notes) - 1]->security->first_name) . ' ' . ucfirst($alarm->notes[count($alarm->notes) - 1]->security->last_name) : '---' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>

                                                Note: {{ $alarm->notes[count($alarm->notes) - 1]->notes }}
                                            </td>
                                        </tr>
                                    </table>
                                    <div style="padding-top: 10px;  ">

                                        <span class="bold" style="font-style:italic">
                                        </span>
                                    </div>
                                @endif


                            </td>
                        </tr>

                    </table>



                </td>
            </tr>

            <tr>
                <td style="width:100%;border-top: 0px solid #313131!important; ">
                    <div style="color:red">Required Action:</div>
                    <div>
                        Please verify the situation and take necessary action as per the security protocol. If immediate
                        intervention is required, contact the relevant authority listed above for further assistance.
                        Reach out to our XtremeGuard Security company Operator/Supervisor if needed.
                        <br />
                        @if ($alarm->security)
                            <div style="font-weight:bold;line-height:20px">
                                Name: Mr.{{ ucfirst($alarm->security->first_name) }}


                                {{ ucfirst($alarm->security->last_name) }}
                            </div>

                            <div style="font-weight:bold">Phone: {{ $alarm->security?->contact_number ?? '---' }}</div>
                        @endif

                    </div>
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
