<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"/>
    <x-card title="Workplans" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-button label="Get Workplans" wire:click="modal=true" class="btn-primary" />
        </x-slot:menu>

     <table class="table table-bordered">
        <thead>
            <tr>
              
                <th>Output</th>
                <th>Indicator</th>
                <th>Quarter</th>
                <th>Month</th>
                <th>Target</th>
                <th>Weightage</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($workplans as $workplan)
            <tr>
               
                <td>{{ $workplan->output }}</td>
                <td>{{ $workplan->indicator }}</td>
                <td>{{ $workplan->targetmatrix->month }}</td>
                <td>{{ $workplan->month }}</td>
                <td>{{ $workplan->target }}</td>
                <td>{{ $workplan->weightage }}%</td>
                <td>{{ $workplan->status }}</td>
                <td>
                    <div class="flex gap-2">
                        <x-button icon="o-pencil"  class="btn-info btn-ghost btn-sm" wire:click="getworkplan({{ $workplan->id }})" />
                        <x-button icon="o-trash"  class="btn-error btn-ghost btn-sm" wire:click="deleteworkplan({{ $workplan->id }})" wire:confirm="Are you sure you want to delete this workplan?" />
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">
                    <x-alert class="alert-error" icon="o-exclamation-triangle" title="No workplans found" />
                </td>
            </tr>
            @endforelse
        </tbody>
     </table>
<x-slot:actions>
    @if($year && $strategy_id )
    <x-button icon="o-plus" label="Add Workplan" class="btn-primary btn-outline btn-sm" wire:click="addworkplan()" />
    @endif
</x-slot:actions>

    </x-card>
    <x-modal title="Search parameters" wire:model="modal">
        <x-form wire:submit.prevent="getworkplans">
            <div class="grid gap-2">
                <x-select label="Strategy" wire:model="strategy_id" placeholder="Select Strategy" :options="$strategies" option-label="name" option-value="id" />
                <x-input label="Year" wire:model="year" type="number" />
            </div>
            <x-slot:actions>
                <x-button label="Close" wire:click="$wire.closeModal()" class="btn-outline" />
                <x-button label="Search" type="submit" class="btn-primary" spinner="getworkplans" />
            </x-slot:actions>
        </x-form>
    </x-modal>
    <x-modal title="{{ $id ? 'Edit Workplan' : 'Add Workplan' }}" wire:model="addworkplanmodal" box-class="max-w-5xl">
        <x-hr/>
        <x-form wire:submit.prevent="saveworkplan">
            <div class="grid grid-cols-3 gap-2">
                @if($strategy)
                <x-select label="Programme" wire:model.live="programme_id" placeholder="Select Programme" :options="$programmes" option-label="title" option-value="id" />
                <x-select label="Outcome" wire:model.live="outcome_id" placeholder="Select Outcome" :options="$outcomes" option-label="title" option-value="id" />
                <x-select label="Output" wire:model.live="output_id" placeholder="Select Output" :options="$outputs" option-label="title" option-value="id" />
                <x-select label="Indicator" wire:model.live="indicator_id" placeholder="Select Indicator" :options="$indicators" option-label="title" option-value="id" />
                <x-select label="Target" wire:model.live="target_id" placeholder="Select Target" :options="$targets" option-label="target" option-value="id" />
       
                <x-select label="Target Matrix" wire:model.live="targetmatrix_id" placeholder="Select Target Matrix" :options="$targetmatrices" option-label="name" option-value="id" />
                @endif
                <x-select label="Month" wire:model="month" placeholder="Select month" :options="$monthlist" option-label="name" option-value="id" />
                <x-input label="Output" wire:model="output" type="text" />
                <x-input label="Indicator" wire:model="indicator" type="text" />
                <x-input label="Target" wire:model="target" type="number" />
                <x-input label="Weightage(%)" type="number" wire:model="weightage" type="number" />
                <x-input label="supervisor" type="text" wire:model="myapprover" readonly />
               
            </div>
            <x-slot:actions>
                <x-button label="Close" wire:click="$wire.closeModal()" class="btn-outline" />
                <x-button label="{{ $id ? 'Update' : 'Add' }}" type="submit" class="btn-primary" spinner="saveworkplan" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
