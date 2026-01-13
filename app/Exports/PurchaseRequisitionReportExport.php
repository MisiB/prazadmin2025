<?php

namespace App\Exports;

use App\Models\Purchaserequisition;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PurchaseRequisitionReportExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $startDate;

    protected $endDate;

    protected $statusFilter;

    protected $departmentFilter;

    protected $search;

    public function __construct($startDate, $endDate, $statusFilter = 'ALL', $departmentFilter = 'ALL', $search = '')
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->statusFilter = $statusFilter;
        $this->departmentFilter = $departmentFilter;
        $this->search = $search;
    }

    public function collection()
    {
        $query = Purchaserequisition::query()
            ->with([
                'workflow',
                'department',
                'requestedby',
                'recommendedby',
                'budgetitem',
                'approvals.user',
                'approvals.workflowparameter',
            ])
            ->whereBetween('created_at', [$this->startDate, $this->endDate.' 23:59:59']);

        if ($this->statusFilter !== 'ALL') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->departmentFilter !== 'ALL') {
            $query->where('department_id', $this->departmentFilter);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('prnumber', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhere('purpose', 'like', '%'.$this->search.'%');
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function title(): string
    {
        return 'Purchase Requisitions';
    }

    public function headings(): array
    {
        return [
            'PR Number',
            'Year',
            'Department',
            'Budget Item',
            'Quantity',
            'Description',
            'Purpose',
            'Requested By',
            'Recommended By',
            'Fund Available',
            'Status',
            'Workflow',
            'Created At',
            'Updated At',
        ];
    }

    public function map($requisition): array
    {
        return [
            $requisition->prnumber,
            $requisition->year,
            $requisition->department?->name ?? 'N/A',
            $requisition->budgetitem?->name ?? 'N/A',
            $requisition->quantity,
            $requisition->description,
            $requisition->purpose,
            $requisition->requestedby?->name ?? 'N/A',
            $requisition->recommendedby?->name ?? 'N/A',
            $requisition->fundavailable ?? 'N/A',
            $requisition->status,
            $requisition->workflow?->name ?? 'N/A',
            $requisition->created_at->format('Y-m-d H:i:s'),
            $requisition->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E2E8F0']]],
        ];
    }
}
