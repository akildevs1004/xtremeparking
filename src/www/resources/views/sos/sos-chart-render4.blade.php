<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Generating SOS Report</title>

    <style>
        body {
            margin: 0;
            font-family: Inter, Arial, sans-serif;
            background: #ffffff;
        }

        /* ================= LOADING OVERLAY ================= */
        #loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.94);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loading-box {
            text-align: center;
            color: #0f172a;
        }

        .loading-text {
            margin-top: 14px;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .spinner {
            width: 44px;
            height: 44px;
            border: 4px solid #e2e8f0;
            border-top-color: #137fec;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ================= CONTENT ================= */
        .content {
            padding: 20px;
        }
    </style>
</head>

<body>

    <!-- LOADING OVERLAY -->
    <div id="loading-overlay">
        <div class="loading-box">
            <div class="spinner"></div>
            <div class="loading-text">
                Generating SOS Analytics Report…<br>
                Please wait
            </div>
        </div>
    </div>

    <!-- PAGE CONTENT (charts load behind loader) -->
    <div class="content">

        <table style="width:1200px">
            <tr>
                <td>
                    @include('sos.sos-chart-render-sos-donut-stats')
                </td>
                <td>
                    @include('sos.sos-chart-render-sos-hourly-barchart')
                </td>
            </tr>

            <tr>
                <td>
                    @include('sos.sos-chart-render-sos-response-minutes')
                </td>
                <td>

                </td>
            </tr>
        </table>

    </div>



    <!-- REDIRECT SCRIPT -->
    <script>
        window.onload = function() {
            setTimeout(function() {
                const currentUrl = new URL(window.location.href);

                // Change only the path, keep ALL query params
                currentUrl.pathname = "/api/sos_analytics_pdf";

                window.location.href = currentUrl.toString();
            }, 3000);
        };
    </script>



</body>

</html>
