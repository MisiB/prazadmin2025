<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box" />
    <x-card title="{{ $outcome->title }}" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-button  class="btn-outline" icon="o-plus" label="Add Outcome Indicator" responsive wire:click="openModal()"/>
            </x-slot:menu>
    <table class="table table-zebra table-responsive">
        <thead>
            <tr>
                <th>Indicator</th>
                <th>Target</th>
                <th>Variance</th>
                <th>Subprogrammes</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($outcome->indicators as $indicator)
            <tr>
                <td>{{ $indicator->indicator }}</td>
                <td>{{ $indicator->target }} {{ $indicator->uom }}</td>
                <td>{{ $indicator->varianceuom }}{{ $indicator->variance }}{{ $indicator->uom }} </td>
                <td>
                    <x-button icon="o-plus" label="Subprogrammes" class="btn-info btn-outline btn-sm" badge="{{ $indicator->subprogrammes->count() }}" wire:click="openSubprogrammeModal({{ $indicator->id }})"/>
                </td>

                <td class="flex justify-end gap-2 pt-10">
                    <x-button icon="o-pencil" label="Edit" class="btn-info btn-outline btn-sm" wire:click="getindicator({{ $indicator->id }})" />
                    <x-button icon="o-trash" label="Delete" class="btn-error btn-outline btn-sm"  wire:confirm="Are you sure you want to delete this indicator?" wire:click="deleteindicator({{ $indicator->id }})"/>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">
                    <x-alert class="alert-error" icon="o-exclamation-triangle" title="No indicators found" />
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </x-card>
    <x-modal wire:model="modal" box-class="max-w-2xl" title="{{ $id ? 'Edit Indicator' : 'Add Indicator' }}">
        <x-form wire:submit="save">
            <x-input wire:model="indicator" label="Indicator" placeholder="Enter indicator" />
            <x-input wire:model="target" label="Target" placeholder="Enter target">
                <x-slot:append>
                    <x-select wire:model="uom" placeholder="Select unit of measure" :options="[['id'=>'EACH','name'=>'EACH'],['id'=>'%','name'=>'%']]" option-label="name" option-value="id" />
                </x-slot:append>
            </x-input>
            <x-input wire:model="variance" label="Variance" placeholder="Enter variance">
                <x-slot:prepend>
                    <x-select wire:model="varianceuom" placeholder="Select variance" :options="[['id'=>'+/-','name'=>'+/-'],['id'=>'+','name'=>'+'],['id'=>'-','name'=>'-']]" option-label="name" option-value="id" />
                </x-slot:prepend>
            </x-input>

    
        <x-slot:actions>
            <x-button label="Close" wire:click="closeModal" class="btn-outline" />
            <x-button label="Save" type="submit" class="btn-primary" spinner="save" />
       
        </x-slot:actions>
    </x-form>
    </x-modal>


    <x-modal wire:model="subprogrammeModal" box-class="max-w-4xl" title="Subprogrammes">
     
            <x-button icon="o-plus" label="Add Subprogramme" class="btn-info btn-outline" wire:click="addsubprogrammeModal=true"/>
       
        <table class="table table-zebra table-responsive">
            <thead>
                <tr>
                    <th>Subprogramme</th>
                    <th>Contribution(%)</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($subprogrammes as $subprogramme)
                <tr>
                    <td>{{ $subprogramme->department->name }}</td>
                    <td>{{ $subprogramme->weightage }}%</td>
                    <td class="flex justify-end gap-2">
                        <x-button icon="o-pencil" label="Edit" class="btn-info btn-outline btn-sm" wire:click="getsubprogramme({{ $subprogramme->id }})" />
                        <x-button icon="o-trash" label="Delete" class="btn-error btn-outline btn-sm"  wire:confirm="Are you sure you want to delete this subprogramme?" wire:click="deletesubprogramme({{ $subprogramme->id }})"/>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center">
                        <x-alert class="alert-error" icon="o-exclamation-triangle" title="No subprogrammes found" />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </x-modal>
    <x-modal wire:model="addsubprogrammeModal" title="{{ $subprogramme_id ? 'Edit Subprogramme' : 'Add Subprogramme' }}">
        <x-form wire:submit="saveSubprogramme">
            <x-select wire:model="department_id" placeholder="Select department" :options="$departments" option-label="name" option-value="id" />
            <x-input wire:model="weightage" type="number" label="Contribution(%)" placeholder="Enter contribution" />
        <x-slot:actions>
            <x-button label="Close" wire:click="closeSubprogrammeModal" class="btn-outline" />
            <x-button label="Save" type="submit" class="btn-primary" spinner="saveSubprogramme" />
       
        </x-slot:actions>
    </x-form>
    </x-modal>
</div>
