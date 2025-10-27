<div>
    <x-breadcrumbs :items="$breadcrumbs"     class="bg-base-300 p-3 mt-2 rounded-box"    link-item-class="text-sm font-bold" />

<x-card title="Bank Reconciliation Report"  class="mt-5 border-2 border-gray-200" separator>
   <x-slot:menu>
    <x-select wire:model.live="filterbystatus" :options="[['id'=>'ALL','name'=>'Show all'],['id'=>'SYNCED','name'=>'Synced'],['id'=>'NOT FOUND','name'=>'Not Found']]"></x-select>
    <x-checkbox label="Show Debit" wire:model.live="showdebit" />
   </x-slot:menu>
   @php
    $rows = $bankreconciliation->bankreconciliationdata??[];
    $totalamount = $rows->sum("tnxamount");
    $totalcurrency = $bankreconciliation->currency->name;
    $totalutilized = $rows->map(function($row){
        return $row->banktransaction?->suspense?->suspenseutilizations->sum("amount");
    })->sum();
    $totalwalletbalance = $totalamount-$totalutilized;
    $row_decoration = [
        'text-error'=>fn($row)=>$row->status == "NOT FOUND",
        'text-success'=>fn($row)=>$row->status == "SYNCED",
        'text-warning'=>fn($row)=>$row->status == "PENDING",
    ];
   @endphp
   <x-tabs wire:model="selectedTab">
    <x-tab name="data-tab" label="Report data" icon="o-list-bullet">
        <div class="grid grid-cols-3 gap-2">
            <div class="bg-gray-200 p-2 rounded">
                <span>Total Amount</span>
                <span>{{ $totalcurrency }}{{ number_format($totalamount,2) }}</span>
            </div>
            <div class="bg-gray-200 p-2 rounded">
                <span>Total Utilized</span>
                <span>{{ $totalcurrency }}{{ number_format($totalutilized,2) }}</span>
            </div>
            <div class="bg-gray-200 p-2 rounded">
                <span>Total Wallet Balance</span>
                <span>{{ $totalcurrency }}{{ number_format($totalwalletbalance,2) }}</span>
            </div>
        </div>
    <x-table :headers="$headers" :rows="$rows" :row-decoration="$row_decoration">
        @scope("cell_claimed", $row)
        <span>{{ $row->banktransaction?->customer?->name }}</span><br/>
        <span>{{ $row->banktransaction?->customer?->regnumber }}</span>
        @endscope
        @scope("cell_utilization", $row)
        <span>{{ $row->banktransaction?->suspense?->currency }}{{ number_format($row->banktransaction?->suspense?->suspenseutilizations->sum("amount"),2) }}</span>
        @endscope
        @scope("cell_walletbalance", $row)
        @php 
            $walletbalance = $row->banktransaction?->suspense?->amount-$row->banktransaction?->suspense?->suspenseutilizations->sum("amount");
        
            @endphp
        <span>{{ $row->banktransaction?->suspense?->currency }}{{ number_format($walletbalance,2) }}</span>
        @endscope
    </x-table>
    </x-tab>
    <x-tab name="summary-tab" label="Summary inventory items" icon="o-list-bullet">
 
    </x-tab>
    </x-tabs>
</x-card>
    
</div>
