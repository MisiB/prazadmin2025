<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />
   <x-card title="Invoices" separator class="mt-5 border-2 border-gray-200">
    <x-slot:menu>
        <x-input wire:model.live="search" placeholder="Search invoices..." />
        <x-button icon="o-plus" label="New Invoice" wire:click="modal=true" class="btn-primary" />
    </x-slot:menu>
                    <x-table :rows="$invoices" :headers="$headers">
                        @scope('cell_status', $row)
                            <span class="badge badge-{{ $row->status == 'PAID' ? 'success' : 'warning' }}">
                                {{ $row->status }}
                            </span>
                        @endscope
                    
                    @scope('cell_amount', $row)
                        {{ $row->currency->name ?? '' }} {{ $row->amount }}
                    @endscope
                    
                    @scope('cell_inventoryitem->name', $row)
                        {{ $row->inventoryitem->name ?? 'N/A' }}
                    @endscope
                    
                    @scope('cell_created_at', $row)
                        {{ date('Y-m-d', strtotime($row->created_at)) }}
                    @endscope

                    @scope('actions', $row)
                    @if($row->status=="PENDING")
                        <div class="flex items-center space-x-2">
                            @if($row->invoicesource=="MANUAL")
                            <x-button icon="o-pencil" class="btn-sm btn-info btn-outline" 
                                wire:click="edit({{ $row->id }})" spinner />
                            <x-button icon="o-trash" class="btn-sm btn-outline btn-error" 
                                wire:click="delete({{ $row->id }})" wire:confirm="Are you sure?" spinner />
                              
                            <x-button icon="o-currency-dollar" class="btn-sm btn-success btn-outline" 
                                wire:click="settle({{ $row->id }})" spinner wire:confirm="You are about to settle  invoice using wallet, Do you wish to proceed"/>
                                @endif
                        </div>
                        @elseif($row->status=="PAID" && $row->invoicesource=="MANUAL")
                        <div class="flex items-center space-x-2">
                            <x-button icon="o-document-currency-dollar" class="btn-sm btn-secondary btn-outline" 
                                wire:click="edit({{ $row->id }})" spinner />
                                       </div>
                    @endif
                    @endscope
                    <x-slot:empty>
                        <p class="text-gray-500">No invoices found for this customer.</p>
                    </x-slot:empty>
                </x-table>
        
       
   </x-card>
   <x-modal wire:model="modal" title="Invoice" seperator box-class="max-w-3xl">
    <x-form wire:submit.prevent="save">
        <div class="grid grid-cols-3 gap-2">
        <x-select wire:model.live="inventoryitem" placeholder="Select Inventory Item" label="Inventory Item" :options="$inventoryitems" option-label="name" option-value="id" />
        <x-select wire:model="currency" placeholder="Select Currency" label="Currency" :options="$currencies" option-label="name" option-value="id" />
        <x-input wire:model.live="amount" label="Amount" />
        </div>
        <div class="grid grid-cols-3 gap-2">
        <x-input wire:model="invoicenumber" label="Invoice Number" hint="Leave blank to auto generate" />
        <x-input wire:model="invoicdate" type="date" label="Invoice Date" />
        <x-input wire:model="ratelabel" label="Exchange Rate" readonly>
            <x-slot:append>
                <x-button label="Add" icon="o-plus" class="join-item btn-primary" wire:click="getExchangeRate" spinner="getExchangeRate"/>
            </x-slot:append>
        </x-input>
        </div>
        @if($tendermodal)
        <x-card title="Select Tender" class="border-2 border-gray-200 mt-2" separator>
     
            <x-input wire:model="tendernumber" placeholder="Enter tender number">
                <x-slot:append>
                    <x-button label="Search" class="join-item btn-primary" wire:click="searchtender" spinner="searchtender"/>
                </x-slot:append>
            </x-input>
            
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Tender Number</th>
                        <th>Tender Title</th>
                        <th>Status</th>
                        <th>Fees</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenders as $tender)
                    <tr>
                        <td>{{ $tender->tender_number }}</td>
                        <td>{{ $tender->tender_title }}</td>
                        <td>{{ $tender->status }}</td>
                        <td>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Amount</th>
                                        <th>Currency</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse($tender->tenderfees->where('inventoryitem_id',$this->inventoryitem) as $fee)
                                <tr>
                                    <td>{{ $fee->inventoryitem->name }}</td>
                                    <td>{{ $fee->currency->name }} {{ number_format($fee->amount,2) }}</td>
                                    <td><x-button icon="o-plus" class="btn-primary btn-sm" wire:click="setTender({{ $tender->id }},{{ $fee->id }})"/></td>
                                    
        
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center p-3 text-red-500">No fees found</td>
                                </tr>
                                @endforelse
                            </tbody>
                            </table>
                        </td>
                       
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No tenders found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table> 
            </x-card> 
        @endif

        <div class="grid grid-cols-3 gap-2">
            <x-input wire:model="convertedamount" class="text-lg font-bold" label="Total Due" readonly prefix="{{ $prefix }}"/>
        </div>
        
        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.modal=false"/>
            <x-button label="Save" type="submit" class="btn-primary" spinner="save"/>
        </x-slot:actions>
    </x-form>
   </x-modal>

   <x-modal wire:model="ratemodal" title="Exchange Rate" separator >
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Date</th>
                <th>Rate</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($rates as $rate)
            <tr>
                <td>{{ $rate->created_at }}</td>
                <td>{{ $rate->primarycurrency->name }} 1 = {{ $rate->secondarycurrency->name }} {{ $rate->value }}</td>
                <td><x-button icon="o-plus" class="btn-primary btn-sm" wire:click="setExchangeRate({{ $rate->id }})"/></td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-center p-3 text-red-500">No rates found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
   </x-modal>
 
   <x-modal wire:model="workshopmodal" title="Select Workshop" separator box-class="max-w-3xl">
    
   </x-modal>
   
</div>
