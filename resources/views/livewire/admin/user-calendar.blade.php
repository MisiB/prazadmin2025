<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50">
    <!-- Modern Header with Glassmorphism -->
    <div class="bg-white/80 backdrop-blur-sm shadow-sm border-b border-gray-200 mb-6  mt-2 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 mb-3 tracking-tight">My Weekly Tasks</h1>
                    <div class="flex items-center gap-3 flex-wrap">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-1.5 rounded-full text-sm font-semibold shadow-lg shadow-blue-500/30">
                        Week {{ $currentweek->week }}
                    </div>
                        <div class="flex items-center gap-2 text-gray-600 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="font-medium">{{ $currentweek->start_date }} - {{ $currentweek->end_date }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3">
                    <x-select wire:model.live="week_id" placeholder="Filter by Week" :options="$weeks" option-label="week" option-value="id" class="min-w-[200px]" />
                    <div class="flex gap-2">
                        <x-button 
                            icon="o-document-duplicate" 
                            label="Templates" 
                            class="btn-outline btn-sm" 
                            link="{{ route('admin.tasks.templates') }}"
                        />
                        <x-button 
                            icon="o-arrow-path" 
                            label="Recurring Tasks" 
                            class="btn-outline btn-sm" 
                            link="{{ route('admin.tasks.recurring') }}"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <!-- Status Alerts -->
    @php
        $allTasks = collect();
        foreach ($currentweek->calendardays as $day) {
            if ($day->relationLoaded('userTasks')) {
                $allTasks = $allTasks->merge($day->userTasks);
            } elseif ($day->relationLoaded('tasks')) {
                // Fallback: filter tasks by current user if userTasks not loaded
                $allTasks = $allTasks->merge($day->tasks->where('user_id', auth()->id()));
            }
        }
        $hasPendingApproval = $allTasks->where('approvalstatus', 'pending')->count() > 0;
        $hasRejected = $allTasks->where('approvalstatus', 'Rejected')->count() > 0;
        $allApproved = $allTasks->count() > 0 && $allTasks->where('approvalstatus', 'Approved')->count() === $allTasks->count();
        
        // Explicitly get approval records for this specific week only (already filtered by calendarweek_id via relationship)
        $currentWeekApprovalRecords = $currentweek->calenderworkusertasks->filter(function ($record) use ($currentweek) {
            return $record->calendarweek_id == $currentweek->id;
        });
        $hasCalenderworkusertasks = $currentWeekApprovalRecords->count() > 0;
        $calenderworkusertaskStatus = $hasCalenderworkusertasks ? $currentWeekApprovalRecords->first()->status : null;
        $rejectionComment = $hasCalenderworkusertasks && $currentWeekApprovalRecords->first()->comment ? $currentWeekApprovalRecords->first()->comment : null;
    @endphp
    
    @if($taskSummary['total'] > 0 && $currentWeekApprovalRecords->count() == 0)
    <div class="mb-6 animate-fade-in">
        <x-alert title="Awaiting Approval" description="Your supervisor has not approved your tasks for this week yet." icon="o-envelope" class="alert-error shadow-lg">
  <x-slot:actions>
                <x-button label="Send for Approval" wire:click="sendforapproval" wire:confirm="Are you sure you want to send for approval?" class="btn-sm" />
  </x-slot:actions>
</x-alert>
    </div>
    @elseif($hasPendingApproval && $calenderworkusertaskStatus == 'pending')
    <div class="mb-6 animate-fade-in">
        <x-alert title="Pending Approval" description="Your supervisor is currently reviewing your tasks for this week." icon="o-envelope" class="alert-warning shadow-lg">
<x-slot:actions>
  @if($currentWeekApprovalRecords->first() && $currentWeekApprovalRecords->first()->comment)
                    <x-button label="View Comment" wire:click="viewcommentmodal=true" class="btn-sm" />
  @endif
</x-slot:actions>
</x-alert>
    </div>
    @elseif($allApproved)
    <div class="mb-6 animate-fade-in">
        <x-alert title="All Tasks Approved" description="All your tasks for this week have been approved by your supervisor." icon="o-check-circle" class="alert-success shadow-lg">
</x-alert>
    </div>
    @elseif($hasRejected)
    <div class="mb-6 animate-fade-in">
        <x-alert title="Some Tasks Rejected" description="Some of your tasks have been rejected. Please review and update them." icon="o-exclamation-triangle" class="alert-error shadow-lg">
    <x-slot:actions>
        @if($hasPendingApproval)
            <x-button label="Resend for Approval" wire:click="sendforapproval" wire:confirm="Are you sure you want to resend for approval?" class="btn-sm" />
        @endif
    </x-slot:actions>
</x-alert>
    </div>
    @elseif($hasPendingApproval)
    <div class="mb-6 animate-fade-in">
        <x-alert title="New Tasks Added" description="You have added new tasks or updated rejected tasks. Send them for approval." icon="o-envelope" class="alert-info shadow-lg">
    <x-slot:actions>
        <x-button label="Send for Approval" wire:click="sendforapproval" wire:confirm="Are you sure you want to send for approval?" class="btn-sm" />
    </x-slot:actions>
</x-alert>
    </div>
    @endif

        <!-- Task Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Total Tasks Card -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
                <div class="space-y-1">
                    <p class="text-blue-100 text-sm font-semibold uppercase tracking-wide">Total Tasks</p>
                    <p class="text-4xl font-bold">{{ $taskSummary['total'] }}</p>
                    <div class="flex items-center gap-2 text-blue-100 text-sm mt-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="font-semibold">{{ number_format($taskSummary['total_hours'], 1) }} hours</span>
                    </div>
                </div>
            </div>

            <!-- Pending Tasks Card -->
            <div class="bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold">{{ $taskSummary['pending'] }}</div>
                    </div>
                </div>
                <div class="space-y-1">
                    <p class="text-yellow-100 text-sm font-semibold uppercase tracking-wide">Pending</p>
                    <div class="flex items-center gap-2 text-yellow-100 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ number_format($taskSummary['pending_hours'], 1) }} hrs</span>
                    </div>
                </div>
            </div>

            <!-- Ongoing Tasks Card -->
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold">{{ $taskSummary['ongoing'] }}</div>
                    </div>
                </div>
                <div class="space-y-1">
                    <p class="text-purple-100 text-sm font-semibold uppercase tracking-wide">Ongoing</p>
                    <div class="flex items-center gap-2 text-purple-100 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ number_format($taskSummary['ongoing_hours'], 1) }} hrs</span>
                    </div>
                </div>
            </div>

            <!-- Completed Tasks Card -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold">{{ $taskSummary['completed'] }}</div>
                    </div>
                </div>
                <div class="space-y-1">
                    <p class="text-green-100 text-sm font-semibold uppercase tracking-wide">Completed</p>
                    <div class="flex items-center gap-2 text-green-100 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ number_format($taskSummary['completed_hours'], 1) }} hrs</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Overview -->
        @if($taskSummary['total'] > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Weekly Progress</h3>
                    <p class="text-sm text-gray-500">Track your task completion rate</p>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold text-gray-900">{{ $taskSummary['total'] > 0 ? round(($taskSummary['completed'] / $taskSummary['total']) * 100) : 0 }}%</div>
                    <div class="text-sm text-gray-500">Complete</div>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="relative w-full h-4 bg-gray-200 rounded-full overflow-hidden">
                <div class="absolute inset-0 flex">
                    <!-- Completed -->
                    @if($taskSummary['completed'] > 0)
                    <div class="bg-gradient-to-r from-green-500 to-green-600 h-full transition-all duration-500" 
                         style="width: {{ $taskSummary['total'] > 0 ? ($taskSummary['completed'] / $taskSummary['total']) * 100 : 0 }}%"
                         title="Completed: {{ $taskSummary['completed'] }} tasks">
                    </div>
                    @endif
                    <!-- Ongoing -->
                    @if($taskSummary['ongoing'] > 0)
                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-full transition-all duration-500" 
                         style="width: {{ $taskSummary['total'] > 0 ? ($taskSummary['ongoing'] / $taskSummary['total']) * 100 : 0 }}%"
                         title="Ongoing: {{ $taskSummary['ongoing'] }} tasks">
                    </div>
                    @endif
                    <!-- Pending -->
                    @if($taskSummary['pending'] > 0)
                    <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 h-full transition-all duration-500" 
                         style="width: {{ $taskSummary['total'] > 0 ? ($taskSummary['pending'] / $taskSummary['total']) * 100 : 0 }}%"
                         title="Pending: {{ $taskSummary['pending'] }} tasks">
                    </div>
                    @endif
                </div>
            </div>

           
            <div class="flex items-center justify-center gap-6 mt-4 flex-wrap">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-gradient-to-r from-green-500 to-green-600"></div>
                    <span class="text-sm text-gray-600">Completed ({{ $taskSummary['completed'] }})</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-gradient-to-r from-purple-500 to-purple-600"></div>
                    <span class="text-sm text-gray-600">Ongoing ({{ $taskSummary['ongoing'] }})</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-gradient-to-r from-yellow-400 to-yellow-500"></div>
                    <span class="text-sm text-gray-600">Pending ({{ $taskSummary['pending'] }})</span>
                </div>
            </div>
        </div>
        @endif

        <!-- Days Grid Layout -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mb-6">
            @foreach ($currentweek->calendardays as $day)
                @php
                    $dayTasks = $day->userTasks ?? [];
                    $taskCount = count($dayTasks);
                    $completedCount = $dayTasks->where('status', 'completed')->count();
                    $ongoingCount = $dayTasks->where('status', 'ongoing')->count();
                    $pendingCount = $dayTasks->where('status', 'pending')->count();
                @endphp
                
                <div wire:key="day-{{ $day->id }}" class="bg-white rounded-xl shadow-sm border-2 border-gray-200 hover:border-blue-400 hover:shadow-lg transition-all duration-300 flex flex-col">
                    <!-- Day Card Header -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 px-4 py-4 border-b border-gray-200 rounded-t-xl">
                        <div class="flex items-center justify-between">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-md">
                                {{ Carbon\Carbon::parse($day->maindate)->format('d') }}
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-bold text-gray-700 uppercase tracking-wide">
                                    {{ Carbon\Carbon::parse($day->maindate)->format('D') }}
                                </div>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    {{ Carbon\Carbon::parse($day->maindate)->format('M Y') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Day Card Body -->
                    <div class="p-4 flex-1 flex flex-col">
                        <!-- Task Count Section -->
                        <div class="mb-4">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="p-1.5 bg-gray-100 rounded-lg">
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-lg font-bold text-gray-900">{{ $taskCount }}</span>
                                    <span class="text-sm text-gray-500 ml-1">task{{ $taskCount != 1 ? 's' : '' }}</span>
                                </div>
                            </div>

                            <!-- Task Status Summary -->
                            @if($taskCount > 0)
                            <div class="space-y-1.5 mt-3">
                                @if($completedCount > 0)
                                <div class="flex items-center gap-2 text-xs">
                                    <div class="w-2.5 h-2.5 rounded-full bg-green-500 shadow-sm"></div>
                                    <span class="text-gray-600 font-medium">{{ $completedCount }} completed</span>
                                </div>
                                @endif
                                @if($ongoingCount > 0)
                                <div class="flex items-center gap-2 text-xs">
                                    <div class="w-2.5 h-2.5 rounded-full bg-purple-500 shadow-sm"></div>
                                    <span class="text-gray-600 font-medium">{{ $ongoingCount }} ongoing</span>
                                </div>
                                @endif
                                @if($pendingCount > 0)
                                <div class="flex items-center gap-2 text-xs">
                                    <div class="w-2.5 h-2.5 rounded-full bg-yellow-500 shadow-sm"></div>
                                    <span class="text-gray-600 font-medium">{{ $pendingCount }} pending</span>
                                </div>
                                @endif
                            </div>
                            @else
                            <div class="text-center py-3 mt-2">
                                <p class="text-sm text-gray-400 font-medium">No tasks scheduled</p>
                            </div>
                            @endif
                        </div>

                        <!-- Action Buttons Section -->
                        <div class="mt-auto pt-4 border-t border-gray-100 space-y-2">
                            @if($taskCount > 0)
                            <x-button 
                                icon="o-eye" 
                                label="View Tasks" 
                                class="btn-sm btn-primary w-full shadow-sm hover:shadow-md transition-shadow" 
                                wire:click="openDayModal({{ $day->id }})"
                            />
                            @endif
                            <x-button 
                                icon="o-plus" 
                                label="Add Task" 
                                class="btn-sm {{ $taskCount > 0 ? 'btn-outline btn-primary' : 'btn-primary' }} w-full shadow-sm hover:shadow-md transition-shadow" 
                                wire:click="openModal({{ $day->id }})"
                            />
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Day Tasks Modal -->
        <x-modal wire:model="dayTasksModal" :title="$selectedDayTitle ?? 'Tasks'" box-class="max-w-4xl">
            <!-- Custom Header with Bulk Actions -->
            <div class="mb-4 pb-4 border-b border-gray-200">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        @if(count($selectedTaskIds) > 0)
                            <x-badge value="{{ count($selectedTaskIds) }} selected" class="badge-sm badge-info" />
                        @endif
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        @if(count($selectedTaskIds) > 0)
                            @php
                                // Check if any selected tasks are eligible for rollover
                                $hasEligibleForRollover = false;
                                if ($selectedDayTasks) {
                                    foreach ($selectedDayTasks as $group) {
                                        foreach ($group as $task) {
                                            if (in_array($task->id, $selectedTaskIds)) {
                                                // Check if task is eligible: pending OR (ongoing with worked hours)
                                                if ($task->status === 'pending') {
                                                    $hasEligibleForRollover = true;
                                                    break 2;
                                                } elseif ($task->status === 'ongoing') {
                                                    $activeInstance = $task->taskinstances?->where('status', 'ongoing')->sortByDesc('date')->first();
                                                    if ($activeInstance && $activeInstance->worked_hours > 0) {
                                                        $hasEligibleForRollover = true;
                                                        break 2;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            @endphp
                            @if($hasEligibleForRollover)
                                <x-button 
                                    icon="o-arrow-path" 
                                    label="Bulk Rollover" 
                                    class="btn-xs btn-outline btn-warning" 
                                    wire:click="bulkRollover"
                                    wire:confirm="Roll over eligible selected task(s) to tomorrow?"
                                />
                            @endif
                        @endif
                        @if($selectedDayTasks)
                            @php
                                $selectableCount = 0;
                                foreach($selectedDayTasks as $group) {
                                    foreach($group as $task) {
                                        if ($task->status != 'completed' && $task->approvalstatus != 'Rejected') {
                                            $selectableCount++;
                                        }
                                    }
                                }
                            @endphp
                            @if($selectableCount > 0)
                                @if(count($selectedTaskIds) === $selectableCount)
                                    <x-button 
                                        icon="o-x-mark" 
                                        label="Deselect All" 
                                        class="btn-xs btn-outline btn-ghost" 
                                        wire:click="deselectAllTasks"
                                    />
                                @else
                                    <x-button 
                                        icon="o-check" 
                                        label="Select All" 
                                        class="btn-xs btn-outline btn-ghost" 
                                        wire:click="selectAllTasks"
                                    />
                                @endif
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <!-- Scrollable Task List Container -->
            <div class="max-h-[60vh] overflow-y-auto pr-2 -mr-2">
                @if($selectedDayTasks && is_array($selectedDayTasks))
                    @php
                        $hasTasks = false;
                        foreach($selectedDayTasks as $group) {
                            if($group->count() > 0) {
                                $hasTasks = true;
                                break;
                            }
                        }
                    @endphp

                    @if($hasTasks)
                        <!-- Rejected Tasks Section -->
                        @if($selectedDayTasks['rejected']->count() > 0)
                        <div class="mb-6">
                            <div class="flex items-center gap-2 mb-3 sticky top-0 bg-white z-10 py-2 border-b-2 border-red-300">
                                <div class="w-1 h-6 bg-red-500 rounded-full"></div>
                                <h3 class="text-sm font-bold text-red-700 uppercase tracking-wide">Rejected Tasks ({{ $selectedDayTasks['rejected']->count() }})</h3>
                                <x-badge value="Needs Attention" class="badge-xs badge-error ml-auto" />
                            </div>
                            <div class="space-y-3">
                                @foreach($selectedDayTasks['rejected'] as $task)
                                    @php
                                        $activeInstance = $task->taskinstances?->where('status', 'ongoing')->sortByDesc('date')->first();
                                    @endphp
                                    <div wire:key="task-rejected-{{ $task->id }}">
                                        @include('livewire.admin.partials.task-card', [
                                            'task' => $task, 
                                            'activeInstance' => $activeInstance,
                                            'selectedTaskIds' => $selectedTaskIds
                                        ])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Pending Approval Section -->
                        @if($selectedDayTasks['pending_approval']->count() > 0)
                        <div class="mb-6">
                            <div class="flex items-center gap-2 mb-3 sticky top-0 bg-white z-10 py-2 border-b-2 border-yellow-300">
                                <div class="w-1 h-6 bg-yellow-500 rounded-full"></div>
                                <h3 class="text-sm font-bold text-yellow-700 uppercase tracking-wide">Awaiting Approval ({{ $selectedDayTasks['pending_approval']->count() }})</h3>
                                <x-badge value="Pending Review" class="badge-xs badge-warning ml-auto" />
                            </div>
                            <div class="space-y-3">
                                @foreach($selectedDayTasks['pending_approval'] as $task)
                                    @php
                                        $activeInstance = $task->taskinstances?->where('status', 'ongoing')->sortByDesc('date')->first();
                                    @endphp
                                    <div wire:key="task-pending-approval-{{ $task->id }}">
                                        @include('livewire.admin.partials.task-card', [
                                            'task' => $task, 
                                            'activeInstance' => $activeInstance,
                                            'selectedTaskIds' => $selectedTaskIds
                                        ])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Pending Tasks Section -->
                        @if($selectedDayTasks['pending']->count() > 0)
                        <div class="mb-6">
                            <div class="flex items-center gap-2 mb-3 sticky top-0 bg-white z-10 py-2 border-b-2 border-blue-300">
                                <div class="w-1 h-6 bg-blue-500 rounded-full"></div>
                                <h3 class="text-sm font-bold text-blue-700 uppercase tracking-wide">Not Started ({{ $selectedDayTasks['pending']->count() }})</h3>
                                <x-badge value="Ready to Start" class="badge-xs badge-info ml-auto" />
                            </div>
                            <div class="space-y-3">
                                @foreach($selectedDayTasks['pending'] as $task)
                                    @php
                                        $activeInstance = $task->taskinstances?->where('status', 'ongoing')->sortByDesc('date')->first();
                                    @endphp
                                    <div wire:key="task-pending-{{ $task->id }}">
                                        @include('livewire.admin.partials.task-card', [
                                            'task' => $task, 
                                            'activeInstance' => $activeInstance,
                                            'selectedTaskIds' => $selectedTaskIds
                                        ])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Ongoing Tasks Section -->
                        @if($selectedDayTasks['ongoing']->count() > 0)
                        <div class="mb-6">
                            <div class="flex items-center gap-2 mb-3 sticky top-0 bg-white z-10 py-2 border-b-2 border-purple-300">
                                <div class="w-1 h-6 bg-purple-500 rounded-full"></div>
                                <h3 class="text-sm font-bold text-purple-700 uppercase tracking-wide">In Progress ({{ $selectedDayTasks['ongoing']->count() }})</h3>
                                <x-badge value="Active" class="badge-xs badge-primary ml-auto" />
                            </div>
                            <div class="space-y-3">
                                @foreach($selectedDayTasks['ongoing'] as $task)
                                    @php
                                        $activeInstance = $task->taskinstances?->where('status', 'ongoing')->sortByDesc('date')->first();
                                    @endphp
                                    <div wire:key="task-ongoing-{{ $task->id }}">
                                        @include('livewire.admin.partials.task-card', [
                                            'task' => $task, 
                                            'activeInstance' => $activeInstance,
                                            'selectedTaskIds' => $selectedTaskIds
                                        ])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Completed Tasks Section -->
                        @if($selectedDayTasks['completed']->count() > 0)
                        <div class="mb-6">
                            <div class="flex items-center gap-2 mb-3 sticky top-0 bg-white z-10 py-2 border-b-2 border-green-300">
                                <div class="w-1 h-6 bg-green-500 rounded-full"></div>
                                <h3 class="text-sm font-bold text-green-700 uppercase tracking-wide">Completed ({{ $selectedDayTasks['completed']->count() }})</h3>
                                <x-badge value="Done" class="badge-xs badge-success ml-auto" />
                            </div>
                            <div class="space-y-3">
                                @foreach($selectedDayTasks['completed'] as $task)
                                    @php
                                        $activeInstance = $task->taskinstances?->where('status', 'ongoing')->sortByDesc('date')->first();
                                    @endphp
                                    <div wire:key="task-completed-{{ $task->id }}">
                                        @include('livewire.admin.partials.task-card', [
                                            'task' => $task, 
                                            'activeInstance' => $activeInstance,
                                            'selectedTaskIds' => $selectedTaskIds
                                        ])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <p class="text-gray-500 font-medium">No tasks scheduled for this day</p>
                        </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <p class="text-gray-500 font-medium">No tasks scheduled for this day</p>
                    </div>
                @endif
            </div>

            <!-- Fixed Footer with Actions -->
            <x-slot:actions>
                <x-button label="Close" wire:click="closeDayModal()" class="btn-outline" />
                <x-button 
                    icon="o-plus" 
                    label="Add Task" 
                    class="btn-primary" 
                    wire:click="openModal({{ $selectedDayId }})"
                />
            </x-slot:actions>
        </x-modal>

    </div>
           

        
        <!-- Add/Edit Task Modal -->
        <x-modal wire:model="modal" title="{{ $id ? 'Edit Task' : 'Add New Task' }}" box-class="max-w-2xl">
          
            
          <x-form wire:submit="save">
                <div class="space-y-5">
                    @if(!$id && count($templates) > 0)
                    <div class="bg-purple-50 rounded-xl p-4 border border-purple-200">
                        <x-select 
                            wire:model.live="selectedTemplateId" 
                            label="Use Template (Optional)" 
                            placeholder="Select a template to pre-fill form..."
                            :options="$templates" 
                            option-label="title" 
                            option-value="id"
                            icon="o-document-duplicate"
                            hint="Select a saved template to quickly create a similar task"
                        />
                    </div>
                    @endif

                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                        <x-input 
                            wire:model="title" 
                            label="Task Title" 
                            placeholder="Enter a descriptive title for your task"
                            hint="Be specific and clear"
                        />
            </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-select 
                            wire:model="priority" 
                            label="Priority Level" 
                            placeholder="Select Priority" 
                            :options="[['id' => 'High', 'name' => 'High'], ['id' => 'Medium', 'name' => 'Medium'], ['id' => 'Low', 'name' => 'Low']]" 
                            option-label="name" 
                            option-value="id"
                            icon="o-flag"
                        />
                        
            <div class="grid grid-cols-2 gap-2">
                            <x-input 
                                wire:model="duration" 
                                type="number" 
                                label="Duration" 
                                placeholder="0"
                                min="0"
                            />
                            <x-select 
                                wire:model="uom" 
                                label="Unit" 
                                placeholder="UOM" 
                                :options="[['id' => 'Hours', 'name' => 'Hours']]" 
                                option-label="name" 
                                option-value="id" 
                            />
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                        <x-textarea 
                            wire:model="description" 
                            label="Description" 
                            placeholder="Provide details about this task..."
                            rows="4"
                            hint="Include important details and context"
                        />
            </div>

                    <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                        <x-checkbox 
                            label="Link to workplan activity" 
                            wire:model.live="link"
                            hint="Connect this task to your strategic workplan"
                        />   
            </div>

            @if($link)
                    <div class="animate-fade-in">
                        <x-select 
                            wire:model="individualworkplan_id" 
                            label="Individual Workplan Activity" 
                            placeholder="Select workplan activity" 
                            :options="$workplanlist" 
                            option-label="description" 
                            option-value="id"
                            icon="o-chart-bar"
                        />
            </div>
            @endif

                    @if(!$id)
                    <div class="bg-amber-50 rounded-xl p-4 border border-amber-200">
                        <x-checkbox 
                            wire:model="saveAsTemplate" 
                            label="Save as Template" 
                            hint="Save this task as a template for future use"
                        />
                    </div>
                    @endif
                </div>

            <x-slot name="actions">
                    <x-button 
                        label="Cancel" 
                        wire:click="$wire.closeModal()" 
                        class="btn-outline" 
                    />
                    <x-button 
                        label="{{ $id ? 'Update Task' : 'Create Task' }}" 
                        type="submit" 
                        class="btn-primary shadow-lg shadow-blue-500/30" 
                        spinner="save"
                        icon="o-check"
                    />
            </x-slot>
          </x-form>
        </x-modal>

        <!-- Change Status Modal -->
        <x-modal wire:model="markmodal" title="Update Task Status" separator box-class="max-w-md">
            
            @php
                $taskForStatus = $taskid ? \App\Models\Task::with('taskinstances')->find($taskid) : null;
                $totalWorkedHours = $taskForStatus ? $taskForStatus->taskinstances->sum('worked_hours') : 0;
                $hasLoggedHours = $totalWorkedHours > 0;
            @endphp
            
            <div class="space-y-3">
                <p class="text-sm text-gray-600 mb-4">Choose the new status for this task:</p>
                
                @if(!$hasLoggedHours)
                <div class="alert alert-warning text-sm mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>You must log hours before marking this task as completed.</span>
                </div>
                @endif
                
                <button 
                    type="button"
                    wire:click="openEvidenceModal({{ $taskid }})"
                    @if(!$hasLoggedHours) disabled @endif
                    class="w-full flex items-center gap-3 p-4 bg-gradient-to-r {{ $hasLoggedHours ? 'from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 border-green-300' : 'from-gray-50 to-gray-100 border-gray-300 opacity-50 cursor-not-allowed' }} border-2 rounded-xl transition-all duration-200 group"
                >
                    <div class="p-2 {{ $hasLoggedHours ? 'bg-green-500' : 'bg-gray-400' }} rounded-lg group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="text-left">
                        <div class="font-bold {{ $hasLoggedHours ? 'text-green-900' : 'text-gray-500' }}">Completed</div>
                        <div class="text-xs {{ $hasLoggedHours ? 'text-green-700' : 'text-gray-500' }}">
                            @if($hasLoggedHours)
                                Task is finished (with optional evidence)
                            @else
                                Log hours first to mark as completed
                            @endif
                        </div>
                    </div>
                </button>

                <button 
                    type="button"
                    wire:click="marktaskasongoing({{ $taskid }})" 
                    wire:confirm="Are you sure you want to mark this task as ongoing?"
                    class="w-full flex items-center gap-3 p-4 bg-gradient-to-r from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 border-2 border-blue-300 rounded-xl transition-all duration-200 group"
                >
                    <div class="p-2 bg-blue-500 rounded-lg group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="text-left">
                        <div class="font-bold text-blue-900">Ongoing</div>
                        <div class="text-xs text-blue-700">Currently working on it</div>
                    </div>
                </button>

                <button 
                    type="button"
                    wire:click="marktaskaspending({{ $taskid }})" 
                    wire:confirm="Are you sure you want to mark this task as pending?"
                    class="w-full flex items-center gap-3 p-4 bg-gradient-to-r from-yellow-50 to-yellow-100 hover:from-yellow-100 hover:to-yellow-200 border-2 border-yellow-300 rounded-xl transition-all duration-200 group"
                >
                    <div class="p-2 bg-yellow-500 rounded-lg group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="text-left">
                        <div class="font-bold text-yellow-900">Pending</div>
                        <div class="text-xs text-yellow-700">Not started yet</div>
                    </div>
                </button>
          </div>
        </x-modal>

        <!-- Evidence Upload Modal -->
        <x-modal wire:model="showEvidenceModal" title="Complete Task" box-class="max-w-md">
            <div class="space-y-4">
                <div class="bg-green-50 rounded-xl p-4 border border-green-200">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2 bg-green-500 rounded-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <span class="font-semibold text-green-900">Mark as Completed</span>
                    </div>
                    <p class="text-sm text-green-700">You can optionally upload evidence to support task completion.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Evidence Document (Optional)</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 hover:border-blue-400 transition-colors">
                        <input 
                            type="file" 
                            wire:model="evidenceFile"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.rar"
                        />
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Supported: PDF, Word, Excel, PowerPoint, Images, ZIP (Max 10MB)</p>
                    
                    @error('evidenceFile') 
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                    
                    <div wire:loading wire:target="evidenceFile" class="mt-2 text-sm text-blue-600">
                        <svg class="animate-spin inline w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Uploading...
                    </div>

                    @if($evidenceFile)
                    <div class="mt-2 p-2 bg-gray-100 rounded-lg flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm text-gray-700 truncate">{{ $evidenceFile->getClientOriginalName() }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.showEvidenceModal = false" />
                <x-button 
                    label="Complete Without Evidence" 
                    wire:click="completeWithoutEvidence"
                    class="btn-outline btn-success"
                    wire:loading.attr="disabled"
                />
                <x-button 
                    label="Complete with Evidence" 
                    wire:click="marktaskascompleted({{ $completingTaskId }})"
                    class="btn-success"
                    wire:loading.attr="disabled"
                    icon="o-check"
                    :disabled="!$evidenceFile"
                />
            </x-slot:actions>
        </x-modal>

        <!-- View Comment Modal -->
        <x-modal wire:model="viewcommentmodal" box-class="max-w-lg">
            <x-slot:title>
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-amber-100 rounded-lg">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900">Supervisor's Comment</span>
                </div>
            </x-slot:title>
            
            <div class="bg-gradient-to-br from-amber-50 to-yellow-50 rounded-xl p-6 border border-amber-200">
            @if($currentWeekApprovalRecords->count() > 0 && $currentWeekApprovalRecords->first()->comment)
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                        </svg>
                        <p class="text-gray-800 leading-relaxed">{{ $currentWeekApprovalRecords->first()->comment }}</p>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">No comments available</p>
            @endif
          </div>
        </x-modal>

        <!-- Log Hours Modal -->
        <x-modal wire:model="logHoursModal" title="Log Worked Hours" box-class="max-w-md">
            <x-form wire:submit="logHours">
                <div class="space-y-4">
                    <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 bg-blue-500 rounded-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span class="font-semibold text-blue-900">Track Your Progress</span>
                        </div>
                        <p class="text-sm text-blue-700">Enter the number of hours you have worked on this task today.</p>
                    </div>

                    <x-input 
                        wire:model="workedHours" 
                        type="number" 
                        label="Worked Hours" 
                        placeholder="0"
                        min="0"
                        step="0.5"
                        hint="You can use decimals (e.g., 2.5 for 2 hours 30 minutes)"
                    />

                    <div class="border-t border-gray-200 pt-4">
                        <div class="bg-amber-50 rounded-xl p-4 border border-amber-200 mb-3">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="p-2 bg-amber-500 rounded-lg">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </div>
                                <span class="font-semibold text-amber-900 text-sm">Need More Time?</span>
                            </div>
                            <p class="text-xs text-amber-700">If you need more hours than originally planned, add additional hours below.</p>
                        </div>

                        <x-input 
                            wire:model="additionalHours" 
                            type="number" 
                            label="Additional Hours (Optional)" 
                            placeholder="0"
                            min="0"
                            step="0.5"
                            hint="Extra hours to add to your planned hours"
                        />
                    </div>
                </div>

                <x-slot:actions>
                    <x-button 
                        label="Cancel" 
                        @click="$wire.closeLogHoursModal()"
                    />
                    <x-button 
                        label="Save Hours" 
                        type="submit" 
                        class="btn-primary" 
                        spinner="logHours"
                        icon="o-check"
                    />
                </x-slot:actions>
            </x-form>
        </x-modal>
 
    
</div>