<div>
    <x-breadcrumbs :items="$breadcrumbs"
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />

    <x-card title="Remaining Leave Days" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-button label="Export statements for update" responsive icon="o-plus"  @click="$wire.exportmodal = true" 
                class="bg-gradient-to-bl from-blue-600 to-blue-800 shadow-md shadow-gray-200 rounded-lg text-white" />
            <x-button label="Import leave statement updates" responsive icon="o-plus" @click="$wire.importmodal = true"
                class="bg-gradient-to-bl from-blue-600 to-blue-800 shadow-md shadow-gray-200 rounded-lg text-white" />
        </x-slot:menu>

        <x-table :headers="$headers" :rows="$leavestatements" show-empty-text empty-text="No leave statements found.">
            @scope('cell_vacationleave',$leavestatement)
                <span>{{$leavestatement['leavetypes']['Vacation']??'-'}}</span>
            @endscope       
            @scope('cell_annualleave',$leavestatement)
                <span>{{$leavestatement['leavetypes']['Annual']??'-'}}</span>
            @endscope 
            @scope('cell_studyleave',$leavestatement)
                <span>{{$leavestatement['leavetypes']['Study']??'-'}}</span>
            @endscope 
            @scope('cell_sickleave',$leavestatement)
                <span>{{$leavestatement['leavetypes']['Sick']??'-'}}</span>
            @endscope             
            @scope('cell_maternityleave',$leavestatement)
                <span>{{$leavestatement['leavetypes']['Maternity']??'-'}}</span>
            @endscope             
            @scope('cell_compassionateleave',$leavestatement)
                <span>{{$leavestatement['leavetypes']['Compassionate']??'-'}}</span>
            @endscope             
            <x-slot:empty>
                <x-alert class="alert-error" title="No leave statement found." />
            </x-slot:empty>
        </x-table>
    </x-card>

    <x-modal wire:model="exportmodal"  title="Export A Copy">
        <x-form wire:submit="export">
            <div class="grid gap-2 ">
                <x-select label="Select Leave Type" wire:model.live="leavetypeid" placeholder="Select leave type" :options="[['id'=>'1', 'name' => 'Vacation'], ['id'=>'2', 'name' => 'Annual']]" />              
            </div>
            <x-slot:actions>
                <x-button label="Export Copy" type="submit" spinner="export" 
                class="bg-gradient-to-bl from-blue-600 to-blue-800 shadow-md shadow-gray-200 rounded-lg text-white"/>
            </x-slot:actions>
        </x-form>
    </x-modal>    

    <x-modal wire:model="importmodal"  title="Import Updates">
        <x-form wire:submit="import">
            <div class="grid gap-2 ">
                <x-select label="Select Leave Type" wire:model.live="leavetypeid" :options="[['id'=>'1', 'name' => 'Vacation'], ['id'=>'2', 'name' => 'Annual']]" placeholder="Select Leave Type" placeholder-value="0" />    
                <x-file  hint="Upload an updated Export Copy" wire:model.live="updatedexportfile" accept="text/csv"/>          
            </div>
            <x-slot:actions>
                <x-button label="Import Updates" type="submit" spinner="import" 
                class="bg-gradient-to-bl from-blue-600 to-blue-800 shadow-md shadow-gray-200 rounded-lg text-white" />
            </x-slot:actions>
        </x-form>
    </x-modal>    
 
</div>