<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />
    <x-card title="Wallet Tops" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-button icon="o-plus" wire:click="modal = true" label="Add" class="btn-primary" />
        </x-slot:menu>
        <x-table :headers="$headers" :rows="$wallettopups">
            @scope('cell_amount', $row)
            {{ $row->currency?->name }} {{ $row->amount }} 
            @endscope
         
            @scope('cell_status', $row)
            @if(strtoupper($row->status) == "PENDING")
                <span class="badge badge-warning">{{ $row->status }}</span>
            @elseif(strtoupper($row->status) == "REJECTED")
                <span class="badge badge-error">{{ $row->status }}</span>
            @else
                <span class="badge badge-success">{{ $row->status }}</span>
            @endif
            @endscope

            @scope('actions', $row)
            <div class="flex items-center space-x-2">
                <x-button wire:click="show({{ $row->id }})" icon="o-magnifying-glass-circle" class="btn btn-success btn-outline btn-xs"/>
            @if(strtoupper($row->status) == "PENDING")
          

            <x-button wire:click="edit({{ $row->id }})" icon="o-pencil" class="btn btn-warning btn-outline btn-xs"/>
            <x-button wire:click="  delete({{ $row->id }})" icon="o-trash" class="btn btn-error btn-outline btn-xs" wire:confirm="Are you sure?" spinner="delete"/>
          
            @endif
            </div>
            @endscope

            <x-slot name="empty">
             <x-alert class="alert-error" title="No Wallet Topup Record Found" />
            </x-slot>
        </x-table>
    </x-card>

    <x-modal wire:model="modal" title="{{ $id ? 'Edit Wallet Topup' : 'New Wallet Topup' }}" separator>
        <x-form wire:submit.prevent="save">
            <div class="grid gap-2">
                <x-input label="Amount" type="number" wire:model="amount" />
                <x-select label="Currency" wire:model.live="currency_id" placeholder="Select Currency" :options="$currencies" />
                <x-select label="Bank Account" wire:model="bankaccount_id" placeholder="Select Bank Account" option-label="account_number" option-value="account_number" :options="$bankaccounts" />
                <x-textarea label="Reason" wire:model="reason" />
            </div>
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.modal = false" />
                <x-button label="Save" class="btn-primary"  type="submit" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>
    <x-modal wire:model="showmodal" title="Wallet Topup" separator>
       @if($wallettopup)
       <table class="table">
        <tbody>
        <tr>
            <th>Amount</th>
            <td>{{ $wallettopup->currency->name }}{{ $wallettopup->amount }}</td>
        </tr>
        <tr>
            <th>Bank Account</th>
            <td>{{ $wallettopup->accountnumber }}</td>
        </tr>
        <tr>
            <th>Reason</th>
            <td>{{ $wallettopup->reason }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                @if(strtoupper($wallettopup->status) == "PENDING")
                <span class="badge badge-warning">{{ $wallettopup->status }}</span>
                @elseif(strtoupper($wallettopup->status) == "REJECTED")
                <span class="badge badge-error">{{ $wallettopup->status }}</span>
                @else
                <span class="badge badge-success">{{ $wallettopup->status }}</span>
                @endif
            </td>
        </tr>
        <tr>
            <th>Initiated By</th>
            <td>{{ $wallettopup->initiator->email }}</td>
        </tr>
        <tr>
            <th>Processed By</th>
            <td>{{ $wallettopup->approver?->email }}</td>
        </tr>
        <tr>
            <th>Initiated At</th>
            <td>{{ $wallettopup->created_at }}</td>
        </tr>
        @if($wallettopup->status == "REJECTED")
        <tr>
            <th>Rejection Reason</th>
            <td>{{ $wallettopup->rejectedreason }}</td>
        </tr>
        
        @endif
        <tr>
            <th>Linked bank transaction</th>
            <td>{{ $wallettopup->banktransaction->sourcereference ?? "--" }}</td>
        </tr>
        <tr>
            <th>Linked by</th>
            <td>{{ $wallettopup->linkeduser->email ?? "--" }}</td>
        </tr>
        <tr>
            <th>Suspense</th>
            <td>{{ $wallettopup->currency->name }}{{ $wallettopup?->suspense?->amount-$wallettopup?->suspense?->suspenseutilizations->sum('amount') ?? "--" }}</td>
        </tr>
        </tbody>
       </table>
       @endif
    </x-modal>
</div>
