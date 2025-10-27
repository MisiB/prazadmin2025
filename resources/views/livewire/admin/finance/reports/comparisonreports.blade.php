<div>
   <x-card title="Comparison report" separator>
    <x-slot:menu>
        <x-button class="btn-primary" @click="$wire.retrievemodal=true" label="Retrieve"/>
    </x-slot:menu>
    
    @if(session()->has('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    @if(count($rangedata) > 0)
    @php
        $groupedByInventory = $rangedata->sortBy('inventory_name')->groupBy('inventory_name');
    @endphp
        <div class="mb-4">
            <div class="flex justify-between mb-2">
                <div class="text-sm">
                    <span class="font-medium">Current Period:</span> {{ \Carbon\Carbon::parse($fromdate)->format('Y-m-d') }} to {{ \Carbon\Carbon::parse($todate)->format('Y-m-d') }}
                </div>
                <div class="text-sm">
                    <span class="font-medium">Previous Period:</span> {{ \Carbon\Carbon::parse($fromdate2)->format('Y-m-d') }} to {{ \Carbon\Carbon::parse($todate2)->format('Y-m-d') }}
                </div>
            </div>
            
            <div class="overflow-x-auto">
                @foreach($groupedByInventory as $inventoryName => $items)
                <x-card title="{{ $inventoryName }}" separator class="border-2 mt-2">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Currency</th>
                            <th>Current Period</th>
                            <th>Previous Period</th>
                            <th>Difference</th>
                            <th>Change %</th>
                            <th>Trend</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td>{{ $item['currency_name'] }}</td>
                                <td>{{ number_format($item['current_total'], 2) }}</td>
                                <td>{{ number_format($item['previous_total'], 2) }}</td>
                                <td>{{ number_format($item['difference'], 2) }}</td>
                                <td>
                                    <span class="{{ $item['trend'] === 'up' ? 'text-success' : 'text-error' }}">
                                        {{ $item['percentage_change'] }}%
                                    </span>
                                </td>
                                <td>
                                    @if($item['trend'] === 'up')
                                        <x-icon name="o-arrow-trending-up" class="text-success h-5 w-5" />
                                    @else
                                        <x-icon name="o-arrow-trending-down" class="text-error h-5 w-5" />
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </x-card>
                @endforeach
            </div>
        </div>
        
   
    @else
        <div class="alert alert-info mt-4">
            <div class="flex-1">
                <x-icon name="o-information-circle" class="h-6 w-6 mr-2" />
                <span>Use the Retrieve button to generate a comparison report</span>
            </div>
        </div>
    @endif
   </x-card>
   
   <x-modal wire:model="retrievemodal">
      <x-form wire:submit="getrangedata">
         <div class="grid grid-cols-2 gap-2">
            <x-input wire:model="fromdate" label="From Date" type="date"/>
            <x-input wire:model="todate" label="To Date" type="date"/>
         </div>
         <div class="grid grid-cols-2 gap-2">
            <x-select wire:model="status" label="Status" :options="$statuslist" placeholder="Select Status" option-label="name" option-value="id"/>
            <x-select wire:model="rangeperiod" label="Range Period" :options="$rangeperiodlist" placeholder="Select Range Period" option-label="name" option-value="id"/>
         </div>
         <div class="grid  gap-2">
            <x-choices wire:model="inventoryitems" label="Inventory Items" :options="$inventoryitemlist" option-label="name" option-value="id"/>
            <x-choices wire:model="currencyitems" label="Currency Items" :options="$currencyitemlist" option-label="name" option-value="id"/>
         </div>
         <x-slot:actions>
            <x-button class="btn-ghost" @click="$wire.retrievemodal=false" label="Close"/>
            <x-button type="submit" type="submit" class="btn-primary" spinner="getrangedata" label="Retrieve"/>
         </x-slot:actions>
      </x-form>
   </x-modal>
</div>
