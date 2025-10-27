<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />
    <x-card title="Account Types" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-button label="New Account Type" responsive icon="o-plus" class="btn-outline" @click="$wire.modal = true" />
        </x-slot:menu>

        <x-table :headers="$headers" :rows="$accounttypes">
            @scope('cell_icon', $accounttype)
                <x-icon name="{{ $accounttype->icon }}" class="w-5 h-5" />
            @endscope
            @scope('cell_status', $accounttype)
                @if($accounttype->status==1)
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-error">Inactive</span>
                @endif
            @endscope
            @scope('actions', $accounttype)
            <div class="flex items-center space-x-2">
                <x-button icon="o-pencil" class="btn-sm btn-info btn-outline" 
                    wire:click="edit({{ $accounttype->id }})" spinner />
                <x-button icon="o-trash" class="btn-sm btn-outline btn-error" 
                    wire:click="delete({{ $accounttype->id }})" confirm="Are you sure?" spinner />
            </div>
            @endscope
            <x-slot:empty>
                <x-alert class="alert-error" title="No account types found." />
            </x-slot:empty>
        </x-table>
    </x-card>

    <x-modal wire:model="modal"  title="{{ $id ? 'Edit Account Type' : 'New Account Type' }}">
        <x-form wire:submit="{{ $id ? 'update' : 'save' }}">
            <div class="grid gap-2 lg:grid-cols-3">
            <x-input label="Name" wire:model="name" />
            <x-select label="Status" wire:model="status" placeholder="Select a status" :options="[['id'=>'1', 'name' => 'Active'], ['id'=>'0', 'name' => 'Inactive']]" />
                <x-input label="Icon" wire:model="icon" />
            </div>
            <div class="grid gap-2">
            <x-textarea label="Description" wire:model="description" />
            </div>
         

            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.modal = false" />
                <x-button label="{{ $id ? 'Update' : 'Save' }}" type="submit" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
