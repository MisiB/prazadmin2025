<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50">
    <!-- Modern Breadcrumbs -->
    <div class="bg-white/80 backdrop-blur-sm border-b border-gray-200 px-4 py-3">
        <div class="max-w-7xl mx-auto">
            <x-breadcrumbs :items="$breadcrumbs" 
                class="bg-gray-50 p-3 rounded-xl overflow-x-auto whitespace-nowrap"
                link-item-class="text-base hover:text-blue-600 transition-colors" />
        </div>
    </div>

    <!-- Modern Header with Glassmorphism -->
    <div class="bg-white/80 backdrop-blur-sm shadow-sm border-b border-gray-200 mb-6 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 mb-3 tracking-tight">
                        {{ isset($tasks['calendarweek']) && $tasks['calendarweek'] ? 'Week ' . $tasks['calendarweek']->week : 'Performance Tracker' }}
                    </h1>
                    <div class="flex items-center gap-3 flex-wrap">
                        @if(isset($tasks['calendarweek']) && $tasks['calendarweek'])
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-1.5 rounded-full text-sm font-semibold shadow-lg shadow-blue-500/30">
                            Week {{ $tasks['calendarweek']->week }}
                        </div>
                        <div class="flex items-center gap-2 text-gray-600 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="font-medium">{{ $tasks['calendarweek']->start_date }} - {{ $tasks['calendarweek']->end_date }}</span>
                        </div>
                        @else
                        <p class="text-gray-600">Select a week to view performance data</p>
                        @endif
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3">
                    <x-select wire:model.live="currentWeekId" :options="$weeks" option-label="week" option-value="id" placeholder="Filter by week" class="min-w-[200px]" />
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <!-- Task Summary -->
        <div class="mb-8">
            @if(isset($tasks['users']) && $tasks['users']->count() > 0)
                @php
                    $departmentStats = $this->getDepartmentStats($tasks['users']);
                    $totalTasks = $this->getTotalTasksCount($tasks['users']);
                    $linkedTasks = $this->getLinkedTasksCount($tasks['users']);
                    $unlinkedTasks = $this->getUnlinkedTasksCount($tasks['users']);
                    $linkedPercentage = $this->getLinkedTasksPercentage($tasks['users']);
                @endphp
                
                <!-- Key Metrics Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <!-- Departments Card -->
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
                            <p class="text-4xl font-bold">{{ $departmentStats->count() }}</p>
                        </div>
                    </div>

                    <!-- Users Card -->
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <p class="text-purple-100 text-sm font-semibold uppercase tracking-wide">Total Users</p>
                            <p class="text-4xl font-bold">{{ $tasks['users']->count() }}</p>
                        </div>
                    </div>

                    <!-- Total Tasks Card -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <p class="text-green-100 text-sm font-semibold uppercase tracking-wide">Total Tasks</p>
                            <p class="text-4xl font-bold">{{ $totalTasks }}</p>
                        </div>
                    </div>

                    <!-- Linked Percentage Card -->
                    <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <p class="text-amber-100 text-sm font-semibold uppercase tracking-wide">Linked Tasks</p>
                            <p class="text-4xl font-bold">{{ $linkedPercentage }}%</p>
                        </div>
                    </div>
                </div>

                <!-- Task Breakdown & Chart Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Task Breakdown Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-gray-900">Task Breakdown</h4>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-green-500 rounded-lg">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                        </svg>
                                    </div>
                                    <span class="font-semibold text-gray-900">Linked Tasks</span>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-green-700">{{ $linkedTasks }}</div>
                                    <div class="text-sm text-green-600">{{ $linkedPercentage }}%</div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-gradient-to-r from-yellow-50 to-amber-50 rounded-xl border border-yellow-200">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-yellow-500 rounded-lg">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                    </div>
                                    <span class="font-semibold text-gray-900">Unlinked Tasks</span>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-yellow-700">{{ $unlinkedTasks }}</div>
                                    <div class="text-sm text-yellow-600">{{ 100 - $linkedPercentage }}%</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="mt-6">
                            <div class="relative w-full h-4 bg-gray-200 rounded-full overflow-hidden">
                                <div class="absolute inset-0 flex">
                                    <div class="bg-gradient-to-r from-green-500 to-green-600 h-full transition-all duration-500" 
                                         style="width: {{ $linkedPercentage }}%"
                                         title="Linked: {{ $linkedTasks }} tasks">
                                    </div>
                                    <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 h-full transition-all duration-500" 
                                         style="width: {{ 100 - $linkedPercentage }}%"
                                         title="Unlinked: {{ $unlinkedTasks }} tasks">
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-2">
                                <span class="font-medium">Linked ({{ $linkedPercentage }}%)</span>
                                <span class="font-medium">Unlinked ({{ 100 - $linkedPercentage }}%)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Chart Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-gray-900">Task Distribution</h4>
                        </div>
                        <div class="h-64 flex items-center justify-center">
                            <x-chart wire:model="myChart" />
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-4">
                    <div class="text-gray-500">No data available for the selected week.</div>
                </div>
            @endif
        </div>

        <!-- Department Sections -->
        @if(isset($tasks['users']) && $tasks['users']->count() > 0)
            @php
                $departmentStats = $this->getDepartmentStats($tasks['users']);
            @endphp

            <div class="space-y-6">
                @foreach($departmentStats as $department)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <!-- Department Header -->
                        <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-purple-50 px-6 py-5 border-b border-gray-200">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-4">
                                    <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg shadow-blue-500/30">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-bold text-gray-900">{{ $department['name'] }}</h3>
                                        <p class="text-sm text-gray-600 mt-1">Department Performance Overview</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div class="bg-white rounded-xl px-4 py-3 border border-blue-200">
                                    <div class="flex items-center gap-2 text-blue-700 text-sm font-semibold mb-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                        </svg>
                                        Users
                                    </div>
                                    <div class="text-2xl font-bold text-gray-900">{{ $department['total_users'] }}</div>
                                </div>
                                
                                <div class="bg-white rounded-xl px-4 py-3 border border-green-200">
                                    <div class="flex items-center gap-2 text-green-700 text-sm font-semibold mb-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        Tasks
                                    </div>
                                    <div class="text-2xl font-bold text-gray-900">{{ $department['total_tasks'] }}</div>
                                </div>
                                
                                <div class="bg-white rounded-xl px-4 py-3 border border-emerald-200">
                                    <div class="flex items-center gap-2 text-emerald-700 text-sm font-semibold mb-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                        </svg>
                                        Linked
                                    </div>
                                    <div class="text-2xl font-bold text-gray-900">{{ $department['linked_tasks'] }} <span class="text-sm text-emerald-600">({{ $department['linked_percentage'] }}%)</span></div>
                                </div>
                                
                                <div class="bg-white rounded-xl px-4 py-3 border border-amber-200">
                                    <div class="flex items-center gap-2 text-amber-700 text-sm font-semibold mb-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                        Unlinked
                                    </div>
                                    <div class="text-2xl font-bold text-gray-900">{{ $department['unlinked_tasks'] }} <span class="text-sm text-amber-600">({{ 100 - $department['linked_percentage'] }}%)</span></div>
                                </div>
                            </div>
                        </div>

                        <!-- Users Table -->
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b border-gray-200">
                                            <th class="text-left py-3 px-4 font-semibold text-gray-700">User</th>
                                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Task Statistics</th>
                                            <th class="text-right py-3 px-4 font-semibold text-gray-700">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($department['users'] as $user)
                                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                                <td class="py-4 px-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-lg">
                                                            {{ substr($user->name, 0, 1) }}{{ substr($user->surname, 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <div class="font-semibold text-gray-900">{{ $user->name }} {{ $user->surname }}</div>
                                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-4 px-4">
                                                  @php
                                                    $actualTasksCount = $this->getUserTaskCount($user);
                                                    $linkedTasksCount = $this->getUserLinkedTasksCount($user);
                                                    $unlinkedTasksCount = $this->getUserUnlinkedTasksCount($user);
                                                    $linkedPercentage = $actualTasksCount > 0 ? round(($linkedTasksCount / $actualTasksCount) * 100, 1) : 0;
                                                  @endphp
                                                  
                                                  @if($actualTasksCount > 0)
                                                    <div class="flex items-center gap-3">
                                                        <div class="flex items-center gap-2 bg-blue-50 px-3 py-1.5 rounded-lg">
                                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                            </svg>
                                                            <span class="text-sm font-semibold text-blue-900">{{ $actualTasksCount }} total</span>
                                                        </div>
                                                        <div class="flex items-center gap-2 bg-green-50 px-3 py-1.5 rounded-lg">
                                                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                                            <span class="text-sm font-medium text-green-900">{{ $linkedTasksCount }} linked</span>
                                                        </div>
                                                        <div class="flex items-center gap-2 bg-amber-50 px-3 py-1.5 rounded-lg">
                                                            <div class="w-2 h-2 bg-amber-500 rounded-full"></div>
                                                            <span class="text-sm font-medium text-amber-900">{{ $unlinkedTasksCount }} unlinked</span>
                                                        </div>
                                                    </div>
                                                  @else
                                                    <div class="inline-flex items-center gap-2 bg-red-50 text-red-700 px-3 py-1.5 rounded-lg text-sm font-medium">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        No tasks found
                                                    </div>
                                                  @endif
                                                </td>
                                                <td class="py-4 px-4 text-right">
                                                    <button 
                                                        wire:click="openTaskModal('{{ $user->id }}')" 
                                                        class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-lg shadow-blue-500/30 font-medium text-sm"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                        View Tasks
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-16">
                <div class="bg-blue-50 rounded-2xl p-12 border border-blue-200 inline-block">
                    <svg class="w-20 h-20 mx-auto text-blue-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">No Performance Data Available</h3>
                    <p class="text-gray-600">Please select a week to view performance data by department.</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Task Details Modal -->
    @if($showModal && $selectedUser)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>

        <!-- Modal panel -->
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="sticky top-0 z-10 bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-5 border-b border-blue-400">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center text-white font-bold text-lg backdrop-blur-sm">
                                {{ substr($selectedUser->name, 0, 1) }}{{ substr($selectedUser->surname, 0, 1) }}
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-white">{{ $selectedUser->name }} {{ $selectedUser->surname }}</h3>
                                <p class="text-blue-100 text-sm">Weekly Task Overview</p>
                            </div>
                        </div>
                        <button 
                            wire:click="closeModal" 
                            class="p-2 bg-white/10 hover:bg-white/20 rounded-lg transition-colors backdrop-blur-sm"
                        >
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Content -->
                <div class="p-6">
                    @if($selectedUserTasks->count() > 0)
                        <div class="space-y-4">
                            @foreach($selectedUserTasks as $task)
                                <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-all">
                                    <div class="flex justify-between items-start mb-3">
                                        <h5 class="text-base font-bold text-gray-900 flex-1">{{ $task->title }}</h5>
                                        <div class="flex gap-2 flex-wrap">
                                            <span class="px-2 py-1 rounded-lg text-xs font-semibold {{ $task->status == 'completed' ? 'bg-green-100 text-green-800' : ($task->status == 'ongoing' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ ucfirst($task->status ?? 'pending') }}
                                            </span>
                                            <span class="px-2 py-1 rounded-lg text-xs font-semibold {{ ($task->priority ?? 'Low') == 'High' ? 'bg-red-100 text-red-800' : (($task->priority ?? 'Low') == 'Medium' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800') }}">
                                                {{ $task->priority ?? 'Low' }}
                                            </span>
                                            @if($task->individualworkplan_id)
                                                <span class="px-2 py-1 bg-emerald-100 text-emerald-800 rounded-lg text-xs font-semibold">
                                                    <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                    </svg>
                                                    Linked
                                                </span>
                                            @else
                                                <span class="px-2 py-1 bg-amber-100 text-amber-800 rounded-lg text-xs font-semibold">
                                                    Unlinked
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <p class="text-sm text-gray-600 mb-3 leading-relaxed">{{ $task->description ?? 'No description provided' }}</p>
                                    
                                    @if($task->duration ?? $task->uom ?? null)
                                    <div class="flex items-center gap-2 text-sm text-gray-700">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>{{ $task->duration ?? 'N/A' }} {{ $task->uom ?? '' }}</span>
                                    </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-16">
                            <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">No Tasks Found</h3>
                            <p class="text-gray-500">This user has no tasks for the selected week.</p>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="sticky bottom-0 bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end">
                    <button 
                        wire:click="closeModal" 
                        class="px-6 py-2.5 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-lg shadow-blue-500/30 font-semibold"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>