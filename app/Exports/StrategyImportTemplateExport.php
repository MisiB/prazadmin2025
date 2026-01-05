<?php

namespace App\Exports;

use App\Models\Department;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StrategyImportTemplateExport implements WithMultipleSheets
{
    public function __construct(protected int $strategyId) {}

    public function sheets(): array
    {
        return [
            new StrategyTemplateSheet($this->strategyId),
            new StrategyDepartmentsReferenceSheet,
        ];
    }
}

class StrategyTemplateSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function __construct(protected int $strategyId) {}

    public function array(): array
    {
        return [
            [
                'P001',
                'Programme 1 - Strategic Management',
                'Enhanced organizational effectiveness',
                'Strategic plans developed and implemented',
                'ICT',
                '100',
                'Number of systems upgraded',
                'Number',
                '2025',
                '5',
                '10',
            ],
            [
                'P001',
                'Programme 1 - Strategic Management',
                'Enhanced organizational effectiveness',
                'Strategic plans developed and implemented',
                'Finance',
                '50',
                'Budget utilization rate',
                'Percentage',
                '2025',
                '95',
                '5',
            ],
            [
                'P002',
                'Programme 2 - Operations Excellence',
                'Improved service delivery',
                'Customer satisfaction improved',
                'Operations',
                '100',
                'Customer satisfaction score',
                'Percentage',
                '2025',
                '90',
                '5',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'programme_code',
            'programme_title',
            'outcome_title',
            'output_title',
            'department',
            'weightage',
            'indicator_title',
            'indicator_uom',
            'target_year',
            'target_value',
            'target_variance',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(40);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(35);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(12);
        $sheet->getColumnDimension('J')->setWidth(12);
        $sheet->getColumnDimension('K')->setWidth(15);

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '16A34A'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Strategy Import';
    }
}

class StrategyDepartmentsReferenceSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function array(): array
    {
        return Department::query()
            ->select('name')
            ->orderBy('name')
            ->get()
            ->map(fn ($dept) => [$dept->name])
            ->toArray();
    }

    public function headings(): array
    {
        return ['Available Departments'];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getColumnDimension('A')->setWidth(40);

        return [1 => ['font' => ['bold' => true]]];
    }

    public function title(): string
    {
        return 'Departments';
    }
}
