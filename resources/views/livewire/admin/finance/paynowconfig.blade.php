<div>
   
    <x-card title="Paynow configurations" separator class="mt-5 border-2 border-gray-200" separator>
        <x-slot:menu>
            <x-button label="New Config" responsive icon="o-plus" class="btn-outline" @click="$wire.modal = true" />
        </x-slot:menu>

        <x-table :headers="$headers" :rows="$rows">
            @scope('cell_status', $row)
                @if($row->status=="active")
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
                <x-alert class="alert-error" title="No paynow integrations found." />
            </x-slot:empty>
        </x-table>
    </x-card>
 
    <x-modal wire:model="modal"  title="{{ $id ? 'Edit Inventory' : 'New Inventory' }}" persistent> 
        <x-form wire:submit="{{ $id ? 'update' : 'save' }}">
            <div class="grid gap-2">
                <x-input label="Key" wire:model="key" />
                <x-input label="Token" wire:model="token" />
                <x-select label="Type" wire:model="type" placeholder="Select a type" :options="[ ['id'=>'NONREFUNDABLE','name'=>'NONREFUNDABLE'], ['id'=>'REFUNDABLE','name'=>'REFUNDABLE']]" />
                <x-select label="Currency" wire:model="currencyid" placeholder="Select a currency" :options="$currencyMap" />
                <x-select label="Bank account" wire:model="bankaccountid" placeholder="Select a status" :options="$bankMap" />
            </div>
            <x-slot:actions>
                <x-button label="Close" wire:click="closeModal" />
                <x-button label="{{ $id ? 'Update' : 'Save' }}" type="submit" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
