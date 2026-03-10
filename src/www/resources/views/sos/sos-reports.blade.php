<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Parking Reports</title>

    <style>
        @page {
            margin: 20px 20px 20px 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #222;
        }

        .header {
            width: 100%;
            margin-bottom: 0px;
        }

        .header-left {
            float: left;
            width: 60%;
        }

        .header-right {
            float: right;
            width: 40%;
            text-align: right;
        }

        .company-name {
            font-weight: bold;
            font-size: 16px;
        }

        .report-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .clearfix {
            clear: both;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 4px;
            text-align: center;
            word-wrap: break-word;
        }

        th {
            background: #f2f2f2;
            font-weight: bold;
        }

        .badge {
            padding: 2px 5px;
            font-size: 9px;
            border-radius: 3px;
            display: inline-block;
        }

        .green {
            background: #d4edda;
            color: #155724;
            border: 1px solid #bcd0c7;
        }

        .red {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #e4b9be;
        }

        .outline-green {
            background: #fff;
            color: #155724;
            border: 1px solid #155724;
        }

        .photo img {
            max-width: 90px;
            max-height: 30px;
        }

        .footer {
            position: fixed;
            bottom: 5px;
            right: 0;
            font-size: 9px;
            text-align: right;
            color: #777;
        }
    </style>
</head>

<body>

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

        if (!function_exists('changeDateformat')) {
            function changeDateformat($date)
            {
                if ($date == '') {
                    return ['---', '---'];
                }
                $date = new DateTime($date);

                // Format the date to the desired format
                return [$date->format('M j, Y'), $date->format('H:i')];
            }
        }
        if (!function_exists('minutesToTime')) {
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
        }
    @endphp
    @php
        $title2 = '';
        // Check if the request has 'date_from' and 'date_to' parameters
        if ($request->date_from && $request->date_to) {
            // Assuming changeDateformat returns an array, so accessing the first element
            $fromDate = changeDateformat($request->date_from)[0];
            $toDate = changeDateformat($request->date_to)[0];
            $title2 = "From $fromDate to $toDate";
        }
    @endphp
    @php
        $title1 = 'SOS  Reports';

    @endphp
    <!-- Header -->
    <header>

        @include('header', [
            'company' => $company,
            'title1' => $title1,

            'title2' => $title2,
            'request' => $request,
        ])



    </header>

    <!-- Footer -->
    <footer>

        @include('footer', [
            'company' => $company,
        ])

    </footer>

    <main>

        <div class="clearfix"></div>


        <!-- TABLE -->
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Alarm Start</th>
                    <th>Alarm End</th>
                    <th>Location / Room</th>
                    <th>Room Type</th>
                    <th class="center">Response (HH:MM)</th>
                    <th>Status</th>
                    <th>ACK</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reports as $i => $r)
                    @php
                        $status = $r->sos_status ? ($r->responded_datetime ? 'ACKNOWLEDGED' : 'PENDING') : 'RESOLVED';

                        $statusClass =
                            $status === 'PENDING' ? 'pending' : ($status === 'ACKNOWLEDGED' ? 'ack' : 'resolved');

                        $respMin = $r->response_in_minutes;
                        $respClass = $respMin !== null && $respMin > 5 ? 'resp-warn' : 'resp-ok';

                        $roomType = $r->room?->room_type
                            ? strtoupper(str_replace('-PH', '', $r->room->room_type))
                            : '--';
                    @endphp

                    <tr>
                        <td>{{ $i + 1 }}</td>

                        <td>
                            @if ($r->alarm_start_datetime)
                                <div class="time">{{ \Carbon\Carbon::parse($r->alarm_start_datetime)->format('H:i') }}
                                </div>
                                <div class="date">
                                    {{ \Carbon\Carbon::parse($r->alarm_start_datetime)->format('M d, Y') }}
                                </div>
                            @else
                                --
                            @endif
                        </td>

                        <td>
                            @if ($r->alarm_end_datetime)
                                <div class="time">{{ \Carbon\Carbon::parse($r->alarm_end_datetime)->format('H:i') }}
                                </div>
                                <div class="date">
                                    {{ \Carbon\Carbon::parse($r->alarm_end_datetime)->format('M d, Y') }}
                                </div>
                            @else
                                --
                            @endif
                        </td>

                        <td>
                            <div class="room">{{ $r->room_name ?? '--' }}</div>
                            <div class="location">{{ $r->device?->location ?? '--' }}</div>
                        </td>

                        <td>{{ $roomType }}</td>

                        <td class="center">
                            @if ($respMin !== null)
                                <span class="resp {{ $respClass }}">
                                    {{ gmdate('H:i', $respMin * 60) }}
                                </span>
                            @else
                                --
                            @endif
                        </td>

                        <td>
                            <span class="chip {{ $statusClass }}">
                                {{ $status }}
                            </span>
                        </td>

                        <td>
                            @if ($r->responded_datetime)
                                <div class="time">{{ \Carbon\Carbon::parse($r->responded_datetime)->format('H:i') }}
                                </div>
                                <div class="date">
                                    {{ \Carbon\Carbon::parse($r->responded_datetime)->format('M d, Y') }}
                                </div>
                            @else
                                --
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </main>


</body>

</html>
