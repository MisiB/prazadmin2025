<div>
    <x-breadcrumbs :items="$breadcrumbs"     class="bg-base-300 p-3 mt-2 rounded-box"    link-item-class="text-sm font-bold" />
   <x-card title="Budget Item" subtitle="Budget Item Approval Status :{{ strtoupper($budgetitem->status) }}" separator class="mt-5 border-2 border-gray-200">
    <x-slot:menu>
        <x-button icon="o-plus" label="Add Virament Request" wire:click="modal=true" class="btn-primary btn-sm" />
             </x-slot:menu>
    <table class="table table-zebra table-sm">
        <tbody> 
          <tr>
            <th>Activity</th>
            <td>{{ $budgetitem?->activity }}</td>
          </tr>
          <tr>
            <th>Description</th>
            <td>{{ $budgetitem?->description }}</td>
          </tr>
          <tr>
            <th>Expense Category</th>
            <td>{{ $budgetitem?->expensecategory?->name }}</td>
          </tr>
          <tr>
            <th>Source of Fund</th>
            <td>{{ $budgetitem?->sourceoffund?->name }}</td>
          </tr>
          <tr>
            <th>Strategy Subprogramme Output</th>
            <td>{{ $budgetitem?->strategysubprogrammeoutput?->output }}</td>
          </tr>
          <tr>
            <th>Quantity</th>
            <td>{{ $budgetitem?->quantity }}</td>
          </tr>
          <tr>
            <th>Unit Price</th>
            <td>{{ $budgetitem?->currency?->name }}{{ $budgetitem?->unitprice }}</td>
          </tr>
          <tr>
            <th>Total</th>
            <td>{{ $budgetitem?->currency?->name }}{{ $budgetitem?->total }}</td>
          </tr>
          <tr>
            <th>Status</th>
            <td>{{ $budgetitem?->status }}</td>
          </tr>
          <tr>
            <th>Focus Date</th>
            <td>{{ $budgetitem?->focusdate }}</td>
          </tr>
          <tr>
            <th>Created By</th>
            <td>{{ $budgetitem?->created_by }}</td>
          </tr>
          <tr>
            <th>Created At</th>
            <td>{{ $budgetitem?->created_at }}</td>
          </tr>
          <tr>
            <th>Updated At</th>
            <td>{{ $budgetitem?->updated_at }}</td>
          </tr>
        </tbody>
       </table>

       <x-card title="Actions" separator class="mt-5">
       
       <x-tabs wire:model="myTab">
        <x-tab name="purchase-tab">
            <x-slot:label>  
                Purchase requisitions
                <x-badge value="3" class="badge-soft badge-sm" />
            </x-slot:label>
     
            <div>Users</div>
        </x-tab>
        <x-tab name="incoming-tab" >
          <x-slot:label>  
              Incoming virement requests
              <x-badge value="{{ $budgetitem?->incomingvirements?->count() }}" class="badge-error badge-sm" />
          </x-slot:label>
          <table class="table table-zebra table-sm">
            <thead>
              <tr>
                <th>From Budget Item</th>
                <th>To Budget Item</th>
                <th>Amount</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
          @if($budgetitem?->incomingvirements?->count() > 0)
          <tbody>
            @forelse($budgetitem?->incomingvirements as $incomingvirement)
            <tr>
                <td>{{ $incomingvirement->from_budgetitem->activity }}</td>
                <td>{{ $incomingvirement->to_budgetitem->activity }}</td>
                <td>{{ $incomingvirement->from_budgetitem->currency->name }}{{ $incomingvirement->amount }}</td>
                <td>
                  @if($incomingvirement->status == "PENDING")
                  <x-badge value="PENDING" class="badge-warning badge-sm" />
                  @elseif($incomingvirement->status == "APPROVED")
                  <x-badge value="APPROVED" class="badge-success badge-sm" />
                  @elseif($incomingvirement->status == "REJECTED")
                  <x-badge value="REJECTED" class="badge-error badge-sm" />
                  @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center text-red-500">No incoming virement requests</td>
            </tr>
            @endforelse
          </tbody>
          @else
          <tr>
            <td colspan="4" class="text-center text-red-500">No incoming virement requests</td>
          </tr>
          @endif
      </table>
      </x-tab>
      <x-tab name="outgoing-tab" >
        <x-slot:label>  
            Outgoing virement requests
            <x-badge value="{{ $budgetitem?->outgoingvirements?->count() }}" class="badge-warning badge-sm" />
        </x-slot:label>
 
        <table class="table table-zebra table-sm">
          <thead>
            <tr>
              <th>From Budget Item</th>
              <th>To Budget Item</th>
              <th>Amount</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
        @if($budgetitem?->outgoingvirements?->count() > 0)
        <tbody>
          @forelse($budgetitem?->outgoingvirements as $outgoingvirement)
          <tr>
              <td>{{ $outgoingvirement->from_budgetitem->activity }}</td>
              <td>{{ $outgoingvirement->to_budgetitem->activity }}</td>
              <td>{{ $outgoingvirement->from_budgetitem->currency->name }}{{ $outgoingvirement->amount }}</td>
              <td>
                @if($outgoingvirement->status == "PENDING")
                <x-badge value="PENDING" class="badge-warning badge-sm" />
                @elseif($outgoingvirement->status == "APPROVED")
                <x-badge value="APPROVED" class="badge-success badge-sm" />
                @elseif($outgoingvirement->status == "REJECTED")
                <x-badge value="REJECTED" class="badge-error badge-sm" />
                @endif
              </td>
          </tr>
          @empty
          <tr>
              <td colspan="4" class="text-center text-red-500">No outgoing virement requests</td>
          </tr>
          @endforelse
        </tbody>
        @else
        <tr>
          <td colspan="4" class="text-center text-red-500">No outgoing virement requests</td>
        </tr>
        @endif
    </table>
    </x-tab>
     
    </x-tabs>
    
   </x-card>
   </x-card>

   <x-modal title="{{ $id ? 'Update Virement' : 'Add Virement' }}" wire:model="modal">
   
     <x-form wire:submit="savevirement">
         <div class="grid gap-2">
             <x-select label="To Budget Item" placeholder="Select To Budget Item" wire:model="to_budgetitem" :options="$budgetitems" option-label="activity" option-value="id" />
             <x-input label="Amount" wire:model="amount" min="0" type="number" max="{{ number_format($budgetitem->total) }}" />
             <x-textarea label="Description" wire:model="description" />
         </div>
         <x-slot:actions>
             <x-button label="Cancel" @click="$wire.modal = false" />
             <x-button label="Save" type="submit" class="btn-primary" spinner="savevirement" />
         </x-slot:actions>
     </x-form>
   </x-modal>
</div>
