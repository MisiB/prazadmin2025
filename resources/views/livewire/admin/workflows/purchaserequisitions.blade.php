<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-base" />
    <x-card title="Purchase Requisitions" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-input type="text" wire:model="search" placeholder="Search..."/>
            <x-button icon="o-plus" class="btn-primary" label="New" @click="$wire.modal=true"/>
        </x-slot:menu>
        <x-table :headers="$headers" :rows="$purchaserequisitions" class="table-zebra table-xs">
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
                <x-button icon="o-eye" class="btn-xs btn-success btn-outline" link="{{ route('admin.workflows.purchaserequisition',$purchaserequisition->uuid) }}"/>
                @if($purchaserequisition->status == "AWAITING_RECOMMENDATION"||$purchaserequisition->status == "PENDING")
                
                <x-button icon="o-pencil" class="btn-xs btn-info btn-outline" wire:click="edit({{ $purchaserequisition->id }})" spinner/>
                <x-button icon="o-trash" class="btn-xs btn-outline btn-error" wire:click="delete({{ $purchaserequisition->id }})" wire:confirm="Are you sure?" spinner/>
                @endif
            </div>
            @endscope
            <x-slot:empty>
                <x-alert class="alert-error" title="No Purchase Requisitions found."/>
            </x-slot:empty>
        </x-table>
    </x-card>

 
    <x-modal title="{{ $id ? 'Edit Purchase Requisition' : 'New Purchase Requisition' }}" wire:model="modal" box-class="max-w-5xl" separator>
         <x-form wire:submit="save">
            <div class="grid gap-2">
                
                <x-select wire:model.live="budgetitem_id" label="Budget Item" :options="$budgetitems" placeholder="Select Budget Item" option-label="activity" option-value="id" />
            </div>
            <div class="grid grid-cols-3 gap-2">
               
                <x-input wire:model.live="quantity" type="number" label="Quantity" max="{{ $maxquantity }}" />
                <x-input wire:model="unitprice" readonly label="Unit Price"  prefix="USD"/>
                <x-input wire:model="total" readonly label="Total" max="{{ $maxbudget }}" prefix="USD"/>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <x-input wire:model="purpose" label="Purpose" />

                <x-input wire:model="description" label="Description" />
            </div>
            <table class="table table-zebra">
                <tbody>
                    <tr>
                        <th>Available Budget</th>
                        <td class="text-right border-b">USD {{ $maxbudget }}</td>
                    </tr>
                    <tr>
                        <th>Available Quantity</th>
                        <td class="text-right border-b">{{ $maxquantity }}</td>
                    </tr>
                </tbody>
             </table>
            <x-slot:actions>
                <x-button  class="btn-outline btn-error" label="Close" wire:click="$wire.modal = false"/>
                <x-button  class="btn-primary" label="Save" type="submit" spinner="save"/>
            </x-slot:actions>
         </x-form>
       
    </x-modal>
</div>
