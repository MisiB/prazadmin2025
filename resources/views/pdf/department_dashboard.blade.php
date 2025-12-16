<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $departmentName }} Dashboard Report</title>
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
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border-radius: 12px;
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header h1 {
            font-size: 24pt;
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
            font-size: 16pt;
            font-weight: bold;
            color: #111827;
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 3px solid #3b82f6;
            position: relative;
        }
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 60px;
            height: 3px;
            background: #1d4ed8;
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
        .metric-red { 
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); 
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
            font-size: 28pt;
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
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
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
        table td {
            padding: 12px 15px;
            font-size: 9.5pt;
            color: #374151;
        }
        
        /* Progress Bar Styles */
        .progress-bar {
            width: 100%;
            height: 20px;
            background-color: #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            margin: 8px 0;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .progress-fill {
            height: 100%;
            border-radius: 10px;
            text-align: center;
            line-height: 20px;
            color: white;
            font-size: 9pt;
            font-weight: bold;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
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
            padding: 25px 20px;
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            border-radius: 12px;
            margin-bottom: 25px;
            border: 2px solid #e5e7eb;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .health-score-value {
            font-size: 48pt;
            font-weight: bold;
            margin-bottom: 10px;
            line-height: 1;
        }
        .health-score-label {
            font-size: 14pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Factor Grid */
        .factor-grid {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px;
        }
        .factor-card {
            display: table-cell;
            width: 20%;
            padding: 15px 10px;
            background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
            border-radius: 10px;
            vertical-align: top;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        .factor-name {
            font-weight: 600;
            font-size: 9pt;
            margin-bottom: 8px;
            color: #374151;
        }
        .factor-weight {
            font-size: 8pt;
            color: #6b7280;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .factor-score {
            font-size: 20pt;
            font-weight: bold;
            margin: 8px 0;
        }
        
        /* Priority Badge Styles */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 8pt;
            font-weight: 600;
        }
        .badge-red { background-color: #fee2e2; color: #dc2626; }
        .badge-yellow { background-color: #fef3c7; color: #d97706; }
        .badge-green { background-color: #d1fae5; color: #059669; }
        .badge-blue { background-color: #dbeafe; color: #2563eb; }
        
        /* Alert Styles */
        .alert-item {
            padding: 15px;
            margin-bottom: 12px;
            border-radius: 10px;
            border-left: 4px solid;
        }
        .alert-red { 
            background-color: #fef2f2; 
            border-left-color: #ef4444; 
        }
        .alert-yellow { 
            background-color: #fffbeb; 
            border-left-color: #f59e0b; 
        }
        .alert-orange { 
            background-color: #fff7ed; 
            border-left-color: #ea580c; 
        }
        
        /* Footer */
        .footer {
            margin-top: 40px;
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
        
        /* Small Card Grid */
        .small-metrics-grid {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            margin-bottom: 20px;
        }
        .small-metric-card {
            display: table-cell;
            width: 20%;
            padding: 15px 10px;
            text-align: center;
            border-radius: 10px;
            vertical-align: top;
        }
        .small-metric-value {
            font-size: 20pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .small-metric-label {
            font-size: 8pt;
            color: #6b7280;
        }
        
        /* Trend Indicator */
        .trend-up { color: #10b981; }
        .trend-down { color: #ef4444; }
        .trend-neutral { color: #6b7280; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ $departmentName }} Dashboard Report</h1>
        <div class="header-info">
            <strong>Generated:</strong> {{ $generatedAt }}<br>
            <strong>Period:</strong> {{ $startDate }} to {{ $endDate }}
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="section">
        <div class="section-title">Key Metrics Overview</div>
        <div class="metrics-grid">
            <div class="metric-card metric-blue">
                <div class="metric-label">Team Members</div>
                <div class="metric-value">{{ $departmentUsersCount }}</div>
            </div>
            <div class="metric-card metric-green">
                <div class="metric-label">Total Tasks</div>
                <div class="metric-value">{{ $tasksMetrics['total'] }}</div>
                <div class="metric-sub">{{ $tasksMetrics['completion_rate'] }}% completed</div>
            </div>
            <div class="metric-card metric-purple">
                <div class="metric-label">Budget Remaining</div>
                <div class="metric-value">${{ number_format($budgetMetrics['total_remaining'], 0) }}</div>
                <div class="metric-sub">{{ $budgetMetrics['percentage_spent'] }}% spent</div>
            </div>
            <div class="metric-card metric-red">
                <div class="metric-label">Open Issues</div>
                <div class="metric-value">{{ $issuesMetrics['open'] + $issuesMetrics['in_progress'] }}</div>
                <div class="metric-sub">{{ $issuesMetrics['total'] }} total</div>
            </div>
        </div>
    </div>

    <!-- Health Scorecard -->
    <div class="section">
        <div class="section-title">Department Health Scorecard</div>
        <div class="card">
            <div class="health-score">
                <div class="health-score-value text-{{ $departmentHealthScorecard['status_color'] }}">
                    {{ $departmentHealthScorecard['overall_score'] }}%
                </div>
                <div class="health-score-label text-{{ $departmentHealthScorecard['status_color'] }}">
                    {{ $departmentHealthScorecard['status'] }}
                </div>
            </div>
            
            <div class="factor-grid">
                @foreach($departmentHealthScorecard['factors'] as $factor)
                <div class="factor-card">
                    <div class="factor-name">{{ $factor['name'] }}</div>
                    <div class="factor-weight">({{ $factor['weight'] }}% weight)</div>
                    <div class="factor-score text-{{ $factor['status'] === 'good' ? 'green' : ($factor['status'] === 'fair' ? 'yellow' : 'red') }}">
                        {{ $factor['score'] }}%
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill bg-{{ $factor['status'] === 'good' ? 'green' : ($factor['status'] === 'fair' ? 'yellow' : 'red') }}" style="width: {{ min(100, $factor['score']) }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Tasks Overview -->
    <div class="section">
        <div class="section-title">Tasks Overview</div>
        <div class="card">
            <div class="small-metrics-grid">
                <div class="small-metric-card" style="background-color: #d1fae5;">
                    <div class="small-metric-value text-green">{{ $tasksMetrics['completed'] }}</div>
                    <div class="small-metric-label">Completed</div>
                </div>
                <div class="small-metric-card" style="background-color: #fef3c7;">
                    <div class="small-metric-value text-yellow">{{ $tasksMetrics['ongoing'] }}</div>
                    <div class="small-metric-label">Ongoing</div>
                </div>
                <div class="small-metric-card" style="background-color: #f3f4f6;">
                    <div class="small-metric-value" style="color: #6b7280;">{{ $tasksMetrics['pending'] }}</div>
                    <div class="small-metric-label">Pending</div>
                </div>
                <div class="small-metric-card" style="background-color: #fee2e2;">
                    <div class="small-metric-value text-red">{{ $tasksMetrics['overdue'] }}</div>
                    <div class="small-metric-label">Overdue</div>
                </div>
                <div class="small-metric-card" style="background-color: #dbeafe;">
                    <div class="small-metric-value text-blue">{{ $tasksMetrics['linked_percentage'] }}%</div>
                    <div class="small-metric-label">Linked to Workplan</div>
                </div>
            </div>
            
            <div style="margin-top: 15px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <span style="font-weight: 600;">Task Completion Progress</span>
                    <span style="font-weight: 600;">{{ $tasksMetrics['completion_rate'] }}%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill bg-{{ $tasksMetrics['completion_rate'] >= 80 ? 'green' : ($tasksMetrics['completion_rate'] >= 60 ? 'yellow' : 'red') }}" style="width: {{ min(100, $tasksMetrics['completion_rate']) }}%">
                        @if($tasksMetrics['completion_rate'] > 15){{ $tasksMetrics['completion_rate'] }}%@endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Budget Overview -->
    <div class="section">
        <div class="section-title">Budget Overview</div>
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Total Budget</th>
                        <th>Spent</th>
                        <th>Remaining</th>
                        <th>Utilization</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-bold">${{ number_format($budgetMetrics['total_budget'], 2) }}</td>
                        <td class="text-red">${{ number_format($budgetMetrics['total_spent'], 2) }}</td>
                        <td class="text-green">${{ number_format($budgetMetrics['total_remaining'], 2) }}</td>
                        <td>
                            <span class="{{ $budgetMetrics['percentage_spent'] <= 80 ? 'text-green' : ($budgetMetrics['percentage_spent'] <= 100 ? 'text-yellow' : 'text-red') }}">
                                {{ $budgetMetrics['percentage_spent'] }}%
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div style="margin-top: 10px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <span style="font-weight: 600;">Budget Utilization</span>
                    <span style="font-weight: 600;">{{ $budgetMetrics['percentage_spent'] }}%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill bg-{{ $budgetMetrics['percentage_spent'] <= 80 ? 'green' : ($budgetMetrics['percentage_spent'] <= 100 ? 'yellow' : 'red') }}" style="width: {{ min(100, $budgetMetrics['percentage_spent']) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Issue Resolution Metrics -->
    <div class="section">
        <div class="section-title">Issue Resolution Metrics</div>
        <div class="card">
            <div class="small-metrics-grid">
                <div class="small-metric-card" style="background-color: #dbeafe;">
                    <div class="small-metric-value text-blue">{{ $issueResolutionMetrics['avg_turnaround_days'] }}</div>
                    <div class="small-metric-label">Avg Days</div>
                </div>
                <div class="small-metric-card" style="background-color: #d1fae5;">
                    <div class="small-metric-value text-green">{{ $issueResolutionMetrics['resolution_rate'] }}%</div>
                    <div class="small-metric-label">Resolution Rate</div>
                </div>
                <div class="small-metric-card" style="background-color: #fee2e2;">
                    <div class="small-metric-value text-red">{{ $issueResolutionMetrics['open_issues'] }}</div>
                    <div class="small-metric-label">Open</div>
                </div>
                <div class="small-metric-card" style="background-color: #fef3c7;">
                    <div class="small-metric-value text-yellow">{{ $issueResolutionMetrics['in_progress_issues'] }}</div>
                    <div class="small-metric-label">In Progress</div>
                </div>
                <div class="small-metric-card" style="background-color: #f3f4f6;">
                    <div class="small-metric-value" style="color: #6b7280;">{{ $issueResolutionMetrics['closed_issues'] }}</div>
                    <div class="small-metric-label">Closed</div>
                </div>
            </div>
            
            <div style="margin-top: 10px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <span style="font-weight: 600;">Resolution Progress</span>
                    <span style="font-weight: 600;">{{ $issueResolutionMetrics['total_resolved'] }}/{{ $issueResolutionMetrics['total_issues'] }} resolved</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill bg-green" style="width: {{ min(100, $issueResolutionMetrics['resolution_rate']) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Performance -->
    <div class="section">
        <div class="section-title">Team Performance</div>
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Tasks</th>
                        <th>Completed</th>
                        <th>Overdue</th>
                        <th>Rate</th>
                        <th>Issues</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teamPerformance as $member)
                    <tr>
                        <td class="font-bold">{{ $member['name'] }}</td>
                        <td>{{ $member['position'] }}</td>
                        <td class="text-center">{{ $member['total_tasks'] }}</td>
                        <td class="text-center text-green">{{ $member['completed_tasks'] }}</td>
                        <td class="text-center {{ $member['overdue_tasks'] > 0 ? 'text-red' : '' }}">{{ $member['overdue_tasks'] }}</td>
                        <td class="text-center">
                            <span class="{{ $member['completion_rate'] >= 80 ? 'text-green' : ($member['completion_rate'] >= 60 ? 'text-yellow' : 'text-red') }}">
                                {{ $member['completion_rate'] }}%
                            </span>
                        </td>
                        <td class="text-center text-purple">{{ $member['resolved_issues'] }}/{{ $member['total_issues'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center" style="color: #6b7280;">No team members found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Workplan Progress -->
    <div class="section">
        <div class="section-title">Workplan Progress</div>
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Total Workplans</th>
                        <th>Approved</th>
                        <th>Completion Rate</th>
                        <th>Tasks Linked</th>
                        <th>Tasks Unlinked</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center font-bold">{{ $workplanProgress['total_workplans'] }}</td>
                        <td class="text-center text-green">{{ $workplanProgress['approved_workplans'] }}</td>
                        <td class="text-center text-purple">{{ $workplanProgress['completion_percentage'] }}%</td>
                        <td class="text-center text-blue">{{ $workplanProgress['linked_tasks_percentage'] }}% ({{ $workplanProgress['linked_tasks_count'] }})</td>
                        <td class="text-center text-red">{{ $workplanProgress['unlinked_tasks_percentage'] }}% ({{ $workplanProgress['unlinked_tasks_count'] }})</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Task Priority Distribution -->
    <div class="section">
        <div class="section-title">Task Priority Distribution</div>
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Priority</th>
                        <th>Count</th>
                        <th>Percentage</th>
                        <th>Distribution</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="badge badge-red">High</span></td>
                        <td class="text-center">{{ $priorityDistribution['high'] }}</td>
                        <td class="text-center">{{ $priorityDistribution['high_percentage'] }}%</td>
                        <td>
                            <div class="progress-bar" style="height: 12px;">
                                <div class="progress-fill bg-red" style="width: {{ $priorityDistribution['high_percentage'] }}%"></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="badge badge-yellow">Medium</span></td>
                        <td class="text-center">{{ $priorityDistribution['medium'] }}</td>
                        <td class="text-center">{{ $priorityDistribution['medium_percentage'] }}%</td>
                        <td>
                            <div class="progress-bar" style="height: 12px;">
                                <div class="progress-fill bg-yellow" style="width: {{ $priorityDistribution['medium_percentage'] }}%"></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="badge badge-green">Low</span></td>
                        <td class="text-center">{{ $priorityDistribution['low'] }}</td>
                        <td class="text-center">{{ $priorityDistribution['low_percentage'] }}%</td>
                        <td>
                            <div class="progress-bar" style="height: 12px;">
                                <div class="progress-fill bg-green" style="width: {{ $priorityDistribution['low_percentage'] }}%"></div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="text-center" style="margin-top: 10px; font-weight: 600; color: #374151;">
                Total Tasks This Period: {{ $priorityDistribution['total'] }}
            </div>
        </div>
    </div>

    <!-- Monthly Assessment -->
    <div class="section">
        <div class="section-title">Monthly Assessment Comparison</div>
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Metric</th>
                        <th>{{ $monthlyAssessment['current_month']['name'] }}</th>
                        <th>{{ $monthlyAssessment['previous_month']['name'] }}</th>
                        <th>Change</th>
                        <th>Trend</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-bold">Total Tasks</td>
                        <td class="text-center">{{ $monthlyAssessment['current_month']['tasks']['total'] }}</td>
                        <td class="text-center">{{ $monthlyAssessment['previous_month']['tasks']['total'] }}</td>
                        <td class="text-center">
                            <span class="{{ $monthlyAssessment['comparison']['tasks']['total']['trend'] === 'up' ? 'text-green' : ($monthlyAssessment['comparison']['tasks']['total']['trend'] === 'down' ? 'text-red' : '') }}">
                                {{ $monthlyAssessment['comparison']['tasks']['total']['difference'] > 0 ? '+' : '' }}{{ $monthlyAssessment['comparison']['tasks']['total']['difference'] }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($monthlyAssessment['comparison']['tasks']['total']['trend'] === 'up') ↑
                            @elseif($monthlyAssessment['comparison']['tasks']['total']['trend'] === 'down') ↓
                            @else —
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="font-bold">Completed</td>
                        <td class="text-center">{{ $monthlyAssessment['current_month']['tasks']['completed'] }}</td>
                        <td class="text-center">{{ $monthlyAssessment['previous_month']['tasks']['completed'] }}</td>
                        <td class="text-center">
                            <span class="{{ $monthlyAssessment['comparison']['tasks']['completed']['trend'] === 'up' ? 'text-green' : ($monthlyAssessment['comparison']['tasks']['completed']['trend'] === 'down' ? 'text-red' : '') }}">
                                {{ $monthlyAssessment['comparison']['tasks']['completed']['difference'] > 0 ? '+' : '' }}{{ $monthlyAssessment['comparison']['tasks']['completed']['difference'] }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($monthlyAssessment['comparison']['tasks']['completed']['trend'] === 'up') ↑
                            @elseif($monthlyAssessment['comparison']['tasks']['completed']['trend'] === 'down') ↓
                            @else —
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="font-bold">Completion Rate</td>
                        <td class="text-center">{{ $monthlyAssessment['current_month']['tasks']['completion_rate'] }}%</td>
                        <td class="text-center">{{ $monthlyAssessment['previous_month']['tasks']['completion_rate'] }}%</td>
                        <td class="text-center">
                            <span class="{{ $monthlyAssessment['comparison']['tasks']['completion_rate']['trend'] === 'up' ? 'text-green' : ($monthlyAssessment['comparison']['tasks']['completion_rate']['trend'] === 'down' ? 'text-red' : '') }}">
                                {{ $monthlyAssessment['comparison']['tasks']['completion_rate']['difference'] > 0 ? '+' : '' }}{{ $monthlyAssessment['comparison']['tasks']['completion_rate']['difference'] }}%
                            </span>
                        </td>
                        <td class="text-center">
                            @if($monthlyAssessment['comparison']['tasks']['completion_rate']['trend'] === 'up') ↑
                            @elseif($monthlyAssessment['comparison']['tasks']['completion_rate']['trend'] === 'down') ↓
                            @else —
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="font-bold">Budget Spent</td>
                        <td class="text-center">${{ number_format($monthlyAssessment['current_month']['budget_spent'], 2) }}</td>
                        <td class="text-center">${{ number_format($monthlyAssessment['previous_month']['budget_spent'], 2) }}</td>
                        <td class="text-center">
                            <span class="{{ $monthlyAssessment['comparison']['budget']['trend'] === 'up' ? 'text-red' : ($monthlyAssessment['comparison']['budget']['trend'] === 'down' ? 'text-green' : '') }}">
                                {{ $monthlyAssessment['comparison']['budget']['difference'] > 0 ? '+' : '' }}${{ number_format($monthlyAssessment['comparison']['budget']['difference'], 2) }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($monthlyAssessment['comparison']['budget']['trend'] === 'up') ↑
                            @elseif($monthlyAssessment['comparison']['budget']['trend'] === 'down') ↓
                            @else —
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="font-bold">Issues Resolved</td>
                        <td class="text-center">{{ $monthlyAssessment['current_month']['issues']['resolved'] }}</td>
                        <td class="text-center">{{ $monthlyAssessment['previous_month']['issues']['resolved'] }}</td>
                        <td class="text-center">
                            <span class="{{ $monthlyAssessment['comparison']['issues']['resolved']['trend'] === 'up' ? 'text-green' : ($monthlyAssessment['comparison']['issues']['resolved']['trend'] === 'down' ? 'text-red' : '') }}">
                                {{ $monthlyAssessment['comparison']['issues']['resolved']['difference'] > 0 ? '+' : '' }}{{ $monthlyAssessment['comparison']['issues']['resolved']['difference'] }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($monthlyAssessment['comparison']['issues']['resolved']['trend'] === 'up') ↑
                            @elseif($monthlyAssessment['comparison']['issues']['resolved']['trend'] === 'down') ↓
                            @else —
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Budget Spending Trend -->
    <div class="section">
        <div class="section-title">Budget Spending Trend (Last 6 Months)</div>
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Spent</th>
                        <th>Cumulative</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($budgetSpendingTrend['labels'] as $index => $label)
                    <tr>
                        <td class="font-bold">{{ $label }}</td>
                        <td class="text-red">${{ number_format($budgetSpendingTrend['spent'][$index], 2) }}</td>
                        <td class="text-purple">${{ number_format($budgetSpendingTrend['cumulative_spent'][$index], 2) }}</td>
                        <td>
                            <div class="progress-bar" style="height: 12px;">
                                @php
                                    $progressWidth = $budgetSpendingTrend['total_budget'] > 0 ? min(100, ($budgetSpendingTrend['spent'][$index] / $budgetSpendingTrend['total_budget']) * 100) : 0;
                                @endphp
                                <div class="progress-fill bg-red" style="width: {{ $progressWidth }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top: 15px; display: table; width: 100%;">
                <div style="display: table-cell; width: 33%; text-align: center; padding: 10px; background-color: #dbeafe; border-radius: 8px;">
                    <div style="font-weight: 600; color: #2563eb;">Total Budget</div>
                    <div style="font-size: 14pt; font-weight: bold;">${{ number_format($budgetSpendingTrend['total_budget'], 0) }}</div>
                </div>
                <div style="display: table-cell; width: 33%; text-align: center; padding: 10px; background-color: #fee2e2; border-radius: 8px; margin: 0 10px;">
                    <div style="font-weight: 600; color: #dc2626;">Total Spent</div>
                    <div style="font-size: 14pt; font-weight: bold;">${{ number_format($budgetSpendingTrend['total_spent'], 0) }}</div>
                </div>
                <div style="display: table-cell; width: 33%; text-align: center; padding: 10px; background-color: #d1fae5; border-radius: 8px;">
                    <div style="font-weight: 600; color: #059669;">Remaining</div>
                    <div style="font-size: 14pt; font-weight: bold;">${{ number_format($budgetSpendingTrend['total_remaining'], 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Completion Trends -->
    <div class="section">
        <div class="section-title">Weekly Completion Rate Trends</div>
        <div class="card">
            <div style="margin-bottom: 15px;">
                <div style="display: table; width: 100%;">
                    <div style="display: table-cell; width: 50%;">
                        <span style="font-weight: 600;">Current Week Average:</span>
                        <span class="text-blue" style="font-size: 16pt; margin-left: 10px;">{{ $weeklyTrends['current_week_avg'] }}%</span>
                    </div>
                    <div style="display: table-cell; width: 50%;">
                        <span style="font-weight: 600;">Previous Week Average:</span>
                        <span style="font-size: 14pt; margin-left: 10px; color: #6b7280;">{{ $weeklyTrends['previous_week_avg'] }}%</span>
                    </div>
                </div>
                @if($weeklyTrends['current_week_avg'] > $weeklyTrends['previous_week_avg'])
                    <div class="text-green" style="margin-top: 5px;">↑ {{ round($weeklyTrends['current_week_avg'] - $weeklyTrends['previous_week_avg'], 1) }}% improvement</div>
                @elseif($weeklyTrends['current_week_avg'] < $weeklyTrends['previous_week_avg'])
                    <div class="text-red" style="margin-top: 5px;">↓ {{ round($weeklyTrends['previous_week_avg'] - $weeklyTrends['current_week_avg'], 1) }}% decrease</div>
                @endif
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Week</th>
                        <th>Average Rate</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($weeklyTrends['labels'] as $index => $label)
                    <tr>
                        <td class="font-bold">{{ $label }}</td>
                        <td class="text-center text-blue">{{ $weeklyTrends['department_average'][$index] }}%</td>
                        <td>
                            <div class="progress-bar" style="height: 12px;">
                                <div class="progress-fill bg-blue" style="width: {{ min(100, $weeklyTrends['department_average'][$index]) }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <strong>{{ $departmentName }} Dashboard Report</strong><br>
        Generated on {{ $generatedAt }} | Period: {{ $startDate }} to {{ $endDate }}<br>
        <span style="font-size: 8pt; color: #9ca3af;">This report was automatically generated by the Department Dashboard System</span>
    </div>
</body>
</html>
