<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50">
    <!-- Modern Breadcrumbs -->
    <div class="bg-white/80 backdrop-blur-sm border-b border-gray-200 px-4 py-3">
        <div class="max-w-7xl mx-auto">
            <x-breadcrumbs :items="$breadcrumbs" 
                class="bg-gray-50 p-3 rounded-xl overflow-x-auto whitespace-nowrap"
                link-item-class="text-base hover:text-blue-600 transition-colors" />
        </div>
    </div>

    <!-- Executive Header -->
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 shadow-xl border-b border-indigo-700 mb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between">
<div>
                    <h1 class="text-4xl font-bold text-white mb-3 tracking-tight flex items-center gap-3">
                        <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        Issue Tracker Dashboard
                    </h1>
                    <p class="text-indigo-100 text-lg">Executive overview of issue management and performance metrics</p>
                </div>
                <div class="hidden md:flex items-center gap-2 bg-white/20 backdrop-blur-sm px-5 py-3 rounded-xl">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-white font-semibold">{{ now()->format('F j, Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <!-- Overall Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-xl p-6 text-white transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                </div>
                <div class="space-y-1">
                    <p class="text-blue-100 text-sm font-semibold uppercase tracking-wide">Total Issues</p>
                    <p class="text-5xl font-bold">{{ $overallStats['total'] }}</p>
                    <p class="text-blue-200 text-xs">All time</p>
                </div>
            </div>

            <div class="bg-gradient-to-br from-yellow-500 to-orange-600 rounded-2xl shadow-xl p-6 text-white transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="space-y-1">
                    <p class="text-yellow-100 text-sm font-semibold uppercase tracking-wide">Active Issues</p>
                    <p class="text-5xl font-bold">{{ $overallStats['open'] + $overallStats['in_progress'] }}</p>
                    <p class="text-yellow-200 text-xs">{{ $overallStats['open'] }} open, {{ $overallStats['in_progress'] }} in progress</p>
                </div>
            </div>

            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl shadow-xl p-6 text-white transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
                <div class="space-y-1">
                    <p class="text-red-100 text-sm font-semibold uppercase tracking-wide">High Priority</p>
                    <p class="text-5xl font-bold">{{ $overallStats['high_priority'] }}</p>
                    <p class="text-red-200 text-xs">Requires attention</p>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-xl p-6 text-white transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="space-y-1">
                    <p class="text-green-100 text-sm font-semibold uppercase tracking-wide">Resolved</p>
                    <p class="text-5xl font-bold">{{ $overallStats['resolved'] + $overallStats['closed'] }}</p>
                    <p class="text-green-200 text-xs">
                        {{ $overallStats['total'] > 0 ? round((($overallStats['resolved'] + $overallStats['closed']) / $overallStats['total']) * 100, 1) : 0 }}% resolution rate
                    </p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-7 gap-4">
                <x-input 
                    wire:model.live.debounce.300ms="search" 
                    icon="o-magnifying-glass" 
                    placeholder="Search..." 
                />
                
                <x-select 
                    wire:model.live="filterStatus" 
                    :options="[
                        ['id' => '', 'name' => 'All Statuses'],
                        ['id' => 'open', 'name' => 'Open'],
                        ['id' => 'in_progress', 'name' => 'In Progress'],
                        ['id' => 'resolved', 'name' => 'Resolved'],
                        ['id' => 'closed', 'name' => 'Closed']
                    ]" 
                    option-label="name" 
                    option-value="id"
                />
                
                <x-select 
                    wire:model.live="filterPriority" 
                    :options="[
                        ['id' => '', 'name' => 'All Priorities'],
                        ['id' => 'Low', 'name' => 'Low'],
                        ['id' => 'Medium', 'name' => 'Medium'],
                        ['id' => 'High', 'name' => 'High']
                    ]" 
                    option-label="name" 
                    option-value="id"
                />

                <x-select 
                    wire:model.live="filterDepartment" 
                    :options="[['id' => '', 'name' => 'All Departments'], ...$departments->map(fn($d) => ['id' => $d->id, 'name' => $d->name])]" 
                    option-label="name" 
                    option-value="id"
                />

                <x-select 
                    wire:model.live="filterGroup" 
                    :options="[['id' => '', 'name' => 'All Groups'], ...$issueGroups->map(fn($g) => ['id' => $g->id, 'name' => $g->name])]" 
                    option-label="name" 
                    option-value="id"
                />

                <x-select 
                    wire:model.live="groupBy" 
                    :options="[
                        ['id' => 'status', 'name' => 'Group by Status'],
                        ['id' => 'group', 'name' => 'Group by Issue Group'],
                        ['id' => 'type', 'name' => 'Group by Type'],
                        ['id' => 'department', 'name' => 'Group by Department'],
                        ['id' => 'none', 'name' => 'No Grouping']
                    ]" 
                    option-label="name" 
                    option-value="id"
                    icon="o-squares-2x2"
                />
                
                <x-button 
                    icon="o-arrow-path" 
                    label="Reset" 
                    wire:click="$set('search', ''); $set('filterStatus', ''); $set('filterPriority', ''); $set('filterDepartment', ''); $set('filterGroup', ''); $set('filterType', '')" 
                    class="btn-outline"
                />
            </div>
        </div>

        <!-- Department Performance & TAT -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-purple-500 to-indigo-600 px-6 py-4">
                <h2 class="text-2xl font-bold text-white flex items-center gap-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Department Performance & Turnaround Time
                </h2>
                <p class="text-purple-100 mt-1">Average resolution time per department</p>
            </div>
            
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b-2 border-gray-200">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Department</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-700">Total</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-700">Open</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-700">In Progress</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-700">Resolved</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-700">Avg TAT (Hours)</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-700">Avg TAT (Days)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($departmentStats as $dept)
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                <td class="py-3 px-4">
                                    <span class="font-semibold text-gray-900">{{ $dept['name'] }}</span>
                                </td>
                                <td class="text-center py-3 px-4">
                                    <span class="px-3 py-1 bg-gray-100 text-gray-900 rounded-full text-sm font-bold">{{ $dept['total'] }}</span>
                                </td>
                                <td class="text-center py-3 px-4">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">{{ $dept['open'] }}</span>
                                </td>
                                <td class="text-center py-3 px-4">
                                    <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-semibold">{{ $dept['in_progress'] }}</span>
                                </td>
                                <td class="text-center py-3 px-4">
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">{{ $dept['resolved'] }}</span>
                                </td>
                                <td class="text-center py-3 px-4">
                                    <span class="text-gray-900 font-bold">{{ $dept['avg_tat_hours'] }}</span>
                                </td>
                                <td class="text-center py-3 px-4">
                                    <span class="px-3 py-1 {{ $dept['avg_tat_days'] <= 1 ? 'bg-green-100 text-green-800' : ($dept['avg_tat_days'] <= 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }} rounded-full text-sm font-bold">
                                        {{ $dept['avg_tat_days'] }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-gray-500">No department data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- User Performance & TAT -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-blue-500 to-cyan-600 px-6 py-4">
                <h2 class="text-2xl font-bold text-white flex items-center gap-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Top User Performance
                </h2>
                <p class="text-blue-100 mt-1">Individual performance and resolution metrics</p>
            </div>
            
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b-2 border-gray-200">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">User</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Department</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-700">Assigned</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-700">Resolved</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-700">Pending</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-700">Resolution %</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-700">Avg TAT (Hours)</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-700">Avg TAT (Days)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($userPerformance as $user)
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">
                                            {{ substr($user['name'], 0, 1) }}
                                        </div>
                                        <span class="font-semibold text-gray-900">{{ $user['name'] }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-sm text-gray-600">{{ $user['department'] }}</span>
                                </td>
                                <td class="text-center py-3 px-4">
                                    <span class="px-3 py-1 bg-gray-100 text-gray-900 rounded-full text-sm font-bold">{{ $user['total_assigned'] }}</span>
                                </td>
                                <td class="text-center py-3 px-4">
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">{{ $user['resolved'] }}</span>
                                </td>
                                <td class="text-center py-3 px-4">
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">{{ $user['pending'] }}</span>
                                </td>
                                <td class="text-center py-3 px-4">
                                    <div class="flex items-center gap-2 justify-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2">
                                            <div class="bg-gradient-to-r from-green-500 to-green-600 h-2 rounded-full" style="width: {{ $user['resolution_rate'] }}%"></div>
                                        </div>
                                        <span class="text-sm font-bold text-gray-900">{{ $user['resolution_rate'] }}%</span>
                                    </div>
                                </td>
                                <td class="text-center py-3 px-4">
                                    <span class="text-gray-900 font-bold">{{ $user['avg_tat_hours'] }}</span>
                                </td>
                                <td class="text-center py-3 px-4">
                                    <span class="px-3 py-1 {{ $user['avg_tat_days'] <= 1 ? 'bg-green-100 text-green-800' : ($user['avg_tat_days'] <= 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }} rounded-full text-sm font-bold">
                                        {{ $user['avg_tat_days'] }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-gray-500">No user performance data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Issues by Group -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-pink-500 to-rose-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Issues by Group
                    </h2>
                </div>
                <div class="p-6">
                    @forelse($issuesByGroup as $group)
                    <div class="mb-4 last:mb-0">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-semibold text-gray-900">{{ $group['name'] }}</span>
                            <span class="text-sm text-gray-600">{{ $group['count'] }} issues</span>
                        </div>
                        <div class="flex gap-2">
                            <div class="flex-1 bg-blue-100 rounded-lg p-2 text-center">
                                <p class="text-xs text-blue-600 mb-1">Open</p>
                                <p class="text-lg font-bold text-blue-900">{{ $group['open'] }}</p>
                            </div>
                            <div class="flex-1 bg-green-100 rounded-lg p-2 text-center">
                                <p class="text-xs text-green-600 mb-1">Resolved</p>
                                <p class="text-lg font-bold text-green-900">{{ $group['resolved'] }}</p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-gray-500 py-4">No data available</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        Issues by Type
                    </h2>
                </div>
                <div class="p-6 max-h-96 overflow-y-auto">
                    @forelse($issuesByType as $type)
                    <div class="mb-3 last:mb-0 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900">{{ $type['name'] }}</p>
                                <p class="text-xs text-gray-600">{{ $type['department'] }}</p>
                            </div>
                            <span class="px-3 py-1 bg-indigo-100 text-indigo-900 rounded-full text-sm font-bold">{{ $type['count'] }}</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-gray-500 py-4">No data available</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Summary Count -->
        <div class="text-center text-gray-500 text-sm mb-4">
            Showing {{ $groupedIssues->flatten(1)->count() }} of {{ $overallStats['total'] }} total issues
        </div>
    </div>
</div>
