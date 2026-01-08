<div>
    @if (session()->has('error'))
        <x-alert class="alert-error" title="{{ session('error') }}" />
    @endif
    
    @if (session()->has('info'))
        <x-alert class="alert-info" title="{{ session('info') }}" />
    @endif
   
    <x-card title="Transaction Report" subtitle="From:  {{ $startdate }} To:  {{ $enddate }}" separator>
        <x-slot:menu>
            @if($transactions && $transactions->count() > 0)
                <x-button icon="o-arrow-down-tray" label="Export CSV" class="btn btn-success" wire:click="export" spinner="export" />
            @endif
            <x-button icon="o-magnifying-glass-circle" label="Retrive records" class="btn btn-primary" wire:click="modal = true" />
        </x-slot>
        @if($transactions && $transactions->count() > 0)
            {{-- Pie Charts Summary by Account --}}
            @php
                $groupedByAccount = $transactions->groupBy('accountnumber');
            @endphp
            <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2 lg:grid-cols-{{ min(count($groupedByAccount), 3) }}">
                @foreach($groupedByAccount as $accountNumber => $accountTransactions)
                    @php
                        $totalClaimed = $accountTransactions->where('status', 'CLAIMED')->sum(fn($t) => (float) $t->amount);
                        $totalPending = $accountTransactions->where('status', 'PENDING')->sum(fn($t) => (float) $t->amount);
                        $totalBlocked = $accountTransactions->where('status', 'BLOCKED')->sum(fn($t) => (float) $t->amount);
                    @endphp
                    <livewire:admin.finance.piechart 
                        :accounumber="$accountNumber" 
                        :totalclaimed="$totalClaimed" 
                        :totalpending="$totalPending" 
                        :totalblocked="$totalBlocked"
                        :key="'piechart-' . $accountNumber" />
                @endforeach
            </div>
            
            {{-- Transactions Table --}}
            <x-table :rows="$transactions" :headers="$headers" separator>
                @scope('cell_transactiondate', $row)
                    @php
                        // Handle both formats: d/m/Y (old) and Y-m-d (new)
                        if (str_contains($row->transactiondate, '/')) {
                            $formattedDate = \Carbon\Carbon::createFromFormat('d/m/Y', $row->transactiondate)->format('Y-m-d');
                        } else {
                            $formattedDate = $row->transactiondate;
                        }
                    @endphp
                    {{ $formattedDate }}
                @endscope
                
                @scope('cell_customer.name', $row)
                    {{ $row->customer?->name ?? '-' }}
                @endscope
                
                @scope('cell_description', $row)
                    <div class="max-w-xs truncate" title="{{ $row->description }}">
                        {{ $row->description }}
                    </div>
                @endscope
                
                @scope('cell_amount', $row)
                    <span class="font-semibold">{{ $row->currency }} {{ number_format((float)$row->amount, 2) }}</span>
                @endscope
                
                @scope('cell_status', $row)
                    @php
                        $statusColors = [
                            'CLAIMED' => 'badge-success',
                            'PENDING' => 'badge-warning',
                            'BLOCKED' => 'badge-error',
                        ];
                        $color = $statusColors[$row->status] ?? 'badge-info';
                    @endphp
                    <x-badge :value="$row->status" class="{{ $color }}" />
                @endscope
                
                <x-slot:empty>
                    <x-alert class="alert-error" title="No transactions found" />
                </x-slot:empty>
            </x-table>
        @else
            <x-alert class="alert-error" title="No transactions found" />
        @endif
    </x-card>

    <x-modal wire:model="modal" title="Date Range">
        <x-form wire:submit="retriverecords">
        <div class="grid gap-2">
         
                
                <x-input id="startdate" placeholder="Start Date" type="date" wire:model="startdate" />
           
              
                <x-input id="enddate" placeholder="End Date" type="date" wire:model="enddate" />
                <x-select id="bankaccount" placeholder="Select Bank Account" wire:model="bankaccount" :options="$bankaccounts" option-label="account_number" option-value="account_number" />
         
        </div>
        <x-button icon="o-check" label="Retrive records" type="submit"  spinner="retriverecords" class="btn btn-primary"/>
        </x-form>
    </x-modal>
</div>
