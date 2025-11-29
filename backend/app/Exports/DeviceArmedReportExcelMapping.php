<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithTitle;


class DeviceArmedReportExcelMapping  implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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


            "Date",
            "Customer",
            "Armed1",
            "Disarm1",

            "Armed2",
            "Disarm2",

            "Armed3",
            "Disarm3",

            "Armed4",
            "Disarm4",

            "Armed5",
            "Disa5",

            "Armed ",
            "Disarm ",

            "Events",

        ];
    }

    public function map($row): array
    {
        $mappedRow = [
            $row['date'],
            $row['customer'],
        ];

        for ($i = 0; $i < 5; $i++) {
            if (isset($row['armed'][$i])) {
                $armedEntry = $row['armed'][$i];
                if (is_array($armedEntry)) {
                    $mappedRow[] = date("H:i", strtotime($armedEntry['armed_datetime']));
                    $mappedRow[] = date("H:i", strtotime($armedEntry['disarm_datetime']));
                } else {
                    $mappedRow[] = '---';
                    $mappedRow[] = '---';
                }
            } else {
                $mappedRow[] = '---';
                $mappedRow[] = '---';
            }
        }


        $mappedRow[] = $row['armedHours'];
        $mappedRow[] = $row['disarmHours'];
        $mappedRow[] = $row['events_count'];

        return $mappedRow;
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
    public function styles($sheet)
    {
        $styles = [
            '1' => [ // Header row
                'font' => [
                    'bold' => true,
                ],
            ],
        ];

        // Loop through columns C to L (C=3 to L=12)
        for ($col = ord('C'); $col <= ord('L'); $col++) { // Convert letters to ASCII
            $columnLetter = chr($col); // Convert back to letter

            $styles[$columnLetter] = [
                'font' => [
                    'color' => [
                        'argb' => ($col % 2 === 0) ? 'FF008000' : 'FFFF0000', // Green for even, red for odd
                    ],
                ],
            ];
        }

        return $styles;
    }
}
