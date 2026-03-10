<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alarm Customer Events with Notes</title>
    <style>
        .my-break {
            page-break-after: always;
            /* background-color: red !important; */
            border-color: red;
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

        .no-border {
            border: 0px !important;
        }

        .no-border tr {
            border: 0px;
        }

        .no-border td {
            border: 0px;
        }

        .no-border table {
            border: 0px;
        }
    </style>
</head>

<body>

    @php


    $customerLogo=getcwd() .'/no-business_profile.png';
    $companyLogo=getcwd() .'/no-business_profile.png';
    $securityLogo=getcwd() .'/no-profile-image.jpg';
    if (env('APP_ENV') !== 'local')
    {

    if($customer['profile_picture']!='')
    {
    $customerLogo=$customer['profile_picture'];
    }
    }








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
    $title1='Alarm Customer Events with Notes';

    @endphp
    <!-- Header -->
    <header style="border-bottom:0px solid #313131">

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
        <table class="table-border-header" style="width:100%;" cellspacing="0" cellpadding="0">
            <tr>
                <td colspan="2" style="background-color:#8f8f8f;height:20px;color:#FFF;font-size:16px">Customer Details</td>
            </tr>
            <tr>
                <td style="  padding:0px">
                    <table class="table-border-top11" width="100%" cellspacing="0" cellpadding="0" style="width:100%;border-collapse: collapse;">

                        <tr style="border: 1px solid #8f8f8f;border-top:0px">
                            <td rowspan="4" style="width:100px;text-align:center"> <img
                                    style="border-radius: 10%;height: 70px;min-height: 70px;  "
                                    src="{{ $customerLogo }}" /> </td>

                            <td style="font-weight:bold">Customer/Building Name</td>
                            <td>: {{ $customer['building_name'] }}</td>

                            <td style="font-weight:bold">Type</td>
                            <td>:{{$customer['buildingtype']['name']}}</td>


                        </tr>
                        <tr style="border: 1px solid #8f8f8f;">

                            <td style="font-weight:bold">Address</td>
                            <td colspan="3">: <span>{{$customer->house_number ?? "---"}}, {{$customer->street_number ?? "---"}}</span>
                                <span>{{$customer->area ?? "---"}}, {{$customer->city ?? "---"}} </span> <span>{{$customer->state ?? "---"}}, {{$customer->country ?? "---"}} </span>
                            </td>


                        </tr>

                        <tr style="border: 1px solid #8f8f8f;">

                            <td style="font-weight:bold">Contact</td>

                            <td colspan=3>: {{$customer?->primary_contact?->first_name ?? "---"}} {{$customer?->primary_contact?->last_name ?? "---"}} </td>


                        </tr>
                        <tr style="border: 1px solid #8f8f8f;">

                            <td style="font-weight:bold">Phone Number</td>

                            <td>: {{$customer?->primary_contact?->phone1 ?? "---"}}</td>
                            <td style="font-weight:bold">Email</td>

                            <td>: {{$customer?->primary_contact?->email ?? "---"}}</td>

                        </tr>

                    </table>
                </td>

                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr style="border: 1px solid #8f8f8f;">
                            <td style="color:#FF0000">SOS</td>
                            <td>{{ $counts->soscount}}</td>

                            <td style="color:#df3079">Critical</td>
                            <td>


                                {{ $counts->criticalcount}}



                            </td>


                        </tr>
                        <tr style="border: 1px solid #8f8f8f;">
                            <td style="color:#007BFF">Technical</td>
                            <td> {{ $counts->technicalcount}}
                            </td>
                            <td style="color:black">Event</td>
                            <td> {{ $counts->eventscount}}
                            </td>




                        </tr>

                        <tr style="border: 1px solid #8f8f8f;">

                            <td style="color:#28A745">Medical</td>
                            <td> {{ $counts->medicalcount}}
                            </td>

                            <td style="color:#FF8C00">Fire</td>
                            <td> {{ $counts->firecount}}
                            </td>


                        </tr>
                        <tr style="border: 1px solid #8f8f8f;">


                            <td style="color:#20C997">Water</td>
                            <td> {{ $counts->watercount}}
                            </td>
                            <td style="color:#DC3545">Temperature</td>
                            <td> {{ $counts->temperaturecount}}
                            </td>

                        </tr>

                    </table>
                </td>
            </tr>



        </table>


        @php if(count($reports)>0) { @endphp
        <br />


        <table class="table-border" width="100%" cellspacing="0" cellpadding="5">



            <tbody>



                @foreach ($reports as $index=>$item)
                <tr style="border:0px">
                    <td colspan="7" style="border:0px;background-color:#8f8f8f;height:20px;color:#FFF;font-size:16px">Event ID : #{{ $item->id }}</td>
                </tr>
                <tr style="border-top:0px;font-weight:bold">
                    <td>#</td>

                    <!-- <td>Property</td> -->
                    <!-- <td>Address</td>
                    <td>City</td> -->
                    <td>Date</td>
                    <td>Type</td>
                    <td>Event Time</td>
                    <td>Closed Time</td>
                    <td>Priority</td>
                    <td>Duration <br /> (HH:MM)</td>
                </tr>


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



                    <td>{{ ++$index }}</td>
                    <!-- <td>

                        {{$item->device->customer?->building_name??'---'}}
                        <div style="font-size:8px">{{ $item->device->customer?->primary_contact?->first_name ?? '---' }}
                            {{ $item->device->customer?->primary_contact?->last_name ?? '---' }}
                        </div>
                    </td> -->

                    <!-- <td>{{ $item->device->customer?->buildingtypee?->name ??'---'  }} </td> -->
                    <!-- <td>{{ $item->device->customer?->area ?? '---' }} </td>
                    <td>{{ $item->device->customer?->city ?? '---' }} </td> -->
                    <td>{{ changeDateformat($item->alarm_start_datetime)[0] }} </td>
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
                <tr>

                    <td colspan="7">

                        @if (count($item->notes)==0)
                        <div style="width:100%;height:50px;margin:auto;font-size:12px;text-align:center">
                            <div style="margin:auto;padding-top:50px">
                                Operator Notes are not available
                            </div>

                        </div>
                        @endif
                    </td>
                </tr>


                @foreach ($item->notes as $note )
                <tr class="no-border" cellpadding="0" cellspacing="0">

                    <td style="padding:0px" colspan="7">@if($note->event_status== 'Forwarded')
                        @include('alarm_reports.include_alarm_event_notes_track_forward1', [
                        'note' => $note
                        ])

                        @elseif($note->event_status != 'Forwarded')


                        @include('alarm_reports.include_alarm_event_notes_track_operator_notes', [
                        'note' => $note
                        ])


                        @endif



                    </td>
                </tr>

                @endforeach
                <tr class="no-border">

                    <td colspan="7" style="padding:0px">

                        @if($item->alarm_end_datetime != '')
                        <!-- Closed Alarm  -->

                        @include('alarm_reports.include_alarm_event_notes_track_closed', [

                        'alarm' => $item
                        ])

                        @endif

                    </td>
                </tr>

                @php if(count($reports)>1) { @endphp
                <tr class="my-break" style="border:0px">
                    <td colspan="7" style="border: none;text-align:center;color:red">Alarm #{{$item->id}} Notes Page End</td>
                </tr>
                @php } @endphp


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
