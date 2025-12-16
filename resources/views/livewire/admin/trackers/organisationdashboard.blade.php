@can('organisationdashboard.access')
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50">
    <!-- Breadcrumbs -->
    <div class="bg-white/80 backdrop-blur-sm border-b border-gray-200 px-4 py-3">
        <div class="max-w-7xl mx-auto">
            <x-breadcrumbs :items="$breadcrumbs" 
                class="bg-gray-50 p-3 rounded-xl overflow-x-auto whitespace-nowrap"
                link-item-class="text-base hover:text-blue-600 transition-colors" />
        </div>
    </div>

    <!-- Header -->
    <div class="bg-white/80 backdrop-blur-sm shadow-sm border-b border-gray-200 mb-6 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2 tracking-tight">
                        Organization Dashboard
                    </h1>
                    <p class="text-gray-600">Comprehensive overview of all departments and organization performance</p>
                </div>
                
                <div class="grid grid-cols-3 sm:grid-cols-3 gap-2">
                    <!-- Filter Type -->
                    <x-select wire:model.live="filterType" :options="[['id'=>'day', 'name' => 'By Day'], ['id'=>'week', 'name' => 'By Week'], ['id'=>'month', 'name' => 'By Month']]" option-label="name" option-value="id" placeholder="Filter Type" class="min-w-[120px]" />

                    <!-- Date Picker -->
                    <x-input type="date" wire:model.live="selectedDate" class="min-w-[150px]" />

                    <!-- Budget Select -->
                    <x-select wire:model.live="currentBudgetId" :options="$budgets" option-label="year" option-value="id" placeholder="Select Budget" class="min-w-[200px]" />

                    <!-- Department Filter -->
                    {{-- <x-select wire:model.live="selectedDepartmentId" :options="$departments" option-label="name" option-value="id" placeholder="All Departments" class="min-w-[200px]" /> --}}

                    <!-- Export Button -->
                    <x-button wire:click="exportOrganizationReport" class="btn-primary" spinner="exportOrganizationReport">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export
                    </x-button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        
        {{-- ============================================== --}}
        {{-- SECTION 1: EXECUTIVE SUMMARY --}}
        {{-- ============================================== --}}

        <!-- 1. Overall Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Total Departments -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
                <div class="space-y-1">
                    <p class="text-blue-100 text-sm font-semibold uppercase tracking-wide">Departments</p>
                    <p class="text-4xl font-bold">{{ $overallMetrics['total_departments'] }}</p>
                </div>
            </div>

            <!-- Total Employees -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                </div>
                <div class="space-y-1">
                    <p class="text-green-100 text-sm font-semibold uppercase tracking-wide">Total Employees</p>
                    <p class="text-4xl font-bold">{{ $overallMetrics['total_employees'] }}</p>
                </div>
            </div>

            <!-- Total Tasks -->
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
                <div class="space-y-1">
                    <p class="text-purple-100 text-sm font-semibold uppercase tracking-wide">Total Tasks</p>
                    <p class="text-4xl font-bold">{{ $overallMetrics['total_tasks'] }}</p>
                    <p class="text-purple-100 text-sm">{{ $overallMetrics['completion_rate'] }}% completed</p>
                </div>
            </div>

            <!-- Budget -->
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="space-y-1">
                    <p class="text-orange-100 text-sm font-semibold uppercase tracking-wide">Total Budget</p>
                    <p class="text-4xl font-bold">${{ number_format($overallMetrics['total_budget'], 0) }}</p>
                    <p class="text-orange-100 text-sm">{{ $overallMetrics['percentage_spent'] }}% spent</p>
                </div>
            </div>
        </div>

        <!-- 2. Organization Health Scorecard -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Organization Health Scorecard</h2>
                    <p class="text-gray-600 mt-1">Weighted performance score combining 5 key organizational metrics</p>
                </div>
                <div class="text-right">
                    <div class="text-4xl font-bold text-{{ $organizationHealth['status_color'] }}-600">
                        {{ $organizationHealth['overall_score'] }}%
                    </div>
                    <div class="text-sm font-semibold text-{{ $organizationHealth['status_color'] }}-600 mt-1">
                        {{ $organizationHealth['status'] }}
                    </div>
                </div>
            </div>

            <!-- How Score is Calculated -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-sm text-blue-800">
                        <p class="font-semibold mb-1">How is this score calculated?</p>
                        <p class="text-blue-700">Each metric below has a <strong>weight</strong> (shown as %) indicating its importance. The overall score is calculated by multiplying each metric's score by its weight and adding them together. For example: if Task Completion is 50% with 25% weight, it contributes 12.5 points to the overall score.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                @php
                    $factorDescriptions = [
                        'Task Completion' => 'Percentage of tasks marked as completed vs total tasks assigned',
                        'Budget Management' => 'How efficiently budget is utilized (50-90% utilization is optimal)',
                        'Issue Resolution' => 'Percentage of reported issues that have been resolved',
                        'Workplan Progress' => 'Progress on strategic workplans and organizational goals',
                        'Department Health' => 'Average task completion rate across all active departments',
                    ];
                @endphp
                @foreach($organizationHealth['factors'] as $factor)
                <div class="bg-gray-50 rounded-xl p-4 hover:bg-gray-100 transition-colors group">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-semibold text-gray-800">{{ $factor['name'] }}</span>
                        <span class="text-xs font-medium bg-gray-200 text-gray-700 px-2 py-0.5 rounded-full" title="Weight: This metric contributes {{ $factor['weight'] }}% to the overall score">
                            {{ $factor['weight'] }}% weight
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mb-3 leading-relaxed">{{ $factorDescriptions[$factor['name']] ?? '' }}</p>
                    <div class="text-2xl font-bold text-{{ $factor['status'] === 'good' ? 'green' : ($factor['status'] === 'fair' ? 'yellow' : 'red') }}-600">
                        {{ $factor['score'] }}%
                    </div>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-{{ $factor['status'] === 'good' ? 'green' : ($factor['status'] === 'fair' ? 'yellow' : 'red') }}-500 h-2 rounded-full transition-all duration-500" style="width: {{ $factor['score'] }}%"></div>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-xs">
                        <span class="text-gray-500">Contributes: <strong class="text-gray-700">{{ round($factor['score'] * $factor['weight'] / 100, 1) }} pts</strong></span>
                        <span class="text-{{ $factor['status'] === 'good' ? 'green' : ($factor['status'] === 'fair' ? 'yellow' : 'red') }}-600 font-medium">
                            {{ $factor['status'] === 'good' ? '✓ Good' : ($factor['status'] === 'fair' ? '◐ Fair' : '✗ Poor') }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Legend -->
            <div class="mt-4 pt-4 border-t border-gray-200 flex flex-wrap items-center justify-center gap-6 text-xs text-gray-600">
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 bg-green-500 rounded-full"></span> Good (≥80%)</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 bg-yellow-500 rounded-full"></span> Fair (60-79%)</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 bg-red-500 rounded-full"></span> Poor (&lt;60%)</span>
            </div>
        </div>


        {{-- ============================================== --}}
        {{-- SECTION 3: TASK MANAGEMENT & APPROVALS --}}
        {{-- ============================================== --}}

        <!-- 5. Task Approval Metrics -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Task Approval Metrics</h2>
                    <p class="text-gray-600 mt-1">Supervisor approval tracking and rates</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Pending Approval</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $taskApprovalMetrics['pending_approval'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $taskApprovalMetrics['pending_rate'] }}% of total</p>
                </div>
                <div class="bg-green-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Approved Tasks</p>
                    <p class="text-3xl font-bold text-green-600">{{ $taskApprovalMetrics['approved_tasks'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $taskApprovalMetrics['approval_rate'] }}% approval rate</p>
                </div>
                <div class="bg-red-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Rejected Tasks</p>
                    <p class="text-3xl font-bold text-red-600">{{ $taskApprovalMetrics['rejected_tasks'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $taskApprovalMetrics['rejection_rate'] }}% rejection rate</p>
                </div>
                <div class="bg-purple-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Avg Approval Time</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $taskApprovalMetrics['avg_approval_days'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">days ({{ $taskApprovalMetrics['avg_approval_hours'] }} hours)</p>
                </div>
            </div>

            <div class="w-full bg-gray-200 rounded-full h-6">
                <div class="flex h-6 rounded-full overflow-hidden">
                    <div class="bg-green-500 h-6 flex items-center justify-center text-white text-xs font-semibold" style="width: {{ $taskApprovalMetrics['approval_rate'] }}%">
                        @if($taskApprovalMetrics['approval_rate'] > 10){{ $taskApprovalMetrics['approval_rate'] }}%@endif
                    </div>
                    <div class="bg-yellow-500 h-6 flex items-center justify-center text-white text-xs font-semibold" style="width: {{ $taskApprovalMetrics['pending_rate'] }}%">
                        @if($taskApprovalMetrics['pending_rate'] > 10){{ $taskApprovalMetrics['pending_rate'] }}%@endif
                    </div>
                    <div class="bg-red-500 h-6 flex items-center justify-center text-white text-xs font-semibold" style="width: {{ $taskApprovalMetrics['rejection_rate'] }}%">
                        @if($taskApprovalMetrics['rejection_rate'] > 10){{ $taskApprovalMetrics['rejection_rate'] }}%@endif
                    </div>
                </div>
            </div>
            <div class="flex justify-center gap-6 mt-2 text-sm">
                <span class="flex items-center gap-2"><span class="w-3 h-3 bg-green-500 rounded-full"></span>Approved</span>
                <span class="flex items-center gap-2"><span class="w-3 h-3 bg-yellow-500 rounded-full"></span>Pending</span>
                <span class="flex items-center gap-2"><span class="w-3 h-3 bg-red-500 rounded-full"></span>Rejected</span>
            </div>
        </div>

        <!-- 6. Supervisor Approval Rates & Weekly Top Approvers -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Supervisor Approval Rates -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Supervisor Approval Rates</h2>
                        <p class="text-gray-600 mt-1 text-sm">Task approval performance by supervisor</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-2 px-3 font-semibold text-gray-700">Supervisor</th>
                                <th class="text-center py-2 px-3 font-semibold text-gray-700">Reviewed</th>
                                <th class="text-center py-2 px-3 font-semibold text-gray-700">Approved</th>
                                <th class="text-center py-2 px-3 font-semibold text-gray-700">Rate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse(array_slice($supervisorApprovalRates, 0, 8) as $supervisor)
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-3">
                                    <p class="font-medium text-gray-900">{{ $supervisor['approver_name'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $supervisor['department'] }}</p>
                                </td>
                                <td class="py-2 px-3 text-center font-semibold">{{ $supervisor['total_reviewed'] }}</td>
                                <td class="py-2 px-3 text-center text-green-600 font-semibold">{{ $supervisor['approved'] }}</td>
                                <td class="py-2 px-3 text-center">
                                    <span class="font-semibold {{ $supervisor['approval_rate'] >= 80 ? 'text-green-600' : ($supervisor['approval_rate'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ $supervisor['approval_rate'] }}%
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-gray-500">No supervisor approvals recorded</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Weekly Top Approvers Log -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Weekly Top Approvers</h2>
                        <p class="text-gray-600 mt-1 text-sm">Top task approvers by week</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($weeklyTopApprovers as $week)
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-gray-900 text-sm">{{ $week['week_label'] }}</h3>
                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">{{ $week['total_approvals'] }} total</span>
                        </div>
                        <div class="space-y-2">
                            @forelse($week['top_approvers'] as $index => $approver)
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 flex items-center justify-center text-xs font-bold rounded-full 
                                        {{ $index === 0 ? 'bg-yellow-400 text-yellow-900' : ($index === 1 ? 'bg-gray-300 text-gray-700' : ($index === 2 ? 'bg-orange-400 text-orange-900' : 'bg-gray-100 text-gray-600')) }}">
                                        {{ $index + 1 }}
                                    </span>
                                    <span class="text-gray-700 truncate max-w-[100px]" title="{{ $approver['name'] }}">{{ $approver['name'] }}</span>
                                </div>
                                <span class="font-semibold text-green-600">{{ $approver['tasks_approved'] }}</span>
                            </div>
                            @empty
                            <p class="text-xs text-gray-500 text-center py-2">No approvals</p>
                            @endforelse
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ============================================== --}}
        {{-- SECTION 4: DEPARTMENT OVERVIEW --}}
        {{-- ============================================== --}}

        <!-- 7. Department Breakdown Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Department Breakdown</h2>
                    <p class="text-gray-600 mt-1">Performance metrics by department</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Department</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Employees</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Tasks</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Completed</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Completion Rate</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Budget</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Spent</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Utilization</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Issues</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($departmentBreakdown as $dept)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium text-gray-900">{{ $dept['name'] }}</td>
                            <td class="py-3 px-4 text-center">{{ $dept['employees'] }}</td>
                            <td class="py-3 px-4 text-center">{{ $dept['tasks']['total'] }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="font-semibold text-green-600">{{ $dept['tasks']['completed'] }}</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <span class="font-semibold {{ $dept['tasks']['completion_rate'] >= 80 ? 'text-green-600' : ($dept['tasks']['completion_rate'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ $dept['tasks']['completion_rate'] }}%
                                    </span>
                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                        <div class="bg-{{ $dept['tasks']['completion_rate'] >= 80 ? 'green' : ($dept['tasks']['completion_rate'] >= 60 ? 'yellow' : 'red') }}-500 h-2 rounded-full" style="width: {{ $dept['tasks']['completion_rate'] }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center">${{ number_format($dept['budget']['total'], 0) }}</td>
                            <td class="py-3 px-4 text-center">${{ number_format($dept['budget']['spent'], 0) }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="font-semibold {{ $dept['budget']['percentage_spent'] <= 80 ? 'text-green-600' : ($dept['budget']['percentage_spent'] <= 100 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ $dept['budget']['percentage_spent'] }}%
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="text-sm">{{ $dept['issues']['total'] }} total</span>
                                <span class="text-xs text-gray-500 block">{{ $dept['issues']['resolved'] }} resolved</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <x-button wire:click="viewDepartment({{ $dept['id'] }})" class="btn-sm btn-primary">
                                    View Details
                                </x-button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="py-8 text-center text-gray-500">No departments found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 8. Department Performance Heatmap -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Department Performance Heatmap</h2>
                    <p class="text-gray-600 mt-1">Visual performance comparison across departments</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($performanceHeatmap as $dept)
                <div class="bg-{{ $dept['heat_color'] }}-50 rounded-xl p-4 border-2 border-{{ $dept['heat_color'] }}-300">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-gray-900">{{ $dept['department'] }}</h3>
                        @if($dept['has_any_data'])
                        <span class="text-2xl font-bold text-{{ $dept['heat_color'] }}-600">{{ $dept['performance_score'] }}%</span>
                        @else
                        <span class="text-lg font-bold text-gray-400">No Activity</span>
                        @endif
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Completion:</span>
                            @if($dept['has_tasks'])
                            <span class="font-semibold {{ $dept['completion_rate'] >= 70 ? 'text-green-600' : ($dept['completion_rate'] >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ $dept['completion_rate'] }}% <span class="text-xs text-gray-500">({{ $dept['completed_tasks'] }} tasks)</span>
                            </span>
                            @else
                            <span class="text-gray-400 text-xs">No tasks</span>
                            @endif
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Budget:</span>
                            @if($dept['has_budget'])
                            <span class="font-semibold {{ $dept['budget_utilization'] <= 80 ? 'text-green-600' : ($dept['budget_utilization'] <= 100 ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ $dept['budget_utilization'] }}% <span class="text-xs text-gray-500">(${{ number_format($dept['total_budget'], 0) }})</span>
                            </span>
                            @else
                            <span class="text-gray-400 text-xs">No budget</span>
                            @endif
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Issues:</span>
                            @if($dept['has_issues'])
                            <span class="font-semibold {{ $dept['issue_resolution'] >= 70 ? 'text-green-600' : ($dept['issue_resolution'] >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ $dept['issue_resolution'] }}% <span class="text-xs text-gray-500">({{ $dept['total_issues'] }} issues)</span>
                            </span>
                            @else
                            <span class="text-gray-400 text-xs">No issues</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- 9. Department Comparison -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- By Completion Rate -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top by Completion Rate</h3>
                <div class="space-y-3">
                    @foreach(array_slice($departmentComparison['by_completion_rate'], 0, 5) as $dept)
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">{{ $dept['name'] }}</span>
                        <span class="font-semibold text-green-600">{{ $dept['tasks']['completion_rate'] }}%</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- By Budget Utilization -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top by Budget Utilization</h3>
                <div class="space-y-3">
                    @foreach(array_slice($departmentComparison['by_budget_utilization'], 0, 5) as $dept)
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">{{ $dept['name'] }}</span>
                        <span class="font-semibold text-blue-600">{{ $dept['budget']['percentage_spent'] }}%</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- By Issue Resolution -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top by Issue Resolution</h3>
                <div class="space-y-3">
                    @foreach(array_slice($departmentComparison['by_issue_resolution'], 0, 5) as $dept)
                    @php
                        $resolutionRate = $dept['issues']['total'] > 0 ? round(($dept['issues']['resolved'] / $dept['issues']['total']) * 100, 1) : 0;
                    @endphp
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">{{ $dept['name'] }}</span>
                        <span class="font-semibold text-purple-600">{{ $resolutionRate }}%</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ============================================== --}}
        {{-- SECTION 5: PRODUCTIVITY & PERFORMANCE --}}
        {{-- ============================================== --}}

        <!-- 10. Top Performers -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Top Performers</h2>
                    <p class="text-gray-600 mt-1">Highest performing employees across the organization</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Employee</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Department</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Position</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Total Tasks</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Completed</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Completion Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($topPerformers as $performer)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium text-gray-900">{{ $performer['name'] }}</td>
                            <td class="py-3 px-4 text-gray-600">{{ $performer['department'] }}</td>
                            <td class="py-3 px-4 text-gray-600">{{ $performer['position'] }}</td>
                            <td class="py-3 px-4 text-center">{{ $performer['total_tasks'] }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="font-semibold text-green-600">{{ $performer['completed_tasks'] }}</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="font-semibold {{ $performer['completion_rate'] >= 80 ? 'text-green-600' : ($performer['completion_rate'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ $performer['completion_rate'] }}%
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-500">No performers found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 11. Productivity Metrics & Workload Distribution -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Productivity Metrics -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Productivity Metrics</h2>
                        <p class="text-gray-600 mt-1 text-sm">Organization-wide productivity indicators</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-blue-50 rounded-xl p-4 text-center">
                        <p class="text-sm text-gray-600 mb-1">Tasks/Employee</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $productivityMetrics['tasks_per_employee'] }}</p>
                    </div>
                    <div class="bg-green-50 rounded-xl p-4 text-center">
                        <p class="text-sm text-gray-600 mb-1">Completed/Employee</p>
                        <p class="text-2xl font-bold text-green-600">{{ $productivityMetrics['completed_per_employee'] }}</p>
                    </div>
                    {{-- <div class="bg-purple-50 rounded-xl p-4 text-center">
                        <p class="text-sm text-gray-600 mb-1">Cost/Task</p>
                        <p class="text-2xl font-bold text-purple-600">${{ number_format($productivityMetrics['cost_per_task_completion'] ?? $productivityMetrics['cost_per_task'] ?? 0, 2) }}</p>
                    </div> --}}
                    <div class="bg-orange-50 rounded-xl p-4 text-center">
                        <p class="text-sm text-gray-600 mb-1">Efficiency Score</p>
                        <p class="text-2xl font-bold text-orange-600">{{ $productivityMetrics['efficiency_score'] }}%</p>
                    </div>
                </div>
                <div class="mt-4 bg-indigo-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Productivity Index</p>
                    <p class="text-3xl font-bold text-indigo-600">{{ $productivityMetrics['productivity_index'] }}</p>
                </div>
            </div>

            <!-- Workload Distribution -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Workload Distribution</h2>
                        <p class="text-gray-600 mt-1 text-sm">Tasks per employee across departments</p>
                    </div>
                </div>

                <div class="space-y-4">
                    @foreach($workloadDistribution as $workload)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-gray-700 text-sm">{{ $workload['department'] }}</span>
                                <span class="text-xs text-gray-500">({{ $workload['employees'] }} employees)</span>
                            </div>
                            <div class="text-right">
                                <span class="font-semibold text-blue-600 text-sm">{{ $workload['avg_workload_per_employee'] }} workload/emp</span>
                                <span class="text-xs text-gray-500">({{ $workload['total_tasks'] }} tasks, {{ $workload['total_issues'] }} issues)</span>
                                <span class="text-xs px-2 py-1 rounded-full {{ $workload['workload_status'] === 'overloaded' ? 'bg-red-100 text-red-800' : ($workload['workload_status'] === 'underloaded' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }} ml-2">
                                    {{ ucfirst($workload['workload_status']) }}
                                </span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-{{ $workload['workload_status'] === 'overloaded' ? 'red' : ($workload['workload_status'] === 'underloaded' ? 'yellow' : 'green') }}-500 h-2 rounded-full transition-all duration-500" style="width: {{ min(100, ($workload['avg_workload_per_employee'] / 20) * 100) }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 12. Task Hours Productivity -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Task Hours Productivity</h2>
                    <p class="text-gray-600 mt-1">Planned vs actual hours worked</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Total Planned Hours</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $taskHoursProductivity['total_planned_hours'] }}</p>
                </div>
                <div class="bg-green-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Total Worked Hours</p>
                    <p class="text-3xl font-bold text-green-600">{{ $taskHoursProductivity['total_worked_hours'] }}</p>
                </div>
                <div class="bg-purple-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Efficiency Rate</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $taskHoursProductivity['efficiency_rate'] }}%</p>
                </div>
                <div class="bg-{{ $taskHoursProductivity['variance_hours'] >= 0 ? 'orange' : 'red' }}-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Variance</p>
                    <p class="text-3xl font-bold text-{{ $taskHoursProductivity['variance_hours'] >= 0 ? 'orange' : 'red' }}-600">
                        {{ $taskHoursProductivity['variance_hours'] > 0 ? '+' : '' }}{{ $taskHoursProductivity['variance_hours'] }}h
                    </p>
                </div>
            </div>

            <!-- Task Instance Status -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-yellow-50 rounded-xl p-3 text-center">
                    <p class="text-xs text-gray-600">Ongoing</p>
                    <p class="text-xl font-bold text-yellow-600">{{ $taskHoursProductivity['instance_status']['ongoing'] }}</p>
                </div>
                <div class="bg-green-50 rounded-xl p-3 text-center">
                    <p class="text-xs text-gray-600">Completed</p>
                    <p class="text-xl font-bold text-green-600">{{ $taskHoursProductivity['instance_status']['completed'] }}</p>
                </div>
                <div class="bg-orange-50 rounded-xl p-3 text-center">
                    <p class="text-xs text-gray-600">Rolled Over</p>
                    <p class="text-xl font-bold text-orange-600">{{ $taskHoursProductivity['instance_status']['rolled_over'] }}</p>
                </div>
            </div>

            <!-- By Department -->
            <h3 class="text-lg font-semibold text-gray-900 mb-3">By Department</h3>
            <div class="space-y-3">
                @foreach($taskHoursProductivity['by_department'] as $dept)
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700">{{ $dept['department'] }}</span>
                        <div class="flex items-center gap-4 text-xs">
                            <span class="text-blue-600">Planned: {{ $dept['planned_hours'] }}h</span>
                            <span class="text-green-600">Worked: {{ $dept['worked_hours'] }}h</span>
                            <span class="font-semibold {{ $dept['efficiency'] >= 80 ? 'text-green-600' : ($dept['efficiency'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">{{ $dept['efficiency'] }}%</span>
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ min(100, $dept['efficiency']) }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- 13. Weekly Review Summary -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Weekly Review Summary</h2>
                    <p class="text-gray-600 mt-1">Self-assessment reviews across organization</p>
                </div>
            </div>

            <div class="grid grid-cols-5 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-blue-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Total Reviews</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $weeklyReviewSummary['total_reviews'] }}</p>
                </div>
                <div class="bg-green-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Avg Completion Rate</p>
                    <p class="text-2xl font-bold text-green-600">{{ $weeklyReviewSummary['avg_completion_rate'] }}%</p>
                </div>
                <div class="bg-purple-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Participation Rate</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $weeklyReviewSummary['participation_rate'] }}%</p>
                    <p class="text-xs text-gray-500">{{ $weeklyReviewSummary['users_with_reviews'] }}/{{ $weeklyReviewSummary['total_users'] }} users</p>
                </div>
                <div class="bg-orange-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Avg Hours Planned</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $weeklyReviewSummary['avg_hours_planned'] }}</p>
                </div>
                <div class="bg-teal-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Avg Hours Completed</p>
                    <p class="text-2xl font-bold text-teal-600">{{ $weeklyReviewSummary['avg_hours_completed'] }}</p>
                </div>
            </div>

            <!-- Weekly Trends -->
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Weekly Trends (Last 4 Weeks)</h3>
            <div class="grid grid-cols-4 gap-4 mb-6">
                @foreach($weeklyReviewSummary['weekly_trends'] as $trend)
                <div class="bg-gray-50 rounded-xl p-3 text-center">
                    <p class="text-xs text-gray-600 mb-1">{{ $trend['week'] }}</p>
                    <p class="text-xl font-bold text-gray-900">{{ $trend['submissions'] }}</p>
                    <p class="text-xs text-green-600">{{ $trend['avg_completion'] }}% avg</p>
                </div>
                @endforeach
            </div>

            <!-- By Department -->
            <h3 class="text-lg font-semibold text-gray-900 mb-3">By Department</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-2 px-3 font-semibold text-gray-700">Department</th>
                            <th class="text-center py-2 px-3 font-semibold text-gray-700">Submissions</th>
                            <th class="text-center py-2 px-3 font-semibold text-gray-700">Avg Completion</th>
                            <th class="text-center py-2 px-3 font-semibold text-gray-700">Participation</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($weeklyReviewSummary['by_department'] as $dept)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-3 font-medium text-gray-900">{{ $dept['department'] }}</td>
                            <td class="py-2 px-3 text-center">{{ $dept['submissions'] }}</td>
                            <td class="py-2 px-3 text-center">
                                <span class="font-semibold {{ $dept['avg_completion_rate'] >= 80 ? 'text-green-600' : ($dept['avg_completion_rate'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ $dept['avg_completion_rate'] }}%
                                </span>
                            </td>
                            <td class="py-2 px-3 text-center">{{ $dept['participation_rate'] }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ============================================== --}}
        {{-- SECTION 6: STRATEGIC GOALS --}}
        {{-- ============================================== --}}

        <!-- 14. Strategic Goals/KPI Tracking -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Strategic Goals & KPI Tracking</h2>
                    <p class="text-gray-600 mt-1">Organization-wide workplan progress</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Total Workplans</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $workplanProgress['total_workplans'] }}</p>
                </div>
                <div class="bg-green-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Approved</p>
                    <p class="text-2xl font-bold text-green-600">{{ $workplanProgress['approved_workplans'] }}</p>
                </div>
                <div class="bg-purple-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Completion Rate</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $workplanProgress['completion_percentage'] }}%</p>
                </div>
                <div class="bg-orange-50 rounded-xl p-4">
                    <div class="text-center">
                        <p class="text-sm text-gray-600 mb-1">Tasks Linked</p>
                        <p class="text-2xl font-bold text-orange-600">{{ $workplanProgress['linked_tasks_percentage'] }}%</p>
                        <p class="text-xs text-gray-500 mt-1">({{ $workplanProgress['linked_tasks_count'] ?? 0 }} tasks)</p>
                    </div>
                </div>
            </div>
            
            <!-- Tasks Unlinked card below Tasks Linked -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-red-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Tasks Unlinked</p>
                    <p class="text-2xl font-bold text-red-600">{{ $workplanProgress['unlinked_tasks_percentage'] ?? 0 }}%</p>
                    <p class="text-xs text-gray-500 mt-1">({{ $workplanProgress['unlinked_tasks_count'] ?? 0 }} tasks)</p>
                </div>
            </div>

            <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">By Department</h3>
                <div class="space-y-3">
                    @foreach($workplanProgress['by_department'] as $dept)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <span class="font-medium text-gray-700 w-48">{{ $dept['department'] }}</span>
                                <span class="text-sm text-gray-600">{{ $dept['total_goals'] }} workplans</span>
                                <span class="text-sm text-gray-600">{{ $dept['approved'] }} approved</span>
                            </div>
                            <span class="font-semibold text-blue-600">{{ $dept['progress'] }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-blue-500 h-3 rounded-full transition-all duration-500" style="width: {{ $dept['progress'] }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ============================================== --}}
        {{-- SECTION 7: BUDGET & FINANCE --}}
        {{-- ============================================== --}}

        <!-- 15. Budget Distribution Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Budget Distribution</h2>
                    <p class="text-gray-600 mt-1">Budget allocation and utilization by department</p>
                </div>
            </div>

            <div class="space-y-4">
                @foreach($budgetDistribution as $dist)
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-3">
                            <span class="font-medium text-gray-700 w-48">{{ $dist['department'] }}</span>
                            <span class="text-sm text-gray-600">Allocated: ${{ number_format($dist['allocated'], 0) }}</span>
                            <span class="text-sm text-gray-600">Spent: ${{ number_format($dist['spent'], 0) }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-semibold text-blue-600">{{ $dist['utilization'] }}% utilized</span>
                            <span class="text-xs text-gray-500 block">{{ $dist['percentage'] }}% of total</span>
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-4">
                        <div class="bg-blue-500 h-4 rounded-full transition-all duration-500" style="width: {{ $dist['utilization'] }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- 16. Budget Spending Trends & Forecast -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Budget Spending Trends -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Budget Spending Trends</h2>
                        <p class="text-gray-600 mt-1 text-sm">Last 6 months spending analysis</p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-blue-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-gray-600 mb-1">Total Budget</p>
                        <p class="text-lg font-bold text-blue-600">${{ number_format($budgetSpendingTrends['total_budget'], 0) }}</p>
                    </div>
                    <div class="bg-red-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-gray-600 mb-1">Total Spent</p>
                        <p class="text-lg font-bold text-red-600">${{ number_format($budgetSpendingTrends['total_spent'], 0) }}</p>
                    </div>
                    <div class="bg-green-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-gray-600 mb-1">Remaining</p>
                        <p class="text-lg font-bold text-green-600">${{ number_format($budgetSpendingTrends['total_remaining'], 0) }}</p>
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach($budgetSpendingTrends['labels'] as $index => $label)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium text-gray-700">{{ $label }}</span>
                            <span class="text-xs text-gray-600">${{ number_format($budgetSpendingTrends['spent'][$index], 0) }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-red-500 h-3 rounded-full transition-all duration-500" style="width: {{ $budgetSpendingTrends['total_budget'] > 0 ? min(100, ($budgetSpendingTrends['spent'][$index] / $budgetSpendingTrends['total_budget']) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Budget Forecast/Projections -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Budget Forecast & Projections</h2>
                        <p class="text-gray-600 mt-1 text-sm">Projected spending based on current trends</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-blue-50 rounded-xl p-3">
                        <p class="text-xs text-gray-600 mb-1">Current Spent</p>
                        <p class="text-lg font-bold text-blue-600">${{ number_format($budgetForecast['current_spent'], 0) }}</p>
                    </div>
                    <div class="bg-purple-50 rounded-xl p-3">
                        <p class="text-xs text-gray-600 mb-1">Avg Monthly</p>
                        <p class="text-lg font-bold text-purple-600">${{ number_format($budgetForecast['avg_monthly_spending'], 0) }}</p>
                    </div>
                    <div class="bg-orange-50 rounded-xl p-3">
                        <p class="text-xs text-gray-600 mb-1">Projected</p>
                        <p class="text-lg font-bold text-orange-600">${{ number_format($budgetForecast['projected_spending'], 0) }}</p>
                    </div>
                    <div class="bg-{{ $budgetForecast['risk_level'] === 'high' ? 'red' : ($budgetForecast['risk_level'] === 'medium' ? 'yellow' : 'green') }}-50 rounded-xl p-3">
                        <p class="text-xs text-gray-600 mb-1">Risk Level</p>
                        <p class="text-lg font-bold text-{{ $budgetForecast['risk_level'] === 'high' ? 'red' : ($budgetForecast['risk_level'] === 'medium' ? 'yellow' : 'green') }}-600 capitalize">
                            {{ $budgetForecast['risk_level'] }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-600 mb-1">Burn Rate</p>
                        <p class="text-lg font-bold text-gray-900">{{ $budgetForecast['burn_rate_months'] }} months</p>
                        <p class="text-xs text-gray-500">Est: {{ $budgetForecast['estimated_completion'] }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-600 mb-1">Projected Remaining</p>
                        <p class="text-lg font-bold {{ $budgetForecast['projected_remaining'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            ${{ number_format($budgetForecast['projected_remaining'], 0) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================== --}}
        {{-- SECTION 8: ISSUES --}}
        {{-- ============================================== --}}

        <!-- 17. Issue Resolution Metrics -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Issue Resolution Metrics</h2>
                    <p class="text-gray-600 mt-1">Organization-wide issue handling performance</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Avg Turnaround</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $issueResolution['avg_turnaround_days'] }} days</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $issueResolution['avg_turnaround_hours'] }} hours</p>
                </div>
                <div class="bg-green-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Resolution Rate</p>
                    <p class="text-2xl font-bold text-green-600">{{ $issueResolution['resolution_rate'] }}%</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $issueResolution['total_resolved'] }}/{{ $issueResolution['total_issues'] }} resolved</p>
                </div>
                <div class="bg-red-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Open Issues</p>
                    <p class="text-2xl font-bold text-red-600">{{ $issueResolution['open_issues'] }}</p>
                </div>
             
                <div class="bg-orange-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Closed Issues</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $issueResolution['closed_issues'] }}</p>
                </div>
            </div>

            <div class="mt-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Overall Resolution Progress</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $issueResolution['resolution_rate'] }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="bg-green-500 h-3 rounded-full transition-all duration-500" style="width: {{ $issueResolution['resolution_rate'] }}%"></div>
                </div>
            </div>
        </div>

        {{-- ============================================== --}}
        {{-- SECTION 9: TRENDS & ANALYSIS --}}
        {{-- ============================================== --}}

        <!-- 18. Time-Based Trends & Comparative Analysis -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Time-Based Trends -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Time-Based Trends</h2>
                        <p class="text-gray-600 mt-1 text-sm">Task creation and completion trends</p>
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach($timeBasedTrends as $trend)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">{{ $trend['period'] }}</span>
                            <div class="flex items-center gap-4">
                                <span class="text-xs text-gray-600">Created: {{ $trend['tasks_created'] }}</span>
                                <span class="text-xs text-gray-600">Completed: {{ $trend['tasks_completed'] }}</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-green-500 h-3 rounded-full transition-all duration-500" style="width: {{ $trend['tasks_created'] > 0 ? min(100, ($trend['tasks_completed'] / $trend['tasks_created']) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Comparative Analysis Summary -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Comparative Analysis</h2>
                        <p class="text-gray-600 mt-1 text-sm">Period-over-period comparison</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <!-- Tasks -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <span class="font-medium text-gray-900">Total Tasks</span>
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-600">{{ $comparativeAnalysis['current_period']['total_tasks'] }}</span>
                            <span class="font-semibold {{ $comparativeAnalysis['comparison']['tasks']['trend'] === 'up' ? 'text-green-600' : ($comparativeAnalysis['comparison']['tasks']['trend'] === 'down' ? 'text-red-600' : 'text-gray-600') }}">
                                {{ $comparativeAnalysis['comparison']['tasks']['difference'] > 0 ? '+' : '' }}{{ $comparativeAnalysis['comparison']['tasks']['difference'] }}
                            </span>
                        </div>
                    </div>

                    <!-- Completion Rate -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <span class="font-medium text-gray-900">Completion Rate</span>
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-600">{{ $comparativeAnalysis['current_period']['completion_rate'] }}%</span>
                            <span class="font-semibold {{ $comparativeAnalysis['comparison']['completion_rate']['trend'] === 'up' ? 'text-green-600' : ($comparativeAnalysis['comparison']['completion_rate']['trend'] === 'down' ? 'text-red-600' : 'text-gray-600') }}">
                                {{ $comparativeAnalysis['comparison']['completion_rate']['difference'] > 0 ? '+' : '' }}{{ $comparativeAnalysis['comparison']['completion_rate']['difference'] }}%
                            </span>
                        </div>
                    </div>

                    <!-- Budget Spent -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <span class="font-medium text-gray-900">Budget Spent</span>
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-600">${{ number_format($comparativeAnalysis['current_period']['total_spent'], 0) }}</span>
                            <span class="font-semibold {{ $comparativeAnalysis['comparison']['budget']['trend'] === 'up' ? 'text-red-600' : ($comparativeAnalysis['comparison']['budget']['trend'] === 'down' ? 'text-green-600' : 'text-gray-600') }}">
                                {{ $comparativeAnalysis['comparison']['budget']['difference'] > 0 ? '+' : '' }}${{ number_format($comparativeAnalysis['comparison']['budget']['difference'], 0) }}
                            </span>
                        </div>
                    </div>

                    <!-- Issues Resolved -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <span class="font-medium text-gray-900">Issues Resolved</span>
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-600">{{ $comparativeAnalysis['current_period']['resolved_issues'] }}</span>
                            <span class="font-semibold {{ $comparativeAnalysis['comparison']['resolved']['trend'] === 'up' ? 'text-green-600' : ($comparativeAnalysis['comparison']['resolved']['trend'] === 'down' ? 'text-red-600' : 'text-gray-600') }}">
                                {{ $comparativeAnalysis['comparison']['resolved']['difference'] > 0 ? '+' : '' }}{{ $comparativeAnalysis['comparison']['resolved']['difference'] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

       

        <!-- 4. Risk Indicators -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Risk Indicators</h2>
                    <p class="text-gray-600 mt-1">Departments at risk requiring attention</p>
                </div>
            </div>

            @if(count($riskIndicators) > 0)
            <div class="space-y-4">
                @foreach($riskIndicators as $risk)
                <div class="bg-{{ $risk['risk_level'] === 'critical' ? 'red' : ($risk['risk_level'] === 'high' ? 'orange' : 'yellow') }}-50 rounded-xl p-4 border border-{{ $risk['risk_level'] === 'critical' ? 'red' : ($risk['risk_level'] === 'high' ? 'orange' : 'yellow') }}-200">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="font-semibold text-gray-900">{{ $risk['department'] }}</h3>
                                <span class="px-2 py-1 text-xs font-bold rounded-full bg-{{ $risk['risk_level'] === 'critical' ? 'red' : ($risk['risk_level'] === 'high' ? 'orange' : 'yellow') }}-500 text-white">
                                    {{ strtoupper($risk['risk_level']) }}
                                </span>
                            </div>
                            <ul class="list-disc list-inside space-y-1 text-sm text-gray-700">
                                @foreach($risk['factors'] as $factor)
                                <li>{{ $factor }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8 text-gray-500">
                <svg class="mx-auto h-12 w-12 text-green-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p>No risk indicators at this time</p>
            </div>
            @endif
        </div>


        {{-- ============================================== --}}
        {{-- SECTION 10: ACTIVITY FEED --}}
        {{-- ============================================== --}}

        <!-- 19. Recent Activity Feed -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Recent Activity Feed</h2>
                    <p class="text-gray-600 mt-1">Latest activities across the organization</p>
                </div>
            </div>

            <div class="space-y-3 max-h-96 overflow-y-auto">
                @forelse($recentActivity as $activity)
                <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                    <div class="p-2 bg-{{ $activity['color'] }}-100 rounded-lg">
                        <svg class="w-5 h-5 text-{{ $activity['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($activity['icon'] === 'check-circle')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            @elseif($activity['icon'] === 'exclamation-circle')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            @endif
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900 text-sm">{{ $activity['title'] }}</p>
                        <p class="text-xs text-gray-600 mt-1">{{ $activity['department'] }} • {{ $activity['user'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::parse($activity['timestamp'])->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-500">No recent activity</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Department Drill-Down Modal -->
@if($showDepartmentModal && $selectedDepartmentDetails)
<x-modal wire:model="showDepartmentModal" title="Department Details" class="backdrop-blur">
    <div class="space-y-6">
        <div>
            <h3 class="text-xl font-bold text-gray-900 mb-4">{{ $selectedDepartmentDetails['department']->name ?? 'Department' }}</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 rounded-xl p-4">
                <p class="text-sm text-gray-600 mb-1">Total Tasks</p>
                <p class="text-2xl font-bold text-blue-600">{{ $selectedDepartmentDetails['tasks']['total'] }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $selectedDepartmentDetails['tasks']['completion_rate'] }}% completed</p>
            </div>
            <div class="bg-green-50 rounded-xl p-4">
                <p class="text-sm text-gray-600 mb-1">Budget</p>
                <p class="text-2xl font-bold text-green-600">${{ number_format($selectedDepartmentDetails['budget']['total'], 0) }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $selectedDepartmentDetails['budget']['percentage_spent'] }}% spent</p>
            </div>
            <div class="bg-purple-50 rounded-xl p-4">
                <p class="text-sm text-gray-600 mb-1">Issues</p>
                <p class="text-2xl font-bold text-purple-600">{{ $selectedDepartmentDetails['issues']['total'] }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $selectedDepartmentDetails['issues']['resolved'] }} resolved</p>
            </div>
        </div>

        <div>
            <h4 class="font-semibold text-gray-900 mb-3">Team Members</h4>
            <div class="space-y-2">
                @foreach($selectedDepartmentDetails['employees'] as $employee)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">{{ $employee['name'] }}</p>
                        <p class="text-sm text-gray-600">{{ $employee['position'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <x-button wire:click="closeDepartmentModal" class="btn-secondary">Close</x-button>
        </div>
    </div>
</x-modal>
@endif

@else
<div class="min-h-screen bg-gray-50 flex items-center justify-center">
    <div class="text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Access Denied</h3>
        <p class="mt-1 text-sm text-gray-500">You do not have permission to access this page.</p>
        <div class="mt-6">
            <a href="{{ route('admin.home') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                Return to Home
            </a>
        </div>
    </div>
</div>
@endcan
