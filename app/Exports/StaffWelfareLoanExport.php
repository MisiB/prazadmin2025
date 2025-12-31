<?php

namespace App\Exports;

use App\Models\StaffWelfareLoan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StaffWelfareLoanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
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
        $query = StaffWelfareLoan::query()
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
            ->whereBetween('created_at', [$this->startDate, $this->endDate.' 23:59:59']);

        if ($this->statusFilter !== 'ALL') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->departmentFilter !== 'ALL') {
            $query->where('department_id', $this->departmentFilter);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('loan_number', 'like', '%'.$this->search.'%')
                    ->orWhere('full_name', 'like', '%'.$this->search.'%')
                    ->orWhere('employee_number', 'like', '%'.$this->search.'%');
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Loan Number',
            'Employee Number',
            'Full Name',
            'Job Title',
            'Department',
            'Date Joined',
            'Loan Amount Requested',
            'Repayment Period (Months)',
            'Loan Purpose',
            'Status',
            'Submission Date',
            'HR Review Date',
            'Employment Status',
            'Basic Salary',
            'Monthly Deduction',
            'Existing Loan Balance',
            'Monthly Repayment',
            'Amount Paid',
            'Payment Currency',
            'Exchange Rate Used',
            'Exchange Rate Set By',
            'Payment Method',
            'Payment Reference',
            'Payment Date',
            'Finance Officer',
            'Acknowledgement Statement',
            'Acceptance Date',
            'Created At',
            'Updated At',
        ];
    }

    public function map($loan): array
    {
        $payment = $loan->payments->first();

        return [
            $loan->loan_number,
            $loan->employee_number,
            $loan->full_name,
            $loan->job_title,
            $loan->department->name ?? 'N/A',
            $loan->date_joined?->format('Y-m-d'),
            $loan->loan_amount_requested,
            $loan->repayment_period_months,
            $loan->loan_purpose,
            $loan->status,
            $loan->submission_date?->format('Y-m-d H:i:s'),
            $loan->hr_review_date?->format('Y-m-d H:i:s'),
            $loan->employment_status ?? 'N/A',
            $loan->basic_salary ?? 0,
            $loan->monthly_deduction_amount ?? 0,
            $loan->existing_loan_balance ?? 0,
            $loan->monthly_repayment ?? 0,
            $payment ? ($payment->amount_paid_original ?? $loan->amount_paid ?? 0) : ($loan->amount_paid ?? 0),
            $payment && $payment->currency ? $payment->currency->name : 'USD',
            $payment && $payment->exchange_rate_used ? $payment->exchange_rate_used : 'N/A',
            $payment && $payment->exchangerate ? $payment->exchangerate->user->name : 'N/A',
            $loan->payment_method ?? 'N/A',
            $loan->payment_reference ?? 'N/A',
            $loan->payment_date?->format('Y-m-d'),
            $loan->financeOfficer->name ?? 'N/A',
            $loan->acknowledgement_of_debt_statement ?? 'N/A',
            $loan->acceptance_date?->format('Y-m-d H:i:s'),
            $loan->created_at->format('Y-m-d H:i:s'),
            $loan->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function title(): string
    {
        return 'Staff Welfare Loans';
    }
}
