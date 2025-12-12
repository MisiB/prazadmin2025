<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50">
    <!-- Modern Breadcrumbs -->
    <div class="bg-white/80 backdrop-blur-sm border-b border-gray-200 px-4 py-3">
        <div class="max-w-7xl mx-auto">
            <x-breadcrumbs :items="$breadcrumbs" 
                class="bg-gray-50 p-3 rounded-xl overflow-x-auto whitespace-nowrap"
                link-item-class="text-base hover:text-blue-600 transition-colors" />
        </div>
    </div>

    <!-- Modern Header -->
    <div class="bg-white/80 backdrop-blur-sm shadow-sm border-b border-gray-200 mb-6 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 mb-3 tracking-tight">My Issues</h1>
                    <p class="text-gray-600">View and manage your personal issue tickets</p>
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
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Created Issues Summary -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg shadow-blue-500/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Issues I Created</h3>
                        <p class="text-sm text-gray-600">Tickets you've submitted</p>
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-3">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900">{{ $createdCounts['total'] }}</div>
                        <div class="text-xs text-gray-600">Total</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ $createdCounts['open'] }}</div>
                        <div class="text-xs text-gray-600">Open</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600">{{ $createdCounts['in_progress'] }}</div>
                        <div class="text-xs text-gray-600">Progress</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $createdCounts['resolved'] }}</div>
                        <div class="text-xs text-gray-600">Resolved</div>
                    </div>
                </div>
            </div>

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
                        <div class="text-2xl font-bold text-gray-900">{{ $assignedCounts['total'] }}</div>
                        <div class="text-xs text-gray-600">Total</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ $assignedCounts['open'] }}</div>
                        <div class="text-xs text-gray-600">Open</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600">{{ $assignedCounts['in_progress'] }}</div>
                        <div class="text-xs text-gray-600">Progress</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $assignedCounts['resolved'] }}</div>
                        <div class="text-xs text-gray-600">Resolved</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
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
                    label="Reset" 
                    wire:click="$set('search', ''); $set('filterStatus', ''); $set('filterPriority', '')" 
                    class="btn-outline"
                />
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex gap-2 px-6" aria-label="Tabs">
                    <button 
                        wire:click="$set('activeTab', 'created')"
                        class="px-6 py-4 text-sm font-semibold border-b-2 transition-colors {{ $activeTab === 'created' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                    >
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                            Issues I Created
                            <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-xs font-bold">{{ $createdCounts['total'] }}</span>
                        </div>
                    </button>
                    <button 
                        wire:click="$set('activeTab', 'assigned')"
                        class="px-6 py-4 text-sm font-semibold border-b-2 transition-colors {{ $activeTab === 'assigned' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                    >
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            Assigned to Me
                            <span class="px-2 py-0.5 bg-purple-100 text-purple-800 rounded-full text-xs font-bold">{{ $assignedCounts['total'] }}</span>
                        </div>
                    </button>
                </nav>
            </div>

            <!-- Issues I Created Tab -->
            @if($activeTab === 'created')
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($createdIssues as $issue)
                        @include('livewire.admin.partials.issue-card', ['issue' => $issue, 'showEdit' => true])
                    @empty
                        <div class="text-center py-16">
                            <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">No Tickets Created</h3>
                            <p class="text-gray-600 mb-4">You haven't created any support tickets yet</p>
                            <x-button 
                                icon="o-plus" 
                                label="Create Your First Ticket" 
                                wire:click="openModal" 
                                class="btn-primary"
                            />
                        </div>
                    @endforelse
                </div>
            </div>
            @endif

            <!-- Assigned to Me Tab -->
            @if($activeTab === 'assigned')
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($assignedIssues as $issue)
                        @include('livewire.admin.partials.issue-card', ['issue' => $issue, 'showEdit' => false])
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
            @endif
        </div>
    </div>

    <!-- Assign Issue Modal -->
    <x-modal wire:model="showAssignModal" title="Assign Issue to Department" separator box-class="max-w-2xl">
       
        
        <div class="space-y-5">
            <div class="bg-purple-50 rounded-xl p-4 border border-purple-200">
                <p class="text-sm text-purple-900">
                    Assign this issue to a department to handle it.
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
    <!-- Create/Edit Ticket Modal (Same as Issues page) -->
    @include('livewire.admin.partials.issue-form-modal')

</div>
