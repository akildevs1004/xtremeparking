<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Invoice</title>
    <style>
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



    @php

    @endphp
    <!-- Header -->
    <header>

        @include('alarm_reports.header_invoice', [
            'company' => $company,
            'invoice' => $invoice,
        ])



    </header>

    <!-- Footer -->
    <footer>

        @include('alarm_reports.footer', [
            'company' => $company,
        ])

    </footer>
    <main>
        <table style="width:100%;font-size:12px">



            <tr>
                <td>
                    <table class="table-border-top" style="width:100%">
                        <tr>
                            <td colspan="5" style="background-color:#8f8f8f;height:20px;color:#FFF;font-size:16px">
                                Bill To: </td>
                        </tr>
                        <tr style="border: 1px solid #8f8f8f;border-top:0px">


                            <td> {{ $customer['building_name'] }}</td>




                        </tr>
                        <tr style="border: 1px solid #8f8f8f;">


                            <td> <span>{{ $customer->house_number ?? '---' }},
                                    {{ $customer->street_number ?? '---' }}</span>
                                <span>{{ $customer->area ?? '---' }},<br />
                                    {{ $customer->city ?? '---' }} </span>
                                <span>{{ $customer->state ?? '---' }},
                                    {{ $customer->country ?? '---' }} </span>
                            </td>


                        </tr>

                        <tr style="border: 1px solid #8f8f8f;">



                            <td>{{ $customer?->primary_contact?->first_name ?? '---' }}
                                {{ $customer?->primary_contact?->last_name ?? '---' }} </td>


                        </tr>
                        <tr style="border: 1px solid #8f8f8f;">



                            <td>Phone: {{ $customer?->primary_contact?->phone1 ?? '---' }}</td>


                        </tr>
                        <tr style="border: 1px solid #8f8f8f;">




                            <td>Email: {{ $customer?->primary_contact?->email ?? '---' }}</td>

                        </tr>
                    </table>
                </td>
            </tr>
            <tr>




                <td style="  ">

                    <table class="pdf_table"
                        style="height: 90px; width: 100%; border-collapse: collapse; border-style: solid;"
                        border="1">
                        <tbody>
                            <tr style="height: 25px;">
                                <td style="width: 50px; height: 25px;text-align:center;padding:10px">#</td>
                                <td style="width: 50%; height: 25px;padding:10px">Item and Description</td>
                                <td style="width: 25%; height: 25px;text-align:center;padding:10px;">Qty</td>
                                <td style="width: 25%; height: 25px;text-align:right;padding:10px;">Price</td>
                                <td style="width: 25%; height: 25px;text-align:right;padding:10px;">Total</td>

                            </tr>
                            <tr style=" vertical-align: top;">
                                <td style="width: 50%; height: 400px;text-align:center;padding:10px;">1</td>
                                <td style="width: 50%; height: 200px;padding:10px; ">
                                    Package:
                                    <strong>{{ $invoice->customer_product_services?->device_product_service?->name }}
                                    </strong>
                                    <p>Payment Mode: <strong> {{ $invoice->customer_product_services?->payment_type }}
                                        </strong>
                                    </p>
                                    <p>Sensor Count:
                                        <strong>
                                            {{ $invoice->customer_product_services?->device_product_service?->sensor_count }}
                                        </strong>
                                    </p>
                                    <p>Time Period:
                                        <strong> {{ date('d-m-Y', strtotime($invoice->invoice_date)) }} to

                                            @if ($invoice->customer_product_services?->payment_type == 'Monthly')
                                                {{ date('d-m-Y', strtotime($invoice->invoice_date . ' +30 days')) }}
                                            @elseif ($invoice->customer_product_services?->payment_type == 'Quarter')
                                                {{ date('d-m-Y', strtotime($invoice->invoice_date . ' +90 days')) }}
                                            @elseif ($invoice->customer_product_services?->payment_type == 'Yearly')
                                                {{ date('d-m-Y', strtotime($invoice->invoice_date . ' +365 days')) }}
                                            @endif

                                        </strong>

                                    </p>

                                </td>
                                <td style="width: 25%; height: 200px;text-align:center;padding:10px;">1</td>
                                <td style="width: 40%; height: 200px;text-align:right;padding:10px;">
                                    {{ $company->currency ?? '$' }}
                                    {{ number_format(round($invoice->amount + $invoice->customer_product_services->discount), 2, '.', '') }}
                                </td>
                                <td style="width: 40%; height: 200px;text-align:right;padding:10px;">
                                    {{ $company->currency ?? '$' }}
                                    {{ number_format(round($invoice->amount + $invoice->customer_product_services->discount), 2, '.', '') }}
                                </td>

                            </tr>
                            @if ($invoice->customer_product_services->discount > 0)
                                <tr style="height: 25px;; ">
                                    <td style="width: 50%; height: 25px;"> </td>
                                    <td style="width: 50%; height: 25px;"> </td>
                                    <td style="width: 25%; height: 25px;"> </td>
                                    <td style="width: 25%; height: 25px;text-align:center;color:red"> Discount </td>
                                    <td style="width: 25%; height: 25px;text-align:right;padding:10px;;color:red">
                                        - {{ $company->currency ?? '$' }}
                                        {{ number_format(round($invoice->customer_product_services->discount), 2, '.', '') }}
                                    </td>

                                </tr>
                            @endif
                            @if ($invoice->customer_product_services->tax_amount > 0)
                                <tr style="height: 25px;; ">
                                    <td style="width: 50%; height: 25px;"> </td>
                                    <td style="width: 50%; height: 25px;"> </td>
                                    <td style="width: 25%; height: 25px;"> </td>
                                    <td style="width: 25%; height: 25px;text-align:center; "> Tax
                                        ({{ $invoice->customer_product_services->tax_percentage }}%) </td>
                                    <td style="width: 25%; height: 25px;text-align:right;padding:10px; ">
                                        {{ $company->currency ?? '$' }}
                                        {{ $invoice->customer_product_services->tax_amount }}
                                    </td>

                                </tr>
                            @endif
                            <tr style="height: 25px;;font-weight:bold">
                                <td style="width: 50%; height: 25px;"> </td>
                                <td style="width: 50%; height: 25px;"> </td>
                                <td style="width: 25%; height: 25px;"> </td>
                                <td style="width: 25%; height: 25px;text-align:center"> Total </td>
                                <td style="width: 25%; height: 25px;text-align:right;padding:10px;">
                                    {{ $company->currency ?? '$' }}
                                    {{ number_format(round($invoice->amount + $invoice->tax_amount, 2), 2, '.', '') }}
                                </td>

                            </tr>
                        </tbody>
                    </table>
                </td>

            </tr>

            <tr>

                <td style="">
                    Note:Conputer Generated Invoice. Feel free to contact Manager if any corrections.
                </td>
            </tr>


        </table>

    </main>


    <script type="text/php">
        if (isset($pdf)) {
        $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $font = $fontMetrics->getFont("Verdana");
        $size = 6;
        $x = $pdf->get_width() - 50;
        $y = $pdf->get_height() - 30;
        $pdf->page_text($x, $y, $text, $font, $size);
    }
    </script>
    @php

        function changeDateformat($date)
        {
            if ($date == '') {
                return ['---', '---'];
            }
            $date = new DateTime($date);

            // Format the date to the desired format
            return [$date->format('M j, Y'), $date->format('H:i')];
        }
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
    @endphp

</body>

</html>
