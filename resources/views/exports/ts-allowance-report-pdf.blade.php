<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>T&S Allowance Report</title>
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
            width: 16.66%;
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
        .status-submitted, .status-recommended, .status-finance_verified { background: #dbeafe; color: #1e40af; }
        .status-approved { background: #d1fae5; color: #065f46; }
        .status-payment_processed { background: #a7f3d0; color: #047857; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #666;
            text-align: center;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>T&S Allowance Report</h1>
        <p>Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
        <p>Generated: {{ now()->format('d M Y H:i:s') }}</p>
    </div>

    <!-- Summary Statistics -->
    <div class="summary-section">
        <div class="summary-title">Summary Statistics</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total Applications</div>
                <div class="value">{{ $summaryStats['total_applications'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Draft</div>
                <div class="value">${{ number_format($summaryStats['amount_draft'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">In Approval</div>
                <div class="value">${{ number_format($summaryStats['amount_pending_approval'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Approved</div>
                <div class="value">${{ number_format($summaryStats['amount_approved'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Paid</div>
                <div class="value">${{ number_format($summaryStats['amount_processed'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Rejected</div>
                <div class="value">${{ number_format($summaryStats['amount_rejected'], 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Payments by Currency -->
    @if($paymentsByCurrency->count() > 0)
        <div class="currency-section">
            <div class="summary-title">Payments by Currency</div>
            <table class="currency-table">
                <thead>
                    <tr>
                        <th>Currency</th>
                        <th>Count</th>
                        <th>Amount</th>
                        <th>USD Equivalent</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paymentsByCurrency as $currency)
                        <tr>
                            <td>{{ $currency['currency_name'] }}</td>
                            <td>{{ $currency['count'] }}</td>
                            <td>{{ number_format($currency['total_original'], 2) }}</td>
                            <td>${{ number_format($currency['total_usd'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr style="font-weight: bold; background: #e2e8f0;">
                        <td colspan="3">Total (USD)</td>
                        <td>${{ number_format($summaryStats['total_paid_usd'] ?? 0, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    <!-- Applications Table -->
    <div class="summary-title">Applications ({{ $allowances->count() }} records)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Application #</th>
                <th>Applicant</th>
                <th>Department</th>
                <th>Trip Dates</th>
                <th>Days</th>
                <th>Amount Due</th>
                <th>Status</th>
                <th>Payment Type</th>
                <th>Payment Details</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allowances as $allowance)
                @php
                    $isSplitPayment = $allowance->payment_notes && str_contains($allowance->payment_notes, 'Split Payment:');
                    $splitDetails = '';
                    if ($isSplitPayment) {
                        $lines = explode("\n", $allowance->payment_notes);
                        foreach ($lines as $line) {
                            if (str_contains($line, 'Split Payment:')) {
                                $splitDetails = trim(str_replace('Split Payment:', '', $line));
                                break;
                            }
                        }
                    }
                @endphp
                <tr>
                    <td>{{ $allowance->application_number }}</td>
                    <td>{{ $allowance->full_name }}</td>
                    <td>{{ $allowance->department?->name ?? 'N/A' }}</td>
                    <td>{{ $allowance->trip_start_date?->format('d/m/y') }} - {{ $allowance->trip_end_date?->format('d/m/y') }}</td>
                    <td>{{ $allowance->number_of_days }}</td>
                    <td>${{ number_format($allowance->balance_due, 2) }}</td>
                    <td>
                        @php
                            $statusClass = match($allowance->status) {
                                'DRAFT' => 'status-draft',
                                'SUBMITTED', 'RECOMMENDED', 'FINANCE_VERIFIED' => 'status-submitted',
                                'APPROVED' => 'status-approved',
                                'PAYMENT_PROCESSED' => 'status-payment_processed',
                                'REJECTED' => 'status-rejected',
                                default => '',
                            };
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ $allowance->status }}</span>
                    </td>
                    <td>
                        @if($allowance->status === 'PAYMENT_PROCESSED')
                            @if($isSplitPayment)
                                <span style="background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 3px; font-weight: bold;">SPLIT</span>
                            @else
                                <span style="background: #d1fae5; color: #065f46; padding: 2px 6px; border-radius: 3px;">SINGLE</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td style="font-size: 8px;">
                        @if($allowance->status === 'PAYMENT_PROCESSED')
                            @if($isSplitPayment)
                                {{ $splitDetails }}
                            @else
                                {{ $allowance->currency?->name ?? 'USD' }}: {{ number_format($allowance->amount_paid_original ?? $allowance->amount_paid_usd ?? 0, 2) }}
                            @endif
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>T&S Allowance Report - Generated by System</p>
    </div>
</body>
</html>

