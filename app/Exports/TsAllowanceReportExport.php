<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TsAllowanceReportExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $data;

    protected $summaryStats;

    protected $paymentsByCurrency;

    protected $startDate;

    protected $endDate;

    public function __construct($data, $summaryStats, $paymentsByCurrency, $startDate, $endDate)
    {
        $this->data = $data;
        $this->summaryStats = $summaryStats;
        $this->paymentsByCurrency = $paymentsByCurrency;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return $this->data;
    }

    public function title(): string
    {
        return 'T&S Allowance Report';
    }

    public function headings(): array
    {
        return [
            'Application #',
            'Employee #',
            'Full Name',
            'Department',
            'Job Title',
            'Grade',
            'Trip Start',
            'Trip End',
            'Days',
            'Out of Station',
            'Overnight',
            'Bed Allowance',
            'Breakfast',
            'Lunch',
            'Dinner',
            'Fuel',
            'Toll Gates',
            'Mileage (km)',
            'Balance Due (USD)',
            'Status',
            'Payment Type',
            'Currency Paid',
            'Amount Paid (Original)',
            'Amount Paid (USD)',
            'Split Payment Details',
            'Payment Method',
            'Payment Reference',
            'Payment Date',
            'Submission Date',
            'Created Date',
        ];
    }

    public function map($row): array
    {
        // Check if this is a split payment
        $isSplitPayment = $row->payment_notes && str_contains($row->payment_notes, 'Split Payment:');
        $paymentType = $isSplitPayment ? 'SPLIT' : ($row->status === 'PAYMENT_PROCESSED' ? 'SINGLE' : 'N/A');

        // Extract split payment details if applicable
        $splitDetails = '';
        if ($isSplitPayment) {
            // Extract the split payment line from notes
            $lines = explode("\n", $row->payment_notes);
            foreach ($lines as $line) {
                if (str_contains($line, 'Split Payment:')) {
                    $splitDetails = trim($line);
                    break;
                }
            }
        }

        return [
            $row->application_number,
            $row->employee_number,
            $row->full_name,
            $row->department?->name ?? 'N/A',
            $row->job_title,
            $row->grade,
            $row->trip_start_date?->format('Y-m-d'),
            $row->trip_end_date?->format('Y-m-d'),
            $row->number_of_days,
            $row->out_of_station_subsistence ?? 0,
            $row->overnight_allowance ?? 0,
            $row->bed_allowance ?? 0,
            $row->breakfast ?? 0,
            $row->lunch ?? 0,
            $row->dinner ?? 0,
            $row->fuel ?? 0,
            $row->toll_gates ?? 0,
            $row->mileage_estimated_distance ?? 0,
            $row->balance_due ?? 0,
            $row->status,
            $paymentType,
            $row->currency?->name ?? 'N/A',
            $row->amount_paid_original ?? 0,
            $row->amount_paid_usd ?? 0,
            $splitDetails ?: 'N/A',
            $row->payment_method ?? 'N/A',
            $row->payment_reference ?? 'N/A',
            $row->payment_date?->format('Y-m-d'),
            $row->submission_date?->format('Y-m-d'),
            $row->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E2E8F0']]],
        ];
    }
}
