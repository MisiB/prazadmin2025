<?php

namespace App\Livewire\Admin\Workflows\Reports;

use App\Exports\PaymentVoucherReportExport;
use App\Interfaces\services\ipaymentvoucherService;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Mary\Traits\Toast;

class PaymentVoucherReport extends Component
{
    use Toast, WithPagination;

    public $breadcrumbs = [];

    public $start_date;

    public $end_date;

    public $status_filter = 'ALL';

    public $search = '';

    public $expandedVouchers = [];

    protected $paymentvoucherService;

    public function mount()
    {
        $this->authorize('report.payment.voucher.access');

        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Payment Voucher Report'],
        ];

        // Default to current year
        $this->start_date = date('Y-01-01');
        $this->end_date = date('Y-m-d');
    }

    public function boot(ipaymentvoucherService $paymentvoucherService)
    {
        $this->paymentvoucherService = $paymentvoucherService;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
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
        $this->search = '';
        $this->resetPage();
        $this->success('Filters reset successfully');
    }

    public function getVouchersProperty()
    {
        $query = \App\Models\PaymentVoucher::query()
            ->with([
                'workflow',
                'preparedBy',
                'verifiedBy',
                'checkedBy',
                'financeApprovedBy',
                'ceoApprovedBy',
                'bankAccount',
                'items',
            ])
            ->whereBetween('created_at', [$this->start_date, $this->end_date.' 23:59:59']);

        // Status filter
        if ($this->status_filter !== 'ALL') {
            $query->where('status', $this->status_filter);
        }

        // Search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('voucher_number', 'like', '%'.$this->search.'%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    public function getStatusOptionsProperty()
    {
        return [
            ['id' => 'ALL', 'name' => 'All Statuses'],
            ['id' => 'DRAFT', 'name' => 'Draft'],
            ['id' => 'PREPARED', 'name' => 'Prepared'],
            ['id' => 'VERIFIED', 'name' => 'Verified'],
            ['id' => 'CHECKED', 'name' => 'Checked'],
            ['id' => 'FINANCE_APPROVED', 'name' => 'Finance Approved'],
            ['id' => 'CEO_APPROVED', 'name' => 'CEO Approved'],
            ['id' => 'REJECTED', 'name' => 'Rejected'],
            ['id' => 'PAID', 'name' => 'Paid'],
        ];
    }

    public function getSummaryStatsProperty()
    {
        $baseQuery = function () {
            return \App\Models\PaymentVoucher::whereBetween('created_at', [$this->start_date, $this->end_date.' 23:59:59']);
        };

        return [
            'total_vouchers' => $baseQuery()->count(),
            'total_amount' => $baseQuery()->sum('total_amount'),
            'draft_vouchers' => $baseQuery()->where('status', 'DRAFT')->count(),
            'prepared_vouchers' => $baseQuery()->where('status', 'PREPARED')->count(),
            'verified_vouchers' => $baseQuery()->where('status', 'VERIFIED')->count(),
            'checked_vouchers' => $baseQuery()->where('status', 'CHECKED')->count(),
            'finance_approved' => $baseQuery()->where('status', 'FINANCE_APPROVED')->count(),
            'ceo_approved' => $baseQuery()->where('status', 'CEO_APPROVED')->count(),
            'paid_vouchers' => $baseQuery()->where('status', 'PAID')->count(),
            'rejected_vouchers' => $baseQuery()->where('status', 'REJECTED')->count(),
            'amount_draft' => $baseQuery()->where('status', 'DRAFT')->sum('total_amount'),
            'amount_prepared' => $baseQuery()->where('status', 'PREPARED')->sum('total_amount'),
            'amount_verified' => $baseQuery()->where('status', 'VERIFIED')->sum('total_amount'),
            'amount_checked' => $baseQuery()->where('status', 'CHECKED')->sum('total_amount'),
            'amount_finance_approved' => $baseQuery()->where('status', 'FINANCE_APPROVED')->sum('total_amount'),
            'amount_ceo_approved' => $baseQuery()->where('status', 'CEO_APPROVED')->sum('total_amount'),
            'amount_paid' => $baseQuery()->where('status', 'PAID')->sum('total_amount'),
            'amount_rejected' => $baseQuery()->where('status', 'REJECTED')->sum('total_amount'),
        ];
    }

    public function getVouchersByCurrencyProperty()
    {
        $vouchers = \App\Models\PaymentVoucher::whereBetween('created_at', [$this->start_date, $this->end_date.' 23:59:59'])
            ->where('status', 'PAID')
            ->get();

        // Group by currency and sum amounts
        $byCurrency = [];

        foreach ($vouchers as $voucher) {
            $currencyName = $voucher->currency ?? 'USD';
            $currencyId = $currencyName;

            if (! isset($byCurrency[$currencyId])) {
                $byCurrency[$currencyId] = [
                    'currency_name' => $currencyName,
                    'total_amount' => 0,
                    'count' => 0,
                ];
            }

            $byCurrency[$currencyId]['total_amount'] += $voucher->total_amount ?? 0;
            $byCurrency[$currencyId]['count']++;
        }

        return collect($byCurrency)->sortByDesc('total_amount');
    }

    public function exportToExcel()
    {
        $this->authorize('report.payment.voucher.export');

        $fileName = 'payment-vouchers-'.date('Y-m-d-His').'.xlsx';

        $this->success('Generating Excel report...');

        return Excel::download(
            new PaymentVoucherReportExport(
                $this->start_date,
                $this->end_date,
                $this->status_filter,
                $this->search
            ),
            $fileName
        );
    }

    public function exportToPdf()
    {
        $this->authorize('report.payment.voucher.export');

        $vouchers = \App\Models\PaymentVoucher::query()
            ->with([
                'workflow',
                'preparedBy',
                'verifiedBy',
                'checkedBy',
                'financeApprovedBy',
                'ceoApprovedBy',
                'bankAccount',
                'items',
            ])
            ->whereBetween('created_at', [$this->start_date, $this->end_date.' 23:59:59']);

        if ($this->status_filter !== 'ALL') {
            $vouchers->where('status', $this->status_filter);
        }

        if ($this->search) {
            $vouchers->where(function ($q) {
                $q->where('voucher_number', 'like', '%'.$this->search.'%');
            });
        }

        $vouchers = $vouchers->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('pdf.payment-voucher-report', [
            'vouchers' => $vouchers,
            'summaryStats' => $this->summaryStats,
            'vouchersByCurrency' => $this->vouchersByCurrency,
            'startDate' => $this->start_date,
            'endDate' => $this->end_date,
        ])->setPaper('a4', 'landscape');

        $fileName = 'payment-vouchers-'.date('Y-m-d-His').'.pdf';

        $this->success('Generating PDF report...');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName);
    }

    public function headers(): array
    {
        return [
            ['key' => 'voucher_number', 'label' => 'Voucher #', 'class' => 'w-32'],
            ['key' => 'voucher_date', 'label' => 'Date', 'class' => 'w-32'],
            ['key' => 'total_amount', 'label' => 'Amount', 'class' => 'w-32'],
            ['key' => 'currency', 'label' => 'Currency', 'class' => 'w-24'],
            ['key' => 'status', 'label' => 'Status', 'class' => 'w-32'],
            ['key' => 'prepared_by', 'label' => 'Prepared By', 'class' => 'w-40'],
            ['key' => 'created_at', 'label' => 'Created', 'class' => 'w-32'],
            ['key' => 'action', 'label' => '', 'class' => 'w-20'],
        ];
    }

    public function render()
    {
        return view('livewire.admin.workflows.reports.payment-voucher-report', [
            'vouchers' => $this->vouchers,
            'statusOptions' => $this->statusOptions,
            'summaryStats' => $this->summaryStats,
            'vouchersByCurrency' => $this->vouchersByCurrency,
            'headers' => $this->headers(),
        ]);
    }
}
