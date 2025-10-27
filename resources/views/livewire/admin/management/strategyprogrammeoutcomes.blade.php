<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm" item-class="text-sm" />

<x-card title="{{ $programme?->title }}" separator class="mt-2 border-2 border-gray-200">
    <x-slot:menu>
        <x-button icon="o-plus" label="Add Outcome" wire:click="openModal()" class="btn-outline" />
        </x-slot:menu>
    <table class="table table-zebra">
        <thead>
            <tr>
                <th>Output</th>
                <th></th> 
            </tr>
        </thead>
        <tbody>
            {{ $programme }}
            @forelse($programme?->outcomes??[] as $outcome)
            <tr>
                <td>{{ $outcome->title }}</td>
                <td></td>
                <td class="flex justify-end gap-2 pt-10">
                    <x-button icon="o-magnifying-glass" label="View indicators" class="btn-success btn-outline btn-sm" link="{{ route('admin.management.strategyprogrammeoutcomeindicators',[$programme->strategy->uuid,$programme->id,$outcome->id]) }}"/>
              
                    <x-button icon="o-pencil" label="Edit" class="btn-info btn-outline btn-sm" wire:click="getprogrammeoutcome({{ $outcome->id }})" />
                    <x-button icon="o-trash" label="Delete" class="btn-error btn-outline btn-sm" wire:click="deleteprogrammeoutcome({{ $outcome->id }})" wire:confirm="Are you sure you want to delete this outcome?" />
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">
                    <x-alert class="alert-error" icon="o-exclamation-triangle" title="No outcomes found" />
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</x-card>
<x-modal wire:model="modal" title="{{ $id ? 'Edit Outcome' : 'Add Outcome' }}">
    <x-form wire:submit="save">
        <div class="grid gap-2">
            <x-input label="Title" wire:model="title" />
        </div>
        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.closeModal()" />
            <x-button label="Save" type="submit" class="btn-primary" spinner="save" />
        </x-slot:actions>
    </x-form>
</x-modal>
</div>
        
