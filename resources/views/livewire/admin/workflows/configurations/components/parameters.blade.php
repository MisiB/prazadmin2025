<div>
    <x-button class="btn-success btn-outline btn-xs" icon="o-magnifying-glass-circle" wire:click="getparameters"/>
    <x-modal wire:model="modal" title="Workflow parameters" separator box-class="max-w-5xl">
        <x-slot:menu>
            <x-button icon="o-plus" class="btn-primary" label="New parameter" @click="$wire.modal=true" />
        </x-slot:menu>
        <x-table :headers="$headers" :rows="$parameters">
            <x-slot:empty>
                <x-alert class="alert-error" title="No parameters found." />
            </x-slot:empty>
            @scope("actions", $row)
                <div class="flex items-center space-x-2">
                    <x-button icon="o-pencil" class="btn-outline btn-xs btn-primary" wire:click="edit({{ $row->id }})" />
                    <x-button icon="o-trash" class="btn-outline btn-xs btn-error" wire:click="deleteparameter({{ $row->id }})" wire:confirm="Are you sure?" />
                </div>
            @endscope
        </x-table>
        <x-slot:actions>
            <x-button icon="o-plus" class="btn-primary btn-circle"  @click="$wire.newmodal=true" />
        </x-slot:actions>
    </x-modal>
    <x-modal wire:model="newmodal" title="New parameter" separator>
        <x-form wire:submit="saveparameter">
            <x-input wire:model="order" type="number" label="Order"/>
            <x-input wire:model="name" label="Name"/>
            <x-input wire:model="status" label="Status"/>
            <x-select wire:model="permission_id" label="Permission" :options="$permissions" option-label="name" option-value="id"/>
            <x-slot:actions>
                <x-button class="btn-primary" label="Save" type="submit" spinner="saveparameter"/>
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
