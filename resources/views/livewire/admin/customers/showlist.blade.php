<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />

<x-card class="mt-5 border-2 border-gray-200" title="Customers" separator progress-indicator>
    <x-slot:menu>
        <x-input wire:model.live.debounce.500ms="search" placeholder="Search customers..." />
        <x-button class="btn-outline" label="New Customer" icon="o-plus" @click="$wire.modal = true" />
    </x-slot:menu>

        <x-table :headers="$headers" :rows="$customers">
            @scope('actions', $customer)
            <div class="flex items-center space-x-2">
                <x-button icon="o-eye" class="btn-sm btn-success btn-outline" link="{{ route('admin.customers.show',$customer->id) }}" />
                <x-button icon="o-pencil" class="btn-sm btn-info btn-outline" 
                    wire:click="edit({{ $customer->id }})" spinner />
                <x-button icon="o-trash" class="btn-sm btn-outline btn-error" 
                    wire:click="delete({{ $customer->id }})" wire:confirm="Are you sure?" spinner />
            </div>
            @endscope
            <x-slot:empty>
                <x-alert class="alert-error" title="No customers found." />
            </x-slot:empty>
        </x-table>

</x-card>
<x-modal title="{{ $id ? 'Edit Customer' : 'New Customer' }}" wire:model="modal">
    <x-form wire:submit="save">
        <div class="grid grid-cols-2 gap-2">
            <x-input label="Name" wire:model="name" />
            <x-input label="Reg Number" readonly wire:model="regnumber" />
            <x-select label="Type" wire:model.live="type" placeholder="Select Customer Type" :options="$customertypelist" />
            <x-input label="Country" wire:model="country" />
            <x-input label="Default Email" wire:model="default_email" />
        </div>
        <x-slot:actions>
            <x-button label="Close" wire:click="$wire.modal = false" />
            <x-button label="Save" type="submit" class="btn-primary" spinner="update" />
        </x-slot:actions>
    </x-form>
</x-modal>
</div>
