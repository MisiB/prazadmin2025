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
                    <h1 class="text-3xl font-bold text-gray-900 mb-3 tracking-tight">
                        {{ $userDepartment ? $userDepartment->name : 'Department' }} Issues
                    </h1>
                    <p class="text-gray-600">
                        @if($isPrimaryUser)
                            Manage and assign department issues
                        @else
                            View department issue tickets
                        @endif
                    </p>
                </div>
                
                @if($isPrimaryUser)
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-2 rounded-full shadow-lg shadow-blue-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                        <span class="text-sm font-semibold">Department Head</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        @if($userDepartment)
            <!-- Status Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                <div class="bg-gradient-to-br from-gray-500 to-gray-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <p class="text-gray-100 text-sm font-semibold uppercase tracking-wide">Total</p>
                        <p class="text-4xl font-bold">{{ $statusCounts['total'] }}</p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <p class="text-blue-100 text-sm font-semibold uppercase tracking-wide">Open</p>
                        <p class="text-4xl font-bold">{{ $statusCounts['open'] }}</p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <p class="text-purple-100 text-sm font-semibold uppercase tracking-wide">In Progress</p>
                        <p class="text-4xl font-bold">{{ $statusCounts['in_progress'] }}</p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <p class="text-green-100 text-sm font-semibold uppercase tracking-wide">Resolved</p>
                        <p class="text-4xl font-bold">{{ $statusCounts['resolved'] }}</p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-gray-600 to-gray-700 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <p class="text-gray-100 text-sm font-semibold uppercase tracking-wide">Closed</p>
                        <p class="text-4xl font-bold">{{ $statusCounts['closed'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Filters and Grouping -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
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
                        placeholder="Filter Status"
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
                        placeholder="Filter Priority"
                    />

                    <x-select 
                        wire:model.live="filterType" 
                        :options="[['id' => '', 'name' => 'All Types'], ...$issueTypes->map(fn($t) => ['id' => $t->id, 'name' => $t->name])]" 
                        option-label="name" 
                        option-value="id"
                        placeholder="Filter Type"
                    />

                    @if($isPrimaryUser)
                    <x-select 
                        wire:model.live="groupBy" 
                        :options="[
                            ['id' => 'status', 'name' => 'Group by Status'],
                            ['id' => 'type', 'name' => 'Group by Type'],
                            ['id' => 'none', 'name' => 'No Grouping']
                        ]" 
                        option-label="name" 
                        option-value="id"
                        icon="o-squares-2x2"
                    />
                    @endif
                    
                    <x-button 
                        icon="o-arrow-path" 
                        label="Reset" 
                        wire:click="$set('search', ''); $set('filterStatus', ''); $set('filterPriority', ''); $set('filterType', '')" 
                        class="btn-outline"
                    />
                </div>
            </div>

            <!-- Grouped Issues Display -->
            @if($groupedIssues->count() > 0)
                @foreach($groupedIssues as $groupKey => $issues)
                    <div class="mb-6">
                        <!-- Group Header -->
                        <div class="bg-white rounded-t-2xl shadow-sm border-x border-t border-gray-200 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    @if($groupBy === 'status')
                                        <div class="p-2 {{ $groupKey == 'open' ? 'bg-blue-100' : ($groupKey == 'in_progress' ? 'bg-purple-100' : ($groupKey == 'resolved' ? 'bg-green-100' : 'bg-gray-100')) }} rounded-lg">
                                            <svg class="w-6 h-6 {{ $groupKey == 'open' ? 'text-blue-600' : ($groupKey == 'in_progress' ? 'text-purple-600' : ($groupKey == 'resolved' ? 'text-green-600' : 'text-gray-600')) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <h2 class="text-2xl font-bold text-gray-900">{{ ucfirst(str_replace('_', ' ', $groupKey)) }}</h2>
                                    @elseif($groupBy === 'type')
                                        <div class="p-2 bg-purple-100 rounded-lg">
                                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                            </svg>
                                        </div>
                                        <h2 class="text-2xl font-bold text-gray-900">{{ $issues->first()->issuetype->name ?? 'Untyped' }}</h2>
                                    @else
                                        <div class="p-2 bg-blue-100 rounded-lg">
                                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                            </svg>
                                        </div>
                                        <h2 class="text-2xl font-bold text-gray-900">All Issues</h2>
                                    @endif
                                    
                                    <span class="px-3 py-1 bg-gray-100 text-gray-900 rounded-full text-sm font-semibold">
                                        {{ $issues->count() }} {{ $issues->count() == 1 ? 'ticket' : 'tickets' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Issues in this group -->
                        <div class="bg-white rounded-b-2xl shadow-sm border-x border-b border-gray-200 p-6">
                            <div class="space-y-4">
                                @foreach($issues as $issue)
                                    <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition-all duration-300">
                                        <!-- Issue Header -->
                                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-5 py-3 border-b border-gray-200">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="p-2 bg-gradient-to-br {{ $issue->priority == 'High' ? 'from-red-500 to-red-600' : ($issue->priority == 'Medium' ? 'from-yellow-500 to-yellow-600' : 'from-green-500 to-green-600') }} rounded-lg shadow-lg">
                                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                        </svg>
                                                    </div>
<div>
                                                        <h3 class="text-lg font-bold text-gray-900">{{ $issue->title }}</h3>
                                                        <div class="flex items-center gap-2 mt-0.5">
                                                            <span class="text-xs text-gray-600 font-mono">{{ $issue->ticketnumber }}</span>
                                                            <span class="text-xs text-gray-400">•</span>
                                                            <span class="text-xs text-gray-600">{{ $issue->created_at->diffForHumans() }}</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $issue->priority == 'High' ? 'bg-red-100 text-red-800' : ($issue->priority == 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                                        {{ $issue->priority }}
                                                    </span>
                                                    @if($groupBy != 'status')
                                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $issue->status == 'open' ? 'bg-blue-100 text-blue-800' : ($issue->status == 'in_progress' ? 'bg-purple-100 text-purple-800' : ($issue->status == 'resolved' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) }}">
                                                        {{ ucfirst(str_replace('_', ' ', $issue->status)) }}
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Issue Content -->
                                        <div class="p-5">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                                                <div class="space-y-2">
                                                    <div class="flex items-center gap-2 text-sm">
                                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                        </svg>
                                                        <span class="text-gray-600">Reporter:</span>
                                                        <span class="font-semibold text-gray-900">{{ $issue->name }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-2 text-sm">
                                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                        </svg>
                                                        <span class="text-gray-600">Email:</span>
                                                        <span class="font-semibold text-gray-900">{{ $issue->email }}</span>
                                                    </div>
                                                    @if($groupBy != 'type')
                                                    <div class="flex items-center gap-2 text-sm">
                                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                        </svg>
                                                        <span class="text-gray-600">Type:</span>
                                                        <span class="font-semibold text-gray-900">{{ $issue->issuetype->name ?? 'N/A' }}</span>
                                                    </div>
                                                    @endif
                                                </div>

                                                <div class="space-y-2">
                                                    @if($issue->assigned_to)
                                                    <div class="flex items-center gap-2 text-sm bg-indigo-50 px-3 py-1.5 rounded-lg">
                                                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                        </svg>
                                                        <span class="text-gray-600">Assigned to:</span>
                                                        <span class="font-semibold text-indigo-900">{{ $issue->assignedto->name ?? 'N/A' }} {{ $issue->assignedto->surname ?? '' }}</span>
                                                    </div>
                                                    @else
                                                    <div class="flex items-center gap-2 text-sm bg-yellow-50 px-3 py-1.5 rounded-lg">
                                                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                        </svg>
                                                        <span class="font-semibold text-yellow-900">Unassigned</span>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="bg-gray-50 rounded-lg p-3 mb-3">
                                                <p class="text-sm text-gray-900 leading-relaxed">{{ Str::limit($issue->description, 200) }}</p>
                                            </div>

                                            <!-- Attachments -->
                                            @if($issue->attachments && count($issue->attachments) > 0)
                                            <div class="mb-3">
                                                <div class="flex items-center gap-2 text-xs text-purple-700 mb-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                                    </svg>
                                                    {{ count($issue->attachments) }} attachment(s)
                                                </div>
                                                <div class="flex gap-2">
                                                    @foreach(array_slice($issue->attachments, 0, 4) as $attachment)
                                                        @if(is_array($attachment) && isset($attachment['path']))
                                                        <a href="{{ Storage::url($attachment['path']) }}" target="_blank">
                                                            <img src="{{ Storage::url($attachment['path']) }}" alt="{{ $attachment['original_name'] ?? 'Attachment' }}" class="w-16 h-16 object-cover rounded border border-purple-200 hover:border-purple-400 transition-all">
                                                        </a>
                                                        @elseif(is_string($attachment))
                                                        <a href="{{ Storage::url($attachment) }}" target="_blank">
                                                            <img src="{{ Storage::url($attachment) }}" alt="Attachment" class="w-16 h-16 object-cover rounded border border-purple-200 hover:border-purple-400 transition-all">
                                                        </a>
                                                        @endif
                                                    @endforeach
                                                    @if(count($issue->attachments) > 4)
                                                    <div class="w-16 h-16 bg-purple-100 rounded flex items-center justify-center text-purple-700 font-bold text-xs">
                                                        +{{ count($issue->attachments) - 4 }}
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                            @endif

                                            <!-- Actions -->
                                            <div class="flex items-center gap-2 pt-3 border-t border-gray-200 flex-wrap">
                                                @if($isPrimaryUser)
                                                    @if(!$issue->assigned_to)
                                                        <x-button 
                                                            icon="o-user-plus" 
                                                            label="Assign user" 
                                                            wire:click="openAssignModal({{ $issue->id }}, false)" 
                                                            class="btn-outline btn-purple btn-sm"
                                                        />
                                                        @if($userDepartment->name === 'ICT')
                                                            <x-button 
                                                                icon="o-user-plus" 
                                                                label="Assign consultant"  
                                                                wire:click="consultantAssigningTrigger({{ $issue->id }})" 
                                                                class="btn-outline btn-purple btn-sm"
                                                            />
                                                        @endif
                                                    @else
                                                    <x-button 
                                                        icon="o-arrow-path-rounded-square" 
                                                        label="Reassign" 
                                                        wire:click="reassignIssue({{ $issue->id }})" 
                                                        class="btn-outline btn-purple btn-sm"
                                                    />
                                                    @endif
                                                @else
                                                <!--The claim button allows users to claim unassigned tickets-->
                                                    @if(!$issue->assigned_to)
                                                        <x-button 
                                                            icon="o-user-plus" 
                                                            label="Claim ticket"  
                                                            wire:click="claimIssue({{ $issue->id }})" 
                                                            class="btn-outline btn-purple btn-sm"
                                                        />                                                
                                                        <!--The assign consultant button allows users to assign tickets to a consultant-->
                                                        @if($userDepartment->name === 'ICT')
                                                            <x-button 
                                                                icon="o-user-plus" 
                                                                label="Assign consultant"  
                                                                wire:click="consultantAssigningTrigger({{ $issue->id }})" 
                                                                class="btn-outline btn-purple btn-sm"
                                                            />
                                                        @endif
                                                    @endif
                                                @endif

                                                @if($issue->status != 'closed')
                                                    @if($issue->status == 'open' && $issue->assigned_to == auth()->id())
                                                    <x-button 
                                                        icon="o-play" 
                                                        label="Start" 
                                                        wire:click="updateStatus({{ $issue->id }}, 'in_progress')" 
                                                        wire:confirm="Mark as in progress?"
                                                        class="btn-outline btn-info btn-sm"
                                                    />
                                                    @endif

                                                    @if($issue->status == 'in_progress')
                                                    <x-button 
                                                        icon="o-check-circle" 
                                                        label="Resolve" 
                                                        wire:click="updateStatus({{ $issue->id }}, 'resolved')" 
                                                        wire:confirm="Mark as resolved? Email will be sent."
                                                        class="btn-outline btn-success btn-sm"
                                                    />
                                                    @endif

                                                    @if($issue->status == 'resolved')
                                                    <x-button 
                                                        icon="o-lock-closed" 
                                                        label="Close" 
                                                        wire:click="updateStatus({{ $issue->id }}, 'closed')" 
                                                        wire:confirm="Close permanently?"
                                                        class="btn-outline btn-warning btn-sm"
                                                    />
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-16">
                    <div class="bg-gray-50 rounded-2xl p-12 border border-gray-200 inline-block">
                        <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">No Department Issues</h3>
                        <p class="text-gray-600">No issues have been assigned to your department yet</p>
                    </div>
                </div>
            @endif
        @else
            <div class="text-center py-16">
                <div class="bg-yellow-50 rounded-2xl p-12 border border-yellow-200 inline-block">
                    <svg class="w-20 h-20 mx-auto text-yellow-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">No Department Assigned</h3>
                    <p class="text-gray-600">You are not assigned to any department</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Assign/Reassign Issue Modal -->
    @if($isPrimaryUser)
    <x-modal wire:model="showAssignModal" title="{{ $isReassigning ? 'Reassign to Team Member' : 'Assign to Team Member' }}" separator box-class="max-w-lg">
       
        
        <div class="space-y-5">
            <div class="bg-purple-50 rounded-xl p-4 border border-purple-200">
                <p class="text-sm text-purple-900">
                    @if($isReassigning)
                        Change the assignment to a different team member in your department.
                    @else
                        Assign this issue to a team member in your department.
                    @endif
                </p>
            </div>

            <x-select 
                wire:model="selectedUser" 
                label="Select Team Member" 
                placeholder="Choose a user" 
                :options="$departmentUsers->map(fn($u) => ['id' => $u->id, 'name' => $u->name . ' ' . $u->surname . ($u->id == $selectedUser ? ' (Current)' : '')])" 
                option-label="name" 
                option-value="id"
                icon="o-user"
                hint="{{ $isReassigning ? 'Select new assignee' : 'Select who will handle this issue' }}"
            />
        </div>

        <x-slot name="actions">
            <x-button 
                label="Cancel" 
                wire:click="closeAssignModal" 
                class="btn-outline" 
            />
            <x-button 
                label="{{ $isReassigning ? 'Reassign Issue' : 'Assign Issue' }}" 
                wire:click="assignIssue" 
                class="btn-primary shadow-lg shadow-purple-500/30" 
                spinner="assignIssue"
                icon="o-check"
            />
        </x-slot>
    </x-modal>
    @endif

    
    <!-- Assign/Reassign Issue Modal To Consultant-->
    <x-modal wire:model="consultantAssignModal" title="{{ $isReassigning ? 'Reassign to Consultant' : 'Assign to Consultant' }}" separator box-class="max-w-lg">
       
        <div class="space-y-5">
            <div class="bg-purple-50 rounded-xl p-4 border border-purple-200">
                <p class="text-sm text-purple-900">
                    @if($isReassigning)
                        Change the assignment to a different consultant.
                    @else
                        Assign this issue to a consultant of your choice.
                    @endif
                </p>
            </div>

            <x-select 
                wire:model="selectedconsultantUser" 
                label="Select Consultant" 
                placeholder="Choose a consultant" 
                :options="$consultantsOptions" 
                option-label="name" 
                option-value="id"
                placeholder="Select a consultant"
                placeholder-value="0"
                icon="o-user"
                hint="{{ $isReassigning ? 'Select new assignee' : 'Select the consultant who will handle this issue' }}"
            />
        </div>

        <x-slot name="actions">
            <x-button 
                label="Cancel" 
                wire:click="closeAssignModal" 
                class="btn-outline" 
            />
            <x-button 
                label="{{ $isReassigning ? 'Reassign Issue' : 'Assign Issue' }}" 
                wire:click="consultantIssue" 
                class="btn-primary shadow-lg shadow-purple-500/30" 
                spinner="consultantIssue"
                icon="o-check"
            />
        </x-slot>
    </x-modal>
</div>
