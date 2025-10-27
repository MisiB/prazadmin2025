<div>
<x-card>
    <x-slot name="title">Suspense Reports</x-slot>
    <x-slot name="menu">
        <div class="flex items-center gap-4">
            <x-input icon="o-magnifying-glass" wire:model.live="search" placeholder="Search Suspense..." class="max-w-sm" />
        </div>
    </x-slot>
    @php
        $groupbyaccountnumber = collect($rows)->groupBy("accountnumber");
        $array = [];
        foreach($groupbyaccountnumber as $key => $value){
            $array[] = [
                "accountnumber"=>$key,
                "total"=>$value->sum("amount")-$value->sum("total_utilized"),
                "currency"=>$value[0]['currency'],
                "count"=>$value->count()
            ];
        }
    @endphp
    @if(count($array)>0)
    <div class="grid grid-cols-4 gap-1">
     @foreach($array as $row)
     <div class="p-5 border bg-gray-100 rounded shadow-sm">
        <div>
            <span>{{ $row['accountnumber'] }}</span>
            <br/>
            <span><b>{{ $row['currency'] }}{{ number_format($row['total'],2) }}</b></span>
        </div>
     </div>
     @endforeach
    </div>
    @endif
    <x-card title="Suspense data" separator>
     <x-table :headers="$headers" :rows="$rows">
        @scope('cell_amount',$row)
        <span class="font-bold text-green-500">{{ $row['currency'] }}{{ number_format($row['amount'],2) }}</span>-<span class="font-bold text-red-500">{{ $row['currency'] }}{{ number_format($row['total_utilized'],2) }}</span>
        @endscope
        @scope('cell_balance',$row)
        <span class="font-bold text-red-500">{{ $row['currency'] }}{{$row['balance']}}</span>
        @endscope
        <x-slot:empty>
            <x-alert class="alert-error" title="No Suspense found." />
        </x-slot:empty>
    </x-table> 
    </x-card>

</x-card>
</div>
