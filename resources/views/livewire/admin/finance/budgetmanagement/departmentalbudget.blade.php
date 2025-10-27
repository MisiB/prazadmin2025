<div>
    <x-breadcrumbs :items="$breadcrumbs"     class="bg-base-300 p-3 mt-2 rounded-box"    link-item-class="text-sm font-bold" />
    <x-card title="Departmental Budget" subtitle="Budget Approval Status :{{ strtoupper($budget->status) }}" separator class="mt-5 border-2 border-gray-200">
          <x-slot:menu>
               <x-select :options="$budgets" option-label="year" option-value="id" wire:model.live="budget_id" placeholder="Select Budget Year" />
            
               <x-button icon="o-plus" label="Add Budget Item" wire:click="modal=true" class="btn-primary" />
           
          </x-slot:menu>

          <x-tabs wire:model="selectedTab">
            <x-tab name="budget-tab" label="Detailed Budget" icon="o-currency-dollar">
                <div class="grid grid-cols-3 gap-2">
                    <div class="p-5 border-2 border-gray-200 text-center rounded-box">
                        <div>Total Budget</div>
                       <div class=" text-blue-500">
                        {{ $totalbudget }}
                       </div>
                    </div>
                    <div class="p-5 border-2 border-gray-200 text-center rounded-box">
                        <div>Total Utilized</div>
                       <div class=" text-red-500">
                        {{ $totalutilized }}
                       </div>
                    </div>
                 
                  
                    <div class="p-5 border-2 border-gray-200 text-center rounded-box">
                        <div>Total Remaining</div>
                       <div class=" text-green-500">
                        {{ $totalremaining }}
                       </div>
                    </div>
                </div>

    <x-table :headers="$headers" :rows="$budgetitems" class="table-zebra table-sm">
          <x-slot:empty>
            <x-alert class="alert-error" title="No departmental budget found." />
          </x-slot:empty>
          @scope('cell_unitprice', $row)
           {{ $row->currency->name }} {{ $row->unitprice }}
          @endscope
          @scope('cell_total', $row)
          <span class="flex text-blue-500">
           {{ $row->currency->name }} {{ $row->total }}
          </span>
          @endscope

          @scope('cell_utilized', $row)
          <span class="flex text-red-500">
           {{ $row->currency->name }} {{ $row->utilized??0 }}
          </span>
          @endscope
          @scope('cell_remaining', $row)
          <span class="flex text-green-500">
           {{ $row->currency->name }} {{ $row->remaining??0 }}
          </span>
          @endscope
          @scope('cell_status', $row)
          <x-badge value="{{ $row->status }}" class="{{ $row->status == 'PENDING' ? 'badge-warning' : 'badge-success' }} badge-sm"/>
          @endscope
          @scope('actions', $row)
          <div class="flex items-center space-x-2">
            <x-button icon="o-pencil" class="btn-sm btn-info btn-outline" wire:click="edit({{ $row->id }})" spinner />
         
            @if(strtoupper($row->status) == "PENDING")
                 <x-button icon="o-trash" class="btn-sm btn-outline btn-error" wire:click="delete({{ $row->id }})" wire:confirm="Are you sure?" spinner />
            @else
              <x-button icon="o-magnifying-glass-circle"  class="btn-sm btn-warning" link="{{ route('admin.finance.budgetmanagement.departmentalbudgetdetail',$row->uuid) }}" spinner />
               @endif
          </div>
      @endscope
            
    </x-table>
            </x-tab>
            <livewire:admin.finance.budgetmanagement.components.amendmentrequest :budget="$budget" />
       
          </x-tabs>
    </x-card>
    <x-modal  wire:model="modal" title="{{ $id ? 'Edit Budget Item' : 'New Budget Item' }}" separator box-class="max-w-4xl">
        <x-form wire:submit="save">
            <div class="grid grid-cols-3 gap-2">                
                <x-select wire:model="expensecategory_id" label="Expense Category" :options="$expensecategories" placeholder="Select Expense Category" option-label="name" option-value="id"/>
                <x-select wire:model="sourceoffund_id" label="Source of Fund" :options="$sourceoffunds" placeholder="Select Source of Fund" option-label="name" option-value="id"/>
                <x-input wire:model="focusdate" label="Focus Date" type="date"/>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <x-input wire:model.live="quantity" label="Quantity" type="number"/>
                <x-input wire:model.live="unitprice" label="Unit Price"/>
                <x-input wire:model="total" label="Total" readonly/>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <x-input wire:model="activity" label="Activity"/>
                <x-select wire:model="strategysubprogrammeoutput_id" label="Subprogramme Output" :options="$outputs" placeholder="Select Subprogramme Output" option-label="output" option-value="id"/>
            </div>
            <div class="grid gap-2">
                <x-textarea wire:model="description" label="Description"/>
            </div>
            <x-slot:actions>
                <x-button label="Close" wire:click="$wire.modal = false" />
                <x-button label="{{ $id ? 'Update' : 'Save' }}" type="submit" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>
    <x-modal wire:model="viewmodal" title="Budget Item" separator box-class="max-w-4xl">
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
        <x-slot:menu>
            <x-button icon="o-plus" label="Add Purchase Requisition" wire:click="modal=true" class="btn-primary" />
            <x-button icon="o-plus" label="Add Virament Request" wire:click="modal=true" class="btn-primary" />
                 </x-slot:menu>
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
              Incoming virament requests
              <x-badge value="3" class="badge-error badge-sm" />
          </x-slot:label>
   
          <div>Users</div>
      </x-tab>
      <x-tab name="outgoing-tab" >
        <x-slot:label>  
            Outgoing virament requests
            <x-badge value="3" class="badge-warning badge-sm" />
        </x-slot:label>
 
        <div>Users</div>
    </x-tab>
     
    </x-tabs>
    </x-card>
    </x-modal>
</div>
