<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Armed Report {{ $request->date_from}} - {{$request->date_to}}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;

        }

        @page {
            margin: 130px 25px;
            /* Top margin adjusted to fit the 100px header */
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
            bottom: -60px;
            left: 0;
            right: 0;
            height: 30px;
            border-top: 1px solid #ddd;
            padding-top: 0px;
            text-align: center;
            font-size: 12px;
        }

        .page-number {
            text-align: right;
            position: absolute;
            right: 0;
        }

        main {
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <header style="height:100px">

        <table style="margin-top:  0px !important; padding-bottom:0px;height:100px; ;width:100%;border:0px solid red">
            <tr>
                <td style="border: nonse;width:30%">
                    <div style="text-align:left;    ;margin:auto;">

                        @if (env('APP_ENV') !== 'local')
                        <img src="{{ $company->logo }}" style=" margin:auto;width:100px;max-width:150px;max-height:60px ">
                        @else
                        <img src="{{ getcwd() .   '/'.$company->logo_raw }}" style="margin:auto; width:100px;max-width:150px; ;max-height:60px  ">
                        @endif
                    </div>
                </td>
                <td style="font-size:12px;text-align: center;width: 35%; border :0px solid red;padding-left:0px;margin:0px   ">
                    <table style="width:100%">
                        <tr>
                            <td style="text-align:center">
                                Device Armed Report
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align:center">
                                From {{changeDateformat($request->date_from)[0]}} to {{changeDateformat($request->date_to)[0]}}
                            </td>
                        </tr>
                    </table>




                    </div>
                </td>
                <td style=" text-align: right; width:35% ;margin:auto">

                    <table style="padding:0px;margin:0px;border-left :1px solid #DDD; ">
                        <tr style="text-align: left; border :none;padding:10px 0px">
                            <td style="text-align: left; border :none;font-size:12px;padding:0 0 5px 0px;">
                                <b style="padding:0px;margin:0px">
                                    {{ $company->name }}
                                </b>
                                <br>
                            </td>
                        </tr>
                        <tr style="text-align: left; border :none;padding:10px 0px">
                            <td style="text-align: left; border :none;font-size:10px;padding:5px 0px;">
                                <span style="margin-left: 3px">P.O.Box
                                    {{ $company->p_o_box_no == 'null' ? '---' : $company->p_o_box_no }}</span>
                                <br>
                            </td>
                        </tr>
                        <tr style="text-align: left; border :none;padding:10px 0px">
                            <td style="text-align: left; border :none;font-size:10px;padding:5px 0px">
                                <span style="margin-left: 3px">{{ $company->location }}</span>
                                <br>
                            </td>
                        </tr>
                        <tr style="text-align: left; border :none;padding:10px 0px">
                            <td style="text-align: left; border :none;font-size:10px;padding:5px 0px">
                                <span style="margin-left: 3px">{{ $company->contact->number ?? '' }}</span>
                                <br>
                            </td>
                        </tr>

                    </table>

                </td>
            </tr>
        </table>
    </header>

    <main>


        @php if(count($reports)>0) { @endphp

        <table width="100%" border="1" style="font-size:9px" cellspacing="0" cellpadding="5">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Arm1</th>
                    <th>Dis1</th>

                    <th>Arm2</th>
                    <th>Dis2</th>

                    <th>Arm3</th>
                    <th>Dis3</th>

                    <th>Arm4</th>
                    <th>Dis4</th>

                    <th>Arm5</th>
                    <th>Disa5</th>

                    <th>Armed </th>
                    <th>Disarm </th>

                    <th>Events</th>

                </tr>
            </thead>
            <tbody>
                @foreach ($reports as $index=>$item )


                <tr>


                    <td>{{ $index+1 }}</td>
                    <td>{{ date("M d",strtotime($item['date']))   }}</td>
                    <td>{{ $item['customer'] }}</td>

                    @for ($i = 0; $i < 5; $i++)
                        @if (is_array($item['armed']) && isset($item['armed'][$i]))
                        <td style="color:red">{{ changeDateformat($item['armed'][$i]['armed_datetime'])[1]   }}

                        <div style="font-size:7px;">{{ changeDateformat($item['armed'][$i]['armed_datetime'])[0]   }}</div>
                        </td>

                        <td style="color:green">{{ changeDateformat($item['armed'][$i]['disarm_datetime'])[1]   }}

                            <div style="font-size:7px;">{{ changeDateformat($item['armed'][$i]['disarm_datetime'])[0]   }}</div>
                        </td>
                        @else
                        <td>---</td>
                        <td>---</td>
                        @endif


                        @endfor


                        <td style="color:red">{{ $item['armedHours'] }}</td>
                        <td style="color:green">{{ $item['disarmHours'] }}</td>
                        <td style="text-align:center">{{ $item['events_count'] }}</td>

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


    </footer>
    <script type="text/php">
        if (isset($pdf)) {
        $text = "Page Number {PAGE_NUM} / {PAGE_COUNT}";
        $size = 8;
        $font = $fontMetrics->getFont("Verdana");
        $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
        $x =$pdf->get_width() - $width;// ($pdf->get_width() - $width) / 2;
        $y = $pdf->get_height() - 35;
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