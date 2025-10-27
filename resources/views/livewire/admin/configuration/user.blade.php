<div>
<x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />

    <x-card title="User Details" class="mt-5 border-2 border-gray-200" separator>
    <x-form wire:submit="update">
        <div class="grid grid-cols-2 gap-2">
            <x-input label="Name" wire:model="name" />
            <x-input label="Email" wire:model="email" />
        </div>
        <div class="grid grid-cols-2 gap-2">
            <x-input label="Phone" wire:model="phonenumber" />
            <x-select label="Status" wire:model="status" :options="[['id' => 'Active', 'name' => 'Active'], ['id' => 'Inactive', 'name' => 'Inactive']]" option-label="name" option-value="id" placeholder="Select Status" />
        </div>
    </x-form>
    </x-card>
</div>