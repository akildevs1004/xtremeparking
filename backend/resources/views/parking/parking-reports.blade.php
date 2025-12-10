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
        $title1 = 'Parking Reports';

    @endphp
    <!-- Header -->
    <header>

        @include('parking.header', [
            'company' => $company,
            'title1' => $title1,
        
            'title2' => $title2,
            'request' => $request,
        ])



    </header>

    <!-- Footer -->
    <footer>

        @include('parking.footer', [
            'company' => $company,
        ])

    </footer>
    <main>

        <div class="clearfix"></div>

        {{-- Filters --}}
        @if ($request)
            <div style="font-size: 10px; margin-top: 0px;">
                @if ($request->date_from || $request->date_to)
                    <strong>Date:</strong> {{ $request->date_from }} - {{ $request->date_to }} &nbsp;
                @endif

                @if ($request->filter_payment && $request->filter_payment != 'All')
                    <strong>Payment:</strong> {{ $request->filter_payment }} &nbsp;
                @endif

                @if ($request->common_search)
                    <strong>Search:</strong> {{ $request->common_search }} &nbsp;
                @endif
            </div>
        @endif

        {{-- MAIN TABLE --}}
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Vehicle</th>
                    <th>Entry Time</th>
                    <th>Exit Time</th>
                    <th>Hours</th>
                    <th>State</th>
                    <th>Amount</th>
                    <th>Member</th>
                    <th>Payment</th>

                </tr>
            </thead>

            <tbody>
                @php $i = 1; @endphp

                @forelse($reports as $log)
                    @php
                        // Member chip logic
                        $isMember = !empty($log['membership_id']);
                        $guestVehicle = !empty($log['member_guest_vehicle_id']);
                        $memberType = $log['parking_members']['member_type'] ?? 'Member';

                        $memberLabel = $isMember ? ucfirst($memberType) . ($guestVehicle ? ' - Guest' : '') : 'GUEST';

                        $memberClass = $isMember ? 'green' : 'red';

                        // Payment status logic
                        $paymentMode = $log['payment_mode'] ?? null;
                        $hasExit = !empty($log['out_time']);

                        if ($paymentMode && $hasExit) {
                            $statusText = ucfirst($paymentMode);
                            $statusClass = 'green';
                        } elseif ($hasExit && $isMember) {
                            $statusText = 'Completed';
                            $statusClass = 'outline-green';
                        } else {
                            $statusText = '---';
                            $statusClass = '';
                        }

                        // Photo Logic
                        $photo = null;

                    @endphp

                    <tr>
                        <td>{{ $i++ }}</td>

                        <td>{{ $log['log_vehicle_number'] ?? '---' }}</td>

                        <td>
                            {{ $log['in_time'] ? \Carbon\Carbon::parse($log['in_time'])->format('d-M-Y H:i') : '---' }}
                        </td>

                        <td>
                            {{ $log['out_time'] ? \Carbon\Carbon::parse($log['out_time'])->format('d-M-Y H:i') : '---' }}
                        </td>

                        <td>{{ $log['duration_in_hours'] ?? '---' }}</td>

                        <td>{{ $log['raw_country_region'] ?? '---' }}</td>

                        <td>{{ $log['total_amount'] ? number_format($log['total_amount'], 2) : '---' }}</td>

                        <td>
                            <span class="badge {{ $memberClass }}">{{ $memberLabel }}</span>
                        </td>

                        <td>
                            @if ($statusText !== '---')
                                <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                            @else
                                ---
                            @endif
                        </td>


                    </tr>

                @empty
                    <tr>
                        <td colspan="10">No data found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </main>

</body>

</html>
