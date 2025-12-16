<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Organization Dashboard Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 10pt;
            color: #1f2937;
            line-height: 1.6;
            padding: 25px;
            background-color: #f9fafb;
        }
        
        /* Header Styles */
        .header {
            text-align: center;
            margin-bottom: 35px;
            padding: 25px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header h1 {
            font-size: 28pt;
            font-weight: bold;
            margin-bottom: 12px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .header-info {
            font-size: 10pt;
            opacity: 0.95;
            margin-top: 8px;
            line-height: 1.8;
        }
        
        /* Section Styles */
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 18pt;
            font-weight: bold;
            color: #111827;
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
            position: relative;
        }
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 60px;
            height: 3px;
            background: #764ba2;
        }
        
        /* Metric Cards */
        .metrics-grid {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px;
            margin-bottom: 25px;
        }
        .metric-card {
            display: table-cell;
            width: 25%;
            padding: 20px 15px;
            text-align: center;
            border-radius: 12px;
            vertical-align: top;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .metric-blue { 
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); 
            color: white; 
        }
        .metric-green { 
            background: linear-gradient(135deg, #10b981 0%, #059669 100%); 
            color: white; 
        }
        .metric-purple { 
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); 
            color: white; 
        }
        .metric-orange { 
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); 
            color: white; 
        }
        .metric-label {
            font-size: 10pt;
            opacity: 0.95;
            margin-bottom: 10px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .metric-value {
            font-size: 32pt;
            font-weight: bold;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .metric-sub {
            font-size: 9pt;
            opacity: 0.9;
            font-weight: 500;
        }
        
        /* Card Styles */
        .card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        table th {
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 10pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }
        table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        table tbody tr:hover {
            background-color: #f3f4f6;
        }
        table td {
            padding: 12px 15px;
            font-size: 9.5pt;
            color: #374151;
        }
        
        /* Progress Bar Styles */
        .progress-bar {
            width: 100%;
            height: 24px;
            background-color: #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            margin: 8px 0;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .progress-fill {
            height: 100%;
            border-radius: 12px;
            text-align: center;
            line-height: 24px;
            color: white;
            font-size: 9pt;
            font-weight: bold;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .bg-green { background-color: #10b981; }
        .bg-yellow { background-color: #eab308; }
        .bg-red { background-color: #ef4444; }
        .bg-blue { background-color: #3b82f6; }
        .bg-purple { background-color: #8b5cf6; }
        
        /* Text Colors */
        .text-green { color: #10b981; font-weight: 600; }
        .text-yellow { color: #eab308; font-weight: 600; }
        .text-red { color: #ef4444; font-weight: 600; }
        .text-blue { color: #3b82f6; font-weight: 600; }
        .text-purple { color: #8b5cf6; font-weight: 600; }
        
        /* Utility Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        /* Health Score Styles */
        .health-score {
            text-align: center;
            padding: 30px 20px;
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            border-radius: 12px;
            margin-bottom: 25px;
            border: 2px solid #e5e7eb;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .health-score-value {
            font-size: 56pt;
            font-weight: bold;
            margin-bottom: 12px;
            line-height: 1;
        }
        .health-score-label {
            font-size: 16pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Factor Grid */
        .factor-grid {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px;
        }
        .factor-card {
            display: table-cell;
            width: 20%;
            padding: 18px 12px;
            background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
            border-radius: 10px;
            vertical-align: top;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .factor-name {
            font-weight: 600;
            font-size: 9.5pt;
            margin-bottom: 8px;
            color: #374151;
        }
        .factor-weight {
            font-size: 8pt;
            color: #6b7280;
            margin-bottom: 10px;
            font-weight: 500;
        }
        .factor-score {
            font-size: 22pt;
            font-weight: bold;
            margin: 12px 0;
        }
        .factor-contribution {
            font-size: 8.5pt;
            color: #6b7280;
            margin-top: 8px;
            font-weight: 500;
        }
        
        /* Risk Item Styles */
        .risk-item {
            padding: 18px 20px;
            margin-bottom: 12px;
            border-radius: 10px;
            border-left: 5px solid;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .risk-critical { 
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); 
            border-left-color: #ef4444; 
        }
        .risk-high { 
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); 
            border-left-color: #f59e0b; 
        }
        .risk-medium { 
            background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%); 
            border-left-color: #eab308; 
        }
        .risk-header {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .risk-badge {
            font-size: 8pt;
            padding: 4px 12px;
            background: #1f2937;
            color: white;
            border-radius: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .risk-factors {
            margin-left: 20px;
            font-size: 9.5pt;
        }
        .risk-factors li {
            margin-bottom: 6px;
            color: #374151;
        }
        
        /* Footer */
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 8.5pt;
            color: #6b7280;
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 8px;
        }
        
        /* Page Break Controls */
        @media print {
            .section {
                page-break-inside: avoid;
            }
            .card {
                page-break-inside: avoid;
            }
        }
        
        /* Badge Styles */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 8pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-success { background-color: #10b981; color: white; }
        .badge-warning { background-color: #eab308; color: white; }
        .badge-danger { background-color: #ef4444; color: white; }
        .badge-info { background-color: #3b82f6; color: white; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Organization Dashboard Report</h1>
        <div class="header-info">
            <strong>Generated:</strong> {{ $generatedAt }}<br>
            <strong>Period:</strong> {{ $startDate }} to {{ $endDate }} 
            <strong>| Filter:</strong> {{ ucfirst($filterType) }}
        </div>
    </div>

    <!-- Overall Key Metrics -->
    <div class="section">
        <div class="section-title">Executive Summary</div>
        <div class="metrics-grid">
            <div class="metric-card metric-blue">
                <div class="metric-label">Departments</div>
                <div class="metric-value">{{ $overallMetrics['total_departments'] }}</div>
            </div>
            <div class="metric-card metric-green">
                <div class="metric-label">Total Employees</div>
                <div class="metric-value">{{ $overallMetrics['total_employees'] }}</div>
            </div>
            <div class="metric-card metric-purple">
                <div class="metric-label">Total Tasks</div>
                <div class="metric-value">{{ $overallMetrics['total_tasks'] }}</div>
                <div class="metric-sub">{{ $overallMetrics['completion_rate'] }}% completed</div>
            </div>
            <div class="metric-card metric-orange">
                <div class="metric-label">Total Budget</div>
                <div class="metric-value">${{ number_format($overallMetrics['total_budget'], 0) }}</div>
                <div class="metric-sub">{{ $overallMetrics['percentage_spent'] }}% spent</div>
            </div>
        </div>
    </div>

    <!-- Organization Health Scorecard -->
    <div class="section card">
        <div class="section-title">Organization Health Scorecard</div>
        <div class="health-score">
            <div class="health-score-value text-{{ $organizationHealth['status_color'] }}">{{ $organizationHealth['overall_score'] }}%</div>
            <div class="health-score-label text-{{ $organizationHealth['status_color'] }}">{{ $organizationHealth['status'] }}</div>
        </div>
        <div class="factor-grid">
            @foreach($organizationHealth['factors'] as $factor)
            <div class="factor-card">
                <div class="factor-name">{{ $factor['name'] }}</div>
                <div class="factor-weight">Weight: {{ $factor['weight'] }}%</div>
                <div class="factor-score text-{{ $factor['status'] === 'good' ? 'green' : ($factor['status'] === 'fair' ? 'yellow' : 'red') }}">
                    {{ $factor['score'] }}%
                </div>
                <div class="progress-bar">
                    <div class="progress-fill bg-{{ $factor['status'] === 'good' ? 'green' : ($factor['status'] === 'fair' ? 'yellow' : 'red') }}" style="width: {{ $factor['score'] }}%">
                        @if($factor['score'] > 15){{ $factor['score'] }}%@endif
                    </div>
                </div>
                <div class="factor-contribution">
                    Contributes: {{ round($factor['score'] * $factor['weight'] / 100, 1) }} pts
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Task Approval Metrics -->
    <div class="section card">
        <div class="section-title">Task Approval Metrics</div>
        <table>
            <thead>
                <tr>
                    <th>Metric</th>
                    <th class="text-center">Count</th>
                    <th class="text-center">Rate</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Pending Approval</strong></td>
                    <td class="text-center font-bold">{{ $taskApprovalMetrics['pending_approval'] }}</td>
                    <td class="text-center">{{ $taskApprovalMetrics['pending_rate'] }}%</td>
                </tr>
                <tr>
                    <td><strong>Approved Tasks</strong></td>
                    <td class="text-center font-bold text-green">{{ $taskApprovalMetrics['approved_tasks'] }}</td>
                    <td class="text-center text-green">{{ $taskApprovalMetrics['approval_rate'] }}%</td>
                </tr>
                <tr>
                    <td><strong>Rejected Tasks</strong></td>
                    <td class="text-center font-bold text-red">{{ $taskApprovalMetrics['rejected_tasks'] }}</td>
                    <td class="text-center text-red">{{ $taskApprovalMetrics['rejection_rate'] }}%</td>
                </tr>
                <tr>
                    <td><strong>Average Approval Time</strong></td>
                    <td class="text-center font-bold">{{ $taskApprovalMetrics['avg_approval_days'] }} days</td>
                    <td class="text-center">({{ $taskApprovalMetrics['avg_approval_hours'] }} hours)</td>
                </tr>
            </tbody>
        </table>
        <div style="margin-top: 20px;">
            <div class="progress-bar">
                <div class="progress-fill bg-green" style="width: {{ $taskApprovalMetrics['approval_rate'] }}%">
                    @if($taskApprovalMetrics['approval_rate'] > 10){{ $taskApprovalMetrics['approval_rate'] }}%@endif
                </div>
                <div class="progress-fill bg-yellow" style="width: {{ $taskApprovalMetrics['pending_rate'] }}%">
                    @if($taskApprovalMetrics['pending_rate'] > 10){{ $taskApprovalMetrics['pending_rate'] }}%@endif
                </div>
                <div class="progress-fill bg-red" style="width: {{ $taskApprovalMetrics['rejection_rate'] }}%">
                    @if($taskApprovalMetrics['rejection_rate'] > 10){{ $taskApprovalMetrics['rejection_rate'] }}%@endif
                </div>
            </div>
        </div>
    </div>

    <!-- Department Breakdown -->
    <div class="section card">
        <div class="section-title">Department Breakdown</div>
        <table>
            <thead>
                <tr>
                    <th>Department</th>
                    <th class="text-center">Employees</th>
                    <th class="text-center">Tasks</th>
                    <th class="text-center">Completed</th>
                    <th class="text-center">Rate</th>
                    <th class="text-right">Budget</th>
                    <th class="text-right">Spent</th>
                    <th class="text-center">Util %</th>
                    <th class="text-center">Issues</th>
                </tr>
            </thead>
            <tbody>
                @foreach($departmentBreakdown as $dept)
                <tr>
                    <td><strong>{{ $dept['name'] }}</strong></td>
                    <td class="text-center">{{ $dept['employees'] }}</td>
                    <td class="text-center">{{ $dept['tasks']['total'] }}</td>
                    <td class="text-center">{{ $dept['tasks']['completed'] }}</td>
                    <td class="text-center">{{ number_format($dept['tasks']['completion_rate'], 2) }}%</td>
                    <td class="text-right">${{ number_format($dept['budget']['total'], 2) }}</td>
                    <td class="text-right">${{ number_format($dept['budget']['spent'], 2) }}</td>
                    <td class="text-center">{{ number_format($dept['budget']['percentage_spent'], 2) }}%</td>
                    <td class="text-center">{{ $dept['issues']['total'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Issue Resolution -->
    @if(isset($issueResolution))
    <div class="section card">
        <div class="section-title">Issue Resolution Metrics</div>
        <table>
            <thead>
                <tr>
                    <th>Metric</th>
                    <th class="text-center">Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Average Turnaround Time</strong></td>
                    <td class="text-center">{{ $issueResolution['avg_turnaround_days'] }} days ({{ $issueResolution['avg_turnaround_hours'] }} hours)</td>
                </tr>
                <tr>
                    <td><strong>Resolution Rate</strong></td>
                    <td class="text-center font-bold">{{ $issueResolution['resolution_rate'] }}%</td>
                </tr>
                <tr>
                    <td><strong>Total Issues</strong></td>
                    <td class="text-center">{{ $issueResolution['total_issues'] }}</td>
                </tr>
                <tr>
                    <td><strong>Resolved Issues</strong></td>
                    <td class="text-center text-green">{{ $issueResolution['total_resolved'] }}</td>
                </tr>
                <tr>
                    <td><strong>Open Issues</strong></td>
                    <td class="text-center text-red">{{ $issueResolution['open_issues'] }}</td>
                </tr>
                <tr>
                    <td><strong>In Progress</strong></td>
                    <td class="text-center text-yellow">{{ $issueResolution['in_progress_issues'] }}</td>
                </tr>
                <tr>
                    <td><strong>Closed Issues</strong></td>
                    <td class="text-center">{{ $issueResolution['closed_issues'] }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <!-- Risk Indicators -->
    @if(isset($riskIndicators) && count($riskIndicators) > 0)
    <div class="section card">
        <div class="section-title">Risk Indicators</div>
        @foreach($riskIndicators as $risk)
        <div class="risk-item risk-{{ $risk['risk_level'] }}">
            <div class="risk-header">
                <span>{{ $risk['department'] }}</span>
                <span class="risk-badge">{{ strtoupper($risk['risk_level']) }}</span>
            </div>
            <ul class="risk-factors">
                @foreach($risk['factors'] as $factor)
                <li>{{ $factor }}</li>
                @endforeach
            </ul>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Supervisor Approval Rates -->
    @if(isset($supervisorApprovalRates) && count($supervisorApprovalRates) > 0)
    <div class="section card">
        <div class="section-title">Supervisor Approval Rates</div>
        <table>
            <thead>
                <tr>
                    <th>Supervisor</th>
                    <th>Department</th>
                    <th class="text-center">Reviewed</th>
                    <th class="text-center">Approved</th>
                    <th class="text-center">Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_slice($supervisorApprovalRates, 0, 10) as $supervisor)
                <tr>
                    <td><strong>{{ $supervisor['approver_name'] }}</strong></td>
                    <td>{{ $supervisor['department'] }}</td>
                    <td class="text-center">{{ $supervisor['total_reviewed'] }}</td>
                    <td class="text-center text-green font-bold">{{ $supervisor['approved'] }}</td>
                    <td class="text-center font-bold {{ $supervisor['approval_rate'] >= 80 ? 'text-green' : ($supervisor['approval_rate'] >= 60 ? 'text-yellow' : 'text-red') }}">
                        {{ $supervisor['approval_rate'] }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Workplan Progress -->
    @if(isset($workplanProgress))
    <div class="section card">
        <div class="section-title">Strategic Goals & KPI Tracking</div>
        <table>
            <thead>
                <tr>
                    <th>Metric</th>
                    <th class="text-center">Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Total Workplans</strong></td>
                    <td class="text-center">{{ $workplanProgress['total_workplans'] }}</td>
                </tr>
                <tr>
                    <td><strong>Approved Workplans</strong></td>
                    <td class="text-center text-green">{{ $workplanProgress['approved_workplans'] }}</td>
                </tr>
                <tr>
                    <td><strong>Completion Rate</strong></td>
                    <td class="text-center font-bold">{{ $workplanProgress['completion_percentage'] }}%</td>
                </tr>
                <tr>
                    <td><strong>Tasks Linked</strong></td>
                    <td class="text-center">{{ $workplanProgress['linked_tasks_percentage'] }}% ({{ $workplanProgress['linked_tasks_count'] ?? 0 }} tasks)</td>
                </tr>
                <tr>
                    <td><strong>Tasks Unlinked</strong></td>
                    <td class="text-center">{{ $workplanProgress['unlinked_tasks_percentage'] ?? 0 }}% ({{ $workplanProgress['unlinked_tasks_count'] ?? 0 }} tasks)</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <!-- Productivity Metrics -->
    @if(isset($productivityMetrics))
    <div class="section card">
        <div class="section-title">Productivity Metrics</div>
        <table>
            <thead>
                <tr>
                    <th>Metric</th>
                    <th class="text-center">Value</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($productivityMetrics['tasks_per_employee']))
                <tr>
                    <td><strong>Tasks per Employee</strong></td>
                    <td class="text-center">{{ number_format($productivityMetrics['tasks_per_employee'], 2) }}</td>
                </tr>
                @endif
                @if(isset($productivityMetrics['completions_per_employee']))
                <tr>
                    <td><strong>Completions per Employee</strong></td>
                    <td class="text-center">{{ number_format($productivityMetrics['completions_per_employee'], 2) }}</td>
                </tr>
                @endif
                @if(isset($productivityMetrics['cost_per_task_completion']))
                <tr>
                    <td><strong>Cost per Task Completion</strong></td>
                    <td class="text-center">${{ number_format($productivityMetrics['cost_per_task_completion'], 2) }}</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p><strong>This report was generated on {{ $generatedAt }}</strong></p>
        <p>Period: {{ $startDate }} to {{ $endDate }} | Filter: {{ ucfirst($filterType) }}</p>
        <p style="margin-top: 10px; font-size: 8pt; color: #9ca3af;">Organization Dashboard - Confidential Report</p>
    </div>
</body>
</html>
