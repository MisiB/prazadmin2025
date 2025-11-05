<div>
    <x-button icon="o-pencil" wire:click="getorder()" class="btn-ghost btn-sm"/>

    <x-modal wire:model="showCreateModal" title="Edit Order"  box-class="w-11/12 max-w-5xl">
        <div class="grid grid-cols-4 gap-1">
            <x-input label="Name" wire:model="name" />
            <x-input label="Surname" wire:model="surname" />
            <x-input label="Email" type="email" wire:model="email" />
            <x-input label="Phone" type="text" wire:model="phone" />
          
        
        </div>

        <div class="grid grid-cols-4 gap-1 mt-4">
            
             <x-select label="Currency" wire:model.live="currency_id" :options="$currencies" placeholder="Select Currency" option-label="name" option-value="id" />
             <x-select label="Exchange Rate" wire:model.live="exchangerate_id" :options="$exchangerates" placeholder="Select Exchange Rate" option-label="name" option-value="id" />
             <x-input label="Delegates" type="number" wire:model.live="delegates" min="1" />
                <x-input  label="Total Cost" type="text" readonly 
                   wire:model.live="cost"
                />
      
         
        </div>

      


        <x-slot:actions>
            <div class="flex justify-between w-full">
                <x-button label="Cancel" wire:click="$set('showCreateModal', false)"/>
                <x-button label="Save Order" wire:click="saveorder" spinner="saveorder" class="btn-primary"/>
            </div>
        </x-slot>
    </x-modal>
</div>
