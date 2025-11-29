<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;


class AlarmEventsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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
            "Event Id",
            "Customer",
            "Property",
            "Address",
            "City",
            "Type",
            "Event Time",
            "Closed Time",
            "Priority",
            "Resolved Time(HH:MM)",

        ];
    }

    public function map($row): array
    {
        return [
            $row['id'],

            $row['device']['customer']['primary_contact']['first_name'] . ' ' . $row['device']['customer']['primary_contact']['last_name'],
            $row['device']['customer']['buildingtype']['name'],
            $row['device']['customer']['area'],
            $row['device']['customer']['city'],
            $row['alarm_type'],
            $row['alarm_start_datetime'],
            $row['alarm_end_datetime'],
            $row['category']['name'],
            $this->minutesToTime($row['response_minutes']),


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
