<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box overflow-x-auto whitespace-nowrap"
    link-item-class="text-base" />

    <x-card title="{{ $tasks['calendarweek'] ? $tasks['calendarweek']->week : 'No Calendar Week Found' }}" subtitle="{{ $tasks['calendarweek'] ? $tasks['calendarweek']->start_date . ' - ' . $tasks['calendarweek']->end_date : 'Please select a valid week' }}" class="mt-2 border-2 border-gray-200" separator>
        <x-slot:menu>
            <x-select wire:model.live="week" :options="$weeks" option-label="week" option-value="id" placeholder="Filter by week" />
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
                // Include new tasks (approvalstatus == 'pending') for bulk approval, except completed
                // New ongoing tasks will be included, but previously approved tasks updated to ongoing won't
                $pendingInitialTasks = $selectedUserTasks->filter(function($task) {
                    return $task->approvalstatus == 'pending' && $task->status != 'completed';
                });
                $completedTasksNeedingApproval = $selectedUserTasks->filter(function($task) {
                    return $task->approvalstatus == 'pending' && $task->status == 'completed';
                });
            @endphp
            
            @if($pendingInitialTasks->count() > 0)
            <div class="alert alert-info mb-4">
                <div class="flex-1">
                    <p class="text-sm font-semibold">{{ $pendingInitialTasks->count() }} task(s) pending initial approval</p>
                </div>
                <div>
                    <button wire:click="openBulkApprovalModal('{{ $selectedUser->id }}')" class="btn btn-sm btn-primary">
                        Approve All Tasks
                    </button>
                </div>
            </div>
            @endif
            
            @if($completedTasksNeedingApproval->count() > 0)
            <div class="alert alert-warning mb-4">
                <div class="flex-1">
                    <p class="text-sm font-semibold">{{ $completedTasksNeedingApproval->count() }} completed task(s) pending approval</p>
                </div>
                <div>
                    <button wire:click="openCompletedBulkApprovalModal('{{ $selectedUser->id }}')" class="btn btn-sm btn-warning">
                        Approve All Completed Tasks
                    </button>
                </div>
            </div>
            @endif
            @php
                $tasksByDay = $selectedUserTasks->groupBy(function($task) {
                    return $task->calendarday ? \Carbon\Carbon::parse($task->calendarday->maindate)->format('l') : 'Unknown';
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
                                                        <span class="badge badge-sm {{ $task->approvalstatus == 'Approved' ? 'badge-success' : ($task->approvalstatus == 'Rejected' ? 'badge-error' : 'badge-warning') }}">{{ $task->approvalstatus }}</span>
                                                        @if($task->approvalstatus === 'pending')
                                                            <div class="flex gap-2 mt-2">
                                                                <button wire:click="openIndividualApprovalModal({{ $task->id }}, 'Approved')" class="btn btn-xs btn-success">Approve</button>
                                                                <button wire:click="openIndividualApprovalModal({{ $task->id }}, 'Rejected')" class="btn btn-xs btn-error">Send Back</button>
                                                            </div>
                                                        @endif
                                                        @if($task->approval_comment)
                                                            <div class="mt-2 p-2 bg-base-300 rounded text-xs">
                                                                <span class="font-medium">Comment:</span> {{ $task->approval_comment }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                {{-- Show evidence if available --}}
                                                @if($task->evidence_path)
                                                <div class="mt-2 flex items-center gap-2 text-xs">
                                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                                    </svg>
                                                    <span class="font-medium">Evidence:</span>
                                                    <a href="{{ asset('storage/' . $task->evidence_path) }}" target="_blank" class="text-blue-600 hover:underline">
                                                        {{ $task->evidence_original_name ?? 'View Attachment' }}
                                                    </a>
                                                </div>
                                                @endif
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

    <!-- Bulk Approval Modal -->
    @if($showBulkApprovalModal && $selectedUser)
    <div class="modal modal-open">
        <div class="modal-box max-w-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Bulk Approval - {{ $selectedUser->name }} {{ $selectedUser->surname }}</h3>
                <button wire:click="closeBulkApprovalModal" class="btn btn-sm btn-circle btn-ghost">✕</button>
            </div>
            
            @php
                // Include new tasks (approvalstatus == 'pending') for bulk approval, except completed
                // New ongoing tasks will be included, but previously approved tasks updated to ongoing won't
                $pendingInitialTasks = $selectedUserTasks->filter(function($task) {
                    return $task->approvalstatus == 'pending' && $task->status != 'completed';
                });
            @endphp
            
            <div class="space-y-4">
                <div class="alert alert-info">
                    <p class="text-sm">You are about to make a decision on <strong>{{ $pendingInitialTasks->count() }} task(s)</strong> for this week.</p>
                </div>
                
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Decision</span>
                    </label>
                    <select wire:model="bulkApprovalStatus" class="select select-bordered w-full">
                        <option value="Approved">Approve</option>
                        <option value="Rejected">Send Back</option>
                    </select>
                </div>
                
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Comment (Optional)</span>
                    </label>
                    <textarea 
                        wire:model="bulkApprovalComment" 
                        class="textarea textarea-bordered w-full" 
                        rows="4"
                        placeholder="Enter an optional comment for this decision..."
                    ></textarea>
                </div>
                
                <div class="bg-base-200 p-3 rounded-lg">
                    <p class="text-sm font-semibold mb-2">Tasks:</p>
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach($pendingInitialTasks as $task)
                            <li>{{ $task->title }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            
            <div class="modal-action">
                <button wire:click="closeBulkApprovalModal" class="btn btn-outline">Cancel</button>
                <button wire:click="bulkApproveTasks" class="btn btn-primary">
                    Make Decision
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Bulk Approval Modal for Completed Tasks -->
    @if($showCompletedBulkApprovalModal && $selectedUser)
    <div class="modal modal-open">
        <div class="modal-box max-w-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Bulk Approval - Completed Tasks - {{ $selectedUser->name }} {{ $selectedUser->surname }}</h3>
                <button wire:click="closeCompletedBulkApprovalModal" class="btn btn-sm btn-circle btn-ghost">✕</button>
            </div>
            
            @php
                $completedTasks = $selectedUserTasks->filter(function($task) {
                    return $task->approvalstatus == 'pending' && $task->status == 'completed';
                });
            @endphp
            
            <div class="space-y-4">
                <div class="alert alert-warning">
                    <p class="text-sm">You are about to make a decision on <strong>{{ $completedTasks->count() }} completed task(s)</strong>.</p>
                </div>
                
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Decision</span>
                    </label>
                    <select wire:model="completedBulkApprovalStatus" class="select select-bordered w-full">
                        <option value="Approved">Approve</option>
                        <option value="Rejected">Send Back</option>
                    </select>
                </div>
                
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Comment (Optional)</span>
                    </label>
                    <textarea 
                        wire:model="completedBulkApprovalComment" 
                        class="textarea textarea-bordered w-full" 
                        rows="4"
                        placeholder="Enter an optional comment for this decision..."
                    ></textarea>
                </div>
                
                <div class="bg-base-200 p-3 rounded-lg">
                    <p class="text-sm font-semibold mb-2">Completed Tasks:</p>
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach($completedTasks as $task)
                            <li>{{ $task->title }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            
            <div class="modal-action">
                <button wire:click="closeCompletedBulkApprovalModal" class="btn btn-outline">Cancel</button>
                <button wire:click="bulkApproveCompletedTasks" class="btn btn-warning">
                    Make Decision
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Individual Task Approval Modal -->
    @if($showIndividualApprovalModal)
    <div class="modal modal-open">
        <div class="modal-box max-w-lg">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold {{ $individualApprovalStatus == 'Approved' ? 'text-success' : 'text-error' }}">
                    {{ $individualApprovalStatus == 'Approved' ? '✓ Approve Task' : '✕ Send Back Task' }}
                </h3>
                <button wire:click="closeIndividualApprovalModal" class="btn btn-sm btn-circle btn-ghost">✕</button>
            </div>
            
            <div class="space-y-4">
                <div class="alert {{ $individualApprovalStatus == 'Approved' ? 'alert-success' : 'alert-error' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        @if($individualApprovalStatus == 'Approved')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        @endif
                    </svg>
                    <p class="text-sm">
                        You are about to <strong>{{ $individualApprovalStatus == 'Approved' ? 'approve' : 'send back' }}</strong> this task.
                    </p>
                </div>
                
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Comment (Optional)</span>
                    </label>
                    <textarea 
                        wire:model="individualApprovalComment" 
                        class="textarea textarea-bordered w-full" 
                        rows="3"
                        placeholder="Enter an optional comment for your decision..."
                    ></textarea>
                </div>
            </div>
            
            <div class="modal-action">
                <button wire:click="closeIndividualApprovalModal" class="btn btn-outline">Cancel</button>
                <button 
                    wire:click="submitIndividualApproval" 
                    class="btn {{ $individualApprovalStatus == 'Approved' ? 'btn-success' : 'btn-error' }}"
                >
                    {{ $individualApprovalStatus == 'Approved' ? 'Approve Task' : 'Send Back Task' }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div> 
 