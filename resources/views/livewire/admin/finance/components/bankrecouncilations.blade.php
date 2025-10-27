<div>
    <x-card class="border-2 border-gray-200" title="Bank Reconciliations" separator>
        <x-slot:menu>
            <x-button icon="o-plus" label="Add" wire:click="modal=true" class="btn-primary" />
        </x-slot:menu>
       <x-table :headers="$headers" :rows="$bankreconciliations">
        @scope("cell_dates", $row)
        <div>
           <small>Start date: {{ $row->start_date }}</small> <br> <small>End date: {{ $row->end_date }}</small>
        </div>
        @endscope
        @scope("cell_balances", $row)
        <div>
          <small>Opening Balance: {{ $row->currency->name }} {{ $row->opening_balance }}</small> <br> <small>Closing Balance: {{ $row->currency->name }} {{ $row->closing_balance }}</small>
        </div>

        @endscope
        @scope("actions", $row)
        <div class="flex items-center space-x-2">
            <x-button icon="o-pencil" class="btn-sm btn-info btn-outline" 
                wire:click="edit({{ $row->id }})" spinner />
                @if($row->status == "PENDING")
            <x-button icon="o-trash" class="btn-sm btn-outline btn-error" 
                wire:click="delete({{ $row->id }})" wire:confirm="Are you sure?" spinner />
                @endif
                @if($row->status == "PENDING")
                <x-button icon="o-folder-open" class="btn-sm btn-outline btn-warning" 
                wire:click="extractdata({{ $row->id }})" spinner="extractdata" />
            
                @endif
                @if($row->status == "EXTRACTED")
                <x-button icon="o-arrows-pointing-in" class="btn-sm btn-outline btn-warning" 
                wire:click="syncdata({{ $row->id }})" spinner="syncdata" />
                @endif
                @if($row->status == "SYNCED")
                <x-button icon="o-document-chart-bar" class="btn-sm btn-outline btn-success" spinner="viewreport" link="{{ route('admin.finance.reports.bankreconciliationreport',$row->id) }}"/>
                @endif
        </div>
        @endscope
        @scope("cell_status", $row)
    
        <x-badge value="{{ $row->status }}" class="{{ $row->status == 'PENDING' ? 'badge-warning' : 'badge-success' }}"/>
        @endscope
        <x-slot:empty>
            <div class="flex items-center justify-center h-full">
                <p class="text-red-500">No bank reconciliations found</p>
            </div>
        </x-slot:empty>
       </x-table>
    </x-card>
   
    <x-modal wire:model="modal" title="{{ $id ? 'Edit Bank Reconciliation' : 'New Bank Reconciliation' }}">
        <x-form wire:submit="save">
            <div class="grid grid-cols-2 gap-2">
                <x-input label="Start Date" wire:model="startdate" type="date" />
                <x-input label="End Date" wire:model="enddate" type="date" />
            </div>
            <div class="grid grid-cols-3 gap-2">
                <x-select label="Bank Account" wire:model="bankaccount" placeholder="Select Bank Account" :options="$bankaccounts" option-label="account_number" option-value="id" />
                <x-input label="Opening Balance" wire:model="openingbalance" type="number" step="0.01"/>
                <x-input label="Closing Balance" wire:model="closingbalance" type="number" step="0.01"/>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <x-input label="Year" wire:model="year" />
                <x-input label="File" wire:model="file" type="file" />
            </div>
         <x-slot:actions>
            <x-button label="Close" wire:click="closemodal" class="btn-outline" />
            <x-button label="Save" type="submit" class="btn-primary" spinner="save" />          
        </x-slot:actions>
    </x-form>
    </x-modal>

    <x-modal wire:model="viewmodal" title="Bank Reconciliation" box-class="max-w-5xl">
    </x-modal>
 
</div>
