<?php

namespace App\Exports;

use App\Models\PaymentRequisition;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentRequisitionReportExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
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
        $query = PaymentRequisition::query()
            ->with([
                'workflow',
                'department',
                'createdBy',
                'recommendedByHod',
                'reviewedByAdmin',
                'recommendedByAdmin',
                'approvedByFinal',
                'budget',
                'budgetLineItem',
                'currency',
                'lineItems',
                'approvals',
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
                $q->where('reference_number', 'like', '%'.$this->search.'%')
                    ->orWhere('purpose', 'like', '%'.$this->search.'%');
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function title(): string
    {
        return 'Payment Requisitions';
    }

    public function headings(): array
    {
        return [
            'Reference Number',
            'Source Type',
            'Source ID',
            'Year',
            'Department',
            'Budget',
            'Budget Line Item',
            'Purpose',
            'Currency',
            'Total Amount',
            'Status',
            'Created By',
            'HOD Recommended By',
            'Admin Reviewed By',
            'Admin Recommended By',
            'Final Approved By',
            'Workflow',
            'Line Items Count',
            'Created At',
            'Updated At',
        ];
    }

    public function map($requisition): array
    {
        return [
            $requisition->reference_number,
            $requisition->source_type,
            $requisition->source_id ?? 'N/A',
            $requisition->year,
            $requisition->department?->name ?? 'N/A',
            $requisition->budget?->name ?? 'N/A',
            $requisition->budgetLineItem?->name ?? 'N/A',
            $requisition->purpose,
            $requisition->currency?->name ?? 'N/A',
            $requisition->total_amount,
            $requisition->status,
            $requisition->createdBy?->name ?? 'N/A',
            $requisition->recommendedByHod?->name ?? 'N/A',
            $requisition->reviewedByAdmin?->name ?? 'N/A',
            $requisition->recommendedByAdmin?->name ?? 'N/A',
            $requisition->approvedByFinal?->name ?? 'N/A',
            $requisition->workflow?->name ?? 'N/A',
            $requisition->lineItems->count(),
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
