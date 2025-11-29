<div>
   <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"/>

<x-card title="Departmental Outputs" separator class="mt-5 border-2 border-gray-200">
<x-slot:menu>    
    <x-button label="Get outputs" wire:click="modal=true" class="btn-primary" />
</x-slot:menu>
@if($strategy)
<x-card title="{{ $strategy?->name }}" subtitle="{{ $strategy?->status }}" separator class="mt-5 border-2 border-gray-200">
    <x-slot:menu>
        <x-input type="number" min="{{ $strategy->startyear }}" max="{{ $strategy->endyear }}" wire:model="year"  />
    </x-slot:menu>
    <x-card title="Programmes" separator class=" border-2 border-gray-200">
   
    <x-table :headers="$headers" :rows="$strategy?->programmes??[]" wire:model="expanded" expandable>
   
        @scope('cell_code', $programme)
        {{ $programme->code }}
        @endscope
        @scope('cell_title', $programme)
        {{ $programme->title }}
        @endscope
        @scope('cell_status', $programme)
        <x-badge :value="$programme->status" :class="$programme->status == 'Draft' ? 'badge-warning' : ($programme->status == 'Approved' ? 'badge-success' : 'badge-error')"/>
        @endscope

        @scope('expansion', $programme,$headersoutcome,$headerssubprogramme,$headersindicator,$headerstarget,$headerstargetmatrix)
        <x-card title="Outcomes" separator class=" border-2 border-blue-200">
        <x-table :headers="$headersoutcome" :rows="$programme?->outcomes??[]" wire:model="outcomeexpanded" expandable>
           
            @scope('expansion', $outcome,$headersoutcome,$headerssubprogramme,$headersindicator,$headerstarget,$headerstargetmatrix)
            <x-card title="Outputs" separator class=" border-2 border-green-200">
                <x-table :headers="$headersoutcome" :rows="$outcome?->outputs??[]" wire:model="outputexpanded" expandable>
                  

                    <x-slot:empty>
                        <x-alert class="alert-error" icon="o-exclamation-triangle" title="No outputs found" />
                    </x-slot:empty>
                    @scope('expansion', $output,$headersoutcome,$headerssubprogramme,$headersindicator,$headerstarget,$headerstargetmatrix)
                    <x-card title="Sub programmes" separator class=" border-2 border-orange-200">
                        
                        <x-table :headers="$headerssubprogramme" :rows="$output?->departmentoutputs??[]" wire:model="subprogrammeexpanded" expandable>
                         
                          
                           @scope('cell_weightage', $subprogramme)
                           {{ $subprogramme->weightage }} %
                           @endscope
                          
                            @scope('expansion', $subprogramme,$headersindicator,$headerstarget,$headerstargetmatrix)
                            <x-card title="Indicators" separator class=" border-2 border-red-200">
                                <x-slot:menu>
                                    <x-button icon="o-plus" wire:click="addindicator({{ $subprogramme->id }})" class="btn-circle btn-primary" />
                                </x-slot:menu>
                                <x-table :headers="$headersindicator" :rows="$subprogramme?->indicators??[]" wire:model="indicatorexpanded" expandable>
                                    @scope('actions', $indicator)
                                    <div class="flex justify-end gap-2">           
                                        <x-button icon="o-pencil"  class="btn-ghost  btn-sm" wire:click="editindicator({{ $indicator->id }})" />
                                        <x-button icon="o-trash"  class="btn-ghost  btn-sm" wire:click="deleteindicator({{ $indicator->id }})" wire:confirm="Are you sure you want to delete this indicator?" />
                                    </div>
                                    @endscope

                                    <x-slot:empty>
                                        <x-alert class="alert-error" icon="o-exclamation-triangle" title="No indicators found" />
                                    </x-slot:empty>


                                    @scope('expansion', $indicator,$headersindicator,$headerstarget,$headerstargetmatrix)
                                    <x-card title="Targets" separator class=" border-2 border-yellow-200">
                                        <x-slot:menu>
                                            <x-button icon="o-plus" wire:click="addtarget({{ $indicator->id }})" class="btn-circle btn-primary" />
                                        </x-slot:menu>
                                        <x-table :headers="$headerstarget" :rows="$indicator?->targets??[]" wire:model="targetmatrixexpanded" expandable>
                                           @scope('actions', $target)
                                           <div class="flex justify-end gap-2">           
                                               <x-button icon="o-pencil"  class="btn-ghost  btn-sm" wire:click="edittarget({{ $target->id }})" />
                                               <x-button icon="o-trash"  class="btn-ghost  btn-sm" wire:click="deletetarget({{ $target->id }})" wire:confirm="Are you sure you want to delete this target?" />
                                           </div>
                                           @endscope
                                           <x-slot:empty>
                                            <x-alert class="alert-error" icon="o-exclamation-triangle" title="No targets found" />
                                           </x-slot:empty>
                                           @scope('expansion', $target,$headerstargetmatrix)
                                           <x-card title="Target Matrices" separator class=" border-2 border-purple-200">
                                            <x-slot:menu>
                                                <x-button icon="o-plus" wire:click="addtargetmatrix({{ $target->id }})" class="btn-circle btn-primary" />
                                            </x-slot:menu>
                                           <table class="table table-zebra table-sm">
                                            <thead>
                                                <tr><th>Quarter</th><th>Target</th><th>Status</th><th></th></tr>
                                            </thead>
                                            <tbody>
                                               
                                                @forelse($target->targetmatrices as $month)
                                                <tr>
                                                    <td>{{ $month->month }}</td>
                                                    <td>{{ $month->target }}</td>
                                                    <td>{{ $month->status }}</td>
                                                    <td>{{ $month->action }}</td>
                                                    <td>
                                                        <x-button icon="o-pencil"  class="btn-ghost  btn-sm" wire:click="edittargetmatrix({{ $month->id }})" />
                                                        <x-button icon="o-trash"  class="btn-ghost  btn-sm" wire:click="deletetargetmatrix({{ $month->id }})" wire:confirm="Are you sure you want to delete this target matrix?" />
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="4" class="text-center">No target matrices found</td></tr>
                                                @endforelse
                                             
                                            </tbody>
                                           </table>
                                        </x-card>
                                        @endscope
                                        </x-table>
                                    </x-card>
                                    @endscope
                                              </x-table>
                             </x-card>
                            @endscope
                            <x-slot:empty>
                                <x-alert class="alert-error" icon="o-exclamation-triangle" title="No subprogrammes found" />
                            </x-slot:empty>
                           
                        </x-table>
                    </x-card>
                    @endscope
                </x-table>
               </x-card>
            @endscope

        </x-table>
        </x-card>
        @endscope
        <x-slot:empty>
            <x-alert class="alert-error" icon="o-exclamation-triangle" title="No programmes found" />
        </x-slot:empty>
    </x-table>
 </x-card>
</x-card>
@else
<x-alert class="alert-error" icon="o-exclamation-triangle" title="No strategy selected please select a strategy" />
@endif




<x-modal wire:model="indicatormodal" title="{{ $indicator_id ? 'Edit Indicator' : 'Add Indicator' }}" >
    <x-hr/>
    <x-form wire:submit="saveindicator">
        <div class="grid  gap-2">
        <x-input wire:model="title"  placeholder="Enter indicator" />
        <x-select wire:model="uom" placeholder="Select unit of measure" :options="[['id'=>'Number','name'=>'Number'],['id'=>'Percentage','name'=>'Percentage']]" option-label="name" option-value="id" />
        
     
    <x-slot:actions>
        <x-button label="Cancel" @click="$wire.closeModal()" />
        <x-button label="{{ $indicator_id ? 'Update' : 'Save' }}" type="submit" class="btn-primary" spinner="saveindicator" />
    </x-slot:actions>
    </x-form>
   
</x-modal>



<x-modal wire:model="targetmodal" title="{{ $target_id ? 'Edit Target' : 'Add Target' }}">
    <x-form wire:submit="savetarget">
        <div class="grid gap-2">
            <x-input label="Target" type="number" wire:model="target" />
            <x-input label="Variance" type="number" wire:model="variance" />
            
        </div>
        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.closeModal()" />
            <x-button label="Save" type="submit" class="btn-primary" spinner="save" />
        </x-slot:actions>
    </x-form>
</x-modal>

<x-modal wire:model="outputmodal" title="{{ $output_id ? 'Edit Output' : 'Add Output' }}">
    <x-form wire:submit="saveoutput">
        <div class="grid gap-2">
            <x-input label="Title" wire:model="title" />
        </div>
        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.outputmodal = false" />
            <x-button label="Save" type="submit" class="btn-primary" spinner="save" />
        </x-slot:actions>
    </x-form>
</x-modal>


   
</x-card>
<x-modal title="Search parameters" wire:model="modal">
    <x-form wire:submit.prevent="getsubprogrammeoutputs">
        <div class="grid gap-2">
            <x-select label="Strategy" wire:model="strategy_id" placeholder="Select Strategy" :options="$strategies" option-label="name" option-value="id" />
            <x-input label="Year" wire:model="year" type="number" />
        </div>
        <x-slot:actions>
            <x-button label="Close" wire:click="$wire.closeModal()" class="btn-outline" />
            <x-button label="Search" type="submit" class="btn-primary" spinner="getsubprogrammeoutputs" />
        </x-slot:actions>
    </x-form>
</x-modal>

<x-modal wire:model="targetmatrixmodal" title="{{ $targetmatrix_id ? 'Edit Target Matrix' : 'Add Target Matrix' }}">
    <x-form wire:submit="savetargetmatrix">
        <div class="grid gap-2">
            <x-select label="Month" wire:model="month" placeholder="Select month" :options="$monthlist" option-label="name" option-value="id" />
            <x-input label="Target" wire:model="target" type="number" />
        </div>
        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.closeModal()" />
            <x-button label="{{ $targetmatrix_id ? 'Update' : 'Save' }}" type="submit" class="btn-primary" spinner="savetargetmatrix" />
        </x-slot:actions>
    </x-form>
</x-modal>

</div>
    
