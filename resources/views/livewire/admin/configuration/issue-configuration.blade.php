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
    <div class="bg-white/80 backdrop-blur-sm shadow-sm border-b border-gray-200 mb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 mb-3 tracking-tight">Issue Management Configuration</h1>
                    <p class="text-gray-600">Manage issue groups and types for the ticket system</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <!-- Tabs -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex gap-2 px-6" aria-label="Tabs">
                    <button 
                        wire:click="$set('activeTab', 'groups')"
                        class="px-6 py-4 text-sm font-semibold border-b-2 transition-colors {{ $activeTab === 'groups' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                    >
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            Issue Groups
                        </div>
                    </button>
                    <button 
                        wire:click="$set('activeTab', 'types')"
                        class="px-6 py-4 text-sm font-semibold border-b-2 transition-colors {{ $activeTab === 'types' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                    >
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            Issue Types
                        </div>
                    </button>
                </nav>
            </div>

            <!-- Issue Groups Tab -->
            @if($activeTab === 'groups')
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Issue Groups</h2>
                        <p class="text-sm text-gray-600 mt-1">Manage categories for organizing issue tickets</p>
                    </div>
                    <x-button 
                        icon="o-plus" 
                        label="Add Group" 
                        wire:click="openGroupModal" 
                        class="btn-primary shadow-lg shadow-blue-500/30" 
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($issueGroups as $group)
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-200 p-5 hover:shadow-md transition-all">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-500 rounded-lg">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                                <h3 class="font-bold text-gray-900 text-lg">{{ $group->name }}</h3>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2 text-xs text-gray-600 mb-4">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            {{ $group->issuelogs->count() }} tickets
                        </div>

                        <div class="flex gap-2 pt-3 border-t border-blue-200">
                            <x-button 
                                icon="o-pencil" 
                                label="Edit" 
                                wire:click="editGroup({{ $group->id }})" 
                                class="btn-outline btn-sm flex-1"
                            />
                            <x-button 
                                icon="o-trash" 
                                label="Delete" 
                                wire:click="deleteGroup({{ $group->id }})" 
                                wire:confirm="Are you sure you want to delete this group?"
                                class="btn-outline btn-error btn-sm flex-1"
                            />
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">No Issue Groups</h3>
                        <p class="text-gray-500 mb-4">Create your first issue group to get started</p>
                        <x-button 
                            icon="o-plus" 
                            label="Add First Group" 
                            wire:click="openGroupModal" 
                            class="btn-primary"
                        />
                    </div>
                    @endforelse
                </div>
            </div>
            @endif

            <!-- Issue Types Tab -->
            @if($activeTab === 'types')
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Issue Types</h2>
                        <p class="text-sm text-gray-600 mt-1">Manage specific types of issues and link them to departments</p>
                    </div>
                    <x-button 
                        icon="o-plus" 
                        label="Add Type" 
                        wire:click="openTypeModal" 
                        class="btn-primary shadow-lg shadow-blue-500/30" 
                    />
                </div>

                <div class="overflow-x-auto rounded-xl border border-gray-200">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                                <th class="text-left py-4 px-4 font-semibold text-gray-700 text-sm uppercase tracking-wide">Type Name</th>
                                <th class="text-left py-4 px-4 font-semibold text-gray-700 text-sm uppercase tracking-wide">Linked Department</th>
                                <th class="text-center py-4 px-4 font-semibold text-gray-700 text-sm uppercase tracking-wide">Tickets</th>
                                <th class="text-right py-4 px-4 font-semibold text-gray-700 text-sm uppercase tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($issueTypes as $type)
                            <tr class="hover:bg-blue-50 transition-colors">
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="p-2 bg-purple-100 rounded-lg">
                                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                            </svg>
                                        </div>
                                        <span class="font-semibold text-gray-900">{{ $type->name }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    @if($type->department)
                                    <div class="flex items-center gap-2 bg-blue-50 px-3 py-1.5 rounded-lg inline-flex">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        <span class="text-sm font-medium text-blue-900">{{ $type->department->name }}</span>
                                    </div>
                                    @else
                                    <span class="text-sm text-gray-400">Not linked</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 bg-gray-100 rounded-full text-sm font-semibold text-gray-900">
                                        {{ $type->issuelogs->count() }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex justify-end gap-2">
                                        <x-button 
                                            icon="o-pencil" 
                                            wire:click="editType({{ $type->id }})" 
                                            class="btn-outline btn-sm"
                                        />
                                        <x-button 
                                            icon="o-trash" 
                                            wire:click="deleteType({{ $type->id }})" 
                                            wire:confirm="Are you sure you want to delete this type?"
                                            class="btn-outline btn-error btn-sm"
                                        />
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-12 text-center">
                                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">No Issue Types</h3>
                                    <p class="text-gray-500 mb-4">Create your first issue type to get started</p>
                                    <x-button 
                                        icon="o-plus" 
                                        label="Add First Type" 
                                        wire:click="openTypeModal" 
                                        class="btn-primary"
                                    />
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Issue Group Modal -->
    <x-modal wire:model="showGroupModal" title="{{ $groupId ? 'Edit Issue Group' : 'Add Issue Group' }}" separator box-class="max-w-lg">
      
        
        <div class="space-y-5">
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                <p class="text-sm text-blue-900">
                    Issue groups help categorize and organize different types of support tickets.
                </p>
            </div>

            <x-input 
                wire:model="groupName" 
                label="Group Name" 
                placeholder="e.g., Technical Support, Billing, General Inquiry"
                hint="Choose a clear, descriptive name"
                icon="o-rectangle-stack"
            />
        </div>

        <x-slot name="actions">
            <x-button 
                label="Cancel" 
                wire:click="closeGroupModal" 
                class="btn-outline" 
            />
            <x-button 
                label="{{ $groupId ? 'Update Group' : 'Create Group' }}" 
                wire:click="saveGroup" 
                class="btn-primary shadow-lg shadow-blue-500/30" 
                spinner="saveGroup"
                icon="o-check"
            />
        </x-slot>
    </x-modal>

    <!-- Issue Type Modal -->
    <x-modal wire:model="showTypeModal" title="{{ $typeId ? 'Edit Issue Type' : 'Add Issue Type' }}" separator box-class="max-w-lg">
       
        <div class="space-y-5">
            <div class="bg-purple-50 rounded-xl p-4 border border-purple-200">
                <p class="text-sm text-purple-900">
                    Issue types can be linked to specific departments for better routing and assignment.
                </p>
            </div>

            <x-input 
                wire:model="typeName" 
                label="Type Name" 
                placeholder="e.g., Network Issue, Password Reset, Hardware Request"
                hint="Be specific about the type of issue"
                icon="o-tag"
            />

            <x-select 
                wire:model="typeDepartmentId" 
                label="Link to Department (Optional)" 
                placeholder="Select a department" 
                :options="$departments" 
                option-label="name" 
                option-value="id"
                icon="o-building-office"
                hint="Link this issue type to a specific department for automatic routing"
            />
        </div>

        <x-slot name="actions">
            <x-button 
                label="Cancel" 
                wire:click="closeTypeModal" 
                class="btn-outline" 
            />
            <x-button 
                label="{{ $typeId ? 'Update Type' : 'Create Type' }}" 
                wire:click="saveType" 
                class="btn-primary shadow-lg shadow-purple-500/30" 
                spinner="saveType"
                icon="o-check"
            />
        </x-slot>
    </x-modal>
</div>
