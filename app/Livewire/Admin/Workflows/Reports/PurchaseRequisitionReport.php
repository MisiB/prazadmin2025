<?php

namespace App\Livewire\Admin\Workflows\Reports;

use App\Exports\PurchaseRequisitionReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Mary\Traits\Toast;

class PurchaseRequisitionReport extends Component
{
    use Toast, WithPagination;

    public $breadcrumbs = [];

    public $start_date;

    public $end_date;

    public $status_filter = 'ALL';

    public $department_filter = 'ALL';

    public $search = '';

    public function mount()
    {
        $this->authorize('report.purchase.requisition.access');

        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Purchase Requisition Report'],
        ];

        // Default to current year
        $this->start_date = date('Y-01-01');
        $this->end_date = date('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingDepartmentFilter()
    {
        $this->resetPage();
    }

    public function applyFilters()
    {
        $this->resetPage();
        $this->success('Filters applied successfully');
    }

    public function resetFilters()
    {
        $this->start_date = date('Y-01-01');
        $this->end_date = date('Y-m-d');
        $this->status_filter = 'ALL';
        $this->department_filter = 'ALL';
        $this->search = '';
        $this->resetPage();
        $this->success('Filters reset successfully');
    }

    public function getRequisitionsProperty()
    {
        $query = \App\Models\Purchaserequisition::query()
            ->with([
                'workflow',
                'department',
                'requestedby',
                'recommendedby',
                'budgetitem',
                'approvals.user',
                'approvals.workflowparameter',
            ])
            ->whereBetween('created_at', [$this->start_date, $this->end_date.' 23:59:59']);

        // Status filter
        if ($this->status_filter !== 'ALL') {
            $query->where('status', $this->status_filter);
        }

        // Department filter
        if ($this->department_filter !== 'ALL') {
            $query->where('department_id', $this->department_filter);
        }

        // Search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('prnumber', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhere('purpose', 'like', '%'.$this->search.'%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    public function getDepartmentsProperty()
    {
        return \App\Models\Department::orderBy('name')->get();
    }

    public function getStatusOptionsProperty()
    {
        return [
            ['id' => 'ALL', 'name' => 'All Statuses'],
            ['id' => 'DRAFT', 'name' => 'Draft'],
            ['id' => 'AWAITING_RECOMMENDATION', 'name' => 'Awaiting Recommendation'],
            ['id' => 'RECOMMENDED', 'name' => 'Recommended'],
            ['id' => 'APPROVED', 'name' => 'Approved'],
            ['id' => 'REJECTED', 'name' => 'Rejected'],
            ['id' => 'AWARDED', 'name' => 'Awarded'],
            ['id' => 'COMPLETED', 'name' => 'Completed'],
        ];
    }

    public function getSummaryStatsProperty()
    {
        $baseQuery = function () {
            $query = \App\Models\Purchaserequisition::whereBetween('created_at', [$this->start_date, $this->end_date.' 23:59:59']);

            if ($this->department_filter !== 'ALL') {
                $query->where('department_id', $this->department_filter);
            }

            return $query;
        };

        return [
            'total_requisitions' => $baseQuery()->count(),
            'draft_requisitions' => $baseQuery()->where('status', 'DRAFT')->count(),
            'awaiting_recommendation' => $baseQuery()->where('status', 'AWAITING_RECOMMENDATION')->count(),
            'recommended_requisitions' => $baseQuery()->where('status', 'RECOMMENDED')->count(),
            'approved_requisitions' => $baseQuery()->where('status', 'APPROVED')->count(),
            'rejected_requisitions' => $baseQuery()->where('status', 'REJECTED')->count(),
            'awarded_requisitions' => $baseQuery()->where('status', 'AWARDED')->count(),
            'completed_requisitions' => $baseQuery()->where('status', 'COMPLETED')->count(),
        ];
    }

    public function exportToExcel()
    {
        $this->authorize('report.purchase.requisition.export');

        $fileName = 'purchase-requisitions-'.date('Y-m-d-His').'.xlsx';

        $this->success('Generating Excel report...');

        return Excel::download(
            new PurchaseRequisitionReportExport(
                $this->start_date,
                $this->end_date,
                $this->status_filter,
                $this->department_filter,
                $this->search
            ),
            $fileName
        );
    }

    public function exportToPdf()
    {
        $this->authorize('report.purchase.requisition.export');

        $requisitions = \App\Models\Purchaserequisition::query()
            ->with([
                'workflow',
                'department',
                'requestedby',
                'recommendedby',
                'budgetitem',
                'approvals.user',
                'approvals.workflowparameter',
            ])
            ->whereBetween('created_at', [$this->start_date, $this->end_date.' 23:59:59']);

        if ($this->status_filter !== 'ALL') {
            $requisitions->where('status', $this->status_filter);
        }

        if ($this->department_filter !== 'ALL') {
            $requisitions->where('department_id', $this->department_filter);
        }

        if ($this->search) {
            $requisitions->where(function ($q) {
                $q->where('prnumber', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhere('purpose', 'like', '%'.$this->search.'%');
            });
        }

        $requisitions = $requisitions->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('pdf.purchase-requisition-report', [
            'requisitions' => $requisitions,
            'summaryStats' => $this->summaryStats,
            'startDate' => $this->start_date,
            'endDate' => $this->end_date,
        ])->setPaper('a4', 'landscape');

        $fileName = 'purchase-requisitions-'.date('Y-m-d-His').'.pdf';

        $this->success('Generating PDF report...');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName);
    }

    public function headers(): array
    {
        return [
            ['key' => 'prnumber', 'label' => 'PR Number', 'class' => 'w-32'],
            ['key' => 'description', 'label' => 'Description', 'class' => 'w-48'],
            ['key' => 'department.name', 'label' => 'Department', 'class' => 'w-40'],
            ['key' => 'quantity', 'label' => 'Quantity', 'class' => 'w-24'],
            ['key' => 'status', 'label' => 'Status', 'class' => 'w-32'],
            ['key' => 'created_at', 'label' => 'Created', 'class' => 'w-32'],
            ['key' => 'action', 'label' => '', 'class' => 'w-20'],
        ];
    }

    public function render()
    {
        return view('livewire.admin.workflows.reports.purchase-requisition-report', [
            'requisitions' => $this->requisitions,
            'departments' => $this->departments,
            'statusOptions' => $this->statusOptions,
            'summaryStats' => $this->summaryStats,
            'headers' => $this->headers(),
        ]);
    }
}
