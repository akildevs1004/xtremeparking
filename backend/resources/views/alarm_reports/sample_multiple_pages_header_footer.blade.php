<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        @page {
            margin: 100px 25px;
            /* Top, Bottom, Left, Right */
        }

        /* Footer with Page Number */
        @page {
            @bottom-right {
                content: "Page " counter(page) " of {PAGE_COUNT} " counter(pages);
            }
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
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header>
        Company Name - Report Title
    </header>

    <!-- Footer -->
    <footer>
        Company Name - Report Title
        <script type="text/php">
            if (isset($pdf)) {
        $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $font = $fontMetrics->getFont("Verdana");
        $size = 8;
        $x = $pdf->get_width() - 50; 
        $y = $pdf->get_height() - 30;

        
        $pdf->page_text($x, $y, $text, $font, $size);
    }
</script>
    </footer>

    <!-- Page 1 Content -->
    <div>
        <h2>Page 1 Content</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit...</p>
    </div>

    <div style="page-break-before: always;"></div>

    <!-- Page 2 Content -->
    <div>
        <h2>Page 2 Content</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit...</p>
    </div>

    <div style="page-break-before: always;"></div>

    <!-- Page 3 Content -->
    <div>
        <h2>Page 3 Content</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit...</p>
    </div>

    <div style="page-break-before: always;"></div>

    <!-- Page 4 Content -->
    <div>
        <h2>Page 4 Content</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit...</p>
    </div>

</body>

</html>