<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box overflow-x-auto whitespace-nowrap"
    link-item-class="text-base" />
    <x-card title="Awaiting PMU" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-input type="text" wire:model.live="search" placeholder="Search..."/>
        </x-slot:menu>
        <x-table :headers="$headers" :rows="$rows" class="table-zebra table-xs">
            @scope("cell_budgetitem", $row)
            <div>{{ $row->budgetitem->activity }}</div>
            @endscope
            @scope("cell_purpose", $row)
            <div>{{ $row->purpose }}</div>
            @endscope
            @scope("cell_quantity", $row)
            <div>{{ $row->quantity }}</div>
            @endscope
            @scope("cell_unitprice", $row)
            <div>{{ $row->budgetitem->currency->name }} {{ $row->budgetitem->unitprice }}</div>
            @endscope
            @scope("cell_total", $row)
            <div>{{ $row->budgetitem->currency->name }} {{ $row->budgetitem->unitprice * $row->quantity }}</div>
            @endscope
            @scope("cell_created_at", $row)
            <div>{{ $row->created_at->diffForHumans() }}</div>
            @endscope
            @scope("cell_updated_at", $row)
            <div>{{ $row->updated_at->diffForHumans() }}</div>
            @endscope
            @scope("cell_action", $row)
            <div class="flex items-center space-x-2">
                <x-button icon="o-eye" class="btn-xs btn-success btn-outline" wire:click="getpurchaseerequisition({{ $row->id }})"/>
          
            </div>
            @endscope
            <x-slot:empty>
                <x-alert class="alert-error" title="No Purchase Requisitions found."/>
            </x-slot:empty>
        </x-table>
    </x-card>

    <x-modal wire:model="modal" title="Purchase Requisition Details" separator box-class="max-w-6xl" progress-indicator>
        <x-card title="Purchase Requisition" subtitle="{{ $purchaserequisition?->status }}" separator class="mt-5 border-2 border-gray-200">
            <x-slot:menu>
                @if($purchaserequisition?->status == "AWAITING_PMU")
                @can("procurement.approve")
                <x-button icon="o-check" class="btn-success" label="Approve" @click="$wire.approve" wire:confirm="Are you sure you want to approve this purchase requisition?"/>
                @endcan
                @endif
            </x-slot:menu>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <x-input label="PR Number" placeholder="{{ $purchaserequisition?->prnumber }}" readonly />
                <x-input label="Department" placeholder="{{ $purchaserequisition?->department?->name }}" readonly />
                <x-input label="Year" placeholder="{{ $purchaserequisition?->year }}" readonly />
                <x-input label="Budget Item" placeholder="{{ $purchaserequisition?->budgetitem?->activity }}" readonly />
                      <x-input label="Requested By" placeholder="{{ $purchaserequisition?->requestedby?->name }}" readonly />
                <x-input label="Recommended By" placeholder="{{ $purchaserequisition?->recommendedby?->name }}" readonly />
                    <x-textarea label="Purpose" placeholder="{{ $purchaserequisition?->purpose }}" readonly />
                        <x-textarea label="Description" placeholder="{{ $purchaserequisition?->description }}" readonly />
                    
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <x-input label="Quantity" placeholder="{{ $purchaserequisition?->quantity }}" readonly />
                <x-input label="Unit Price" placeholder="{{ $purchaserequisition?->budgetitem?->currency?->name }} {{ $purchaserequisition?->budgetitem?->unitprice }}" readonly />
                <x-input label="Total" placeholder="{{ $purchaserequisition?->budgetitem?->currency?->name }} {{ $purchaserequisition?->budgetitem?->unitprice * $purchaserequisition?->quantity }}" readonly />
            </div>     
                
           
        </x-card>

        <x-card title="Awards" separator class="mt-5 border-2 border-gray-200 mx-auto max-w-full">
            <x-slot:menu>
                @if($purchaserequisition?->awards->count() > 0)
                <div class="font-bold ">Total award: {{ $purchaserequisition?->budgetitem?->currency?->name }}{{ $purchaserequisition?->awards->sum('amount') }}</div>
                @endif
                @if($purchaserequisition?->status == "AWAITING_PMU")
                
                <x-button icon="o-plus" class="btn-primary" label="Add Award" @click="$wire.awardmodal=true" />
              
                @endif
            </x-slot:menu>
           <table class="table  table-zebra">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Tender Number</th>
                    <th>Quantity</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Documents</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($purchaserequisition?->awards??[] as $award)
                <tr>
                    <td>{{ $award->customer->name }}</td>
                    <td>{{ $award->tendernumber }}</td>
                    <td>{{ $award->quantity }}</td>
                    <td>{{ $purchaserequisition?->budgetitem?->currency?->name }}{{ $award->amount }}</td>
                    <td>{{ $award->status }}</td>
                    <td>
                        <x-button icon="o-document" class="indicator btn-xs btn-outline btn-primary" wire:click="getdocuments({{ $award->id }})">
                        
                            <x-badge value="{{ $award->documents->count() }}" class="badge-secondary badge-sm indicator-item" />
                        </x-button>
                        
                    <td>
                        @if($award->status=="PENDING")
                        <div class="flex items-center space-x-2">
                            <x-button icon="o-pencil" class="btn-outline btn-xs btn-primary" wire:click="edit({{ $award->id }})" />
                            <x-button icon="o-trash" class="btn-outline btn-xs btn-error" wire:click="delete({{ $award->id }})" wire:confirm="Are you sure?" />
                        </div>
                        @endif
                        @if($award->status == "AWAITING_PMU" &&  $award->status == "PENDING")
                        @can("purchaserequisition.award")
                        <x-button icon="o-check" class="btn-primary" label="Make decision" @click="$wire.awardmodal=true"/>
                        @endcan
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-red-500">No awards found</td>
                </tr>
                @endforelse
            </tbody>
           </table>

         
           
        </x-card>
       
      
    </x-modal>
    <x-modal wire:model="documentmodal" title="Documents" separator box-class="max-w-3xl" progress-indicator>
        <table class="table table-xs table-zebra">
            <thead>
                <tr>
                    <th>Document</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($documents as $document)
                <tr>
                    <td>{{ $document->document }}</td>
                       <td class="flex justify-end space-x-2">      
                        <x-button icon="o-eye" class="btn-primary btn-xs" wire:click="ViewDocument({{ $document->id }})"/>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" class="text-center text-red-500">No documents found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </x-modal>
    <x-modal wire:model="viewdocumentmodal" title="Document" separator progress-indicator box-class="fixed inset-0 w-screen max-w-full h-screen max-h-full rounded-none">
        @if($currentdocument)
            <div class="w-full h-screen overflow-hidden">
                <iframe src="{{ $currentdocument }}" class="w-full h-full" frameborder="0"></iframe>
            </div>
        @else
            <div class="text-center text-red-500">Document not found</div>
        @endif
    </x-modal>
</div>
