<div>
    <x-card title="Source of Funds" separator>
     <x-slot:menu>
         <x-button icon="o-plus" label="Add" wire:click="modal=true" class="btn-primary" />
     </x-slot:menu>
 
         <x-table :headers="$headers" :rows="$data" class="table-zebra">
             @scope('actions', $row)
                 <div class="flex items-center space-x-2">
                     <x-button icon="o-pencil" class="btn-outline btn-xs btn-primary" wire:click="edit({{ $row->id }})" />
                     <x-button icon="o-trash" class="btn-outline btn-xs btn-error" wire:click="delete({{ $row->id }})" wire:confirm="Are you sure?" />
                 </div>
             @endscope
             <x-slot:empty>
                 <x-alert class="alert-error" title="No Expense Categories found." />
             </x-slot:empty>
         </x-table>
   
    </x-card>
    <x-modal title="{{ $id ? 'Edit Expense Category' : 'New Expense Category' }}" wire:model="modal">
     <x-form wire:submit="save">
         <div class="grid gap-2">
             <x-input wire:model="name" label="Name" />
         </div>
         <x-slot:actions>
             <x-button label="Close" wire:click="modal=false" />
             <x-button label="{{ $id ? 'Update' : 'Save' }}" type="submit" class="btn-primary" spinner="save" />
         </x-slot:actions>
     </x-form>
    </x-modal>
 </div>
 