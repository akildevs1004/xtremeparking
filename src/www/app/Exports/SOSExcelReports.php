<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SOSExcelReports implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles
{
    /** @var \Illuminate\Support\Collection */
    protected Collection $reports;

    public function __construct($reports)
    {
        $this->reports = collect($reports);
    }

    public function collection()
    {
        // Excel passes each row into map()
        return $this->reports->values();
    }

    public function headings(): array
    {
        return [

            'Alarm Start',
            'Alarm End',
            'Location',
            'Room',
            'Room Id',

            'Room Type',
            'Response (HH:MM)',
            'Status',
            'ACK',
        ];
    }

    public function map($r): array
    {
        // Index (Excel does not give index in map(), so compute from collection position)
        // We can locate by unique id if exists; otherwise, fallback to search position.
        $i = $this->rowIndex($r);

        // Room type same as Blade:
        // strtoupper(str_replace('-PH', '', $r->room->room_type))
        $roomType = '--';
        if (!empty($r->room?->room_type)) {
            $roomType = strtoupper(str_replace('-PH', '', $r->room->room_type));
        }

        // Status same as Blade:
        // $r->sos_status ? ($r->responded_datetime ? 'ACKNOWLEDGED' : 'PENDING') : 'RESOLVED';
        if ($r->sos_status) {
            $status = $r->responded_datetime ? 'ACKNOWLEDGED' : 'PENDING';
        } else {
            $status = 'RESOLVED';
        }

        // Response in minutes -> HH:MM (same as Blade: gmdate('H:i', $respMin * 60))
        $respMin = $r->response_in_minutes;
        $response = $respMin !== null ? gmdate('H:i', (int)$respMin * 60) : '--';

        // Alarm start/end and ACK formatting.
        // In PDF you split time/date; for Excel better as one datetime string.
        $alarmStart = $this->fmtDateTime($r->alarm_start_datetime);
        $alarmEnd   = $this->fmtDateTime($r->alarm_end_datetime);
        $ack        = $this->fmtDateTime($r->responded_datetime);

        // Location / Room (your Blade shows room_name and device location on next line)
        $roomName = $r->room_name ?? '--';
        $roomType = $r->room?->room_type ?? '--';
        $roomId = $r->room_id ?? '--';

        $location = $r->device?->location ?? '--';
        $locationRoom = $roomName . ' | ' . $location;

        return [




            $alarmStart,
            $alarmEnd,
            $location,
            $roomName,
            $roomId,
            $roomType,
            $response,
            $status,
            $ack,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Bold headers and freeze first row
        $sheet->freezePane('A2');

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function fmtDateTime($value): string
    {
        if (!$value) return '--';

        try {
            // Keep it close to your PDF style, but Excel-friendly.
            // Example: "Dec 20, 2025 14:35"
            return Carbon::parse($value)->format('M d, Y H:i');
        } catch (\Throwable $e) {
            return (string)$value;
        }
    }

    private function rowIndex($r): int
    {
        // Prefer id if exists to avoid object comparison edge cases
        if (isset($r->id)) {
            $pos = $this->reports->search(fn($x) => isset($x->id) && $x->id == $r->id);
            if ($pos !== false) return $pos + 1;
        }

        // Fallback: search by strict object equality
        $pos = $this->reports->search(fn($x) => $x === $r);
        return ($pos === false) ? 0 : ($pos + 1);
    }
}
