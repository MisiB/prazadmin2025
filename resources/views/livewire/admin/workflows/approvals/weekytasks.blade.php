<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box overflow-x-auto whitespace-nowrap"
    link-item-class="text-base" />

    <x-card title="{{ $tasks['calendarweek'] ? $tasks['calendarweek']->week : 'No Calendar Week Found' }}" subtitle="{{ $tasks['calendarweek'] ? $tasks['calendarweek']->start_date . ' - ' . $tasks['calendarweek']->end_date : 'Please select a valid week' }}" class="mt-2 border-2 border-gray-200" separator>
        <x-slot:menu>
            <x-select wire:model="week" :options="$weeks" option-label="week" option-value="id" placeholder="Filter by week" />
        </x-slot:menu>
        
        <!-- Task Summary -->
        <div class="mb-4 p-4 bg-base-200 rounded-lg">
            <h3 class="text-lg font-semibold mb-2">Task Summary by Department</h3>
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div class="stat">
                    <div class="stat-title">Total Departments</div>
                    <div class="stat-value text-primary">{{ $this->getDepartmentStats($tasks['users'])->count() }}</div>
                </div>
                <div class="stat">
                    <div class="stat-title">Total Users</div>
                    <div class="stat-value text-secondary">{{ $tasks['users']->count() }}</div>
                </div>
                <div class="stat">
                    <div class="stat-title">Actual Tasks</div>
                    <div class="stat-value text-accent">{{ $this->getTotalTasksCount($tasks['users']) }}</div>
                </div>
                <div class="stat">
                    <div class="stat-title">Linked Tasks</div>
                    <div class="stat-value text-success">{{ $this->getLinkedTasksCount($tasks['users']) }}</div>
                    <div class="stat-desc">{{ $this->getLinkedTasksPercentage($tasks['users']) }}% linked</div>
                </div>
                <div class="stat">
                    <div class="stat-title">Unlinked Tasks</div>
                    <div class="stat-value text-warning">{{ $this->getUnlinkedTasksCount($tasks['users']) }}</div>
                    <div class="stat-desc">{{ 100 - $this->getLinkedTasksPercentage($tasks['users']) }}% unlinked</div>
                </div>
                <div class="stat">
                    <div class="stat-title">Week: {{ $tasks['calendarweek'] ? $tasks['calendarweek']->week : 'N/A' }}</div>
                    <div class="stat-desc">{{ $tasks['calendarweek'] ? $tasks['calendarweek']->start_date . ' - ' . $tasks['calendarweek']->end_date : 'No week selected' }}</div>
                </div>
            </div>
        </div>
        @if($tasks['calendarweek'])
            @php
                $departmentStats = $this->getDepartmentStats($tasks['users']);
            @endphp

            <div class="space-y-6">
                @foreach($departmentStats as $department)
                <div class="card bg-base-100 shadow-sm border">
                    <div class="card-body p-6">
                        <!-- Department Header -->
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 class="text-xl font-bold text-primary">{{ $department['name'] }}</h3>
                                <div class="flex items-center gap-4 mt-2">
                                    <span class="badge badge-primary">{{ $department['total_users'] }} users</span>
                                    <span class="badge badge-secondary">{{ $department['total_tasks'] }} tasks</span>
                                    <span class="badge badge-success">{{ $department['linked_tasks'] }} ({{ $department['linked_percentage'] }}%) linked</span>
                                    <span class="badge badge-warning">{{ $department['unlinked_tasks'] }} ({{ 100 - $department['linked_percentage'] }}%) unlinked</span>
                                </div>
                            </div>
                        </div>

                        <!-- Users Table -->
                        <div class="overflow-x-auto">
                            <table class="table table-zebra table-compact">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Tasks (Count & Details)</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($department['users'] as $user)
                                        <tr>
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    <div class="avatar placeholder">
                                                        <div class="bg-neutral text-neutral-content rounded-full w-8">
                                                            <span class="text-xs">{{ substr($user->name, 0, 1) }}{{ substr($user->surname, 0, 1) }}</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="font-medium">{{ $user->name }} {{ $user->surname }}</div>
                                                        <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                              @php
                                                $actualTasksCount = $this->getUserTaskCount($user);
                                                $linkedTasksCount = $this->getUserLinkedTasksCount($user);
                                                $unlinkedTasksCount = $this->getUserUnlinkedTasksCount($user);
                                                $linkedPercentage = $actualTasksCount > 0 ? round(($linkedTasksCount / $actualTasksCount) * 100, 1) : 0;
                                              @endphp
                                              
                                              @if($actualTasksCount > 0)
                                                <div class="space-y-2">
                                                  <div class="flex items-center gap-2">
                                                    <span class="badge badge-primary">{{ $actualTasksCount }} task(s)</span>
                                                  </div>
                                                  <div class="text-xs text-gray-600">
                                                    <div class="flex items-center gap-2">
                                                      <span class="badge badge-success badge-sm">{{ $linkedTasksCount }} ({{ $linkedPercentage }}%) linked</span>
                                                      <span class="badge badge-warning badge-sm">{{ $unlinkedTasksCount }} ({{ 100 - $linkedPercentage }}%) unlinked</span>
                                                    </div>
                                                  </div>
                                                </div>
                                              @else
                                                <x-alert class="alert-error">No tasks found</x-alert>
                                              @endif
                                            </td>
                                            <td>
                                                <button wire:click="openTaskModal('{{ $user->id }}')" class="btn btn-sm btn-outline btn-primary">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
            <div class="text-center py-8">
                <div class="alert alert-warning">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <div>
                        <h3 class="font-bold">No Calendar Week Found</h3>
                        <div class="text-xs">Please select a valid week from the dropdown above or ensure the calendar data is properly set up.</div>
                    </div>
                </div>
            </div>
        @endif
        
    </x-card>

    <!-- Task Details Modal -->
    @if($showModal && $selectedUser)
    <div class="modal modal-open">
        <div class="modal-box max-w-4xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Task Details - {{ $selectedUser->name }} {{ $selectedUser->surname }}</h3>
                <button wire:click="closeModal" class="btn btn-sm btn-circle btn-ghost">✕</button>
            </div>
            
            @if($selectedUserTasks->count() > 0)
                @php
                    $tasksByDay = $selectedUserTasks->groupBy(function($task) {
                        return \Carbon\Carbon::parse($task->start_date)->format('l'); // Day name (Monday, Tuesday, etc.)
                    });
                    $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                @endphp
                
                <div class="space-y-4">
                    @foreach($daysOfWeek as $day)
                        <div class="card bg-base-100 shadow-sm border">
                            <div class="card-body p-4">
                                <div class="flex justify-between items-center mb-3">
                                    <h4 class="card-title text-lg">
                                        @if($tasksByDay->has($day))
                                            <span class="text-success">{{ $day }}</span>
                                            <span class="badge badge-success badge-sm">{{ $tasksByDay[$day]->count() }} task(s)</span>
                                        @else
                                            <span class="text-error">{{ $day }}</span>
                                            <span class="badge badge-error badge-sm">No tasks</span>
                                        @endif
                                    </h4>
                                </div>
                                
                                @if($tasksByDay->has($day))
                                    <div class="space-y-3">
                                        @foreach($tasksByDay[$day] as $task)
                                            <div class="bg-base-200 p-3 rounded-lg">
                                                <div class="flex justify-between items-start mb-2">
                                                    <h5 class="font-medium">{{ $task->title }}</h5>
                                                    <div class="flex gap-2">
                                                        <span class="badge badge-outline badge-sm">{{ $task->status }}</span>
                                                        <span class="badge badge-secondary badge-sm">{{ $task->priority }}</span>
                                                        @if($task->individualoutputbreakdown_id)
                                                            <span class="badge badge-success badge-sm">Linked</span>
                                                        @else
                                                            <span class="badge badge-warning badge-sm">Unlinked</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <p class="text-sm text-gray-600 mb-2">{{ $task->description }}</p>
                                                
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                                                    <div>
                                                        <span class="font-medium">Time:</span>
                                                        <span>{{ \Carbon\Carbon::parse($task->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($task->end_date)->format('M d, Y') }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="font-medium">Approval:</span>
                                                        <span class="badge badge-sm">{{ $task->approvalstatus }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4 text-error">
                                        <div class="text-sm">No tasks assigned for this day</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <div class="text-gray-500">No tasks found for this user.</div>
                </div>
            @endif
            
            <div class="modal-action">
                <button wire:click="closeModal" class="btn btn-primary">Close</button>
            </div>
        </div>
    </div>
    @endif
</div> 
 