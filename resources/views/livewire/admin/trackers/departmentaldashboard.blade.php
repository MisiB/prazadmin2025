@can('departmentaloverview.access')
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
                        {{ $userDepartment->name ?? 'Department' }} Dashboard
                    </h1>
                    <p class="text-gray-600">Comprehensive overview of your department's performance</p>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <x-select wire:model.live="currentWeekId" :options="$weeks" option-label="week" option-value="id" placeholder="Select Week" class="min-w-[200px]" />
                    <x-select wire:model.live="currentBudgetId" :options="$budgets" option-label="name" option-value="id" placeholder="Select Budget" class="min-w-[200px]" />
                    <x-button wire:click="exportDashboardReport" class="btn-primary" spinner="exportDashboardReport">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export Report
                    </x-button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <!-- 1. Key Metrics Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Department Users -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                </div>
                <div class="space-y-1">
                    <p class="text-blue-100 text-sm font-semibold uppercase tracking-wide">Team Members</p>
                    <p class="text-4xl font-bold">{{ $departmentUsersCount }}</p>
                </div>
            </div>

            <!-- Total Tasks -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
                <div class="space-y-1">
                    <p class="text-green-100 text-sm font-semibold uppercase tracking-wide">Total Tasks</p>
                    <p class="text-4xl font-bold">{{ $tasksMetrics['total'] }}</p>
                    <p class="text-green-100 text-sm">{{ $tasksMetrics['completion_rate'] }}% completed</p>
                </div>
            </div>

            <!-- Budget -->
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="space-y-1">
                    <p class="text-purple-100 text-sm font-semibold uppercase tracking-wide">Budget Remaining</p>
                    <p class="text-4xl font-bold">${{ number_format($budgetMetrics['total_remaining'], 0) }}</p>
                    <p class="text-purple-100 text-sm">{{ $budgetMetrics['percentage_spent'] }}% spent</p>
                </div>
            </div>

            <!-- Issues -->
            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
                <div class="space-y-1">
                    <p class="text-red-100 text-sm font-semibold uppercase tracking-wide">Open Issues</p>
                    <p class="text-4xl font-bold">{{ $issuesMetrics['open'] + $issuesMetrics['in_progress'] }}</p>
                    <p class="text-red-100 text-sm">{{ $issuesMetrics['total'] }} total</p>
                </div>
            </div>
        </div>

        <!-- Department Health Scorecard -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Department Health Scorecard</h2>
                    <p class="text-gray-600 mt-1">Overall performance indicator</p>
                </div>
                <div class="text-right">
                    <div class="text-4xl font-bold text-{{ $departmentHealthScorecard['status_color'] }}-600">
                        {{ $departmentHealthScorecard['overall_score'] }}%
                    </div>
                    <div class="text-sm font-semibold text-{{ $departmentHealthScorecard['status_color'] }}-600 mt-1">
                        {{ $departmentHealthScorecard['status'] }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                @foreach($departmentHealthScorecard['factors'] as $factor)
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">{{ $factor['name'] }}</span>
                        <span class="text-xs text-gray-500">({{ $factor['weight'] }}%)</span>
                    </div>
                    <div class="text-2xl font-bold text-{{ $factor['status'] === 'good' ? 'green' : ($factor['status'] === 'fair' ? 'yellow' : 'red') }}-600">
                        {{ $factor['score'] }}%
                    </div>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-{{ $factor['status'] === 'good' ? 'green' : ($factor['status'] === 'fair' ? 'yellow' : 'red') }}-500 h-2 rounded-full" style="width: {{ $factor['score'] }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- 2. Upcoming Deadlines & Alerts (Actionable Items - High Priority) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Upcoming Deadlines & Alerts</h2>
                    <p class="text-gray-600 mt-1">Actionable items requiring immediate attention</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Overdue Tasks -->
                <div class="bg-red-50 rounded-xl p-4 border border-red-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-red-900">Overdue Tasks</h3>
                        <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">{{ $upcomingDeadlines['overdue']->count() }}</span>
                    </div>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @forelse($upcomingDeadlines['overdue'] as $task)
                        <div class="bg-white rounded-lg p-3 border border-red-200">
                            <p class="font-medium text-sm text-gray-900 mb-1">{{ Str::limit($task['title'], 40) }}</p>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-600">{{ $task['user_name'] }}</span>
                                <span class="text-red-600 font-semibold">{{ abs($task['days_overdue']) }} days overdue</span>
                            </div>
                            <div class="mt-1">
                                <span class="px-2 py-0.5 text-xs rounded {{ $task['priority'] === 'High' ? 'bg-red-100 text-red-800' : ($task['priority'] === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ $task['priority'] }}
                                </span>
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500 text-center py-4">No overdue tasks</p>
                        @endforelse
                    </div>
                </div>

                <!-- Due Soon -->
                <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-yellow-900">Due in Next 7 Days</h3>
                        <span class="bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded-full">{{ $upcomingDeadlines['due_soon']->count() }}</span>
                    </div>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @forelse($upcomingDeadlines['due_soon'] as $task)
                        <div class="bg-white rounded-lg p-3 border border-yellow-200">
                            <p class="font-medium text-sm text-gray-900 mb-1">{{ Str::limit($task['title'], 40) }}</p>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-600">{{ $task['user_name'] }}</span>
                                <span class="text-yellow-600 font-semibold">{{ $task['days_until_due'] }} days left</span>
                            </div>
                            <div class="mt-1">
                                <span class="px-2 py-0.5 text-xs rounded {{ $task['priority'] === 'High' ? 'bg-red-100 text-red-800' : ($task['priority'] === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ $task['priority'] }}
                                </span>
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500 text-center py-4">No tasks due soon</p>
                        @endforelse
                    </div>
                </div>

                <!-- Critical Issues -->
                <div class="bg-orange-50 rounded-xl p-4 border border-orange-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-orange-900">Critical Issues</h3>
                        <span class="bg-orange-500 text-white text-xs font-bold px-2 py-1 rounded-full">{{ $upcomingDeadlines['critical_issues']->count() }}</span>
                    </div>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @forelse($upcomingDeadlines['critical_issues'] as $issue)
                        <div class="bg-white rounded-lg p-3 border border-orange-200">
                            <p class="font-medium text-sm text-gray-900 mb-1">{{ Str::limit($issue['title'], 40) }}</p>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-600">{{ $issue['ticketnumber'] }}</span>
                                <span class="text-orange-600 font-semibold">{{ $issue['days_open'] }} days open</span>
                            </div>
                            <div class="mt-1">
                                <span class="px-2 py-0.5 text-xs rounded bg-red-100 text-red-800">
                                    High Priority
                                </span>
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500 text-center py-4">No critical issues</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Detailed Sections (Current State) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Tasks Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Tasks Overview</h2>
                    <a href="{{ route('admin.trackers.performancetracker') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">
                        View Details →
                    </a>
                </div>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-green-50 rounded-xl p-4">
                            <p class="text-sm text-gray-600 mb-1">Completed</p>
                            <p class="text-2xl font-bold text-green-600">{{ $tasksMetrics['completed'] }}</p>
                        </div>
                        <div class="bg-yellow-50 rounded-xl p-4">
                            <p class="text-sm text-gray-600 mb-1">Ongoing</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ $tasksMetrics['ongoing'] }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-sm text-gray-600 mb-1">Pending</p>
                            <p class="text-2xl font-bold text-gray-600">{{ $tasksMetrics['pending'] }}</p>
                        </div>
                        <div class="bg-red-50 rounded-xl p-4">
                            <p class="text-sm text-gray-600 mb-1">Overdue</p>
                            <p class="text-2xl font-bold text-red-600">{{ $tasksMetrics['overdue'] }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Linked to Workplan</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $tasksMetrics['linked_percentage'] }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-500" style="width: {{ $tasksMetrics['linked_percentage'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Budget Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Budget Overview</h2>
                    <a href="{{ route('admin.trackers.budgettracker') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">
                        View Details →
                    </a>
                </div>
                
                <div class="space-y-4">
                    <div class="bg-blue-50 rounded-xl p-4">
                        <p class="text-sm text-gray-600 mb-1">Total Budget</p>
                        <p class="text-2xl font-bold text-blue-600">${{ number_format($budgetMetrics['total_budget'], 2) }}</p>
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Spent</span>
                            <span class="text-sm font-semibold text-red-600">${{ number_format($budgetMetrics['total_spent'], 2) }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-red-500 h-3 rounded-full transition-all duration-500" style="width: {{ $budgetMetrics['percentage_spent'] }}%"></div>
                        </div>
                    </div>
                    
                    <div class="bg-green-50 rounded-xl p-4">
                        <p class="text-sm text-gray-600 mb-1">Remaining</p>
                        <p class="text-2xl font-bold text-green-600">${{ number_format($budgetMetrics['total_remaining'], 2) }}</p>
                    </div>
                    
                    <div class="text-sm text-gray-600">
                        <span class="font-semibold">{{ $budgetMetrics['items_count'] }}</span> budget items
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Issues Section (Current State) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Issues Overview</h2>
                <a href="{{ route('admin.departmentalissues') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">
                    View All →
                </a>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-gray-50 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ $issuesMetrics['total'] }}</p>
                    <p class="text-sm text-gray-600 mt-1">Total</p>
                </div>
                <div class="bg-blue-50 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-blue-600">{{ $issuesMetrics['open'] }}</p>
                    <p class="text-sm text-gray-600 mt-1">Open</p>
                </div>
                <div class="bg-yellow-50 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-yellow-600">{{ $issuesMetrics['in_progress'] }}</p>
                    <p class="text-sm text-gray-600 mt-1">In Progress</p>
                </div>
                <div class="bg-green-50 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-green-600">{{ $issuesMetrics['resolved'] }}</p>
                    <p class="text-sm text-gray-600 mt-1">Resolved</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-gray-600">{{ $issuesMetrics['closed'] }}</p>
                    <p class="text-sm text-gray-600 mt-1">Closed</p>
                </div>
            </div>

            @if($recentIssues->count() > 0)
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Issues</h3>
                <div class="space-y-3">
                    @foreach($recentIssues as $issue)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900">{{ $issue->title }}</p>
                            <p class="text-sm text-gray-600">{{ $issue->ticketnumber }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                @if($issue->status === 'open') bg-blue-100 text-blue-800
                                @elseif($issue->status === 'in_progress') bg-yellow-100 text-yellow-800
                                @elseif($issue->status === 'resolved') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $issue->status)) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- 5. Team Performance Breakdown (Individual Contributions) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Team Performance Breakdown</h2>
                    <p class="text-gray-600 mt-1">Individual contributions and performance metrics</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Team Member</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Position</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Tasks</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Completed</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Overdue</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Completion Rate</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Weekly Rate</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Issues Resolved</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($teamPerformance as $member)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium text-gray-900">{{ $member['name'] }}</td>
                            <td class="py-3 px-4 text-center text-gray-600">{{ $member['position'] }}</td>
                            <td class="py-3 px-4 text-center">{{ $member['total_tasks'] }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="font-semibold text-green-600">{{ $member['completed_tasks'] }}</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($member['overdue_tasks'] > 0)
                                    <span class="font-semibold text-red-600">{{ $member['overdue_tasks'] }}</span>
                                @else
                                    <span class="text-gray-400">0</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <span class="font-semibold {{ $member['completion_rate'] >= 80 ? 'text-green-600' : ($member['completion_rate'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ $member['completion_rate'] }}%
                                    </span>
                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                        <div class="bg-{{ $member['completion_rate'] >= 80 ? 'green' : ($member['completion_rate'] >= 60 ? 'yellow' : 'red') }}-500 h-2 rounded-full" style="width: {{ $member['completion_rate'] }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="font-semibold text-blue-600">{{ $member['weekly_completion_rate'] }}%</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="font-semibold text-purple-600">{{ $member['resolved_issues'] }}/{{ $member['total_issues'] }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-gray-500">No team members found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 6. Weekly Completion Rate Trends & Task Priority Distribution (Trends & Analysis) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Weekly Completion Rate Trends -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Weekly Completion Rate Trends</h2>
                        <p class="text-gray-600 mt-1">Department average over last 8 weeks</p>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Current Week Average</span>
                        <span class="text-2xl font-bold text-blue-600">{{ $weeklyTrends['current_week_avg'] }}%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Previous Week Average</span>
                        <span class="text-lg font-semibold text-gray-600">{{ $weeklyTrends['previous_week_avg'] }}%</span>
                    </div>
                    @if($weeklyTrends['current_week_avg'] > $weeklyTrends['previous_week_avg'])
                        <div class="mt-2 text-sm text-green-600 font-semibold">
                            ↑ {{ round($weeklyTrends['current_week_avg'] - $weeklyTrends['previous_week_avg'], 1) }}% improvement
                        </div>
                    @elseif($weeklyTrends['current_week_avg'] < $weeklyTrends['previous_week_avg'])
                        <div class="mt-2 text-sm text-red-600 font-semibold">
                            ↓ {{ round($weeklyTrends['previous_week_avg'] - $weeklyTrends['current_week_avg'], 1) }}% decrease
                        </div>
                    @endif
                </div>

                <div class="space-y-2">
                    @foreach($weeklyTrends['labels'] as $index => $label)
                        <div class="flex items-center gap-3">
                            <div class="w-24 text-xs text-gray-600">{{ $label }}</div>
                            <div class="flex-1 bg-gray-200 rounded-full h-4">
                                <div class="bg-blue-500 h-4 rounded-full transition-all duration-500" style="width: {{ $weeklyTrends['department_average'][$index] }}%"></div>
                            </div>
                            <div class="w-12 text-right text-sm font-semibold text-gray-700">{{ $weeklyTrends['department_average'][$index] }}%</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Task Priority Distribution -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Task Priority Distribution</h2>
                        <p class="text-gray-600 mt-1">Current month breakdown by priority</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <!-- High Priority -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                <span class="font-medium text-gray-700">High Priority</span>
                            </div>
                            <span class="font-bold text-red-600">{{ $priorityDistribution['high'] }} ({{ $priorityDistribution['high_percentage'] }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-red-500 h-3 rounded-full transition-all duration-500" style="width: {{ $priorityDistribution['high_percentage'] }}%"></div>
                        </div>
                    </div>

                    <!-- Medium Priority -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                <span class="font-medium text-gray-700">Medium Priority</span>
                            </div>
                            <span class="font-bold text-yellow-600">{{ $priorityDistribution['medium'] }} ({{ $priorityDistribution['medium_percentage'] }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-yellow-500 h-3 rounded-full transition-all duration-500" style="width: {{ $priorityDistribution['medium_percentage'] }}%"></div>
                        </div>
                    </div>

                    <!-- Low Priority -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                <span class="font-medium text-gray-700">Low Priority</span>
                            </div>
                            <span class="font-bold text-green-600">{{ $priorityDistribution['low'] }} ({{ $priorityDistribution['low_percentage'] }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-green-500 h-3 rounded-full transition-all duration-500" style="width: {{ $priorityDistribution['low_percentage'] }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200">
                    <div class="text-center">
                        <span class="text-2xl font-bold text-gray-900">{{ $priorityDistribution['total'] }}</span>
                        <p class="text-sm text-gray-600 mt-1">Total Tasks This Month</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 7. Issue Resolution Metrics (Performance Metrics) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Issue Resolution Metrics</h2>
                    <p class="text-gray-600 mt-1">Department issue handling performance</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Average Turnaround</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $issueResolutionMetrics['avg_turnaround_days'] }} days</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $issueResolutionMetrics['avg_turnaround_hours'] }} hours</p>
                </div>

                <div class="bg-green-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Resolution Rate</p>
                    <p class="text-2xl font-bold text-green-600">{{ $issueResolutionMetrics['resolution_rate'] }}%</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $issueResolutionMetrics['total_resolved'] }}/{{ $issueResolutionMetrics['total_issues'] }} resolved</p>
                </div>

                <div class="bg-red-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Open Issues</p>
                    <p class="text-2xl font-bold text-red-600">{{ $issueResolutionMetrics['open_issues'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">Requiring attention</p>
                </div>

                <div class="bg-yellow-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">In Progress</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $issueResolutionMetrics['in_progress_issues'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">Being worked on</p>
                </div>
            </div>

            <div class="mt-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Overall Resolution Progress</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $issueResolutionMetrics['resolution_rate'] }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="bg-green-500 h-3 rounded-full transition-all duration-500" style="width: {{ $issueResolutionMetrics['resolution_rate'] }}%"></div>
                </div>
            </div>
        </div>

        <!-- Workplan Progress/Goals Tracking -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Workplan Progress & Goals</h2>
                    <p class="text-gray-600 mt-1">Department workplan tracking and targets</p>
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
                <div class="bg-orange-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Tasks Linked</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $workplanProgress['linked_tasks_percentage'] }}%</p>
                </div>
            </div>
        </div>

        <!-- Budget Spending Trend Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Budget Spending Trend</h2>
                    <p class="text-gray-600 mt-1">Last 6 months spending analysis</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-blue-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Total Budget</p>
                    <p class="text-2xl font-bold text-blue-600">${{ number_format($budgetSpendingTrend['total_budget'], 0) }}</p>
                </div>
                <div class="bg-red-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Total Spent</p>
                    <p class="text-2xl font-bold text-red-600">${{ number_format($budgetSpendingTrend['total_spent'], 0) }}</p>
                </div>
                <div class="bg-green-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Remaining</p>
                    <p class="text-2xl font-bold text-green-600">${{ number_format($budgetSpendingTrend['total_remaining'], 0) }}</p>
                </div>
            </div>

            <!-- Monthly Spending Bars -->
            <div class="space-y-4">
                @foreach($budgetSpendingTrend['labels'] as $index => $label)
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                        <div class="flex items-center gap-4">
                            <span class="text-xs text-gray-600">Spent: ${{ number_format($budgetSpendingTrend['spent'][$index], 0) }}</span>
                            <span class="text-xs text-gray-600">Cumulative: ${{ number_format($budgetSpendingTrend['cumulative_spent'][$index], 0) }}</span>
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-4">
                        <div class="bg-red-500 h-4 rounded-full transition-all duration-500" style="width: {{ $budgetSpendingTrend['total_budget'] > 0 ? min(100, ($budgetSpendingTrend['spent'][$index] / $budgetSpendingTrend['total_budget']) * 100) : 0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- 8. Monthly Assessment Section (Historical Comparison) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Monthly Assessment</h2>
                    <p class="text-gray-600 mt-1">Performance comparison with previous months</p>
                </div>
            </div>

            <!-- Month Comparison Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                @foreach(['current_month', 'previous_month', 'two_months_ago'] as $period)
                    @php
                        $periodData = $monthlyAssessment[$period];
                        $isCurrent = $period === 'current_month';
                    @endphp
                    <div class="bg-gradient-to-br {{ $isCurrent ? 'from-blue-500 to-blue-600' : 'from-gray-500 to-gray-600' }} rounded-xl shadow-lg p-6 text-white">
                        <h3 class="text-lg font-semibold mb-4">{{ $periodData['name'] }}</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm opacity-90">Tasks Completed</span>
                                <span class="font-bold">{{ $periodData['tasks']['completed'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm opacity-90">Completion Rate</span>
                                <span class="font-bold">{{ $periodData['tasks']['completion_rate'] ?? 0 }}%</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm opacity-90">Budget Spent</span>
                                <span class="font-bold">${{ number_format($periodData['budget_spent'] ?? 0, 0) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm opacity-90">Issues Resolved</span>
                                <span class="font-bold">{{ $periodData['issues']['resolved'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Detailed Comparison Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Metric</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">{{ $monthlyAssessment['current_month']['name'] }}</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">{{ $monthlyAssessment['previous_month']['name'] }}</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Change</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Trend</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <!-- Tasks Total -->
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium text-gray-900">Total Tasks</td>
                            <td class="py-3 px-4 text-center">{{ $monthlyAssessment['current_month']['tasks']['total'] }}</td>
                            <td class="py-3 px-4 text-center">{{ $monthlyAssessment['previous_month']['tasks']['total'] }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="font-semibold {{ $monthlyAssessment['comparison']['tasks']['total']['trend'] === 'up' ? 'text-green-600' : ($monthlyAssessment['comparison']['tasks']['total']['trend'] === 'down' ? 'text-red-600' : 'text-gray-600') }}">
                                    {{ $monthlyAssessment['comparison']['tasks']['total']['difference'] > 0 ? '+' : '' }}{{ $monthlyAssessment['comparison']['tasks']['total']['difference'] }}
                                </span>
                                <span class="text-sm text-gray-500 ml-1">
                                    ({{ $monthlyAssessment['comparison']['tasks']['total']['percentage'] > 0 ? '+' : '' }}{{ $monthlyAssessment['comparison']['tasks']['total']['percentage'] }}%)
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($monthlyAssessment['comparison']['tasks']['total']['trend'] === 'up')
                                    <svg class="w-5 h-5 text-green-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                @elseif($monthlyAssessment['comparison']['tasks']['total']['trend'] === 'down')
                                    <svg class="w-5 h-5 text-red-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                    </svg>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Tasks Completed -->
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium text-gray-900">Tasks Completed</td>
                            <td class="py-3 px-4 text-center">{{ $monthlyAssessment['current_month']['tasks']['completed'] }}</td>
                            <td class="py-3 px-4 text-center">{{ $monthlyAssessment['previous_month']['tasks']['completed'] }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="font-semibold {{ $monthlyAssessment['comparison']['tasks']['completed']['trend'] === 'up' ? 'text-green-600' : ($monthlyAssessment['comparison']['tasks']['completed']['trend'] === 'down' ? 'text-red-600' : 'text-gray-600') }}">
                                    {{ $monthlyAssessment['comparison']['tasks']['completed']['difference'] > 0 ? '+' : '' }}{{ $monthlyAssessment['comparison']['tasks']['completed']['difference'] }}
                                </span>
                                <span class="text-sm text-gray-500 ml-1">
                                    ({{ $monthlyAssessment['comparison']['tasks']['completed']['percentage'] > 0 ? '+' : '' }}{{ $monthlyAssessment['comparison']['tasks']['completed']['percentage'] }}%)
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($monthlyAssessment['comparison']['tasks']['completed']['trend'] === 'up')
                                    <svg class="w-5 h-5 text-green-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                @elseif($monthlyAssessment['comparison']['tasks']['completed']['trend'] === 'down')
                                    <svg class="w-5 h-5 text-red-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                    </svg>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Completion Rate -->
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium text-gray-900">Completion Rate</td>
                            <td class="py-3 px-4 text-center">{{ $monthlyAssessment['current_month']['tasks']['completion_rate'] }}%</td>
                            <td class="py-3 px-4 text-center">{{ $monthlyAssessment['previous_month']['tasks']['completion_rate'] }}%</td>
                            <td class="py-3 px-4 text-center">
                                <span class="font-semibold {{ $monthlyAssessment['comparison']['tasks']['completion_rate']['trend'] === 'up' ? 'text-green-600' : ($monthlyAssessment['comparison']['tasks']['completion_rate']['trend'] === 'down' ? 'text-red-600' : 'text-gray-600') }}">
                                    {{ $monthlyAssessment['comparison']['tasks']['completion_rate']['difference'] > 0 ? '+' : '' }}{{ $monthlyAssessment['comparison']['tasks']['completion_rate']['difference'] }}%
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($monthlyAssessment['comparison']['tasks']['completion_rate']['trend'] === 'up')
                                    <svg class="w-5 h-5 text-green-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                @elseif($monthlyAssessment['comparison']['tasks']['completion_rate']['trend'] === 'down')
                                    <svg class="w-5 h-5 text-red-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                    </svg>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Budget Spent -->
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium text-gray-900">Budget Spent</td>
                            <td class="py-3 px-4 text-center">${{ number_format($monthlyAssessment['current_month']['budget_spent'], 2) }}</td>
                            <td class="py-3 px-4 text-center">${{ number_format($monthlyAssessment['previous_month']['budget_spent'], 2) }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="font-semibold {{ $monthlyAssessment['comparison']['budget']['trend'] === 'up' ? 'text-red-600' : ($monthlyAssessment['comparison']['budget']['trend'] === 'down' ? 'text-green-600' : 'text-gray-600') }}">
                                    {{ $monthlyAssessment['comparison']['budget']['difference'] > 0 ? '+' : '' }}${{ number_format($monthlyAssessment['comparison']['budget']['difference'], 2) }}
                                </span>
                                <span class="text-sm text-gray-500 ml-1">
                                    ({{ $monthlyAssessment['comparison']['budget']['percentage'] > 0 ? '+' : '' }}{{ $monthlyAssessment['comparison']['budget']['percentage'] }}%)
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($monthlyAssessment['comparison']['budget']['trend'] === 'up')
                                    <svg class="w-5 h-5 text-red-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                @elseif($monthlyAssessment['comparison']['budget']['trend'] === 'down')
                                    <svg class="w-5 h-5 text-green-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                    </svg>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Issues Resolved -->
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium text-gray-900">Issues Resolved</td>
                            <td class="py-3 px-4 text-center">{{ $monthlyAssessment['current_month']['issues']['resolved'] }}</td>
                            <td class="py-3 px-4 text-center">{{ $monthlyAssessment['previous_month']['issues']['resolved'] }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="font-semibold {{ $monthlyAssessment['comparison']['issues']['resolved']['trend'] === 'up' ? 'text-green-600' : ($monthlyAssessment['comparison']['issues']['resolved']['trend'] === 'down' ? 'text-red-600' : 'text-gray-600') }}">
                                    {{ $monthlyAssessment['comparison']['issues']['resolved']['difference'] > 0 ? '+' : '' }}{{ $monthlyAssessment['comparison']['issues']['resolved']['difference'] }}
                                </span>
                                <span class="text-sm text-gray-500 ml-1">
                                    ({{ $monthlyAssessment['comparison']['issues']['resolved']['percentage'] > 0 ? '+' : '' }}{{ $monthlyAssessment['comparison']['issues']['resolved']['percentage'] }}%)
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($monthlyAssessment['comparison']['issues']['resolved']['trend'] === 'up')
                                    <svg class="w-5 h-5 text-green-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                @elseif($monthlyAssessment['comparison']['issues']['resolved']['trend'] === 'down')
                                    <svg class="w-5 h-5 text-red-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                    </svg>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

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