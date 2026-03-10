{{-- resources/views/sos/report/page2.blade.php --}}

<style>
    /* ======================================================
   PAGE 2 – SCOPED CSS (NO CONFLICT WITH OTHER PAGES)
====================================================== */

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

    /* .page-2 {
        page-break-after: always;
        padding-bottom: 18mm;
        font-size: 12px;
    } */

    /* HEADER */
    .page-2 .header {
        width: 100%;
        margin-bottom: 16px;
        border-bottom: 2px solid #137fec;
    }

    .page-2 .header-left {
        padding-bottom: 12px;
    }

    .page-2 .header-right {
        text-align: right;
        padding-bottom: 12px;
    }

    .page-2 .title {
        font-size: 18px;
        /* font-weight: 800; */
    }

    .page-2 .subtitle {
        font-size: 11px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #64748b;
    }

    .page-2 .report-title {
        font-size: 16px;
        /* font-weight: 800; */
    }

    /* STATS CARDS */
    .page-2 .stats {
        width: 100%;
        margin-bottom: 24px;
    }

    .page-2 .stats td {
        width: 25%;
        padding-right: 10px;
        vertical-align: top;
    }

    .page-2 .stats td:last-child {
        padding-right: 0;
    }

    .page-2 .card {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px;
        background: #ffffff;
    }

    .page-2 .card-title {
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 6px;
    }

    .page-2 .big {
        font-size: 22px;
        font-weight: 900;
        margin: 0;
    }

    .page-2 .progress {
        height: 8px;
        background: #f1f5f9;
        border-radius: 999px;
        overflow: hidden;
        margin-top: 10px;
    }

    .page-2 .progress>div {
        height: 8px;
        background: #22c55e;
    }

    .page-2 .location {
        font-size: 16px;
        /* font-weight: 800; */
        margin: 0;
    }

    /* SECTION TITLE */
    .page-2 .section-title {
        font-size: 20px;
        font-weight: 900;
        margin-bottom: 10px;
        padding-bottom: 8px;
        border-bottom: 2px solid #c8cfd8;
    }

    /* CHART GRID */
    .page-2 .chart-grid {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0px;
    }

    .page-2 .chart-grid td {
        width: 50%;
        vertical-align: top;
    }

    .page-2 .chart-card {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px;
        background: #ffffff;
    }

    .page-2 .chart-title {
        font-size: 12px;
        font-weight: 900;
        margin-bottom: 10px;
    }

    .page-2 .chart-img {
        display: block;
        width: 100%;
        height: 210px;
        object-fit: contain;
    }

    .page-2 .note {
        margin-top: 8px;
        font-size: 11px;
        color: #64748b;
    }

    .page-2 {
        position: relative;
        padding-bottom: 22mm;
        /* reserve space for footer */
    }

    /* PAGE 2 FOOTER (only on page-2) */
    .page-2 .page-2-footer {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        /* stick to bottom of page-2 */
        font-size: 10px;
        color: #94a3b8;
    }

    .page-2 .page-2-footer table {
        width: 100%;
        border-collapse: collapse;
    }

    .page-2 .page-2-footer td {
        border: 0;
        padding: 0;
    }
</style>

<div class="page page-2" id="page-2">

    {{-- HEADER --}}
    <table class="header">
        <tr>
            <td class="header-left">
                @if (env('APP_ENV') !== 'local')
                    <img src="{{ $company->logo }}" style="width:70px; max-width:70px;">
                @else
                    <img src="{{ getcwd() . '/' . $company->logo_raw }}" style="width:70px; max-width:70px;">
                @endif

                <div class="title"> {{ $company->name }}</div>
                <div class="subtitle">Intelligent Nurse Call System</div>
            </td>
            <td class="header-right">
                <div class="report-title">SOS Call Report</div>
                <div class="subtitle" style="letter-spacing:normal;text-transform:none;">
                    Detailed Analytics
                </div>
            </td>
        </tr>
    </table>

    {{-- STATS --}}
    <table class="stats">
        <tr>
            <td>
                <div class="card">
                    <div class="card-title">Total Calls</div>
                    <p class="big">{{ $totalCalls }}</p>
                    <div class="note">
                        &nbsp;
                    </div>
                </div>

            </td>

            <td>
                <div class="card">
                    <div class="card-title">Avg Response</div>
                    <p class="big">{{ $avgResponse }}</p>
                    <div class="note">
                        &nbsp;

                    </div>
                </div>
            </td>

            <td>
                <div class="card">
                    <div class="card-title">Resolved</div>
                    <p class="big">{{ $resolvedPct }}</p>
                    <div class="progress">
                        <div style="width: {{ rtrim($resolvedPct, '%') }}%;"></div>
                    </div>
                </div>
            </td>

            <td>
                <div class="card">
                    <div class="card-title">Top Location</div>
                    <p class="location">{{ $topLocation }}</p>
                    <div class="note">{{ $topLocationCalls }} calls</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- CHARTS --}}
    <div class="section-title">Data Visualization</div>

    <table class="chart-grid" style="padding:0px">
        <tr style="padding:0px">
            <td style="padding:5px">
                <div class="chart-card">
                    <div class="chart-title">Hourly SOS Calls</div>
                    <img src="{{ $chartHourlySos }}" class="chart-img">
                </div>
            </td>

            <td style="padding:5px">
                <div class="chart-card">
                    <div class="chart-title">Response / Acknowledgement</div>
                    <img src="{{ $chartResponseHourly }}" class="chart-img">
                </div>
            </td>
        </tr>

        <tr>
            <td style="padding:5px">
                <div class="chart-card">
                    <div class="chart-title">SOS Status Breakdown</div>
                    <img src="{{ $chartStatusDonut }}" class="chart-img">
                </div>
            </td>

            <td style="padding:5px">
                <div class="chart-card " style="height:240px!important">
                    <div class="chart-title">SOS Rooms / Sources</div>
                    @include('sos.sos-chart-render-sos-rooms-type')
                </div>
            </td>
        </tr>
    </table>


</div>
