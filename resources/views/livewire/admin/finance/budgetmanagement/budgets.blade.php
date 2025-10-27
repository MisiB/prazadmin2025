<div>
    <x-breadcrumbs :items="$breadcrumbs"     class="bg-base-300 p-3 mt-2 rounded-box"    link-item-class="text-sm font-bold" />
    <x-card title="Budget Management" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-button label="New Budget" wire:click="modal=true" class="btn-primary" />
        </x-slot:menu>
        <x-table :headers="$headers" :rows="$data">
            @scope('cell_currency', $row)
                {{ $row->currency->name }}
            @endscope
            @scope('cell_createdby', $row)
                {{ $row->createdby?->name??"--" }} {{ $row->createdby?->surname??"--" }}
            @endscope
            @scope('cell_updatedby', $row)
                {{ $row->updatedby?->name??"--" }} {{ $row->updatedby?->surname??"--" }}
            @endscope
            @scope('cell_approvedby', $row)
                {{ $row->approvedby?->name??"--" }} {{ $row->approvedby?->surname??"--" }}
            @endscope
            @scope('cell_status', $row)
                <x-badge class="{{ $row->status == 'APPROVED' ? 'badge-success' : 'badge-warning' }} badge-sm" value="{{ $row->status }}"/>
            @endscope
            @scope('actions', $row)
                <div class="flex items-center space-x-2">
                    <x-button icon="o-pencil" class="btn-sm btn-info btn-outline" wire:click="edit({{ $row->id }})" spinner />
                    <x-button icon="o-trash" class="btn-sm btn-outline btn-error" wire:click="delete({{ $row->id }})" wire:confirm="Are you sure?" spinner />
                    <x-button icon="o-eye" class="btn-sm btn-outline btn-info" link="{{ route('admin.finance.budgetmanagement.budgetdetail',$row->uuid) }}" spinner />
                </div>
            @endscope
            <x-slot:empty>
                <x-alert class="alert-error" title="No Budget found." />
            </x-slot:empty>
        </x-table>
    </x-card>

    <x-modal title="{{ $id ? 'Edit Budget' : 'New Budget' }}" wire:model="modal">
        <x-form wire:submit="save">
            <div class="grid gap-2">
                <x-input wire:model="year" label="Year" />
                <x-select wire:model="currency" label="Currency" :options="$currencies" placeholder="Select Currency" option-label="name" option-value="id" />
            </div>
            <x-slot:actions>
                <x-button label="Close" wire:click="modal=false" />
                <x-button label="{{ $id ? 'Update' : 'Save' }}" type="submit" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
