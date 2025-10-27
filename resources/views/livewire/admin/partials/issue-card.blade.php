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
                    @if(is_array($attachment) && isset($attachment['path']))
                    <a 
                        href="{{ Storage::url($attachment['path']) }}" 
                        target="_blank"
                        class="group relative block"
                    >
                        <img 
                            src="{{ Storage::url($attachment['path']) }}" 
                            alt="{{ $attachment['original_name'] ?? 'Attachment' }}" 
                            class="w-full h-24 object-cover rounded-lg border border-purple-200 group-hover:border-purple-400 transition-all"
                        />
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                            </svg>
                        </div>
                    </a>
                    @elseif(is_string($attachment))
                    <a 
                        href="{{ Storage::url($attachment) }}" 
                        target="_blank"
                        class="group relative block"
                    >
                        <img 
                            src="{{ Storage::url($attachment) }}" 
                            alt="Attachment" 
                            class="w-full h-24 object-cover rounded-lg border border-purple-200 group-hover:border-purple-400 transition-all"
                        />
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                            </svg>
                        </div>
                    </a>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="flex items-center gap-2 pt-4 border-t border-gray-200 flex-wrap">
            @if($issue->status != 'closed')
            
            @if($showEdit ?? false)
            <x-button 
                icon="o-pencil" 
                label="Edit" 
                wire:click="edit({{ $issue->id }})" 
                class="btn-outline btn-sm"
            />
            @endif
            
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
</div>

