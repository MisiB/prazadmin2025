<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />
    @if($suspenselist->isNotEmpty())

    @php 
    $groupedSuspense = $suspenselist->groupBy('accountnumber');
    @endphp

    @endif

    @if($groupedSuspense->isNotEmpty())
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-2">
    @foreach($groupedSuspense as $accountnumber => $suspense)
    <div class="p-4 rounded-lg border-2 text-center border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
        <x-icon name="o-currency-dollar" class="w-12 h-12 bg-green-700 text-white p-2 rounded-full mb-2" />
        <div class="font-bold">{{ $accountnumber }}</div>
        @php
            $totalutilized =0;
            foreach($suspense as $utilized){
                $totalutilized += $utilized->suspenseutilizations->sum('amount');
            }
           $balance = $suspense->sum('amount')-$totalutilized;
        @endphp
        <div class="text-xs text-green-500">Total claimed {{ $suspense[0]['currency'] }}{{ number_format($suspense->sum('amount'),2) }}</div>
        <div class="text-xs text-red-500">Total utilized {{ $suspense[0]['currency'] }}{{ number_format($totalutilized,2) }}</div>
        <div class="text-xs  border-t-2  border-b-2 ">Balance {{ $suspense[0]['currency'] }}{{ number_format($balance,2) }}</div>
    </div>
    @endforeach
    </div>
    
    @endif
    <x-card title="Suspense Statement" separator class="mt-5 border-2 border-gray-200">
        <x-table :headers="$headers" class="table-zebra table-xs" :rows="$suspenselist">
            <x-slot name="empty">
                <x-alert class="alert-error" title="No Suspense found." />
            </x-slot>
            @scope("cell_amount", $row)
           
                <span class="font-bold text-green-500">{{ $row['currency'] }}{{ number_format($row['amount'],2) }}</span>
          
            @endscope
            @scope("cell_utilized", $row)
            <span class="font-bold text-red-500">{{ $row['currency'] }}{{ number_format($row['suspenseutilizations']->sum('amount'),2) }}</span>
            @endscope
            
            @scope("cell_balance", $row)
            <span class="font-bold text-green-500">{{ $row['currency'] }}{{ number_format($row['amount']-$row['suspenseutilizations']->sum('amount'),2) }}</span>
            @endscope
            
            @scope("cell_action", $row)
            <x-button wire:click="showSuspense({{ $row->id }})" class="btn btn-outline btn-primary btn-xs" spinner="showSuspense">View</x-button>
            @endscope
        </x-table>
    </x-card>
    <x-modal wire:model="showmodal" title="Suspense details" box-class="max-w-3xl">
        <table class="table table-zebra table-xs">
            <thead>
                <tr><th>Date</th><th>Invoice number</th><th>Receipt number</th><th>Item</th><th>Amount</th></tr>
            </thead>
            <tbody>
                @if($showsuspense)
                @php
                $totalutilized = 0;
                @endphp
                @forelse($showsuspense?->suspenseutilizations??[] as $suspenseutilization)
                @php
                $totalutilized += $suspenseutilization->amount;
                @endphp
                <tr>
                    <td>{{ $suspenseutilization->created_at }}</td>
                    <td>{{ $suspenseutilization?->invoice?->invoicenumber }}</td>
                    <td>{{ $suspenseutilization->receiptnumber }}</td>
                    <td>{{ $suspenseutilization->invoice->inventoryitem->name }}</td>
                    <td>{{ $showsuspense->currency }}{{ $suspenseutilization->amount }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">No Suspense Utilizations found.</td>
                </tr>
                @endforelse
                <tr>
                    <td colspan="4"></td>
                    <td  class="text-red-500  border-t ">( {{ $showsuspense->currency }}{{ number_format($totalutilized,2) }})</td>
                </tr>
                <tr>
                    <td colspan="4"></td>
                    <td  class="text-green-500 "> {{ $showsuspense->currency }}{{ number_format($showsuspense->amount,2) }}</td>
                </tr>
                <tr>
                    <td colspan="4"></td>
                    <td  class="text-green-500 border-t-1 border-b-1"> {{ $showsuspense->currency }}{{ number_format($showsuspense->amount-$totalutilized,2) }}</td>
                </tr>
                @endif
            </tbody>
        </table>
    </x-modal>
</div>
