<div>
    <x-breadcrumbs :items="$breadcrumbs"
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />

    <x-card title="Leave Statements" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-button label="Export statements for update" responsive icon="o-plus" class="btn-outline" @click="$wire.exportmodal = true" />
            <x-button label="Import leave statement updates" responsive icon="o-plus" class="btn-outline" @click="$wire.importmodal = true" />
        </x-slot:menu>

        <x-table :headers="$headers" :rows="$leavestatements">
            @scope('cell_days',$leavestatement)
            <span>
               {{ (int)$leavestatement->leavetype->ceiling-(int)$leavestatement->days}}
            </span>
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
                <x-button label="Export Copy" type="submit" class="btn-primary" spinner="export" />
            </x-slot:actions>
        </x-form>
    </x-modal>    

    <x-modal wire:model="importmodal"  title="Import Updates">
        <x-form wire:submit="import">
            <div class="grid gap-2 ">
                <x-select label="Select Leave Type" wire:model.live="leavetypeid" placeholder="Select leave type" :options="[['id'=>'1', 'name' => 'Vacation'], ['id'=>'2', 'name' => 'Annual']]" />    
                <x-file  hint="Upload an updated Export Copy" wire:model.live="updatedexportfile" accept="text/csv"/>          
            </div>
            <x-slot:actions>
                <x-button label="Import Updates" type="submit" class="btn-primary" spinner="import" />
            </x-slot:actions>
        </x-form>
    </x-modal>    
 
</div>
