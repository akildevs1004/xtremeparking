<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;


class AlarmEventNotesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            "ID",
            "Alarm ID",
            "Alarm Start Time",
            "Alarm End Time",
            "Alarm Type",
            "Security",
            "Customer",
            "Contact",
            "Call Status",
            "Response",
            "Event Status",
            "Date",
            "Notes",

        ];
    }

    public function map($row): array
    {
        return [
            $row['id'],
            $row['alarm_id'],
            $row['alarm']['alarm_start_datetime'],
            $row['alarm']['alarm_end_datetime'],
            $row['alarm']['alarm_type'],
            $row['security'] ?  $row['security']['first_name'] . ' ' . $row['security']['last_name'] : '---',
            $row['contact'] ? $row['contact']['first_name'] . ' ' . $row['contact']['last_name'] : '---',
            $row['contact'] ? $row['contact']['phone1'] : '---',
            $row['call_status'],
            $row['response'],
            $row['event_status'],
            $row['created_datetime'],
            $row['notes'],



        ];
    }
    public function minutesToTime($totalMinutes)
    {
        if ($totalMinutes == 0) return '00:00';
        if ($totalMinutes == null) return '---';
        // Calculate hours and minutes
        $hours = intdiv($totalMinutes, 60); // Integer division to get hours
        $minutes = $totalMinutes % 60; // Remainder to get minutes

        // Format hours and minutes to HH:MM
        return $formattedTime = sprintf('%02d:%02d', $hours, $minutes);
    }
    // public function styles($sheet)
    // {
    //     return [
    //         // Apply text format to the 'Email' column
    //         'C' => ['numberFormat' => NumberFormat::FORMAT_TEXT],
    //     ];
    // }
}
