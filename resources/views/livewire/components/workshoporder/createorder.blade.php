<div>
    <x-button label="Create Order" icon="o-plus" wire:click="$set('showCreateModal', true)" class="btn-primary"/>

    <x-modal wire:model="showCreateModal" title="Create Order" subtitle="Base cost per delegate: {{ number_format($workshop->Cost, 2) }} {{ $workshop->currency->name }}" box-class="w-11/12 max-w-5xl">
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

        <div class="grid gap-4 mt-4">
        

         <x-card title="Search account" separator progress-indicator>
            <x-input  wire:model="search">
                <x-slot:append>
                    {{-- Add `rounded-s-none` class (RTL support) --}}
                    <x-button label="Search" icon="o-check" class="btn-primary rounded-s-none" wire:click="searchAccount" spinner="searchAccount"/>
                </x-slot:append>
            </x-input>
            <x-table :headers="$accountheaders" :rows="$accounts" separator progress-indicator   show-empty-text empty-text="Nothing Here!">
               
                @scope('cell_action',$row)
                <div class="flex justify-end">
                @if($row->id == $this->customer_id)
                <x-button label="Selected"  class="btn btn-xs"/>
                @else   
                <x-button label="Select"  class="btn btn-sm btn-success" icon="o-check" wire:click="selectAccount({{ $row->id }})" spinner="selectAccount({{ $row->id }})"/>
                @endif
                </div>
                @endscope
              </x-table>
         </x-card>
                   </div>


        <x-slot:actions>
            <div class="flex justify-between w-full">
                <x-button label="Cancel" wire:click="$set('showCreateModal', false)"/>
                <x-button label="Create Order" wire:click="createorder" class="btn-primary"/>
            </div>
        </x-slot>
    </x-modal>
</div>
