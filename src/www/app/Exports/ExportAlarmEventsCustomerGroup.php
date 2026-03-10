<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;


class ExportAlarmEventsCustomerGroup  implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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


            "Customer",
            "Type",
            "SOS",
            "Critical",
            "Technical",
            "Events",
            "Medical",
            "Fire",
            "Water",
            "Temperature",
            "Total",


        ];
    }

    public function map($row): array
    {

        $total = $row['counts']["soscount"] + $row['counts']["criticalcount"] + $row['counts']["technicalcount"] + $row['counts']["eventscount"] + $row['counts']["medicalcount"] + $row['counts']["firecount"] + $row['counts']["watercount"] + $row['counts']["temperaturecount"];

        return [

            $row['building_name'],
            $row['buildingtype']["name"],
            $row['counts']["soscount"] ? $row['counts']["soscount"] : "0",
            $row['counts']["criticalcount"] ? $row['counts']["criticalcount"] : "0",
            $row['counts']["technicalcount"] ? $row['counts']["technicalcount"] : "0",
            $row['counts']["eventscount"] ? $row['counts']["eventscount"] : "0",
            $row['counts']["medicalcount"] ? $row['counts']["medicalcount"] : "0",
            $row['counts']["firecount"] ? $row['counts']["firecount"] : "0",
            $row['counts']["watercount"] ? $row['counts']["watercount"] : "0",
            $row['counts']["temperaturecount"] ? $row['counts']["temperaturecount"] : "0",

            $total ? $total : "0",

        ];
    }
    public function styles($sheet)
    {
        $styles = [
            '1' => [ // Header row
                'font' => [
                    'bold' => true,
                ],
            ],
            '0' => [ // Header row
                'font' => [
                    'bold' => true,
                ],
            ],
        ];



        return $styles;
    }
}
