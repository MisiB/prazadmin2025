<div>
    <x-card>
        <x-input placeholder="Search for entity" wire:model.live.debounce.500ms="search"/>
        <x-table :headers="$headers" :rows="$customers">
    
            @scope('cell_action',$row)
            <div>
                <x-button label="Select" class="btn-primary" wire:click="selectCustomer({{ $row->id }})" />
            </div>
            @endscope
            <x-slot:empty>
                <x-alert class="alert-error" title="No customers found." />
            </x-slot:empty>
        </x-table>
    </x-card>
    <x-modal title="Create Tender" wire:model="modal" box-class="max-w-2xl">
       
            <x-form wire:submit.prevent="save">
                <div class="grid grid-cols-2 gap-2">
                <x-input placeholder="Tender Number" wire:model="tender_number"/>
                <x-input placeholder="Tender Title" wire:model="tender_title"/>
                <x-select placeholder="Status" wire:model="status" :options="$statuses" option-label="name" option-value="id"/>
           
                
                <x-select placeholder="Tender Type" wire:model="tender_type" :options="$tendertypes" option-label="name" option-value="id"/>
                <x-input type="date" placeholder="Closing Date" wire:model="closing_date"/>
                <x-input type="time" placeholder="Closing Time" wire:model="closing_time"/>
                    </div>
                    <div class="grid gap-2 mt-2">
                    <x-textarea placeholder="Tender Description" wire:model="tender_description"/>
                    </div>
                    <x-card class="bg-gray-50">
                        <div class="grid grid-cols-5 gap-2">
                            <x-select placeholder="Inventory Item" wire:model="inventoryitem_id" :options="$inventoryitems" option-label="name" option-value="id"/>
                            <x-select placeholder="Currency" wire:model="currency_id" :options="$currencies" option-label="name" option-value="id"/>
                            <x-input placeholder="Amount" wire:model="amount"/>
                            <x-input placeholder="Validity Period" wire:model="validityperiod"/>
                            <x-button label="Add" wire:click="addtenderfee" class="btn-primary"/>
                        </div>
                        <div class="mt-2">
                        @if(count($tenderfees)>0)
                              <table class="table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Amount</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                             @foreach ($tenderfees as $tenderfee )
                                <tr>
                                 <td>{{ $inventoryitems->where("id",$tenderfee['inventoryitem_id'])->first()->name }}</td>
                                 <td>{{ $currencies->where("id",$tenderfee['currency_id'])->first()->name }}{{ $tenderfee['amount'] }}</td>
                                 <td class="flex justify-end">
                                    <x-button label="Remove"  wire:click="removetenderfee({{ $loop->index }})" class="btn-error"/>
                                 </td>
                                </tr> 
                             @endforeach
                                </tbody>
                             </table>
                        @else
                        <x-alert class="alert-error" title="No tender fees added." />
                        @endif
                        </div>
                    </x-card>
                <x-slot:actions>
                    <x-button label="Close" wire:click="modal=false"/>
                    <x-button label="Create" type="submit" class="btn-primary" spinner="create"/>
                </x-slot:actions>
            </x-form>
      
    </x-modal>
</div>
