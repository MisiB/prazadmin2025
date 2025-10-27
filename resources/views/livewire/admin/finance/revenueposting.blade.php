<div>
    <x-breadcrumbs :items="$breadcrumbs"     class="bg-base-300 p-3 mt-2 rounded-box"    link-item-class="text-sm font-bold" />
    <x-card class="mt-5 border-2 border-gray-200" title="Revenue Posting" separator>
        <x-slot:menu>
            <x-button label="New Revenue Posting" responsive icon="o-plus" class="btn-outline" @click="$wire.modal = true" />
        </x-slot:menu>
        <x-table :headers="$headers" :rows="$jobs" class="table-zebra table-xs">
            @scope('cell_inventoryitem', $row)
                {{ $row->inventoryitem->name }}
            @endscope
            @scope('cell_currency', $row)
                {{ $row->currency->name }}
            @endscope
            @scope('cell_start_date', $row)
                {{ $row->start_date }}
            @endscope
            @scope('cell_end_date', $row)
                {{ $row->end_date }}
            @endscope
            @scope('cell_year', $row)
                {{ $row->year }}
            @endscope
            @scope('cell_status', $row)
                {{ $row->status }}
            @endscope
            @scope('cell_processed', $row)
                {{ $row->processed }}
            @endscope
            @scope('cell_createdBy', $row)
                {{ $row->createdBy->name }} {{ $row->createdBy->surname }}
            @endscope
            @scope('cell_invoice_count', $row)
            {{ $row->currency->name }}  {{ $row->revenuepostingjobitems->sum('invoice.amount') }}
            @endscope
            @scope('actions', $row)
                <div class="flex items-center space-x-2">
                    @if($row->processed == "PENDING" && $row->status == "PENDING")
                    <x-button icon="o-pencil" class="btn-sm btn-info btn-outline" 
                        wire:click="edit({{ $row->id }})" spinner />
                    <x-button icon="o-trash" class="btn-sm btn-outline btn-error" 
                        wire:confirm="Are you sure?"  wire:click="delete({{ $row->id }})"  spinner="delete" />
                    @endif
                    @if($row->status == "PENDING" )
                    <x-button icon="o-check" class="btn-sm btn-outline btn-success" 
                        wire:confirm="Are you sure?"  wire:click="approve({{ $row->id }})"  spinner="approve" />
                    @endif
                    <x-button icon="o-eye" class="btn-sm btn-outline btn-warning"   wire:click="getjobitems({{ $row->id }})"  spinner="getjobitems" />
                
                </div>
            @endscope
            <x-slot:empty>
                <x-alert class="alert-error" title="No revenue postings found." />
            </x-slot:empty>
        </x-table>
    </x-card>
    <x-modal title="{{ $id ? 'Edit Revenue Posting' : 'New Revenue Posting' }}" wire:model="modal">
        <x-form wire:submit="save">
            <div class="grid grid-cols-2 gap-2">
                <x-select wire:model="inventoryitem_id" label="Inventory Item" placeholder="Select Inventory Item" :options="$inventoryitems" option-label="name" option-value="id" />
                <x-select wire:model="currency_id" label="Currency" placeholder="Select Currency" :options="$currencies" option-label="name" option-value="id" />
          
                <x-input wire:model.live="start_date" label="Start Date" type="date" />
                <x-input wire:model="end_date" label="End Date" type="date"  max="{{ Carbon\Carbon::parse($start_date)->addMonth(3) }}"/>
                  </div>
            <x-slot:actions>
                <x-button label="Close" wire:click="modal=false" />
                <x-button label="{{ $id ? 'Update' : 'Save' }}" type="submit" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>
    <x-modal title="Revenue Posting Items" wire:model="showitemModal" box-class="max-w-5xl">
      
            <x-button label="Export" class="btn-sm btn-outline btn-info" wire:click="exportcsv" />
      
        <table class="table table-xs">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Item</th>
                    <th>Invoice Number</th>
                    <th>Settlement Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jobitems as $jobitem)
                <tr>
                    <td>{{ $jobitem->customer_name }}</td>
                    <td>{{ $jobitem->inventoryitem_name }}</td>
                    <td>{{ $jobitem->invoicenumber }}</td>
                    <td>{{ $jobitem->updated_at }}</td>
                    <td>{{ $jobitem->currency_name }}{{ number_format($jobitem->amount, 2) }}</td>
                    <td>{{ $jobitem->posted }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </x-modal>
</div>
