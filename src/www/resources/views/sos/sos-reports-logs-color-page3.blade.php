{{-- resources/views/sos/report/page3.blade.php --}}

<style>
    /* ======================================================
   PAGE 3 ONLY (CALL LOGS TABLE) — SCOPED CSS
====================================================== */
    /* .page-3 {
        position: relative;

        page-break-after: always;
        padding-bottom: 18mm;

    } */

    /* Header area (your page3 header table) */
    .page-3 .header {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
        border-bottom: 1px solid #e2e8f0;
    }

    .page-3 .header-left {
        padding-bottom: 12px;
    }

    .page-3 .header-right {
        text-align: right;
        padding-bottom: 12px;
    }

    .page-3 .title {
        font-size: 18px;
        font-weight: 800;
        margin-top: 4px;
    }

    .page-3 .subtitle {
        font-size: 11px;
        color: #64748b;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .page-3 .report-title {
        font-size: 16px;
        font-weight: 800;
    }

    .page-3 .h2 {
        font-size: 16px;
        font-weight: 900;
        margin: 12px 0 12px 0;
        color: #0f172a;
    }

    /* ===== Clean table like screenshot ===== */
    .page-3 .report-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
    }

    .page-3 .report-table thead th {
        text-align: left;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: #64748b;
        padding: 10px 10px;
        border-bottom: 2px solid #e2e8f0;
        background: #ffffff;
    }

    .page-3 .report-table tbody td {
        padding: 12px 10px;
        border-bottom: 1px solid #eef2f7;
        vertical-align: middle;
    }

    .page-3 .report-table tbody tr:last-child td {
        border-bottom: 0;
    }

    /* alternate subtle rows */
    .page-3 .report-table tbody tr:nth-child(even) td {
        background: #f8fafc;
    }

    /* Column sizing similar to screenshot */
    .page-3 .col-id {
        font-size: 10px;
        color: #334155;
        white-space: nowrap;
        width: 44px;
    }

    .page-3 .col-time {
        white-space: nowrap;
        color: #0f172a;
        width: 135px;
    }

    .page-3 .col-duration {
        white-space: nowrap;
        color: #334155;
        width: 80px;
    }

    .page-3 .col-status {
        white-space: nowrap;
        width: 95px;
    }

    .page-3 .location-strong {
        font-weight: 800;
        color: #0f172a;
    }

    .page-3 .location-muted {
        display: block;
        margin-top: 2px;
        font-size: 10px;
        color: #64748b;
    }

    /* Pills */
    .page-3 .pill {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 7px;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
        border: 1px solid transparent;
    }

    .page-3 .pill-pending {
        background: #fee2e2;
        color: #b91c1c;
        border-color: #fecaca;
    }

    .page-3 .pill-ack {
        background: #fef3c7;
        color: #92400e;
        border-color: #fde68a;
    }

    .page-3 .pill-resolved {
        background: #d1fae5;
        color: #047857;
        border-color: #a7f3d0;
    }

    /* Duration highlight */
    .page-3 .duration-critical {
        color: #dc2626;
        font-weight: 900;
    }

    /* Unassigned style */
    .page-3 .staff-unassigned {
        color: #94a3b8;
        font-style: italic;
    }

    /* Footer (page-3 only) — always visible */
    .page-3 .page-3-footer {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        font-size: 10px;
        color: #94a3b8;
    }

    .page-3 .page-3-footer table {
        width: 100%;
        border-collapse: collapse;
    }

    .page-3 .page-3-footer td {
        border: 0;
        padding: 0;
    }
</style>

<div class="page page-3" id="page-3">

    @php
        // ===== Optional helper functions (kept minimal) =====
        if (!function_exists('fmtTime')) {
            function fmtTime($dt)
            {
                if (!$dt) {
                    return '--';
                }
                try {
                    return \Carbon\Carbon::parse($dt)->format('h:i A d-m-Y');
                } catch (\Throwable $e) {
                    return '--';
                }
            }
        }

        if (!function_exists('fmtDuration')) {
            // NOTE: this expects seconds
            function fmtDuration($seconds)
            {
                if ($seconds === null || $seconds === '') {
                    return '--';
                }
                $seconds = (int) $seconds;

                // If you want 45s style: return seconds + "s"
                // return $seconds . 's';

                // Current format:
                if ($seconds < 60) {
                    return $seconds . 's';
                }
                $m = intdiv($seconds, 60);
                $s = $seconds % 60;
                return $m . 'm ' . $s . 's';
            }
        }
    @endphp

    <main>
        {{-- HEADER --}}
        <table class="header">
            <tr>
                <td class="header-left">
                    @if (env('APP_ENV') !== 'local')
                        <img src="{{ $company->logo }}" style="width:70px; max-width:70px;">
                    @else
                        <img src="{{ getcwd() . '/' . $company->logo_raw }}" style="width:70px; max-width:70px;">
                    @endif

                    <div class="title">{{ $company->name }}</div>
                    <div class="subtitle">Intelligent Nurse Call System</div>
                </td>

                <td class="header-right">
                    <div class="report-title">SOS Call Report</div>
                    <div class="subtitle" style="letter-spacing:normal;text-transform:none;color:#64748b;">
                        Detailed Analytics
                    </div>
                </td>
            </tr>
        </table>

        <div class="h2">SOS Call Logs</div>

        {{-- TABLE --}}
        <table class="report-table">
            <thead>
                <tr>
                    <th class="col-id">#</th>
                    <th>Location</th>
                    <th class="col-time">Time</th>
                    <th class="col-duration">Duration</th>
                    <th class="col-status">Status</th>
                    <th class="col-status">Acknowledgement</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($reports as $index => $r)
                    @php
                        $room = $r->room_name ?? ($r->room?->room_name ?? '--');
                        $loc = $r->device?->location ?? ($r->location ?? '');

                        $time = fmtTime($r->alarm_start_datetime ?? null);

                        $durationSeconds = null;
                        if (isset($r->duration_seconds)) {
                            $durationSeconds = (int) $r->duration_seconds;
                        } elseif (!empty($r->alarm_start_datetime) && !empty($r->alarm_end_datetime)) {
                            try {
                                $durationSeconds = \Carbon\Carbon::parse($r->alarm_start_datetime)->diffInSeconds(
                                    \Carbon\Carbon::parse($r->alarm_end_datetime),
                                );
                            } catch (\Throwable $e) {
                                $durationSeconds = null;
                            }
                        }

                        // Status mapping (as you did)
                        $status = $r->sos_status ? ($r->responded_datetime ? 'ACK.' : 'PENDING') : 'RESOLVED';

                        $pillClass =
                            $status === 'PENDING'
                                ? 'pill-pending'
                                : ($status === 'ACK.'
                                    ? 'pill-ack'
                                    : 'pill-resolved');

                        $ackstatus = $r->responded_datetime ? 'ACK.' : '';

                        // Highlight rule: < 60s = critical (45s etc.)
                        $durationClass = $durationSeconds !== null && $durationSeconds < 60 ? 'duration-critical' : '';
                    @endphp

                    <tr>
                        <td class="col-id"><strong>#{{ $index + 1 }}</strong></td>

                        <td>
                            <span class="location-strong">{{ $room }}</span>
                            @if (!empty($loc))
                                <span class="location-muted">{{ $loc }}</span>
                            @endif
                        </td>

                        <td class="col-time">{{ $time }}</td>

                        <td class="col-duration">
                            <span class="{{ $durationClass }}">{{ fmtDuration($durationSeconds) }}</span>
                        </td>

                        <td class="col-status">
                            <span class="pill {{ $pillClass }}">{{ $status }}</span>
                        </td>

                        <td class="col-status">
                            @if ($ackstatus)
                                <span class="pill pill-ack">{{ $ackstatus }}</span>
                            @else
                                --
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </main>

    {{-- FOOTER (page-3 only) --}}
    <div class="page-3-footer">
        <table>
            <tr>
                <td>© {{ date('Y') }} </td>
                <td style="text-align:right;"> </td>
            </tr>
        </table>
    </div>

</div>
