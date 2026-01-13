<?php

namespace App\Livewire\Admin\Workflows\Reports;

use App\Exports\PaymentRequisitionReportExport;
use App\Interfaces\services\ipaymentrequisitionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Mary\Traits\Toast;

class PaymentRequisitionReport extends Component
{
    use Toast, WithPagination;

    public $breadcrumbs = [];

    public $start_date;

    public $end_date;

    public $status_filter = 'ALL';

    public $department_filter = 'ALL';

    public $search = '';

    protected $paymentrequisitionService;

    public function mount()
    {
        $this->authorize('report.payment.requisition.access');

        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Payment Requisition Report'],
        ];

        // Default to current year
        $this->start_date = date('Y-01-01');
        $this->end_date = date('Y-m-d');
    }

    public function boot(ipaymentrequisitionService $paymentrequisitionService)
    {
        $this->paymentrequisitionService = $paymentrequisitionService;
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
        $query = \App\Models\PaymentRequisition::query()
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
                $q->where('reference_number', 'like', '%'.$this->search.'%')
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
            ['id' => 'Submitted', 'name' => 'Submitted'],
            ['id' => 'HOD_RECOMMENDED', 'name' => 'HOD Recommended'],
            ['id' => 'ADMIN_REVIEWED', 'name' => 'Admin Reviewed'],
            ['id' => 'ADMIN_RECOMMENDED', 'name' => 'Admin Recommended'],
            ['id' => 'AWAITING_PAYMENT_VOUCHER', 'name' => 'Awaiting Payment Voucher'],
            ['id' => 'Rejected', 'name' => 'Rejected'],
        ];
    }

    public function getSummaryStatsProperty()
    {
        $baseQuery = function () {
            $query = \App\Models\PaymentRequisition::whereBetween('created_at', [$this->start_date, $this->end_date.' 23:59:59']);

            if ($this->department_filter !== 'ALL') {
                $query->where('department_id', $this->department_filter);
            }

            return $query;
        };

        return [
            'total_requisitions' => $baseQuery()->count(),
            'total_amount' => $baseQuery()->sum('total_amount'),
            'draft_requisitions' => $baseQuery()->where('status', 'DRAFT')->count(),
            'submitted_requisitions' => $baseQuery()->where('status', 'Submitted')->count(),
            'hod_recommended' => $baseQuery()->where('status', 'HOD_RECOMMENDED')->count(),
            'admin_reviewed' => $baseQuery()->where('status', 'ADMIN_REVIEWED')->count(),
            'admin_recommended' => $baseQuery()->where('status', 'ADMIN_RECOMMENDED')->count(),
            'awaiting_voucher' => $baseQuery()->where('status', 'AWAITING_PAYMENT_VOUCHER')->count(),
            'rejected_requisitions' => $baseQuery()->where('status', 'Rejected')->count(),
            'amount_draft' => $baseQuery()->where('status', 'DRAFT')->sum('total_amount'),
            'amount_submitted' => $baseQuery()->where('status', 'Submitted')->sum('total_amount'),
            'amount_awaiting_voucher' => $baseQuery()->where('status', 'AWAITING_PAYMENT_VOUCHER')->sum('total_amount'),
            'amount_rejected' => $baseQuery()->where('status', 'Rejected')->sum('total_amount'),
        ];
    }

    public function getPaymentsByCurrencyProperty()
    {
        $requisitions = \App\Models\PaymentRequisition::with('currency')
            ->whereBetween('created_at', [$this->start_date, $this->end_date.' 23:59:59'])
            ->where('status', 'AWAITING_PAYMENT_VOUCHER');

        if ($this->department_filter !== 'ALL') {
            $requisitions->where('department_id', $this->department_filter);
        }

        $requisitions = $requisitions->get();

        // Group by currency and sum amounts
        $byCurrency = [];

        foreach ($requisitions as $requisition) {
            $currencyName = $requisition->currency?->name ?? 'USD';
            $currencyId = $requisition->currency_id ?? 0;

            if (! isset($byCurrency[$currencyId])) {
                $byCurrency[$currencyId] = [
                    'currency_name' => $currencyName,
                    'total_amount' => 0,
                    'count' => 0,
                ];
            }

            $byCurrency[$currencyId]['total_amount'] += $requisition->total_amount ?? 0;
            $byCurrency[$currencyId]['count']++;
        }

        return collect($byCurrency)->sortByDesc('total_amount');
    }

    public function exportToExcel()
    {
        $this->authorize('report.payment.requisition.export');

        $fileName = 'payment-requisitions-'.date('Y-m-d-His').'.xlsx';

        $this->success('Generating Excel report...');

        return Excel::download(
            new PaymentRequisitionReportExport(
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
        $this->authorize('report.payment.requisition.export');

        $requisitions = \App\Models\PaymentRequisition::query()
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
            ->whereBetween('created_at', [$this->start_date, $this->end_date.' 23:59:59']);

        if ($this->status_filter !== 'ALL') {
            $requisitions->where('status', $this->status_filter);
        }

        if ($this->department_filter !== 'ALL') {
            $requisitions->where('department_id', $this->department_filter);
        }

        if ($this->search) {
            $requisitions->where(function ($q) {
                $q->where('reference_number', 'like', '%'.$this->search.'%')
                    ->orWhere('purpose', 'like', '%'.$this->search.'%');
            });
        }

        $requisitions = $requisitions->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('pdf.payment-requisition-report', [
            'requisitions' => $requisitions,
            'summaryStats' => $this->summaryStats,
            'paymentsByCurrency' => $this->paymentsByCurrency,
            'startDate' => $this->start_date,
            'endDate' => $this->end_date,
        ])->setPaper('a4', 'landscape');

        $fileName = 'payment-requisitions-'.date('Y-m-d-His').'.pdf';

        $this->success('Generating PDF report...');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName);
    }

    public function headers(): array
    {
        return [
            ['key' => 'reference_number', 'label' => 'Reference #', 'class' => 'w-32'],
            ['key' => 'purpose', 'label' => 'Purpose', 'class' => 'w-48'],
            ['key' => 'department.name', 'label' => 'Department', 'class' => 'w-40'],
            ['key' => 'total_amount', 'label' => 'Amount', 'class' => 'w-32'],
            ['key' => 'status', 'label' => 'Status', 'class' => 'w-32'],
            ['key' => 'created_at', 'label' => 'Created', 'class' => 'w-32'],
            ['key' => 'action', 'label' => '', 'class' => 'w-20'],
        ];
    }

    public function render()
    {
        return view('livewire.admin.workflows.reports.payment-requisition-report', [
            'requisitions' => $this->requisitions,
            'departments' => $this->departments,
            'statusOptions' => $this->statusOptions,
            'summaryStats' => $this->summaryStats,
            'paymentsByCurrency' => $this->paymentsByCurrency,
            'headers' => $this->headers(),
        ]);
    }
}
