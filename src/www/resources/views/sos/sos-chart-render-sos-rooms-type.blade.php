@php
    /**
     * Expected:
     * $items = [
     *   ['label' => 'Room', 'count' => 0, 'percentage' => 0.0, 'color' => '#3b82f6'],
     * ];
     */
    $title = $title ?? 'SOS Rooms / Sources';
    $items = $items ?? [];
@endphp

<style>
    .rs-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 14px 14px 10px 14px;
        color: #000000;
        max-width: 820px;
    }

    .rs-title {
        font-weight: 700;
        font-size: 14px;
        margin: 0 0 12px 0;
    }

    .rs-item {
        padding: 10px 0 12px 0;
        border-top: 1px solid #e5e7eb;
    }

    .rs-item:first-of-type {
        border-top: 0;
        padding-top: 0;
    }

    .rs-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 6px;
    }

    .rs-left {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    .rs-label {
        font-weight: 600;
        font-size: 13px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .rs-right {
        font-weight: 600;
        font-size: 12px;
        color: #555555;
        display: flex;

    }

    .rs-dot {
        width: 10px;
        height: 10px;
        border-radius: 3px;
        flex: 0 0 auto;
    }

    .rs-bar {
        width: 100%;
        height: 10px;
        border-radius: 999px;
        background: #eeeeee;
        overflow: hidden;
    }

    .rs-fill {
        height: 100%;
        border-radius: 999px;
        width: 0%;
    }

    .rs-empty {
        font-size: 12px;
        color: #777777;
        padding: 6px 0;
    }
</style>


@if (empty($roomTypeItems['items']))
    <div class="rs-empty">No data available</div>
@else
    @foreach ($roomTypeItems['items'] as $index => $it)
        @php
            $label = $it['label'] ?? 'Unknown';
            $count = (int) ($it['count'] ?? 0);
            $pct = max(0, min(100, (float) ($it['percentage'] ?? 0)));
            $color = $roomTypeItems['colors'][$index] ?? '#fb8c00';
        @endphp

        <div class="rs-item">
            <div class="rs-row">

                <table>
                    <tr>
                        <td>
                            <span class="rs-left">
                                <span class="rs-dot" style="background: {{ $color }};"></span>
                                <div class="rs-label">{{ $label }} </div>
                            </span>
                        </td>
                        <td>
                            <span class="rs-right" style="text-align:right">
                                {{ $count }} Calls
                            </span>
                            {{-- <div class="rs-right">{{ number_format($pct, 2) }}%</div> --}}
                        </td>
                    </tr>



            </div>

            <div class="rs-bar">
                <div class="rs-fill" style="width: {{ $pct }}%; background: {{ $color }};"></div>
            </div>
        </div>
    @endforeach
@endif
