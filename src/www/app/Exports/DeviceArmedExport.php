<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;


class DeviceArmedExport  implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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

            "Device",
            "Armed Time",
            "Disarm Time",
            "Duration(HH:MM)",

        ];
    }

    public function map($row): array
    {
        return [

            $row['device']['name'],

            $row['armed_datetime'],
            $row['disarm_datetime'],

            $this->minutesToTime($row['duration_in_minutes']),


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
