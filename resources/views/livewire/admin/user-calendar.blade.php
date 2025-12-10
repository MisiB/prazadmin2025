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
        <div class="grid gap-6">
      @foreach ($currentweek->calendardays as $day)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-all duration-300">
                <!-- Day Header -->
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center text-white font-bold shadow-lg shadow-blue-500/30">
                                    {{ Carbon\Carbon::parse($day->maindate)->format('d') }}
                                </div>
              </div>
              <div>
                                <h3 class="text-xl font-bold text-gray-900">{{ Carbon\Carbon::parse($day->maindate)->format('l') }}</h3>
                                <p class="text-sm text-gray-500">{{ Carbon\Carbon::parse($day->maindate)->format('F d, Y') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2 text-sm text-gray-600 bg-white px-3 py-1.5 rounded-full border border-gray-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <span class="font-semibold">{{ count($day->userTasks ?? []) }}</span>
                                <span>tasks</span>
                            </div>
                            <x-button icon="o-plus" label="Add Task" class="btn-primary btn-sm shadow-lg shadow-blue-500/30" wire:click="openModal({{ $day->id }})" />
                        </div>
                    </div>
                </div>

                <!-- Tasks List -->
                <div class="p-6">
              @forelse ($day->userTasks ?? [] as $task)
                    <div class="group relative mb-4 last:mb-0 bg-gradient-to-br from-gray-50 to-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all duration-300 overflow-hidden">
                        <!-- Priority Bar -->
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ $task->priority == 'High' ? 'bg-gradient-to-b from-red-500 to-red-600' : ($task->priority == 'Medium' ? 'bg-gradient-to-b from-yellow-500 to-yellow-600' : 'bg-gradient-to-b from-green-500 to-green-600') }}"></div>
                        
                        <div class="p-5 pl-6">
                            <!-- Task Header -->
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-lg font-bold text-gray-900 mb-1 truncate">{{ $task->title }}</h4>
                                    <p class="text-sm text-gray-600 line-clamp-2">{{ $task->description }}</p>
                                </div>
                  </div>

                            <!-- Task Meta -->
                            <div class="flex items-center gap-4 mb-4 flex-wrap">
                                <div class="flex items-center gap-2 text-sm text-gray-700">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="font-medium">{{ $task->duration }} {{ $task->uom }}</span>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <x-badge 
                                        value="{{ ucfirst($task->status) }}" 
                                        class="badge-sm {{ $task->status == 'completed' ? 'badge-success' : ($task->status == 'ongoing' ? 'badge-info' : 'badge-warning') }}" 
                                    />
                                    <x-badge 
                                        value="{{ $task->priority }}" 
                                        class="badge-sm {{ $task->priority == 'High' ? 'badge-error' : ($task->priority == 'Medium' ? 'badge-warning' : 'badge-success') }}" 
                                    />
                                    <x-badge 
                                        value="{{ $task->approvalstatus == 'Approved' ? 'Approved' : ($task->approvalstatus == 'Rejected' ? 'Rejected' : 'Pending Approval') }}" 
                                        class="badge-sm {{ $task->approvalstatus == 'Approved' ? 'badge-success' : ($task->approvalstatus == 'Rejected' ? 'badge-error' : 'badge-warning') }}" 
                                    />
                                </div>
              </div>

                            <!-- Rejection Comment -->
                            @if($task->approvalstatus == 'Rejected' && $rejectionComment)
                            <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-red-900 mb-1">Supervisor's Comment:</p>
                                        <p class="text-sm text-red-800">{{ $rejectionComment }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Task Actions -->
               @if($task->status != 'completed')
                            <div class="flex items-center gap-2 pt-3 border-t border-gray-200">
                                <x-button 
                                    icon="o-check-circle" 
                                    label="Status" 
                                    class="btn-outline btn-success btn-sm" 
                                    wire:click="openmarkmodal({{ $task->id }})" 
                                />
                                <x-button 
                                    icon="o-pencil" 
                                    label="Edit" 
                                    class="btn-outline btn-info btn-sm" 
                                    wire:click="edit({{ $task->id }})" 
                                />
                     @if($task->approvalstatus == 'pending' || $task->approvalstatus == 'Rejected')
                                <x-button 
                                    icon="o-trash" 
                                    label="Delete" 
                                    class="btn-outline btn-error btn-sm" 
                                    wire:click="delete({{ $task->id }})" 
                                    wire:confirm="Are you sure you want to delete this task?" 
                                />
                   @endif
                          </div>
                            @else
                            <div class="flex items-center gap-2 pt-3 border-t border-gray-200">
                                <div class="flex items-center gap-2 text-sm text-green-700 bg-green-50 px-3 py-1.5 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="font-semibold">Completed</span>
                                </div>
                            </div>
               @endif
               </div>
             </div>
              @empty
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">No tasks for this day</h3>
                        <p class="text-sm text-gray-500 mb-4">Get started by adding your first task</p>
                        <x-button icon="o-plus" label="Add Task" class="btn-primary btn-sm" wire:click="openModal({{ $day->id }})" />
                    </div>
              @endforelse
              </div>
            </div>
      @endforeach
        </div>
    </div>
           

        
        <!-- Add/Edit Task Modal -->
        <x-modal wire:model="modal" title="{{ $id ? 'Edit Task' : 'Add New Task' }}" box-class="max-w-2xl">
          
            
          <x-form wire:submit="save">
                <div class="space-y-5">
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
            
            
            <div class="space-y-3">
                <p class="text-sm text-gray-600 mb-4">Choose the new status for this task:</p>
                
                <button 
                    type="button"
                    wire:click="marktaskascompleted({{ $taskid }})" 
                    wire:confirm="Are you sure you want to mark this task as completed?"
                    class="w-full flex items-center gap-3 p-4 bg-gradient-to-r from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 border-2 border-green-300 rounded-xl transition-all duration-200 group"
                >
                    <div class="p-2 bg-green-500 rounded-lg group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="text-left">
                        <div class="font-bold text-green-900">Completed</div>
                        <div class="text-xs text-green-700">Task is finished</div>
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
 
    
</div>