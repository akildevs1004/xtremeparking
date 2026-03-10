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
    <header style="min-height:100px">
        <h1>Report11</h1>
    </header>
    <footer>
        Footer Content 3333

    </footer>
    <main>
        <table width="100%" border="1" cellspacing="0" cellpadding="5">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <!-- <th>Date</th> -->
                    <!-- Add more table headers as needed -->
                </tr>
            </thead>
            <tbody>
                @foreach ($reports as $report)
                <tr>
                    <td>{{ $report->id }}</td>
                    <td>{{ $report->LogTime }}</td>
                    <!-- Add more table data as needed -->
                </tr>
                @endforeach
            </tbody>
        </table>
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


</body>

</html>