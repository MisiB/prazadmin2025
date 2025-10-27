<div>
    <x-card title="Invoices" separator>
        <x-slot:menu>
            <x-button class="btn-primary" @click="$wire.retrievemodal=true" label="Retrieve"/>
        </x-slot>
        @if($invoices->count()>0)
        <x-button class="btn-success" wire:click="exportdocument" icon="o-arrow-up-on-square-stack" spinner="exportdocument" label="Export"/>
        <x-button class="btn-warning" wire:click="summaryreport" icon="o-presentation-chart-bar" spinner="summaryreport" label="Summary"/>
        @endif
        <x-table :headers="$headers" :rows="$invoices"  separator progress-indicator  with-pagination>
                @scope('cell_receipts',$row)
                        <span>{{ $row?->receipts?->last()?->created_at }}</span>
                    
                 
                @endscope
                <x-slot:empty>
                    <div class="alert alert-warning">
                        No invoices found
                    </div>
                </x-slot:empty>
                </x-table>

    </x-card>
    <x-modal wire:model="retrievemodal" title="Retrieve Invoices" >
      <x-form wire:submit="getInvoicespaginated">
        <div class="grid grid-cols-3 gap-4">
        <x-input wire:model="fromdate" label="From Date" type="date" />
        <x-input wire:model="todate" label="To Date" type="date" />
        <x-select wire:model="status" label="Status" :options="$statuslist" placeholder="Select Status" option-label="name" option-value="id" />
       </div>
       <div class="grid  gap-4">
        <x-choices wire:model="inventoryitems" label="Inventory Items" :options="$inventoryitemlist" option-label="name" option-value="id" />
        <x-choices wire:model="currencyitems" label="Currency Items" :options="$currencyitemlist" option-label="name" option-value="id" />
       
        </div>
    
        <x-slot:actions>
            <x-button class="btn-ghost" @click="$wire.retrievemodal=false">Close</x-button>
            <x-button type="submit" type="submit" class="btn-primary" spinner="getInvoicespaginated" >Retrieve</x-button>
        </x-slot:actions>
        </x-form>
    </x-modal>
    <x-modal wire:model="summarymodal" title="Summary">
       <table class="table table-striped">
        <thead>
            <tr>
                <th>Inventory Item</th>                
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $groupbyinventoryitem = collect($summarydata)->groupBy("inventoryitem");
            @endphp
            @foreach ($groupbyinventoryitem as $inventoryitem => $items)
            <tr>
                <td>{{ $inventoryitem }}</td>
                <td>
                    @foreach ($items as $item)
                       <x-badge value="{{ $item['currency'] }}{{ $item['total'] }}" class="badge-soft" />
                    @endforeach
                </td>
            </tr>
            @endforeach
        </tbody>
       </table>
    </x-modal>
</div>
