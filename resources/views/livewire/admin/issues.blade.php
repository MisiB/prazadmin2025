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
                    <h1 class="text-3xl font-bold text-gray-900 mb-3 tracking-tight">Issue Ticket System</h1>
                    <p class="text-gray-600">Create and track support tickets for quick resolution</p>
               </div>
                
                <div class="flex flex-col sm:flex-row gap-3">
                    <x-button 
                        icon="o-plus" 
                        label="Create New Ticket" 
                        wire:click="openModal" 
                        class="btn-primary shadow-lg shadow-blue-500/30" 
                    />
                </div>
            </div>
        </div>
              </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <!-- Filters and Search -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                    label="Reset Filters" 
                    wire:click="$set('search', ''); $set('filterStatus', ''); $set('filterPriority', '')" 
                    class="btn-outline"
                />
            </div>
        </div>

        <!-- Issues Grid -->
        <div class="grid gap-4">
            @forelse($issues as $issue)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-all duration-300">
                    <!-- Ticket Header -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-gradient-to-br {{ $issue->priority == 'High' ? 'from-red-500 to-red-600' : ($issue->priority == 'Medium' ? 'from-yellow-500 to-yellow-600' : 'from-green-500 to-green-600') }} rounded-xl shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">{{ $issue->title }}</h3>
                                    <div class="flex items-center gap-3 mt-1">
                                        <span class="text-sm text-gray-600 font-mono">{{ $issue->ticketnumber }}</span>
                                        <span class="text-sm text-gray-400">•</span>
                                        <span class="text-sm text-gray-600">{{ $issue->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $issue->priority == 'High' ? 'bg-red-100 text-red-800' : ($issue->priority == 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                    {{ $issue->priority }}
                                </span>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $issue->status == 'open' ? 'bg-blue-100 text-blue-800' : ($issue->status == 'in_progress' ? 'bg-purple-100 text-purple-800' : ($issue->status == 'resolved' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) }}">
                                    {{ ucfirst(str_replace('_', ' ', $issue->status)) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Ticket Content -->
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    <span class="text-sm text-gray-600">Group:</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $issue->issuegroup->name ?? 'N/A' }}</span>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    <span class="text-sm text-gray-600">Type:</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $issue->issuetype->name ?? 'N/A' }}</span>
                                </div>

                                @if($issue->regnumber)
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="text-sm text-gray-600">Reg #:</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $issue->regnumber }}</span>
                                </div>
                                @endif
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span class="text-sm text-gray-600">Name:</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $issue->name }}</span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-sm text-gray-600">Email:</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $issue->email }}</span>
                                </div>

                                @if($issue->phone)
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    <span class="text-sm text-gray-600">Phone:</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $issue->phone }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Assignment Information -->
                        @if($issue->assigned_to)
                        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl p-4 border border-indigo-200 mb-4">
                            <h4 class="text-sm font-semibold text-indigo-900 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Assignment Details
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <div>
                                        <div class="text-xs text-gray-600">Department</div>
                                        <div class="text-sm font-semibold text-gray-900">{{ $issue->department->name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <div>
                                        <div class="text-xs text-gray-600">Assigned To</div>
                                        <div class="text-sm font-semibold text-gray-900">{{ $issue->assignedto->name ?? 'N/A' }} {{ $issue->assignedto->surname ?? '' }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <div class="text-xs text-gray-600">Assigned At</div>
                                        <div class="text-sm font-semibold text-gray-900">{{ $issue->assigned_at ? $issue->assigned_at->format('M d, Y') : 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Description -->
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 mb-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Description</h4>
                            <p class="text-sm text-gray-900 leading-relaxed">{{ $issue->description }}</p>
                        </div>

                        <!-- Attachments Display -->
                        @if($issue->attachments && count($issue->attachments) > 0)
                        <div class="bg-purple-50 rounded-xl p-4 border border-purple-200 mb-4">
                            <h4 class="text-sm font-semibold text-purple-900 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                Attachments ({{ count($issue->attachments) }})
                            </h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                @foreach($issue->attachments as $attachment)
                                <a 
                                    href="{{ asset('storage/'.$attachment['path']) }}" 
                                    target="_blank"
                                    class="group relative block"
                                    title="{{ $attachment['original_name'] ?? 'Attachment' }}"
                                >
                                    @if(isset($attachment['mime_type']) && str_starts_with($attachment['mime_type'], 'image/'))
                                    <img 
                                        src="{{ asset('storage/'.$attachment['path']) }}" 
                                        alt="{{ $attachment['original_name'] ?? 'Attachment' }}" 
                                        class="w-full h-24 object-cover rounded-lg border border-purple-200 group-hover:border-purple-400 transition-all"
                                    />
                                    @else
                                    <div class="w-full h-24 bg-purple-100 rounded-lg border border-purple-200 group-hover:border-purple-400 transition-all flex flex-col items-center justify-center">
                                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-xs text-purple-700 mt-1 truncate max-w-full px-2">{{ pathinfo($attachment['original_name'] ?? 'file', PATHINFO_EXTENSION) }}</span>
                                    </div>
                                    @endif
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </div>
                                </a>
                                @endforeach
                            </div>
              </div>
                        @endif

                        <!-- Actions -->
                        <div class="flex items-center gap-2 pt-4 border-t border-gray-200 flex-wrap">
                            <!-- View Comments Button -->
                            <x-button 
                                icon="o-chat-bubble-left-right" 
                                label="View Comments ({{ $issue->comments->count() }})" 
                                wire:click="openCommentsModal({{ $issue->id }})" 
                                class="btn-outline btn-info btn-sm"
                            />
                            
                            @if($issue->status != 'closed')
                            
                            <!-- Assign Button -->
                            @if(!$issue->assigned_to)
                            <x-button 
                                icon="o-user-plus" 
                                label="Assign to Department" 
                                wire:click="openAssignModal({{ $issue->id }})" 
                                class="btn-outline btn-purple btn-sm"
                            />
                            @endif

                            <x-button 
                                icon="o-pencil" 
                                label="Edit" 
                                wire:click="edit({{ $issue->id }})" 
                                class="btn-outline btn-sm"
                            />
                            
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
                            <x-button 
                                icon="o-lock-closed" 
                                label="Close Ticket" 
                                wire:click="updateStatus({{ $issue->id }}, 'closed')" 
                                wire:confirm="Close this ticket permanently?"
                                class="btn-outline btn-warning btn-sm"
                            />
                            @endif

                            <x-button 
                                icon="o-trash" 
                                label="Delete" 
                                wire:click="delete({{ $issue->id }})" 
                                wire:confirm="Are you sure you want to delete this ticket?"
                                class="btn-outline btn-error btn-sm"
                            />
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-16">
                    <div class="bg-blue-50 rounded-2xl p-12 border border-blue-200 inline-block">
                        <svg class="w-20 h-20 mx-auto text-blue-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">No Tickets Found</h3>
                        <p class="text-gray-600 mb-4">Create your first support ticket to get started</p>
                        <x-button 
                            icon="o-plus" 
                            label="Create Ticket" 
                            wire:click="openModal" 
                            class="btn-primary"
                        />
                    </div>
                </div>
            @endforelse
        </div>
               </div>

    <!-- Create/Edit Ticket Modal -->
    <x-modal wire:model="showModal" title="{{ $editingId ? 'Edit Ticket' : 'Create New Ticket' }}" separator box-class="max-w-4xl">
       
        
        <x-form wire:submit="save">
            <div class="space-y-5">
                <!-- Ticket Information -->
                <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                    <h3 class="text-sm font-bold text-blue-900 mb-3">Ticket Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-select 
                            wire:model="issuegroup_id" 
                            label="Issue Group" 
                            placeholder="Select issue group" 
                            :options="$issuegroups" 
                            option-label="name" 
                            option-value="id"
                            icon="o-rectangle-stack"
                        />
                        
                        <x-select 
                            wire:model="issuetype_id" 
                            label="Issue Type" 
                            placeholder="Select issue type" 
                            :options="$issuetypes" 
                            option-label="name" 
                            option-value="id"
                            icon="o-tag"
                        />
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                    <h3 class="text-sm font-bold text-gray-900 mb-3">Contact Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-input 
                            wire:model="name" 
                            label="Full Name" 
                            placeholder="Enter your name"
                            icon="o-user"
                        />
                        
                        <x-input 
                            wire:model="email" 
                            label="Email Address" 
                            type="email"
                            placeholder="your@email.com"
                            icon="o-envelope"
                        />
                        
                        <x-input 
                            wire:model="phone" 
                            label="Phone Number" 
                            placeholder="+263..."
                            icon="o-phone"
                        />
                        
                        <x-input 
                            wire:model="regnumber" 
                            label="Registration Number (Optional)" 
                            placeholder="REG-XXXXX"
                            icon="o-identification"
                        />
                    </div>
                </div>

                <!-- Issue Details -->
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                    <h3 class="text-sm font-bold text-gray-900 mb-3">Issue Details</h3>
                    
                    <div class="mb-4">
                        <x-input 
                            wire:model="title" 
                            label="Issue Title" 
                            placeholder="Brief description of the issue"
                            hint="Provide a clear, concise title"
                            icon="o-document-text"
                        />
                    </div>

                    <div class="mb-4">
                        <x-textarea 
                            wire:model="description" 
                            label="Detailed Description" 
                            placeholder="Describe the issue in detail..."
                            rows="5"
                            hint="Include all relevant information to help us resolve your issue quickly"
                        />
                    </div>

                    <x-select 
                        wire:model="priority" 
                        label="Priority Level" 
                        :options="[
                            ['id' => 'Low', 'name' => 'Low - Can wait'],
                            ['id' => 'Medium', 'name' => 'Medium - Normal priority'],
                            ['id' => 'High', 'name' => 'High - Urgent']
                        ]" 
                        option-label="name" 
                        option-value="id"
                        icon="o-flag"
                    />
                </div>

                <!-- Attachments Section -->
                <div class="bg-purple-50 rounded-xl p-4 border border-purple-200">
                    <h3 class="text-sm font-bold text-purple-900 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                        Supporting Images (Optional)
                    </h3>
                    
                    <div class="mb-3">
                        <input 
                            type="file" 
                            wire:model="attachments" 
                            multiple 
                            accept="image/*"
                            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-white focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-500 file:text-white hover:file:bg-purple-600"
                        />
                        <p class="text-xs text-gray-500 mt-2">Upload images (PNG, JPG, GIF). Max 5MB per file.</p>
                    </div>

                    <!-- Loading indicator -->
                    <div wire:loading wire:target="attachments" class="flex items-center gap-2 text-purple-600 text-sm">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Uploading...
                    </div>

                    <!-- Preview existing attachments -->
                    @if($existingAttachments && count($existingAttachments) > 0)
                    <div class="mt-4">
                        <h4 class="text-xs font-semibold text-gray-700 mb-2">Existing Attachments:</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            @foreach($existingAttachments as $index => $attachment)
                            <div class="relative group">
                                @if(isset($attachment['mime_type']) && str_starts_with($attachment['mime_type'], 'image/'))
                                <img 
                                    src="{{ asset('storage/'.$attachment['path']) }}" 
                                    alt="{{ $attachment['original_name'] ?? 'Attachment' }}" 
                                    class="w-full h-24 object-cover rounded-lg border border-gray-200"
                                />
                                @else
                                <div class="w-full h-24 bg-gray-100 rounded-lg border border-gray-200 flex flex-col items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-xs text-gray-700 mt-1 truncate max-w-full px-2">{{ pathinfo($attachment['original_name'] ?? 'file', PATHINFO_EXTENSION) }}</span>
                                </div>
                                @endif
                                <button 
                                    type="button"
                                    wire:click="removeAttachment({{ $index }})"
                                    class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity shadow-lg"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            @endforeach
                        </div>
            </div>
                    @endif

                    <!-- Preview new attachments -->
                    @if($attachments && count($attachments) > 0)
                    <div class="mt-4">
                        <h4 class="text-xs font-semibold text-gray-700 mb-2">New Attachments to Upload:</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            @foreach($attachments as $attachment)
                            <div class="relative">
                                <img 
                                    src="{{ $attachment->temporaryUrl() }}" 
                                    alt="Preview" 
                                    class="w-full h-24 object-cover rounded-lg border-2 border-purple-300"
                                />
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
        </div>
      </div>

            <x-slot name="actions">
                <x-button 
                    label="Cancel" 
                    wire:click="closeModal" 
                    class="btn-outline" 
                />
                <x-button 
                    label="{{ $editingId ? 'Update Ticket' : 'Create Ticket' }}" 
                    type="submit" 
                    class="btn-primary shadow-lg shadow-blue-500/30" 
                    spinner="save"
                    icon="o-check"
                />
            </x-slot>
        </x-form>
    </x-modal>

    <!-- Assign Issue Modal -->
    <x-modal wire:model="showAssignModal" title="Assign Issue to Department" separator box-class="max-w-2xl">
       
        
        <div class="space-y-5">
            <div class="bg-purple-50 rounded-xl p-4 border border-purple-200">
                <p class="text-sm text-purple-900">
                    Assign this issue to a department and a specific team member to handle it.
                </p>
            </div>

            <x-select 
                wire:model.live="selectedDepartment" 
                label="Select Department" 
                placeholder="Choose a department" 
                :options="$departments" 
                option-label="name" 
                option-value="id"
                icon="o-building-office"
                hint="First select the department that will handle this issue"
            />

            @if($selectedDepartment)
            <x-select 
                wire:model="selectedUser" 
                label="Assign To User" 
                placeholder="Select a user from the department" 
                :options="$departmentUsers->map(fn($u) => ['id' => $u->id, 'name' => $u->name . ' ' . $u->surname])" 
                option-label="name" 
                option-value="id"
                icon="o-user"
                hint="Select the team member who will work on this issue"
            />
            @else
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                <p class="text-sm text-gray-600 text-center">Please select a department first</p>
            </div>
            @endif
        </div>

        <x-slot name="actions">
            <x-button 
                label="Cancel" 
                wire:click="closeAssignModal" 
                class="btn-outline" 
            />
            <x-button 
                label="Assign Issue" 
                wire:click="assignIssue" 
                class="btn-primary shadow-lg shadow-purple-500/30" 
                spinner="assignIssue"
                icon="o-check"
            />
        </x-slot>
    </x-modal>

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
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    Comment History ({{ $issueComments->count() }})
                </h3>

                @forelse($issueComments as $comment)
                <div class="bg-white rounded-xl p-4 border {{ $comment->is_internal ? 'border-yellow-200 bg-yellow-50' : 'border-gray-200' }} shadow-sm">
                    @if($editingCommentId === $comment->id)
                        <!-- Edit Mode -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-semibold text-blue-900">Editing Comment</span>
                                <button wire:click="cancelEditComment" class="text-gray-500 hover:text-gray-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            
                            <x-textarea 
                                wire:model="editCommentText" 
                                rows="3"
                                placeholder="Edit your comment..."
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
                        <div class="flex items-start gap-3">
                            <!-- Avatar -->
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold shadow-md">
                                    {{ strtoupper(substr($comment->user_email ?? 'U', 0, 1)) }}
                                </div>
                            </div>

                            <!-- Comment Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-gray-900">
                                            {{ $comment->user_email ?? 'Unknown' }}
                                        </span>
                                        @if($comment->is_internal)
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 border border-yellow-300">
                                            Internal
                                        </span>
                                        @endif
                                        <span class="text-xs text-gray-500">
                                            {{ $comment->created_at->diffForHumans() }}
                                        </span>
                                        @if($comment->created_at != $comment->updated_at)
                                        <span class="text-xs text-gray-400 italic">
                                            (edited)
                                        </span>
                                        @endif
                                    </div>
                                    
                                    <!-- Action Buttons (only show if user owns the comment) -->
                                    @if($comment->user_email === auth()->user()->email)
                                    <div class="flex gap-1">
                                        <button 
                                            wire:click="startEditComment({{ $comment->id }})"
                                            class="p-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded transition-colors"
                                            title="Edit comment"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button 
                                            wire:click="deleteComment({{ $comment->id }})"
                                            wire:confirm="Are you sure you want to delete this comment?"
                                            class="p-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded transition-colors"
                                            title="Delete comment"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                    @endif
                                </div>
                                
                                <p class="text-sm text-gray-900 leading-relaxed whitespace-pre-wrap">{{ $comment->comment }}</p>
                                
                                <div class="text-xs text-gray-400 mt-2">
                                    {{ $comment->created_at->format('M d, Y \a\t g:i A') }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                @empty
                <div class="text-center py-8">
                    <div class="bg-gray-50 rounded-xl p-8 border border-gray-200">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <p class="text-gray-600 text-sm">No comments yet</p>
                        <p class="text-gray-500 text-xs mt-1">Be the first to add a comment!</p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        <x-slot name="actions">
            <x-button 
                label="Close" 
                wire:click="closeCommentsModal" 
                class="btn-outline" 
            />
        </x-slot>
    </x-modal>
</div>
