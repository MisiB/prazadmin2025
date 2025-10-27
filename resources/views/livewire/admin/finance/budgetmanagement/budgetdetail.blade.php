<div>
    <x-breadcrumbs :items="$breadcrumbs"     class="bg-base-300 p-3 mt-2 rounded-box"    link-item-class="text-sm font-bold" />
    <x-card title="Budget Management" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            @if(strtoupper($budget->status) == "PENDING")
                <x-button wire:click="approvebudget" class="btn btn-primary">Approve</x-button>
            @endif
        </x-slot:menu>
        <table class="table table-zebra table-sm">
            <tbody>
                <tr>
                    <td>Year</td>
                    <td>{{ $budget->year }}</td>
                </tr>
                <tr>
                    <td>Created By</td>
                    <td>{{ $budget->createdby?->name }}</td>
                </tr>
                <tr>
                    <td>Updated By</td>
                    <td>{{ $budget->updatedby?->name??"--" }}</td>
                </tr>
                <tr>
                    <td>Approved By</td>
                    <td>{{ $budget->approvedby?->name??"--" }}</td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>{{ $budget->status }}</td>
                </tr>
            </tbody>
        </table>
        <x-card class="mt-5 border-2 border-gray-200">
        <x-tabs wire:model="selectedTab">
            <x-tab name="budget-tab" label="Consolidated" icon="o-currency-dollar">
                <livewire:admin.finance.budgetmanagement.components.consolidated :budget="$budget" />
            </x-tab>
            <x-tab name="users-tab" label="Summary by departments" icon="o-currency-dollar">
                <livewire:admin.finance.budgetmanagement.components.summarybydepartment :budget="$budget" />
            </x-tab>
            <x-tab name="tricks-tab" label="Summary by outputs" icon="o-currency-dollar">
                <livewire:admin.finance.budgetmanagement.components.summarybyactivity :budget="$budget" />
            </x-tab>
           
                <livewire:admin.finance.budgetmanagement.components.viramentrequest :budget="$budget" />
        
          
                <livewire:admin.finance.budgetmanagement.components.amendmentrequest :budget="$budget" />
       
        </x-tabs>
        </x-card>
    </x-card>
</div>
