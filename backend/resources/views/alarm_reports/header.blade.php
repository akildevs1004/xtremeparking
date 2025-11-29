<style>
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
</style>
<table style="margin-top:  0px !important; padding-bottom:5px; ;width:100%;border-bottom:1px solid #156082;">

    <tr>
        <td style="border: nonse; ;height:90px;width:200px;">
            <div style="text-align:left;    ;margin:auto;">

                @if (env('APP_ENV') !== 'local')
                    <img src="{{ $company->logo }}" style=" margin:auto;width:70px;max-width:70px;   ">
                @else
                    <img src="{{ getcwd() . '/' . $company->logo_raw }}"
                        style="margin:auto; width:70px;max-width:70px;    ">
                @endif

                {{-- <div style=" font-size:10px;padding-top:10px"> {{ $company->name }}</div> --}}

            </div>
        </td>
        <td style="text-align: center;  border :0px solid red;padding-left:0px;margin:0px   ">
            <table style="width:100%">
                <tr>
                    <td style="text-align:center;font-size:14px;color:red">
                        {{ $title1 }}
                    </td>
                </tr>
                <tr>
                    <td style="text-align:center">
                        @if ($title2)
                            {{ $title2 }}
                        @endif

                    </td>

                </tr>
            </table>

        </td>
        <td style=" text-align: center; width:90px; ;margin:auto;vertical-align:middle;margin:auto">


            @if (!empty($alarm_id))
                <img style="width:80px;   "
                    src="https://alarmbackend.xtremeguard.org/api/qrcodeevent?content={{ $alarm_id }}" />
            @endif

            <div style="font-size:9px;margin:auto;padding-top:5px"> {{ !empty($alarm_id) ? 'SCAN' : '' }}
                {{ date('d M Y ') }}</div>



        </td>
    </tr>
    <!-- <tr>
        <td colspan="100%" style="border-top:1px solid black"></td>
    </tr> -->
    <!-- <tr>
        <td colspan="100%" style="background-color:brown;height:10px"></td>
    </tr> -->

</table>
@php
    if (!function_exists('changeDateformatTime')) {
        function changeDateformatTime($date)
        {
            if ($date == '') {
                return '---';
            }
            $date = new DateTime($date);

            // Format the date to the desired format
            return $date->format('M j, Y') . ' ' . $date->format('H:i');
        }
    }
@endphp
