<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Voucher Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 11px;
        }
        .summary-section {
            margin-bottom: 20px;
        }
        .summary-title {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .summary-grid {
            display: table;
            width: 100%;
        }
        .summary-item {
            display: table-cell;
            width: 11.11%;
            text-align: center;
            padding: 8px;
            background: #f8f9fa;
            border: 1px solid #ddd;
        }
        .summary-item .label {
            font-size: 9px;
            color: #666;
            margin-bottom: 3px;
        }
        .summary-item .value {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }
        .currency-section {
            margin-bottom: 20px;
        }
        .currency-table {
            width: 50%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .currency-table th, .currency-table td {
            border: 1px solid #ddd;
            padding: 6px 10px;
            text-align: left;
        }
        .currency-table th {
            background: #e2e8f0;
            font-weight: bold;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #ddd;
            padding: 5px 8px;
            text-align: left;
            font-size: 9px;
        }
        table.data-table th {
            background: #4a5568;
            color: white;
            font-weight: bold;
        }
        table.data-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .status-draft { background: #fef3c7; color: #92400e; }
        .status-prepared { background: #dbeafe; color: #1e40af; }
        .status-verified { background: #dbeafe; color: #1e40af; }
        .status-checked { background: #dbeafe; color: #1e40af; }
        .status-finance_approved { background: #d1fae5; color: #065f46; }
        .status-approved_payment_processed { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #666;
            text-align: center;
        }
        .amount {
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Payment Voucher Report</h1>
        <p>Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
        <p>Generated: {{ now()->format('d M Y H:i:s') }}</p>
    </div>

    <!-- Summary Statistics -->
    <div class="summary-section">
        <div class="summary-title">Summary Statistics</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total</div>
                <div class="value">{{ $summaryStats['total_vouchers'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Draft</div>
                <div class="value">{{ $summaryStats['draft_vouchers'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Prepared</div>
                <div class="value">{{ $summaryStats['prepared_vouchers'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Verified</div>
                <div class="value">{{ $summaryStats['verified_vouchers'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Checked</div>
                <div class="value">{{ $summaryStats['checked_vouchers'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Finance Approved</div>
                <div class="value">{{ $summaryStats['finance_approved'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Approved - Payment Processed</div>
                <div class="value">{{ $summaryStats['approved_payment_processed'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Rejected</div>
                <div class="value">{{ $summaryStats['rejected_vouchers'] }}</div>
            </div>
        </div>
    </div>

    <!-- Amount Summary -->
    <div class="summary-section">
        <div class="summary-title">Amount Summary</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total Amount</div>
                @if(isset($summaryStats['total_amounts_by_currency']) && count($summaryStats['total_amounts_by_currency']) > 0)
                    <div class="value" style="font-size: 10px;">
                        @foreach($summaryStats['total_amounts_by_currency'] as $currency => $amount)
                            <div>{{ $currency }} {{ number_format($amount, 2) }}</div>
                        @endforeach
                    </div>
                @else
                    <div class="value">${{ number_format($summaryStats['total_amount'], 2) }}</div>
                @endif
            </div>
            <div class="summary-item">
                <div class="label">Approved - Payment Processed</div>
                @if(isset($summaryStats['approved_amounts_by_currency']) && count($summaryStats['approved_amounts_by_currency']) > 0)
                    <div class="value" style="font-size: 10px;">
                        @foreach($summaryStats['approved_amounts_by_currency'] as $currency => $amount)
                            <div>{{ $currency }} {{ number_format($amount, 2) }}</div>
                        @endforeach
                    </div>
                @else
                    <div class="value">${{ number_format($summaryStats['amount_approved_payment_processed'], 2) }}</div>
                @endif
            </div>
            <div class="summary-item">
                <div class="label">Finance Approved</div>
                @if(isset($summaryStats['amounts_by_stage_and_currency']['FINANCE_APPROVED']) && count($summaryStats['amounts_by_stage_and_currency']['FINANCE_APPROVED']) > 0)
                    <div class="value" style="font-size: 10px;">
                        @foreach($summaryStats['amounts_by_stage_and_currency']['FINANCE_APPROVED'] as $currency => $amount)
                            <div>{{ $currency }} {{ number_format($amount, 2) }}</div>
                        @endforeach
                    </div>
                @else
                    <div class="value">${{ number_format($summaryStats['amount_finance_approved'], 2) }}</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Vouchers by Currency -->
    @if($vouchersByCurrency->count() > 0)
        <div class="currency-section">
            <div class="summary-title">Vouchers by Currency (Paid)</div>
            <table class="currency-table">
                <thead>
                    <tr>
                        <th>Currency</th>
                        <th>Count</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vouchersByCurrency as $currency)
                        <tr>
                            <td>{{ $currency['currency_name'] }}</td>
                            <td>{{ $currency['count'] }}</td>
                            <td>{{ number_format($currency['total_amount'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Vouchers Table -->
    <div class="summary-title">Vouchers ({{ $vouchers->count() }} records)</div>
    @foreach($vouchers as $voucher)
        <div style="margin-bottom: 20px; page-break-inside: avoid;">
            <table class="data-table" style="margin-bottom: 10px;">
                <thead>
                    <tr>
                        <th>Voucher #</th>
                        <th>Date</th>
                        <th>Bank Account</th>
                        <th>Amount</th>
                        <th>Currency</th>
                        <th>Status</th>
                        <th>Prepared By</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>{{ $voucher->voucher_number }}</strong></td>
                        <td>{{ $voucher->voucher_date?->format('d/m/y') ?? 'N/A' }}</td>
                        <td>{{ $voucher->bankAccount?->account_name ?? 'N/A' }}</td>
                        <td class="amount"><strong>{{ number_format($voucher->total_amount, 2) }}</strong></td>
                        <td>{{ $voucher->currency ?? 'N/A' }}</td>
                        <td>
                            @php
                                $statusClass = match($voucher->status) {
                                    'DRAFT' => 'status-draft',
                                    'PREPARED' => 'status-prepared',
                                    'VERIFIED' => 'status-verified',
                                    'CHECKED' => 'status-checked',
                                    'FINANCE_APPROVED' => 'status-finance_approved',
                                    'APPROVED_PAYMENT_PROCESSED' => 'status-approved_payment_processed',
                                    'REJECTED' => 'status-rejected',
                                    default => '',
                                };
                            @endphp
                            <span class="status-badge {{ $statusClass }}">{{ $voucher->status }}</span>
                        </td>
                        <td>{{ $voucher->preparedBy?->name ?? 'N/A' }}</td>
                        <td>{{ $voucher->created_at->format('d/m/y') }}</td>
                    </tr>
                </tbody>
            </table>

            @if($voucher->items->count() > 0)
                <div style="margin-left: 20px; margin-top: 10px;">
                    <div style="font-weight: bold; font-size: 10px; margin-bottom: 5px;">Voucher Items ({{ $voucher->items->count() }}):</div>
                    <table class="data-table" style="font-size: 8px;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Source Type</th>
                                <th>Payee Reg Number</th>
                                <th>Payee Name</th>
                                <th>Source ID</th>
                                <th>Description</th>
                                <th>Original Currency</th>
                                <th>Original Amount</th>
                                <th>Edited Amount</th>
                                <th>Partial Payment</th>
                                <th>Partial Amount</th>
                                <th>Exchange Rate</th>
                                <th>Payable Amount</th>
                                <th>Currency</th>
                                <th>Account Type</th>
                                <th>GL Code</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($voucher->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->source_type }}</td>
                                    <td>{{ $item->payee_regnumber ?? 'N/A' }}</td>
                                    <td>{{ $item->payee_name ?? 'N/A' }}</td>
                                    <td>{{ $item->source_id ?? 'N/A' }}</td>
                                    <td style="max-width: 150px;">{{ \Illuminate\Support\Str::limit($item->description, 40) }}</td>
                                    <td>{{ $item->original_currency ?? 'N/A' }}</td>
                                    <td class="amount">{{ $item->original_amount ? number_format($item->original_amount, 2) : 'N/A' }}</td>
                                    <td class="amount">{{ $item->edited_amount ? number_format($item->edited_amount, 2) : 'N/A' }}</td>
                                    <td>
                                        @php
                                            // Determine if this is a partial payment
                                            $baseAmount = $item->edited_amount ?? $item->original_amount ?? 0;
                                            $isPartial = false;
                                            
                                            if ($baseAmount > 0 && $item->payable_amount > 0) {
                                                if ($item->exchange_rate && $item->exchange_rate > 0) {
                                                    // Convert payable_amount back to original currency for comparison
                                                    $convertedPayable = $item->payable_amount / $item->exchange_rate;
                                                    // Allow small floating point differences (0.01)
                                                    $isPartial = $convertedPayable < ($baseAmount - 0.01);
                                                } else {
                                                    // Same currency, direct comparison
                                                    $isPartial = $item->payable_amount < ($baseAmount - 0.01);
                                                }
                                            }
                                        @endphp
                                        {{ $isPartial ? 'Yes' : 'No' }}
                                    </td>
                                    <td class="amount">
                                        @php
                                            // Calculate partial amount in original currency
                                            if ($isPartial && $item->payable_amount > 0) {
                                                if ($item->exchange_rate && $item->exchange_rate > 0) {
                                                    $partialAmount = $item->payable_amount / $item->exchange_rate;
                                                } else {
                                                    $partialAmount = $item->payable_amount;
                                                }
                                            } else {
                                                $partialAmount = null;
                                            }
                                        @endphp
                                        @if($partialAmount !== null)
                                            {{ number_format($partialAmount, 2) }} {{ $item->original_currency ?? '' }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="amount">{{ $item->exchange_rate ? number_format($item->exchange_rate, 4) : 'N/A' }}</td>
                                    <td class="amount"><strong>{{ number_format($item->payable_amount, 2) }}</strong></td>
                                    <td>{{ $voucher->currency ?? 'N/A' }}</td>
                                    <td>{{ $item->account_type ?? 'N/A' }}</td>
                                    <td>{{ $item->gl_code ?? 'N/A' }}</td>
                                </tr>
                                @if($item->amount_change_comment)
                                    <tr style="background: #f8f9fa;">
                                        <td colspan="16" style="font-size: 7px; padding: 3px;">
                                            <strong>Note:</strong> {{ $item->amount_change_comment }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="font-weight: bold; background: #e2e8f0;">
                                <td colspan="12" style="text-align: right;">Total:</td>
                                <td class="amount"><strong>{{ number_format($voucher->items->sum('payable_amount'), 2) }}</strong></td>
                                <td>{{ $voucher->currency ?? 'N/A' }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    @endforeach

    <div class="footer">
        <p>Payment Voucher Report - Generated by System</p>
    </div>
</body>
</html>

