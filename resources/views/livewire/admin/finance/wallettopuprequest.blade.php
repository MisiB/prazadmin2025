<div>
    <x-breadcrumbs :items="$breadcrumbs"     class="bg-base-300 p-3 mt-2 rounded-box"    link-item-class="text-sm font-bold" />
   
    @if($wallettopups->count() > 0)
     @php 
     $groupbyaccount = $wallettopups->groupBy('accountnumber');
     
     @endphp
   
    @if($groupbyaccount->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-5">
    @foreach($groupbyaccount as $account=>$data)
    <div class="p-4 rounded-lg border-2 text-center border-gray-200  hover:shadow-md transition-shadow duration-300">
        <div class="flex flex-col items-center">
            <span class="text-lg font-bold">{{ $account }}</span>
            <span class="text-sm text-gray-600">{{ $data[0]->currency->name }}{{ $data->sum('amount') }} </span>
        </div>
    </div>
    @endforeach
    </div>
    @endif
    @endif
    <x-card title="Wallet topup request" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
                <x-input wire:model.live="year" type="number" placeholder="Year" />
                <x-select wire:model.live="status" :options="$statuslist" option-label="label" option-value="id" />
            
        </x-slot:menu>
      <table class="table table-xs table-zebra">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Year</th>
                <th>Bank Account</th>
                <th>Amount</th>
                <th>Request date</th>
                <th>Initiated by</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($wallettopups as $wallettopup)
            <tr>
                <td>{{ $wallettopup->customer->name }}</td>
                <td>{{ $wallettopup->year }}</td>
                <td>{{ $wallettopup->accountnumber }}</td>
                <td>{{ $wallettopup->currency->name }}{{ $wallettopup->amount }}</td>
                <td>{{ $wallettopup->created_at }}</td>
                <td>{{ $wallettopup->initiator->email }}</td>
                <td>
                    @if($wallettopup->status == "PENDING")
                    <span class="badge badge-xs badge-warning">{{ $wallettopup->status }}</span>
                    @elseif($wallettopup->status == "REJECTED")
                    <span class="badge badge-xs badge-error">{{ $wallettopup->status }}</span>
                    @else
                    <span class="badge badge-xs badge-success">{{ $wallettopup->status }}</span>
                    @endif
                </td>
                <td class="flex space-x-2">
                    <x-button label="View" wire:click="view({{ $wallettopup->id }})" class="btn-outline btn-sm" />
                    
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">No wallet topup requests found.</td>
            </tr>
            @endforelse
        </tbody>
      </table>
       
    </x-card>

    <x-modal wire:model="showmodal" title="Wallet Topup" box-class="max-w-3xl" separator>
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
             <td>
                @if($wallettopup->banktransaction)
                {{ $wallettopup->banktransaction->sourcereference }}
                @elseif($wallettopup->status == "PENDING")
                  
                 <x-button label="Link" wire:click="showlinkmodal=true" class="btn-primary btn-xs" />
                 @else
                 <span class="text-red-500">--</span>
                @endif
             </td>
         </tr>
         <tr>
             <th>Linked by</th>
             <td>{{ $wallettopup->linkeduser->email ?? "--" }}</td>
         </tr>
         <tr>
             <th>Suspense balance</th>
             <td>{{ $wallettopup->currency->name }}{{ $wallettopup?->suspense?->amount-$wallettopup?->suspense?->suspenseutilizations->sum('amount') ?? "--" }}</td>
         </tr>
         </tbody>
        </table>
        @if($wallettopup->status == "PENDING")
            <x-form wire:submit.prevent="makedecision">
       
                <x-select label="Status" wire:model.live="status" placeholder="Select Status" :options="[['id'=>'APPROVED','label'=>'APPROVED'],['id'=>'REJECTED','label'=>'REJECTED']]" option-label="label" option-value="id" />
                
                @if($status == "REJECTED")
                <x-textarea label="Reason" wire:model="reason" placeholder="Enter Reason" />
                @endif
                <x-slot name="actions">
                    <x-button label="Cancel" @click="$wire.showmodal = false" />
                    <x-button label="Save" class="btn-primary" type="submit" spinner="save" spinner/>
                </x-slot>
            </x-form>
                         @endif

      @endif
     </x-modal>
     <x-modal wire:model="showlinkmodal" title="Link Bank Transaction" box-class="max-w-4xl" separator>
        <x-input label="Bank Transaction" wire:model.live="search" placeholder="Enter Bank Transaction" />
        <x-table :rows="$banktransactions" :headers="$headers"  class="table-xs table-zebra">
            @scope('cell_amount', $row)
            {{ $row->currency }} {{ $row->amount }} 
            @endscope
            @scope('actions', $row)
            @if($row->status=="PENDING")
                <div class="flex items-center space-x-2">
                    <x-button icon="o-magnifying-glass-circle" class="btn-sm btn-info btn-outline" label="Link" wire:click="link({{ $row->id }})" spinner="link" wire:confirm="Are you sure?" />
                </div>
            @endif
            @endscope

            <x-slot:empty>
                <x-alert class="alert-error" title="No bank transactions found." />
            </x-slot:empty>
        </x-table>
     </x-modal>
        
</div>
