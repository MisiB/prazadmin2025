<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Requisition Report</title>
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
            grid-template-columns: repeat(4, 1fr);
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
        .status-awaiting { background-color: #bee3f8; color: #2c5282; }
        .status-recommended { background-color: #c6f6d5; color: #22543d; }
        .status-approved { background-color: #c6f6d5; color: #22543d; }
        .status-rejected { background-color: #fed7d7; color: #742a2a; }
        .status-awarded { background-color: #d1fae5; color: #065f46; }
        .status-completed { background-color: #c6f6d5; color: #22543d; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Purchase Requisition Report</h1>
        <p>Generated on {{ now()->format('F d, Y H:i:s') }}</p>
        <p>Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>
    </div>

    <div class="summary">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total Requisitions</div>
                <div class="value">{{ $summaryStats['total_requisitions'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Draft</div>
                <div class="value">{{ $summaryStats['draft_requisitions'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Approved</div>
                <div class="value">{{ $summaryStats['approved_requisitions'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Completed</div>
                <div class="value">{{ $summaryStats['completed_requisitions'] }}</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">PR Number</th>
                <th style="width: 15%;">Description</th>
                <th style="width: 12%;">Department</th>
                <th style="width: 8%;">Quantity</th>
                <th style="width: 12%;">Purpose</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 12%;">Requested By</th>
                <th style="width: 10%;">Created</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requisitions as $requisition)
                <tr>
                    <td><strong>{{ $requisition->prnumber }}</strong></td>
                    <td>{{ \Illuminate\Support\Str::limit($requisition->description, 50) }}</td>
                    <td>{{ $requisition->department->name ?? 'N/A' }}</td>
                    <td>{{ $requisition->quantity }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($requisition->purpose, 30) }}</td>
                    <td>
                        @php
                            $statusClass = match($requisition->status) {
                                'DRAFT' => 'status-draft',
                                'AWAITING_RECOMMENDATION' => 'status-awaiting',
                                'RECOMMENDED' => 'status-recommended',
                                'APPROVED' => 'status-approved',
                                'REJECTED' => 'status-rejected',
                                'AWARDED' => 'status-awarded',
                                'COMPLETED' => 'status-completed',
                                default => 'status-draft',
                            };
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ $requisition->status }}</span>
                    </td>
                    <td>{{ $requisition->requestedby->name ?? 'N/A' }}</td>
                    <td>{{ $requisition->created_at->format('M d, Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is a system-generated report. For any discrepancies, please contact the procurement department.</p>
        <p>&copy; {{ date('Y') }} Purchase Requisition Management System</p>
    </div>
</body>
</html>

