<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Armed Logs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 10px;
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
                <td style="text-align: center;width: 35%; border :0px solid red;padding-left:0px;margin:0px   ">
                    <table style="width:100%">
                        <tr>
                            <td style="text-align:center;font-size:14px">
                                Device Armed Logs : {{$reports[0]?$reports[0]->device->name:'---'}}
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

        <table width="100%" border="1" cellspacing="0" cellpadding="5">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Device</th>
                    <th>Armed Time</th>
                    <th>Disarm Time</th>

                    <th>Duration(HH:MM)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reports as $index=>$item )


                <tr>


                    <td>{{ $index+1 }}</td>
                    <td>{{ $item->device->name   }}
                    </td>

                    <td>{{ changeDateformat($item->armed_datetime)[1] }} <br /> {{ changeDateformat($item->armed_datetime)[0] }} </td>
                    <td>{{ changeDateformat($item->disarm_datetime)[1] }} <br /> {{ changeDateformat($item->disarm_datetime)[0] }} </td>

                    <td style="text-align:center">
                        @php
                        if($item->disarm_datetime==null)
                        echo '---';
                        else echo minutesToTime($item->duration_in_minutes)
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