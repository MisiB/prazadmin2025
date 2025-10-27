<div>
   
    <x-card title="Currencies" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-button label="New Currency" responsive icon="o-plus" class="btn-outline" @click="$wire.modal = true" />
        </x-slot:menu>

        <x-table :headers="$headers" :rows="$rows">
            @scope('cell_status', $row)
                @if($row->status=="ACTIVE")
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-error">Inactive</span>
                @endif
            @endscope
            @scope('actions', $row)
            <div class="flex items-center space-x-2">
                <x-button icon="o-pencil" class="btn-sm btn-info btn-outline" 
                    wire:click="edit({{ $row->id }})" spinner />
                <x-button icon="o-trash" class="btn-sm btn-outline btn-error" 
                    wire:click="delete({{ $row->id }})" wire:confirm="Are you sure?" spinner />
            </div>
            @endscope
            <x-slot:empty>
                <x-alert class="alert-error" title="No currencies found." />
            </x-slot:empty>
        </x-table>
    </x-card>

    <x-modal wire:model="modal"  title="{{ $id ? 'Edit Currency' : 'New Currency' }}" persistent>
        <x-form wire:submit="{{ $id ? 'update' : 'save' }}">
            <div class="grid gap-2">
                <x-input label="Name" wire:model="name" />
                <x-select label="Status" wire:model="status" placeholder="Select a status" :options="[['id'=>'ACTIVE', 'name' => 'Active'], ['id'=>'inactive', 'name' => 'Inactive']]" />
           </div>
            <x-slot:actions>
                <x-button label="Close" wire:click="closeModal" />
                <x-button label="{{ $id ? 'Update' : 'Save' }}" type="submit" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
