<div>
<x-tab name="amendment-tab"  icon="o-currency-dollar">
    <x-slot:label>  
        Amendment requests
        <x-badge value="{{ $budget->budgetitems->where('status', 'PENDING')->count() }}" class="badge-error badge-sm" />
    </x-slot:label>
  @if($budget->status == "APPROVED")
  <x-card title="Amendment Request">
     <x-slot:menu>
        @if($budget->budgetitems->where('status', 'PENDING')->count() > 0)
        @can('consolidatedbudget.approve')
      <x-button label="Approve all" class="btn-sm btn-primary btn-outline" wire:click="approveall" wire:confirm="Are you sure you want to approve all budget items?" spinner />
      @endcan
      @endif
     </x-slot:menu>
    <table class="table table-zebra table-sm">
      <thead>
        <tr>
          <th>Activity</th>
          <th>Output</th>
          <th>Department</th>
          <th>Expense Category</th>
          <th>Source of Funds</th>
          <th>Quantity</th>
          <th>Unit Price</th>
          <th>Total</th>
          <th>Utilized</th>
          <th>Remaining</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($budget->budgetitems->where('status', 'PENDING') as $budgetitem)
        <tr>
          <td>{{ $budgetitem->activity }}</td>
          <td>{{ $budgetitem->output }}</td>
          <td>{{ $budgetitem->department->name }}</td>
          <td>{{ $budgetitem->expensecategory->name }}</td>
          <td>{{ $budgetitem->sourceoffund->name }}</td>
          <td>{{ $budgetitem->quantity }}</td>
          <td>{{ $budgetitem->unitprice }}</td>
          <td>{{ $budgetitem->total }}</td>
          <td>{{ $budgetitem->utilized }}</td>
          <td>{{ $budgetitem->remaining }}</td>
          <td>{{ $budgetitem->status }}</td>
          <td>
            @can('consolidatedbudget.approve')
            <x-button icon="o-check"  class="btn-sm btn-primary btn-outline" wire:click="approvebudgetitem({{ $budgetitem->id }})" wire:confirm="Are you sure you want to approve this budget item?" spinner />
            @endcan
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="12" class="text-center text-red-500">No amendment request found</td>
        </tr>
        @endforelse
      </tbody>
    </table>
    
  </x-card>
  @else
  <x-alert type="error" message="Budget is not approved" />
  @endif
</x-tab>
</div>
