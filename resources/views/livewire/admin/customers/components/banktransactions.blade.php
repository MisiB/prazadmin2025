<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />
    <x-card title="Bank Transactions" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-button icon="o-magnifying-glass" label="Search" wire:click="modal=true" class="btn-primary" />
        </x-slot:menu>
        <x-table :rows="$banktransactions" :headers="$headers">
          
            @scope('cell_transactiondate', $row)
                {{ date('Y-m-d', strtotime($row->transactiondate)) }}
            @endscope
            @scope('actions', $row)
                <div class="flex items-center space-x-2">
                    <x-button icon="o-magnifying-glass-circle" class="btn-sm btn-info btn-outline" wire:click="show({{ $row->id }})" />
                </div>
            @endscope

            <x-slot:empty>
                <x-alert class="alert-error" title="No bank transactions found." />
            </x-slot:empty>
        </x-table>
    </x-card>
    <x-modal wire:model="modal" title="Search bank transactions" separator box-class="max-w-3xl" progress-indicator>
        <x-input wire:model.live.debounce.500ms="search" placeholder="Search bank transactions..." />
        <x-table :rows="$transactions" :headers="$headers">
            @scope('cell_amount', $row)
            {{ $row->currency }} {{ $row->amount }} 
            @endscope
            @scope('actions', $row)
            @if($row->status=="PENDING")
                <div class="flex items-center space-x-2">
                    <x-button icon="o-magnifying-glass-circle" class="btn-sm btn-info btn-outline" label="Claim" wire:click="claim({{ $row->id }})" spinner="claim" wire:confirm="Are you sure?" />
                </div>
            @endif
            @endscope

            <x-slot:empty>
                <x-alert class="alert-error" title="No bank transactions found." />
            </x-slot:empty>
        </x-table>
    </x-modal>
</div>
