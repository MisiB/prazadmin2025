<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />
    <x-card title="Workflows" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-button icon="o-plus" class="btn-primary" label="New workflow" @click="$wire.modal=true"/>
        </x-slot:menu>
        <x-table :headers="$headers" :rows="$workflows">
            <x-slot:empty>
                <x-alert class="alert-error" title="No workflows found." />
            </x-slot:empty>
            @scope("actions", $row)
                <div class="flex items-center space-x-2">
                    <x-button icon="o-pencil" class="btn-outline btn-xs btn-primary" wire:click="edit({{ $row->id }})" />
                    <x-button icon="o-trash" class="btn-outline btn-xs btn-error" wire:click="delete({{ $row->id }})" wire:confirm="Are you sure?" />
                    <livewire:admin.workflows.configurations.components.parameters :workflow="$row"/>
                </div>
            @endscope
        </x-table>
    </x-card>
    <x-modal wire:model="modal" title="{{ $id ? 'Edit workflow' : 'New workflow' }}" separator>
        <x-form wire:submit="save">
            
            <x-input wire:model="name" label="Name"/>
            <x-input wire:model="description" label="Description"/>
        
        <x-slot:actions>
            <x-button class="btn-primary" label="Save" type="submit" spinner="save"/>
        </x-slot:actions>
        </x-form>
    </x-modal>
</div>
