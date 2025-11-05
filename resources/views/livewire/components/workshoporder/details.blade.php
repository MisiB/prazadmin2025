<div>
    <x-card title="{{ $status }} Orders" separator>
        <x-table :headers="$headers" :rows="$orders">
             @scope('cell_customer', $row)
                <div>{{ $row->customer->name }}</div>
                <div><b>name:</b> <small>{{ $row->name }} {{ $row->surname }}</small></b></div>
                <div><b>email:</b> <small>{{ $row->email }}</small></b></div>
                <div><b>phone:</b> <small>{{ $row->phone }}</small></b></div>
             @endscope
             @scope('cell_amount', $row)
                <div>{{ $row->currency->name }} {{ $row->amount }}</div>
             @endscope
             @scope('cell_status', $row)
                <div class="badge badge-{{ $row->status == 'AWAITING' ? 'warning' : ($row->status == 'PENDING' ? 'info' : ($row->status == 'PAID' ? 'success' : 'error')) }}" >{{ $row->status }}</div>
             @endscope
             @scope('cell_actions', $row)
                <div class="flex items-center space-x-2">
                  <livewire:components.workshoporder.vieworder :orderid="$row->id" />
                 @can("workshops.modify")
                  @if($row->status != 'PAID')
                 
                  <livewire:components.workshoporder.editorder :orderid="$row->id"  />
                     <x-button icon="o-trash" class="btn-ghost btn-sm btn-error" wire:click="deleteorder({{ $row->id }})" wire:confirm="Are you sure?" spinner="deleteorder" />
                
                  @endif
                 @endcan

                  </div>
             @endscope
             <x-slot:empty>
                <x-alert class="alert-error" title="No orders found." />
                
             </x-slot:empty>
        </x-table>
    </x-card>
</div>
