<div>
    <div class="text-sm breadcrumbs">
        <ul class="flex">
            <li>
                <x-button label="Home" link="{{ route('admin.home') }}" class="rounded-none btn-ghost" icon="o-home"/>
            </li>
            <li><x-button class="rounded-none border-l-2 border-l-gray-200 btn-ghost"  label="Workshops"/></li>
        </ul>
    </div>

    <x-card title="Workshops" separator progress-indicator class="mt-4">
        <x-slot:menu>
            <x-button label="Create Workshop" icon="o-plus" wire:click="$set('showCreateModal', true)" class="btn-primary"/>
 
        </x-slot:menu>

        @if (session()->has('message'))
            <div class="mb-4 alert alert-success">
                {{ session('message') }}
            </div>
        @endif

        <x-table :headers="$headers" :rows="$workshops" separator progress-indicator show-empty-text empty-text="Nothing Here!">
            @scope('cell_action', $row)
                <div class="flex gap-2">
                    <x-button label="View" link="{{ route('admin.workshop.view', $row->id) }}" class="btn btn-xs btn-info"/>
                    <x-button label="Edit" wire:click="editWorkshop({{ $row->id }})" class="btn btn-xs btn-primary"/>
                        @if($row->document_url)
                        <x-button label="Preview" wire:click="previewDocument('{{ $row->document_url }}')" class="btn btn-xs btn-success"/>
                    @endif
                    <x-button label="Delete" class="text-white bg-red-500" wire:click="deleteWorkshop({{ $row->id }})" 
                        wire:confirm="Are you sure you want to delete this workshop?"
                        class="btn btn-xs btn-error"/>
                </div>
            @endscope
        </x-table>
    </x-card>

    <!-- Create Workshop Modal -->
    <x-modal wire:model="showCreateModal" title="Create Workshop">
        <div class="grid grid-cols-2 gap-4">
            <x-input label="Title" wire:model="title"/>
            <x-select label="Target" wire:model="target" placeholder="select target" :options="$targetlist"/>
         
        </div>
        <div class="grid grid-cols-2 gap-4">    
            <x-input type="date" label="Start Date" wire:model="startDate"/>
            <x-input type="date" label="End Date" wire:model="endDate"/>
        </div>
        <div class="grid grid-cols-2 gap-4">    
            <x-input label="Location" wire:model="location"/>
            <x-input type="number" label="Limit" wire:model="limit"/>
        </div>
        <div class="grid grid-cols-3 gap-4">    
            <x-select label="Status" wire:model="status" placeholder="select status" option-label="name" option-value="id" :options="$statuslist"/>
            <x-select label="Currency" wire:model="currency" placeholder="select currency" option-label="name" option-value="id" :options="$currencies"/>
            <x-input type="number" label="Cost" wire:model="cost" step="0.01"/>
        </div>

        <div class="mt-4">
            <x-input type="file" label="Workshop Document" wire:model="document" accept=".pdf,.doc,.docx"/>
            <div class="text-sm text-gray-500 mt-1">Accepted file types: PDF, DOC, DOCX (Max: 10MB)</div>
        </div>

        <x-slot:actions>
                <x-button label="Cancel" wire:click="$set('showCreateModal', false)"/>
                <x-button label="Create" wire:click="createWorkshop" class="btn-primary"/>
            
        </x-slot>
    </x-modal>

    <!-- Edit Workshop Modal -->
    <x-modal wire:model="showEditModal" title="Edit Workshop">
        <div class="grid grid-cols-2 gap-4">
            <x-input label="Title" wire:model="title"/>
            <x-select label="Target" wire:model="target" placeholder="select target" :options="$targetlist"/>
         
        </div>
        <div class="grid grid-cols-2 gap-4">    
            <x-input type="date" label="Start Date" wire:model="start_date"/>
            <x-input type="date" label="End Date" wire:model="end_date"/>
        </div>
        <div class="grid grid-cols-2 gap-4">    
            <x-input label="Location" wire:model="location"/>
            <x-input type="number" label="Limit" wire:model="limit"/>
        </div>
        <div class="grid grid-cols-3 gap-4">    
            <x-select label="Status" wire:model="status" placeholder="select status" option-label="name" option-value="id" :options="$statuslist"/>
            <x-select label="Currency" wire:model="currency" placeholder="select currency" option-label="name" option-value="id" :options="$currencies"/>
            <x-input type="number" label="Cost" wire:model="cost" step="0.01"/>
        </div>

        <div class="mt-4">
            <x-input type="file" label="Workshop Document" wire:model="editDocument" accept=".pdf,.doc,.docx"/>
            <div class="text-sm text-gray-500 mt-1">Accepted file types: PDF, DOC, DOCX (Max: 10MB)</div>
            @if($editingWorkshop && $editingWorkshop->document_url)
                <div class="mt-2">
                    <span class="text-sm">Current document:</span>
                    <x-button wire:click="previewDocument('{{ $editingWorkshop->document_url }}')" 
                             label="Preview Current Document" class="btn-sm btn-link"/>
                </div>
            @endif
        </div>

        <x-slot:actions>
                <x-button label="Cancel" wire:click="$set('showEditModal', false)"/>
                <x-button label="Update" wire:click="updateWorkshop" class="btn-primary"/>
            
        </x-slot>
    </x-modal>

    <!-- Document Preview Modal -->
    <x-modal wire:model="showPreviewModal" title="Document Preview" max-width="6xl">
        <div class="w-full h-[80vh]">
            @if($previewUrl)
                <iframe src="{{ $previewUrl }}" class="w-full h-full border-0" frameborder="0"></iframe>
            @else
                <div class="flex items-center justify-center h-full text-gray-500">
                    No document available for preview
                </div>
            @endif
        </div>
        <x-slot:actions>
            <div class="flex justify-between w-full">
                <x-button label="Close" wire:click="$set('showPreviewModal', false)"/>
                <x-button label="Download" link="{{ $previewUrl }}" target="_blank" class="btn-primary"/>
            </div>
        </x-slot>
    </x-modal>
</div>
