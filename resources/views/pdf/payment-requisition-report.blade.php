<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Requisition Report</title>
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
            width: 12.5%;
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
        .status-submitted, .status-hod_recommended, .status-admin_reviewed, .status-admin_recommended { background: #dbeafe; color: #1e40af; }
        .status-awaiting_voucher { background: #d1fae5; color: #065f46; }
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
        <h1>Payment Requisition Report</h1>
        <p>Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
        <p>Generated: {{ now()->format('d M Y H:i:s') }}</p>
    </div>

    <!-- Summary Statistics -->
    <div class="summary-section">
        <div class="summary-title">Summary Statistics</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total</div>
                <div class="value">{{ $summaryStats['total_requisitions'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Draft</div>
                <div class="value">{{ $summaryStats['draft_requisitions'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Submitted</div>
                <div class="value">{{ $summaryStats['submitted_requisitions'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">HOD Recommended</div>
                <div class="value">{{ $summaryStats['hod_recommended'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Admin Reviewed</div>
                <div class="value">{{ $summaryStats['admin_reviewed'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Admin Recommended</div>
                <div class="value">{{ $summaryStats['admin_recommended'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Awaiting Voucher</div>
                <div class="value">{{ $summaryStats['awaiting_voucher'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Rejected</div>
                <div class="value">{{ $summaryStats['rejected_requisitions'] }}</div>
            </div>
        </div>
    </div>

    <!-- Amount Summary -->
    <div class="summary-section">
        <div class="summary-title">Amount Summary</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total Amount</div>
                <div class="value">${{ number_format($summaryStats['total_amount'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Draft Amount</div>
                <div class="value">${{ number_format($summaryStats['amount_draft'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Submitted Amount</div>
                <div class="value">${{ number_format($summaryStats['amount_submitted'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Awaiting Voucher</div>
                <div class="value">${{ number_format($summaryStats['amount_awaiting_voucher'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Rejected Amount</div>
                <div class="value">${{ number_format($summaryStats['amount_rejected'], 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Payments by Currency -->
    @if($paymentsByCurrency->count() > 0)
        <div class="currency-section">
            <div class="summary-title">Payments by Currency (Awaiting Voucher)</div>
            <table class="currency-table">
                <thead>
                    <tr>
                        <th>Currency</th>
                        <th>Count</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paymentsByCurrency as $currency)
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

    <!-- Requisitions Table -->
    <div class="summary-title">Requisitions ({{ $requisitions->count() }} records)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Reference #</th>
                <th>Purpose</th>
                <th>Department</th>
                <th>Amount</th>
                <th>Currency</th>
                <th>Status</th>
                <th>Created By</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requisitions as $requisition)
                <tr>
                    <td>{{ $requisition->reference_number }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($requisition->purpose, 40) }}</td>
                    <td>{{ $requisition->department?->name ?? 'N/A' }}</td>
                    <td class="amount">{{ number_format($requisition->total_amount, 2) }}</td>
                    <td>{{ $requisition->currency?->name ?? 'N/A' }}</td>
                    <td>
                        @php
                            $statusClass = match($requisition->status) {
                                'DRAFT' => 'status-draft',
                                'Submitted' => 'status-submitted',
                                'HOD_RECOMMENDED' => 'status-hod_recommended',
                                'ADMIN_REVIEWED' => 'status-admin_reviewed',
                                'ADMIN_RECOMMENDED' => 'status-admin_recommended',
                                'AWAITING_PAYMENT_VOUCHER' => 'status-awaiting_voucher',
                                'Rejected' => 'status-rejected',
                                default => '',
                            };
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ $requisition->status }}</span>
                    </td>
                    <td>{{ $requisition->createdBy?->name ?? 'N/A' }}</td>
                    <td>{{ $requisition->created_at->format('d/m/y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Payment Requisition Report - Generated by System</p>
    </div>
</body>
</html>

