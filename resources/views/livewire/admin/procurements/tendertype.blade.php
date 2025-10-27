<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />
    <x-card title="Tender Types" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-button class="btn-outline" label="New Tender Type" icon="o-plus" @click="$wire.modal = true" />
        </x-slot:menu>
        <x-table :headers="$headers" :rows="$types">
            @scope('actions', $type)
            <div class="flex space-x-2">
                <x-button icon="o-pencil" wire:click="edit({{ $type->id }})" spinner class="text-blue-500 btn-outline btn-sm" />
                <x-button icon="o-trash" wire:click="delete({{ $type->id }})" wire:confirm="Are you sure?" spinner class="text-red-500 btn-outline btn-sm" />
            </div>
            @endscope
            <x-slot:empty>
                <x-alert class="alert-error" title="No tender types found." />
            </x-slot:empty>
        </x-table>
    </x-card>
    <x-modal wire:model="modal" title="{{ $id ? 'Edit Tender Type' : 'New Tender Type' }}" separator>
        <x-form wire:submit="save">
            <div class="grid gap-2">
                <x-input label="Name" wire:model="name" />
            </div>
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.modal = false" />
                <x-button label="Save" class="btn-primary"  type="submit" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
