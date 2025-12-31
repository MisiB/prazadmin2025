<?php

namespace App\Livewire\Admin\Workflows\Reports;

use App\Exports\StaffWelfareLoanExport;
use App\Interfaces\services\istaffwelfareloanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Mary\Traits\Toast;

class StaffWelfareLoanReport extends Component
{
    use Toast, WithPagination;

    public $breadcrumbs = [];

    public $start_date;

    public $end_date;

    public $status_filter = 'ALL';

    public $department_filter = 'ALL';

    public $search = '';

    protected $staffwelfareloanService;

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Staff Welfare Loan Report'],
        ];

        // Default to current year
        $this->start_date = date('Y-01-01');
        $this->end_date = date('Y-m-d');
    }

    public function boot(istaffwelfareloanService $staffwelfareloanService)
    {
        $this->staffwelfareloanService = $staffwelfareloanService;
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

    public function getLoansProperty()
    {
        $query = \App\Models\StaffWelfareLoan::query()
            ->with([
                'workflow',
                'department',
                'applicant',
                'financeOfficer',
                'approvals.approver',
                'approvals.workflowparameter',
                'payments.currency',
                'payments.exchangerate.user',
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
                $q->where('loan_number', 'like', '%'.$this->search.'%')
                    ->orWhere('full_name', 'like', '%'.$this->search.'%')
                    ->orWhere('employee_number', 'like', '%'.$this->search.'%');
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
            ['id' => 'SUBMITTED', 'name' => 'Submitted'],
            ['id' => 'HR_REVIEW', 'name' => 'HR Review'],
            ['id' => 'FINANCE_REVIEW', 'name' => 'Finance Review'],
            ['id' => 'CEO_APPROVAL', 'name' => 'CEO Approval'],
            ['id' => 'APPROVED', 'name' => 'Approved'],
            ['id' => 'REJECTED', 'name' => 'Rejected'],
            ['id' => 'PAYMENT_PROCESSED', 'name' => 'Payment Processed'],
            ['id' => 'AWAITING_ACKNOWLEDGEMENT', 'name' => 'Awaiting Acknowledgement'],
            ['id' => 'COMPLETED', 'name' => 'Completed'],
        ];
    }

    public function getSummaryStatsProperty()
    {
        $baseQuery = function () {
            $query = \App\Models\StaffWelfareLoan::whereBetween('created_at', [$this->start_date, $this->end_date.' 23:59:59']);

            if ($this->department_filter !== 'ALL') {
                $query->where('department_id', $this->department_filter);
            }

            return $query;
        };

        // Get currency-specific payment totals
        $zigCurrency = \App\Models\Currency::where('name', 'ZIG')->first();
        $usdCurrency = \App\Models\Currency::where('name', 'USD')->orWhere('name', 'US Dollar')->first();

        $totalPaidZig = \App\Models\StaffWelfareLoanPayment::whereHas('staffWelfareLoan', function ($q) {
            $q->whereBetween('created_at', [$this->start_date, $this->end_date.' 23:59:59']);
            if ($this->department_filter !== 'ALL') {
                $q->where('department_id', $this->department_filter);
            }
        })
            ->where('currency_id', $zigCurrency?->id)
            ->sum('amount_paid_original');

        $totalPaidUsd = \App\Models\StaffWelfareLoanPayment::whereHas('staffWelfareLoan', function ($q) {
            $q->whereBetween('created_at', [$this->start_date, $this->end_date.' 23:59:59']);
            if ($this->department_filter !== 'ALL') {
                $q->where('department_id', $this->department_filter);
            }
        })
            ->where(function ($q) use ($usdCurrency) {
                $q->where('currency_id', $usdCurrency?->id)
                    ->orWhereNull('currency_id'); // Legacy payments without currency
            })
            ->sum('amount_paid_usd');

        return [
            'total_loans' => $baseQuery()->count(),
            'total_amount_requested' => $baseQuery()->sum('loan_amount_requested'),
            'total_amount_paid' => $baseQuery()->whereNotNull('amount_paid')->sum('amount_paid'),
            'total_paid_zig' => $totalPaidZig,
            'total_paid_usd' => $totalPaidUsd,
            'approved_loans' => $baseQuery()->where('status', 'APPROVED')->count(),
            'rejected_loans' => $baseQuery()->where('status', 'REJECTED')->count(),
            'pending_loans' => $baseQuery()->whereNotIn('status', ['APPROVED', 'REJECTED', 'COMPLETED'])->count(),
            'completed_loans' => $baseQuery()->where('status', 'COMPLETED')->count(),
        ];
    }

    public function exportToExcel()
    {
        $fileName = 'staff-welfare-loans-'.date('Y-m-d-His').'.xlsx';

        $this->success('Generating Excel report...');

        return Excel::download(
            new StaffWelfareLoanExport(
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
        $loans = \App\Models\StaffWelfareLoan::query()
            ->with([
                'workflow',
                'department',
                'applicant',
                'financeOfficer',
                'approvals.approver',
                'approvals.workflowparameter',
                'payments.currency',
                'payments.exchangerate.user',
            ])
            ->whereBetween('created_at', [$this->start_date, $this->end_date.' 23:59:59']);

        if ($this->status_filter !== 'ALL') {
            $loans->where('status', $this->status_filter);
        }

        if ($this->department_filter !== 'ALL') {
            $loans->where('department_id', $this->department_filter);
        }

        if ($this->search) {
            $loans->where(function ($q) {
                $q->where('loan_number', 'like', '%'.$this->search.'%')
                    ->orWhere('full_name', 'like', '%'.$this->search.'%')
                    ->orWhere('employee_number', 'like', '%'.$this->search.'%');
            });
        }

        $loans = $loans->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('pdf.staff-welfare-loan-report', [
            'loans' => $loans,
            'summaryStats' => $this->summaryStats,
            'startDate' => $this->start_date,
            'endDate' => $this->end_date,
        ])->setPaper('a4', 'landscape');

        $fileName = 'staff-welfare-loans-'.date('Y-m-d-His').'.pdf';

        $this->success('Generating PDF report...');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName);
    }

    public function headers(): array
    {
        return [
            ['key' => 'loan_number', 'label' => 'Loan Number', 'class' => 'w-32'],
            ['key' => 'full_name', 'label' => 'Applicant', 'class' => 'w-48'],
            ['key' => 'department.name', 'label' => 'Department', 'class' => 'w-40'],
            ['key' => 'loan_amount_requested', 'label' => 'Amount', 'class' => 'w-32'],
            ['key' => 'status', 'label' => 'Status', 'class' => 'w-32'],
            ['key' => 'submission_date', 'label' => 'Submitted', 'class' => 'w-32'],
            ['key' => 'action', 'label' => '', 'class' => 'w-20'],
        ];
    }

    public function render()
    {
        return view('livewire.admin.workflows.reports.staff-welfare-loan-report', [
            'loans' => $this->loans,
            'departments' => $this->departments,
            'statusOptions' => $this->statusOptions,
            'summaryStats' => $this->summaryStats,
            'headers' => $this->headers(),
        ]);
    }
}
