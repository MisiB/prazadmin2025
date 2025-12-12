<div class="group relative bg-white rounded-lg border-l-4 {{ $task->priority == 'High' ? 'border-red-500' : ($task->priority == 'Medium' ? 'border-yellow-500' : 'border-green-500') }} border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all duration-200">
    <!-- Row 1: Title, Description, Badges -->
    <div class="p-3 pb-2">
        <div class="flex items-start justify-between gap-3 mb-2">
            <div class="flex-1 min-w-0">
                <h4 class="text-base font-bold text-gray-900 mb-0.5 truncate">{{ $task->title }}</h4>
                <p class="text-xs text-gray-500 line-clamp-1">{{ $task->description }}</p>
            </div>
            <div class="flex items-center gap-1.5 flex-shrink-0">
                <x-badge 
                    value="{{ ucfirst($task->status) }}" 
                    class="badge-xs {{ $task->status == 'completed' ? 'badge-success' : ($task->status == 'ongoing' ? 'badge-info' : 'badge-warning') }}" 
                />
                <x-badge 
                    value="{{ $task->priority }}" 
                    class="badge-xs {{ $task->priority == 'High' ? 'badge-error' : ($task->priority == 'Medium' ? 'badge-warning' : 'badge-success') }}" 
                />
                <x-badge 
                    value="{{ $task->approvalstatus == 'Approved' ? 'Approved' : ($task->approvalstatus == 'Rejected' ? 'Rejected' : 'Pending') }}" 
                    class="badge-xs {{ $task->approvalstatus == 'Approved' ? 'badge-success' : ($task->approvalstatus == 'Rejected' ? 'badge-error' : 'badge-warning') }}" 
                />
            </div>
        </div>
        
        <!-- Row 2: Hours Information -->
        <div class="flex items-center gap-4 text-xs mb-2">
            <div class="flex items-center gap-1.5 text-gray-600">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-medium">{{ $task->duration }} {{ $task->uom }}</span>
            </div>
            
            @if($activeInstance)
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-1 text-blue-600">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-semibold">{{ $activeInstance->planned_hours }}h</span>
                    <span class="text-blue-500">planned</span>
                </div>
                <div class="flex items-center gap-1 text-green-600">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-semibold">{{ $activeInstance->worked_hours }}h</span>
                    <span class="text-green-500">worked</span>
                </div>
            </div>
            @endif
        </div>
        
        <!-- Approval Comment -->
        @if($task->approval_comment)
        <div class="mb-2 p-2.5 rounded-lg text-xs {{ $task->approvalstatus == 'Approved' ? 'bg-green-50 border border-green-200' : ($task->approvalstatus == 'Rejected' ? 'bg-red-50 border border-red-200' : 'bg-gray-50 border border-gray-200') }}">
            <div class="flex items-start gap-2">
                <svg class="w-4 h-4 {{ $task->approvalstatus == 'Approved' ? 'text-green-600' : ($task->approvalstatus == 'Rejected' ? 'text-red-600' : 'text-gray-600') }} flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                </svg>
                <div class="flex-1">
                    <p class="font-semibold {{ $task->approvalstatus == 'Approved' ? 'text-green-900' : ($task->approvalstatus == 'Rejected' ? 'text-red-900' : 'text-gray-900') }} mb-0.5">
                        {{ $task->approvalstatus == 'Approved' ? 'Approver\'s Comment:' : ($task->approvalstatus == 'Rejected' ? 'Rejection Comment:' : 'Comment:') }}
                    </p>
                    <p class="{{ $task->approvalstatus == 'Approved' ? 'text-green-800' : ($task->approvalstatus == 'Rejected' ? 'text-red-800' : 'text-gray-800') }} leading-relaxed">
                        {{ $task->approval_comment }}
                    </p>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Row 3: Action Buttons -->
        <div class="flex flex-wrap items-center gap-1.5 pt-2 border-t border-gray-100">
            @if($task->status != 'completed')
                {{-- Rejected tasks: Only show Edit and Delete --}}
                @if($task->approvalstatus == 'Rejected')
                <x-button 
                    icon="o-pencil" 
                    label="Edit" 
                    class="btn-xs btn-outline btn-info" 
                    wire:click="edit({{ $task->id }})"
                />
                <x-button 
                    icon="o-trash" 
                    label="Delete" 
                    class="btn-xs btn-outline btn-error" 
                    wire:click="delete({{ $task->id }})"
                    wire:confirm="Are you sure you want to delete this task?"
                />
                @else
                {{-- Non-rejected tasks: Show all available buttons --}}
                <x-button 
                    icon="o-check-circle" 
                    label="Status" 
                    class="btn-xs btn-outline btn-success" 
                    wire:click="openmarkmodal({{ $task->id }})"
                />
                <x-button 
                    icon="o-pencil" 
                    label="Edit" 
                    class="btn-xs btn-outline btn-info" 
                    wire:click="edit({{ $task->id }})"
                />
                @if($activeInstance)
                    {{-- Log Hours - Only for ongoing tasks --}}
                    @if($task->status == 'ongoing')
                    <x-button 
                        icon="o-clock" 
                        label="Log Hours" 
                        class="btn-xs btn-outline btn-primary" 
                        wire:click="openLogHoursModal({{ $task->id }})"
                    />
                    @endif
                    
                    {{-- Rollover - For ongoing tasks with logged hours OR pending tasks --}}
                    @if(($task->status == 'ongoing' && $activeInstance->worked_hours > 0) || $task->status == 'pending')
                    <x-button 
                        icon="o-arrow-path" 
                        label="Rollover" 
                        class="btn-xs btn-outline btn-warning" 
                        wire:click="rolloverTask({{ $task->id }})"
                        wire:confirm="Roll over this task to tomorrow? @if($task->status == 'ongoing' && $activeInstance){{ $activeInstance->planned_hours - $activeInstance->worked_hours }} hours will be carried forward.@else The task will be moved to the next day.@endif"
                    />
                    @endif
                @elseif($task->status == 'pending')
                    {{-- Rollover for pending tasks without active instance --}}
                    <x-button 
                        icon="o-arrow-path" 
                        label="Rollover" 
                        class="btn-xs btn-outline btn-warning" 
                        wire:click="rolloverTask({{ $task->id }})"
                        wire:confirm="Roll over this task to tomorrow? The task will be moved to the next day."
                    />
                @endif
                @if($task->approvalstatus == 'pending')
                <x-button 
                    icon="o-trash" 
                    label="Delete" 
                    class="btn-xs btn-outline btn-error" 
                    wire:click="delete({{ $task->id }})"
                    wire:confirm="Are you sure you want to delete this task?"
                />
                @endif
                @endif
            @else
            <div class="flex items-center gap-1.5 text-xs text-green-700 bg-green-50 px-2 py-1 rounded">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-semibold">Completed</span>
            </div>
            @endif
        </div>
    </div>
</div>
