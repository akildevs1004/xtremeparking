<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report1</title>
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
            height: 100px;
            text-align: center;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            height: 50px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            text-align: center;
            font-size: 12px;
        }

        .page-number {
            text-align: right;
            position: absolute;
            right: 0;
        }

        main {
            margin-top: 20px;
        }
    </style>
</head>

<body>

    @php



    echo $alarm['device']['customer']['profile_picture'];
    exit;

    $customerLogo=getcwd() .'no-business_profile.png';
    $companyLogo=getcwd() .'no-business_profile.png';
    $operatorLogo=getcwd() .'no-profile-image.png';





    @endphp
    <header style="min-height:100px; width:750px">
        <table
            style="
        width: 750px;
        border: 0px solid black;
        margin: auto;
        line-height: 15px;
      ">
            <tr>
                <td>
                    <table>
                        <tr>
                            <td style="width: 50px">
                                <img
                                    style="
                    border-radius: 50%;
                    height: 50px;
                    min-height: 50px;
                    width: 50px;
                    max-width: 50px;
                  "
                                    src="https://alarmbackend.xtremeguard.org/customers/building_photos/1725614712.png" />
                            </td>
                            <td>
                                <div style="font-size: 10px">
                                    <div style="font-weight: bold">
                                        Akil Security <span>(Commercial)</span>
                                    </div>
                                    <div>888-88-456, Al Fahidi,</div>
                                    <div>Bur Dubai</div>
                                    <div>customer@gmail.com</div>
                                    <div>971559952568</div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="border-left: 1px solid #ddd">
                    <table>
                        <tr>
                            <td style="width: 50px">
                                <img
                                    style="
                    border-radius: 50%;
                    height: 50px;
                    min-height: 50px;
                    width: 50px;
                    max-width: 50px;
                  "
                                    src="https://alarmbackend.xtremeguard.org/upload/1724341619.png"
                                    alt="Akil Security Logo" />
                            </td>
                            <td>
                                <div style="font-size: 10px">
                                    <div style="font-weight: bold">Xtreme Guard LLC</div>
                                    <div>888-88-456, Al Fahidi,</div>
                                    <div>Bur Dubai</div>
                                    <div>customer@gmail.com</div>
                                    <div>971559952568</div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="border-left: 1px solid #ddd">
                    <table>
                        <tr>
                            <td style="width: 50px">
                                <img
                                    style="
                    border-radius: 50%;
                    height: 50px;
                    min-height: 50px;
                    width: 50px;
                    max-width: 50px;
                  "
                                    src="https://alarmbackend.xtremeguard.org/security/1726599377.jpeg"
                                    alt="Akil Security Logo" />
                            </td>
                            <td>
                                <div style="font-size: 10px">
                                    <div style="font-weight: bold">Sravan Kumar</div>
                                    <div>888-88-456, Al Fahidi,</div>
                                    <div>Bur Dubai</div>
                                    <div>sravan@gmail.com</div>
                                    <div>99999999999</div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="border-left: 1px solid #ddd">
                    <table>
                        <tr>
                            <td style="text-align: center">
                                <img
                                    style="
                    border-radius: 50%;

                    width: 20px;
                    max-width: 20px;
                  "
                                    src="https://alarmbackend.xtremeguard.org//google_map_icons/google_alarm.png?4=3"
                                    alt="Akil Security Logo" />
                                <div style="font-size: 10px">
                                    <div style="color: blue">Intruder,<span>Bed room</span></div>
                                    <div style="color: red">
                                        Vibration Sensor ,
                                        <div>Emergency zone</div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size: 10px; padding-left: 10px">
                                <table style="width: 100%; border-collapse: collapse">
                                    <tr style="border-bottom: 1px solid #ddd">
                                        <td>Event Id</td>
                                        <td>#1537</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #ddd">
                                        <td>Status</td>
                                        <td>Closed</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #ddd">
                                        <td>Category</td>
                                        <td>Critical</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #ddd">
                                        <td>Start</td>
                                        <td>Oct 23, 2024 12:31</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #ddd">
                                        <td>End</td>
                                        <td>Oct 23, 2024 12:30</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </header>
    <footer>
        Footer Content 3333

    </footer>
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
            <table 111111 cellpadding="0" cellspacing="0">
                <!-- Forward -->
                <tr 111111 cellpadding="0" cellspacing="0">
                    <td 111111 cellpadding="0" cellspacing="0">
                        <table cellpadding="0" cellspacing="0">
                            <td
                                style="
                      vertical-align: middle;
                      text-align: center;
                      font-size: 10px;width:180px
                    ">
                                Sep 28, 2024 16:50
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
                                            src="https://alarmbackend.xtremeguard.org/customers/contacts/1721749459.jpg" />
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
                                            Notes: Event Forwarded to primary@gmail.com
                                        </div>
                                    </div>
                                </div>

                            </td>
                        </table>
                    </td>
                </tr>
                <!-- Contact -->
                <tr style=" border-collapse: collapse;">
                    <td 111111>
                        <table cellpadding="0" cellspacing="0" style="height:140px">
                            <td
                                style="
                      vertical-align: middle;
                      text-align: center;
                      font-size: 10px;width:180px
                    ">
                                OCT 28, 2024 16:50
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
                                            src="https://alarmbackend.xtremeguard.org/customers/contacts/1721749459.jpg" />
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
                                            Secondary
                                        </div>
                                        <div style="padding-top: 10px; font-size: 10px">
                                            Notes: Secondary Secondary SecondarySecondary Secondary Notes: Secondary
                                        </div>
                                        <hr style="color:#ddd;margin-top:10px " />
                                        <table style=font-size:10px;>
                                            <tr>
                                                <td style="font-weight:bold;width:60px;">Call Status
                                                </td>
                                                <td>: Answered

                                                </td>
                                                <td style="font-weight:bold;width:70px;">Call Response

                                                </td>
                                                <td>: Answered

                                                </td>
                                                <td style="font-weight:bold;width:70px;">Event Status

                                                </td>
                                                <td>: Answered

                                                </td>
                                            </tr>
                                        </table>
                                        <div style="height:20px"> </div>
                                    </div>


                                </div>
                            </td>
                        </table>
                    </td>
                </tr>
                <!-- Unnown   -->
                <tr style=" border-collapse: collapse;">
                    <td 111111>
                        <table cellpadding="0" cellspacing="0" style="height:140px">
                            <td
                                style="
                      vertical-align: middle;
                      text-align: center;
                      font-size: 10px;width:180px
                    ">
                                OCT 28, 2024 16:50
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
                                            src="https://alarmbackend.xtremeguard.org/unknown-person-icon.png" />
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
                                            Secondary
                                        </div>
                                        <div style="padding-top: 10px; font-size: 10px">
                                            Notes: Secondary Secondary SecondarySecondary Secondary Notes: Secondary
                                        </div>
                                        <hr style="color:#ddd;margin-top:10px " />
                                        <table style=font-size:10px;>
                                            <tr>
                                                <td style="font-weight:bold;width:60px;">Call Status
                                                </td>
                                                <td>: Answered

                                                </td>
                                                <td style="font-weight:bold;width:70px;">Call Response

                                                </td>
                                                <td>: Answered

                                                </td>
                                                <td style="font-weight:bold;width:70px;">Event Status

                                                </td>
                                                <td>: Answered

                                                </td>
                                            </tr>
                                        </table>
                                        <div style="height:20px"> </div>
                                    </div>


                                </div>
                            </td>
                        </table>
                    </td>
                </tr>

                <!-- Closed Alarm  -->
                <tr style=" border-collapse: collapse;">
                    <td 111111>
                        <table cellpadding="0" cellspacing="0" style="height:150px">
                            <td
                                style="
                      vertical-align: middle;
                      text-align: center;
                      font-size: 10px;width:180px;color:red
                    ">
                                Nov 28, 2024 16:50
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
                                            Alarm Event Closed at Oct 23, 2024 12:17
                                        </div>
                                        <div style="padding-top: 10px; font-size: 10px">
                                            Auto Closed by Disarm Event

                                        </div>

                                        <div style="height:30px"> </div>
                                    </div>


                                </div>
                            </td>
                        </table>
                    </td>
                </tr>
            </table>

        </body>

        </html>

    </main>
    <script type="text/php">
        if (isset($pdf)) {
        $text = "Page Number {PAGE_NUM} / {PAGE_COUNT}";
        $size = 10;
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