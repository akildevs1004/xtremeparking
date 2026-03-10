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
<table style="width:100%; padding-bottom:5px; border-collapse:collapse;">

    <tr>
        <!-- LEFT SECTION: LOGO + ADDRESS -->
        <td style="width:100px; vertical-align:top; padding-top:5px; border:none;padding-top:30px">
            <div style="text-align:left;">
                @if (env('APP_ENV') !== 'local')
                    <img src="{{ $company->logo }}" style="width:70px; max-width:70px;">
                @else
                    <img src="{{ getcwd() . '/' . $company->logo_raw }}" style="width:70px; max-width:70px;">
                @endif

                <div style="font-size:9px; margin-top:5px; line-height:12px;">
                    {{ $company->address }}
                </div>
            </div>
        </td>

        <!-- CENTER SECTION: COMPANY NAME + REPORT TITLE -->
        <td style="text-align:center; vertical-align:middle; border:none;">
            <div style="font-size:13px; font-weight:bold; margin-top:5px;">
                {{ $company->name }}
            </div>

            @if ($title1)
                <div style="font-size:12px; margin-top:3px;">
                    {{ $title1 }}
                </div>
            @endif

            @if (!empty($title2))
                <div style="font-size:11px; margin-top:2px;">
                    {{ $title2 }}
                </div>
            @endif

            @if (!empty($from_date) && !empty($to_date))
                <div style="font-size:10px; margin-top:6px;">
                    From: {{ $from_date }} &nbsp;&nbsp; To: {{ $to_date }}
                </div>
            @endif
        </td>

        <!-- RIGHT SECTION: QR + DATE -->
        <td style="width:100px; text-align:center; vertical-align:middle; border:none;">
            @if (!empty($alarm_id))
                <img src="https://alarmbackend.xtremeguard.org/api/qrcodeevent?content={{ $alarm_id }}"
                    style="width:80px; margin-bottom:5px;">
            @endif

            <div style="font-size:10px; margin-top:5px;">
                {{ !empty($alarm_id) ? 'SCAN' : '' }}
                {{ date('d M Y') }}
            </div>
        </td>
    </tr>

</table>

<!-- SINGLE BLUE SEPARATOR LINE -->
<div style="width:100%; height:1px; background:#156082; margin-top:5px;"></div>

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
