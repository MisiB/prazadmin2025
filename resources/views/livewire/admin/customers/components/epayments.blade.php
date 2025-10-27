<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />
       <x-card title="ePayments" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-input wire:model.live="search" placeholder="Search"/>
        </x-slot:menu>
        <x-table :headers="$headers" :rows="$epayments">
            @scope('cell_amount', $row)
            {{ $row->currency }} {{ $row->amount }} 
            @endscope
         
            @scope('cell_status', $row)
            @if($row->status=="PENDING")
                <span class="badge badge-warning">{{ $row->status }}</span>
            @else
                <span class="badge badge-success">{{ $row->status }}</span>
            @endif
            @endscope

            <x-slot:empty>
                <x-alert class="alert-error" title="No bank transactions found." />
            </x-slot:empty>
        </x-table>
       </x-card>
</div>
