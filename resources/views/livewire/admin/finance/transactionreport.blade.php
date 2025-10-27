<div>
   
    <x-card title="Transaction Report" subtitle="From:  {{ $startdate }} To:  {{ $enddate }}" separator>
        <x-slot:menu>
            <x-button icon="o-magnifying-glass-circle" label="Retrive records" class="btn btn-primary" wire:click="modal = true" />
        </x-slot>
        @if($transactions->count() > 0)
        @php 
        $groupbyaccount = $transactions->groupBy('accountnumber');
        @endphp
        <div class="grid grid-cols-2 gap-1">
        @foreach($groupbyaccount as $accountnumber => $transactions)
     
            <livewire:admin.finance.piechart :accounumber="$accountnumber" :totalclaimed="$transactions->where('status', 'CLAIMED')->sum('amount')" :totalpending="$transactions->where('status', 'PENDING')->sum('amount')" :totalblocked="$transactions->where('status', 'BLOCKED')->sum('amount')" />
    
        @endforeach
       </div>
        @else
        <x-alert class="alert-error" title="No transactions found" />
        
        @endif
    </x-card>

    <x-modal wire:model="modal" title="Date Range">
        <x-form wire:submit="retriverecords">
        <div class="grid gap-2">
         
                
                <x-input id="startdate" placeholder="Start Date" type="date" wire:model="startdate" />
           
              
                <x-input id="enddate" placeholder="End Date" type="date" wire:model="enddate" />
                <x-select id="bankaccount" placeholder="Select Bank Account" wire:model="bankaccount" :options="$bankaccounts" option-label="account_number" option-value="id" />
         
        </div>
        <x-button icon="o-check" label="Retrive records" type="submit"  spinner="retriverecords" class="btn btn-primary"/>
        </x-form>
    </x-modal>
</div>
