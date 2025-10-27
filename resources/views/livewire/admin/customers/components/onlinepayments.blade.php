<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />
    <x-card title="Online Payments" separator class="mt-5 border-2 border-gray-200">
        <x-table :headers="$headers" :rows="$onlinepayments" with-pagination>
            @scope('cell_amount', $row)
            {{ $row->currency?->name }} {{ $row->amount }} 
            @endscope
         
            @scope('cell_status', $row)
            @if(strtoupper($row->status)=="PENDING")
                <span class="badge badge-warning">{{ $row->status }}</span>
            @elseif(strtoupper($row->status)=="CANCELLED" || strtoupper($row->status)=="SENT" || strtoupper($row->status)=="CREATED")
                <span class="badge badge-error">{{ $row->status }}</span>
            @else
                <span class="badge badge-success">{{ $row->status }}</span>
            @endif
            @endscope

            @scope('actions', $row)
            @if(strtoupper($row->status) != "PAID")
            <x-button wire:click="checkpaymentstatus({{ $row->id }})" icon="o-arrow-path" class="btn btn-primary btn-outline btn-xs" label="SYNC" spinner="checkpaymentstatus"/>
            @endif
            @endscope

            <x-slot name="empty">
                <x-alert class="alert-error" title="No online payments found." />
            </x-slot>
        </x-table>
    </x-card>
</div>
