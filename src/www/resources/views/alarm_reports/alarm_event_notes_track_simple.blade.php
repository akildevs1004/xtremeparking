<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alarm Event #{{$alarm->id}} - Track Notes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        @page {
            margin: 0px 0px;
            /* Top margin adjusted to fit the 100px header */
        }

        /* header {
            position: fixed;
            top: -100px;
          
        left: 0;
        right: 0;
        height: 100px;
        text-align: center;
        border-bottom: 1px solid #ddd;
        padding-bottom: 10px;
        }

        */
        /* footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            height: 50px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            text-align: center;
            font-size: 12px;
        } */

        .page-number {
            text-align: right;
            position: absolute;
            right: 0;
            font-size: 10px;
            ;
        }

        main {
            margin-top: 20px;
        }
    </style>
</head>

<body>

    @php

    $customerLogo=getcwd() .'/no-business_profile.png';
    $companyLogo=getcwd() .'/no-business_profile.png';
    $securityLogo=getcwd() .'/no-profile-image.jpg';

    if($alarm['device']&&$alarm['device']['customer']&&$alarm['device']['customer']['profile_picture']!='')
    {
    $customerLogo=$alarm['device']['customer']['profile_picture'];
    }
    if($alarm['device']&&$alarm['device']['company']&&$alarm['device']['company']['logo']!='')
    {
    $companyLogo=$alarm['device']['company']['logo'];
    }
    if($alarm['security']&&$alarm['security']['picture']&&$alarm['security']['picture'] !='')
    {
    $securityLogo=$alarm['security']['picture'];
    }





    @endphp
    <header style="min-height:0px; width:750px">

    </header>
    <!-- <footer>
        Footer Content

    </footer> -->
    <main>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Event Timeline</title>
            <link rel="stylesheet" href="styles.css">
        </head>

        <body>
            <table
                style="
        width: 750px;
        border: 0px solid black;
        margin: auto;
        line-height: 15px;font-size: 9px;
      ">
                <tr>
                    <td>
                        <table>
                            <tr>
                                <td style="width: 45px">
                                    <img
                                        style="
                    border-radius: 50%;
                    height: 40px;
                    min-height: 40px;
                    width: 40px;
                    max-width: 40px;
                  "
                                        src="{{ $customerLogo }}" />
                                </td>
                                <td>
                                    <div>
                                        <div style="font-weight: bold">
                                            {{ $alarm['device']['customer']['building_name']   }} <span>({{$alarm['device']['customer']['buildingtype']['name']}})</span>
                                        </div>
                                        <div>{{$alarm->device->customer->house_number ?? "---"}}, {{$alarm->device->customer->street_number ?? "---"}}</div>
                                        <div>{{$alarm->device->customer->area ?? "---"}}, {{$alarm->device->customer->city ?? "---"}}</div>
                                        <div>{{$alarm->device->customer->user->email ?? "---"}}</div>
                                        <div>{{$alarm->device->customer->contact_number ?? "---"}}</div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="border-left: 1px solid #ddd">
                        <table>
                            <tr>
                                <td style="width: 45px">
                                    <img
                                        style="
                    border-radius: 50%;
                    height: 40px;
                    min-height: 40px;
                    width: 40px;
                    max-width: 40px;
                  "
                                        src="{{ $companyLogo }}" />
                                </td>
                                <td>
                                    <div>
                                        <div style="font-weight: bold">{{$alarm->device->company->name ?? "---"}}</div>
                                        <div>{{$alarm->device->company->location ?? "---"}}</div>
                                        <div>{{$alarm->device->company->user->email ?? "---"}}</div>
                                        <div>{{$alarm->device->company->contact_number ?? "---"}}</div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="border-left: 1px solid #ddd">
                        <table>
                            <tr>
                                <td style="width: 45px">

                                    <img
                                        style="
                    border-radius: 50%;
                    height: 40px;
                    min-height: 40px;
                    width: 40px;
                    max-width: 40px;
                  "
                                        src="{{ $securityLogo }}" />
                                </td>
                                <td>
                                    <div>
                                        <div style="font-weight: bold">{{$alarm['security'] ? $alarm['security'] ['first_name'].' '.$alarm['security'] ['last_name'] : '---'}} </div>
                                        <div>{{$alarm['security'] ? $alarm['security']['user']['email']  : '---'}} </div>
                                        <div>{{$alarm['security'] ? $alarm['security']['contact_number']  : '---'}} </div>

                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="border-left: 1px solid #ddd;width:220px">
                        <table>
                            <tr>
                                <td style="text-align: center">

                                    <img
                                        style="
                    border-radius: 50%;

                    width: 20px;
                    max-width: 20px;
                  "
                                        src="{{env('BASE_URL')}}{{$icons['alarm']}}" />
                                    <div>
                                        <div style="color: blue"> {{ $alarm->alarm_type ?? "---" }},
                                            {{ $alarm->zoneData->location ?? "---"   }}
                                        </div>
                                        <div style="color: red">
                                            {{ $alarm->zoneData->sensor_type ?? "---" }}
                                            <div> {{ $alarm->zoneData->sensor_name ?? "---" }} </div>
                                        </div>
                                    </div>
                                </td>
                                <td style="  padding-left: 10px">
                                    <table style="width: 100%; border-collapse: collapse">
                                        <tr style="border-bottom: 1px solid #ddd">
                                            <td>Event Id</td>
                                            <td style="color:red">#{{ $alarm->id   }}</td>
                                        </tr>
                                        <tr style="border-bottom: 1px solid #ddd">
                                            <td>Status</td>

                                            <td> @if($alarm->forwarded ==true && $alarm->alarm_status==1)
                                                Forwarded
                                                @elseif($alarm->alarm_status==1)
                                                Open
                                                @else
                                                Closed
                                                @endif</td>
                                        </tr>
                                        <tr style="border-bottom: 1px solid #ddd">
                                            <td>Category</td>
                                            <td>@if($alarm->alarm_category==1 )
                                                Critical
                                                @elseif ($alarm->alarm_category==2)
                                                Medium
                                                @else
                                                Low
                                                @endif

                                            </td>
                                        </tr>
                                        <tr style="border-bottom: 1px solid #ddd">
                                            <td>Start</td>
                                            <td>{{changeDateformatTime($alarm->alarm_start_datetime)}}</td>
                                        </tr>
                                        <tr style="border-bottom: 1px solid #ddd">
                                            <td>End</td>
                                            <td>{{changeDateformatTime($alarm->alarm_end_datetime)}}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <hr style="color:#ddd;margin-top:0px " />

            @if (count($alarm->notes)==0)
            <div style="width:100%;height:50px;margin:auto;font-size:12px;text-align:center">
                <div style="margin:auto;padding-top:50px">
                    Operator Notes are not available
                </div>

            </div>
            @endif



            <table cellpadding="0" cellspacing="0">
                @foreach ($alarm->notes as $note )
                <!-- Forward -->
                @if($note->event_status== 'Forwarded')
                <tr cellpadding="0" cellspacing="0">
                    <td cellpadding="0" cellspacing="0">
                        <table cellpadding="0" cellspacing="0">
                            <td
                                style="
                      vertical-align: middle;
                      text-align: center;
                      font-size: 10px;width:180px
                    ">
                                {{changeDateformatTime($note->created_datetime)}}
                            </td>
                            <td
                                style="
                      border-left: 1px solid #ddd;
                      vertical-align: middle;
                      text-align: center;width:70px
                    ">
                                <div
                                    style="
                        position: relative;
                        left: -24px;
                        width: 1px;
                        height: 1px;
                        border-radius: 50%;
                        padding: 20px;
                        border: 1px solid #000;
                        background-color: #fff;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                      ">
                                    <div style="text-align: center">
                                        <img
                                            style="
                            border-radius: 50%;

                            width: 35px;
                            max-width: 35px;
                            margin-top:-16px
                          "
                                            src="https://alarmbackend.xtremeguard.org/forwardicon.png" />
                                    </div>
                                </div>
                            </td>
                            <td style="vertical-align: middle;">
                                <div style="position: relative; top: 50px; left: -15px">
                                    <img
                                        style="width: 15px"
                                        src="https://alarmbackend.xtremeguard.org/alarm-notes-left-arrow.png?1=2" />
                                </div>
                                <div style="  margin:auto; ">
                                    <div
                                        style="
                                        min-height:80px;
                        height:auto;
                        border: 1px solid #ddd;
                        border-radius: 6px;
                        padding-left: 20px;
                        padding-top: 20px;
                        margin-top: -10px;
                      ">
                                        <div style="font-weight: bold; font-size: 14px">

                                            Forwarded



                                        </div>
                                        <div style="padding-top: 10px; font-size: 10px">
                                            <span class="bold">Notes: </span>{{ $note->notes }}
                                        </div>

                                    </div>
                                </div>

                            </td>
                        </table>
                    </td>
                </tr>

                @elseif($note->event_status != 'Forwarded') <!-- Contact -->
                <tr style=" border-collapse: collapse;">
                    <td>
                        <table cellpadding="0" cellspacing="0" style="height:140px">
                            <td
                                style="
                      vertical-align: middle;
                      text-align: left;
                      font-size: 10px;width:180px
                    ">
                                <div style="width:100%;text-align:center;font-weight:bold">
                                    {{changeDateformat($note->created_datetime)}}
                                </div>

                                @if($note->contact)


                                <div style="text-align:left;font-size:8px;padding-left:50px;margin-top:10px;">
                                    <div style="font-weight:bold">
                                        {{ $note->contact?->first_name }}
                                        {{ $note->contact?->last_name }}
                                    </div>
                                    <div>
                                        {{ $note->contact->phone1 }}
                                    </div>
                                    <div>
                                        {{ $note->contact->phone2 }}
                                    </div>
                                    <div>
                                        {{ $note->contact->email }}
                                    </div>
                                    <div>
                                        {{ $note->contact->whatsapp }}
                                    </div>
                                </div>
                                @endif
                            </td>
                            <td
                                style="
                      border-left: 1px solid #ddd;
                      vertical-align: middle;
                      text-align: center;width:70px
                    ">
                                <div
                                    style="
                        position: relative;
                        left: -24px;
                        width: 1px;
                        height: 1px;
                        border-radius: 50%;
                        padding: 20px;
                        border: 1px solid #000;
                        background-color: #fff;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                      ">
                                    <div style="text-align: center">

                                        <img
                                            style="
                            border-radius: 50%;

                            width: 35px;
                            max-width: 35px;
                            margin-top:-16px
                          "
                                            src="{{ $note->contact?->profile_picture ?: 'https://alarmbackend.xtremeguard.org/unknown-person-icon.png' }}" />


                                    </div>
                                </div>
                            </td>
                            <td style="vertical-align: middle;height:140px; ">
                                <div style="position: relative; top: 50px; left: -15px">
                                    <img
                                        style="width: 15px"
                                        src="https://alarmbackend.xtremeguard.org/alarm-notes-left-arrow.png?1=2" />
                                </div>
                                <div style="  margin:auto; ">

                                    <div
                                        style="
                         
                        border: 1px solid #ddd;
                        border-radius: 6px;
                        padding-left: 20px;
                        padding-top: 20px;padding-right: 20px;
                        margin-top: -10px;
                        height:auto
                      ">
                                        <div style="font-weight: bold; font-size: 14px">
                                            {{$note->contact->address_type}}
                                        </div>
                                        <div style="padding-top: 10px; font-size: 10px">
                                            <span class="bold">Notes: </span>{{ $note->notes }}
                                        </div>

                                        <hr style="color:#ddd;margin-top:10px " />
                                        <table style=font-size:10px;>
                                            <tr>
                                                <td style="font-weight:bold;width:60px;">Call Status
                                                </td>
                                                <td>: {{ $note->call_status }}

                                                </td>
                                                <td style="font-weight:bold;width:70px;">Call Response

                                                </td>
                                                <td>: {{ $note->response }}

                                                </td>
                                                <td style="font-weight:bold;width:70px;">Event Status

                                                </td>
                                                <td>: {{ $note->event_status }}

                                                </td>
                                            </tr>
                                        </table>

                                        <div style="height:20px"> </div>
                                    </div>


                                </div>
                            </td>
                        </table>
                    </td>
                </tr> <!-- Contact -->

                @endif





                @endforeach

                @if($alarm->alarm_end_datetime != '')
                <!-- Closed Alarm  -->
                <tr style=" border-collapse: collapse;">
                    <td>
                        <table cellpadding="0" cellspacing="0" style="height:150px">
                            <td
                                style="
                      vertical-align: middle;
                      text-align: center;
                      font-size: 10px;width:180px;color:red
                    ">
                                {{changeDateformatTime($alarm->alarm_end_datetime)}}
                            </td>
                            <td
                                style="
                      border-left: 1px solid #ddd;
                      vertical-align: middle;
                      text-align: center;width:70px
                    ">
                                <div
                                    style="
                        position: relative;
                        left: -24px;
                        width: 1px;
                        height: 1px;
                        border-radius: 50%;
                        padding: 18px;
                        border: 1px solid #000;
                        background-color: #fff;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                      ">
                                    <div style="text-align: center">
                                        <div
                                            style="
                        position: relative;
                         
                        width: 1px;
                        height: 1px;
                        border-radius: 50%;
                        padding: 12px;
                        border: 1px solid #FFF;
                        background-color:  red;
                        display: flex;
                        justify-content: center;
                        align-items: center; 

                            
                            
                            margin-top:-13px;
                            margin-left:-13px;
                      ">

                                        </div>
                                    </div>
                            </td>
                            <td style="vertical-align: middle;height:140px; ">
                                <div style="position: relative; top: 50px; left: -15px">
                                    <img
                                        style="width: 15px"
                                        src="https://alarmbackend.xtremeguard.org/alarm-notes-left-arrow.png?1=2" />
                                </div>
                                <div style="  margin:auto; ">

                                    <div
                                        style="
                         
                        border: 1px solid #ddd;
                        border-radius: 6px;
                        padding-left: 20px;
                        padding-top: 20px;padding-right: 20px;
                        margin-top: -10px;
                        height:auto
                      ">
                                        <div style="font-weight: bold; font-size: 14px">
                                            Alarm Event Closed at {{changeDateformatTime($alarm->alarm_end_datetime)}}
                                        </div>
                                        <div style="padding-top: 10px; font-size: 10px">
                                            @if($alarm->alarm_end_manually == 1)
                                            <div>
                                                Operator Verified PIN with {{ $alarm->pin_verified_by }} -

                                                {{
                        $alarm->pinverifiedby
                          ? $alarm->pinverifiedby->first_name +
                            " " +
                            $alarm->pinverifiedby->last_name
                          : "---"
                      }}
                                            </div>
                                            @else

                                            Auto Closed by Disarm Event
                                            @endif
                                        </div>

                                        <div style="height:30px"> </div>
                                    </div>


                                </div>
                            </td>
                        </table>
                    </td>
                </tr>

                @endif
            </table>

        </body>

        </html>

    </main>
    <script type="text/php">
        if (isset($pdf)) {
        $text = "Page Number {PAGE_NUM} / {PAGE_COUNT}";
        $size = 6;
        $font = $fontMetrics->getFont("Verdana");
        $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
        $x =$pdf->get_width() - $width;// ($pdf->get_width() - $width) / 2;
        $y = $pdf->get_height() - 35;
        $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>

    <style>
        td {
            padding: 0;
            margin: 0;

        }



        table {
            border-collapse: collapse;
            width: 100%;

        }

        /* table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
        }

        th,
        td {
            padding-top: 10px;
            padding-bottom: 20px;
            padding-left: 30px;
            padding-right: 40px;
        } */
    </style>


</body>

<style>

</style>

</html>
@php

function changeDateformat($date)

{
if($date=='') return '---';
$date = new DateTime($date);

// Format the date to the desired format
return $date->format('M j, Y').' '.$date->format('H:i') ;
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