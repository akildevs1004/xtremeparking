<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>SOS Call Report</title>

    <style>
        @page {
            margin: 14mm;
        }

        /* =========================================
           LOCAL INTER (wkhtmltopdf-safe)
           IMPORTANT: use file:// + public_path()
        ========================================= */
        @font-face {
            font-family: "Inter";
            src: url("file://{{ str_replace('\\', '/', public_path('assets/fonts/inter/extras/ttf/Inter-Regular.ttf')) }}") format("truetype");
            font-weight: 400;
            font-style: normal;
        }

        @font-face {
            font-family: "Inter";
            src: url("file://{{ str_replace('\\', '/', public_path('assets/fonts/inter/extras/ttf/Inter-Medium.ttf')) }}") format("truetype");
            font-weight: 500;
            font-style: normal;
        }

        @font-face {
            font-family: "Inter";
            src: url("file://{{ str_replace('\\', '/', public_path('assets/fonts/inter/extras/ttf/Inter-SemiBold.ttf')) }}") format("truetype");
            font-weight: 600;
            font-style: normal;
        }

        @font-face {
            font-family: "Inter";
            src: url("file://{{ str_replace('\\', '/', public_path('assets/fonts/inter/extras/ttf/Inter-Bold.ttf')) }}") format("truetype");
            font-weight: 700;
            font-style: normal;
        }

        @font-face {
            font-family: "Inter";
            src: url("file://{{ str_replace('\\', '/', public_path('assets/fonts/inter/extras/ttf/Inter-Black.ttf')) }}") format("truetype");
            font-weight: 900;
            font-style: normal;
        }

        /* Global font: Inter if available, otherwise system sans-serif */
        body {
            font-family:
                "Inter",
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                Helvetica,
                Arial,
                sans-serif;
            font-size: 12px;
            color: #0f172a;
        }


        td {
            font-family: "Inter" !important;
        }

        /* ===============================
           PAGE
        =============================== */
        .page {
            page-break-after: always;
            padding-bottom: 18mm;
        }

        .page:last-child {
            page-break-after: auto;
        }

        /* ===============================
           COVER LAYOUT
        =============================== */
        .cover-wrap {
            margin-top: 8px;
        }

        .cover-title-row {
            width: 100%;
            border-collapse: collapse;
        }

        .cover-title-left {
            width: 18px;
            vertical-align: top;
        }

        .cover-title-right {
            vertical-align: top;
        }

        .cover-accent {
            width: 4px;
            height: 250px;
            background: #137fec;
            border-radius: 2px;
            margin-top: 6px;
        }

        .cover-h1 {
            font-size: 65px;
            font-weight: 900;
            line-height: 1.05;
            margin: 0;
        }

        .cover-unit {
            font-size: 30px;
            margin-top: 12px;
            color: #64748b;
            font-weight: 600;
        }

        /* ===============================
           META
        =============================== */
        .cover-meta {
            margin-top: 28px;
            width: 100%;
            border-collapse: collapse;
        }

        .cover-meta td {
            padding: 10px 0;
        }

        .label {
            width: 250px;
            font-size: 18px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .value {
            font-size: 22px;
            /* font-weight: 800; */
        }

        /* ===============================
           EXECUTIVE SUMMARY
        =============================== */
        .exec-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            margin-top: 30px;
        }

        .exec-head {
            font-size: 14px;
            font-weight: 900;
            margin-bottom: 12px;
        }

        .exec-head {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .exec-head img {
            width: 18px;
            height: 18px;
            margin-top: 10px;
        }

        .mini-icon {
            display: inline-block;
            width: 18px;
            height: 18px;
            background: #e8f1ff;
            border: 1px solid #cfe0ff;
            border-radius: 4px;
            margin-right: 8px;
            position: relative;
            vertical-align: -3px;
        }

        .mini-icon:after {
            content: "";
            width: 6px;
            height: 6px;
            background: #137fec;
            position: absolute;
            top: 5px;
            left: 5px;
            border-radius: 2px;
        }

        .exec-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .exec-left {
            width: 56%;
            padding-right: 14px;
        }

        .exec-text {
            font-size: 12px;
            line-height: 1.55;
            color: #475569;
        }

        .exec-bullets {
            list-style: none;
            padding: 0;
            margin: 10px 0 0 0;
        }

        .exec-bullets li {
            margin: 6px 0;
            font-size: 12px;
            color: #475569;
        }

        .dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .dot-green {
            background: #16a34a;
        }

        .dot-amber {
            background: #f59e0b;
        }

        /* ===============================
           METRICS
        =============================== */
        .metrics-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px;
        }

        .metrics-title {
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 8px;
        }

        .metrics-table {
            width: 100%;
            border-collapse: collapse;
        }

        .metrics-table td {
            padding: 10px 0;
        }

        .k {
            color: #475569;
        }

        .v {
            text-align: right;
            font-weight: 900;
        }

        .metrics-sep td {
            height: 1px;
            background: #e2e8f0;
            padding: 0;
        }
    </style>
</head>

<body>
    @php
        $icon1 = 'file://' . public_path('pdf_icons/pdfreporticon1.png');
        $icon2 = 'file://' . public_path('pdf_icons/pdfreporticon2.png');
        $unitName = $unitName ?? 'Cardiology Unit A';
        $reportPeriod = $reportPeriod ?? '-';
        $generatedOn = $generatedOn ?? '-';
        $preparedBy = $preparedBy ?? '-';

        $totalCalls = $totalCalls ?? 0;
        $avgResponse = $avgResponse ?? '00:00';
        $resolvedPct = $resolvedPct ?? '0%';
        $topLocation = $topLocation ?? '-';
        $topLocationCalls = $topLocationCalls ?? 0;

        $maxHour = $maxHour ?? '-';
        $summaryLine1 = $summaryLine1 ?? ' ' . $resolvedPct;
        $summaryLine2 = $summaryLine2 ?? ' ' . $maxHour . ' ';
    @endphp

    {{-- PAGE 1 --}}
    <div class="page">
        <div class="cover-wrap">

            <img src="{{ $icon1 }}" class="chart-img">

            <br />
            <br />
            <br />

            <table class="cover-title-row">
                <tr>
                    <td class="cover-title-left">
                        <div class="cover-accent"></div>
                    </td>
                    <td class="cover-title-right" style="height:100px;">

                        {{-- No inline font needed --}}
                        <p class="cover-h1">SOS Call<br>Report</p>
                        <p class="cover-unit">{{ $unitName }}</p>

                    </td>
                </tr>
            </table>

            <table class="cover-meta">
                <tr>
                    <td class="label">Report Period</td>
                    <td class="value">{{ $reportPeriod }}</td>
                </tr>
                <tr>
                    <td class="label">Generated On</td>
                    <td class="value">{{ $generatedOn }}</td>
                </tr>
                <tr>
                    <td class="label">Prepared By</td>
                    <td class="value">{{ $preparedBy }}</td>
                </tr>
            </table>

            <div class="exec-card">


                <div class="exec-head" style="display:flex; align-items:flex-center; gap:10px;">
                    <img src="{{ $icon2 }}" alt=""
                        style="width:18px; height:18px; margin-top:10px; display:block;" />
                    <span>Executive Summary</span>
                </div>



                <table class="exec-grid">
                    <tr>
                        <td class="exec-left">
                            <p class="exec-text">
                                During the report period, a total of <strong>{{ $totalCalls }}</strong> SOS calls
                                were
                                registered,
                                achieving an average response time of <strong>{{ $avgResponse }}</strong>.
                                Highest activity was recorded at <strong>{{ $topLocation }}</strong>
                                ({{ $topLocationCalls }} calls).
                            </p>

                            <ul class="exec-bullets">
                                <li><span class="dot dot-green"></span>Resolution rate achieved:
                                    <strong>{{ $summaryLine1 }}</strong>








                                </li>
                                <li><span class="dot dot-amber"></span>Peak activity observed:
                                    <strong>{{ $summaryLine2 }}</strong>
                                </li>
                            </ul>
                        </td>

                        <td style="width:44%;">
                            <div class="metrics-box">
                                <div class="metrics-title">Key Metrics at a Glance</div>
                                <table class="metrics-table">
                                    <tr>
                                        <td class="k">Total Calls</td>
                                        <td class="v">{{ $totalCalls }}</td>
                                    </tr>
                                    <tr class="metrics-sep">
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr>
                                        <td class="k">Avg Response</td>
                                        <td class="v">{{ $avgResponse }}</td>
                                    </tr>
                                    <tr class="metrics-sep">
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr>
                                        <td class="k">Top Location</td>
                                        <td class="v">{{ $topLocation }}</td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

        </div>
    </div>

</body>

</html>
