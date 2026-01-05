<?php

namespace App\Exports;

use App\Models\Department;
use App\Models\Expensecategory;
use App\Models\Sourceoffund;
use App\Models\Strategyprogramme;
use App\Models\Strategysubprogrammeoutput;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BudgetImportTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new BudgetTemplateSheet,
            new DepartmentsReferenceSheet,
            new ExpenseCategoriesReferenceSheet,
            new ProgrammesReferenceSheet,
            new SourcesOfFundReferenceSheet,
            new OutputsReferenceSheet,
        ];
    }
}

class BudgetTemplateSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function array(): array
    {
        // Return sample data row
        return [
            [
                'Office Equipment Purchase',
                'Purchase of computers and printers for ICT department',
                'ICT',
                'CAPEX',
                '', // programme_title - optional
                'Electronic Tools/Systems developed',
                'INTERNAL FUNDS',
                5,
                1500.00,
                '2026-03-15',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'activity',
            'description',
            'department',
            'expense_category',
            'programme_title',
            'output',
            'source_of_fund',
            'quantity',
            'unit_price',
            'focus_date',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(35);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(40);
        $sheet->getColumnDimension('F')->setWidth(40);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(12);
        $sheet->getColumnDimension('I')->setWidth(12);
        $sheet->getColumnDimension('J')->setWidth(15);

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Budget Items';
    }
}

class DepartmentsReferenceSheet implements FromArray, WithHeadings, WithStyles, WithTitle
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

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Departments';
    }
}

class ExpenseCategoriesReferenceSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function array(): array
    {
        return Expensecategory::query()
            ->select('name')
            ->orderBy('name')
            ->get()
            ->map(fn ($cat) => [$cat->name])
            ->toArray();
    }

    public function headings(): array
    {
        return ['Available Expense Categories'];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getColumnDimension('A')->setWidth(40);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Expense Categories';
    }
}

class SourcesOfFundReferenceSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function array(): array
    {
        return Sourceoffund::query()
            ->select('name')
            ->orderBy('name')
            ->get()
            ->map(fn ($source) => [$source->name])
            ->toArray();
    }

    public function headings(): array
    {
        return ['Available Sources of Fund'];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getColumnDimension('A')->setWidth(40);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Sources of Fund';
    }
}

class OutputsReferenceSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function array(): array
    {
        return Strategysubprogrammeoutput::query()
            ->select('output')
            ->orderBy('output')
            ->get()
            ->map(fn ($output) => [$output->output])
            ->toArray();
    }

    public function headings(): array
    {
        return ['Available Outputs'];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getColumnDimension('A')->setWidth(60);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Outputs';
    }
}

class ProgrammesReferenceSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function array(): array
    {
        return Strategyprogramme::query()
            ->select('title')
            ->orderBy('title')
            ->get()
            ->map(fn ($programme) => [$programme->title])
            ->toArray();
    }

    public function headings(): array
    {
        return ['Available Programmes'];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getColumnDimension('A')->setWidth(60);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Programmes';
    }
}
