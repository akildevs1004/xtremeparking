<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alarm Events</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 10px;
        }

        @page {
            margin: 200px 25px 200px 200px;
            /* Adjust bottom margin */
        }

        @page :first {
            @bottom-center {
                content: "Company Name | Page {PAGE_NUM} of {PAGE_COUNT}";
            }
        }

        footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            text-align: center;
            font-size: 12px;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }



        header {
            position: fixed;
            top: -100px;
            /* Start the header 100px above the top of the page */
            left: 0;
            right: 0;
            height: 80px;
            text-align: center;

            padding-bottom: 10px;
        }

        footer {
            position: fixed;
            bottom: -150px;

            left: 0;
            right: 0;
            height: 30px;
            border-top: 1px solid #ddd;
            padding-top: 0px;
            text-align: center;
            font-size: 12px;
            border: 1px solid red;
        }

        .page-number {
            text-align: right;
            position: absolute;
            right: 0;
        }

        main {
            margin-top: 10px;
        }

        .table-border table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-border tr {
            border-top: 1px solid black;
        }

        .table-border th,
        .table-border td {
            padding: 8px;
            text-align: left;
        }
    </style>
</head>

<body>
    <header style="height:90px">

        <table style="margin-top:  0px !important; padding-bottom:5px;height:90px; ;width:100%;border:0px solid red">

            <tr>
                <td style="border: nonse;width:30%">
                    <div style="text-align:left;    ;margin:auto;">

                        @if (env('APP_ENV') !== 'local')
                        <img src="{{ $company->logo }}" style=" margin:auto;width:100px;max-width:150px;max-height:40px ">
                        @else
                        <img src="{{ getcwd() .   '/'.$company->logo_raw }}" style="margin:auto; width:100px;max-width:150px; ;max-height:40px  ">
                        @endif
                    </div>
                </td>
                <td style="text-align: center;width: 35%; border :0px solid red;padding-left:0px;margin:0px   ">
                    <table style="width:100%">
                        <tr>
                            <td style="text-align:center;font-size:14px">
                                Alarm Events Report
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align:center">
                                From {{changeDateformat($request->date_from)[0]}} to {{changeDateformat($request->date_to)[0]}}
                            </td>
                        </tr>
                    </table>





                </td>
                <td style=" text-align: right; width:35% ;margin:auto">

                    <table style="padding:0px;margin:0px;border-left :1px solid #DDD; ">
                        <tr style="text-align: left; border :none;padding:0px 0px">
                            <td style="text-align: left; border :none;font-size:12px;padding:0 0 5px 0px;">
                                <b style="padding:0px;margin:0px">
                                    {{ $company->name }}
                                </b>
                                <br>
                            </td>
                        </tr>
                        <tr style="text-align: left; border :none;padding:0px 0px">
                            <td style="text-align: left; border :none;font-size:10px;padding:0 0 5px 0px;">
                                <span style="margin-left: 3px">P.O.Box
                                    {{ $company->p_o_box_no == 'null' ? '---' : $company->p_o_box_no }}</span>
                                <br>
                            </td>
                        </tr>
                        <tr style="text-align: left; border :none;padding:0px 0px">
                            <td style="text-align: left; border :none;font-size:10px;padding:0 0 5px 0px;">
                                <span style="margin-left: 3px">{{ $company->location }}</span>
                                <br>
                            </td>
                        </tr>
                        <tr style="text-align: left; border :none;padding:0px 0px">
                            <td style="text-align: left; border :none;font-size:10px;padding:0 0 5px 0px;">
                                <span style="margin-left: 3px">{{ $company->contact->number ?? '' }}</span>
                                <br>
                            </td>
                        </tr>

                    </table>

                </td>
            </tr>

            <tr>
                <td colspan="100%" style="background-color:brown;height:10px"></td>
            </tr>

        </table>

    </header>

    <main>

        <!-- <table width="100%">
            <tr style="background-color:black ">
                <td colspan=100% style="margin-bottom:5px;height:10px">

                </td>

            </tr>
        </table> -->
        @php if(count($reports)>0) { @endphp
        <br />
        <table class="table-border" width="100%" cellspacing="0" cellpadding="5">



            <thead>
                <tr>
                    <th>Event Id</th>
                    <th>Customer</th>
                    <th>Property</th>
                    <th>Address</th>
                    <th>City</th>
                    <th>Type</th>
                    <th>Event Time</th>
                    <th>Closed Time</th>
                    <th>Priority</th>
                    <th>Resolved Time(HH:MM)</th>
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
                    <td style="text-align:center">
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

    <footer>
        <table style="width:100%; border-top: 1px solid #ddd; font-size: 10px; padding-top: 5px;">
            <tr>
                <td style="text-align: left;">
                    <b>{{ $company->name }}</b>
                </td>
                <td style="text-align: center;">
                    Generated on: {{ date('d M Y') }}
                </td>
                <td style="text-align: right;">
                    Page <span class="page-number"></span> of <span class="total-pages"></span>
                </td>
            </tr>
        </table>
    </footer>


    <script type="text/php">
        if (isset($pdf)) {
        $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $font = $fontMetrics->getFont("Verdana");
        $size = 8;
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