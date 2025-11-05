<div>
    <x-button icon="o-plus" class="btn-primary btn-sm" label="Add Delegate" wire:click="showNewDelegateModal = true" />
    <x-modal wire:model="showNewDelegateModal" title="Add Delegate">
        <x-form wire:submit.prevent="savedelegate">
            <div class="grid grid-cols-2 gap-1">
            <x-input label="Name" wire:model="name" />
            <x-input label="Surname" wire:model="surname" />
            <x-input label="Email" wire:model="email" />
            <x-input label="Phone" wire:model="phone" />
            <x-input label="Designation" wire:model="designation" />
            <x-input label="National ID" wire:model="national_id" />
            <x-select label="Title" wire:model="title" placeholder="Select Title"  :options="$titlelist" />
            <x-select label="Gender" wire:model="gender" placeholder="Select Gender"  :options="$genderlist" />
            </div>
            <x-slot:actions>
                <x-button label="Cancel" wire:click="$set('showNewDelegateModal', false)" />
                <x-button label="Save" wire:click="savedelegate" spinner="savedelegate" class="btn-primary" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
