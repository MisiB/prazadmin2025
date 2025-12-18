<div>
    <style>
        /* Hide the issue-card's built-in actions section */
        .issue-card-wrapper .flex.items-center.gap-2.pt-4.border-t.border-gray-200 {
            display: none !important;
        }
        /* Remove the card styling from the included issue-card since we're wrapping it */
        .issue-card-wrapper > div {
            border: none !important;
            box-shadow: none !important;
            border-radius: 0 !important;
        }
    </style>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50">
            <!-- Modern Header -->
            <div class="bg-white/80 backdrop-blur-sm shadow-sm border-b border-gray-200 mb-6 sticky top-0 z-10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div class="flex-1">
                            <h1 class="text-3xl font-bold text-gray-900 mb-3 tracking-tight">My Assigned Issues</h1>
                            <p class="text-gray-600">View and manage issues assigned to you</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
                <!-- Summary Card -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Assigned Issues Summary -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-3 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg shadow-purple-500/30">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Assigned to Me</h3>
                                <p class="text-sm text-gray-600">Tickets for you to handle</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-4 gap-3">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900">{{ $statusCounts['total'] }}</div>
                                <div class="text-xs text-gray-600">Total</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ $statusCounts['open'] }}</div>
                                <div class="text-xs text-gray-600">Open</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600">{{ $statusCounts['in_progress'] }}</div>
                                <div class="text-xs text-gray-600">Progress</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">{{ $statusCounts['resolved'] }}</div>
                                <div class="text-xs text-gray-600">Resolved</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                        <x-input 
                            wire:model.live.debounce.300ms="search" 
                            icon="o-magnifying-glass" 
                            placeholder="Search tickets..." 
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
                            placeholder="Filter by Status"
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
                            placeholder="Filter by Priority"
                        />
                        
                        <x-button 
                            icon="o-arrow-path" 
                            label="Reset" 
                            wire:click="$set('search', ''); $set('filterStatus', ''); $set('filterPriority', '')" 
                            class="btn-outline"
                        />
                    </div>
                    
                    <!-- Export Section -->
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-gray-700">Export Tickets to Excel</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-input 
                                type="date"
                                wire:model="exportStartDate" 
                                label="Start Date"
                            />
                            
                            <x-input 
                                type="date"
                                wire:model="exportEndDate" 
                                label="End Date"
                            />
                            
                            <div class="flex items-end">
                                <x-button 
                                    icon="o-arrow-down-tray" 
                                    label="Export to Excel" 
                                    wire:click="exportToExcel"
                                    spinner="exportToExcel"
                                    class="btn-primary w-full"
                                />
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            Select a date range to export all ticket details. The export will include all tickets assigned to you within the selected period.
                        </p>
                    </div>
                </div>

                <!-- Issues List -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
                    <div class="p-6">
                        <div class="space-y-4">
                            @forelse($issues as $issue)
                                <div wire:key="issue-{{ $issue->id }}" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                                    <!-- Include issue card content but hide its actions section -->
                                    <div class="issue-card-wrapper">
                                        @include('livewire.admin.partials.issue-card', [
                                            'issue' => $issue, 
                                            'showEdit' => false,
                                            'showComments' => false
                                        ])
                                    </div>
                                    
                                    <!-- Custom Actions Section (inside the same card) -->
                                    <div class="flex items-center gap-2 pt-4 border-t border-gray-200 px-6 pb-6 flex-wrap">
                                        <!-- View Comments Button -->
                                        <x-button 
                                            icon="o-chat-bubble-left-right" 
                                            label="View Comments ({{ $issue->comments->count() }})" 
                                            wire:click="openCommentsModal({{ $issue->id }})" 
                                            class="btn-outline btn-info btn-sm"
                                        />
                                        
                                        @if($issue->status != 'closed')
                                            @if($issue->status == 'open')
                                            <x-button 
                                                icon="o-play" 
                                                label="Mark In Progress" 
                                                wire:click="updateStatus({{ $issue->id }}, 'in_progress')" 
                                                wire:confirm="Mark this ticket as in progress?"
                                                class="btn-outline btn-info btn-sm"
                                            />
                                            @endif

                                            @if($issue->status == 'in_progress')
                                            <x-button 
                                                icon="o-check-circle" 
                                                label="Mark Resolved" 
                                                wire:click="updateStatus({{ $issue->id }}, 'resolved')" 
                                                wire:confirm="Mark this ticket as resolved? An email will be sent to the user."
                                                class="btn-outline btn-success btn-sm"
                                            />
                                            @endif

                                            @if($issue->status == 'resolved')
                                            <div class="flex items-center gap-2 text-sm text-green-600 bg-green-100 px-3 py-1.5 rounded-lg">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span class="font-semibold">Resolved - Awaiting Closure</span>
                                            </div>
                                            @endif
                                        @else
                                        <div class="flex items-center gap-2 text-sm text-gray-600 bg-gray-100 px-3 py-1.5 rounded-lg">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                            <span class="font-semibold">Ticket Closed</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-16">
                                    <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                    </svg>
                                    <h3 class="text-2xl font-bold text-gray-900 mb-2">No Assigned Tickets</h3>
                                    <p class="text-gray-600">You don't have any tickets assigned to you at the moment</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comments Modal -->
            <x-modal wire:model="showCommentsModal" title="Issue Comments & Discussion" separator box-class="max-w-4xl">
                <div class="space-y-5">
                    <!-- Add Comment Section -->
                    <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                        <h3 class="text-sm font-bold text-blue-900 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add New Comment
                        </h3>
                        
                        <div class="space-y-3">
                            <x-textarea 
                                wire:model="newComment" 
                                placeholder="Type your comment here..." 
                                rows="3"
                                hint="Share updates, questions, or information about this issue"
                            />
                            
                            <div class="flex items-center justify-between">
                                <x-checkbox 
                                    wire:model="isInternalComment" 
                                    label="Internal Comment (Only visible to staff)" 
                                    hint="Internal comments won't send email notifications"
                                />
                                
                                <x-button 
                                    label="Add Comment" 
                                    wire:click="addComment" 
                                    class="btn-primary btn-sm" 
                                    spinner="addComment"
                                    icon="o-paper-airplane"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Comments List -->
                    <div class="space-y-3">
                        @forelse($this->getIssueComments() as $comment)
                            <div class="bg-white rounded-xl border border-gray-200 p-4">
                                @if($editingCommentId === $comment->id)
                                    <!-- Edit Mode -->
                                    <div class="space-y-3">
                                        <x-textarea 
                                            wire:model="editCommentText" 
                                            rows="3"
                                        />
                                        
                                        <div class="flex items-center justify-between">
                                            <x-checkbox 
                                                wire:model="editIsInternal" 
                                                label="Internal Comment" 
                                            />
                                            
                                            <div class="flex gap-2">
                                                <x-button 
                                                    label="Cancel" 
                                                    wire:click="cancelEditComment" 
                                                    class="btn-outline btn-sm" 
                                                />
                                                <x-button 
                                                    label="Update" 
                                                    wire:click="updateComment" 
                                                    class="btn-primary btn-sm" 
                                                    spinner="updateComment"
                                                    icon="o-check"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <!-- View Mode -->
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="text-sm font-semibold text-gray-900">{{ $comment->user_email }}</span>
                                                @if($comment->is_internal)
                                                    <span class="px-2 py-0.5 bg-purple-100 text-purple-800 rounded-full text-xs font-semibold">Internal</span>
                                                @endif
                                                <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $comment->comment }}</p>
                                        </div>
                                        
                                        @if($comment->user_email === auth()->user()->email)
                                            <div class="flex gap-2">
                                                <x-button 
                                                    icon="o-pencil" 
                                                    wire:click="startEditComment({{ $comment->id }})" 
                                                    class="btn-ghost btn-xs"
                                                />
                                                <x-button 
                                                    icon="o-trash" 
                                                    wire:click="deleteComment({{ $comment->id }})" 
                                                    wire:confirm="Are you sure you want to delete this comment?"
                                                    class="btn-ghost btn-xs text-red-600"
                                                />
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <p>No comments yet. Be the first to comment!</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                
                <x-slot:actions>
                    <x-button 
                        label="Close" 
                        wire:click="closeCommentsModal" 
                        class="btn-outline" 
                    />
                </x-slot:actions>
            </x-modal>
    </div>
</div>