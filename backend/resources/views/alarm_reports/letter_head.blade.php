<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alarm Events Report</title>
    <style>
        /* body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        @page {
            margin: 100px 25px;

        }

        header {
            position: fixed;
            top: -80px;
            left: 0px;
            right: 0px;
            height: 60px;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
        }

        footer {
            position: fixed;
            bottom: -50px;
            left: 0px;
            right: 0px;
            height: 30px;
            text-align: center;
            font-size: 12px;
        }

        .pagenum:before {
            content: counter(page) " of " counter(pages);
        } */


        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 10px;
        }

        @page {
            margin: 100px 25px 100px 25px;
            /* Adjust bottom margin */
        }

        @page :first {
            @bottom-center {
                content: "Company Name | Page {PAGE_NUM} of {PAGE_COUNT}";
            }
        }

        /* footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            text-align: center;
            font-size: 12px;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        } */



        header {
            position: fixed;
            top: -100px;
            /* Start the header 100px above the top of the page */
            left: 0;
            right: 0;
            height: 100px;
            text-align: center;

            padding-bottom: 10px;

            border: 0px solid red;

        }

        footer {
            position: fixed;
            bottom: -50px;
            height: 100px;
            left: 0;
            right: 0;
            height: 30px;
            border-top: 1px solid #ddd;
            padding-top: 0px;
            text-align: center;
            font-size: 10px;
            border: 0px solid red;
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
    <!-- Header -->
    <header>

        <table style="margin-top:  0px !important; padding-bottom:5px; ;width:100%;border:0px solid red; ">

            <tr>
                <td style="border: nonse;width:30%;vertical-align:middle;height:90px">
                    <div style="text-align:left;    ;margin:auto;">

                        @if (env('APP_ENV') !== 'local')
                        <img src="{{ $company->logo }}" style=" margin:auto;width:100px;max-width:150px;max-height:40px ">
                        @else
                        <img src="{{ getcwd() .   '/'.$company->logo_raw }}" style="margin:auto; width:100px;max-width:150px; ;max-height:40px  ">
                        @endif
                    </div>
                </td>
                <td style="text-align: center;width: 55%; border :0px solid red;padding-left:0px;margin:0px   ">
                    <table style="width:100%">
                        <tr>
                            <td style="text-align:center;font-size:14px">
                                {{$company->name}}
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align:center">

                            </td>

                        </tr>
                    </table>

                </td>
                <td style=" text-align: right; width:10% ;margin:auto">

                </td>
            </tr>

            <tr>
                <td colspan="100%" style="background-color:brown;height:10px"></td>
            </tr>

        </table>

    </header>


    <main>

    </main>

    <!-- Footer -->
    <footer>
        <table style="width:100%;text-align:center;border:0px solid black">

            <tr>
                <td colspan="100%" style="background-color:black;height:5px"></td>
            </tr>
            <tr>

                <td style="width:33%;vertical-align: middle;height:40px;;text-align:left">


                    <div style="font-weight:bold">{{ $company->name }}</div>

                    Phone1: {{ $company->contact->number ?? '---' }}
                </td>
                <td style="width:33%;vertical-align: middle;height:50px;padding-top:10px;border:0px solid red;margin:auto">
                    <table>
                        <tr>
                            <td>
                                <img style="margin :auto" src="{{env('BASE_PUBLIC_URL')}}/icons/website.png" width="15">
                            </td>
                            <td>
                                Email: {{ $company->user->email ?? '---' }}
                            </td>

                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                Website:---
                            </td>
                        </tr>
                    </table>





                </td>
                <td style="width:33%;vertical-align: middle;height:40px;text-align:left;padding-left:50px;"><span style=" margin-left: 3px">P.O.Box
                        {{ $company->p_o_box_no == 'null' ? '---' : $company->p_o_box_no }}</span>

                    <br />
                    <span style="margin-left: 3px">{{ $company->location }}</span>

                </td>
            </tr>
        </table>
    </footer>
    <script type="text/php">
        if (isset($pdf)) {
        $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $font = $fontMetrics->getFont("Verdana");
        $size = 6;
        $x = $pdf->get_width() - 50;
        $y = $pdf->get_height() - 30;
        $pdf->page_text($x, $y, $text, $font, $size);
    }

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
</script>
</body>

</html>