<?php

namespace App\Livewire\Admin\Workflows\Reports;

use App\Interfaces\services\itsallowanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Mary\Traits\Toast;

class TsAllowanceReport extends Component
{
    use Toast, WithPagination;

    public $breadcrumbs = [];

    public $start_date;

    public $end_date;

    public $status_filter = 'ALL';

    public $department_filter = 'ALL';

    public $search = '';

    protected $tsallowanceService;

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'T&S Allowance Report'],
        ];

        // Default to current year
        $this->start_date = date('Y-01-01');
        $this->end_date = date('Y-m-d');
    }

    public function boot(itsallowanceService $tsallowanceService)
    {
        $this->tsallowanceService = $tsallowanceService;
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

    public function getAllowancesProperty()
    {
        $query = \App\Models\TsAllowance::query()
            ->with([
                'workflow',
                'department',
                'applicant',
                'financeOfficer',
                'approvals.approver',
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
                $q->where('application_number', 'like', '%'.$this->search.'%')
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

    public function getDepartmentOptionsProperty()
    {
        $departments = \App\Models\Department::orderBy('name')->get()
            ->map(fn ($dept) => ['id' => $dept->id, 'name' => $dept->name])
            ->toArray();

        return array_merge([['id' => 'ALL', 'name' => 'All Departments']], $departments);
    }

    public function getStatusOptionsProperty()
    {
        return [
            ['id' => 'ALL', 'name' => 'All Statuses'],
            ['id' => 'DRAFT', 'name' => 'Draft'],
            ['id' => 'SUBMITTED', 'name' => 'Submitted'],
            ['id' => 'UNDER_REVIEW', 'name' => 'Under Review'],
            ['id' => 'RECOMMENDED', 'name' => 'Recommended'],
            ['id' => 'APPROVED', 'name' => 'Approved'],
            ['id' => 'FINANCE_VERIFIED', 'name' => 'Finance Verified'],
            ['id' => 'PAYMENT_PROCESSED', 'name' => 'Payment Processed'],
            ['id' => 'REJECTED', 'name' => 'Rejected'],
            ['id' => 'ARCHIVED', 'name' => 'Archived'],
        ];
    }

    public function getSummaryStatsProperty()
    {
        $baseQuery = function () {
            $query = \App\Models\TsAllowance::whereBetween('created_at', [$this->start_date, $this->end_date.' 23:59:59']);

            if ($this->department_filter !== 'ALL') {
                $query->where('department_id', $this->department_filter);
            }

            return $query;
        };

        return [
            'total_applications' => $baseQuery()->count(),
            'total_amount_requested' => $baseQuery()->sum('balance_due'),
            'approved_applications' => $baseQuery()->whereIn('status', ['APPROVED', 'FINANCE_VERIFIED', 'PAYMENT_PROCESSED'])->count(),
            'rejected_applications' => $baseQuery()->where('status', 'REJECTED')->count(),
            'pending_applications' => $baseQuery()->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW', 'RECOMMENDED'])->count(),
            'processed_applications' => $baseQuery()->where('status', 'PAYMENT_PROCESSED')->count(),
            'total_paid_usd' => $baseQuery()->where('status', 'PAYMENT_PROCESSED')->sum('amount_paid_usd'),

            // Amounts by stage
            'amount_draft' => $baseQuery()->where('status', 'DRAFT')->sum('balance_due'),
            'amount_pending_approval' => $baseQuery()->whereIn('status', ['SUBMITTED', 'RECOMMENDED', 'FINANCE_VERIFIED'])->sum('balance_due'),
            'amount_approved' => $baseQuery()->where('status', 'APPROVED')->sum('balance_due'),
            'amount_processed' => $baseQuery()->where('status', 'PAYMENT_PROCESSED')->sum('amount_paid_usd'),
            'amount_rejected' => $baseQuery()->where('status', 'REJECTED')->sum('balance_due'),

            // Counts by stage for context
            'count_draft' => $baseQuery()->where('status', 'DRAFT')->count(),
            'count_pending_approval' => $baseQuery()->whereIn('status', ['SUBMITTED', 'RECOMMENDED', 'FINANCE_VERIFIED'])->count(),
            'count_approved' => $baseQuery()->where('status', 'APPROVED')->count(),
            'count_processed' => $baseQuery()->where('status', 'PAYMENT_PROCESSED')->count(),
            'count_rejected' => $baseQuery()->where('status', 'REJECTED')->count(),
        ];
    }

    public function getPaymentsByCurrencyProperty()
    {
        $paidAllowances = \App\Models\TsAllowance::with('currency')
            ->whereBetween('created_at', [$this->start_date, $this->end_date.' 23:59:59'])
            ->where('status', 'PAYMENT_PROCESSED');

        if ($this->department_filter !== 'ALL') {
            $paidAllowances->where('department_id', $this->department_filter);
        }

        $paidAllowances = $paidAllowances->get();

        // Group by currency and sum amounts
        $byCurrency = [];

        foreach ($paidAllowances as $allowance) {
            $currencyName = $allowance->currency?->name ?? 'USD';
            $currencyId = $allowance->currency_id ?? 0;

            if (! isset($byCurrency[$currencyId])) {
                $byCurrency[$currencyId] = [
                    'currency_name' => $currencyName,
                    'total_original' => 0,
                    'total_usd' => 0,
                    'count' => 0,
                ];
            }

            $byCurrency[$currencyId]['total_original'] += $allowance->amount_paid_original ?? $allowance->amount_paid_usd;
            $byCurrency[$currencyId]['total_usd'] += $allowance->amount_paid_usd ?? 0;
            $byCurrency[$currencyId]['count']++;
        }

        return collect($byCurrency)->sortByDesc('total_usd');
    }

    public function headers(): array
    {
        return [
            ['key' => 'application_number', 'label' => 'Application #', 'class' => 'w-32'],
            ['key' => 'full_name', 'label' => 'Applicant', 'class' => 'w-48'],
            ['key' => 'department.name', 'label' => 'Department', 'class' => 'w-40'],
            ['key' => 'balance_due', 'label' => 'Amount', 'class' => 'w-32'],
            ['key' => 'status', 'label' => 'Status', 'class' => 'w-32'],
            ['key' => 'submission_date', 'label' => 'Submitted', 'class' => 'w-32'],
            ['key' => 'action', 'label' => '', 'class' => 'w-20'],
        ];
    }

    public function getExportDataProperty()
    {
        $query = \App\Models\TsAllowance::query()
            ->with(['department', 'currency'])
            ->whereBetween('created_at', [$this->start_date, $this->end_date.' 23:59:59']);

        if ($this->status_filter !== 'ALL') {
            $query->where('status', $this->status_filter);
        }

        if ($this->department_filter !== 'ALL') {
            $query->where('department_id', $this->department_filter);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('application_number', 'like', '%'.$this->search.'%')
                    ->orWhere('full_name', 'like', '%'.$this->search.'%')
                    ->orWhere('employee_number', 'like', '%'.$this->search.'%');
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function exportExcel()
    {
        $data = $this->exportData;
        $summaryStats = $this->summaryStats;
        $paymentsByCurrency = $this->paymentsByCurrency;

        $filename = 'ts_allowance_report_'.date('Y-m-d_His').'.xlsx';

        return Excel::download(new \App\Exports\TsAllowanceReportExport($data, $summaryStats, $paymentsByCurrency, $this->start_date, $this->end_date), $filename);
    }

    public function exportPdf()
    {
        $data = $this->exportData;
        $summaryStats = $this->summaryStats;
        $paymentsByCurrency = $this->paymentsByCurrency;

        $pdf = Pdf::loadView('exports.ts-allowance-report-pdf', [
            'allowances' => $data,
            'summaryStats' => $summaryStats,
            'paymentsByCurrency' => $paymentsByCurrency,
            'startDate' => $this->start_date,
            'endDate' => $this->end_date,
        ])->setPaper('a4', 'landscape');

        $filename = 'ts_allowance_report_'.date('Y-m-d_His').'.pdf';

        return response()->streamDownload(fn () => print ($pdf->output()), $filename);
    }

    public function render()
    {
        return view('livewire.admin.workflows.reports.ts-allowance-report', [
            'allowances' => $this->allowances,
            'departments' => $this->departments,
            'departmentOptions' => $this->departmentOptions,
            'statusOptions' => $this->statusOptions,
            'summaryStats' => $this->summaryStats,
            'paymentsByCurrency' => $this->paymentsByCurrency,
            'headers' => $this->headers(),
        ]);
    }
}
