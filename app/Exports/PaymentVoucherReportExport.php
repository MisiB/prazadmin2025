<?php

namespace App\Exports;

use App\Models\PaymentVoucher;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentVoucherReportExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $startDate;

    protected $endDate;

    protected $statusFilter;

    protected $search;

    public function __construct($startDate, $endDate, $statusFilter = 'ALL', $search = '')
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->statusFilter = $statusFilter;
        $this->search = $search;
    }

    public function collection()
    {
        $query = PaymentVoucher::query()
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
            ->whereBetween('created_at', [$this->startDate, $this->endDate.' 23:59:59']);

        if ($this->statusFilter !== 'ALL') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('voucher_number', 'like', '%'.$this->search.'%');
            });
        }

        $vouchers = $query->orderBy('created_at', 'desc')->get();

        // Flatten to include one row per voucher item
        $flattened = new Collection;
        foreach ($vouchers as $voucher) {
            if ($voucher->items->count() > 0) {
                foreach ($voucher->items as $index => $item) {
                    $flattened->push((object) [
                        'voucher' => $voucher,
                        'item' => $item,
                        'item_index' => $index + 1,
                    ]);
                }
            } else {
                // Include voucher even if no items
                $flattened->push((object) [
                    'voucher' => $voucher,
                    'item' => null,
                    'item_index' => 'N/A',
                ]);
            }
        }

        return $flattened;
    }

    public function title(): string
    {
        return 'Payment Vouchers';
    }

    public function headings(): array
    {
        return [
            'Voucher Number',
            'Voucher Date',
            'Bank Account',
            'Currency',
            'Exchange Rate',
            'Total Amount',
            'Status',
            'Prepared By',
            'Verified By',
            'Checked By',
            'Finance Approved By',
            'CEO Approved By',
            'Rejection Reason',
            'Workflow',
            'Item #',
            'Source Type',
            'Payee Registration Number',
            'Payee Name',
            'Source ID',
            'Description',
            'Original Currency',
            'Original Amount',
            'Edited Amount',
            'Partial Payment',
            'Partial Amount',
            'Amount Change Comment',
            'Item Exchange Rate',
            'Payable Amount',
            'Payable Amount Currency',
            'Account Type',
            'GL Code',
            'Created At',
            'Updated At',
        ];
    }

    public function map($row): array
    {
        $voucher = $row->voucher;
        $item = $row->item;

        return [
            $voucher->voucher_number,
            $voucher->voucher_date?->format('Y-m-d'),
            $voucher->bankAccount?->account_name ?? 'N/A',
            $voucher->currency ?? 'N/A',
            $voucher->exchange_rate ?? 'N/A',
            $voucher->total_amount,
            $voucher->status,
            $voucher->preparedBy?->name ?? 'N/A',
            $voucher->verifiedBy?->name ?? 'N/A',
            $voucher->checkedBy?->name ?? 'N/A',
            $voucher->financeApprovedBy?->name ?? 'N/A',
            $voucher->ceoApprovedBy?->name ?? 'N/A',
            $voucher->rejection_reason ?? 'N/A',
            $voucher->workflow?->name ?? 'N/A',
            $row->item_index ?? 'N/A',
            $item?->source_type ?? 'N/A',
            $item?->payee_regnumber ?? 'N/A',
            $item?->payee_name ?? 'N/A',
            $item?->source_id ?? 'N/A',
            $item?->description ?? 'N/A',
            $item?->original_currency ?? 'N/A',
            $item?->original_amount ?? 'N/A',
            $item?->edited_amount ?? 'N/A',
            $this->isPartialPayment($item),
            $this->getPartialAmount($item),
            $item?->amount_change_comment ?? 'N/A',
            $item?->exchange_rate ?? 'N/A',
            $item?->payable_amount ?? 'N/A',
            $voucher->currency ?? 'N/A',
            $item?->account_type ?? 'N/A',
            $item?->gl_code ?? 'N/A',
            $voucher->created_at->format('Y-m-d H:i:s'),
            $voucher->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    protected function isPartialPayment($item)
    {
        if (! $item) {
            return 'N/A';
        }

        $baseAmount = $item->edited_amount ?? $item->original_amount ?? 0;
        
        if ($baseAmount <= 0 || ! $item->payable_amount || $item->payable_amount <= 0) {
            return 'No';
        }

        // Check if payable amount is less than base amount
        if ($item->exchange_rate && $item->exchange_rate > 0) {
            // Convert payable_amount back to original currency for comparison
            $convertedPayable = $item->payable_amount / $item->exchange_rate;
            // Allow small floating point differences (0.01)
            return ($convertedPayable < ($baseAmount - 0.01)) ? 'Yes' : 'No';
        } else {
            // Same currency, direct comparison
            return ($item->payable_amount < ($baseAmount - 0.01)) ? 'Yes' : 'No';
        }
    }

    protected function getPartialAmount($item)
    {
        if (! $item) {
            return 'N/A';
        }

        $baseAmount = $item->edited_amount ?? $item->original_amount ?? 0;
        $isPartial = false;
        
        if ($baseAmount > 0 && $item->payable_amount > 0) {
            if ($item->exchange_rate && $item->exchange_rate > 0) {
                $convertedPayable = $item->payable_amount / $item->exchange_rate;
                $isPartial = $convertedPayable < ($baseAmount - 0.01);
            } else {
                $isPartial = $item->payable_amount < ($baseAmount - 0.01);
            }
        }

        if (! $isPartial) {
            return 'N/A';
        }

        // Calculate partial amount in original currency
        if ($item->exchange_rate && $item->exchange_rate > 0) {
            $partialAmount = $item->payable_amount / $item->exchange_rate;
        } else {
            $partialAmount = $item->payable_amount;
        }

        $currency = $item->original_currency ?? 'N/A';

        return $currency !== 'N/A' ? $currency.' '.number_format($partialAmount, 2) : number_format($partialAmount, 2);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E2E8F0']]],
        ];
    }
}
