<div>
    <x-breadcrumbs :items="$breadcrumbs"
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />

    <x-card title="Leave Types" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-button label="New Leave Type" responsive icon="o-plus" class="btn-outline" @click="$wire.modal = true" />
        </x-slot:menu>

        <x-table :headers="$headers" :rows="$leavetypes">

            @scope('cell_rollover', $leavetype)
                @if($leavetype->rollover=='Y')
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-error">Inactive</span>
                @endif
            @endscope
            @scope('actions', $leavetype)
            <div class="flex items-center space-x-2">
                <x-button icon="o-pencil" class="btn-sm btn-info btn-outline" 
                    wire:click="edit({{$leavetype->id}})" spinner />
                <x-button icon="o-trash" class="btn-sm btn-outline btn-error" 
                    wire:click="delete({{ $leavetype->id }})" confirm="Are you sure?" spinner />
            </div>
            @endscope
            <x-slot:empty>
                <x-alert class="alert-error" title="No leave types found." />
            </x-slot:empty>
        </x-table>
    </x-card>
 
    <x-modal wire:model="modal"  title="{{ $id ? 'Edit Leave Type' : 'New Leave Type' }}">
        <x-form wire:submit="save">
            <div class="grid gap-2 ">
                <x-input label="Name" wire:model="name" />
                <x-input label="Accumulation" wire:model="accumulation" />
                <x-input label="Ceiling" wire:model="ceiling" />
                <x-select label="Rollover" wire:model="rollover" placeholder="Select rollover" :options="[['id'=>'Y', 'name' => 'Yes'], ['id'=>'N', 'name' => 'No']]" />              
            </div>
        
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.modal = false" />
                <x-button label="{{ $id ? 'Update' : 'Save' }}" type="submit" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
 