<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box overflow-x-auto whitespace-nowrap"
    link-item-class="text-base" />

    <x-card title="Purchase Requisition Approvals" separator class="mt-5 border-2 border-gray-200">
     
        @if ($workflow)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 ">
            @foreach ($workflow->workflowparameters->sortBy("order") as $workflowparameter)
            <div class="p-4 rounded-lg border-2 text-center border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300 hover:bg-gray-100 cursor-pointer" wire:click="setdata('{{ $workflowparameter->status }}')">
                 @php
                     $count = $purchaserequisitions->where('status', $workflowparameter->status)->count();
                 @endphp
                   <x-badge value="{{ $count }}" class="badge-secondary badge-sm indicator-item" />
                <div class="text-xl font-bold mt-2">{{ $workflowparameter->name }}</div>
                <div class="text-sm mt-2">Step {{ $workflowparameter->order }}</div>
                         </div>
            @endforeach
        </div>

        @else
            <div class="flex items-center justify-center h-64">
                  <x-alert type="error" message="Workflow Not Found" />
            </div>
        @endif
        <x-modal title="Purchase Requisition Approvals" wire:model="modal" box-class="max-w-6xl">
            <x-table :headers="$headers" :rows="$selectedpurchaserequisitions">
                @scope("cell_budgetitem", $purchaserequisition)
                <div>{{ $purchaserequisition->budgetitem->activity }}</div>
                @endscope
                @scope("cell_purpose", $purchaserequisition)
                <div>{{ $purchaserequisition->purpose }}</div>
                @endscope
                @scope("cell_quantity", $purchaserequisition)
                <div>{{ $purchaserequisition->quantity }}</div>
                @endscope
                @scope("cell_unitprice", $purchaserequisition)
                <div>{{ $purchaserequisition->budgetitem->currency->name }} {{ $purchaserequisition->budgetitem->unitprice }}</div>
                @endscope
                @scope("cell_total", $purchaserequisition)
                <div>{{ $purchaserequisition->budgetitem->currency->name }} {{ $purchaserequisition->budgetitem->unitprice * $purchaserequisition->quantity }}</div>
                @endscope
                @scope("cell_action", $purchaserequisition)
                <div class="flex items-center space-x-2">
                    <x-button icon="o-eye" class="btn-xs btn-success btn-outline" link="{{ route('admin.workflows.approvals.purchaserequisitionshow',$purchaserequisition->uuid) }}"/>
                   
                </div>
                @endscope
                <x-slot:empty>
                    <x-alert class="alert-error" title="No data to show" />
                </x-slot:empty>
            </x-table>
            
        </x-modal>
    </x-card>
</div>
 