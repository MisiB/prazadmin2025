<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Staff Welfare Loan Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-item .label {
            font-size: 9px;
            color: #666;
            margin-bottom: 5px;
        }
        .summary-item .value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #4a5568;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f7fafc;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: bold;
        }
        .status-draft { background-color: #e2e8f0; color: #4a5568; }
        .status-submitted { background-color: #bee3f8; color: #2c5282; }
        .status-hr-review { background-color: #bee3f8; color: #2c5282; }
        .status-finance-review { background-color: #bee3f8; color: #2c5282; }
        .status-ceo-approval { background-color: #bee3f8; color: #2c5282; }
        .status-approved { background-color: #c6f6d5; color: #22543d; }
        .status-rejected { background-color: #fed7d7; color: #742a2a; }
        .status-payment-processed { background-color: #c6f6d5; color: #22543d; }
        .status-awaiting-acknowledgement { background-color: #feebc8; color: #7c2d12; }
        .status-completed { background-color: #c6f6d5; color: #22543d; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #999;
        }
        .amount {
            text-align: right;
            font-weight: bold;
            color: #2d3748;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Staff Welfare Loan Report</h1>
        <p>Generated on {{ now()->format('F d, Y H:i:s') }}</p>
        <p>Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>
    </div>

    <div class="summary">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total Loans</div>
                <div class="value">{{ $summaryStats['total_loans'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Requested</div>
                <div class="value">${{ number_format($summaryStats['total_amount_requested'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Paid (ZIG)</div>
                <div class="value">{{ number_format($summaryStats['total_paid_zig'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Paid (USD)</div>
                <div class="value">${{ number_format($summaryStats['total_paid_usd'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Completed</div>
                <div class="value">{{ $summaryStats['completed_loans'] }}</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Loan Number</th>
                <th style="width: 15%;">Applicant</th>
                <th style="width: 12%;">Department</th>
                <th style="width: 10%;">Amount</th>
                <th style="width: 8%;">Period</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 10%;">Submitted</th>
                <th style="width: 10%;">Payment</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loans as $loan)
                <tr>
                    <td>
                        <strong>{{ $loan->loan_number }}</strong><br>
                        <small style="color: #718096;">{{ $loan->employee_number }}</small>
                    </td>
                    <td>
                        <strong>{{ $loan->full_name }}</strong><br>
                        <small style="color: #718096;">{{ $loan->job_title }}</small>
                    </td>
                    <td>{{ $loan->department->name ?? 'N/A' }}</td>
                    <td class="amount">${{ number_format($loan->loan_amount_requested, 2) }}</td>
                    <td>{{ $loan->repayment_period_months }} months</td>
                    <td>
                        @php
                            $statusClass = match($loan->status) {
                                'DRAFT' => 'status-draft',
                                'SUBMITTED' => 'status-submitted',
                                'HR_REVIEW' => 'status-hr-review',
                                'FINANCE_REVIEW' => 'status-finance-review',
                                'CEO_APPROVAL' => 'status-ceo-approval',
                                'APPROVED' => 'status-approved',
                                'REJECTED' => 'status-rejected',
                                'PAYMENT_PROCESSED' => 'status-payment-processed',
                                'AWAITING_ACKNOWLEDGEMENT' => 'status-awaiting-acknowledgement',
                                'COMPLETED' => 'status-completed',
                                default => 'status-draft',
                            };
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ $loan->status }}</span>
                    </td>
                    <td>{{ $loan->submission_date?->format('M d, Y') ?? 'N/A' }}</td>
                    <td>
                        @if($loan->amount_paid)
                            @php
                                $payment = $loan->payments->first();
                            @endphp
                            @if($payment && $payment->currency_id)
                                <small style="color: #4299e1;">{{ $payment->currency->name }}</small><br>
                                <strong>{{ number_format($payment->amount_paid_original, 2) }}</strong><br>
                                @if($payment->exchangerate_id)
                                    <small style="color: #718096;">Rate: {{ number_format($payment->exchange_rate_used, 4) }}</small><br>
                                @endif
                                <strong style="color: #38a169;">${{ number_format($payment->amount_paid_usd, 2) }}</strong>
                            @else
                                <strong>${{ number_format($loan->amount_paid, 2) }}</strong>
                            @endif
                            <br><small style="color: #718096;">{{ $loan->payment_method }}</small>
                        @else
                            <span style="color: #a0aec0;">Not paid</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is a system-generated report. For any discrepancies, please contact the HR department.</p>
        <p>&copy; {{ date('Y') }} Staff Welfare Loan Management System</p>
    </div>
</body>
</html>
