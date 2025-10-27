<div>
    <x-card class="mt-5 border-2 border-gray-200" title="Search transactions" separator>
        <x-slot:menu>
            <x-form wire:submit="searchtransactions">
            <x-input placeholder="Search" wire:model="search">
        <x-slot:append>
        <x-button label="Search" type="submit" class="join-item btn-primary" spinner="searchtransactions" />
    </x-slot:append>
</x-input>
            </x-form>
        </x-slot>
        <x-table :headers="$headers" :rows="$transactions" separator progress-indicator>
                    @scope("cell_Status",$payload)
                        @if(strtoupper($payload->Status) =="BLOCKED")
                     
                            <x-badge :value="$payload->Status" class="text-white bg-red-500" />
                           
                        @elseif ($payload->Status =="CLAIMED")
                        <x-badge :value="$payload->Status" class="text-white bg-green-500" />
                        @else
                        <x-badge :value="$payload->Status"  />
                        @endif

                    @endscope
                    @scope("cell_Description", $transaction)
                                            
                    <div><b>Account Name:</b> {{ $transaction?->customer?->name }}- {{ $transaction?->customer?->regnumber }}</div>
                        <div><b>Description:</b> {{ $transaction->description }}</div>
                        <div><b>Reference Number:</b> {{ $transaction->sourcereference }}</div>
                        <div><b>Account Number:</b> {{ $transaction->accountnumber }}</div>
                        <div><b>Source Reference:</b> {{ $transaction->statementreference }}</div>
                        <div><b>Currency:</b> {{ $transaction->currency }}</div>
                        <div><b>Amount:</b> {{ number_format($transaction->amount, 2) }}</div>
                        <div><b>Transaction Date:</b> {{ $transaction->transactiondate }}</div>
                        <div><b>Status:</b> 
                        <x-badge :value="$transaction->status" class="{{$transaction->status == 'PENDING' ? 'bg-yellow-500' : ($transaction->status == 'CLAIMED' ? 'bg-green-500' : 'bg-red-500')}} text-white" />
                        </div>
                    @endscope
                    @scope('actions',$payload)
                    @if(strtoupper($payload->status) =="PENDING")               
                    <x-button icon="o-no-symbol" label="BLOCK" wire:click="blockTransaction({{$payload->id}})"  spinner="blockTransaction" wire:confirm="Are you sure you want to block this transaction?"
                        class="btn btn-sm btn-error"/>
                        @elseif(strtoupper($payload->status) =="BLOCKED")
                        <x-button icon="o-check" label="UNBLOCK" wire:click="unblockTransaction({{$payload->id}})"  spinner="unblockTransaction" wire:confirm="Are you sure you want to unblock this transaction?" class="btn btn-sm btn-success"/>
                        @elseif(strtoupper($payload->status) =="CLAIMED")
                        <x-button icon="o-magnifying-glass" label="View" wire:click="viewTransaction({{$payload->id}})"  spinner="viewtransaction"  class="btn btn-sm btn-success"/>
                    @endif
                 
                    @endscope
                    <x-slot:empty>
                        <div class="text-center">
                            <x-icon name="o-inbox-stack" class="w-10 h-10" />
                            <p class="mt-3 text-sm text-red-500">No transactions found</p>
                        </div>
                    </x-slot>
                </x-table>
    </x-card>
    <x-modal wire:model="transactionmodal" title="View Transaction" box-class="w-full max-w-3xl" >
        <table class="table table-bordered table-sm">
            <tbody>
                <tr>
                 <th>Account Name</th>
                    <td>{{ $transaction?->customer?->name }}</td>
                </tr>
                <tr>
                    <th>Account Number</th>
                    <td>{{ $transaction?->customer?->regnumber }}</td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td>{{ $transaction?->description }}</td>
                </tr>
                <tr>
                    <th>Source Reference</th>
                    <td>{{ $transaction?->sourcereference }}</td>
                </tr>
                <tr>
                    <th>Account Number</th>
                    <td>{{ $transaction?->accountnumber }}</td>
                </tr>
                <tr>
                    <th>Statement Reference</th>
                    <td>{{ $transaction?->statementreference }}</td>
                </tr>
                <tr>
                    <th>Currency</th>
                    <td>{{ $transaction?->currency }}</td>
                </tr>
                <tr>
                    <th>Amount</th>
                    <td>{{ number_format($transaction?->amount, 2) }}</td>
                </tr>
                <tr>
                    <th>Transaction Date</th>
                    <td>{{ $transaction?->transactiondate }}</td>
                </tr>
            </tbody>
        </table>
        @if($transaction?->suspense)
         <x-card title="Suspense Utilizations" separator class="mt-5">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Invoice Number</th>
                    <th>Receipt Number</th>
                    <th>Service</th>
                    <th>Receipt Amount</th>
                    <th>WalletBalance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaction?->suspense?->suspenseutilizations as $suspenseutilization)
                <tr>
                    <td>{{ $suspenseutilization?->created_at->format('d/m/Y') }}</td>
                    <td>{{ $suspenseutilization?->invoice?->invoicenumber }}</td>
                    <td>{{ $suspenseutilization?->receiptnumber }}</td>
                    <td>{{ $suspenseutilization?->invoice?->inventoryitem?->name }}</td>
                    <td>{{ number_format($suspenseutilization?->amount, 2) }}</td>
                    <td>{{ number_format($transaction?->suspense->amount - $suspenseutilization?->amount, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No suspense utilizations found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </x-card>
@else
<div class="text-center">
    <x-icon name="o-inbox-stack" class="w-10 h-10" />
    <p class="mt-3 text-sm text-red-500">No suspense record found</p>
</div>
@endif
    </x-modal>
</div>
