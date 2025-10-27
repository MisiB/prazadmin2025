<div>
    <x-card title="Banks" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-button class="btn btn-primary" x-icon="o-plus" label="Add Bank" @click="$wire.modal = true"/>
        </x-slot:menu>
        <x-table :rows="$banks" :headers="$headers">
            @scope('cell_status', $row)
            @if($row->status=="ACTIVE")
                <span class="badge badge-success">Active</span>
            @else
                <span class="badge badge-error">Inactive</span>
            @endif
        @endscope
        @scope('actions', $row)
        <div class="flex items-center space-x-2">
            <x-button icon="o-pencil" class="btn-sm btn-info btn-outline" 
                wire:click="edit({{ $row->id }})" spinner />
            <x-button icon="o-trash" class="btn-sm btn-outline btn-error" 
                wire:click="delete({{ $row->id }})" confirm="Are you sure?" spinner />
            <x-button icon="o-currency-dollar" class="btn-sm btn-success btn-outline" 
                wire:click="getbankaccounts({{ $row->id }})" spinner />
        </div>
        @endscope
        <x-slot:empty>
            <x-alert class="alert-error" title="No banks found." />
        </x-slot:empty>
        </x-table>
    </x-card>
    <x-modal wire:model='modal' title="{{ $bankid ? 'Edit Bank' : 'New Bank' }}">
        @if($this->errormessage)
            <x-alert class="alert-error" title="{{ $this->errormessage }}" />
        @endif
        <x-form wire:submit="save">
            <div class="grid gap-2">
                <x-input label="Name" wire:model="name" />
                <x-input label="Email" wire:model="email" />
                <x-select label="Status" wire:model="status" placeholder="Select a status" :options="[['id'=>'active', 'name' => 'Active'], ['id'=>'PENDING', 'name' => 'Pending']]" />
            </div>
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.modal = false" />
                <x-button label="{{ $bankid ? 'Update' : 'Save' }}" type="submit" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>
    <x-modal wire:model="accountmodal" title="Bank Accounts" box-class="max-w-3xl">
        <x-button class="btn btn-sm btn-primary btn-outline" @click="$wire.addaccountmodal = true" label="Add Account" />
        <x-table :rows="$accounts" :headers="$accountheaders">
            @scope('cell_status', $row)
            @if($row->account_status=="ACTIVE")
                <span class="badge badge-success">Active</span>
            @else
                <span class="badge badge-error">Inactive</span>
            @endif
        @endscope
        @scope('actions', $row)
        <div class="flex items-center space-x-2">
            <x-button icon="o-pencil" class="btn-sm btn-info btn-outline" 
                wire:click="getaccount({{ $row->id }})" spinner />
            <x-button icon="o-trash" class="btn-sm btn-outline btn-error" 
                wire:click="deleteaccount({{ $row->id }})" confirm="Are you sure?" spinner />
        </div>
        @endscope
        <x-slot:empty>
            <x-alert class="alert-error" title="No bank accounts found." />
        </x-slot:empty>
        </x-table>
    </x-modal>
    <x-modal wire:model="addaccountmodal" title="{{ $accountid ? 'Edit Bank Account' : 'Add Bank Account' }}">
        @if($this->errormessage)
            <x-alert class="alert-error" title="{{ $this->errormessage }}" />
        @endif
        <x-form wire:submit="saveaccount">
            <div class="grid gap-2">
                <x-input label="Account Number" wire:model="accountnumber" />
                <x-select label="Currency" wire:model="currencyid" placeholder="Select a currency" :options="$currencies" />
                <x-select label="Account Type" wire:model="accounttype" placeholder="Select an account type" :options="[['id'=>'REFUNDABLE', 'name' => 'Refundable'], ['id'=>'NON-REFUNDABLE', 'name' => 'Non-Refundable']]" />
                <x-select label="Status" wire:model="status" placeholder="Select a status" :options="[['id'=>'active', 'name' => 'Active'], ['id'=>'PENDING', 'name' => 'Pending']]" />
            </div>
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.addaccountmodal = false" />
                <x-button label="Save" type="submit" class="btn-primary" spinner="saveaccount" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
