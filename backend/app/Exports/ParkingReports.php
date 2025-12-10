<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;


class ParkingReports  implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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


            'S.No',
            'Vehicle Number',
            'In Time',
            'Out Time',
            'Duration (Hours)',
            'Total Amount',

            'Membership',
            'Payment  ',





        ];
    }

    public function map($row): array
    {


        $membershipLabel = "GUEST";
        if ($row['membership_id']) {
            $type = $row['parking_members'] && $row['parking_members']['member_type'] ? ucfirst($row['parking_members']['member_type']) : 'MEMBER';
            $membershipLabel = $type . ($row['member_guest_vehicle_id'] ? ' - Guest' : '');
        }


        // Payment Mode / Status (same logic as your slot)


        $paymentLabel = "---";
        if ($row['payment_mode'] && $row['out_time']) {
            $paymentLabel = ucfirst($row['payment_mode']);
        } else if ($row['out_time'] && $row['membership_id']) {
            $paymentLabel = "Completed";
        }
        return [

            $row['building_name'],
            $row['log_vehicle_number'],
            $row["in_time"] ? $row["in_time"] : "---",
            $row["out_time"] ? $row["out_time"] : "---",
            $row['duration_in_hours'] ? $row['duration_in_hours'] : "---",
            $row['total_amount'] ? $row['total_amount'] : "---",
            $membershipLabel,
            $paymentLabel,


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
