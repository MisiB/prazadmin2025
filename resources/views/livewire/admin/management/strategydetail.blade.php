<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />



<x-card title="{{ $strategy->name }}" subtitle="{{ $strategy->status }}" separator class="mt-5 border-2 border-gray-200">
    <x-slot:menu>
        <x-input type="number" min="{{ $strategy->startyear }}" max="{{ $strategy->endyear }}" wire:model="year"  />
    </x-slot:menu>
    <x-card title="Programmes" separator class=" border-2 border-gray-200">
        <x-slot:menu>
            <x-button icon="o-plus"  wire:click="openModal()" class="btn-circle btn-primary" />
        </x-slot:menu>
    <x-table :headers="$headers" :rows="$strategy?->programmes??[]" wire:model="expanded" expandable>
        @scope('actions', $programme)
        <div class="flex justify-end gap-2">             
            <x-button icon="o-pencil"  class="btn-ghost  btn-sm" wire:click="getprogramme({{ $programme->id }})" />
            <x-button icon="o-trash"  class="btn-ghost  btn-sm" wire:click="deleteprogramme({{ $programme->id }})" wire:confirm="Are you sure you want to delete this programme?" />
        </div>
        @endscope
        @scope('cell_code', $programme)
        {{ $programme->code }}
        @endscope
        @scope('cell_title', $programme)
        {{ $programme->title }}
        @endscope
        @scope('cell_status', $programme)
        <x-badge :value="$programme->status" :class="$programme->status == 'Draft' ? 'badge-warning' : ($programme->status == 'Approved' ? 'badge-success' : 'badge-error')"/>
        @endscope

        @scope('expansion', $programme,$headersoutcome,$headerssubprogramme,$headersindicator,$headerstarget)
        <x-card title="Outcomes" separator class=" border-2 border-blue-200">
            <x-slot:menu>
                <x-button icon="o-plus" wire:click="openViewModal({{ $programme->id }})" class="btn-circle btn-primary" />
            </x-slot:menu>
        <x-table :headers="$headersoutcome" :rows="$programme?->outcomes??[]" wire:model="outcomeexpanded" expandable>
            @scope('actions', $outcome)
            <div class="flex justify-end gap-2">           
                <x-button icon="o-pencil"  class="btn-ghost  btn-sm" wire:click="editoutcome({{ $outcome->id }})" />
                <x-button icon="o-trash"  class="btn-ghost  btn-sm" wire:click="deleteoutcome({{ $outcome->id }})" wire:confirm="Are you sure you want to delete this outcome?" />
            </div>
            @endscope
            @scope('expansion', $outcome,$headersoutcome,$headerssubprogramme,$headersindicator,$headerstarget)
            <x-card title="Outputs" separator class=" border-2 border-green-200">
                <x-slot:menu>
                    <x-button icon="o-plus"  wire:click="addoutput({{ $outcome->id }})" class="btn-circle btn-primary" />
                </x-slot:menu>
                <x-table :headers="$headersoutcome" :rows="$outcome?->outputs??[]" wire:model="outputexpanded" expandable>
                    @scope('actions', $output)
                    <div class="flex justify-end gap-2">           
                        <x-button icon="o-pencil"  class="btn-ghost  btn-sm" wire:click="editoutput({{ $output->id }})" />
                        <x-button icon="o-trash"  class="btn-ghost  btn-sm" wire:click="deleteoutput({{ $output->id }})" wire:confirm="Are you sure you want to delete this output?" />
                    </div>
                    @endscope

                    <x-slot:empty>
                        <x-alert class="alert-error" icon="o-exclamation-triangle" title="No outputs found" />
                    </x-slot:empty>
                    @scope('expansion', $output,$headersoutcome,$headerssubprogramme,$headersindicator,$headerstarget)
                    <x-card title="Sub programmes" separator class=" border-2 border-orange-200">
                        <x-slot:menu>
                            <x-button icon="o-plus"  wire:click="assignsubprogramme({{ $output->id }})" class="btn-circle btn-primary" />
                        </x-slot:menu>
                        <x-table :headers="$headerssubprogramme" :rows="$output?->departmentoutputs??[]" wire:model="subprogrammeexpanded" expandable>
                         
                          
                           @scope('cell_weightage', $subprogramme)
                           {{ $subprogramme->weightage }} %
                           @endscope
                            @scope('actions', $subprogramme)
                            <div class="flex justify-end gap-2">           
                                <x-button icon="o-pencil"  class="btn-ghost  btn-sm" wire:click="editsubprogramme({{ $subprogramme->id }})" />
                                <x-button icon="o-trash"  class="btn-ghost  btn-sm" wire:click="deletesubprogramme({{ $subprogramme->id }})" wire:confirm="Are you sure you want to delete this subprogramme?" />
                            </div>
                            @endscope
                            @scope('expansion', $subprogramme,$headersindicator,$headerstarget)
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


                                    @scope('expansion', $indicator,$headersindicator,$headerstarget)
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
                                           @scope('expansion', $target)
                                           <x-card title="Target Matrices" separator class=" border-2 border-purple-200">
                                            <table class="table table-zebra table-sm">
                                                <thead>
                                                    <tr><th>Month</th><th>Target</th><th>Status</th></tr>
                                                </thead>
                                                <tbody>
                                                   
                                                    @forelse($target->targetmatrices as $month)
                                                    <tr>
                                                        <td>{{ $month->month }}</td>
                                                        <td>{{ $month->target }}</td>
                                                        <td>{{ $month->status }}</td>
                                                     
                                                    </tr>
                                                    @empty
                                                    <tr><td colspan="4" class="text-center">No target matrices found</td></tr>
                                                    @endforelse
                                                 
                                                </tbody>
                                               </table>
                                           </x-card>
                                           @endscope
                                           <x-slot:empty>
                                            <x-alert class="alert-error" icon="o-exclamation-triangle" title="No targets found" />
                                           </x-slot:empty>
                                        
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
<x-modal title="{{ $id ? 'Edit' : 'Add' }} Programme" wire:model="modal">
    <x-form wire:submit="save">
        <div class="grid gap-2">
            <x-input label="Code" wire:model="code" />
            <x-input label="Title" wire:model="title" />
        </div>
        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.closeModal()" />
            <x-button label="Save" type="submit" class="btn-primary" spinner="save" />
        </x-slot:actions>
    </x-form>
</x-modal>



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

<x-modal wire:model="outcomemodal" title="{{ $outcome_id ? 'Edit Outcome' : 'Add Outcome' }}">
    <x-form wire:submit="saveoutcome">
        <div class="grid gap-2">
            <x-input label="Title" wire:model="title" />
        </div>
        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.closeModal()" />
            <x-button label="Save" type="submit" class="btn-primary" spinner="save" />
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

<x-modal wire:model="adddepartmentoutputmodal" title="{{ $departmentoutput_id ? 'Edit Subprogramme' : 'Add Subprogramme' }}">
    <x-hr/>
    <x-form wire:submit="saveassignsubprogramme">
        <div class="grid gap-2">
            <x-select wire:model="department_id" placeholder="Select Department" :options="$departments" option-label="name" option-value="id" />
            <x-input wire:model="weightage" type="number" min="0" max="100" label="Weightage(%)" />
        </div>
        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.adddepartmentoutputmodal = false" />
            <x-button label="{{ $departmentoutput_id ? 'Update' : 'Save' }}" type="submit" class="btn-primary" spinner="saveassignsubprogramme" />
        </x-slot:actions>
    </x-form>
</x-modal>
</div> 
    
